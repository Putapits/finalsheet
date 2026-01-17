<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('admin');
header('Content-Type: application/json');
$dbh = $db; // alias
try {
  $dbh->exec("CREATE TABLE IF NOT EXISTS admin_notification_reads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    kind VARCHAR(32) NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_item (user_id, kind, item_id)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Throwable $e) {
  // ignore
}
$uid = (int)($_SESSION['user_id'] ?? 0);
$action = $_POST['action'] ?? '';
if ($uid <= 0) { echo json_encode(['ok'=>false,'error'=>'auth']); exit; }
if ($action === 'read') {
  $kind = $_POST['kind'] ?? '';
  $id = (int)($_POST['id'] ?? 0);
  if (!in_array($kind, ['hcs_appointment','service_request'], true) || $id <= 0) {
    echo json_encode(['ok'=>false,'error'=>'bad_request']); exit;
  }
  try {
    $stmt = $dbh->prepare('INSERT IGNORE INTO admin_notification_reads (user_id, kind, item_id) VALUES (?, ?, ?)');
    $stmt->execute([$uid, $kind, $id]);
    echo json_encode(['ok'=>true]);
  } catch (Throwable $e) {
    echo json_encode(['ok'=>false,'error'=>'db']);
  }
  exit;
}
if ($action === 'read_all') {
  // Optional: mark all current pending as read for this user
  try {
    $dbh->beginTransaction();
    $dbh->exec("INSERT IGNORE INTO admin_notification_reads (user_id, kind, item_id)
      SELECT {$uid} AS user_id, 'hcs_appointment' AS kind, a.id AS item_id
      FROM appointments a
      WHERE a.status='pending' AND a.deleted_at IS NULL");
    $dbh->exec("INSERT IGNORE INTO admin_notification_reads (user_id, kind, item_id)
      SELECT {$uid} AS user_id, 'service_request' AS kind, s.id AS item_id
      FROM service_requests s
      WHERE s.status='pending' AND s.deleted_at IS NULL");
    $dbh->commit();
    echo json_encode(['ok'=>true]);
  } catch (Throwable $e) {
    try { $dbh->rollBack(); } catch (Throwable $e2) {}
    echo json_encode(['ok'=>false,'error'=>'db']);
  }
  exit;
}
// default
echo json_encode(['ok'=>false,'error'=>'unknown_action']);
