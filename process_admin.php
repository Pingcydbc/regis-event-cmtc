<?php
require_once 'config.php';
if (!defined('SECURE_ACCESS')) { die('Direct access not permitted'); }

$conn = get_db_connection();
if (!$conn) {
    send_json_response(false, "ฐานข้อมูลเชื่อมต่อล้มเหลว");
}

$action = $_GET['action'] ?? '';

// 1. ดาวน์โหลดเทมเพลต CSV
if ($action == 'download_template') {
    $filename = "แบบฟอร์มข้อมูลนักเรียน_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');
    // คืนค่าหัวข้อตารางเพื่อให้คนกรอกข้อมูลรู้ว่าช่องไหนคืออะไร
    fputcsv($output, ['รหัสคิว', 'รหัสผู้เรียน', 'เลขบัตรประชาชน', 'ชื่อผู้เรียน', 'ชื่อผู้ปกครอง', 'ที่อยู่', 'โทรศัพท์ผู้ปกครอง', 'รหัสกลุ่มเรียน', 'ชื่อกลุ่มเรียน', 'สาขาวิชา']);
    fputcsv($output, ['1', '68409010001', '1509901234567', 'นายสมชาย รักดี', 'นายสมบูรณ์ รักดี', '123 ม.1 ต.ช้างเผือก อ.เมือง จ.เชียงใหม่', '0812345678', '673090101', 'IT.67.1', 'เทคโนโลยีสารสนเทศ']);
    fclose($output);
    exit;
}

// 2. อิมพอร์ตข้อมูลจาก CSV
if ($action == 'import_data' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] != UPLOAD_ERR_OK) {
        send_json_response(false, "กรุณาเลือกไฟล์ที่ถูกต้องสำหรับการนำเข้าข้อมูล");
    }

    $file_path = $_FILES['excel_file']['tmp_name'];
    $file_content = file_get_contents($file_path);
    
    // ล้างค่าอักขระขยะ BOM ของ Excel ออก
    $file_content = str_replace("\xEF\xBB\xBF", "", $file_content);

    $file_handle = fopen('php://memory', 'r+');
    fwrite($file_handle, $file_content);
    rewind($file_handle);
    
    // ล้างตารางเดิมออกเพื่ออัปเดตข้อมูลชุดใหม่
    $conn->query("TRUNCATE TABLE students_import");

    $stmt = $conn->prepare("INSERT INTO students_import (registration_code, student_id, id_card, student_name, parent_name, address, parent_phone, group_id, group_name, department, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ยังไม่ลงทะเบียน')");

    $success_count = 0;
    $is_first_row = true; // 🌟 ใช้ตัวแปร Flag ตัวนี้ช่วยล็อกและเตะแถวที่ 1 ทิ้งชัวร์ ๆ

    while (($row = fgetcsv($file_handle, 2000, ",")) !== FALSE) {
        // ถ้าเป็นแถวแรก (แถวหัวข้อ) ให้ข้ามไปทันที ห้ามเอาไปรัน SQL บรรทัดล่าง
        if ($is_first_row) {
            $is_first_row = false;
            continue;
        }

        // เริ่มดึงข้อมูลจากคอลัมน์จริง (แถวที่ 2 เป็นต้นไป)
        $reg_code     = isset($row[0]) ? trim($row[0]) : '';
        $student_id   = isset($row[1]) ? trim($row[1]) : '';
        $id_card      = isset($row[2]) ? trim($row[2]) : '';
        $student_name = isset($row[3]) ? trim($row[3]) : '';
        $parent_name  = isset($row[4]) ? trim($row[4]) : '';
        $address      = isset($row[5]) ? trim($row[5]) : '';
        $parent_phone = isset($row[6]) ? trim($row[6]) : '';
        $group_id     = isset($row[7]) ? trim($row[7]) : '';
        $group_name   = isset($row[8]) ? trim($row[8]) : '';
        $department   = isset($row[9]) ? trim($row[9]) : '';

        // 🌟 แก้ไขปัญหาเลขบัตร หรือ รหัส นศ. เป็นตัวเลขยกกำลัง (Scientific Notation เช่น 1.5E+12)
        if (stripos($id_card, 'E+') !== false) {
            $id_card = number_format((float)$id_card, 0, '', '');
        }
        if (stripos($student_id, 'E+') !== false) {
            $student_id = number_format((float)$student_id, 0, '', '');
        }
        if (stripos($reg_code, 'E+') !== false) {
            $reg_code = number_format((float)$reg_code, 0, '', '');
        }

        // ถ้าไม่มีทั้งรหัสนักศึกษาและชื่อนักศึกษา ให้ข้ามแถวนั้นไป (ป้องกันแถวว่างท้ายไฟล์)
        if (empty($student_id) && empty($student_name)) continue;

        $stmt->bind_param("ssssssssss", $reg_code, $student_id, $id_card, $student_name, $parent_name, $address, $parent_phone, $group_id, $group_name, $department);
        $stmt->execute();
        $success_count++;
    }

    fclose($file_handle);
    $stmt->close();

    if ($success_count > 0) {
        send_json_response(true, "อิมพอร์ตข้อมูลใหม่สำเร็จจำนวน " . $success_count . " รายชื่อ");
    } else {
        send_json_response(false, "ไม่พบข้อมูลในไฟล์ที่นำเข้า");
    }
}
// 3. ดึงข้อมูลตัวเลือกสำหรับ Filter
if ($action == 'get_filters') {
    $depts = [];
    $groups = [];
    $dept_filter = $_GET['department'] ?? '';

    $res_dept = $conn->query("SELECT DISTINCT department FROM students_import WHERE department != '' ORDER BY department ASC");
    while($r = $res_dept->fetch_assoc()) $depts[] = $r['department'];

    if (!empty($dept_filter)) {
        $stmt = $conn->prepare("SELECT DISTINCT group_name FROM students_import WHERE department = ? AND group_name != '' ORDER BY group_name ASC");
        $stmt->bind_param("s", $dept_filter);
        $stmt->execute();
        $res_group = $stmt->get_result();
    } else {
        $res_group = $conn->query("SELECT DISTINCT group_name FROM students_import WHERE group_name != '' ORDER BY group_name ASC");
    }
    
    while($r = $res_group->fetch_assoc()) $groups[] = $r['group_name'];

    send_json_response(true, "Filters retrieved", [
        "departments" => $depts,
        "groups" => $groups
    ]);
}

// 4. ดึงสถิติการลงทะเบียนตาม Filter
if ($action == 'get_stats') {
    $dept = $_GET['department'] ?? '';
    $group = $_GET['group_name'] ?? '';

    $where = " WHERE 1=1 ";
    $params = [];
    $types = "";

    if (!empty($dept)) {
        $where .= " AND department = ? ";
        $params[] = $dept;
        $types .= "s";
    }
    if (!empty($group)) {
        $where .= " AND group_name = ? ";
        $params[] = $group;
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
    $stats = $stmt->get_result()->fetch_assoc();
    
    $sql_all = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'ลงทะเบียนแล้ว' THEN 1 ELSE 0 END) as registered
                FROM students_import";
    $stats_all = $conn->query($sql_all)->fetch_assoc();

    send_json_response(true, "Stats retrieved", [
        "total" => (int)$stats['total'],
        "registered" => (int)$stats['registered'],
        "percent" => $stats['total'] > 0 ? round(($stats['registered'] / $stats['total']) * 100, 2) . '%' : '0%',
        "all_total" => (int)$stats_all['total'],
        "all_reg" => (int)$stats_all['registered']
    ]);
}

// 5. ค้นหารายชื่อนักเรียน (Search)
if ($action == 'search_students') {
    $query = trim($_GET['query'] ?? '');
    if (strlen($query) < 2) {
        send_json_response(false, "กรุณาพิมพ์อย่างน้อย 2 ตัวอักษร");
    }

    $search = "%$query%";
    $stmt = $conn->prepare("SELECT registration_code, student_id, student_name, group_name, status, registered_at FROM students_import WHERE student_name LIKE ? OR student_id LIKE ? OR id_card LIKE ? LIMIT 10");
    $stmt->bind_param("sss", $search, $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $list = [];
    while($row = $result->fetch_assoc()) {
        $list[] = $row;
    }
    
    send_json_response(true, "พบข้อมูล " . count($list) . " รายการ", ["list" => $list]);
}

send_json_response(false, "Invalid Action");
?>
