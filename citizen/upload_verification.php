<?php
require_once '../include/database.php';
startSecureSession();
requireLogin();

function flash($msg, $type = 'error') {
    $_SESSION['flash_message'] = $msg;
    $_SESSION['flash_type'] = $type;
}

function back_to_profile() {
    header('Location: citizen.php?page=profile');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('Invalid request method.');
    back_to_profile();
}

$user_id = (int)($_SESSION['user_id'] ?? 0);
$document_type = isset($_POST['document_type']) ? trim($_POST['document_type']) : '';
if ($user_id <= 0) {
    flash('Not authenticated.');
    back_to_profile();
}
if ($document_type === '') {
    flash('Please select a document type.');
    back_to_profile();
}

// Accept either 'document' or legacy 'document_file'
$file = null;
if (isset($_FILES['document'])) {
    $file = $_FILES['document'];
} elseif (isset($_FILES['document_file'])) {
    $file = $_FILES['document_file'];
}

if (!$file) {
    flash('No file received. Please choose a JPG, PNG, or PDF.');
    back_to_profile();
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    $err = (int)$file['error'];
    switch ($err) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            flash('File too large for server limits. Please upload a smaller file or increase upload_max_filesize and post_max_size in PHP settings.');
            break;
        case UPLOAD_ERR_NO_FILE:
            flash('No file uploaded. Please choose a JPG, PNG, or PDF.');
            break;
        case UPLOAD_ERR_PARTIAL:
            flash('Upload was interrupted. Please try again.');
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
        default:
            flash('Upload failed due to a server error. Please try again.');
            break;
    }
    back_to_profile();
}
$allowedExt  = ['jpg','jpeg','png','pdf'];
$allowedMime = ['image/jpeg','image/png','application/pdf'];
$maxSize = 10 * 1024 * 1024; // 10MB

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    flash('Only JPG, PNG or PDF files are allowed.');
    back_to_profile();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, $allowedMime, true)) {
    flash('Invalid file type.');
    back_to_profile();
}
if ($file['size'] <= 0 || $file['size'] > $maxSize) {
    flash('File too large. Maximum size is 10MB.');
    back_to_profile();
}

// Destination directory
$destDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'verifications';
if (!is_dir($destDir)) {
    @mkdir($destDir, 0775, true);
}

$basename = 'uid' . $user_id . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
$destPath = $destDir . DIRECTORY_SEPARATOR . $basename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    flash('Failed to save the uploaded file.');
    back_to_profile();
}

// Relative path for serving
$relativePath = 'uploads/verifications/' . $basename;

if ($database->submitUserVerification($user_id, $document_type, $relativePath)) {
    flash('Verification submitted successfully. Please wait for admin review.', 'success');
} else {
    flash('Failed to submit verification. Please try again.');
}

back_to_profile();

