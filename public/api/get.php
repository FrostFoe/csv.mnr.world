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

// Get ID from request
$id = $_GET['id'] ?? null;
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing ID']);
    exit;
}

// Fetch data from database
$stmt = $mysqli->prepare("SELECT filename, description, json_text, row_count FROM csv_files WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $data = json_decode($row['json_text'], true);

    $response = [
        'success' => true,
        'id' => (int)$id,
        'filename' => $row['filename'],
        'description' => $row['description'],
        'row_count' => $row['row_count'],
        'data' => $data
    ];

    header('Cache-Control: public, max-age=60');
    echo json_encode($response, JSON_PRETTY_PRINT);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found']);
}

$stmt->close();
$mysqli->close();
ob_end_flush();
