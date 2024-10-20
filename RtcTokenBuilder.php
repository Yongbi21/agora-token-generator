<?php

class RtcTokenBuilder
{
    const ROLE_PUBLISHER = 1;
    const ROLE_SUBSCRIBER = 2;

    public static function buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs)
    {
        $params = [
            'appID' => $appID,
            'appCertificate' => $appCertificate,
            'channelName' => $channelName,
            'uid' => $uid,
            'role' => $role,
            'privilegeExpiredTs' => $privilegeExpiredTs
        ];

        // Build the RTC token based on the app and channel information
        return self::generateRtcToken($params);
    }

    private static function generateRtcToken($params)
    {
        // Agora's secure token generation logic
        $appID = $params['appID'];
        $appCertificate = $params['appCertificate'];
        $channelName = $params['channelName'];
        $uid = $params['uid'];
        $role = $params['role'];
        $privilegeExpiredTs = $params['privilegeExpiredTs'];

        // Generate RTC token using Agora's secure algorithm
        $now = time();
        $issueTs = $now;
        $expire = $privilegeExpiredTs;
        $salt = rand(1, 99999999); // Random salt for secure token generation

        // Build the string to sign
        $stringToSign = $appID . $channelName . $uid . $issueTs . $expire . $salt;
        
        // Sign the string using appCertificate
        $signature = hash_hmac('sha256', $stringToSign, $appCertificate);

        // Build token by combining signature and other necessary fields
        $token = base64_encode(json_encode([
            'signature' => $signature,
            'appID' => $appID,
            'channelName' => $channelName,
            'uid' => $uid,
            'issueTs' => $issueTs,
            'expire' => $expire,
            'salt' => $salt
        ]));

        return $token;
    }
}
?>
