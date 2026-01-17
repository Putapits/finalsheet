<?php
// Admin Users API: block/unblock
require_once '../../include/database.php';
startSecureSession();
requireRole('admin');

header('Content-Type: application/json');

$resp = ['ok' => false];
try {
  // Read-only: show user details for modal
  if (($_SERVER['REQUEST_METHOD'] === 'GET') && strtolower(trim($_GET['action'] ?? '')) === 'show') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['ok'=>false,'error'=>'Invalid id']);
      exit;
    }
    try {
      $sql = "SELECT id, first_name, last_name, email, role, IFNULL(status,'active') AS status, phone, address, date_of_birth, gender, verification_status, created_at, updated_at, last_login FROM users WHERE id = :id AND role IN ('doctor','nurse','inspector') LIMIT 1";
      $st = $db->prepare($sql);
      $st->bindValue(':id', $id, PDO::PARAM_INT);
      $st->execute();
      $user = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
      // Fallback if status column does not exist
      $sql = "SELECT id, first_name, last_name, email, role, 'active' AS status, phone, address, date_of_birth, gender, verification_status, created_at, updated_at, last_login FROM users WHERE id = :id AND role IN ('doctor','nurse','inspector') LIMIT 1";
      $st = $db->prepare($sql);
      $st->bindValue(':id', $id, PDO::PARAM_INT);
      $st->execute();
      $user = $st->fetch(PDO::FETCH_ASSOC);
    }
    if ($user) {
      echo json_encode(['ok'=>true,'user'=>$user]);
    } else {
      http_response_code(404);
      echo json_encode(['ok'=>false,'error'=>'Not found']);
    }
    exit;
  }

  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
    exit;
  }
  $action = strtolower(trim($_POST['action'] ?? ''));
  $id = (int)($_POST['id'] ?? 0);
  if (!in_array($action, ['block','unblock'], true) || $id <= 0) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'Invalid request']);
    exit;
  }

  // Ensure status column exists
  $hasStatus = false;
  try { $hasStatus = (bool)$db->query("SHOW COLUMNS FROM users LIKE 'status'")->fetch(PDO::FETCH_ASSOC); } catch (Throwable $e) {}
  if (!$hasStatus) {
    try { $db->exec("ALTER TABLE users ADD COLUMN status ENUM('active','blocked') NOT NULL DEFAULT 'active'"); } catch (Throwable $e) {}
  }

  $newStatus = $action === 'block' ? 'blocked' : 'active';
  $sql = "UPDATE users SET status = :status WHERE id = :id AND role IN ('doctor','nurse','inspector')";
  $stmt = $db->prepare($sql);
  $stmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
  $stmt->bindValue(':id', $id, PDO::PARAM_INT);
  $stmt->execute();

  if ($stmt->rowCount() > 0) {
    $resp['ok'] = true;
  } else {
    $resp['error'] = 'User not found or no change';
  }
} catch (Throwable $e) {
  http_response_code(500);
  $resp['error'] = 'Server error';
}

echo json_encode($resp);
