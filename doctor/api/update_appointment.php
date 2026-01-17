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

  $raw = file_get_contents('php://input');
  $payload = json_decode($raw, true);
  if (!is_array($payload)) { $payload = []; }

  $id = isset($payload['id']) ? (int)$payload['id'] : 0;
  if ($id <= 0) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'Invalid appointment id']);
    exit;
  }

  // Whitelist of editable fields
  $allowedFields = [
    'first_name','middle_name','last_name','email','phone',
    'birth_date','gender','civil_status','address',
    'appointment_type','preferred_date',
    'health_concerns','medical_history','current_medications','allergies',
    'emergency_contact_name','emergency_contact_phone',
    'status'
  ];

  // Basic validations for enums
  $statusAllowed = ['pending','confirmed','completed','cancelled'];
  $genderAllowed = ['male','female','other','prefer-not-to-say'];
  $civilAllowed  = ['single','married','divorced','widowed'];

  $set = [];
  $params = [':id' => $id];

  foreach ($allowedFields as $f) {
    if (array_key_exists($f, $payload)) {
      $v = $payload[$f];
      if (is_string($v)) { $v = trim($v); }

      if ($f === 'status') {
        $v = strtolower((string)$v);
        if (!in_array($v, $statusAllowed, true)) {
          http_response_code(422);
          echo json_encode(['success'=>false,'message'=>'Invalid status value']);
          exit;
        }
      }
      if ($f === 'gender' && $v !== null && $v !== '') {
        $v = strtolower((string)$v);
        if (!in_array($v, $genderAllowed, true)) {
          http_response_code(422);
          echo json_encode(['success'=>false,'message'=>'Invalid gender value']);
          exit;
        }
      }
      if ($f === 'civil_status' && $v !== null && $v !== '') {
        $v = strtolower((string)$v);
        if (!in_array($v, $civilAllowed, true)) {
          http_response_code(422);
          echo json_encode(['success'=>false,'message'=>'Invalid civil status value']);
          exit;
        }
      }

      $set[] = "$f = :$f";
      $params[":".$f] = $v === '' ? null : $v;
    }
  }

  if (empty($set)) {
    http_response_code(422);
    echo json_encode(['success'=>false,'message'=>'No fields to update']);
    exit;
  }

  $sql = 'UPDATE appointments SET ' . implode(', ', $set) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id';
  $stmt = $db->prepare($sql);
  $ok = $stmt->execute($params);

  if ($ok && $stmt->rowCount() >= 0) {
    // Optional: log audit
    if (isset($_SESSION['user_id'])) {
      $database->logAudit((int)$_SESSION['user_id'], 'doctor', 'update_appointment', json_encode(['id'=>$id, 'fields'=>array_keys($params)]));
    }
    echo json_encode(['success'=>true,'id'=>$id]);
  } else {
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Appointment not found']);
  }
} catch (Throwable $e) {
  error_log('doctor/update_appointment error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
