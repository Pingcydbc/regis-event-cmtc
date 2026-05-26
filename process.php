<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");

// เชื่อมต่อฐานข้อมูลออนไลน์
$conn = new mysqli("sql311.infinityfree.com", "if0_41990714", "HduJK1lBcv", "if0_41990714_school_register");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "ฐานข้อมูลเชื่อมต่อล้มเหลว"]);
    exit;
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+07:00'");

$action = $_GET['action'] ?? '';

// 1. ฟังก์ชันเช็กข้อมูลรหัสนักศึกษา
if ($action == 'check') {
    $student_id = trim($_GET['student_id'] ?? '');

    if (empty($student_id)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกรหัสนักศึกษา"]);
        exit;
    }

    // ดึงค่าตามชื่อคอลัมน์จากตารางจริง
    $stmt = $conn->prepare("SELECT student_id, student_name, parent_name, status, level, room, department FROM students_import WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'ลงทะเบียนแล้ว') {
            echo json_encode(["success" => false, "message" => "รหัสนักศึกษานี้ได้ทำการลงทะเบียนเสร็จสิ้นไปแล้วครับ"]);
        } else {
            echo json_encode([
                "success"      => true,
                "student_id"   => $row['student_id'],
                "student_name" => $row['student_name'],
                "department"   => !empty($row['department']) ? $row['department'] : "ไม่ระบุแผนก",
                "level"        => !empty($row['level']) ? $row['level'] : "ไม่ระบุชั้นปี",
                "room"         => !empty($row['room']) ? $row['room'] : "1",
                "parent_name"  => $row['parent_name']
            ]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ไม่พบรหัสนักศึกษา " . $student_id . " ในระบบ"]);
    }
    $stmt->close();
    exit;
}

// 2. ฟังก์ชันอัปเดตบันทึกข้อมูลการลงทะเบียน
if ($action == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['modal_student_id_val'] ?? '');
    $parent_name = trim($_POST['modal_parent_name'] ?? '');

    if (empty($student_id)) {
        echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลรหัสนักศึกษา"]);
        exit;
    }

    if (empty($parent_name)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อผู้ปกครอง"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE students_import SET parent_name = ?, status = 'ลงทะเบียนแล้ว', registered_at = NOW() WHERE student_id = ? AND status = 'ยังไม่ลงทะเบียน'");
    $stmt->bind_param("ss", $parent_name, $student_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "ผู้ปกครองของนักศึกษาทำรายการลงทะเบียนเสร็จเรียบร้อยแล้วครับ"]);
    } else {
        echo json_encode(["success" => false, "message" => "ทำรายการไม่สำเร็จ อาจถูกลงทะเบียนไปก่อนหน้านี้แล้ว"]);
    }
    $stmt->close();
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid Action"]);
$conn->close();
?>