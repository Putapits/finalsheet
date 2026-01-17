<?php
require_once 'include/database.php';
startSecureSession();

// Audit: log logout before destroying session
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    try { $database->logAudit($_SESSION['user_id'], $_SESSION['role'], 'logout', 'Logout'); } catch (Throwable $e) {}
}

// Destroy all session data
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header('Location: index.php');
exit();
?>
