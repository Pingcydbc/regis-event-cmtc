<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");

// เชื่อมต่อฐานข้อมูลออนไลน์ InfinityFree
$conn = new mysqli("sql311.infinityfree.com", "if0_41990714", "HduJK1lBcv", "if0_41990714_school_register");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "ฐานข้อมูลเชื่อมต่อล้มเหลว"]);
    exit;
}
$conn->set_charset("utf8mb4");

// ⚡ บังคับฐานข้อมูล MySQL ให้ใช้เวลาโซนประเทศไทย (+07:00)
$conn->query("SET time_zone = '+07:00'");

$action = $_GET['action'] ?? '';

// 1. ตรวจสอบข้อมูลรหัสคิว
if ($action == 'check') {
    $code = trim($_GET['code'] ?? '');
    $code = sprintf("%04d", intval($code));

    $stmt = $conn->prepare("SELECT student_id, student_name, parent_name, status FROM students_import WHERE registration_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'ลงทะเบียนแล้ว') {
            echo json_encode(["success" => false, "message" => "รหัสคิวนี้ได้ทำการลงทะเบียนเสร็จสิ้นไปแล้วครับ"]);
        } else {
            echo json_encode([
                "success" => true,
                "student_id" => $row['student_id'],
                "student_name" => $row['student_name'],
                "parent_name" => $row['parent_name']
            ]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ไม่พบเลขรหัสคิวลงทะเบียน " . $code . " ในระบบ"]);
    }
    $stmt->close();
    exit;
}

// 2. บันทึกข้อมูลการลงทะเบียน
if ($action == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['modal_reg_code'] ?? '');
    $code = sprintf("%04d", intval($code));
    $parent_name = trim($_POST['modal_parent_name'] ?? '');

    if (empty($parent_name)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อผู้ปกครอง"]);
        exit;
    }

    // บันทึกเวลาประเทศไทยลงคอลัมน์ registered_at โดยตรงด้วย NOW()
    $stmt = $conn->prepare("UPDATE students_import SET parent_name = ?, status = 'ลงทะเบียนแล้ว', registered_at = NOW() WHERE registration_code = ? AND status = 'ยังไม่ลงทะเบียน'");
    $stmt->bind_param("ss", $parent_name, $code);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "ผู้ปกครองของนักศึกษาทำรายการสำเร็จแล้ว"]);
    } else {
        echo json_encode(["success" => false, "message" => "ทำรายการไม่สำเร็จ รหัสนี้อาจถูกลงทะเบียนตัดหน้าไปก่อนแล้ว"]);
    }
    $stmt->close();
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid Action"]);
$conn->close();
?>