<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ผู้ดูแลระบบ - วิทยาลัยเทคนิคเชียงใหม่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-red-700 text-white shadow-md">
        <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="logo.png" alt="Logo" class="h-10 w-auto bg-white rounded-full p-0.5">
                <div>
                    <h1 class="font-bold text-lg">ระบบจัดการข้อมูลลงทะเบียน</h1>
                    <p class="text-xs text-red-200">Chiang Mai Technical College</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="index.php" class="bg-red-800 hover:bg-red-900 text-xs px-3 py-2 rounded-xl transition font-medium border border-red-600">ไปหน้าแรกเว็บ</a>
                <a href="logout.php" class="bg-slate-800 hover:bg-slate-900 text-xs px-3 py-2 rounded-xl transition font-medium border border-slate-700">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-10 space-y-8">

        <!-- Dashboard สรุปข้อมูล -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 mb-1">สรุปภาพรวมการลงทะเบียน</h2>
                    <p class="text-sm text-slate-500">แสดงสถิติจำนวนผู้ลงทะเบียนเทียบกับทั้งหมด</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full md:w-auto">
                    <select id="filter_dept" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-2.5 outline-none transition flex-1 md:flex-none w-full md:w-[220px]">
                        <option value="">ทุกแผนกวิชา</option>
                    </select>
                    <select id="filter_group" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-2.5 outline-none transition flex-1 md:flex-none w-full md:w-[220px]">
                        <option value="">ทุกชื่อกลุ่มเรียน</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="relative h-[250px] flex items-center justify-center">
                    <canvas id="regChart"></canvas>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-green-50 p-4 rounded-2xl border border-green-100 col-span-2">
                        <p class="text-xs text-green-600 font-bold uppercase tracking-wider mb-1">ลงทะเบียนแล้ว (ตามที่เลือก)</p>
                        <div class="flex items-baseline gap-2">
                            <span id="stat_registered" class="text-3xl font-bold text-green-700">0</span>
                            <span class="text-sm text-green-600 font-medium">คน</span>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 col-span-2">
                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wider mb-1">สถิติรวมทั้งวิทยาลัย</p>
                        <div class="flex flex-col">
                            <span class="text-xl font-bold text-blue-700"><span id="all_reg">0</span> / <span id="all_total">0</span></span>
                            <span class="text-[10px] text-blue-500">ลงทะเบียนแล้ว / ทั้งหมด (ทุกแผนก)</span>
                        </div>
                    </div>
                    <div class="bg-slate-100 p-4 rounded-2xl border border-slate-200 col-span-2">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">จำนวนทั้งหมดที่เลือก</p>
                                <span id="stat_total" class="text-xl font-bold text-slate-800">0 คน</span>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">คิดเป็นร้อยละ</p>
                                <span id="stat_percent" class="text-2xl font-bold text-red-600">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 mb-1">1. ดาวน์โหลดไฟล์ฟอร์แมต CSV เปล่า</h2>
                <p class="text-sm text-slate-500">ใช้สำหรับกรอกข้อมูลรายชื่อผู้เรียน (คอลัมน์ A-J) ก่อนนำเข้าสู่ระบบ</p>
            </div>
            <a href="process_admin.php?action=download_template" class="w-full md:w-auto bg-slate-800 hover:bg-slate-900 text-white font-medium px-5 py-3 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm shrink-0">
                📥 ดาวน์โหลดเทมเพลต CSV
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 mb-1">2. อิมพอร์ตข้อมูล (Import CSV เข้าฐานข้อมูล)</h2>
                <p class="text-sm text-slate-500">เลือกไฟล์ CSV (คอลัมน์ A-J, เริ่มข้อมูลแถวที่ 3) เพื่ออัปเดตรายชื่อใหม่</p>
            </div>
            <form id="importForm" enctype="multipart/form-data" class="bg-slate-50 p-4 rounded-xl border border-dashed border-slate-300 flex flex-col sm:flex-row items-center gap-4">
                <input type="file" id="excel_file" name="excel_file" accept=".csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                <button type="submit" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-2.5 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm shrink-0">
                    🚀 เริ่มนำเข้าข้อมูล (Import)
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 mb-1">3. ส่งออกรายงาน (Export)</h2>
                    <p class="text-sm text-slate-500">ดาวน์โหลดรายงานสรุปการลงทะเบียนตามเงื่อนไขที่เลือกใน Dashboard</p>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <button onclick="exportData('excel')" class="flex-1 md:flex-none bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-3 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm">
                        📊 Excel
                    </button>
                    <button onclick="exportData('pdf')" class="flex-1 md:flex-none bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-3 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm">
                        📄 PDF
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        let regChart = null;
        
        async function initDashboard() {
            try {
                const res = await fetch('process_admin.php?action=get_filters');
                const data = await res.json();
                if (data.success) {
                    const deptSelect = document.getElementById('filter_dept');
                    data.departments.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = opt.textContent = d;
                        deptSelect.appendChild(opt);
                    });
                    updateGroupDropdown(data.groups);
                }
            } catch (e) { console.error('Error loading filters', e); }
            updateStats();
        }

        async function loadGroupsByDept() {
            const dept = document.getElementById('filter_dept').value;
            try {
                const res = await fetch(`process_admin.php?action=get_filters&department=${encodeURIComponent(dept)}`);
                const data = await res.json();
                if (data.success) {
                    updateGroupDropdown(data.groups);
                }
            } catch (e) { console.error('Error loading groups', e); }
            updateStats();
        }

        function updateGroupDropdown(groups) {
            const groupSelect = document.getElementById('filter_group');
            const currentValue = groupSelect.value;
            groupSelect.innerHTML = '<option value="">ทุกชื่อกลุ่มเรียน</option>';
            groups.forEach(g => {
                const opt = document.createElement('option');
                opt.value = opt.textContent = g;
                if (g === currentValue) opt.selected = true;
                groupSelect.appendChild(opt);
            });
        }

        async function updateStats() {
            const dept = document.getElementById('filter_dept').value;
            const group = document.getElementById('filter_group').value;
            try {
                const res = await fetch(`process_admin.php?action=get_stats&department=${encodeURIComponent(dept)}&group_name=${encodeURIComponent(group)}`);
                const data = await res.json();
                if (data.success) {
                    document.getElementById('stat_total').innerText = data.total + ' คน';
                    document.getElementById('stat_registered').innerText = data.registered;
                    document.getElementById('stat_percent').innerText = data.percent;
                    document.getElementById('all_reg').innerText = data.all_reg;
                    document.getElementById('all_total').innerText = data.all_total;
                    renderChart(data.registered, data.total - data.registered);
                }
            } catch (e) { console.error('Error updating stats', e); }
        }

        function renderChart(reg, notReg) {
            const ctx = document.getElementById('regChart').getContext('2d');
            if (regChart) {
                regChart.data.datasets[0].data = [reg, notReg];
                regChart.update();
                return;
            }
            regChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['ลงทะเบียนแล้ว', 'ยังไม่ลงทะเบียน'],
                    datasets: [{
                        data: [reg, notReg],
                        backgroundColor: ['#16a34a', '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '75%',
                    plugins: { legend: { display: false } }
                }
            });
        }

        document.getElementById('filter_dept').addEventListener('change', loadGroupsByDept);
        document.getElementById('filter_group').addEventListener('change', updateStats);

        function exportData(type) {
            const dept = document.getElementById('filter_dept').value;
            const group = document.getElementById('filter_group').value;
            const chartData = document.getElementById('regChart').toDataURL();
            
            const stats = {
                total: document.getElementById('stat_total').innerText.replace(' คน', ''),
                registered: document.getElementById('stat_registered').innerText,
                percent: document.getElementById('stat_percent').innerText
            };

            const form = document.createElement('form');
            form.method = 'POST';
            // สำหรับ PDF ใช้ export_report.php สำหรับ Excel ใช้ export_excel.php (ถ้ามี) หรือประมวลผลตาม format
            form.action = type === 'pdf' ? 'export_report.php' : 'export_report.php?format=excel';
            form.target = '_blank';

            const fields = {
                department: dept,
                group_name: group,
                chart_image: chartData,
                stats: JSON.stringify(stats)
            };

            for (const key in fields) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = fields[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
        }

        document.getElementById('importForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            Swal.fire({
                title: 'กำลังนำเข้าข้อมูล...',
                text: 'กรุณารอสักครู่ ระบบกำลังประมวลผลไฟล์ CSV',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const formData = new FormData(e.target);
            try {
                const response = await fetch('process_admin.php?action=import_data', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, confirmButtonColor: '#dc2626' })
                    .then(() => { window.location.reload(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, confirmButtonColor: '#dc2626' });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'การเชื่อมต่อขัดข้อง', text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้', confirmButtonColor: '#dc2626' });
            }
        });

        initDashboard();
    </script>
</body>
</html>