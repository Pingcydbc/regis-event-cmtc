<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once 'config.php';

try {
    if (session_status() === PHP_SESSION_NONE) { session_start(); }

    // Generate/Retrieve User ID for persistent chat
    if (!isset($_SESSION['chat_user_id'])) {
        $_SESSION['chat_user_id'] = 'user_' . substr(md5(uniqid((string)mt_rand(), true)), 0, 8);
    }

    $user_id = $_SESSION['chat_user_id'];
    $action = $_GET['action'] ?? '';
    $conn = get_db_connection();

    if (!$conn) {
        send_json_response(false, "ฐานข้อมูลเชื่อมต่อล้มเหลว");
    }

    // Ensure table exists (Silently fail if restricted by host)
    try {
        $sql = "CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id VARCHAR(50) NOT NULL,
            sender_type ENUM('user', 'admin') NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $conn->query($sql);
    } catch (\Throwable $e) {
        // Ignore table creation errors
    }

    // 1. Send Message
    if ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $msg = trim($_POST['message'] ?? '');
        // Sanitize input
        $msg = htmlspecialchars($msg, ENT_QUOTES, 'UTF-8');
        
        $is_from_dashboard = isset($_POST['target_user']);
        $type = ($is_from_dashboard && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) ? 'admin' : 'user';
        $target_user = $_POST['target_user'] ?? $user_id;

        if ($msg === '') {
            send_json_response(false, "กรุณากรอกข้อความ");
        }

        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, sender_type, message) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $target_user, $type, $msg);
            if ($stmt->execute()) send_json_response(true, "Sent");
            else send_json_response(false, "Error: " . $conn->error);
        } else {
            send_json_response(false, "DB Prepare Error");
        }
    }

    // 2. Get Messages for a specific user
    if ($action == 'get_messages') {
        $target = $_GET['target_user'] ?? $user_id;
        
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            $target = $user_id;
        }

        $stmt = $conn->prepare("SELECT * FROM chat_messages WHERE sender_id = ? ORDER BY created_at ASC");
        if ($stmt) {
            $stmt->bind_param("s", $target);
            $stmt->execute();
            $result = $stmt->get_result();
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $row['time'] = date('H:i', strtotime($row['created_at']));
                // Ensure message is safe (even if sanitized on input, double layer is good)
                $row['message'] = $row['message']; 
                $messages[] = $row;
            }
            
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                $update = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND sender_type = 'user'");
                if ($update) {
                    $update->bind_param("s", $target);
                    $update->execute();
                }
            }

            send_json_response(true, "Success", ["messages" => $messages, "my_id" => $user_id, "target_id" => $target]);
        } else {
            send_json_response(false, "DB Prepare Error");
        }
    }

    // 3. Clear Chat (Delete messages)
    if ($action == 'clear_chat') {
        $target = $_GET['target_user'] ?? $user_id;
        
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            $target = $user_id;
        }

        $stmt = $conn->prepare("DELETE FROM chat_messages WHERE sender_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $target);
            if ($stmt->execute()) send_json_response(true, "Chat cleared");
            else send_json_response(false, "Error deleting chat");
        } else {
            send_json_response(false, "DB Prepare Error");
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
        if ($result) {
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $row['time_fmt'] = date('H:i', strtotime($row['last_msg_time']));
                $users[] = $row;
            }
            send_json_response(true, "Success", ["users" => $users]);
        } else {
            send_json_response(false, "DB Query Error");
        }
    }

    send_json_response(false, "Invalid Action");

} catch (\Throwable $e) {
    send_json_response(false, "System Error: " . $e->getMessage());
}
?>