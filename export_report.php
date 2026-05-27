<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("Asia/Bangkok");

// 1. เชื่อมต่อฐานข้อมูล
$conn = new mysqli("sql311.infinityfree.com", "if0_41990714", "HduJK1lBcv", "if0_41990714_school_register");
if ($conn->connect_error) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+07:00'");

// รับพารามิเตอร์ Filter
$format = $_GET['format'] ?? 'excel';
$level_group = $_GET['level_group'] ?? '';
$department_filter = $_GET['department'] ?? '';
$level_filter = $_GET['level'] ?? '';
$room_filter = $_GET['room'] ?? '';

// สร้าง Query Filter
$where = " WHERE level NOT LIKE '%ป.ตรี%' ";
$params = [];
$types = "";

if (!empty($level_group)) {
    $where .= " AND level LIKE ? ";
    $params[] = $level_group . "%";
    $types .= "s";
}
if (!empty($department_filter)) {
    $where .= " AND department = ? ";
    $params[] = $department_filter;
    $types .= "s";
}
if (!empty($level_filter)) {
    $where .= " AND level = ? ";
    $params[] = $level_filter;
    $types .= "s";
}
if (!empty($room_filter)) {
    $where .= " AND room = ? ";
    $params[] = $room_filter;
    $types .= "s";
}

// เรียงลำดับตามที่ขอ: registration_code, student_id, id_card, student_name, level, department, room, parent_name, status, registered_at
$sql = "SELECT registration_code, student_id, id_card, student_name, level, department, room, parent_name, status, registered_at 
        FROM students_import $where ORDER BY department ASC, level ASC, room ASC, registration_code ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// รับข้อมูลภาพ Chart และสถิติ
$chart_image = $_POST['chart_image'] ?? '';
$stats_json = $_POST['stats_json'] ?? '';
$stats = !empty($stats_json) ? json_decode($stats_json, true) : null;

// --- แยกการแสดงผลตาม Format ---

if ($format == 'excel') {
    $filename = "รายงานลงทะเบียน_" . date('Ymd_His') . ".xls";
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office:excel" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
    echo '  <Styles>
        <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="11"/></Style>
        <Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:Bold="1" ss:Color="#ffffff"/><Interior ss:Color="#991b1b" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/></Borders></Style>
        <Style ss:ID="CellText"><NumberFormat ss:Format="@"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/></Borders></Style>
        <Style ss:ID="DeptTitle"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:Bold="1" ss:Size="14"/><Interior ss:Color="#f1f5f9" ss:Pattern="Solid"/></Style>
    </Styles>' . "\n";

    // จัดกลุ่มข้อมูลตามแผนก
    $data_by_dept = [];
    while ($row = $result->fetch_assoc()) {
        $dept = !empty($row['department']) ? $row['department'] : "ไม่ระบุแผนก";
        $data_by_dept[$dept][] = $row;
    }

    if (empty($data_by_dept)) {
        // กรณีไม่มีข้อมูล
        echo '  <Worksheet ss:Name="No Data"><Table><Row><Cell><Data ss:Type="String">ไม่พบข้อมูลตามเงื่อนไข</Data></Cell></Row></Table></Worksheet>';
    } else {
        foreach ($data_by_dept as $dept_name => $rows) {
            // ทำความสะอาดชื่อ Sheet (ห้ามมีตัวอักษรพิเศษบางตัว)
            $sheet_name = str_replace([':', '\\', '/', '?', '*', '[', ']'], '', $dept_name);
            $sheet_name = mb_substr($sheet_name, 0, 31); // Excel จำกัดชื่อ Sheet 31 ตัวอักษร

            echo '  <Worksheet ss:Name="' . htmlspecialchars($sheet_name) . '"><Table>' . "\n";
            
            // เพิ่มแถวสรุปสถิติในแต่ละ Sheet (ถ้ามีข้อมูล)
            if ($stats) {
                echo '    <Row ss:Height="25">';
                echo '      <Cell ss:MergeAcross="3" ss:StyleID="Header"><Data ss:Type="String">แผนก: ' . htmlspecialchars($dept_name) . '</Data></Cell>';
                echo '      <Cell ss:MergeAcross="5" ss:StyleID="Header"><Data ss:Type="String">สรุปภาพรวม (ทั้งระบบ): ลงทะเบียนแล้ว ' . $stats['registered'] . ' จาก ' . $stats['total'] . ' (' . $stats['percent'] . ')</Data></Cell>';
                echo '    </Row>' . "\n";
            }

            echo '    <Row><Cell ss:StyleID="Header"><Data ss:Type="String">รหัสคิว</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">รหัสนักศึกษา</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">เลขบัตรประชาชน</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อ-นามสกุล</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ระดับชั้น</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">แผนกวิชา</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ห้อง</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อผู้ปกครอง</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">สถานะ</Data></Cell><Cell ss:StyleID="Header"><Data ss:Type="String">เวลาลงทะเบียน</Data></Cell></Row>' . "\n";

            foreach ($rows as $row) {
                $reg_time = (!empty($row['registered_at'])) ? date('d/m/Y H:i:s', strtotime($row['registered_at'])) : "-";
                echo '    <Row>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['registration_code']) . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['student_id']) . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['id_card'] ?? '') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['student_name']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['level']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['department']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['room']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['parent_name'] ?? '') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['status']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . $reg_time . '</Data></Cell>';
                echo '</Row>' . "\n";
            }
            echo '  </Table></Worksheet>' . "\n";
        }
    }
    echo '</Workbook>';
} else {
    // PDF Format (Printable HTML)
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานการลงทะเบียน - <?php echo date('Ymd_His'); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Sarabun', sans-serif; }
            @media print {
                .no-print { display: none; }
                @page { margin: 1cm; }
            }
        </style>
    </head>
    <body class="bg-gray-100 p-8">
        <div class="max-w-6xl mx-auto bg-white p-8 shadow-lg rounded-xl">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">รายงานสรุปข้อมูลการลงทะเบียน</h1>
                    <p class="text-slate-500">วิทยาลัยเทคนิคเชียงใหม่ | ข้อมูล ณ วันที่ <?php echo date('d/m/Y H:i'); ?> น.</p>
                    <p class="text-xs text-red-600 mt-1">
                        เงื่อนไข: <?php 
                            echo ($level_group ?: 'ทุกระดับ') . ' | ' . 
                                 ($department_filter ?: 'ทุกแผนก') . ' | ' . 
                                 ($level_filter ?: 'ทุกชั้นปี') . ' | ' . 
                                 'ห้อง ' . ($room_filter ?: 'ทั้งหมด'); 
                        ?>
                    </p>
                </div>
                <div class="no-print space-x-2">
                    <button onclick="window.print()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">พิมพ์ / บันทึก PDF</button>
                    <button onclick="window.close()" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">ปิดหน้าต่าง</button>
                </div>
            </div>

            <!-- ส่วนแสดง Chart และสถิติสรุป (แบบตัวหนังสือ) -->
            <?php if ($chart_image || $stats): ?>
            <div class="flex flex-col md:flex-row gap-8 mb-8 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm items-center">
                <div class="w-full md:w-1/2 flex justify-center border-r border-slate-100 pr-8">
                    <?php if ($chart_image): ?>
                        <img src="<?php echo $chart_image; ?>" class="max-h-60 object-contain">
                    <?php endif; ?>
                </div>
                <div class="w-full md:w-1/2 space-y-3 pl-0 md:pl-4">
                    <h3 class="font-bold text-slate-800 border-b pb-2 text-lg text-red-700 mb-4">สรุปข้อมูลการลงทะเบียน</h3>
                    <p class="text-sm text-slate-700">ลงทะเบียนสำเร็จแล้ว: <span class="font-bold text-green-600"><?php echo $stats['registered'] ?? '-'; ?></span> คน</p>
                    <p class="text-sm text-slate-700">จำนวนนักศึกษาทั้งหมด: <span class="font-bold text-slate-900"><?php echo $stats['total'] ?? '-'; ?></span> คน</p>
                    <p class="text-sm text-slate-700">คิดเป็นร้อยละ: <span class="font-bold text-red-600"><?php echo $stats['percent'] ?? '0%'; ?></span></p>
                    <div class="pt-2 border-t border-slate-50 mt-2">
                        <p class="text-xs text-slate-500 font-medium italic">แยกตามกลุ่ม:</p>
                        <p class="text-sm text-slate-600 mt-1">ปวช.: <?php echo $stats['pvc_reg'] ?? '0'; ?> / <?php echo $stats['pvc_total'] ?? '0'; ?> คน</p>
                        <p class="text-sm text-slate-600">ปวส.: <?php echo $stats['pvs_reg'] ?? '0'; ?> / <?php echo $stats['pvs_total'] ?? '0'; ?> คน</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="border p-2">คิว</th>
                        <th class="border p-2">รหัสนักศึกษา</th>
                        <th class="border p-2">เลขบัตรประชาชน</th>
                        <th class="border p-2">ชื่อ-นามสกุล</th>
                        <th class="border p-2">ระดับชั้น</th>
                        <th class="border p-2">แผนกวิชา</th>
                        <th class="border p-2">ห้อง</th>
                        <th class="border p-2">ชื่อผู้ปกครอง</th>
                        <th class="border p-2">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 text-center"><?php echo htmlspecialchars($row['registration_code']); ?></td>
                            <td class="border p-2 text-center"><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td class="border p-2 text-center"><?php echo htmlspecialchars($row['id_card'] ?? '-'); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['student_name']); ?></td>
                            <td class="border p-2 text-center"><?php echo htmlspecialchars($row['level']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['department']); ?></td>
                            <td class="border p-2 text-center"><?php echo htmlspecialchars($row['room']); ?></td>
                            <td class="border p-2"><?php echo htmlspecialchars($row['parent_name'] ?: '-'); ?></td>
                            <td class="border p-2 text-center">
                                <span class="<?php echo $row['status'] == 'ลงทะเบียนแล้ว' ? 'text-green-600 font-bold' : 'text-gray-400'; ?>">
                                    <?php echo htmlspecialchars($row['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                        <tr><td colspan="9" class="border p-8 text-center text-gray-500 italic">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <div class="mt-8 text-right text-[10px] text-gray-400 italic">
                Generated by Chiang Mai Technical College Registration System
            </div>
        </div>
    </body>
    </html>
    <?php
}

$stmt->close();
$conn->close();
exit;
?>