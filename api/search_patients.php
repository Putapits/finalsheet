<?php
require_once '../include/database.php';
startSecureSession();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'health_worker', 'nurse', 'doctor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 1) {
    echo json_encode(['success' => true, 'results' => []]);
    exit();
}

$results = $database->searchPatients($query);

echo json_encode(['success' => true, 'results' => $results]);
?>