<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$login_error = '';

// จัดการการ Login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_action'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === '1234') {
        $_SESSION['loggedin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $login_error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}

// ตรวจสอบว่าต้องการแสดงหน้า Login หรือไม่
$show_login = isset($_GET['login']) || !empty($login_error);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบลงทะเบียนเข้าร่วมประชุมผู้ปกครอง - วิทยาลัยเทคนิคเชียงใหม่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-slate-100 via-stone-50 to-red-50 flex flex-col items-center justify-center min-h-screen p-4 space-y-6">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border-t-8 border-red-600 transform transition-all hover:scale-[1.01] relative overflow-hidden">
        <div class="absolute top-0 right-0 w-24 h-24 bg-red-500/5 rounded-full -mr-8 -mt-8"></div>

        <div class="flex flex-col items-center mb-8 relative z-10">
            <div class="mb-5 drop-shadow-xl">
                <img src="logo.png" alt="โลโก้วิทยาลัยเทคนิคเชียงใหม่" class="h-28 w-auto object-contain mx-auto">
            </div>
            <h2 class="text-2xl font-extrabold text-slate-800 text-center leading-tight">ระบบลงทะเบียนเข้าร่วม</h2>
            <h2 class="text-2xl font-extrabold text-slate-800 text-center leading-tight">ประชุมผู้ปกครอง</h2>
            <div class="h-1 w-16 bg-red-600 rounded-full my-3"></div>
            <p class="text-base font-bold text-red-600">วิทยาลัยเทคนิคเชียงใหม่</p>
            <p class="text-sm text-slate-500 font-medium mt-1">ประจำปีการศึกษา 2569</p>
        </div>

        <form id="checkForm" class="space-y-6 relative z-10">
            <div class="space-y-3">
                <label class="block text-sm font-bold text-slate-700 text-center uppercase tracking-wide">กรอกเลขประจำตัวประชาชน</label>
                <input type="text" id="id_card" name="id_card" placeholder="เช่น 150996600001" required autocomplete="off" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();" class="w-full px-5 py-4 border-2 border-slate-200 rounded-2xl text-center text-2xl font-black tracking-[0.1em] focus:ring-4 focus:ring-red-100 focus:border-red-600 outline-none transition-all duration-300 text-slate-800 placeholder:text-slate-300 placeholder:tracking-normal text-sm">
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-2xl transition-all duration-300 shadow-xl shadow-red-200 text-lg flex items-center justify-center gap-3 transform hover:-translate-y-1 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                ยืนยันข้อมูล
            </button>
        </form>

        <div class="mt-6 text-center border-t border-slate-100 pt-4">
            <span class="text-[10px] text-slate-400 tracking-wider uppercase">Chiang Mai Technical College</span>
        </div>
    </div>

    <!-- ปุ่มลับสำหรับเข้าหน้า Login (หรือจะกด /dashboard ตรงๆ ก็ได้) -->
    <a href="?login=1" class="text-slate-300 hover:text-slate-500 text-[10px] transition font-medium">จัดการระบบ</a>

    <!-- Modal ลงทะเบียน -->
    <div id="modal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 backdrop-blur-sm z-50">
        <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-md space-y-5 transform transition-all duration-300 border-t-4 border-red-600">
            <h3 class="text-lg font-bold text-slate-800 border-b pb-2 text-center text-red-600 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                ตรวจสอบข้อมูลผู้ลงทะเบียน
            </h3>

            <div class="space-y-2.5 text-sm border-b pb-4 border-slate-100">
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">เลขบัตรประชาชน:</span>
                    <span id="modal_id_card" class="font-bold text-slate-800 tracking-wider">-</span>
                </div>
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">ชื่อ-นามสกุล ผู้เรียน:</span>
                    <span id="modal_student_name" class="font-bold text-slate-800">-</span>
                </div>
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">ชื่อกลุ่มเรียน:</span>
                    <span id="modal_student_class" class="font-bold text-slate-800">-</span>
                </div>
                <div class="flex items-start">
                    <span class="text-slate-400 font-medium w-32 shrink-0">แผนกวิชา:</span>
                    <span id="modal_department" class="font-bold text-red-700">-</span>
                </div>
            </div>

            <form id="registerForm" class="space-y-4">
                <input type="hidden" id="modal_id_card_val" name="modal_id_card_val">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">ชื่อ-นามสกุล ผู้ปกครอง (ตรวจสอบ/แก้ไขได้)</label>
                    <input type="text" id="modal_parent_name" name="modal_parent_name" required autocomplete="off" class="w-full px-4 py-3 border border-slate-300 rounded-xl font-medium focus:ring-4 focus:ring-red-100 focus:border-red-600 outline-none transition text-slate-800">
                </div>

                <div class="flex space-x-3 pt-2">
                    <button type="button" onclick="closeModal()" class="w-1/3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-medium py-3 rounded-xl transition duration-150">ยกเลิก</button>
                    <button type="submit" class="w-2/3 bg-red-600 hover:bg-red-700 text-white font-medium py-3 rounded-xl transition duration-150 shadow-lg shadow-red-100">ยืนยันการลงทะเบียน</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🌟 Modal Login (สำหรับ Admin) -->
    <div id="loginModal" class="fixed inset-0 bg-black/70 <?php echo $show_login ? 'flex' : 'hidden'; ?> items-center justify-center p-4 backdrop-blur-md z-[60]">
        <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border-t-8 border-slate-800 relative transform transition-all scale-100">
            <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            
            <div class="flex flex-col items-center mb-6">
                <img src="logo.png" alt="Logo" class="h-16 mb-4">
                <h2 class="text-xl font-bold text-slate-800">เข้าสู่ระบบจัดการ</h2>
                <p class="text-xs text-slate-500">วิทยาลัยเทคนิคเชียงใหม่</p>
            </div>

            <?php if ($login_error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl text-xs mb-4 text-center border border-red-100 font-bold">
                    <?php echo $login_error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="login_action" value="1">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">ชื่อผู้ใช้</label>
                    <input type="text" name="username" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-4 focus:ring-slate-100 focus:border-slate-800 outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">รหัสผ่าน</label>
                    <input type="password" name="password" required class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:ring-4 focus:ring-slate-100 focus:border-slate-800 outline-none transition">
                </div>
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 rounded-xl transition shadow-lg mt-4 uppercase tracking-wider text-sm">
                    ลงชื่อเข้าใช้งาน
                </button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modal');
        function closeModal() { modal.classList.replace('flex', 'hidden'); }

        const loginModal = document.getElementById('loginModal');
        function closeLoginModal() { 
            loginModal.classList.replace('flex', 'hidden'); 
            // ลบ query string ?login=1 ออกจาก URL เพื่อความสวยงาม
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }

        document.getElementById('checkForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id_card = document.getElementById('id_card').value;

            try {
                const response = await fetch('process.php?action=check&id_card=' + id_card);
                const data = await response.json();

                if (data.success) {
                    document.getElementById('modal_id_card_val').value = id_card;
                    document.getElementById('modal_id_card').innerText = data.id_card;
                    document.getElementById('modal_student_name').innerText = data.student_name;
                    document.getElementById('modal_department').innerText = data.department;
                    document.getElementById('modal_student_class').innerText = data.group_name;
                    document.getElementById('modal_parent_name').value = data.parent_name;

                    modal.classList.replace('hidden', 'flex');
                } else {
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
