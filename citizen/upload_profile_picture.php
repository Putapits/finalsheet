<?php
require_once '../include/database.php';
startSecureSession();
requireLogin();

function flash($msg, $type = 'error') {
    $_SESSION['flash_message'] = $msg;
    $_SESSION['flash_type'] = $type;
}

// Always send the user back to the styled profile
function back_to_profile() {
    header('Location: citizen.php?page=profile');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Invalid request method.');
    back_to_profile();
}

if (!isset($_FILES['profile_picture']) || !is_uploaded_file($_FILES['profile_picture']['tmp_name'])) {
    flash('Please choose an image to upload.');
    back_to_profile();
}

$file = $_FILES['profile_picture'];
$maxSize = 5 * 1024 * 1024; // 5MB
$allowedMime = ['image/jpeg','image/png','image/gif','image/webp'];
$allowedExt  = ['jpg','jpeg','png','gif','webp'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    flash('Upload failed. Please try again.');
    back_to_profile();
}
if ($file['size'] <= 0 || $file['size'] > $maxSize) {
    flash('Image too large. Maximum size is 5MB.');
    back_to_profile();
}

// Validate MIME using finfo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, $allowedMime, true)) {
    flash('Unsupported image type. Please upload JPG, PNG, GIF, or WEBP.');
    back_to_profile();
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    // Normalize extension based on MIME
    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
        default      => 'jpg'
    };
}

// Prepare destination
$baseDir = dirname(__DIR__); // project root (capstone-HS)
$destDir = $baseDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profile_pictures';
if (!is_dir($destDir)) {
    @mkdir($destDir, 0775, true);
}

$basename = 'u' . (int)$_SESSION['user_id'] . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
$destPath = $destDir . DIRECTORY_SEPARATOR . $basename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    flash('Failed to save image.');
    back_to_profile();
}

// Save relative web path for use in HTML
$relativePath = 'uploads/profile_pictures/' . $basename;

if ($database->updateUser((int)$_SESSION['user_id'], ['profile_picture' => $relativePath])) {
    flash('Profile photo updated successfully.', 'success');
} else {
    flash('Could not update your profile photo.');
}

back_to_profile();
