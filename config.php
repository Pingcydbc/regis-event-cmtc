<?php
/**
 * Configuration File
 * Centralized database connection and global settings
 */

// ป้องกันการเข้าถึงไฟล์ตรงๆ
define('SECURE_ACCESS', true);

// 1. Database Credentials
define('DB_HOST', 'sql311.infinityfree.com');
define('DB_USER', 'if0_41990714');
define('DB_PASS', 'HduJK1lBcv');
define('DB_NAME', 'if0_41990714_school_register');

// 2. Global Settings
date_default_timezone_set("Asia/Bangkok");
error_reporting(0); // ปิดการแสดง Error หน้าเว็บ (เพื่อความปลอดภัย)
ini_set('display_errors', 0);

// 3. Database Connection Helper
function get_db_connection() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            return null;
        }
        $conn->set_charset("utf8mb4");
        $conn->query("SET time_zone = '+07:00'");
    }
    return $conn;
}

// 4. Response Helper
function send_json_response($success, $message, $extra = []) {
    header('Content-Type: application/json; charset=utf-8');
    $response = array_merge([
        "success" => $success,
        "message" => $message
    ], $extra);
    echo json_encode($response);
    exit;
}

// 5. Auth Helper
function check_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        return false;
    }
    return true;
}
?>
