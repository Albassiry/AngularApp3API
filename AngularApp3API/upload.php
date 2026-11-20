<?php
// Upload handler saving book cover images into AngularApp3API/uploads/
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method Not Allowed"]);
    exit();
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["message" => "No valid file uploaded."]);
    exit();
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$originalName = basename($_FILES['file']['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    http_response_code(400);
    echo json_encode(["message" => "Only JPG, JPEG, PNG, GIF, and WEBP files are allowed."]);
    exit();
}

$safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$newFilename = time() . '_' . $safeBase . '.' . $extension;
$targetPath = $uploadDir . $newFilename;

if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
    echo json_encode([
        "message" => "File uploaded successfully",
        "filename" => $newFilename,
        "url" => "http://localhost/AngularApp3API/uploads/" . $newFilename
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to save uploaded file."]);
}