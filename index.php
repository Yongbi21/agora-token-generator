<?php
require_once(__DIR__ . '/RtcTokenBuilder.php');
require_once(__DIR__ . '/AccessToken.php');
require_once(__DIR__ . '/Util.php');
 // Contains Util class

// Your Agora credentials
$appID = "1e5d6e4a701949f684dd61a3f86f6e56";
$appCertificate = "10844f8702824ee09ef45d14bb280ab0";
$channelName = $_GET['channelName'] ?? "test_channel"; // Channel name (can be passed as query parameter)
$uid = $_GET['uid'] ?? 0; // User ID, default is 0
$role = RtcTokenBuilder::RolePublisher; // Set user role
$privilegeExpireTime = 3600; // Token valid for 1 hour

// Calculate privilege expiration timestamp (current time + privilege expiration time)
$privilegeExpireTs = time() + $privilegeExpireTime;

// Generate token
try {
    $token = RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpireTs);
    echo json_encode(["token" => $token]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
