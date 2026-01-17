<?php
require_once '../include/database.php';
startSecureSession();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$appId = $input['application_id'] ?? null;

if (!$appId) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit;
}

if ($database->deleteSanitaryPermitApplication($appId, $_SESSION['user_id'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete application']);
}
