<?php
require_once 'config.php';
if (!check_auth()) {
    header('Location: index.php?login=1');
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
                    <h1 class="font-bold text-lg text-white">ระบบจัดการข้อมูลลงทะเบียน</h1>
                    <p class="text-[10px] text-red-100 uppercase tracking-widest">Chiang Mai Technical College</p>
                </div>
            </div>
            <div class="flex gap-2">
                <div id="refresh_timer" class="hidden md:flex items-center bg-red-800/50 px-3 py-1.5 rounded-full text-[10px] font-bold gap-2 border border-red-400/30">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                    AUTO REFRESH: <span id="timer_sec">30</span>S
                </div>
                <a href="index.php" class="bg-red-800 hover:bg-red-900 text-xs px-4 py-2 rounded-xl transition font-medium border border-red-600">ไปหน้าแรก</a>
                <a href="logout.php" class="bg-slate-800 hover:bg-slate-900 text-xs px-4 py-2 rounded-xl transition font-medium border border-slate-700">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

        <!-- แถวบน: สรุปภาพรวมและสถิติ -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- การเลือกแผนก -->
            <div class="lg:col-span-1 bg-white p-6 rounded-3xl shadow-sm border border-slate-200 space-y-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    ตัวกรองข้อมูล
                </h3>
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">เลือกแผนกวิชา</label>
                    <select id="filter_dept" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-2xl p-3 outline-none focus:ring-4 focus:ring-red-50 transition">
                        <option value="">ทุกแผนกวิชา</option>
                    </select>
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">เลือกชื่อกลุ่มเรียน</label>
                    <select id="filter_group" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-2xl p-3 outline-none focus:ring-4 focus:ring-red-50 transition">
                        <option value="">ทุกชื่อกลุ่มเรียน</option>
                    </select>
                </div>
            </div>

            <!-- สถิติตัวเลข -->
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <div class="relative h-[180px] flex items-center justify-center">
                    <canvas id="regChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span id="stat_percent" class="text-3xl font-black text-slate-800">0%</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase">ลงทะเบียนแล้ว</span>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-end border-b border-slate-100 pb-3">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">ลงทะเบียนสำเร็จ</p>
                            <h4 class="text-2xl font-bold text-green-600"><span id="stat_registered">0</span> <span class="text-xs font-normal text-slate-400">คน</span></h4>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">เป้าหมายทั้งหมด</p>
                            <h4 class="text-xl font-bold text-slate-800"><span id="stat_total">0</span> <span class="text-xs font-normal text-slate-400">คน</span></h4>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-[10px] font-bold text-blue-600 uppercase">ยอดรวมทั้งวิทยาลัย</span>
                            <span class="text-xs font-bold text-blue-800"><span id="all_reg">0</span> / <span id="all_total">0</span></span>
                        </div>
                        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                            <div id="all_progress_bar" class="bg-blue-600 h-full transition-all duration-1000" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- แถวกลาง: ระบบค้นหา (New!) -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 space-y-4">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    ค้นหาข้อมูลผู้เรียนแบบรวดเร็ว
                </h3>
                <div class="relative w-full md:w-96">
                    <input type="text" id="searchInput" placeholder="พิมพ์ชื่อ-นามสกุล หรือ รหัสผู้เรียน..." class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 pr-12 outline-none focus:ring-4 focus:ring-red-50 focus:border-red-600 transition-all font-medium text-sm">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </div>
                </div>
            </div>

            <div id="searchResults" class="hidden overflow-x-auto border-t border-slate-50 pt-4">
                <table class="w-full text-xs text-left">
                    <thead class="text-slate-400 font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-2">คิว</th>
                            <th class="px-4 py-2">รหัสผู้เรียน</th>
                            <th class="px-4 py-2">ชื่อ-นามสกุล</th>
                            <th class="px-4 py-2">กลุ่มเรียน</th>
                            <th class="px-4 py-2">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="searchResultsBody" class="divide-y divide-slate-50">
                        <!-- Search matches will appear here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- แถวล่าง: จัดการข้อมูลและส่งออก -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- อิมพอร์ต -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 space-y-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                    นำเข้าข้อมูล (CSV)
                </h3>
                <form id="importForm" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-3">
                    <input type="file" id="excel_file" name="excel_file" accept=".csv" required class="flex-1 text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer border border-slate-100 rounded-xl p-1 bg-slate-50">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded-xl transition shadow-md shadow-red-100 text-xs shrink-0">
                        IMPORT
                    </button>
                </form>
                <div class="flex justify-between items-center pt-2">
                    <span class="text-[10px] text-slate-400">*รองรับไฟล์ .CSV เท่านั้น</span>
                    <a href="process_admin.php?action=download_template" class="text-red-600 font-bold text-[10px] hover:underline">ดาวน์โหลดเทมเพลต CSV</a>
                </div>
            </div>

            <!-- ส่งออก -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-200 space-y-4">
                <h3 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    ส่งออกรายงาน (ตามเงื่อนไขที่เลือก)
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="exportData('pdf')" class="bg-slate-800 hover:bg-black text-white font-bold py-3.5 rounded-2xl transition shadow-lg text-xs flex items-center justify-center gap-2 uppercase tracking-widest">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                        PDF Report
                    </button>
                    <button onclick="exportData('excel')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 rounded-2xl transition shadow-lg text-xs flex items-center justify-center gap-2 uppercase tracking-widest">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Excel File
                    </button>
                </div>
            </div>
        </div>

    </div>

    <script>
        let regChart = null;
        let timer = 30;
        let searchTimeout = null;
        
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

        async function updateStats(isAuto = false) {
            const dept = document.getElementById('filter_dept').value;
            const group = document.getElementById('filter_group').value;
            try {
                const res = await fetch(`process_admin.php?action=get_stats&department=${encodeURIComponent(dept)}&group_name=${encodeURIComponent(group)}`);
                const data = await res.json();
                if (data.success) {
                    document.getElementById('stat_total').innerText = data.total;
                    document.getElementById('stat_registered').innerText = data.registered;
                    document.getElementById('stat_percent').innerText = data.percent;
                    document.getElementById('all_reg').innerText = data.all_reg;
                    document.getElementById('all_total').innerText = data.all_total;
                    
                    const progress = data.all_total > 0 ? (data.all_reg / data.all_total) * 100 : 0;
                    document.getElementById('all_progress_bar').style.width = progress + '%';
                    
                    renderChart(data.registered, data.total - data.registered);
                    if(isAuto) timer = 30; // Reset timer if manually or automatically updated successfully
                }
            } catch (e) { console.error('Error updating stats', e); }
        }

        // Auto Refresh Logic
        setInterval(() => {
            timer--;
            if (timer <= 0) {
                updateStats(true);
                timer = 30;
            }
            document.getElementById('timer_sec').innerText = timer;
        }, 1000);

        // Search Logic
        document.getElementById('searchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length < 2) {
                document.getElementById('searchResults').classList.add('hidden');
                return;
            }

            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`process_admin.php?action=search_students&query=${encodeURIComponent(query)}`);
                    const data = await res.json();
                    const tbody = document.getElementById('searchResultsBody');
                    tbody.innerHTML = '';
                    
                    if (data.success && data.list.length > 0) {
                        data.list.forEach(item => {
                            const statusColor = item.status === 'ลงทะเบียนแล้ว' ? 'text-green-600 font-bold' : 'text-slate-300';
                            const row = `
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-2.5 font-mono">${item.registration_code}</td>
                                    <td class="px-4 py-2.5">${item.student_id}</td>
                                    <td class="px-4 py-2.5 font-bold text-slate-700">${item.student_name}</td>
                                    <td class="px-4 py-2.5 text-slate-500">${item.group_name}</td>
                                    <td class="px-4 py-2.5 ${statusColor}">${item.status}</td>
                                </tr>
                            `;
                            tbody.insertAdjacentHTML('beforeend', row);
                        });
                        document.getElementById('searchResults').classList.remove('hidden');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="px-4 py-10 text-center text-slate-400 italic">ไม่พบข้อมูลที่ตรงกับคำค้นหา</td></tr>';
                        document.getElementById('searchResults').classList.remove('hidden');
                    }
                } catch (e) { console.error('Search error', e); }
            }, 300);
        });

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
                        backgroundColor: ['#ef4444', '#f1f5f9'],
                        hoverBackgroundColor: ['#dc2626', '#e2e8f0'],
                        borderWidth: 0,
                        weight: 2
                    }]
                },
                options: {
                    cutout: '80%',
                    responsive: true,
                    maintainAspectRatio: false,
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
                total: document.getElementById('stat_total').innerText,
                registered: document.getElementById('stat_registered').innerText,
                percent: document.getElementById('stat_percent').innerText
            };

            const form = document.createElement('form');
            form.method = 'POST';
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
