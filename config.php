<?php
/**
 * Configuration File - SAFE MODE
 * Optimized for Aiven MySQL and Cloud Deployment
 */

// ป้องกันการเข้าถึงไฟล์ตรงๆ
define('SECURE_ACCESS', true);

// 1. Database Credentials (ดึงจาก Environment Variables บน Render หรือ Aiven)
$db_config = [
    'DB_HOST' => getenv('DB_HOST') ?: 'regis-event-database-pingchayodom2323-ef66.g.aivencloud.com',
    'DB_USER' => getenv('DB_USER') ?: 'avnadmin',
    'DB_PASS' => getenv('DB_PASS') ?: 'AVNS_SZO940c_RwZ4tG4EUlD',
    'DB_NAME' => getenv('DB_NAME') ?: 'defaultdb',
    'DB_PORT' => getenv('DB_PORT') ?: '13028'
];

define('DB_HOST', $db_config['DB_HOST']);
define('DB_USER', $db_config['DB_USER']);
define('DB_PASS', $db_config['DB_PASS']);
define('DB_NAME', $db_config['DB_NAME']);
define('DB_PORT', (int)$db_config['DB_PORT']);

// 2. Global Settings
date_default_timezone_set("Asia/Bangkok");
error_reporting(0); 
ini_set('display_errors', 0);

// 3. Database Connection Helper (รองรับ SSL และ Port สำหรับ Aiven)
function get_db_connection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = mysqli_init();
            // Aiven MySQL มักต้องการการเชื่อมต่อแบบ SSL
            mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
            
            $success = @mysqli_real_connect(
                $conn, 
                DB_HOST, 
                DB_USER, 
                DB_PASS, 
                DB_NAME, 
                DB_PORT,
                NULL,
                MYSQLI_CLIENT_SSL
            );

            if (!$success) {
                error_log("MySQL Connection Error: " . mysqli_connect_error());
                return null;
            }

            $conn->set_charset("utf8mb4");
            $conn->query("SET time_zone = '+07:00'");
        } catch (Exception $e) {
            error_log("Database Exception: " . $e->getMessage());
            return null;
        }
    }
    return $conn;
}

// 4. Response Helper
function send_json_response($success, $message, $extra = []) {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(array_merge(["success" => $success, "message" => $message], $extra));
    exit;
}

// 5. Security Helpers
function h($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }

function check_auth() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}
?>
