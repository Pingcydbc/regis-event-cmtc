<?php
require_once 'config.php';
if (!defined('SECURE_ACCESS')) { die('Direct access not permitted'); }

$conn = get_db_connection();
if (!$conn) {
    send_json_response(false, "ฐานข้อมูลเชื่อมต่อล้มเหลว");
}

$action = $_GET['action'] ?? '';

// 1. ฟังก์ชันเช็กข้อมูลรหัสนักศึกษา หรือ เลขบัตรประชาชน
if ($action == 'check') {
    $input_id = trim($_GET['id_card'] ?? $_GET['student_id'] ?? '');

    if (empty($input_id)) {
        send_json_response(false, "กรุณากรอกเลขประจำตัวประชาชน หรือ รหัสนักศึกษา");
    }

    $stmt = $conn->prepare("SELECT student_id, id_card, student_name, parent_name, status, group_name, department FROM students_import WHERE id_card = ? OR student_id = ?");
    $stmt->bind_param("ss", $input_id, $input_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'ลงทะเบียนแล้ว') {
            send_json_response(false, "เลขบัตรนี้ได้ทำการลงทะเบียนเสร็จสิ้นไปแล้วครับ");
        } else {
            send_json_response(true, "พบข้อมูล", [
                "student_id"   => $row['student_id'],
                "id_card"      => $row['id_card'],
                "student_name" => $row['student_name'],
                "department"   => !empty($row['department']) ? $row['department'] : "ไม่ระบุแผนก",
                "group_name"   => !empty($row['group_name']) ? $row['group_name'] : "ไม่ระบุกลุ่มเรียน",
                "parent_name"  => $row['parent_name']
            ]);
        }
    } else {
        send_json_response(false, "ไม่พบข้อมูล " . $input_id . " ในระบบ");
    }
    $stmt->close();
}

// 2. ฟังก์ชันอัปเดตบันทึกข้อมูลการลงทะเบียน
if ($action == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_id = trim($_POST['modal_id_card_val'] ?? '');
    $parent_name = trim($_POST['modal_parent_name'] ?? '');

    if (empty($input_id)) {
        send_json_response(false, "ไม่พบข้อมูลประจำตัว (กรุณาลองตรวจสอบใหม่อีกครั้ง)");
    }

    if (empty($parent_name)) {
        send_json_response(false, "กรุณากรอกชื่อผู้ปกครอง");
    }

    $stmt = $conn->prepare("UPDATE students_import SET parent_name = ?, status = 'ลงทะเบียนแล้ว', registered_at = NOW() WHERE (student_id = ? OR id_card = ?) AND status != 'ลงทะเบียนแล้ว'");
    
    if (!$stmt) {
        send_json_response(false, "Database preparation failed: " . $conn->error);
    }

    $stmt->bind_param("sss", $parent_name, $input_id, $input_id);
    
    try {
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            send_json_response(true, "ผู้ปกครองทำรายการลงทะเบียนเสร็จเรียบร้อยแล้วครับ");
        } else {
            send_json_response(false, "ทำรายการไม่สำเร็จ หรืออาจถูกลงทะเบียนไปก่อนหน้านี้แล้ว");
        }
    } catch (Exception $e) {
        send_json_response(false, "เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage());
    }
    $stmt->close();
}

send_json_response(false, "Invalid Action");
?>
