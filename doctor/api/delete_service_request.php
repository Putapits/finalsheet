<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('doctor');
header('Content-Type: application/json');

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'message'=>'Method not allowed']);
    exit;
  }
  $payload = json_decode(file_get_contents('php://input'), true);
  $id = isset($payload['id']) ? (int)$payload['id'] : 0;
  if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Invalid id']);
    exit;
  }
  $stmt = $db->prepare('UPDATE service_requests SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL');
  $ok = $stmt->execute([':id'=>$id]);
  if ($ok && $stmt->rowCount() > 0) {
    echo json_encode(['success'=>true,'id'=>$id]);
  } else {
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Service request not found or already deleted']);
  }
} catch (Throwable $e) {
  error_log('doctor/delete_service_request error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
