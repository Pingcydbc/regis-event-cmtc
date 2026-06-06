<?php
/**
 * Configuration File - SAFE MODE
 * Simplified for shared hosting compatibility
 */

// ป้องกันการเข้าถึงไฟล์ตรงๆ
define('SECURE_ACCESS', true);

// 1. Database Credentials (Hardcoded Fallback for Stability)
// หมายเหตุ: ในระบบจริงเราใช้ .env แต่ถ้า Server มีข้อจำกัดเราจะใช้ค่าเหล่านี้
$db_config = [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'school_register'
];

// ลองโหลดจาก .env ถ้าทำได้ (แบบปลอดภัยที่สุด)
$env_path = __DIR__ . '/.env';
if (file_exists($env_path) && is_readable($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') continue;
            $pos = strpos($line, '=');
            if ($pos !== false) {
                $name = trim(substr($line, 0, $pos));
                $value = trim(substr($line, $pos + 1));
                if (!empty($name)) $db_config[$name] = $value;
            }
        }
    }
}

define('DB_HOST', $db_config['DB_HOST']);
define('DB_USER', $db_config['DB_USER']);
define('DB_PASS', $db_config['DB_PASS']);
define('DB_NAME', $db_config['DB_NAME']);

// 2. Global Settings
date_default_timezone_set("Asia/Bangkok");
error_reporting(0); 
ini_set('display_errors', 0);

// 3. Database Connection Helper
function get_db_connection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            if ($conn->connect_error) return null;
            $conn->set_charset("utf8mb4");
            $conn->query("SET time_zone = '+07:00'");
        } catch (Exception $e) {
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
