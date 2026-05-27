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
    $filename = "แบบฟอร์มข้อมูลนักเรียน_10คอลัมน์_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');
    // ลำดับ 10 คอลัมน์ที่ต้องการ: registration_code, student_id, id_card, student_name, level, department, room, parent_name, status, registered_at
    fputcsv($output, ['registration_code', 'student_id', 'id_card', 'student_name', 'level', 'department', 'room', 'parent_name', 'status', 'registered_at']);
    fputcsv($output, ['1', '68409010001', '1509901234567', 'นายสมชาย รักดี', 'ปวช.1', 'เทคโนโลยีสารสนเทศ', '1', '', 'ยังไม่ลงทะเบียน', '']);
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
    fgetcsv($file_handle); // ข้ามหัวตาราง

    $conn->query("TRUNCATE TABLE students_import");

    // เตรียม Statement ตามลำดับใหม่
    $stmt = $conn->prepare("INSERT INTO students_import (registration_code, student_id, id_card, student_name, level, department, room, parent_name, status, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)");

    $success_count = 0;
    while (($row = fgetcsv($file_handle, 1000, ",")) !== FALSE) {
        // บังคับใช้ลำดับ 10 คอลัมน์ที่กำหนดเท่านั้น
        $reg_code     = isset($row[0]) ? trim($row[0]) : '';
        $student_id   = isset($row[1]) ? trim($row[1]) : '';
        $id_card      = isset($row[2]) ? trim($row[2]) : '';
        $student_name = isset($row[3]) ? trim($row[3]) : '';
        $level        = isset($row[4]) ? trim($row[4]) : '';
        $department   = isset($row[5]) ? trim($row[5]) : '';
        $room         = isset($row[6]) ? trim($row[6]) : '';
        $parent_name  = isset($row[7]) ? trim($row[7]) : '';
        $status       = (isset($row[8]) && !empty(trim($row[8]))) ? trim($row[8]) : 'ยังไม่ลงทะเบียน';

        if (empty($reg_code) && empty($student_name)) continue;
        if (strpos($level, 'ป.ตรี') !== false) continue; // ข้าม ป.ตรี

        if (is_numeric($reg_code)) {
            $reg_code = sprintf("%04d", intval($reg_code));
        }

        $stmt->bind_param("sssssssss", $reg_code, $student_id, $id_card, $student_name, $level, $department, $room, $parent_name, $status);
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

// 3. ดึงข้อมูลตัวเลือกสำหรับ Filter (แผนก และ ชั้นปี)
if ($action == 'get_filters') {
    $depts = [];
    $levels = [];
    $rooms = [];

    $res_dept = $conn->query("SELECT DISTINCT department FROM students_import WHERE department != '' ORDER BY department ASC");
    while($r = $res_dept->fetch_assoc()) $depts[] = $r['department'];

    $res_level = $conn->query("SELECT DISTINCT level FROM students_import WHERE level != '' AND level NOT LIKE '%ป.ตรี%' ORDER BY level ASC");
    while($r = $res_level->fetch_assoc()) $levels[] = $r['level'];

    $res_room = $conn->query("SELECT DISTINCT room FROM students_import WHERE room != '' ORDER BY room ASC");
    while($r = $res_room->fetch_assoc()) $rooms[] = $r['room'];

    echo json_encode([
        "success" => true,
        "departments" => $depts,
        "levels" => $levels,
        "rooms" => $rooms
    ]);
    exit;
}

// 4. ดึงสถิติการลงทะเบียนตาม Filter
if ($action == 'get_stats') {
    $dept = $_GET['department'] ?? '';
    $level = $_GET['level'] ?? '';
    $room = $_GET['room'] ?? '';
    $level_group = $_GET['level_group'] ?? ''; // ปวช หรือ ปวส

    $where = " WHERE level NOT LIKE '%ป.ตรี%' ";
    $params = [];
    $types = "";

    if (!empty($dept)) {
        $where .= " AND department = ? ";
        $params[] = $dept;
        $types .= "s";
    }
    if (!empty($level)) {
        $where .= " AND level = ? ";
        $params[] = $level;
        $types .= "s";
    }
    if (!empty($room)) {
        $where .= " AND room = ? ";
        $params[] = $room;
        $types .= "s";
    }
    if (!empty($level_group)) {
        $where .= " AND level LIKE ? ";
        $params[] = $level_group . "%";
        $types .= "s";
    }

    $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ลงทะเบียนแล้ว' THEN 1 ELSE 0 END) as registered
            FROM students_import $where";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    // เพิ่มเติมสถิติแยก ปวช / ปวส
    $sql_groups = "SELECT 
                    SUM(CASE WHEN level LIKE 'ปวช%' THEN 1 ELSE 0 END) as pvc_total,
                    SUM(CASE WHEN level LIKE 'ปวช%' AND status = 'ลงทะเบียนแล้ว' THEN 1 ELSE 0 END) as pvc_reg,
                    SUM(CASE WHEN level LIKE 'ปวส%' THEN 1 ELSE 0 END) as pvs_total,
                    SUM(CASE WHEN level LIKE 'ปวส%' AND status = 'ลงทะเบียนแล้ว' THEN 1 ELSE 0 END) as pvs_reg
                  FROM students_import WHERE level NOT LIKE '%ป.ตรี%'";
    $groups = $conn->query($sql_groups)->fetch_assoc();

    echo json_encode([
        "success" => true,
        "total" => (int)$result['total'],
        "registered" => (int)$result['registered'],
        "not_registered" => (int)$result['total'] - (int)$result['registered'],
        "groups" => [
            "pvc" => ["total" => (int)$groups['pvc_total'], "reg" => (int)$groups['pvc_reg']],
            "pvs" => ["total" => (int)$groups['pvs_total'], "reg" => (int)$groups['pvs_reg']]
        ]
    ]);
    exit;
}
?>