<?php
require 'vendor/autoload.php'; // Load Composer's autoloader
use Dotenv\Dotenv;             // Use the dotenv package

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Load Agora RTC Token Builder
require_once('RtcTokenBuilder.php');

// Fetch Agora credentials from the .env file
$appID = $_ENV['AGORA_APP_ID'];
$appCertificate = $_ENV['AGORA_APP_CERTIFICATE'];

// Get parameters from the request
$channelName = $_GET['channelName'] ?? 'defaultChannel';
$uid = 0; // Use 0 for broadcast mode or pass the specific UID
$role = RtcTokenBuilder::ROLE_PUBLISHER;
$expireTimeInSeconds = $_ENV['TOKEN_EXPIRY_SECONDS'] ?? 3600;

// Generate the token
$currentTimestamp = (new DateTime())->getTimestamp();
$privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
$token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs);

// Return the token in JSON format
header('Content-Type: application/json');
echo json_encode(['token' => $token]);
