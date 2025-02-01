<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$upload_dir = "uploads/payment_proofs/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_FILES["proof"])) {
        $file = $_FILES["proof"];
        
        // Generate unique identifier for the file
        $unique_id = uniqid();
        
        $file_ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $allowed_extensions = ["jpg", "jpeg", "png", "pdf"];

        if (in_array($file_ext, $allowed_extensions)) {
            $new_file_name = "proof_" . $unique_id . "_" . time() . "." . $file_ext;
            $file_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file["tmp_name"], $file_path)) {
                $response["success"] = true;
                $response["message"] = "Upload successful";
                // Make sure this URL matches your actual domain
                $response["file_url"] = "https://crimsonflare.space/uploads/payment_proofs/" . $new_file_name;
            } else {
                $response["success"] = false;
                $response["message"] = "Failed to move uploaded file.";
                // Add error logging
                error_log("Failed to move uploaded file: " . print_r($_FILES, true));
            }
        } else {
            $response["success"] = false;
            $response["message"] = "Invalid file type. Only JPG, PNG, and PDF allowed.";
        }
    } else {
        $response["success"] = false;
        $response["message"] = "No file uploaded.";
    }
} else {
    $response["success"] = false;
    $response["message"] = "Invalid request method.";
}

// Set proper content type for JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>