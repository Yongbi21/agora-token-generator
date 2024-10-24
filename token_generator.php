<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;

class ChannelManager {
    private $prefix;
    private $channelLength;
    private $reservedChannels = [];
    private $usedChannels = [];
    private $maxAttempts = 10;

    public function __construct($prefix = 'channel_', $channelLength = 8) {
        $this->prefix = $prefix;
        $this->channelLength = $channelLength;
    }

    public function generateChannelName($customPrefix = null) {
        $attempts = 0;
        do {
            if ($attempts >= $this->maxAttempts) {
                throw new Exception("Failed to generate unique channel name after {$this->maxAttempts} attempts");
            }

            $channelName = $this->createChannelName($customPrefix);
            $attempts++;
        } while ($this->isChannelUsed($channelName));

        $this->usedChannels[$channelName] = time();
        return $channelName;
    }

    private function createChannelName($customPrefix = null) {
        $prefix = $customPrefix ?? $this->prefix;
        $randomPart = $this->generateRandomString();
        $timestamp = substr(time(), -4);
        return $prefix . $randomPart . '_' . $timestamp;
    }

    private function generateRandomString() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        
        for ($i = 0; $i < $this->channelLength; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        return $randomString;
    }

    private function isChannelUsed($channelName) {
        if (in_array($channelName, $this->reservedChannels)) {
            return true;
        }

        $this->cleanupOldChannels();
        return isset($this->usedChannels[$channelName]);
    }

    private function cleanupOldChannels() {
        $currentTime = time();
        foreach ($this->usedChannels as $channel => $timestamp) {
            if ($currentTime - $timestamp > 86400) {
                unset($this->usedChannels[$channel]);
            }
        }
    }

    public function reserveChannel($channelName) {
        if (!in_array($channelName, $this->reservedChannels)) {
            $this->reservedChannels[] = $channelName;
        }
    }
}

class AgoraTokenManager {
    private $appID;
    private $appCertificate;
    private $channelName;
    private $uid;
    private $role;
    private $expireTimeInSeconds;
    private $token;
    private $tokenGenerationTime;
    private $channelManager;

    public function __construct() {
        // Load .env file
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Initialize properties
        $this->appID = $_ENV['AGORA_APP_ID'];
        $this->appCertificate = $_ENV['AGORA_APP_CERTIFICATE'];
        $this->expireTimeInSeconds = $_ENV['TOKEN_EXPIRY_SECONDS'] ?? 300;
        $this->role = RtcTokenBuilder::ROLE_PUBLISHER;
        $this->channelManager = new ChannelManager(
            $_ENV['CHANNEL_PREFIX'] ?? 'meeting_',
            $_ENV['CHANNEL_LENGTH'] ?? 8
        );
    }

    public function getToken($channelName = null, $uid = null) {
        try {
            // Generate or use provided channel name
            $this->channelName = $channelName ?? $this->channelManager->generateChannelName();
            $this->uid = $uid ?? rand(1, 999999);

            // Log the channel name being used
            error_log("Channel Name Generated/Provided: " . $this->channelName);

            // Check if we need to generate a new token
            if ($this->shouldRegenerateToken()) {
                $this->generateNewToken();
            }

            return [
                'status' => 'success',
                'token' => $this->token,
                'uid' => $this->uid,
                'expiresIn' => $this->getTokenRemainingTime(),
                'channelName' => $this->channelName,
                'timestamp' => time()
            ];
        } catch (Exception $e) {
            error_log("Error in getToken: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function shouldRegenerateToken() {
        if (empty($this->token) || empty($this->tokenGenerationTime)) {
            return true;
        }

        $remainingTime = $this->getTokenRemainingTime();
        return $remainingTime <= 300;
    }

    private function generateNewToken() {
        require_once('RtcTokenBuilder.php');
        
        $this->tokenGenerationTime = (new DateTime())->getTimestamp();
        $privilegeExpiredTs = $this->tokenGenerationTime + $this->expireTimeInSeconds;
        
        $this->token = RtcTokenBuilder::buildTokenWithUid(
            $this->appID,
            $this->appCertificate,
            $this->channelName,
            $this->uid,
            $this->role,
            $privilegeExpiredTs
        );
    }

    private function getTokenRemainingTime() {
        if (empty($this->tokenGenerationTime)) {
            return 0;
        }
        
        $currentTime = (new DateTime())->getTimestamp();
        $remainingTime = ($this->tokenGenerationTime + $this->expireTimeInSeconds) - $currentTime;
        
        return max(0, $remainingTime);
    }
}