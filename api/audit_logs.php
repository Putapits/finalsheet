<?php
require_once '../include/database.php';
startSecureSession();
requireRole('admin');

header('Content-Type: application/json');

function valid_date($d){ return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); }

try {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $role   = isset($_GET['role']) && $_GET['role'] !== '' ? (string)$_GET['role'] : null;
    $action = isset($_GET['action']) && $_GET['action'] !== '' ? (string)$_GET['action'] : null;
    $q      = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
    $df     = isset($_GET['date_from']) && valid_date($_GET['date_from']) ? $_GET['date_from'] : null;
    $dt     = isset($_GET['date_to']) && valid_date($_GET['date_to']) ? $_GET['date_to'] : null;

    $page   = max(1, (int)($_GET['page'] ?? 1));
    $size   = (int)($_GET['size'] ?? 10);
    $allowedSizes = [10,25,50,100];
    if (!in_array($size, $allowedSizes, true)) $size = 10;
    $offset = ($page - 1) * $size;

    $filters = [];
    if ($role)   $filters['role'] = $role;
    if ($action) $filters['action'] = $action;
    if ($q)      $filters['q'] = $q;
    if ($df)     $filters['date_from'] = $df;
    if ($dt)     $filters['date_to']   = $dt;

    // CSV export of all filtered rows
    if (isset($_GET['export']) && strtolower($_GET['export']) === 'csv') {
        $rows = $database->getAuditLogs($filters);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=audit_logs.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Time','User','Role','Action','Details','IP']);
        foreach ($rows as $r) {
            $time = date('Y-m-d H:i:s', strtotime($r['created_at']));
            $user = trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? ''));
            fputcsv($out, [$time, $user . ' <' . ($r['email'] ?? '') . '>', ($r['role'] ?? ''), ($r['action'] ?? ''), ($r['details'] ?? ''), ($r['ip'] ?? '')]);
        }
        fclose($out);
        exit;
    }

    $total = $database->countAuditLogs($filters);
    $rows  = $database->getAuditLogsPaginated($filters, $size, $offset);

    // Normalize
    $data = array_map(function($r){
        return [
            'id' => (int)$r['id'],
            'time' => $r['created_at'],
            'time_ms' => strtotime($r['created_at']) * 1000,
            'user' => trim(($r['first_name'] ?? '').' '.($r['last_name'] ?? '')),
            'email' => $r['email'] ?? '',
            'role' => $r['role'] ?? '',
            'action' => $r['action'] ?? '',
            'details' => $r['details'] ?? '',
            'ip' => $r['ip'] ?? ''
        ];
    }, $rows);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'size' => $size,
        'pages' => (int)max(1, ceil($total / $size))
    ]);
} catch (Throwable $e) {
    error_log('audit_logs api error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
