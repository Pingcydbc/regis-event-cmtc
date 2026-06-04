<?php
require_once 'config.php';
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

$conn = get_db_connection();
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว");
}

// รับพารามิเตอร์ Filter
$format = $_GET['format'] ?? 'pdf';
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

// เรียงลำดับจากรหัสคิวน้อยไปมาก
$sql = "SELECT registration_code, student_id, id_card, student_name, parent_name, address, parent_phone, group_id, group_name, department, status, registered_at 
        FROM students_import $where ORDER BY CAST(registration_code AS UNSIGNED) ASC";

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

// 🌟 [จุดแก้ไข] คำนวณสถิติเองจากฐานข้อมูล (ไม่เชื่อข้อมูลจาก POST)
$sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'ลงทะเบียนแล้ว' THEN 1 ELSE 0 END) as registered
            FROM students_import $where";
$stmt_stats = $conn->prepare($sql_stats);
if (!empty($params)) { $stmt_stats->bind_param($types, ...$params); }
$stmt_stats->execute();
$stats_db = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

$stats = [
    "total" => (int)$stats_db['total'],
    "registered" => (int)$stats_db['registered'],
    "percent" => $stats_db['total'] > 0 ? round(($stats_db['registered'] / $stats_db['total']) * 100, 2) . '%' : '0%'
];

// รับข้อมูลภาพ Chart เท่านั้น
$chart_image = $_POST['chart_image'] ?? '';

// --- แยกการแสดงผลตาม Format ---

if ($format == 'excel') {
    $filename = "รายงานลงทะเบียน_" . date('Ymd_His') . ".xls";
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=\"$filename\"");

    echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
    echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office:excel" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
    echo '  <Styles>
        <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Center"/><Font ss:FontName="Sarabun" x:CharSet="222" ss:Size="10"/></Style>
        <Style ss:ID="Header"><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Font ss:Bold="1" ss:Color="#ffffff"/><Interior ss:Color="#991b1b" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#000000"/></Borders></Style>
        <Style ss:ID="CellText"><NumberFormat ss:Format="@"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#cbd5e1"/></Borders></Style>
        <Style ss:ID="DeptTitle"><Alignment ss:Horizontal="Left" ss:Vertical="Center"/><Font ss:Bold="1" ss:Size="12"/><Interior ss:Color="#f1f5f9" ss:Pattern="Solid"/></Style>
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
            echo '    <Row ss:Height="20"><Cell ss:MergeAcross="11" ss:StyleID="Header"><Data ss:Type="String">รายงานสรุปการลงทะเบียน - ' . htmlspecialchars($dept_name) . '</Data></Cell></Row>' . "\n";
            echo '    <Row>
                <Cell ss:StyleID="Header"><Data ss:Type="String">คิว</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">รหัสนักศึกษา</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">เลขบัตรประชาชน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อ-นามสกุล</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ชื่อผู้ปกครอง</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">เบอร์โทรศัพท์</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">ที่อยู่</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">รหัสกลุ่ม</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">กลุ่มเรียน</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">แผนกวิชา</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">สถานะ</Data></Cell>
                <Cell ss:StyleID="Header"><Data ss:Type="String">วันเวลาที่ลงทะเบียน</Data></Cell>
            </Row>' . "\n";

            foreach ($rows as $row) {
                $reg_time = (!empty($row['registered_at'])) ? date('d/m/Y H:i:s', strtotime($row['registered_at'])) : "-";
                
                $id_card_val = $row['id_card'] ?? '';
                if (stripos($id_card_val, 'E+') !== false) { $id_card_val = number_format((float)$id_card_val, 0, '', ''); }
                
                $student_id_val = $row['student_id'] ?? '';
                if (stripos($student_id_val, 'E+') !== false) { $student_id_val = number_format((float)$student_id_val, 0, '', ''); }

                $reg_code_val = $row['registration_code'] ?? '';
                if (stripos($reg_code_val, 'E+') !== false) { $reg_code_val = number_format((float)$reg_code_val, 0, '', ''); }

                echo '    <Row>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($reg_code_val) . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($student_id_val) . '</Data></Cell>';
                echo '<Cell ss:StyleID="CellText"><Data ss:Type="String">' . htmlspecialchars($id_card_val) . '</Data></Cell>';
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
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700;800&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Sarabun', sans-serif;
                margin: 0;
                padding: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .auto-scale-cell {
                white-space: normal !important;
                word-wrap: break-word;
                word-break: keep-all;
                line-height: 1.1;
            }

            @media screen {
                body {
                    background-color: #f1f5f9;
                    padding: 20px 0;
                }

                .sheet {
                    width: 210mm;
                    min-height: 297mm;
                    background: white;
                    padding: 10mm 15mm 25mm 15mm; 
                    margin: 0 auto 20px auto;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                    border-radius: 4px;
                    box-sizing: border-box;
                }

                .action-buttons {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 100;
                    background: rgba(255, 255, 255, 0.9);
                    padding: 8px;
                    border-radius: 12px;
                    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                    backdrop-filter: blur(8px);
                }
            }

            @media print {
                @page {
                    size: A4;
                    margin: 0; 
                }

                body {
                    background: white;
                    padding: 0 !important;
                }

                .sheet {
                    width: 210mm;
                    padding: 10mm 15mm 25mm 15mm !important;
                    margin: 0 !important;
                    box-shadow: none !important;
                    border-radius: 0 !important;
                    box-sizing: border-box;
                    page-break-after: always;
                }

                .no-print {
                    display: none !important;
                }
            }

            .report-content-table {
                page-break-inside: auto;
                width: 100%;
                border-collapse: collapse !important;
                table-layout: fixed;
            }

            .report-content-table tr {
                page-break-inside: avoid;
            }

            .report-content-table td,
            .report-content-table th {
                vertical-align: top;
                padding: 4px 3px !important;
                border: 1px solid #e2e8f0 !important;
            }

            .report-content-table thead tr {
                background-color: #1e293b !important;
                color: #ffffff !important;
            }

            .report-content-table thead th {
                background-color: #1e293b !important;
                color: #ffffff !important;
                font-weight: bold !important;
                text-align: center !important;
                text-transform: uppercase;
                letter-spacing: 0.02em;
            }
        </style>
    </head>

    <body class="bg-slate-50">
        <div class="action-buttons no-print flex gap-2">
            <button onclick="window.print()"
                class="bg-slate-900 text-white px-4 py-2 rounded-xl font-black hover:bg-black transition shadow-lg uppercase text-[10px] tracking-widest">พิมพ์รายงาน / บันทึก PDF</button>
            <button onclick="window.close()"
                class="bg-white text-slate-500 px-4 py-2 rounded-xl font-black hover:bg-slate-50 transition border border-slate-200 uppercase text-[10px] tracking-widest">ปิดหน้าต่าง</button>
        </div>

        <div class="sheet">
            <table class="master-table">
                <thead>
                    <tr>
                        <td>
                            <div style="height: 10mm;"></div> <!-- 🌟 ระยะขอบบนในทุกหน้า -->
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <!-- Header -->
                            <div class="flex flex-col items-center mb-8 text-center relative pt-2">
                                <img src="logo.png" class="h-16 w-auto mb-4 drop-shadow-sm">
                                <div>
                                    <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">รายงานสรุปการลงทะเบียน</h1>
                                    <p class="text-slate-400 font-bold text-[10px] uppercase tracking-widest mt-1.5">
                                        วิทยาลัยเทคนิคเชียงใหม่ | ข้อมูล ณ วันที่ <?php echo date('d M Y - H:i'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="mb-5 flex justify-between items-end border-b border-slate-100 pb-3">
                                <div class="space-y-1">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">เงื่อนไขรายงาน</p>
                                    <div class="flex gap-2">
                                        <span class="bg-slate-800 text-white text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-wider"><?php echo $department_filter ?: 'ทุกแผนกวิชา'; ?></span>
                                        <span class="bg-slate-100 text-slate-600 text-[8px] font-black px-2 py-0.5 rounded uppercase tracking-wider">กลุ่มเรียน: <?php echo $group_filter ?: 'ทั้งหมด'; ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest">จำนวนรายชื่อ</p>
                                    <p class="text-lg font-black text-slate-800"><?php echo count($students_data); ?></p>
                                </div>
                            </div>

                            <?php if ($chart_image || $stats): ?>
                                <div class="flex gap-6 mb-8 bg-slate-50/50 p-6 rounded-[2rem] border border-slate-100 items-center">
                                    <!-- กราฟวงกลม -->
                                    <div class="w-1/3 flex justify-center items-center border-r border-slate-200 pr-6">
                                        <?php if (!empty($chart_image)): ?>
                                            <div class="relative w-36 h-32 flex items-center justify-center">
                                                <img src="<?php echo $chart_image; ?>" class="w-full h-full object-contain">
                                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" style="padding-top: 2px;">
                                                    <span class="text-xl font-black text-slate-800 leading-none"><?php echo $stats['percent'] ?? '0%'; ?></span>
                                                    <span class="text-[6px] font-bold text-slate-400 uppercase tracking-tighter mt-0.5">Success</span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-slate-300 text-[8px] font-black uppercase italic">ไม่พบข้อมูลกราฟ</div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- ข้อมูลตัวเลข -->
                                    <div class="flex-1 space-y-4">
                                        <h3 class="font-black text-slate-800 text-sm flex items-center gap-2 uppercase tracking-tight">
                                            <span class="w-2 h-2 bg-red-600 rounded-full shadow-[0_0_8px_rgba(220,38,38,0.4)]"></span>
                                            สรุปสถิติภาพรวม
                                        </h3>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                                                <p class="text-[7px] text-slate-400 font-black uppercase tracking-widest mb-0.5">ลงทะเบียนแล้ว</p>
                                                <p class="text-xl font-black text-emerald-600 leading-none">
                                                    <?php echo $stats['registered'] ?? '0'; ?> 
                                                    <span class="text-[8px] font-bold text-slate-300 uppercase ml-0.5">คน</span>
                                                </p>
                                            </div>
                                            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                                                <p class="text-[7px] text-slate-400 font-black uppercase tracking-widest mb-0.5">เป้าหมายทั้งหมด</p>
                                                <p class="text-xl font-black text-slate-800 leading-none">
                                                    <?php echo $stats['total'] ?? '0'; ?> 
                                                    <span class="text-[8px] font-bold text-slate-300 uppercase ml-0.5">คน</span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="bg-slate-900 p-3.5 rounded-2xl shadow-xl shadow-slate-900/10">
                                            <div class="flex justify-between items-center px-1">
                                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">ร้อยละความสำเร็จ</span>
                                                <span class="text-xl font-black text-white"><?php echo $stats['percent'] ?? '0%'; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <table class="report-content-table text-[8px] rounded-lg overflow-hidden shadow-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 30px;">คิว</th>
                                        <th style="width: 60px;">รหัสนักศึกษา</th>
                                        <th style="width: 80px;">เลขบัตรประชาชน</th>
                                        <th>ชื่อ-นามสกุล</th>
                                        <th style="width: 60px;">กลุ่มเรียน</th>
                                        <th style="width: 100px;">แผนกวิชา</th>
                                        <th>ชื่อผู้ปกครอง</th>
                                        <th style="width: 60px;">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($students_data) > 0): ?>
                                        <?php foreach ($students_data as $row): ?>
                                            <?php 
                                                $sid = $row['student_id'] ?? '';
                                                if (stripos($sid, 'E+') !== false) { $sid = number_format((float)$sid, 0, '', ''); }
                                                $icard = $row['id_card'] ?? '-';
                                                if (stripos($icard, 'E+') !== false) { $icard = number_format((float)$icard, 0, '', ''); }
                                                $rcode = $row['registration_code'] ?? '';
                                                if (stripos($rcode, 'E+') !== false) { $rcode = number_format((float)$rcode, 0, '', ''); }
                                            ?>
                                            <tr class="even:bg-slate-50/50">
                                                <td class="text-center font-mono auto-scale-cell">
                                                    <?php echo htmlspecialchars($rcode); ?>
                                                </td>
                                                <td class="text-center auto-scale-cell">
                                                    <?php echo htmlspecialchars($sid); ?>
                                                </td>
                                                <td class="text-center text-slate-500 auto-scale-cell">
                                                    <?php echo htmlspecialchars($icard); ?>
                                                </td>
                                                <td class="font-medium px-1.5 auto-scale-cell">
                                                    <?php echo htmlspecialchars($row['student_name']); ?>
                                                </td>
                                                <td class="text-center auto-scale-cell">
                                                    <?php echo htmlspecialchars($row['group_name']); ?>
                                                </td>
                                                <td class="px-1.5 auto-scale-cell">
                                                    <?php echo htmlspecialchars($row['department']); ?>
                                                </td>
                                                <td class="text-slate-600 px-1.5 auto-scale-cell">
                                                    <?php echo htmlspecialchars($row['parent_name'] ?: '-'); ?>
                                                </td>
                                                <td class="text-center auto-scale-cell">
                                                    <span class="<?php echo $row['status'] == 'ลงทะเบียนแล้ว' ? 'text-emerald-600 font-bold' : 'text-slate-300'; ?>">
                                                        <?php echo htmlspecialchars($row['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="8" class="p-10 text-center text-slate-300 italic bg-slate-50">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>

                            <div class="mt-6 pt-3 border-t border-slate-100 flex justify-between items-center text-[8px] text-slate-300 italic">
                                <div>Registration System | วิทยาลัยเทคนิคเชียงใหม่</div>
                                <div>ระบบสรุปรายงานอัตโนมัติ</div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td>
                            <div style="height: 20mm;"></div> <!-- 🌟 เว้นระยะขอบล่างในทุกหน้า -->
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </body>

    </html>
    <?php
}
exit;
?>