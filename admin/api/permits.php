<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('admin');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = strtolower(trim($_GET['action'] ?? $_POST['action'] ?? ''));

function json_error($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

try {
    if ($method === 'GET' && $action === 'list_all_steps') {
        $step = trim((string) ($_GET['step'] ?? ''));
        if (!$step)
            json_error('Step parameter required');

        try {
            $stmt = $db->prepare("SELECT sps.*, u.first_name, u.last_name, spa.establishment_name, spa.owner_name
                                FROM sanitary_permit_steps sps
                                JOIN sanitary_permit_applications spa ON sps.application_id = spa.id
                                JOIN users u ON spa.user_id = u.id
                                WHERE sps.step = :step
                                ORDER BY sps.created_at DESC");
            $stmt->execute([':step' => $step]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $data]);
            exit;
        } catch (Throwable $e) {
            json_error('Failed to list steps: ' . $e->getMessage(), 500);
        }
    }

    if ($method === 'GET' && $action === 'list_inspectors') {
        // List inspectors for assignment
        try {
            $hasStatus = false;
            try {
                $hasStatus = (bool) $db->query("SHOW COLUMNS FROM users LIKE 'status'")->fetch(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
            }
            $sql = "SELECT id, first_name, last_name, email" . ($hasStatus ? ", status" : "") . " FROM users WHERE role = 'inspector'" . ($hasStatus ? " AND status <> 'blocked'" : "") . " ORDER BY first_name, last_name";
            $st = $db->query($sql);
            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
            $inspectors = array_map(function ($r) {
                return [
                    'id' => (int) $r['id'],
                    'name' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')),
                    'email' => $r['email'] ?? ''
                ];
            }, $rows ?: []);
            echo json_encode(['success' => true, 'inspectors' => $inspectors]);
            exit;
        } catch (Throwable $e) {
            json_error('Failed to list inspectors', 500);
        }
    }

    if ($method === 'POST' && $action === 'update_step_status') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input))
            $input = [];
        $step_id = (int) ($input['step_id'] ?? 0);
        $status = trim((string) ($input['status'] ?? ''));
        if ($step_id <= 0 || $status === '')
            json_error('Invalid parameters');

        // 1. Update the current step
        $st = $db->prepare("UPDATE sanitary_permit_steps SET status = :st, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $ok = $st->execute([':st' => $status, ':id' => $step_id]);

        // 2. If it was the 'submission' or 'payment' step being completed, trigger the next step
        if ($ok && $status === 'completed') {
            $sel = $db->prepare("SELECT application_id, step FROM sanitary_permit_steps WHERE id = :id LIMIT 1");
            $sel->execute([':id' => $step_id]);
            $row = $sel->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $appId = (int) $row['application_id'];
                $currentStep = $row['step'];
                $nextStep = null;
                $remarks = '';

                if ($currentStep === 'submission') {
                    $nextStep = 'payment';
                    $remarks = 'Documents accepted by admin. Ready for payment.';
                } else if ($currentStep === 'payment') {
                    $nextStep = 'inspection';
                    $remarks = 'Payment verified by admin. Inspection to be scheduled.';
                } else if ($currentStep === 'inspection') {
                    $nextStep = 'issuance';
                    $remarks = 'Inspection completed and passed. Permit being prepared.';
                }

                if ($nextStep) {
                    // Check if next step already exists to avoid duplicates
                    $chk = $db->prepare("SELECT id FROM sanitary_permit_steps WHERE application_id = :app AND step = :step LIMIT 1");
                    $chk->execute([':app' => $appId, ':step' => $nextStep]);
                    if (!$chk->fetch()) {
                        $ins = $db->prepare("INSERT INTO sanitary_permit_steps (application_id, user_id, step, status, details) VALUES (:app, :uid, :step, 'pending', :d)");
                        $ins->execute([
                            ':app' => $appId,
                            ':uid' => (int) ($_SESSION['user_id'] ?? 0),
                            ':step' => $nextStep,
                            ':d' => json_encode(['remarks' => $remarks])
                        ]);
                    }
                }
            }
        }

        echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Status updated' : 'Failed to update status']);
        exit;
    }

    if ($method === 'POST' && $action === 'assign_inspector') {
        // Accept JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input))
            $input = [];
        $application_id = (int) ($input['application_id'] ?? 0);
        $inspector_id = (int) ($input['inspector_id'] ?? 0);
        if ($application_id <= 0 || $inspector_id <= 0)
            json_error('Invalid application or inspector');

        // Validate inspector exists
        $st = $db->prepare("SELECT id, first_name, last_name FROM users WHERE id = :id AND role = 'inspector' LIMIT 1");
        $st->execute([':id' => $inspector_id]);
        $insp = $st->fetch(PDO::FETCH_ASSOC);
        if (!$insp)
            json_error('Inspector not found', 404);
        $inspName = trim(($insp['first_name'] ?? '') . ' ' . ($insp['last_name'] ?? ''));

        // Ensure application exists
        $chk = $db->prepare("SELECT id FROM sanitary_permit_applications WHERE id = :id LIMIT 1");
        $chk->execute([':id' => $application_id]);
        if (!$chk->fetch(PDO::FETCH_ASSOC))
            json_error('Application not found', 404);

        // Upsert inspection step assignment (use the LATEST inspection step, merge details)
        $sel = $db->prepare("SELECT id, details FROM sanitary_permit_steps WHERE application_id = :app AND step = 'inspection' ORDER BY id DESC LIMIT 1");
        $sel->execute([':app' => $application_id]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);
        $merged = [
            'assigned_inspector_id' => $inspector_id,
            'assigned_inspector_name' => $inspName,
            'assigned_by_admin_id' => (int) ($_SESSION['user_id'] ?? 0),
            'assigned_at' => date('c')
        ];
        if ($row && isset($row['details']) && $row['details'] !== null && $row['details'] !== '') {
            $cur = json_decode($row['details'], true);
            if (is_array($cur)) {
                $merged = array_merge($cur, $merged);
            }
        }
        $details = json_encode($merged);
        if ($row) {
            $upd = $db->prepare("UPDATE sanitary_permit_steps SET user_id = :uid, status = 'assigned', details = :d, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $ok = $upd->execute([':uid' => $inspector_id, ':d' => $details, ':id' => (int) $row['id']]);
        } else {
            $ins = $db->prepare("INSERT INTO sanitary_permit_steps (application_id, user_id, step, status, details) VALUES (:app, :uid, 'inspection', 'assigned', :d)");
            $ok = $ins->execute([':app' => $application_id, ':uid' => $inspector_id, ':d' => $details]);
        }

        // Optionally bump application status
        try {
            $db->prepare("UPDATE sanitary_permit_applications SET status = 'in_progress', updated_at = CURRENT_TIMESTAMP WHERE id = :id")
                ->execute([':id' => $application_id]);
        } catch (Throwable $e) {
        }

        echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Inspector assigned' : 'Failed to assign']);
        exit;
    }

    if ($method === 'POST' && $action === 'update_doc_status') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input))
            $input = [];
        $appId = (int) ($input['application_id'] ?? 0);
        $type = $input['doc_type'] ?? '';
        $status = $input['status'] ?? 'pending';

        if ($appId <= 0 || !$type)
            json_error('Invalid parameters');

        // Check if doc exists
        $chk = $db->prepare("SELECT id FROM sanitary_permit_documents WHERE application_id = :app AND document_type = :type LIMIT 1");
        $chk->execute([':app' => $appId, ':type' => $type]);
        $exists = $chk->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $up = $db->prepare("UPDATE sanitary_permit_documents SET status = :st, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $up->execute([':st' => $status, ':id' => $exists['id']]);
        } else {
            // If it doesn't exist in the docs table (migrated from JSON), insert it. 
            // We need the file path, but for status tracking, maybe path is optional if we trust the JSON field?
            // Ideally, we should sync. For now, let's insert with a placeholder path or try to find it from JSON.
            // For robustness, let's just insert with status.
            $ins = $db->prepare("INSERT INTO sanitary_permit_documents (application_id, document_type, status, file_path) VALUES (:app, :type, :st, '')");
            $ins->execute([':app' => $appId, ':type' => $type, ':st' => $status]);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    if ($method === 'POST' && $action === 'final_decision') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input))
            $input = [];
        $appId = (int) ($input['application_id'] ?? 0);
        $status = $input['status'] ?? ''; // 'completed' or 'rejected'

        if ($appId <= 0 || !in_array($status, ['completed', 'rejected'])) {
            json_error('Invalid parameters');
        }

        // Update application status
        $appStatus = ($status === 'completed') ? 'completed' : 'cancelled';
        $upd = $db->prepare("UPDATE sanitary_permit_applications SET status = :st, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $upd->execute([':st' => $appStatus, ':id' => $appId]);

        // Upsert issuance step
        $details = json_encode([
            'decision' => $status,
            'decided_by_admin_id' => (int) ($_SESSION['user_id'] ?? 0),
            'decided_at' => date('c'),
            'remarks' => ($status === 'completed') ? 'Sanitation Clearance Approved - Sent to Permit & Licensing' : 'Sanitation Clearance Rejected'
        ]);

        $sel = $db->prepare("SELECT id FROM sanitary_permit_steps WHERE application_id = :app AND step = 'issuance' ORDER BY id DESC LIMIT 1");
        $sel->execute([':app' => $appId]);
        $row = $sel->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $updStep = $db->prepare("UPDATE sanitary_permit_steps SET status = :st, details = :d, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
            $ok = $updStep->execute([':st' => $status, ':d' => $details, ':id' => (int) $row['id']]);
        } else {
            $insStep = $db->prepare("INSERT INTO sanitary_permit_steps (application_id, user_id, step, status, details) VALUES (:app, :uid, 'issuance', :st, :d)");
            $ok = $insStep->execute([':app' => $appId, ':uid' => (int) ($_SESSION['user_id'] ?? 0), ':st' => $status, ':d' => $details]);
        }

        echo json_encode(['success' => (bool) $ok, 'message' => $ok ? 'Decision recorded' : 'Failed to record decision']);
        exit;
    }

    if ($method === 'GET' && $action === 'get_doc_statuses') {
        $appId = (int) ($_GET['app_id'] ?? 0);
        if ($appId <= 0)
            json_error('Invalid ID');

        $st = $db->prepare("SELECT document_type, status FROM sanitary_permit_documents WHERE application_id = :app");
        $st->execute([':app' => $appId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $r) {
            $data[$r['document_type']] = $r['status'];
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    json_error('Unsupported action', 404);
} catch (Throwable $e) {
    json_error('Server error', 500);
}
