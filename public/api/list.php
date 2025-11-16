<?php
ob_start();
require_once '../../includes/config.php';

header('Content-Type: application/json');

// API Key Authentication
if (!isset($_GET['key']) || $_GET['key'] !== API_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
$sql = "SELECT id, filename, description, row_count, size_kb, uploaded_at FROM csv_files ORDER BY uploaded_at DESC";
$result = $mysqli->query($sql);
$files = $result->fetch_all(MYSQLI_ASSOC);

$response_files = [];
foreach ($files as $file) {
    $response_files[] = [
        'id' => $file['id'],
        'filename' => $file['filename'],
        'description' => $file['description'],
        'row_count' => $file['row_count'],
        'size_kb' => $file['size_kb'],
        'uploaded_at' => $file['uploaded_at'],
        'scrape_url' => "{$domain}/public/api/get.php?id={$file['id']}"
    ];
}

echo json_encode(['success' => true, 'files' => $response_files], JSON_PRETTY_PRINT);

$mysqli->close();
ob_end_flush();
