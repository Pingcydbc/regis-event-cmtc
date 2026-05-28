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
$format = $_GET['format'] ?? 'pdf'; // เปลี่ยน default เป็น pdf ตามการใช้งานส่วนใหญ่
$department_filter = $_POST['department'] ?? '';
$group_filter = $_POST['group_name'] ?? '';

// สร้าง Query Filter
$where = " WHERE 1=1 ";
$params = [];
$types = "";

if (!empty($department_filter)) {
    $where .= " AND department = ? ";
    $params[] = $department_filter;
    $types .= "s";
}
if (!empty($group_filter)) {
    $where .= " AND group_name = ? ";
    $params[] = $group_filter;
    $types .= "s";
}

// เรียงลำดับตามที่ขอ (ใช้ group_name แทน level/room)
$sql = "SELECT registration_code, student_id, id_card, student_name, parent_name, address, parent_phone, group_id, group_name, department, status, registered_at 
        FROM students_import $where ORDER BY department ASC, group_name ASC, registration_code ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$students_data = [];
while ($row = $result->fetch_assoc()) {
    $students_data[] = $row;
}

$stmt->close();
$conn->close();

// รับข้อมูลภาพ Chart และสถิติ
$chart_image = $_POST['chart_image'] ?? '';
$stats_json = $_POST['stats'] ?? ''; // แก้ชื่อ key ให้ตรงกับ dashboard.php
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

    $data_by_dept = [];
    foreach ($students_data as $row) {
        $dept = !empty($row['department']) ? $row['department'] : "ไม่ระบุแผนก";
        $data_by_dept[$dept][] = $row;
    }

    if (empty($data_by_dept)) {
        echo '  <Worksheet ss:Name="No Data"><Table><Row><Cell><Data ss:Type="String">ไม่พบข้อมูลตามเงื่อนไข</Data></Cell></Row></Table></Worksheet>';
    } else {
        foreach ($data_by_dept as $dept_name => $rows) {
            $sheet_name = str_replace([':', '\\', '/', '?', '*', '[', ']'], '', $dept_name);
            $sheet_name = mb_substr($sheet_name, 0, 31);

            echo '  <Worksheet ss:Name="' . htmlspecialchars($sheet_name) . '"><Table>' . "\n";
            // ปรับ MergeAcross ตามจำนวนคอลัมน์ (12 คอลัมน์ -> Merge 11)
            echo '    <Row ss:Height="25"><Cell ss:MergeAcross="11" ss:StyleID="Header"><Data ss:Type="String">รายงานสรุปข้อมูลการลงทะเบียน - ' . htmlspecialchars($dept_name) . '</Data></Cell></Row>' . "\n";
            echo '    <Row>
                <Cell ss:StyleID="Header"><Data ss:Type="String">รหัสคิว</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">รหัสผู้เรียน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">เลขบัตรประชาชน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อ-นามสกุล ผู้เรียน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อผู้ปกครอง</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">เบอร์โทรศัพท์</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ที่อยู่</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">รหัสกลุ่มเรียน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อกลุ่มเรียน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">แผนกวิชา</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">สถานะ</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">เวลาลงทะเบียน</Data></Cell>
            </Row>' . "\n";

            foreach ($rows as $row) {
                $reg_time = (!empty($row['registered_at'])) ? date('d/m/Y H:i:s', strtotime($row['registered_at'])) : "-";
                echo '    <Row>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['registration_code']) . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['student_id']) . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['id_card'] ?? '') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['student_name']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['parent_name'] ?? '') . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['parent_phone'] ?? '') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['address'] ?? '') . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($row['group_id'] ?? '') . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['group_name']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['department']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . htmlspecialchars($row['status']) . '</Data></Cell>';
                echo '<Cell><Data ss:Type="String">' . $reg_time . '</Data></Cell>';
                echo '</Row>' . "\n";
            }
            echo '  </Table></Worksheet>' . "\n";
        }
    }
    echo '</Workbook>';
    exit;
} else {
    // PDF Format
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>รายงานการลงทะเบียน - <?php echo date('Ymd_His'); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Sarabun', sans-serif; margin: 0; padding: 0; }
            @media screen {
                body { background-color: #e2e8f0; padding: 40px 0; display: flex; flex-direction: column; align-items: center; }
                .sheet { width: 210mm; min-height: 297mm; background: white; padding: 10mm 12mm; margin-bottom: 20px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); border-radius: 8px; box-sizing: border-box; position: relative; }
                .action-buttons { position: fixed; top: 20px; right: 20px; z-index: 100; background: rgba(255, 255, 255, 0.9); padding: 10px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); backdrop-filter: blur(4px); }
            }
            @media print {
                @page { 
                    size: A4; 
                    margin: 0; /* ลบหัวกระดาษและท้ายกระดาษของ Browser (วันที่, ชื่อหน้า, URL) */
                }
                body { 
                    background: white; 
                    padding: 10mm 12mm; /* กำหนดขอบกระดาษด้วย padding แทน เพื่อไม่ให้เนื้อหาชิดขอบเกินไป */
                }
                .sheet { 
                    width: 100% !important; 
                    padding: 0 !important; 
                    margin: 0 !important; 
                    box-shadow: none !important; 
                    border-radius: 0 !important; 
                }
                .no-print { 
                    display: none !important; 
                }
            }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            thead { display: table-header-group; }
            tfoot { display: table-footer-group; }
        </style>
    </head>
    <body>
        <div class="action-buttons no-print flex gap-2">
            <button onclick="window.print()" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition shadow-lg">พิมพ์ / บันทึก PDF</button>
            <button onclick="window.close()" class="bg-slate-500 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-slate-600 transition shadow-lg">ปิด</button>
        </div>

        <div class="sheet">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 tracking-tight">รายงานสรุปข้อมูลการลงทะเบียน</h1>
                    <p class="text-slate-500 text-sm mt-1">วิทยาลัยเทคนิคเชียงใหม่ | ข้อมูล ณ วันที่ <?php echo date('d/m/Y H:i'); ?> น.</p>
                    <div class="mt-3 inline-block bg-slate-100 px-3 py-1 rounded-full">
                        <p class="text-[11px] font-bold text-slate-600 uppercase">
                            เงื่อนไข: <?php echo ($department_filter ?: 'ทุกแผนก') . ' | กลุ่มเรียน: ' . ($group_filter ?: 'ทั้งหมด'); ?>
                        </p>
                    </div>
                </div>
                <img src="logo.png" class="h-16 w-auto opacity-90">
            </div>

            <?php if ($chart_image || $stats): ?>
                <div class="grid grid-cols-5 gap-6 mb-8 bg-slate-50 p-6 rounded-2xl border border-slate-100">
                    <div class="col-span-2 flex justify-center items-center border-r border-slate-200 pr-6">
                        <?php if ($chart_image): ?>
                            <img src="<?php echo $chart_image; ?>" class="max-h-48 w-full object-contain">
                        <?php endif; ?>
                    </div>
                    <div class="col-span-3 space-y-3 pl-2">
                        <h3 class="font-bold text-slate-800 border-b border-slate-200 pb-2 text-base flex items-center gap-2">
                            <span class="w-2 h-2 bg-red-600 rounded-full"></span>
                            สรุปสถิติภาพรวม
                        </h3>
                        <div class="grid grid-cols-2 gap-4 pt-1">
                            <div>
                                <p class="text-[10px] text-slate-500 font-bold uppercase">ลงทะเบียนสำเร็จ</p>
                                <p class="text-xl font-bold text-green-600"><?php echo $stats['registered'] ?? '0'; ?> <span class="text-xs font-normal text-slate-400">คน</span></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500 font-bold uppercase">ทั้งหมดที่เลือก</p>
                                <p class="text-xl font-bold text-slate-800"><?php echo $stats['total'] ?? '0'; ?> <span class="text-xs font-normal text-slate-400">คน</span></p>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200 mt-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-slate-700">คิดเป็นร้อยละ</span>
                                <span class="text-xl font-bold text-red-600"><?php echo $stats['percent'] ?? '0%'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <table class="w-full text-[9px] border-collapse">
                <thead>
                    <tr class="bg-slate-800 text-white">
                        <th class="border border-slate-700 p-1 w-[30px]">คิว</th>
                        <th class="border border-slate-700 p-1 w-[70px]">รหัสผู้เรียน</th>
                        <th class="border border-slate-700 p-1 w-[90px]">เลขบัตรประชาชน</th>
                        <th class="border border-slate-700 p-1">ชื่อ-นามสกุล</th>
                        <th class="border border-slate-700 p-1">ชื่อกลุ่มเรียน</th>
                        <th class="border border-slate-700 p-1">แผนกวิชา</th>
                        <th class="border border-slate-700 p-1">ผู้ปกครอง</th>
                        <th class="border border-slate-700 p-1 w-[70px]">สถานะ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students_data) > 0): ?>
                        <?php foreach ($students_data as $row): ?>
                            <tr class="even:bg-slate-50">
                                <td class="border border-slate-200 p-1 text-center font-mono"><?php echo htmlspecialchars($row['registration_code']); ?></td>
                                <td class="border border-slate-200 p-1 text-center"><?php echo htmlspecialchars($row['student_id']); ?></td>
                                <td class="border border-slate-200 p-1 text-center text-slate-500"><?php echo htmlspecialchars($row['id_card'] ?? '-'); ?></td>
                                <td class="border border-slate-200 p-1 whitespace-nowrap font-medium px-2"><?php echo htmlspecialchars($row['student_name']); ?></td>
                                <td class="border border-slate-200 p-1 whitespace-nowrap px-2 text-center"><?php echo htmlspecialchars($row['group_name']); ?></td>
                                <td class="border border-slate-200 p-1 whitespace-nowrap px-2"><?php echo htmlspecialchars($row['department']); ?></td>
                                <td class="border border-slate-200 p-1 whitespace-nowrap text-slate-600 px-2"><?php echo htmlspecialchars($row['parent_name'] ?: '-'); ?></td>
                                <td class="border border-slate-200 p-1 text-center whitespace-nowrap">
                                    <span class="<?php echo $row['status'] == 'ลงทะเบียนแล้ว' ? 'text-green-600 font-bold' : 'text-slate-300'; ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="border border-slate-200 p-12 text-center text-slate-400 italic bg-slate-50">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="mt-8 pt-4 border-t border-slate-100 flex justify-between items-center text-[10px] text-slate-400 italic">
                <div>Chiang Mai Technical College Registration System</div>
                <div>หน้า 1 / 1</div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
exit;
?>