<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Ensure table exists
function ensure_chat_table_exists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id VARCHAR(50) NOT NULL,
        sender_type ENUM('user', 'admin') NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql);
}

// Generate/Retrieve User ID for persistent chat
if (!isset($_SESSION['chat_user_id'])) {
    $_SESSION['chat_user_id'] = 'user_' . substr(md5(uniqid(mt_rand(), true)), 0, 8);
}

$user_id = $_SESSION['chat_user_id'];
$action = $_GET['action'] ?? '';
$conn = get_db_connection();

if (!$conn) {
    send_json_response(false, "ฐานข้อมูลเชื่อมต่อล้มเหลว");
}

// ตรวจสอบและสร้างตารางทันทีเพื่อกัน Error 500 เวลา Query
ensure_chat_table_exists($conn);

// 1. Send Message
if ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg = trim($_POST['message'] ?? '');
    
    // แยกแยะว่าส่งมาจากหน้า Dashboard (มี target_user) หรือส่งจากหน้าแรก (ไม่มี target_user)
    $is_from_dashboard = isset($_POST['target_user']);
    $type = ($is_from_dashboard && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) ? 'admin' : 'user';
    
    $target_user = $_POST['target_user'] ?? $user_id;

    if ($msg === '') {
        send_json_response(false, "กรุณากรอกข้อความ");
    }

    $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, sender_type, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $target_user, $type, $msg);
    
    if ($stmt->execute()) {
        send_json_response(true, "Sent");
    } else {
        send_json_response(false, "Error: " . $conn->error);
    }
}

// 2. Get Messages for a specific user
if ($action == 'get_messages') {
    $target = $_GET['target_user'] ?? $user_id;
    
    // If it's a user requesting, they can only see their own chat
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        $target = $user_id;
    }

    $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE sender_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("s", $target);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $row['time'] = date('H:i', strtotime($row['created_at']));
        $messages[] = $row;
    }
    
    // Mark as read if admin is viewing
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $update = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND sender_type = 'user'");
        $update->bind_param("s", $target);
        $update->execute();
    }

    send_json_response(true, "Success", ["messages" => $messages, "my_id" => $user_id, "target_id" => $target]);
}

// 3. Clear Chat (Delete messages)
if ($action == 'clear_chat') {
    $target = $_GET['target_user'] ?? $user_id;
    
    // If it's a user requesting, they can only clear their own chat
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        $target = $user_id;
    }

    $stmt = $conn->prepare("DELETE FROM chat_messages WHERE sender_id = ?");
    $stmt->bind_param("s", $target);
    
    if ($stmt->execute()) {
        send_json_response(true, "Chat cleared");
    } else {
        send_json_response(false, "Error deleting chat");
    }
}

// 4. Get list of users who have chatted (Admin only)
if ($action == 'get_chat_users') {
    if (!check_auth()) send_json_response(false, "Unauthorized");

    $sql = "SELECT sender_id, MAX(created_at) as last_msg_time, 
            SUM(CASE WHEN is_read = 0 AND sender_type = 'user' THEN 1 ELSE 0 END) as unread_count,
            (SELECT message FROM chat_messages m2 WHERE m2.sender_id = m1.sender_id ORDER BY created_at DESC LIMIT 1) as last_message
            FROM chat_messages m1 
            GROUP BY sender_id 
            ORDER BY last_msg_time DESC";
    
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $row['time_fmt'] = date('H:i', strtotime($row['last_msg_time']));
        $users[] = $row;
    }
    send_json_response(true, "Success", ["users" => $users]);
}

send_json_response(false, "Invalid Action");
?>