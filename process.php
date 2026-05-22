<?php
date_default_timezone_set("Asia/Bangkok");

if (!ob_start()) {
    ob_start();
}
ob_clean();
header('Content-Type: application/json; charset=utf-8');

$conn = new mysqli("localhost", "root", "", "school_register");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "ฐานข้อมูลเชื่อมต่อล้มเหลว"]);
    exit;
}
$conn->set_charset("utf8mb4");

$action = $_GET['action'] ?? '';

if ($action == 'check') {
    $code = trim($_GET['code'] ?? '');
    $code = sprintf("%04d", intval($code)); // เติมเลข 0 ข้างหน้าให้ครบ 4 หลักอัตโนมัติ

    $stmt = $conn->prepare("SELECT student_name, parent_name, status FROM students_import WHERE registration_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if ($row['status'] == 'ลงทะเบียนแล้ว') {
            echo json_encode(["success" => false, "message" => "รหัสนี้ลงทะเบียนซ้ำแล้ว"]);
        } else {
            echo json_encode([
                "success" => true,
                "student_name" => $row['student_name'],
                "parent_name" => $row['parent_name']
            ]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ไม่พบเลขลงทะเบียน " . $code]);
    }
    $stmt->close();
    exit;
}

if ($action == 'register') {
    $code = trim($_POST['modal_reg_code'] ?? '');
    $code = sprintf("%04d", intval($code));
    $parent_name = trim($_POST['modal_parent_name'] ?? '');

    // ปรับปรุงคำสั่ง UPDATE เอาช่อง registered_at ออก เพื่อให้ทำงานร่วมกับตาราง 5 คอลัมน์ใหม่ได้สมบูรณ์
    $stmt = $conn->prepare("UPDATE students_import SET parent_name = ?, status = 'ลงทะเบียนแล้ว', registered_at = NOW() WHERE registration_code = ? AND status = 'ยังไม่ลงทะเบียน'");
    $stmt->bind_param("ss", $parent_name, $code);

    if ($stmt->execute() && $conn->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "บันทึกสำเร็จ"]);
    } else {
        echo json_encode(["success" => false, "message" => "บันทึกไม่สำเร็จหรือลงทะเบียนซ้ำไปแล้ว"]);
    }
    $stmt->close();
    exit;
}
$conn->close();
?>