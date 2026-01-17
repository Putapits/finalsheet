<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('nurse');

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

try {
  switch ($action) {
    case 'update': {
      $id = isset($input['id']) ? (int)$input['id'] : 0;
      if ($id <= 0) jerr('Invalid appointment id');
      $allowedFields = [
        'first_name','middle_name','last_name','email','phone',
        'birth_date','gender','civil_status','address',
        'appointment_type','preferred_date',
        'health_concerns','medical_history','current_medications','allergies',
        'emergency_contact_name','emergency_contact_phone',
        'status'
      ];
      $statusAllowed = ['pending','confirmed','completed','cancelled'];
      $genderAllowed = ['male','female','other','prefer-not-to-say'];
      $civilAllowed  = ['single','married','divorced','widowed'];

      $set = [];
      $params = [':id'=>$id];
      foreach ($allowedFields as $f) {
        if (array_key_exists($f, $input)) {
          $v = $input[$f];
          if (is_string($v)) { $v = trim($v); }
          if ($f === 'status' && $v !== null && $v !== '') {
            $v = strtolower((string)$v);
            if (!in_array($v, $statusAllowed, true)) jerr('Invalid status');
          }
          if ($f === 'gender' && $v !== null && $v !== '') {
            $v = strtolower((string)$v);
            if (!in_array($v, $genderAllowed, true)) jerr('Invalid gender');
          }
          if ($f === 'civil_status' && $v !== null && $v !== '') {
            $v = strtolower((string)$v);
            if (!in_array($v, $civilAllowed, true)) jerr('Invalid civil status');
          }
          $set[] = "$f = :$f";
          $params[":$f"] = ($v === '' ? null : $v);
        }
      }
      if (empty($set)) jerr('No fields to update');
      $sql = 'UPDATE appointments SET ' . implode(', ', $set) . ', updated_at = CURRENT_TIMESTAMP WHERE id = :id';
      $stmt = $db->prepare($sql);
      $ok = $stmt->execute($params);
      echo json_encode(['success'=>$ok, 'id'=>$id]);
      break;
    }
    case 'create': {
      $user_id = (int)($input['user_id'] ?? 0);
      $user_email = trim((string)($input['user_email'] ?? ''));
      if ($user_id <= 0) {
        if ($user_email === '' || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) jerr('Provide valid user_email or user_id');
        $u = $db->prepare('SELECT id, first_name, last_name, email FROM users WHERE email = :e');
        $u->execute([':e'=>$user_email]);
        $row = $u->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
          // Walk-in: auto-create citizen user
          $first_name = trim((string)($input['first_name'] ?? 'Citizen'));
          $last_name = trim((string)($input['last_name'] ?? 'Walk-in'));
          try { $randPass = bin2hex(random_bytes(8)); } catch (Throwable $e) { $randPass = substr(sha1(uniqid('', true)), 0, 16); }
          $newId = $database->registerUser($first_name, $last_name, $user_email, $randPass, 'citizen');
          if (!$newId) jerr('Failed to create citizen account');
          $phone = trim((string)($input['phone'] ?? '')); $address = trim((string)($input['address'] ?? ''));
          if ($phone !== '' || $address !== '') { $database->updateUser((int)$newId, array_filter(['phone'=>$phone, 'address'=>$address], fn($v)=>$v!=='')); }
          $user_id = (int)$newId;
        } else { $user_id = (int)$row['id']; }
      }

      // Required fields (per appointments schema)
      $first_name = trim((string)($input['first_name'] ?? ''));
      $last_name = trim((string)($input['last_name'] ?? ''));
      $email = trim((string)($input['email'] ?? $user_email));
      $phone = trim((string)($input['phone'] ?? ''));
      $birth_date = trim((string)($input['birth_date'] ?? ''));
      $gender = trim((string)($input['gender'] ?? ''));
      $civil_status = trim((string)($input['civil_status'] ?? ''));
      $address = trim((string)($input['address'] ?? ''));
      $appointment_type = trim((string)($input['appointment_type'] ?? ''));
      $preferred_date = trim((string)($input['preferred_date'] ?? ''));
      $health_concerns = trim((string)($input['health_concerns'] ?? ''));
      $medical_history = trim((string)($input['medical_history'] ?? ''));
      $emergency_contact_name = trim((string)($input['emergency_contact_name'] ?? ''));
      $emergency_contact_phone = trim((string)($input['emergency_contact_phone'] ?? ''));

      if ($first_name===''||$last_name===''||$email===''||!filter_var($email,FILTER_VALIDATE_EMAIL)||$phone===''||$birth_date===''||$gender===''||$civil_status===''||$address===''||$appointment_type===''||$preferred_date===''||$health_concerns===''||$medical_history===''||$emergency_contact_name===''||$emergency_contact_phone==='') {
        jerr('Missing required fields');
      }

      $middle_name = trim((string)($input['middle_name'] ?? ''));
      $current_medications = trim((string)($input['current_medications'] ?? ''));
      $allergies = trim((string)($input['allergies'] ?? ''));

      $sql = "INSERT INTO appointments (
        user_id, first_name, middle_name, last_name, email, phone, birth_date, gender, civil_status, address, appointment_type, preferred_date, health_concerns, medical_history, current_medications, allergies, emergency_contact_name, emergency_contact_phone
      ) VALUES (
        :uid, :first_name, :middle_name, :last_name, :email, :phone, :birth_date, :gender, :civil_status, :address, :appointment_type, :preferred_date, :health_concerns, :medical_history, :current_medications, :allergies, :ecn, :ecp
      )";
      $stmt = $db->prepare($sql);
      $ok = $stmt->execute([
        ':uid'=>$user_id,
        ':first_name'=>$first_name,
        ':middle_name'=>($middle_name!==''?$middle_name:null),
        ':last_name'=>$last_name,
        ':email'=>$email,
        ':phone'=>$phone,
        ':birth_date'=>$birth_date,
        ':gender'=>$gender,
        ':civil_status'=>$civil_status,
        ':address'=>$address,
        ':appointment_type'=>$appointment_type,
        ':preferred_date'=>$preferred_date,
        ':health_concerns'=>$health_concerns,
        ':medical_history'=>$medical_history,
        ':current_medications'=>($current_medications!==''?$current_medications:null),
        ':allergies'=>($allergies!==''?$allergies:null),
        ':ecn'=>$emergency_contact_name,
        ':ecp'=>$emergency_contact_phone
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
