<?php
require 'vendor/autoload.php';
require_once('RtcTokenBuilder.php');
require_once('token_generator.php');  // This now includes both classes
use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $tokenManager = new AgoraTokenManager();
    
    // Get parameters from the request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $channelName = $data['channelName'] ?? null;
        $uid = $data['uid'] ?? null;
    } else {
        $channelName = $_GET['channelName'] ?? null;
        $uid = $_GET['uid'] ?? null;
    }
    
    // Get token and channel name
    $result = $tokenManager->getToken($channelName, $uid);
    
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
