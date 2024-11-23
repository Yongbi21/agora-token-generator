<?php
// image_upload.php

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate request
        if (!isset($_FILES['image']) || !isset($_POST['userId']) || !isset($_POST['imageType'])) {
            throw new Exception('Missing required parameters');
        }

        $userId = $_POST['userId'];
        $imageType = strtolower($_POST['imageType']); // 'id' or 'selfie'
        
        // Validate image type
        if (!in_array($imageType, ['id', 'selfie'])) {
            throw new Exception('Invalid image type');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPG and PNG are allowed.');
        }

        // Set upload directory
        $uploadDir = __DIR__ . '/uploads/' . $imageType . 's/';

        // Generate unique filename
        $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = $userId . '_' . time() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Generate URL (replace with your actual domain)
            $baseUrl = 'https://' . $_SERVER['HTTP_HOST'];
            $imageUrl = $baseUrl . '/uploads/' . $imageType . 's/' . $fileName;
            
            echo json_encode([
                'success' => true,
                'imageUrl' => $imageUrl,
                'message' => 'File uploaded successfully'
            ]);
        } else {
            throw new Exception('Failed to move uploaded file');
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
