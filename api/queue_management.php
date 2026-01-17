<?php
require_once '../include/database.php';
startSecureSession();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'health_worker', 'nurse', 'doctor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

$db = $database->getConnection();
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'fetch_queue':
            $sql = "SELECT 'appointment' as type, id, first_name, last_name, appointment_type as detail, status, assigned_to, checked_in_at, created_at, phone, address, health_concerns as description, 'Normal' as urgency, preferred_date 
                    FROM appointments 
                    WHERE (preferred_date = CURRENT_DATE OR status = 'pending') AND deleted_at IS NULL
                    UNION ALL
                    SELECT 'service_request' as type, id, full_name as first_name, '' as last_name, service_type as detail, status, assigned_to, checked_in_at, created_at, phone, address, service_details as description, urgency, preferred_date 
                    FROM service_requests 
                    WHERE (preferred_date = CURRENT_DATE OR status = 'pending') 
                    AND service_type NOT IN ('septic-registration', 'maintenance-service', 'system-inspection', 'wastewater-clearance', 'installation-upgrade')
                    AND deleted_at IS NULL
                    ORDER BY CASE WHEN detail = 'emergency-care' THEN 0 ELSE 1 END ASC, created_at ASC";
            $stmt = $db->query($sql);
            echo json_encode(['success' => true, 'queue' => $stmt->fetchAll()]);
            break;

        case 'fetch_doctors':
            $stmt = $db->query("SELECT id, first_name, last_name, status, profile_picture FROM users WHERE role = 'doctor' AND status = 'active'");
            echo json_encode(['success' => true, 'doctors' => $stmt->fetchAll()]);
            break;

        case 'check_in':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['type'] ?? '';
            $table = ($type === 'appointment') ? 'appointments' : 'service_requests';
            $sql = "UPDATE $table SET status = 'in_progress', checked_in_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        case 'assign':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['type'] ?? '';
            $worker_id = $_POST['worker_id'] ?? $_SESSION['user_id'];
            $table = ($type === 'appointment') ? 'appointments' : 'service_requests';
            $sql = "UPDATE $table SET assigned_to = ? WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$worker_id, $id]);
            echo json_encode(['success' => true]);
            break;

        case 'complete':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['type'] ?? '';
            $table = ($type === 'appointment') ? 'appointments' : 'service_requests';
            $sql = "UPDATE $table SET status = 'completed' WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        case 'assign_doctor':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['type'] ?? '';
            $doctor_id = $_POST['doctor_id'] ?? 0;
            $table = ($type === 'appointment') ? 'appointments' : 'service_requests';
            $sql = "UPDATE $table SET assigned_to = ?, status = 'in_progress', checked_in_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$doctor_id, $id]);
            echo json_encode(['success' => true]);
            break;

        case 'decline_request':
            $id = $_POST['id'] ?? 0;
            $type = $_POST['type'] ?? '';
            $table = ($type === 'appointment') ? 'appointments' : 'service_requests';
            $sql = "UPDATE $table SET status = 'rejected' WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>