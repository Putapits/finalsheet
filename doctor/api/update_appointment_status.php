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
  $status = isset($payload['status']) ? strtolower(trim($payload['status'])) : '';
  $allowed = ['pending','confirmed','completed','cancelled'];
  if ($id <= 0 || !in_array($status, $allowed, true)) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Invalid id or status']);
    exit;
  }
  $stmt = $db->prepare('UPDATE appointments SET status = :s, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
  $ok = $stmt->execute([':s'=>$status, ':id'=>$id]);
  if ($ok && $stmt->rowCount() > 0) {
    echo json_encode(['success'=>true,'id'=>$id,'status'=>$status]);
  } else {
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Appointment not found']);
  }
} catch (Throwable $e) {
  error_log('doctor/update_appointment_status error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
