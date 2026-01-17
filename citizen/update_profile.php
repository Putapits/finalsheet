<?php
require_once '../include/database.php';
startSecureSession();
requireLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
        exit;
    }

    $user_id = (int)$_SESSION['user_id'];

    // Collect and validate fields
    $first_name = isset($data['first_name']) ? trim($data['first_name']) : '';
    $last_name  = isset($data['last_name']) ? trim($data['last_name']) : '';
    $email      = isset($data['email']) ? trim($data['email']) : '';
    $phone      = isset($data['phone']) ? trim($data['phone']) : '';
    $address    = isset($data['address']) ? trim($data['address']) : '';
    $dob        = isset($data['date_of_birth']) ? trim($data['date_of_birth']) : '';
    $gender     = isset($data['gender']) ? strtolower(trim($data['gender'])) : '';

    if ($first_name === '' || $last_name === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'First name and last name are required']);
        exit;
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'A valid email address is required']);
        exit;
    }

    // Validate gender
    $allowed_genders = ['male','female','other'];
    if ($gender !== '' && !in_array($gender, $allowed_genders, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid gender value']);
        exit;
    }

    // Validate DOB (YYYY-MM-DD) or allow empty
    if ($dob !== '') {
        $d = DateTime::createFromFormat('Y-m-d', $dob);
        $isValidDob = $d && $d->format('Y-m-d') === $dob;
        if (!$isValidDob) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
            exit;
        }
    }

    // Check email uniqueness excluding current user
    if ($database->emailExists($email, $user_id)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email is already taken']);
        exit;
    }

    // Build update payload (only set provided fields)
    $update = [
        'first_name'    => $first_name,
        'last_name'     => $last_name,
        'email'         => $email,
        'phone'         => $phone,
        'address'       => $address,
    ];
    if ($dob !== '') {
        $update['date_of_birth'] = $dob;
    } else {
        // If client sends empty dob, set to NULL
        $update['date_of_birth'] = null;
    }
    if ($gender !== '') {
        $update['gender'] = $gender;
    } else {
        $update['gender'] = null;
    }

    $ok = $database->updateUser($user_id, $update);
    if (!$ok) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        exit;
    }

    // Refresh minimal user info
    $updated = $database->getUserById($user_id) ?: [];

    // Update session basics for header consistency
    $_SESSION['first_name'] = $updated['first_name'] ?? $first_name;
    $_SESSION['last_name']  = $updated['last_name'] ?? $last_name;
    $_SESSION['email']      = $updated['email'] ?? $email;

    echo json_encode(['success' => true, 'message' => 'Profile updated successfully', 'user' => $updated]);
} catch (Throwable $e) {
    error_log('Update profile error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
