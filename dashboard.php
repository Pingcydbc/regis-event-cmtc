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

        <!-- 0. Dashboard สรุปข้อมูล -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800 mb-1">สรุปภาพรวมการลงทะเบียน</h2>
                    <p class="text-sm text-slate-500">แสดงสถิติจำนวนผู้ลงทะเบียนเทียบกับทั้งหมด</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full md:w-auto">
                    <select id="filter_dept" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-2.5 outline-none transition flex-1 md:flex-none min-w-[150px]">
                        <option value="">ทุกแผนกวิชา</option>
                    </select>
                    <select id="filter_level" class="bg-slate-50 border border-slate-300 text-slate-700 text-sm rounded-xl focus:ring-red-500 focus:border-red-500 block p-2.5 outline-none transition flex-1 md:flex-none min-w-[120px]">
                        <option value="">ทุกชั้นปี</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                <div class="relative h-[300px] flex items-center justify-center">
                    <canvas id="regChart"></canvas>
                </div>
                <div class="space-y-4">
                    <div class="bg-green-50 p-4 rounded-2xl border border-green-100">
                        <p class="text-xs text-green-600 font-bold uppercase tracking-wider mb-1">ลงทะเบียนแล้ว</p>
                        <div class="flex items-baseline gap-2">
                            <span id="stat_registered" class="text-3xl font-bold text-green-700">0</span>
                            <span class="text-sm text-green-600 font-medium">คน</span>
                        </div>
                    </div>
                    <div class="bg-slate-100 p-4 rounded-2xl border border-slate-200">
                        <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1">ยังไม่ลงทะเบียน</p>
                        <div class="flex items-baseline gap-2">
                            <span id="stat_not_registered" class="text-3xl font-bold text-slate-700">0</span>
                            <span class="text-sm text-slate-500 font-medium">คน</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-slate-100 flex justify-between items-center text-sm">
                        <span class="text-slate-400">จำนวนทั้งหมด</span>
                        <span id="stat_total" class="font-bold text-slate-800">0 คน</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-slate-400">คิดเป็นร้อยละ</span>
                        <span id="stat_percent" class="font-bold text-red-600 text-lg">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 mb-1">1. ดาวน์โหลดไฟล์ฟอร์แมต Excel เปล่า</h2>
                <p class="text-sm text-slate-500">ใช้สำหรับกรอกข้อมูลรายชื่อนักเรียนโครงสร้าง 9 คอลัมน์ก่อนนำเข้าสู่ระบบเว็บ</p>
            </div>
            <a href="process_admin.php?action=download_template" class="w-full md:w-auto bg-slate-800 hover:bg-slate-900 text-white font-medium px-5 py-3 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm shrink-0">
                📥 ดาวน์โหลดเทมเพลต Excel
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 mb-1">2. อิมพอร์ตข้อมูล (Import Excel เข้าฐานข้อมูล)</h2>
                <p class="text-sm text-slate-500">เลือกไฟล์ฟอร์แมตที่กรอกข้อมูลเสร็จแล้ว (บันทึกเป็นนามสกุล .csv เท่านั้น) เพื่ออัปเดตรายชื่อใหม่ทั้งหมด</p>
            </div>
            <form id="importForm" enctype="multipart/form-data" class="bg-slate-50 p-4 rounded-xl border border-dashed border-slate-300 flex flex-col sm:flex-row items-center gap-4">
                <input type="file" id="excel_file" name="excel_file" accept=".csv" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                <button type="submit" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-2.5 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm shrink-0">
                    🚀 เริ่มนำเข้าข้อมูล (Import)
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 mb-1">3. ส่งออกรายงาน (Export ข้อมูลผู้ลงทะเบียน)</h2>
                <p class="text-sm text-slate-500">ดาวน์โหลดรายงานสรุปการลงทะเบียนล่าสุด โดยจะทำการแยกแผ่นงาน (Sheet) ตามแผนกวิชาให้อัตโนมัติ</p>
            </div>
            <a href="export_report.php" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-3 rounded-xl transition duration-150 shadow-md flex items-center justify-center gap-2 text-sm shrink-0">
                📊 ส่งออกรายงานแยกแผนก (Export)
            </a>
        </div>

    </div>

    <script>
        // --- ส่วนจัดการ Dashboard & Chart ---
        let regChart = null;

        async function initDashboard() {
            // โหลดตัวเลือก Filter
            try {
                const resp = await fetch('process_admin.php?action=get_filters');
                const data = await resp.json();
                if (data.success) {
                    const deptSelect = document.getElementById('filter_dept');
                    const levelSelect = document.getElementById('filter_level');
                    
                    data.departments.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = opt.text = d;
                        deptSelect.add(opt);
                    });
                    
                    data.levels.forEach(l => {
                        const opt = document.createElement('option');
                        opt.value = opt.text = l;
                        levelSelect.add(opt);
                    });
                }
            } catch (e) { console.error('Error loading filters', e); }

            updateStats();
        }

        async function updateStats() {
            const dept = document.getElementById('filter_dept').value;
            const level = document.getElementById('filter_level').value;

            try {
                const resp = await fetch(`process_admin.php?action=get_stats&department=${encodeURIComponent(dept)}&level=${encodeURIComponent(level)}`);
                const data = await resp.json();

                if (data.success) {
                    document.getElementById('stat_registered').innerText = data.registered.toLocaleString();
                    document.getElementById('stat_not_registered').innerText = data.not_registered.toLocaleString();
                    document.getElementById('stat_total').innerText = data.total.toLocaleString() + ' คน';
                    
                    const percent = data.total > 0 ? Math.round((data.registered / data.total) * 100) : 0;
                    document.getElementById('stat_percent').innerText = percent + '%';

                    renderChart(data.registered, data.not_registered);
                }
            } catch (e) { console.error('Error updating stats', e); }
        }

        function renderChart(registered, not_registered) {
            const ctx = document.getElementById('regChart').getContext('2d');
            
            if (regChart) {
                regChart.data.datasets[0].data = [registered, not_registered];
                regChart.update();
                return;
            }

            regChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['ลงทะเบียนแล้ว', 'ยังไม่ลงทะเบียน'],
                    datasets: [{
                        data: [registered, not_registered],
                        backgroundColor: ['#16a34a', '#e2e8f0'],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { family: 'Sarabun', size: 12 } } },
                        tooltip: { callbacks: { label: (context) => ` ${context.label}: ${context.raw} คน` } }
                    },
                    cutout: '70%'
                }
            });
        }

        document.getElementById('filter_dept').addEventListener('change', updateStats);
        document.getElementById('filter_level').addEventListener('change', updateStats);

        // --- ส่วนจัดการ Import ---
        document.getElementById('importForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            Swal.fire({ title: 'กำลังนำเข้าข้อมูล...', text: 'ระบบกำลังบันทึกชุดข้อมูลใหม่ 9 คอลัมน์ครับ', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            const formData = new FormData(e.target);
            try {
                const response = await fetch('process_admin.php?action=import_data', { method: 'POST', body: formData });
                const data = await response.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'นำเข้าข้อมูลสำเร็จ!', text: data.message, confirmButtonColor: '#16a34a', customClass: { popup: 'rounded-2xl' } });
                    document.getElementById('importForm').reset();
                    updateStats(); // อัปเดต Dashboard หลังอิมพอร์ต
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' } });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'เชื่อมต่อล้มเหลว', text: 'ไม่สามารถติดต่อไฟล์หลังบ้านได้', confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' } });
            }
        });

        // เริ่มต้นการทำงาน
        initDashboard();
    </script>
</body>
</html>
