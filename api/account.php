<?php
require_once '../include/database.php';
startSecureSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['role'] ?? null;

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? null;

function read_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) { $data = $_POST; }
    return $data ?: [];
}

try {
    if ($method === 'GET') {
        // action=me returns current user profile
        if ($action === 'me') {
            $u = $database->getUserById($user_id);
            if (!$u) { throw new Exception('User not found'); }
            unset($u['password']);
            echo json_encode(['success' => true, 'data' => $u]);
            exit;
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    if ($method === 'POST') {
        $data = read_json_body();
        $action = $data['action'] ?? $action;
        if ($action === 'update_profile') {
            $allowed = ['first_name','last_name','email','phone','address','date_of_birth','gender'];
            $update = [];
            foreach ($allowed as $f) {
                if (isset($data[$f])) { $update[$f] = trim((string)$data[$f]); }
            }
            if (isset($update['email']) && $database->emailExists($update['email'], $user_id)) {
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
                exit;
            }
            if (!$update) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No changes provided']);
                exit;
            }
            $ok = $database->updateUser($user_id, $update);
            if ($ok) {
                $database->logAudit($user_id, $role, 'profile_update', json_encode($update));
                echo json_encode(['success' => true, 'message' => 'Profile updated']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
            }
            exit;
        } elseif ($action === 'change_password') {
            $current = (string)($data['current_password'] ?? '');
            $new = (string)($data['new_password'] ?? '');
            $confirm = (string)($data['confirm_password'] ?? '');
            if ($new === '' || $confirm === '' || $current === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'All password fields are required']);
                exit;
            }
            if ($new !== $confirm) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
                exit;
            }
            if (strlen($new) < 8) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters']);
                exit;
            }
            $res = $database->updateUserPassword($user_id, $current, $new);
            if (!empty($res['ok'])) {
                $database->logAudit($user_id, $role, 'password_change', 'User changed password');
                echo json_encode(['success' => true, 'message' => 'Password updated']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $res['error'] ?? 'Unable to update password']);
            }
            exit;
        }
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
} catch (Throwable $e) {
    error_log('account api error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
