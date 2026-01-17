<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('admin');
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    $payload = json_decode(file_get_contents('php://input'), true);
    $id = isset($payload['id']) ? (int)$payload['id'] : 0;
    $status = isset($payload['status']) ? strtolower(trim($payload['status'])) : '';

    $allowed = ['pending','in_progress','completed','cancelled'];
    if ($id <= 0 || !in_array($status, $allowed, true)) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Invalid id or status']);
        exit;
    }

    $stmt = $db->prepare('UPDATE service_requests SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
    $ok = $stmt->execute([':status' => $status, ':id' => $id]);

    if ($ok && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Service request status updated', 'id' => $id, 'status' => $status]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Service request not found']);
    }
} catch (Throwable $e) {
    error_log('update_service_request_status error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
