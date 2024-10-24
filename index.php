<?php
require 'vendor/autoload.php';
require_once('RtcTokenBuilder.php');

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
    // Fetch Agora credentials from the .env file
    $appID = $_ENV['AGORA_APP_ID'];
    $appCertificate = $_ENV['AGORA_APP_CERTIFICATE'];

    // Get parameters from the request (supporting both GET and POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $channelName = $data['channelName'] ?? 'defaultChannel';
        $uid = $data['uid'] ?? rand(1, 999999);
    } else {
        $channelName = $_GET['channelName'] ?? 'defaultChannel';
        $uid = $_GET['uid'] ?? rand(1, 999999);
    }

    $role = RtcTokenBuilder::ROLE_PUBLISHER;
    $expireTimeInSeconds = $_ENV['TOKEN_EXPIRY_SECONDS'] ?? 3600;

    // Generate the token
    $currentTimestamp = (new DateTime())->getTimestamp();
    $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
    $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

    // Return the token in JSON format
    echo json_encode([
        'Status' => 'Success',
        'token' => $token,
        'uid' => $uid,
        'appId' => $appID,
        'channelName' => $channelName
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'Status' => 'error',
        'message' => $e->getMessage()
    ]);
}