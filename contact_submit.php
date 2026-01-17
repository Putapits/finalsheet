<?php
require_once 'include/database.php';
startSecureSession();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    // Support JSON body or form-encoded
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $first_name = trim($data['first_name'] ?? $data['first-name'] ?? '');
    $last_name  = trim($data['last_name'] ?? $data['last-name'] ?? '');
    $email      = trim($data['email'] ?? '');
    $phone      = trim($data['phone'] ?? '');
    $subject    = trim($data['subject'] ?? '');
    $message    = trim($data['message'] ?? '');

    // Validation
    if ($first_name === '' || $last_name === '' || $email === '' || $phone === '' || $subject === '' || $message === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    $ok = $database->createContactMessage([
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'email'      => $email,
        'phone'      => $phone,
        'subject'    => $subject,
        'message'    => $message,
    ]);

    if (!$ok) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to send message. Please try again later.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent.']);
} catch (Throwable $e) {
    error_log('contact_submit error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unexpected server error']);
}
