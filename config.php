<?php
// Database configuration
define('DB_HOST', 'localhost');     // Your database host
define('DB_USER', 'admin');          // Your database username
define('DB_PASS', 'Ajrxx143-');              // Your database password
define('DB_NAME', 'crimsgsx_mindpath_db'); // Your database name

// Other configurations
define('UPLOAD_DIR', 'uploads/profile_images/'); // Directory for uploaded images
define('SITE_URL', 'https://api.crimsonflare.com');  // Your site URL

// Optional: Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Optional: Set timezone
date_default_timezone_set('Asia/Manila'); // Change to your timezone

// Optional: Maximum file size (in bytes)
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Optional: Allowed file types
define('ALLOWED_TYPES', [
    'image/jpeg',
    'image/png',
    'image/jpg'
]);
