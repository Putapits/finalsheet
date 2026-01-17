<?php
require_once '../../include/database.php';
startSecureSession();
requireRole('admin');
header('Content-Type: application/json');

function jerr($msg, $code=400){ http_response_code($code); echo json_encode(['success'=>false,'message'=>$msg]); exit; }

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) { $input = []; }
$action = $input['action'] ?? '';

// Unified list of service types across modules
$serviceTypes = [
  'medical-consultation'   => 'Medical Consultation',
  'emergency-care'         => 'Emergency Care',
  'preventive-care'        => 'Preventive Care',
  'business-permit'        => 'Business Permit',
  'health-inspection'      => 'Health Inspection',
  'vaccination'            => 'Vaccination',
  'nutrition-monitoring'   => 'Nutrition Monitoring',
  'disease-monitoring'     => 'Disease Monitoring',
  'environmental-monitoring'=> 'Environmental Monitoring',
  'system-inspection'      => 'System Inspection',
  'maintenance-service'    => 'Maintenance Service',
  'installation-upgrade'   => 'Installation & Upgrade',
];

try {
  switch ($action) {
    case 'fetch_service_types': {
      echo json_encode(['success'=>true, 'types'=>$serviceTypes]);
      break;
    }

    case 'list': {
      $entity = $input['entity'] ?? 'service_requests';
      $q = trim((string)($input['q'] ?? ''));
      $service_type = trim((string)($input['service_type'] ?? ''));

      if ($entity === 'appointments') {
        $sql = "SELECT id, first_name, middle_name, last_name, email, phone, appointment_type, preferred_date, status, created_at, updated_at, deleted_at
                FROM appointments
                WHERE deleted_at IS NOT NULL";
        $params = [];
        if ($q !== '') {
          $sql .= " AND (CONCAT_WS(' ', first_name, middle_name, last_name) LIKE :q OR email LIKE :q OR phone LIKE :q OR appointment_type LIKE :q OR address LIKE :q OR health_concerns LIKE :q OR medical_history LIKE :q)";
          $params[':q'] = '%'.$q.'%';
        }
        $sql .= ' ORDER BY deleted_at DESC, updated_at DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success'=>true, 'entity'=>'appointments', 'items'=>$items, 'total'=>count($items)]);
        break;
      }

      // default: service_requests
      $sql = "SELECT id, service_type, full_name, email, phone, preferred_date, urgency, status, created_at, updated_at, deleted_at
              FROM service_requests
              WHERE deleted_at IS NOT NULL";
      $params = [];
      if ($service_type !== '') {
        $sql .= " AND service_type = :stype";
        $params[':stype'] = $service_type;
      }
      if ($q !== '') {
        $sql .= " AND (full_name LIKE :q OR email LIKE :q OR phone LIKE :q OR address LIKE :q OR service_details LIKE :q)";
        $params[':q'] = '%'.$q.'%';
      }
      $sql .= ' ORDER BY deleted_at DESC, updated_at DESC';
      $stmt = $db->prepare($sql);
      $stmt->execute($params);
      $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(['success'=>true, 'entity'=>'service_requests', 'items'=>$items, 'total'=>count($items)]);
      break;
    }

    case 'restore': {
      $entity = $input['entity'] ?? '';
      $id = (int)($input['id'] ?? 0);
      if (!in_array($entity, ['appointments','service_requests'], true) || $id <= 0) jerr('Invalid entity or id');
      $table = $entity === 'appointments' ? 'appointments' : 'service_requests';
      $stmt = $db->prepare("UPDATE {$table} SET deleted_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND deleted_at IS NOT NULL");
      $ok = $stmt->execute([':id'=>$id]);
      if ($ok && $stmt->rowCount() > 0) {
        $database->logAudit($_SESSION['user_id'] ?? null, 'admin', 'restore', json_encode(['entity'=>$entity,'id'=>$id]));
        echo json_encode(['success'=>true, 'entity'=>$entity, 'id'=>$id]);
      } else {
        http_response_code(404);
        echo json_encode(['success'=>false, 'message'=>'Record not found or not deleted']);
      }
      break;
    }

    case 'restore_bulk': {
      $entity = $input['entity'] ?? '';
      $ids = $input['ids'] ?? [];
      if (!in_array($entity, ['appointments','service_requests'], true) || !is_array($ids) || empty($ids)) jerr('Invalid entity or ids');
      $ids = array_values(array_unique(array_map('intval', $ids)));
      $placeholders = implode(',', array_fill(0, count($ids), '?'));
      $table = $entity === 'appointments' ? 'appointments' : 'service_requests';
      $sql = "UPDATE {$table} SET deleted_at = NULL, updated_at = CURRENT_TIMESTAMP WHERE deleted_at IS NOT NULL AND id IN ($placeholders)";
      $stmt = $db->prepare($sql);
      $ok = $stmt->execute($ids);
      if ($ok) {
        $database->logAudit($_SESSION['user_id'] ?? null, 'admin', 'restore_bulk', json_encode(['entity'=>$entity,'ids'=>$ids]));
        echo json_encode(['success'=>true, 'entity'=>$entity, 'restored'=>$stmt->rowCount()]);
      } else {
        http_response_code(500);
        echo json_encode(['success'=>false, 'message'=>'Restore failed']);
      }
      break;
    }

    default:
      jerr('Unknown action', 404);
  }
} catch (Throwable $e) {
  error_log('admin/api/restore error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(['success'=>false,'message'=>'Server error']);
}
