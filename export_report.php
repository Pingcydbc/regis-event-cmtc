<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("Asia/Bangkok");

// 1. เชื่อมต่อฐานข้อมูลออนไลน์
$conn = new mysqli("sql311.infinityfree.com", "if0_41990714", "HduJK1lBcv", "if0_41990714_school_register");
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+07:00'");

$filename = "รายงานลงทะเบียน_แยกแผนก_" . date('Ymd_His') . ".xls";

// บังคับส่ง Header เป็นสมุดงาน Excel XML
header('Content-Type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// เริ่มต้นพ่นโครงสร้างสมุดงานแบบ XML ที่ Excel บังคับแยก Sheet แน่นอน
echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
echo 'xmlns:o="urn:schemas-microsoft-com:office:office:excel" ';
echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ';
echo 'xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";

// 2. กำหนดสไตล์ตาราง (CSS ในรูปแบบ Excel) สีสันดูง่ายสบายตา
echo '  <Styles>
    <Style ss:ID="Default" ss:Name="Normal">
      <Alignment ss:Vertical="Center"/>
      <Borders/>
      <Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="11" ss:Color="#334155"/>
      <Interior/>
      <NumberFormat/>
      <Protection/>
    </Style>
    <Style ss:ID="MainTitle">
      <Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="14" ss:Bold="1" ss:Color="#1e293b"/>
    </Style>
    <Style ss:ID="SubTitle">
      <Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="10" ss:Color="#64748b"/>
    </Style>
    <Style ss:ID="Header">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
      </Borders>
      <Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="11" ss:Bold="1" ss:Color="#ffffff"/>
      <Interior ss:Color="#991b1b" ss:Pattern="Solid"/>
    </Style>
    <Style ss:ID="CellLeft">
      <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
      </Borders>
    </Style>
    <Style ss:ID="CellCenter">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
      </Borders>
    </Style>
    <Style ss:ID="CellText">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
      </Borders>
      <NumberFormat ss:Format="@"/>
    </Style>
    <Style ss:ID="StatusGreen">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
      </Borders>
      <Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="11" ss:Bold="1" ss:Color="#16a34a"/>
    </Style>
    <Style ss:ID="StatusGray">
      <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
      <Borders>
        <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
        <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/>
      </Borders>
      <Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="11" ss:Color="#94a3b8"/>
    </Style>
  </Styles>' . "\n";

// 3. วนลูปสร้างหน้าแผ่นงาน (Worksheet) แยกตามแผนกวิชา
$all_departments = array("เทคโนโลยีสารสนเทศ", "ช่างยนต์", "ช่างไฟฟ้ากำลัง", "ช่างอิเล็กทรอนิกส์", "ช่างกลโรงงาน");

foreach ($all_departments as $dept_name) {
    // ตัดชื่อแท็บไม่ให้เกิน 30 ตัวอักษร
    $short_name = mb_substr($dept_name, 0, 30);
    
    echo '  <Worksheet ss:Name="' . htmlspecialchars($short_name) . '">' . "\n";
    echo '    <Table>' . "\n";
    
    // ตั้งความกว้างของคอลัมน์ล่วงหน้า (หน่วยเป็น Point)
    echo '      <Column ss:Width="60"/>' . "\n";  // รหัสคิว
    echo '      <Column ss:Width="90"/>' . "\n";  // รหัสนักศึกษา
    echo '      <Column ss:Width="160"/>' . "\n"; // ชื่อนักศึกษา
    echo '      <Column ss:Width="70"/>' . "\n";  // ระดับชั้น
    echo '      <Column ss:Width="50"/>' . "\n";  // ห้อง
    echo '      <Column ss:Width="160"/>' . "\n"; // ชื่อผู้ปกครอง
    echo '      <Column ss:Width="90"/>' . "\n";  // สถานะ
    echo '      <Column ss:Width="120"/>' . "\n"; // เวลาลงทะเบียน

    // แถวหัวข้อรายงานประจำแผนกวิชา
    echo '      <Row ss:Height="25">' . "\n";
    echo '        <Cell ss:MergeAcross="7" ss:StyleID="MainTitle"><Data ss:Type="String">📊 สรุปรายชื่อลงทะเบียนผู้ปกครอง แผนกวิชา: ' . htmlspecialchars($dept_name) . '</Data></Cell>' . "\n";
    echo '      </Row>' . "\n";
    
    echo '      <Row ss:Height="18">' . "\n";
    echo '        <Cell ss:MergeAcross="7" ss:StyleID="SubTitle"><Data ss:Type="String">วิทยาลัยเทคนิคเชียงใหม่ | ส่งออกข้อมูลเมื่อ: ' . date('d/m/Y H:i') . ' น.</Data></Cell>' . "\n";
    echo '      </Row>' . "\n";
    
    echo '      <Row></Row>' . "\n"; // เว้นแถวว่างให้ดูโปร่ง สบายตา

    // แถวหัวตาราง (สีแดง ตัวหนังสือขาว)
    echo '      <Row ss:Height="26" ss:StyleID="Header">' . "\n";
    echo '        <Cell><Data ss:Type="String">รหัสคิว</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">รหัสนักศึกษา</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">ชื่อ-นามสกุล นักศึกษา</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">ระดับชั้น</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">ห้อง</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">ชื่อ-นามสกุล ผู้ปกครอง</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">สถานะ</Data></Cell>' . "\n";
    echo '        <Cell><Data ss:Type="String">เวลาลงทะเบียน</Data></Cell>' . "\n";
    echo '      </Row>' . "\n";

    // ดึงข้อมูลรายชื่อนักศึกษาจากตารางเฉพาะแผนกวิชานี้
    $stmt = $conn->prepare("SELECT registration_code, student_id, student_name, level, room, parent_name, status, registered_at FROM students_import WHERE department = ? ORDER BY registration_code ASC");
    $stmt->bind_param("s", $dept_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reg_time = (!empty($row['registered_at'])) ? date('d/m/Y H:i:s', strtotime($row['registered_at'])) : "-";
            $status_style = ($row['status'] == 'ลงทะเบียนแล้ว') ? 'StatusGreen' : 'StatusGray';

            echo '      <Row ss:Height="22">' . "\n";
            // ใช้ StyleID="CellText" เพื่อล็อกฟอร์แมตข้อความ ป้องกันเลข 0 ด้านหน้าหาย
            echo '        <Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['registration_code']) . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['student_id']) . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="CellLeft"><Data ss:Type="String">' . htmlspecialchars($row['student_name']) . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . htmlspecialchars($row['level']) . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . htmlspecialchars($row['room']) . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="CellLeft"><Data ss:Type="String">' . htmlspecialchars($row['parent_name'] ?? '') . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="' . $status_style . '"><Data ss:Type="String">' . htmlspecialchars($row['status']) . '</Data></Cell>' . "\n";
            echo '        <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . $reg_time . '</Data></Cell>' . "\n";
            echo '      </Row>' . "\n";
        }
    } else {
        echo '      <Row ss:Height="25">' . "\n";
        echo '        <Cell ss:MergeAcross="7" ss:StyleID="CellCenter"><Data ss:Type="String">ไม่มีข้อมูลรายชื่อในแผนกวิชานี้</Data></Cell>' . "\n";
        echo '      </Row>' . "\n";
    }
    
    $stmt->close();
    echo '    </Table>' . "\n";
    echo '  </Worksheet>' . "\n";
}

echo '</Workbook>' . "\n";
$conn->close();
exit;
?>