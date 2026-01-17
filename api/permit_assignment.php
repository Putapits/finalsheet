<?php
require_once __DIR__ . '/../include/database.php';
startSecureSession();

header('Content-Type: application/json');

$application_id = isset($_GET['application_id']) ? (int) $_GET['application_id'] : 0;
if ($application_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid application_id']);
    exit;
}

try {
    // Get the latest inspection step for this application
    $sql = "SELECT details FROM sanitary_permit_steps WHERE application_id = :app AND step = 'inspection' ORDER BY id DESC LIMIT 1";
    $st = $db->prepare($sql);
    $st->execute([':app' => $application_id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    $details = [];
    $name = null;
    $id = null;
    if ($row && isset($row['details']) && $row['details'] !== null && $row['details'] !== '') {
        $details = json_decode($row['details'], true);
        if (is_array($details)) {
            // Try common keys
            if (!empty($details['assigned_inspector_name'])) {
                $name = (string) $details['assigned_inspector_name'];
            }
            if (!empty($details['assigned_inspector_id'])) {
                $id = (int) $details['assigned_inspector_id'];
            }
            // Fallback keys if needed
            if (!$name && !empty($details['inspector_name'])) {
                $name = (string) $details['inspector_name'];
            }
            if (!$id && !empty($details['inspector_id'])) {
                $id = (int) $details['inspector_id'];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'application_id' => $application_id,
        'inspector' => ['id' => $id, 'name' => $name],
        'details' => $details
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
