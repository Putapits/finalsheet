<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('doctor');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = [];
}

$apptId = (int) ($input['appointment_id'] ?? 0);
$reqId = (int) ($input['service_request_id'] ?? 0);
$hospitalName = trim((string) ($input['hospital_name'] ?? ''));
$reason = trim((string) ($input['reason'] ?? ''));
$urgency = trim((string) ($input['urgency'] ?? 'Routine'));
$notes = trim((string) ($input['notes'] ?? ''));

if (($apptId <= 0 && $reqId <= 0) || $hospitalName === '' || $reason === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields: source id, hospital_name, reason']);
    exit;
}

try {
    // Ensure table exists (Lazy migration)
    $db->exec("CREATE TABLE IF NOT EXISTS patient_referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT NULL,
    service_request_id INT NULL,
    doctor_id INT NOT NULL,
    hospital_name VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    urgency VARCHAR(50) DEFAULT 'Routine',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
  )");

    // Update schema if needed (for existing table)
    try {
        $db->exec("ALTER TABLE patient_referrals MODIFY appointment_id INT NULL");
    } catch (Exception $e) {
    } // Ignore if already null
    try {
        $db->exec("ALTER TABLE patient_referrals ADD COLUMN service_request_id INT NULL AFTER appointment_id");
        $db->exec("ALTER TABLE patient_referrals ADD FOREIGN KEY (service_request_id) REFERENCES service_requests(id) ON DELETE CASCADE");
    } catch (Exception $e) {
    } // Ignore if column exists

    $db->beginTransaction();

    // Insert Referral
    $stmt = $db->prepare("INSERT INTO patient_referrals (appointment_id, service_request_id, doctor_id, hospital_name, reason, urgency, notes) VALUES (:aid, :rid, :did, :h, :r, :u, :n)");
    $stmt->execute([
        ':aid' => ($apptId > 0 ? $apptId : null),
        ':rid' => ($reqId > 0 ? $reqId : null),
        ':did' => $_SESSION['user_id'],
        ':h' => $hospitalName,
        ':r' => $reason,
        ':u' => $urgency,
        ':n' => ($notes !== '' ? $notes : null)
    ]);

    // Update Status
    if ($apptId > 0) {
        $upd = $db->prepare("UPDATE appointments SET status = 'completed' WHERE id = :id");
        $upd->execute([':id' => $apptId]);
    } elseif ($reqId > 0) {
        $upd = $db->prepare("UPDATE service_requests SET status = 'completed' WHERE id = :id");
        $upd->execute([':id' => $reqId]);
    }

    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Referral created successfully']);

} catch (Throwable $e) {
    if ($db->inTransaction())
        $db->rollBack();
    error_log('Referral error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error', 'error' => $e->getMessage()]);
}
