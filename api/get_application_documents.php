<?php
/**
 * API Endpoint to get documents for a sanitary permit application
 * Returns document list with file paths and verification status
 */

require_once __DIR__ . '/../include/database.php';
startSecureSession();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$applicationId = $_GET['application_id'] ?? null;

if (!$applicationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing application_id']);
    exit();
}

global $database;

try {
    $db = $database->getConnection();

    // Verify user owns this application (or is admin)
    $checkStmt = $db->prepare("SELECT user_id FROM sanitary_permit_applications WHERE id = :id");
    $checkStmt->execute([':id' => (int) $applicationId]);
    $app = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$app) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Application not found']);
        exit();
    }

    // Allow if user owns the application or is admin
    $isAdmin = in_array($_SESSION['role'] ?? '', ['admin', 'sanitary_inspector', 'health_worker']);
    if ($app['user_id'] != $_SESSION['user_id'] && !$isAdmin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }

    // Fetch documents for this application
    $stmt = $db->prepare("
        SELECT id, document_type, file_path, status, created_at as uploaded_at
        FROM sanitary_permit_documents
        WHERE application_id = :app_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([':app_id' => (int) $applicationId]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map document types to friendly labels
    $labelMap = [
        'business_permit_image' => 'Business Permit (Current Year)',
        'permit_receipt_image' => 'Business Permit Official Receipt (Present Year)',
        'health_certificates_image' => 'Health Certificates',
        'occupancy_permit_image' => 'Health Occupancy Permit Receipt',
        'water_analysis_image' => 'Microbiological Water Analysis Report',
        'pest_control_image' => 'Pest Control Service Report'
    ];

    // Enhance documents with labels and full URLs
    foreach ($documents as &$doc) {
        $doc['label'] = $labelMap[$doc['document_type']] ?? $doc['document_type'];
        // Fix: Prepend project path to ensure link works
        $doc['url'] = '/hash-master/' . ltrim($doc['file_path'], '/');
    }

    echo json_encode([
        'success' => true,
        'documents' => $documents
    ]);

} catch (PDOException $e) {
    error_log("Get application documents error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
