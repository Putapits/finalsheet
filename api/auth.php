<?php

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database_connection.php';
require_once __DIR__ . '/../services/PHPMailerService.php';


function sendResponse($success, $message, $data = null, $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit();
}

function sanitizeInput($data)
{
    $sanitized = [];
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $sanitized[$key] = sanitizeInput($value);
            continue;
        }

        $trimmed = trim((string) ($value ?? ''));

        if (in_array($key, ['password', 'confirmPassword', 'otp'], true)) {
            $sanitized[$key] = $trimmed;
        } else {
            $sanitized[$key] = htmlspecialchars(strip_tags($trimmed), ENT_QUOTES, 'UTF-8');
        }
    }
    return $sanitized;
}

function getPdoInstance()
{
    try {
        $dsn = "mysql:host=localhost;dbname=gsm_health_system;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, 'root', '', $options);
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        return false;
    }
}

function fetchUserColumns(PDO $pdo)
{
    static $columns = null;
    if ($columns === null) {
        try {
            $stmt = $pdo->query('SHOW COLUMNS FROM users');
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            error_log('Unable to fetch users table columns: ' . $e->getMessage());
            $columns = [];
        }
    }
    return $columns;
}

function userTableHasColumn(PDO $pdo, $column)
{
    return in_array($column, fetchUserColumns($pdo), true);
}

function getMailerService()
{
    static $service = null;
    if ($service === null) {
        try {
            $service = EmailService::fromConfig();
        } catch (\Exception $e) {
            error_log('Email service configuration error: ' . $e->getMessage());
            $service = false;
        }
    }
    return $service;
}

function generateOtpCode($length = 6)
{
    $digits = '';
    for ($i = 0; $i < $length; $i++) {
        $digits .= random_int(0, 9);
    }
    return $digits;
}

function storeOtp(PDO $pdo, $email, $otp)
{
    $expiresAt = (new DateTime('+2 minutes'))->format('Y-m-d H:i:s');
    $hash = password_hash($otp, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare('DELETE FROM login_otps WHERE email = :email OR expires_at < NOW()');
    $stmt->execute([':email' => $email]);

    $stmt = $pdo->prepare('INSERT INTO login_otps (email, otp_hash, expires_at) VALUES (:email, :hash, :expires)');
    $stmt->execute([
        ':email' => $email,
        ':hash' => $hash,
        ':expires' => $expiresAt
    ]);
}

function fetchOtpRecord(PDO $pdo, $email)
{
    $stmt = $pdo->prepare('SELECT * FROM login_otps WHERE email = :email ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([':email' => $email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function incrementOtpAttempt(PDO $pdo, $id)
{
    $stmt = $pdo->prepare('UPDATE login_otps SET attempts = attempts + 1 WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function markOtpVerified(PDO $pdo, $id)
{
    $stmt = $pdo->prepare('UPDATE login_otps SET verified_at = NOW() WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function validateLoginInput($data)
{
    $errors = [];

    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($data['password'])) {
        $errors[] = 'Password is required';
    }

    return $errors;
}

function validateRegisterInput($data)
{
    $errors = [];

    if (empty($data['firstName'])) {
        $errors[] = 'First name is required';
    }

    if (empty($data['lastName'])) {
        $errors[] = 'Last name is required';
    }

    if (empty($data['email'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (empty($data['password'])) {
        $errors[] = 'Password is required';
    } else {
        $password = $data['password'];
        if (strlen($password) < 10) {
            $errors[] = 'Password must be at least 10 characters long';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
    }

    if (empty($data['confirmPassword'])) {
        $errors[] = 'Confirm password is required';
    } elseif (!empty($data['password']) && $data['password'] !== $data['confirmPassword']) {
        $errors[] = 'Passwords do not match';
    }

    return $errors;
}

function determineRedirect($role)
{
    $default = 'website.php';
    if (!class_exists('Database')) {
        return $default;
    }

    $redirect = Database::getRoleRedirect($role);

    if ($role === 'citizen') {
        return $default;
    }

    return $redirect ?: $default;
}

function authenticate(PDO $pdo, $email, $password)
{
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $passwordField = userTableHasColumn($pdo, 'password_hash') ? 'password_hash' : 'password';
        $storedHash = $user[$passwordField] ?? null;

        if (!$storedHash || !password_verify($password, $storedHash)) {
            return false;
        }

        if (isset($user['status']) && $user['status'] !== 'active') {
            return ['error' => 'Account is not active. Please contact support.'];
        }

        return $user;
    } catch (PDOException $e) {
        error_log('Authentication error: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!is_array($input)) {
        sendResponse(false, 'Invalid JSON input', null, 400);
    }

    $sanitizedInput = sanitizeInput($input);
    $pdo = getPdoInstance();

    if (!$pdo) {
        sendResponse(false, 'Database connection failed', null, 500);
    }

    $action = $sanitizedInput['action'] ?? 'login';

    switch ($action) {
        case 'login':
            $errors = validateLoginInput($sanitizedInput);
            if (!empty($errors)) {
                sendResponse(false, 'Validation failed', ['errors' => $errors], 400);
            }

            $result = authenticate($pdo, $sanitizedInput['email'], $sanitizedInput['password']);

            if (!$result) {
                sendResponse(false, 'Invalid email or password', null, 401);
            }

            if (is_array($result) && isset($result['error'])) {
                sendResponse(false, $result['error'], null, 403);
            }

            // Generate and send OTP instead of logging in immediately
            $email = $sanitizedInput['email'];
            $otp = generateOtpCode();
            storeOtp($pdo, $email, $otp);

            $mailer = getMailerService();
            if (!$mailer) {
                sendResponse(false, 'Email service not configured.', null, 500);
            }

            $name = trim(($result['first_name'] ?? '') . ' ' . ($result['last_name'] ?? '')) ?: 'User';
            if (!$mailer->sendOtp($email, $name, $otp)) {
                sendResponse(false, 'Failed to send OTP. Please try again later.', null, 500);
            }

            sendResponse(true, 'OTP sent to your email. Please verify to continue.', [
                'otp_required' => true,
                'email' => $email,
                'expires_in' => 120
            ]);
            break;

        case 'register':
            $errors = validateRegisterInput($sanitizedInput);
            if (!empty($errors)) {
                sendResponse(false, 'Validation failed', ['errors' => $errors], 400);
            }

            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $sanitizedInput['email']]);
                if ($stmt->fetch()) {
                    sendResponse(false, 'Email is already registered', null, 409);
                }

                $columns = ['first_name', 'last_name', 'email'];
                $params = [
                    ':first_name' => $sanitizedInput['firstName'],
                    ':last_name' => $sanitizedInput['lastName'],
                    ':email' => $sanitizedInput['email']
                ];

                $passwordColumn = userTableHasColumn($pdo, 'password_hash') ? 'password_hash' : 'password';
                $columns[] = $passwordColumn;
                $params[':' . $passwordColumn] = password_hash($sanitizedInput['password'], PASSWORD_DEFAULT);

                if (userTableHasColumn($pdo, 'middle_name') && isset($sanitizedInput['middleName'])) {
                    $columns[] = 'middle_name';
                    $params[':middle_name'] = $sanitizedInput['middleName'] ?: null;
                }

                if (userTableHasColumn($pdo, 'contact_number') && isset($sanitizedInput['contactNumber'])) {
                    $columns[] = 'contact_number';
                    $params[':contact_number'] = $sanitizedInput['contactNumber'] ?: null;
                } elseif (userTableHasColumn($pdo, 'phone') && isset($sanitizedInput['contactNumber'])) {
                    $columns[] = 'phone';
                    $params[':phone'] = $sanitizedInput['contactNumber'] ?: null;
                }

                if (userTableHasColumn($pdo, 'address') && isset($sanitizedInput['address'])) {
                    $columns[] = 'address';
                    $params[':address'] = $sanitizedInput['address'] ?: null;
                }

                if (userTableHasColumn($pdo, 'role')) {
                    $columns[] = 'role';
                    $params[':role'] = 'citizen';
                }

                if (userTableHasColumn($pdo, 'status')) {
                    $columns[] = 'status';
                    $params[':status'] = 'active';
                }

                if (userTableHasColumn($pdo, 'created_at')) {
                    $columns[] = 'created_at';
                    $params[':created_at'] = date('Y-m-d H:i:s');
                }

                $sql = 'INSERT INTO users (' . implode(', ', $columns) . ') VALUES (' . implode(', ', array_keys($params)) . ')';
                $insert = $pdo->prepare($sql);
                $insert->execute($params);

                sendResponse(true, 'Registration successful', null, 201);
            } catch (PDOException $e) {
                error_log('Registration error: ' . $e->getMessage());
                sendResponse(false, 'Failed to register account. Please try again later.', null, 500);
            }
            break;

        case 'check_email':
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $sanitizedInput['email']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                sendResponse(true, 'Email check completed', [
                    'exists' => (bool) $user
                ]);
            } catch (PDOException $e) {
                error_log('Email check error: ' . $e->getMessage());
                sendResponse(false, 'Database error', null, 500);
            }
            break;

        case 'request_otp':
            $email = $sanitizedInput['email'] ?? '';
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                sendResponse(false, 'A valid email address is required.', null, 400);
            }

            try {
                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    sendResponse(false, 'Email is not registered.', null, 404);
                }

                if (isset($user['status']) && $user['status'] !== 'active') {
                    sendResponse(false, 'Account is not active. Please contact support.', null, 403);
                }

                $otp = generateOtpCode();
                storeOtp($pdo, $email, $otp);

                $mailer = getMailerService();
                if (!$mailer) {
                    sendResponse(false, 'Email service not configured.', null, 500);
                }

                $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'User';
                if (!$mailer->sendOtp($email, $name, $otp)) {
                    sendResponse(false, 'Failed to send OTP. Please try again later.', null, 500);
                }

                sendResponse(true, 'OTP sent. Please check your email.', [
                    'expires_in' => 120
                ]);
            } catch (PDOException $e) {
                error_log('OTP request error: ' . $e->getMessage());
                sendResponse(false, 'Database error', null, 500);
            }
            break;

        case 'verify_otp':
            $email = $sanitizedInput['email'] ?? '';
            $otp = $sanitizedInput['otp'] ?? '';

            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                sendResponse(false, 'A valid email address is required.', null, 400);
            }

            if (!$otp || strlen($otp) !== 6) {
                sendResponse(false, 'Invalid OTP provided.', null, 400);
            }

            try {
                $record = fetchOtpRecord($pdo, $email);
                if (!$record) {
                    sendResponse(false, 'No OTP found. Please request a new code.', null, 404);
                }

                if ($record['verified_at']) {
                    sendResponse(false, 'OTP already used. Request a new code if needed.', null, 400);
                }

                if (new DateTime() > new DateTime($record['expires_at'])) {
                    sendResponse(false, 'OTP has expired. Please request a new code.', null, 410);
                }

                if ((int) $record['attempts'] >= 5) {
                    sendResponse(false, 'Too many failed attempts. Request a new code.', null, 429);
                }

                if (!password_verify($otp, $record['otp_hash'])) {
                    incrementOtpAttempt($pdo, $record['id']);
                    sendResponse(false, 'Invalid OTP. Please try again.', null, 401);
                }

                $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    sendResponse(false, 'Account not found for this email.', null, 404);
                }

                markOtpVerified($pdo, $record['id']);

                $_SESSION['user_id'] = $user['id'] ?? null;
                $_SESSION['email'] = $user['email'] ?? null;
                $_SESSION['role'] = $user['role'] ?? 'citizen';
                $_SESSION['name'] = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

                sendResponse(true, 'OTP verified successfully.', [
                    'redirect' => determineRedirect($_SESSION['role'])
                ]);
            } catch (PDOException $e) {
                error_log('OTP verification error: ' . $e->getMessage());
                sendResponse(false, 'Database error', null, 500);
            }
            break;

        default:
            sendResponse(false, 'Invalid action', null, 400);
    }
} else {
    sendResponse(false, 'Method not allowed', null, 405);
}
?>