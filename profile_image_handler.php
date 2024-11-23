<?php
// profile_image_handler.php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

function generateProfileImageName($originalName, $userId) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    return 'profile_' . $userId . '_' . time() . '.' . $extension;
}

try {
    // Connect to database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Check if file was uploaded
    if (!isset($_FILES['profile_image'])) {
        throw new Exception('No profile image uploaded');
    }

    $file = $_FILES['profile_image'];
    $userId = $_POST['user_id'] ?? null;

    if (!$userId) {
        throw new Exception('User ID is required');
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG and PNG allowed.');
    }

    // Create profile images directory if it doesn't exist
    $uploadDir = 'uploads/profile_images/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $fileName = generateProfileImageName($file['name'], $userId);
    $targetPath = $uploadDir . $fileName;

    // Delete old profile image if exists
    $stmt = $conn->prepare("SELECT image_path FROM user_profile_images WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($oldImage = $result->fetch_assoc()) {
        if (file_exists($oldImage['image_path'])) {
            unlink($oldImage['image_path']);
        }
    }

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Store file information in database
    $imageUrl = 'https://api.crimsonflare.com/' . $targetPath;
    
    // Check if user already has a profile image
    $stmt = $conn->prepare("SELECT id FROM user_profile_images WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing record
        $stmt = $conn->prepare("UPDATE user_profile_images SET image_path = ?, image_url = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ?");
        $stmt->bind_param("sss", $targetPath, $imageUrl, $userId);
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO user_profile_images (user_id, image_path, image_url) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $userId, $targetPath, $imageUrl);
    }
    
    if (!$stmt->execute()) {
        unlink($targetPath); // Delete uploaded file if database operation fails
        throw new Exception('Failed to save to database');
    }

    echo json_encode([
        'success' => true,
        'image_url' => $imageUrl
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}