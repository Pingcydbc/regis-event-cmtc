<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("Asia/Bangkok");

// เชื่อมต่อฐานข้อมูลออนไลน์ InfinityFree
$conn = new mysqli("sql311.infinityfree.com", "if0_41990714", "HduJK1lBcv", "if0_41990714_school_register");
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// ⚡ บังคับฐานข้อมูล MySQL ให้ส่งเวลาตรงกับประเทศไทย (+07:00)
$conn->query("SET time_zone = '+07:00'");

// ตั้งชื่อไฟล์รายงานดาวน์โหลด
$filename = "รายงานการลงทะเบียน_" . date('Ymd_His') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// เปิดโครงสร้างแท็กเพื่อรองรับภาษาไทยบน Excel
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>';
echo '<body>';

// สร้างส่วนหัวตารางสรุปรายงาน
echo '<table border="1">';
echo '<tr><th colspan="6" style="font-size:16px; font-weight:bold; background-color:#f3f4f6; height:40px;">รายงานข้อมูลการลงทะเบียนประชุมผู้ปกครอง วท.เชียงใหม่</th></tr>';
echo '<tr><td colspan="6" style="text-align:left; border:none; height:25px;"><b>ส่งออกข้อมูล ณ วันที่:</b> ' . date('d/m/Y H:i') . ' น.</td></tr>';
echo '<tr></tr>'; // เว้นบรรทัดสไตล์ Excel

// ชื่อหัวคอลัมน์รายงาน
echo '<tr style="background-color:#dc2626; color:white; font-weight:bold; height:30px;">';
echo '<th>รหัสคิว</th>';
echo '<th>รหัสประจำตัวนักศึกษา</th>';
echo '<th>ชื่อ-นามสกุล นักศึกษา</th>';
echo '<th>ชื่อ-นามสกุล ผู้ปกครอง</th>';
echo '<th>สถานะลงทะเบียน</th>';
echo '<th>วันที่-เวลาที่ลงทะเบียน</th>';
echo '</tr>';

// ดึงข้อมูลเรียงลำดับตามรหัสคิวลงทะเบียน
$query = "SELECT registration_code, student_id, student_name, parent_name, status, registered_at FROM students_import ORDER BY registration_code ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // แปลงฟอร์แมตวันเวลาที่ดึงมาจากคลังข้อมูลให้แสดงเป็นแบบไทยอ่านง่าย
        $reg_time = "-";
        if (!empty($row['registered_at'])) {
            $reg_time = date('d/m/Y H:i:s', strtotime($row['registered_at']));
        }

        echo '<tr style="height:25px;">';
        echo '<td style="text-align:center; x:str;">' . htmlspecialchars($row['registration_code']) . '</td>';
        echo '<td style="text-align:center; x:str;">' . htmlspecialchars($row['student_id']) . '</td>';
        echo '<td style="text-align:left;">' . htmlspecialchars($row['student_name']) . '</td>';
        echo '<td style="text-align:left;">' . htmlspecialchars($row['parent_name']) . '</td>';
        
        // ปรับสีตัวอักษรสถานะให้มองเห็นชัดเจนแยกง่าย
        if ($row['status'] == 'ลงทะเบียนแล้ว') {
            echo '<td style="text-align:center; color:green; font-weight:bold;">ลงทะเบียนแล้ว</td>';
        } else {
            echo '<td style="text-align:center; color:gray;">ยังไม่ลงทะเบียน</td>';
        }
        
        echo '<td style="text-align:center;">' . $reg_time . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" style="text-align:center; height:30px;">ไม่มีข้อมูลการลงทะเบียนในระบบ</td></tr>';
}

echo '</table>';
echo '</body>';
echo '</html>';

$conn->close();
exit;
?>