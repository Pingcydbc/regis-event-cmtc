<?php
// ตั้งโซนเวลาประเทศไทย
date_default_timezone_set("Asia/Bangkok");

// 1. เชื่อมต่อฐานข้อมูล
$conn = new mysqli("localhost", "root", "", "school_register");
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// 2. ตั้งชื่อไฟล์ Excel
$filename = "รายงานผู้ลงทะเบียน_วิทยาลัยเทคนิคเชียงใหม่_" . date('Y-m-d') . ".xls";

// 3. ส่ง Header บังคับให้ดาวน์โหลดเป็น Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// 4. ดึงข้อมูล (เพิ่มการดึงคอลัมน์ registered_at)
$sql = "SELECT registration_code, student_id, student_name, parent_name, status, registered_at 
        FROM students_import 
        WHERE status = 'ลงทะเบียนแล้ว' 
        ORDER BY registered_at DESC"; // เรียงจากคนที่ลงทะเบียนล่าสุดขึ้นก่อน
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body, table { font-family: 'Sarabun', sans-serif; font-size: 14px; }
        th { background-color: #dc2626; color: white; font-weight: bold; }
        th, td { border: 1px solid #cccccc; padding: 6px; text-align: left; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h3>รายงานรายชื่อผู้ลงทะเบียนประชุมผู้ปกครอง วิทยาลัยเทคนิคเชียงใหม่</h3>
    <p>ส่งออกข้อมูล ณ วันที่: <?php echo date('d/m/Y H:i'); ?> น.</p>
    
    <table>
        <thead>
            <tr>
                <th width="80">รหัสคิว</th>
                <th width="130">รหัสนักศึกษา</th>
                <th width="180">ชื่อ-นามสกุล นักศึกษา</th>
                <th width="180">ชื่อ-นามสกุล ผู้ปกครอง</th>
                <th width="100">สถานะ</th>
                <th width="160">วันที่-เวลาที่ลงทะเบียน</th> </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td class='text-center' style='vnd.ms-excel.numberformat:@'>" . htmlspecialchars($row['registration_code']) . "</td>";
                    echo "<td style='vnd.ms-excel.numberformat:@'>" . htmlspecialchars($row['student_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['student_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['parent_name']) . "</td>";
                    echo "<td class='text-center' style='color: green; font-weight: bold;'>ลงทะเบียนแล้ว</td>";
                    
                    // แสดงเวลาลงทะเบียน ถ้าระบบยังไม่มีเวลาให้แดชไว้ (-)
                    $time_display = ($row['registered_at'] && $row['registered_at'] != '0000-00-00 00:00:00') 
                        ? date('d/m/Y H:i:s', strtotime($row['registered_at'])) 
                        : '-';
                    echo "<td class='text-center'>" . $time_display . "</td>";
                    
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='text-center'>ยังไม่มีผู้ลงทะเบียนในระบบ</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
$conn->close();
?>