<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'message' => 'No image uploaded.']);
        exit;
    }

    $file     = $_FILES['image'];
    $fileName = basename($file['name']);
    $tmpName  = $file['tmp_name'];
    $error    = $file['error'];
    $size     = $file['size'];

    // Check for upload errors
    if ($error !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error uploading file. Code: ' . $error]);
        exit;
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileInfo     = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType     = finfo_file($fileInfo, $tmpName);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
        exit;
    }

    // Limit size to 5MB
    if ($size > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
        exit;
    }

    // Setup upload directory
    $uploadDir = __DIR__ . '/../assets/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename to avoid overwrites
    $ext         = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = uniqid('icon_', true) . '.' . $ext;
    $targetPath  = $uploadDir . $newFileName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        // Return relative path from web root for frontend to use
        $relativePath = 'assets/uploads/' . $newFileName;
        echo json_encode(['success' => true, 'path' => $relativePath]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
