<?php
require_once 'config.php';
if (!defined('SECURE_ACCESS')) { die('Direct access not permitted'); }

$conn = get_db_connection();
if (!$conn) {
    send_json_response(false, "ฐานข้อมูลเชื่อมต่อล้มเหลว");
}

function ensure_fix_table_exists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS `fix_student` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `registration_code` varchar(20) DEFAULT NULL,
        `student_id` varchar(20) DEFAULT NULL,
        `id_card` varchar(20) DEFAULT NULL,
        `student_name` varchar(255) DEFAULT NULL,
        `parent_name` varchar(255) DEFAULT NULL,
        `address` text DEFAULT NULL,
        `parent_phone` varchar(20) DEFAULT NULL,
        `group_id` varchar(20) DEFAULT NULL,
        `group_name` varchar(100) DEFAULT NULL,
        `department` varchar(100) DEFAULT NULL,
        `status` varchar(50) DEFAULT 'รอดำเนินการ',
        `registered_at` datetime DEFAULT current_timestamp(),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    $conn->query($sql);
}

$action = $_GET['action'] ?? '';

if ($action == 'get_options') {
    $depts = [];
    $groups = [];
    $dept_filter = $_GET['department'] ?? '';
    
    $res_dept = $conn->query("SELECT DISTINCT department FROM students_import WHERE department != '' ORDER BY department ASC");
    while ($r = $res_dept->fetch_assoc()) $depts[] = $r['department'];

    if (!empty($dept_filter)) {
        $stmt = $conn->prepare("SELECT DISTINCT group_name FROM students_import WHERE department = ? AND group_name != '' ORDER BY group_name ASC");
        $stmt->bind_param("s", $dept_filter);
        $stmt->execute();
        $res_group = $stmt->get_result();
    } else {
        $res_group = $conn->query("SELECT DISTINCT group_name FROM students_import WHERE group_name != '' ORDER BY group_name ASC");
    }

    while ($r = $res_group->fetch_assoc()) $groups[] = $r['group_name'];

    send_json_response(true, "Options retrieved", ["departments" => $depts, "groups" => $groups]);
}

if ($action == 'save' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    ensure_fix_table_exists($conn);

    $id_card = trim($_POST['id_card'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $student_name = trim($_POST['student_name'] ?? '');
    $parent_name = trim($_POST['parent_name'] ?? '');
    $group_name = trim($_POST['group_name'] ?? '');
    $department = trim($_POST['department'] ?? '');

    if (empty($id_card) || empty($student_id) || empty($student_name) || empty($group_name) || empty($department)) {
        send_json_response(false, "กรุณากรอกข้อมูลสำคัญให้ครบถ้วน");
    }

    // 1. ตรวจสอบว่ามีรหัสนักศึกษานี้ในตารางหลัก (students_import) หรือยัง
    $check_main = $conn->prepare("SELECT id FROM students_import WHERE student_id = ? OR id_card = ?");
    $check_main->bind_param("ss", $student_id, $id_card);
    $check_main->execute();
    if ($check_main->get_result()->num_rows > 0) {
        send_json_response(false, "ข้อมูลนักศึกษานี้มีอยู่ในระบบอยู่แล้ว กรุณากลับไปตรวจสอบที่หน้าแรก");
    }
    $check_main->close();

    // 2. ตรวจสอบว่ามีการแจ้งเพิ่มรหัสนักศึกษานี้ในตารางแจ้งเพิ่ม (fix_student) ไปแล้วหรือยัง
    $check_fix = $conn->prepare("SELECT id FROM fix_student WHERE student_id = ? OR id_card = ?");
    $check_fix->bind_param("ss", $student_id, $id_card);
    $check_fix->execute();
    if ($check_fix->get_result()->num_rows > 0) {
        send_json_response(false, "ข้อมูลนักศึกษานี้ได้มีการแจ้งข้อมูลใหม่เข้ามาแล้วครับ ไม่ต้องกรอกซ้ำ");
    }
    $check_fix->close();

    // 3. บันทึกข้อมูลหากไม่พบข้อมูลซ้ำ
    $stmt = $conn->prepare("INSERT INTO fix_student (id_card, student_id, student_name, parent_name, group_name, department) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $id_card, $student_id, $student_name, $parent_name, $group_name, $department);
    
    if ($stmt->execute()) {
        send_json_response(true, "บันทึกข้อมูลเรียบร้อยแล้ว แอดมินจะตรวจสอบข้อมูลของคุณโดยเร็วที่สุด");
    } else {
        send_json_response(false, "ไม่สามารถบันทึกข้อมูลได้: " . $conn->error);
    }
}

send_json_response(false, "Invalid Action");
?>
