<?php
require_once '../../include/database.php';
require_once '../../include/database.php';

startSecureSession();

// Ensure admin/health worker access
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'health_worker', 'inspector'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

/* Helper function for JSON error */
function json_error($msg, $code = 400)
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$db = $database->getConnection();

try {
    // GET: Fetch requests
    if ($method === 'GET' && $action === 'list_requests') {
        $status = $_GET['status'] ?? 'all';
        $pStatus = $_GET['payment_status'] ?? 'all';
        $type = $_GET['type'] ?? 'all';

        // Base query - broad enough to catch all WSS related services
        $sql = "SELECT r.*, u.profile_picture, 
                CONCAT(u.first_name, ' ', u.last_name) as citizen_name,
                CONCAT(insp.first_name, ' ', insp.last_name) as inspector_name 
                FROM service_requests r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN users insp ON r.assigned_inspector_id = insp.id
                WHERE (r.service_type LIKE '%septic%' 
                   OR r.service_type LIKE '%wastewater%' 
                   OR r.service_type LIKE '%desludging%' 
                   OR r.service_type LIKE '%inspection%' 
                   OR r.service_type IN ('maintenance-service', 'system-inspection'))
                AND r.deleted_at IS NULL";

        $params = [];
        if ($status !== 'all') {
            $sql .= " AND r.status = :status";
            $params[':status'] = $status;
        }
        if ($pStatus !== 'all' && $pStatus !== '') {
            $sql .= " AND r.payment_status = :pStatus";
            $params[':pStatus'] = $pStatus;
        }
        if ($type !== 'all' && $type !== '') {
            $sql .= " AND r.service_type = :type";
            $params[':type'] = $type;
        }

        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process details to extract additional fields if JSON or formatted string
        foreach ($rows as &$row) {
            // Helper to parsing the text block service_details if needed, 
            // but front-end can normally display the text directly.
            // If we stored file paths in specific columns, they'd be here. 
            // Since we stored generic files in 'service_details' or separate columns? 
            // process_service_request.php put generic files back into $input and concatenated them into service_details text
            // AND the JS frontend logic for general uploads puts them in JSON body? 
            // Wait, process_service_request.php lines 132-143 handles files and puts the path into $input[$fieldName].
            // Then lines 147-160 puts them into $extra_pairs -> $details text string. 
            // So file paths are embedded in the 'service_details' text column as "Label: path/to/file".

            // We'll let the frontend parse the text or regex it.
        }

        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // GET: List Inspectors
    if ($method === 'GET' && $action === 'list_inspectors') {
        $stmt = $db->prepare("SELECT id, first_name, last_name FROM users WHERE role = 'inspector' AND status = 'active'");
        $stmt->execute();
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // POST: Update Request Status / Review
    if ($method === 'POST' && $action === 'review_request') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = (int) ($input['id'] ?? 0);
        $newStatus = $input['status'] ?? null; // completed, cancelled, in_progress
        $remarks = $input['remarks'] ?? '';
        $paymentAction = $input['payment_action'] ?? null; // 'verify', 'reject'
        $inspectorId = (int) ($input['inspector_id'] ?? 0);

        if ($id <= 0)
            json_error('Invalid ID');

        $updates = [];
        $params = [':id' => $id];

        if ($newStatus) {
            $updates[] = "status = :st";
            $params[':st'] = $newStatus;
        }
        if ($remarks) {
            $updates[] = "status_remarks = :rem";
            $params[':rem'] = $remarks;
        }
        if ($paymentAction === 'verify') {
            $updates[] = "payment_status = 'paid'";
        } else if ($paymentAction === 'reject') {
            $updates[] = "payment_status = 'unpaid'"; // or some rejection status?
        }

        if ($inspectorId > 0) {
            $updates[] = "assigned_inspector_id = :insp";
            $updates[] = "assigned_inspector_at = CURRENT_TIMESTAMP";
            $params[':insp'] = $inspectorId;

            // If just assigning inspector, usually move to in_progress if pending
            if (!$newStatus) {
                // Check current status
                $curr = $db->prepare("SELECT status FROM service_requests WHERE id = :id");
                $curr->execute([':id' => $id]);
                if ($curr->fetchColumn() === 'pending') {
                    $updates[] = "status = 'in_progress'";
                }
            }
        }

        if (empty($updates))
            json_error('No changes requested');

        $updates[] = "updated_at = CURRENT_TIMESTAMP";

        $sql = "UPDATE service_requests SET " . implode(', ', $updates) . " WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'Request updated successfully']);
        exit;
    }

} catch (Exception $e) {
    error_log("WSS API Error: " . $e->getMessage());
    json_error('Server error', 500);
}
?>