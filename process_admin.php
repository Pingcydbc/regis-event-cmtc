<?php
header('Content-Type: application/json; charset=utf-8');
date_default_timezone_set("Asia/Bangkok");

$conn = new mysqli("sql311.infinityfree.com", "if0_41990714", "HduJK1lBcv", "if0_41990714_school_register");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "ฐานข้อมูลเชื่อมต่อล้มเหลว"]);
    exit;
}
$conn->set_charset("utf8mb4");

$action = $_GET['action'] ?? '';

if ($action == 'download_template') {
    $filename = "แบบฟอร์มข้อมูลนักเรียน_9คอลัมน์_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['registration_code', 'student_id', 'student_name', 'parent_name', 'status', 'level', 'room', 'department', 'registered_at']);
    fputcsv($output, ['1', '68409010001', 'นายสมชาย รักดี', '', 'ยังไม่ลงทะเบียน', 'ปวช.1', '1', 'เทคโนโลยีสารสนเทศ', '']);
    fclose($output);
    exit;
}

if ($action == 'import_data' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "กรุณาเลือกไฟล์ที่ถูกต้องสำหรับการนำเข้าข้อมูล"]);
        exit;
    }

    $file_path = $_FILES['excel_file']['tmp_name'];
    $file_content = file_get_contents($file_path);
    $file_content = str_replace("\xEF\xBB\xBF", "", $file_content);
    
    $file_handle = fopen('php://memory', 'r+');
    fwrite($file_handle, $file_content);
    rewind($file_handle);
    fgetcsv($file_handle);
    
    $conn->query("TRUNCATE TABLE students_import");

    $stmt = $conn->prepare("INSERT INTO students_import (registration_code, student_id, student_name, parent_name, status, level, room, department, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL)");
    
    $success_count = 0;
    while (($row = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
        $reg_code    = isset($row[0]) ? trim($row[0]) : '';
        $student_id  = isset($row[1]) ? trim($row[1]) : '';
        $student_name= isset($row[2]) ? trim($row[2]) : '';
        $parent_name = isset($row[3]) ? trim($row[3]) : '';
        $status      = isset($row[4]) && !empty(trim($row[4])) ? trim($row[4]) : 'ยังไม่ลงทะเบียน';
        $level       = isset($row[5]) ? trim($row[5]) : '';
        $room        = isset($row[6]) ? trim($row[6]) : '';
        $department  = isset($row[7]) ? trim($row[7]) : '';

        if (empty($reg_code) && empty($student_name)) continue;

        if (is_numeric($reg_code)) {
            $reg_code = sprintf("%04d", intval($reg_code));
        }

        $stmt->bind_param("ssssssss", $reg_code, $student_id, $student_name, $parent_name, $status, $level, $room, $department);
        $stmt->execute();
        $success_count++;
    }

    fclose($file_handle);
    $stmt->close();
    
    if ($success_count > 0) {
        echo json_encode(["success" => true, "message" => "อิมพอร์ตข้อมูลรายชื่อนักเรียนชุดใหม่สำเร็จจำนวนทั้งหมด " . $success_count . " รายชื่อเรียบร้อยครับ"]);
    } else {
        echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลรายชื่อภายในไฟล์ หรือประเภทไฟล์เซฟมาไม่ถูกต้อง"]);
    }
    $conn->close();
    exit;
}
?>