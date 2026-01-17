<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('inspector');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
  http_response_code(405);
  echo json_encode(['success'=>false,'message'=>'Invalid method']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }
$action = $input['action'] ?? '';

function jerr($msg, $code=400){ http_response_code($code); echo json_encode(['success'=>false,'message'=>$msg]); exit; }

$allowedTypes = ['business-permit','health-inspection'];

try {
  switch ($action) {
    case 'update': {
      $id = (int)($input['id'] ?? 0);
      if ($id <= 0) jerr('Invalid ID');
      $fields = [];
      $params = [':id'=>$id];
      $allowed = ['status','urgency','preferred_date','address','service_details','full_name','email','phone'];
      foreach ($allowed as $k) {
        if (array_key_exists($k, $input)) { $fields[] = "$k = :$k"; $params[":$k"] = $input[$k]; }
      }
      if (empty($fields)) jerr('No fields to update');
      $sql = 'UPDATE service_requests SET '.implode(', ', $fields).', updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL';
      $stmt = $db->prepare($sql);
      $ok = $stmt->execute($params);
      echo json_encode(['success'=>$ok]);
      break;
    }
    case 'delete': {
      $id = (int)($input['id'] ?? 0);
      if ($id <= 0) jerr('Invalid ID');
      $stmt = $db->prepare('UPDATE service_requests SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NULL');
      $ok = $stmt->execute([':id'=>$id]);
      echo json_encode(['success'=>$ok]);
      break;
    }
    case 'create': {
      $service_type = trim((string)($input['service_type'] ?? ''));
      if (!in_array($service_type, $allowedTypes, true)) jerr('Invalid service type');
      $user_id = (int)($input['user_id'] ?? 0);
      $user_email = trim((string)($input['user_email'] ?? ''));
      if ($user_id <= 0) {
        if ($user_email === '' || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) jerr('Provide valid user_email or user_id');
        $u = $db->prepare('SELECT id, first_name, last_name, email FROM users WHERE email = :e');
        $u->execute([':e'=>$user_email]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
          // Walk-in: auto-create citizen user
          $full_name = trim((string)($input['full_name'] ?? ''));
          $first_name = $full_name !== '' ? preg_split('/\s+/', $full_name)[0] : 'Citizen';
          $last_name = '';
          if ($full_name !== '') {
            $parts = preg_split('/\s+/', $full_name, -1, PREG_SPLIT_NO_EMPTY);
            if (count($parts) >= 2) { $first_name = array_shift($parts); $last_name = implode(' ', $parts); }
            else { $last_name = 'Walk-in'; }
          } else { $last_name = 'Walk-in'; }
          try { $randPass = bin2hex(random_bytes(8)); } catch (Throwable $e) { $randPass = substr(sha1(uniqid('', true)), 0, 16); }
          $newId = $database->registerUser($first_name, $last_name, $user_email, $randPass, 'citizen');
          if (!$newId) jerr('Failed to create citizen account');
          $phone = trim((string)($input['phone'] ?? '')); $address = trim((string)($input['address'] ?? ''));
          if ($phone !== '' || $address !== '') { $database->updateUser((int)$newId, array_filter(['phone'=>$phone, 'address'=>$address], fn($v)=>$v!=='')); }
          $user_id = (int)$newId;
        } else { $user_id = (int)$row['id']; }
      }
      // Required core fields
      $full_name = trim((string)($input['full_name'] ?? ''));
      $email = trim((string)($input['email'] ?? ''));
      $phone = trim((string)($input['phone'] ?? ''));
      $address = trim((string)($input['address'] ?? ''));
      $service_details = trim((string)($input['service_details'] ?? ''));
      if ($full_name===''||$email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||$phone===''||$address===''||$service_details==='') jerr('Missing required fields');
      $preferred_date = !empty($input['preferred_date']) ? $input['preferred_date'] : null;
      $urgency = !empty($input['urgency']) ? $input['urgency'] : 'medium';
      $stmt = $db->prepare('INSERT INTO service_requests (user_id, service_type, full_name, email, phone, address, service_details, preferred_date, urgency) VALUES (:uid,:stype,:fname,:email,:phone,:addr,:details,:pdate,:urg)');
      $ok = $stmt->execute([
        ':uid'=>$user_id,
        ':stype'=>$service_type,
        ':fname'=>$full_name,
        ':email'=>$email,
        ':phone'=>$phone,
        ':addr'=>$address,
        ':details'=>$service_details,
        ':pdate'=>$preferred_date,
        ':urg'=>$urgency
      ]);
      echo json_encode(['success'=>$ok, 'id'=>$ok ? (int)$db->lastInsertId() : null]);
      break;
    }
    default:
      jerr('Unknown action', 404);
  }
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error','error'=>$e->getMessage()]);
}
