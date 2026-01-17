<?php
// Debug flag (set to false in production)
$DEBUG = true;
// Start session and check login status BEFORE any output
require_once 'include/database.php';
startSecureSession();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a service request.']);
    exit();
}

// Check verification status (inspectors bypass verification for workflow submissions)
$currentRole = $_SESSION['role'] ?? '';
if ($currentRole !== 'inspector' && !$database->isUserVerified($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your account is not verified. Please upload a valid ID in your Profile and wait for admin approval before submitting service requests.']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
    exit();
}

// Backfill missing identity/contact fields from the logged-in user profile
$userProfile = $database->getUserById($_SESSION['user_id']);
if (empty($input['full_name'])) {
    $input['full_name'] = trim(((string) ($userProfile['first_name'] ?? '')) . ' ' . ((string) ($userProfile['last_name'] ?? '')));
}
if (empty($input['email'])) {
    $input['email'] = (string) ($userProfile['email'] ?? '');
}
if (empty($input['phone'])) {
    $input['phone'] = (string) ($userProfile['phone'] ?? '');
}
if (empty($input['address'])) {
    $input['address'] = (string) ($userProfile['address'] ?? '');
}

// Normalize whitespace on basic identity fields
$input['full_name'] = trim((string) ($input['full_name'] ?? ''));
$input['email'] = trim((string) ($input['email'] ?? ''));
$input['phone'] = trim((string) ($input['phone'] ?? ''));
$input['address'] = trim((string) ($input['address'] ?? ''));
// Fallback: if full_name still empty, use email as identifier
if ($input['full_name'] === '' && $input['email'] !== '') {
    $input['full_name'] = $input['email'];
}

// Validate required fields (service_details becomes optional; we derive if missing). Phone/address may be blank.
$required_fields = ['service_type', 'email'];
// Collect and report missing required fields (for easier troubleshooting)
$missing = [];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || trim((string) $input[$field]) === '') {
        $missing[] = $field;
    }
}
if (!empty($missing)) {
    http_response_code(422);
    $resp = ['success' => false, 'message' => 'Please fill in all required fields.'];
    if ($DEBUG) {
        $resp['debug'] = ['missing' => $missing];
    }
    echo json_encode($resp);
    exit();
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    $resp = ['success' => false, 'message' => 'Please enter a valid email address.'];
    if ($DEBUG) {
        $resp['debug'] = ['email' => $input['email']];
    }
    echo json_encode($resp);
    exit();
}

// Prepare service data and merge dynamic fields into details
$base_fields = [
    'service_type',
    'full_name',
    'email',
    'phone',
    'address',
    'service_details',
    'preferred_date',
    'urgency',
    // allow metadata and structured fields to pass through for specialized services
    'appType',
    'app_type',
    'industry',
    'subIndustry',
    'sub_industry',
    'businessLine',
    'business_line',
    'establishment_name',
    'establishment_address',
    'owner_name',
    'mayor_permit',
    'total_employees',
    'employees_with_health_cert',
    'employees_without_health_cert',
    'ppe_personnel'
];

// Extract additional dynamic fields & Handle generic file uploads
$extra_pairs = [];
$uploadDir = __DIR__ . '/uploads/general/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

foreach ($input as $key => $value) {
    if (str_ends_with($key, '_base64') && !empty($value)) {
        $fieldName = str_replace('_base64', '', $key);
        $originalName = $input[$fieldName . '_name'] ?? ($fieldName . '.png');
        $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'png';
        $safeFileName = 'req_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $savePath = $uploadDir . $safeFileName;

        if (file_put_contents($savePath, base64_decode($value))) {
            $filePath = 'uploads/general/' . $safeFileName;
            $input[$fieldName] = $filePath; // Put path back into input so it's picked up by labeler
        }
    }
}

foreach ($input as $key => $value) {
    // Skip base fields and base64/type/name metadata
    if (
        !in_array($key, $base_fields, true) &&
        !str_ends_with($key, '_base64') &&
        !str_ends_with($key, '_name') &&
        !str_ends_with($key, '_type') &&
        $value !== '' && $value !== null
    ) {

        $label = ucwords(str_replace(['_', '-'], ' ', $key));
        $valStr = (is_array($value) ? implode(', ', $value) : trim((string) $value));
        $extra_pairs[] = "$label: " . $valStr;
    }
}

$details = trim((string) ($input['service_details'] ?? ''));
if ($details === '') {
    $details = 'Service request: ' . trim((string) ($input['service_type'] ?? 'Unknown'));
}
if (!empty($extra_pairs)) {
    $details .= (strlen($details) ? "\n\n" : '') . "Additional Information:\n- " . implode("\n- ", $extra_pairs);
}

// Check for 'Free' keywords in input details to auto-set payment status
$isFreeService = false;
$freeKeywords = ['(Free)', '(Free - Scheduled)', '(Residential Use)'];
foreach (['inspection_scope', 'customer_type', 'clearance_category'] as $key) {
    if (!empty($input[$key])) {
        foreach ($freeKeywords as $chk) {
            if (stripos($input[$key], $chk) !== false) {
                $isFreeService = true;
                break 2;
            }
        }
    }
}

$paymentStatus = 'unpaid';
if ($isFreeService) {
    $paymentStatus = 'paid'; // Free services are considered "paid/settled"
} elseif (!empty($input['payment_receipt'])) {
    $paymentStatus = 'for_verification';
}

// Prepare final payload for DB
$serviceData = [
    'user_id' => $_SESSION['user_id'],
    'service_type' => trim((string) $input['service_type']),
    'full_name' => trim((string) $input['full_name']),
    'email' => trim((string) $input['email']),
    'phone' => trim((string) $input['phone']),
    'address' => trim((string) $input['address']),
    'service_details' => $details,
    'preferred_date' => !empty($input['preferred_date']) ? $input['preferred_date'] : null,
    'urgency' => !empty($input['urgency']) ? $input['urgency'] : 'medium',
    'payment_status' => $paymentStatus
];

// Create record(s)
try {
    $svcType = trim((string) $input['service_type']);

    // New process: Sanitary Permit does NOT create a general service_request entry
    if ($svcType === 'business-permit') {
        $applicationId = null;
        $spaData = [
            'service_request_id' => null, // decoupled from service_requests
            'user_id' => (int) $_SESSION['user_id'],
            'app_type' => $input['app_type'] ?? $input['appType'] ?? null,
            'industry' => $input['industry'] ?? null,
            'sub_industry' => $input['sub_industry'] ?? $input['subIndustry'] ?? null,
            'business_line' => $input['business_line'] ?? $input['businessLine'] ?? null,
            'establishment_name' => $input['establishment_name'] ?? '',
            'establishment_address' => $input['establishment_address'] ?? null,
            'owner_name' => $input['owner_name'] ?? null,
            'mayor_permit' => $input['mayor_permit'] ?? null,
            'total_employees' => $input['total_employees'] ?? null,
            'employees_with_health_cert' => $input['employees_with_health_cert'] ?? null,
            'employees_without_health_cert' => $input['employees_without_health_cert'] ?? null,
            'ppe_personnel' => $input['ppe_personnel'] ?? null,
            'status' => 'pending'
        ];
        $applicationId = $database->createSanitaryPermitApplication($spaData);

        if ($applicationId) {
            // Auto-complete Step 1: form_filing
            $database->createSanitaryPermitStep([
                'application_id' => (int) $applicationId,
                'user_id' => (int) $_SESSION['user_id'],
                'step' => 'form_filing',
                'status' => 'completed',
                'details' => json_encode(['filed_date' => date('Y-m-d'), 'notes' => 'Initial application submitted.'])
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Application created. Proceed to Step 2: Submission of Requirements.',
                'application_id' => (int) $applicationId
            ]);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create sanitary permit application.']);
            exit();
        }
    }

    if ($svcType === 'child-registration') {
        $childName = trim((string) ($input['child_name'] ?? ''));
        if ($childName === '') {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => "Child's Full Name is required."]);
            exit();
        }

        $nameParts = explode(' ', $childName, 2);
        $depData = [
            'first_name' => $nameParts[0],
            'last_name' => $nameParts[1] ?? ($nameParts[0]), // Fallback if only one name provided
            'date_of_birth' => $input['date_of_birth'] ?? '',
            'place_of_birth' => $input['birth_place'] ?? null,
            'gender' => strtolower($input['gender'] ?? 'other'),
            'relationship' => $input['relationship'] ?? null
        ];
        $ownerId = $_SESSION['user_id'];

        // If staff is registering, they can specify a parent/citizen ID or Email
        if (in_array($_SESSION['role'], ['health_worker', 'admin', 'nurse', 'doctor']) && !empty($input['parent_id'])) {
            $identifier = trim($input['parent_id']);
            $parent = null;

            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                $parent = $database->getUserByEmail($identifier);
            } else if (is_numeric($identifier)) {
                $parent = $database->getUserById((int) $identifier);
            }

            if (!$parent || $parent['role'] !== 'citizen') {
                http_response_code(422);
                echo json_encode(['success' => false, 'message' => "Invalid Parent identifier: Citizen account not found."]);
                exit();
            }
            $ownerId = $parent['id'];
        }

        $ok = $database->addDependent($ownerId, $depData);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Dependent registered successfully.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to register dependent. Please verify all data formats.']);
            exit();
        }
    }

    if ($svcType === 'nutrition-update') {
        if (!in_array($_SESSION['role'], ['health_worker', 'admin', 'nurse', 'doctor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Only health staff can update nutrition records.']);
            exit();
        }

        if (empty($input['child_id'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Child ID is required.']);
            exit();
        }

        $nutriData = [
            'weight_kg' => ($input['weight'] !== '' && $input['weight'] !== null) ? $input['weight'] : 0,
            'height_cm' => ($input['height'] !== '' && $input['height'] !== null) ? $input['height'] : 0,
            'status' => !empty($input['nutritional_status']) ? $input['nutritional_status'] : 'Normal',
            'visit_date' => !empty($input['assessment_date']) ? $input['assessment_date'] : date('Y-m-d'),
            'next_visit_date' => !empty($input['next_visit']) ? $input['next_visit'] : null,
            'remarks' => !empty($input['remarks']) ? $input['remarks'] : null
        ];
        $ok = $database->addNutritionRecord($input['child_id'], $nutriData);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Nutrition record updated successfully.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update nutrition record.']);
            exit();
        }
    }

    if ($svcType === 'immunization-record') {
        if (!in_array($_SESSION['role'], ['health_worker', 'admin', 'nurse', 'doctor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Only health staff can add immunization records.']);
            exit();
        }

        if (empty($input['child_id'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Child ID is required.']);
            exit();
        }

        $immData = [
            'vaccine_name' => $input['vaccine_name'] ?? '',
            'dose_number' => $input['dose_number'] ?? 1,
            'batch_number' => !empty($input['batch_number']) ? $input['batch_number'] : null,
            'administered_by' => $_SESSION['user_id'],
            'date_administered' => !empty($input['date_administered']) ? $input['date_administered'] : null,
            'date_due' => !empty($input['date_due']) ? $input['date_due'] : null,
            'status' => !empty($input['status']) ? strtolower($input['status']) : 'scheduled',
            'remarks' => !empty($input['remarks']) ? $input['remarks'] : null
        ];
        $ok = $database->addImmunization($input['child_id'], $immData);
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Immunization record added successfully.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to add immunization record.']);
            exit();
        }
    }

    if ($svcType === 'edit-immunization' || $svcType === 'edit-nutrition') {
        if (!in_array($_SESSION['role'], ['health_worker', 'admin', 'nurse', 'doctor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized. Only health staff can edit medical records.']);
            exit();
        }

        $recordId = $input['record_id'] ?? null;
        if (!$recordId) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Record ID is required for editing.']);
            exit();
        }

        if ($svcType === 'edit-immunization') {
            $updateData = [
                'vaccine_name' => !empty($input['vaccine_name']) ? $input['vaccine_name'] : null,
                'dose_number' => !empty($input['dose_number']) ? $input['dose_number'] : null,
                'batch_number' => !empty($input['batch_number']) ? $input['batch_number'] : null,
                'date_administered' => !empty($input['date_administered']) ? $input['date_administered'] : null,
                'date_due' => !empty($input['date_due']) ? $input['date_due'] : null,
                'status' => !empty($input['status']) ? strtolower($input['status']) : null,
                'remarks' => !empty($input['remarks']) ? $input['remarks'] : null
            ];
            $ok = $database->updateImmunization($recordId, array_filter($updateData, function ($v) {
                return !is_null($v);
            }));
        } else {
            $updateData = [
                'weight_kg' => ($input['weight'] !== '' && $input['weight'] !== null) ? $input['weight'] : null,
                'height_cm' => ($input['height'] !== '' && $input['height'] !== null) ? $input['height'] : null,
                'status' => !empty($input['nutritional_status']) ? $input['nutritional_status'] : null,
                'visit_date' => !empty($input['assessment_date']) ? $input['assessment_date'] : null,
                'next_visit_date' => !empty($input['next_visit']) ? $input['next_visit'] : null,
                'remarks' => !empty($input['remarks']) ? $input['remarks'] : null
            ];
            $ok = $database->updateNutritionRecord($recordId, array_filter($updateData, function ($v) {
                return !is_null($v);
            }));
        }

        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Record updated successfully.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update record.']);
            exit();
        }
    }

    if ($svcType === 'delete-immunization' || $svcType === 'delete-nutrition') {
        if (!in_array($_SESSION['role'], ['health_worker', 'admin', 'nurse', 'doctor'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            exit();
        }

        $recordId = $input['record_id'] ?? null;
        if (!$recordId) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Record ID is required for deletion.']);
            exit();
        }

        if ($svcType === 'delete-immunization') {
            $ok = $database->deleteImmunization($recordId);
        } else {
            $ok = $database->deleteNutritionRecord($recordId);
        }

        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete record.']);
            exit();
        }
    }

    if ($svcType === 'edit-dependent') {
        if (!in_array($_SESSION['role'], ['health_worker', 'admin', 'nurse', 'doctor', 'citizen'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
            exit();
        }
        $recordId = $input['record_id'] ?? null;
        if (!$recordId) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Record ID is required.']);
            exit();
        }
        $updateData = [
            'first_name' => $input['first_name'] ?? null,
            'last_name' => $input['last_name'] ?? null,
            'date_of_birth' => $input['date_of_birth'] ?? null,
            'place_of_birth' => $input['place_of_birth'] ?? null,
            'gender' => $input['gender'] ?? null,
            'relationship' => $input['relationship'] ?? null,
            'fic_status' => $input['fic_status'] ?? null
        ];
        $ok = $database->updateDependent($recordId, array_filter($updateData, function ($v) {
            return !is_null($v);
        }));
        if ($ok) {
            echo json_encode(['success' => true, 'message' => 'Info updated successfully.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update info.']);
            exit();
        }
    }

    if ($svcType === 'sanitation-workflow') {
        $workflowStep = isset($input['workflow_step']) ? strtolower(trim((string) $input['workflow_step'])) : null;
        if (!$workflowStep) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing workflow_step.']);
            exit();
        }
        if (empty($input['application_id'])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Missing application_id for workflow step.']);
            exit();
        }
        $allowedStepFields = [
            'form_filing' => ['notes'],
            'submission' => ['business_permit_image', 'permit_receipt_image', 'health_certificates_image', 'occupancy_permit_image', 'water_analysis_image', 'pest_control_image', 'notes'],
            'payment' => ['or_number', 'amount', 'payment_date', 'payment_method', 'payment_receipt_image', 'notes'],
            'inspection' => ['preferred_date', 'scheduled_date', 'inspector_name', 'result', 'findings'],
            'issuance' => ['permit_no', 'issued_date', 'expiry_date', 'remarks'],
        ];
        $details = [];
        if (isset($allowedStepFields[$workflowStep])) {
            foreach ($allowedStepFields[$workflowStep] as $k) {
                if (array_key_exists($k, $input)) {
                    $details[$k] = is_string($input[$k]) ? trim((string) $input[$k]) : $input[$k];
                }
            }
        }

        // Handle file uploads (base64)
        if ($workflowStep === 'submission' || $workflowStep === 'payment') {
            $uploadDir = __DIR__ . '/uploads/sanitation/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileFields = [];
            if ($workflowStep === 'submission') {
                $fileFields = ['business_permit_image', 'permit_receipt_image', 'health_certificates_image', 'occupancy_permit_image', 'water_analysis_image', 'pest_control_image'];
            } else {
                $fileFields = ['payment_receipt_image'];
            }

            foreach ($fileFields as $f) {
                $base64Key = $f . '_base64';
                if (!empty($input[$base64Key])) {
                    try {
                        $base64Data = $input[$base64Key];
                        $originalName = $input[($f . '_name')] ?? ($f . '.png');
                        $extension = pathinfo($originalName, PATHINFO_EXTENSION) ?: 'png';
                        $safeFileName = 'app_' . ((int) $input['application_id']) . '_' . $f . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                        $savePath = $uploadDir . $safeFileName;

                        if (file_put_contents($savePath, base64_decode($base64Data))) {
                            $filePath = 'uploads/sanitation/' . $safeFileName;
                            $details[$f] = $filePath;

                            // Also insert into dedicated documents table for more structured tracking
                            try {
                                $db = $database->getConnection();
                                $insDoc = $db->prepare("INSERT INTO sanitary_permit_documents (application_id, document_type, file_path, status) VALUES (:app, :type, :path, 'pending')");
                                $insDoc->execute([
                                    ':app' => (int) $input['application_id'],
                                    ':type' => $f,
                                    ':path' => $filePath
                                ]);
                            } catch (Throwable $dbErr) {
                                error_log("Document table insert error for $f: " . $dbErr->getMessage());
                            }
                        }
                    } catch (Throwable $fileErr) {
                        error_log("File upload error for $f: " . $fileErr->getMessage());
                    }
                }
            }
        }
        // Auto-populate inspector name for inspection step when submitted by an inspector
        if ($workflowStep === 'inspection') {
            try {
                $inspector = $database->getUserById((int) ($_SESSION['user_id'] ?? 0));
                $inspector_name = trim(((string) ($inspector['first_name'] ?? '')) . ' ' . ((string) ($inspector['last_name'] ?? '')));
                if ($inspector_name !== '') {
                    $details['inspector_name'] = $inspector_name;
                }
            } catch (Throwable $e) { /* ignore */
            }
        }
        // Upsert for inspection step to avoid duplicates
        $finalStatus = isset($input['status']) ? (string) $input['status'] : 'pending';
        if ($workflowStep === 'inspection') {
            try {
                $sel = $db->prepare("SELECT id FROM sanitary_permit_steps WHERE application_id = :app AND step = 'inspection' ORDER BY id DESC LIMIT 1");
                $sel->execute([':app' => (int) $input['application_id']]);
                $existing = $sel->fetch(PDO::FETCH_ASSOC);
                if ($existing && !empty($existing['id'])) {
                    // Merge with existing details to preserve assignment fields
                    $cur = $db->prepare("SELECT details FROM sanitary_permit_steps WHERE id = :id LIMIT 1");
                    $cur->execute([':id' => (int) $existing['id']]);
                    $curRow = $cur->fetch(PDO::FETCH_ASSOC);
                    $existingDetails = [];
                    if ($curRow && isset($curRow['details']) && $curRow['details'] !== null && $curRow['details'] !== '') {
                        $tmp = json_decode($curRow['details'], true);
                        if (is_array($tmp)) {
                            $existingDetails = $tmp;
                        }
                    }
                    foreach (['assigned_inspector_id', 'assigned_inspector_name', 'assigned_by_admin_id', 'assigned_at'] as $k) {
                        if (array_key_exists($k, $existingDetails) && !array_key_exists($k, $details)) {
                            $details[$k] = $existingDetails[$k];
                        }
                    }
                    $upd = $db->prepare("UPDATE sanitary_permit_steps SET user_id = :uid, status = :st, details = :d, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
                    $upd->execute([
                        ':uid' => (int) $_SESSION['user_id'],
                        ':st' => $finalStatus,
                        ':d' => json_encode($details),
                        ':id' => (int) $existing['id']
                    ]);
                } else {
                    $database->createSanitaryPermitStep([
                        'application_id' => (int) $input['application_id'],
                        'user_id' => (int) $_SESSION['user_id'],
                        'step' => $workflowStep,
                        'status' => $finalStatus,
                        'details' => !empty($details) ? json_encode($details) : null,
                    ]);
                }
            } catch (Throwable $e) {
                // fallback to insert if upsert fails
                $database->createSanitaryPermitStep([
                    'application_id' => (int) $input['application_id'],
                    'user_id' => (int) $_SESSION['user_id'],
                    'step' => $workflowStep,
                    'status' => $finalStatus,
                    'details' => !empty($details) ? json_encode($details) : null,
                ]);
            }
        } else {
            $database->createSanitaryPermitStep([
                'application_id' => (int) $input['application_id'],
                'user_id' => (int) $_SESSION['user_id'],
                'step' => $workflowStep,
                'status' => $finalStatus,
                'details' => !empty($details) ? json_encode($details) : null,
            ]);
        }

        // Auto-progression: If current step is completed, create the next step
        if ($finalStatus === 'completed') {
            $nextStep = null;
            $remarks = '';

            if ($workflowStep === 'inspection') {
                $nextStep = 'issuance';
                $remarks = 'Inspection completed and passed. Permit being prepared.';
            } else if ($workflowStep === 'form_filing') {
                $nextStep = 'submission';
                $remarks = 'Form filed. Ready for document submission.';
            }

            if ($nextStep) {
                // Check if next step already exists
                $chk = $db->prepare("SELECT id FROM sanitary_permit_steps WHERE application_id = :app AND step = :step LIMIT 1");
                $chk->execute([':app' => (int) $input['application_id'], ':step' => $nextStep]);
                if (!$chk->fetch()) {
                    $database->createSanitaryPermitStep([
                        'application_id' => (int) $input['application_id'],
                        'user_id' => (int) ($_SESSION['user_id'] ?? 0),
                        'step' => $nextStep,
                        'status' => 'pending',
                        'details' => json_encode(['remarks' => $remarks])
                    ]);
                }
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Step saved.',
            'application_id' => (int) $input['application_id']
        ]);
        exit();
    }

    if ($svcType === 'wss-payment') {
        $reqId = $input['request_id'] ?? null;
        if (!$reqId) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Request ID is missing.']);
            exit();
        }

        // Verify request belongs to user (or is admin/staff)
        $db = $database->getConnection();
        $chk = $db->prepare("SELECT id, service_details FROM service_requests WHERE id = ? AND (user_id = ? OR ? IN ('admin', 'staff', 'health_worker'))");
        $chk->execute([$reqId, $_SESSION['user_id'], $_SESSION['role']]);
        $existing = $chk->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Service request not found or access denied.']);
            exit();
        }

        // Prepare new details
        $paymentInfo = [
            'Payment Method' => $input['payment_method'] ?? 'N/A',
            'Amount Paid' => $input['amount'] ?? '0.00',
            'Reference / OR Number' => $input['or_number'] ?? 'N/A',
            'Payment Date' => $input['payment_date'] ?? date('Y-m-d'),
            'Upload Proof of Payment' => $input['payment_receipt'] ?? 'No receipt uploaded'
        ];

        // Merge with existing details (append)
        $newDetails = $existing['service_details'];
        if (strpos($newDetails, 'Payment Information:') === false) {
            $newDetails .= "\n\nPayment Information:";
        }
        foreach ($paymentInfo as $k => $v) {
            // updates if key exists or appends
            // Simple append for now as parsing text is brittle, but let's try to be cleaner:
            // RegEx replace if exists? Complex. Let's just append at end, admin parser handles it.
            $newDetails .= "\n$k: $v";
        }

        $upd = $db->prepare("UPDATE service_requests SET payment_status = 'for_verification', service_details = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        if ($upd->execute([$newDetails, $reqId])) {
            echo json_encode(['success' => true, 'message' => 'Payment submitted for verification.']);
            exit();
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update payment status.']);
            exit();
        }
    }

    // Default: non-sanitation services go through the general service_requests flow
    $serviceRequestId = $database->createServiceRequest($serviceData);
    if ($serviceRequestId) {
        echo json_encode([
            'success' => true,
            'message' => 'Service request submitted successfully! We will contact you soon to discuss your request.',
            'id' => (int) $serviceRequestId
        ]);
    } else {
        http_response_code(500);
        $resp = ['success' => false, 'message' => 'Failed to submit service request. Please try again.'];
        if ($DEBUG) {
            $resp['debug'] = [
                'service_type' => $serviceData['service_type'] ?? null,
                'has_user' => isset($_SESSION['user_id']),
                'input_keys' => array_keys($input)
            ];
        }
        echo json_encode($resp);
    }
} catch (Exception $e) {
    error_log("Service request error: " . $e->getMessage());
    http_response_code(500);
    $resp = ['success' => false, 'message' => 'An error occurred. Please try again later.'];
    if ($DEBUG) {
        $resp['debug'] = ['exception' => $e->getMessage()];
    }
    echo json_encode($resp);
}
?>