<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบลงทะเบียนประชุมผู้ปกครอง - วิทยาลัยเทคนิคเชียงใหม่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>

<body class="bg-gradient-to-br from-slate-100 via-stone-50 to-red-50 flex flex-col items-center justify-center min-h-screen p-4 space-y-6">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border-t-8 border-red-600 transform transition-all hover:scale-[1.01] relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-full -mr-8 -mt-8"></div>

        <div class="flex flex-col items-center mb-6 relative z-10">
            <div class="mb-4 drop-shadow-md">
                <img src="logo.png" alt="โลโก้วิทยาลัยเทคนิคเชียงใหม่" class="h-24 w-auto object-contain mx-auto">
            </div>
            <h2 class="text-2xl font-bold text-slate-800 text-center tracking-tight">ระบบลงทะเบียนออนไลน์</h2>
            <p class="text-sm font-medium text-red-600 mt-1">วิทยาลัยเทคนิคเชียงใหม่</p>
            <p class="text-xs text-slate-400 mt-1">การประชุมผู้ปกครองภาคเรียนปัจจุบัน</p>
        </div>

        <form id="checkForm" class="space-y-5 relative z-10">
            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-2 text-center">กรอกเลขรหัสคิวลงทะเบียน (4 หลัก)</label>
                <input type="text" id="reg_code" name="reg_code" placeholder="เช่น 0001" required
                    autocomplete="off" inputmode="numeric"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4);"
                    class="w-full px-4 py-3 border border-slate-300 rounded-xl text-center text-3xl font-bold tracking-widest focus:ring-4 focus:ring-red-100 focus:border-red-600 outline-none transition duration-200 text-slate-800">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3.5 rounded-xl transition duration-200 shadow-lg shadow-red-200 text-base flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                ตรวจสอบข้อมูลรหัสคิว
            </button>
        </form>

        <div class="mt-6 text-center border-t border-slate-100 pt-4">
            <span class="text-[10px] text-slate-400 tracking-wider uppercase">Chiang Mai Technical College</span>
        </div>
    </div>

    <!-- ส่วน Dashboard สำหรับ User (ปรับปรุงขนาด Dropdown) -->
    <div class="bg-white p-6 rounded-2xl shadow-lg w-full max-w-md border-b-4 border-slate-200 space-y-6">
        <div class="space-y-4">
            <h3 class="font-bold text-slate-700 flex items-center gap-2 text-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                สถิติการลงทะเบียน
            </h3>
            
            <!-- ปรับขนาด Dropdown ให้ใหญ่ขึ้นสำหรับมือถือ -->
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight ml-1">แผนกวิชา</label>
                    <select id="filter_dept" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                        <option value="">ทุกแผนก</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-tight ml-1">ชั้นปี</label>
                    <select id="filter_level" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-3 text-sm font-medium outline-none focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all appearance-none bg-[url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C/polyline%3E%3C/svg%3E')] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat pr-10">
                        <option value="">ทุกชั้นปี</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-6 pt-2">
            <div class="w-32 h-32 shrink-0">
                <canvas id="userRegChart"></canvas>
            </div>
            <div class="flex-1 space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">ลงทะเบียนแล้ว</span>
                    <span id="stat_registered" class="font-bold text-green-600 text-base">0</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">ทั้งหมด</span>
                    <span id="stat_total" class="font-bold text-slate-800 text-base">0</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 mt-2">
                    <div id="stat_progress" class="bg-red-600 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <p class="text-right text-sm font-bold text-red-600" id="stat_percent">0%</p>
            </div>
        </div>
    </div>

    <div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 backdrop-blur-sm z-50">
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md space-y-5 transform transition-all duration-300 border-t-4 border-red-600">
            <h3 class="text-lg font-bold text-slate-800 border-b pb-2 text-center text-red-600 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                ตรวจสอบข้อมูลผู้ลงทะเบียน
            </h3>

            <div class="space-y-2.5 text-sm border-b pb-4 border-slate-100">
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">รหัสนักศึกษา:</span>
                    <span id="modal_student_id" class="font-bold text-slate-800 tracking-wider">-</span>
                </div>
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">ชื่อ-นามสกุล นักศึกษา:</span>
                    <span id="modal_student_name" class="font-bold text-slate-800">-</span>
                </div>
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">ระดับชั้น / ห้อง:</span>
                    <span id="modal_student_class" class="font-bold text-slate-800">-</span>
                </div>
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">แผนกวิชา:</span>
                    <span id="modal_department" class="font-bold text-red-700">-</span>
                </div>
            </div>

            <form id="registerForm" class="space-y-4">
                <input type="hidden" id="modal_reg_code" name="modal_reg_code">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">ชื่อ-นามสกุล ผู้ปกครอง (ตรวจสอบ/แก้ไขได้)</label>
                    <input type="text" id="modal_parent_name" name="modal_parent_name" required autocomplete="off"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl font-medium focus:ring-4 focus:ring-red-100 focus:border-red-600 outline-none transition text-slate-800">
                </div>

                <div class="flex space-x-3 pt-2">
                    <button type="button" onclick="closeModal()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium py-3 rounded-xl transition duration-150">ยกเลิก</button>
                    <button type="submit" class="w-2/3 bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-xl transition duration-150 shadow-lg shadow-red-100">ยืนยันการลงทะเบียน</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modal');
        function closeModal() { modal.classList.replace('flex', 'hidden'); }

        // --- Stats Dashboard for Index ---
        let userChart = null;

        async function initStats() {
            try {
                // Load Filters (Re-use admin actions since they are relevant)
                const fResp = await fetch('process_admin.php?action=get_filters');
                const fData = await fResp.json();
                if (fData.success) {
                    const dSel = document.getElementById('filter_dept');
                    const lSel = document.getElementById('filter_level');
                    fData.departments.forEach(d => { dSel.add(new Option(d, d)); });
                    fData.levels.forEach(l => { lSel.add(new Option(l, l)); });
                }
            } catch(e) { console.error(e); }
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
                    document.getElementById('stat_total').innerText = data.total.toLocaleString();
                    const percent = data.total > 0 ? Math.round((data.registered / data.total) * 100) : 0;
                    document.getElementById('stat_percent').innerText = percent + '%';
                    document.getElementById('stat_progress').style.width = percent + '%';
                    renderChart(data.registered, data.not_registered);
                }
            } catch(e) { console.error(e); }
        }

        function renderChart(reg, not) {
            const ctx = document.getElementById('userRegChart').getContext('2d');
            if (userChart) {
                userChart.data.datasets[0].data = [reg, not];
                userChart.update();
                return;
            }
            userChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [reg, not],
                        backgroundColor: ['#16a34a', '#f1f5f9'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '80%',
                    plugins: { tooltip: { enabled: false }, legend: { display: false } }
                }
            });
        }

        document.getElementById('filter_dept').addEventListener('change', updateStats);
        document.getElementById('filter_level').addEventListener('change', updateStats);
        initStats();

        // --- Original Functions ---
        document.getElementById('checkForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const code = document.getElementById('reg_code').value;

            try {
                const response = await fetch('process.php?action=check&code=' + code);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('modal_reg_code').value = code;
                    document.getElementById('modal_student_id').innerText = data.student_id;
                    document.getElementById('modal_student_name').innerText = data.student_name;
                    document.getElementById('modal_department').innerText = data.department;
                    document.getElementById('modal_student_class').innerText = data.level + ' / ห้อง ' + data.room;
                    document.getElementById('modal_parent_name').value = data.parent_name;

                    modal.classList.replace('hidden', 'flex');
                } else {
                    // ปรับแต่ง Pop-up กรณีลงทะเบียนซ้ำ
                    if (data.message.includes('ลงทะเบียนเสร็จสิ้นไปแล้ว')) {
                        Swal.fire({
                            icon: 'info', 
                            title: 'ลงทะเบียนแล้ว', 
                            text: data.message,
                            confirmButtonText: 'รับทราบ',
                            confirmButtonColor: '#3085d6',
                            customClass: { popup: 'rounded-2xl' }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error', title: 'ไม่พบข้อมูล', text: data.message,
                            confirmButtonText: 'ลองอีกครั้ง', confirmButtonColor: '#dc2626',
                            customClass: { popup: 'rounded-2xl' }
                        });
                    }
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error', title: 'เชื่อมต่อล้มเหลว', text: 'ระบบขัดข้อง ไม่สามารถติดต่อไฟล์ประมวลผลได้',
                    confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' }
                });
            }
        });

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('process.php?action=register', { method: 'POST', body: formData });
                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success', title: 'ลงทะเบียนสำเร็จ!', text: data.message,
                        confirmButtonText: 'ตกลง', confirmButtonColor: '#16a34a',
                        customClass: { popup: 'rounded-2xl' }
                    }).then(() => {
                        closeModal();
                        document.getElementById('checkForm').reset();
                        updateStats(); // Refresh stats after registration
                    });
                } else {
                    if (data.message.includes('ถูกลงทะเบียนไปก่อนหน้า')) {
                         Swal.fire({
                            icon: 'info',
                            title: 'ลงทะเบียนแล้ว',
                            text: data.message,
                            confirmButtonColor: '#3085d6', confirmButtonText: 'ปิดหน้าต่าง',
                            customClass: { popup: 'rounded-2xl' }
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning', title: 'ปฏิเสธการลงทะเบียน', text: data.message,
                            confirmButtonColor: '#ea580c', confirmButtonText: 'ปิดหน้าต่าง',
                            customClass: { popup: 'rounded-2xl' }
                        });
                    }
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ระบบขัดข้อง ไม่สามารถบันทึกข้อมูลได้',
                    confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' }
                });
            }
        });
    </script>
</body>
</html>
