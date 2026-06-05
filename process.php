<?php
require_once 'config.php';
if (!defined('SECURE_ACCESS')) { die('Direct access not permitted'); }

$conn = get_db_connection();
if (!$conn) {
    send_json_response(false, "ฐานข้อมูลเชื่อมต่อล้มเหลว");
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 1. ฟังก์ชันเช็กข้อมูลรหัสนักศึกษา หรือ เลขบัตรประชาชน
if ($action == 'check') {
    $input_id = trim(isset($_GET['id_card']) ? $_GET['id_card'] : '');

    if (empty($input_id)) {
        send_json_response(false, "กรุณากรอกเลขประจำตัวประชาชน");
    }

    $found_student = null;
    $source_table = '';

    // ขั้นตอนที่ 1: ค้นหาในตารางหลักก่อน
    $stmt1 = $conn->prepare("SELECT student_id, id_card, student_name, parent_name, status, group_name, department FROM students_import WHERE id_card = ? OR student_id = ? LIMIT 1");
    if ($stmt1) {
        $stmt1->bind_param("ss", $input_id, $input_id);
        $stmt1->execute();
        $result1 = $stmt1->get_result();
        if ($row = $result1->fetch_assoc()) {
            $found_student = $row;
            $source_table = 'students_import';
        }
        $stmt1->close();
    }

    // ขั้นตอนที่ 2: ถ้าไม่พบในตารางหลัก ให้ค้นหาในตารางแจ้งเพิ่ม (fix_student)
    if (!$found_student) {
        $stmt2 = $conn->prepare("SELECT student_id, id_card, student_name, parent_name, status, group_name, department FROM fix_student WHERE id_card = ? OR student_id = ? LIMIT 1");
        if ($stmt2) {
            $stmt2->bind_param("ss", $input_id, $input_id);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            if ($row = $result2->fetch_assoc()) {
                $found_student = $row;
                $source_table = 'fix_student';
            }
            $stmt2->close();
        }
    }

    if ($found_student) {
        if ($found_student['status'] == 'ลงทะเบียนแล้ว') {
            send_json_response(false, "เลขบัตรนี้ได้ทำการลงทะเบียนเสร็จสิ้นไปแล้วครับ");
        } else {
            send_json_response(true, "พบข้อมูล", [
                "student_id"   => $found_student['student_id'],
                "id_card"      => $found_student['id_card'],
                "student_name" => $found_student['student_name'],
                "department"   => !empty($found_student['department']) ? $found_student['department'] : "ไม่ระบุแผนก",
                "group_name"   => !empty($found_student['group_name']) ? $found_student['group_name'] : "ไม่ระบุกลุ่มเรียน",
                "parent_name"  => $found_student['parent_name']
            ]);
        }
    } else {
        send_json_response(false, "ไม่พบข้อมูลในระบบ");
    }
}

// 2. ฟังก์ชันอัปเดตบันทึกข้อมูลการลงทะเบียน
if ($action == 'register' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input_id = trim(isset($_POST['modal_id_card_val']) ? $_POST['modal_id_card_val'] : '');
    $parent_name = trim(isset($_POST['modal_parent_name']) ? $_POST['modal_parent_name'] : '');

    if (empty($input_id) || empty($parent_name)) {
        send_json_response(false, "ข้อมูลไม่ครบถ้วน");
    }

    // ตรวจสอบว่าข้อมูลอยู่ในตารางไหน เพื่อที่จะอัปเดตให้ถูกตาราง
    $target_table = 'students_import';
    $check_stmt = $conn->prepare("SELECT id FROM students_import WHERE id_card = ? OR student_id = ? LIMIT 1");
    $check_stmt->bind_param("ss", $input_id, $input_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        // ถ้าไม่เจอในตารางหลัก ให้ลองดูในตารางแจ้งเพิ่ม
        $target_table = 'fix_student';
    }
    $check_stmt->close();

    $stmt = $conn->prepare("UPDATE $target_table SET parent_name = ?, status = 'ลงทะเบียนแล้ว', registered_at = NOW() WHERE (student_id = ? OR id_card = ?) AND status != 'ลงทะเบียนแล้ว'");
    
    if ($stmt) {
        $stmt->bind_param("sss", $parent_name, $input_id, $input_id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            send_json_response(true, "ทำรายการลงทะเบียนเสร็จเรียบร้อยแล้วครับ");
        } else {
            send_json_response(false, "ทำรายการไม่สำเร็จ หรืออาจถูกลงทะเบียนไปก่อนหน้านี้แล้ว");
        }
        $stmt->close();
    } else {
        send_json_response(false, "Database Error: " . $conn->error);
    }
}

send_json_response(false, "Invalid Action");
?>
