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
        body { font-family: 'Sarabun', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .animate-fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .card-shadow { box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08), 0 4px 15px -8px rgba(0, 0, 0, 0.04); }
        .btn-hover { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
        .btn-hover:hover { transform: translateY(-1px); }
        .btn-hover:active { transform: translateY(0); scale: 0.98; }
    </style>
</head>

<body class="bg-[#f8fafc] flex flex-col items-center justify-center min-h-screen p-4 space-y-6">

    <div class="bg-white p-8 rounded-[2rem] card-shadow w-full max-w-sm border-t-[8px] border-red-700 transform transition-all relative overflow-hidden animate-fade-in">
        <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-full -mr-12 -mt-12 opacity-50"></div>

        <div class="flex flex-col items-center mb-8 relative z-10">
            <div class="mb-4">
                <img src="logo.png" alt="โลโก้วิทยาลัยเทคนิคเชียงใหม่" class="h-24 w-auto object-contain drop-shadow-lg">
            </div>
            <div class="space-y-1 text-center">
                <h2 class="text-xl font-black text-slate-800 leading-tight">ระบบลงทะเบียนเข้าร่วม</h2>
                <h2 class="text-xl font-black text-slate-800 leading-tight">ประชุมผู้ปกครอง</h2>
                <h2 class="text-lg font-black text-red-700 leading-tight">วิทยาลัยเทคนิคเชียงใหม่</h2>
            </div>
            <div class="h-1 w-10 bg-red-600 rounded-full my-3 shadow-[0_0_8px_rgba(220,38,38,0.3)]"></div>
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">ประจำปีการศึกษา 2569</p>
        </div>

        <form id="checkForm" class="space-y-6 relative z-10">
            <div class="space-y-2">
                <label class="block text-[9px] font-black text-slate-400 text-center uppercase tracking-widest">กรอกเลขประจำตัวประชาชน (นักเรียน นักศึกษา)</label>
                <input type="text" id="id_card" name="id_card" placeholder="เช่น 150996600001" required autocomplete="off" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl text-center text-xl font-black tracking-widest focus:ring-8 focus:ring-red-50 focus:border-red-600 outline-none transition-all duration-300 text-slate-800 placeholder:text-slate-200">
            </div>

            <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl transition-all duration-300 shadow-xl shadow-red-700/10 text-xs uppercase tracking-widest flex items-center justify-center gap-3 btn-hover">
                ยืนยันข้อมูล
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </button>
        </form>

        <div class="mt-8 text-center border-t border-slate-50 pt-5">
            <span class="text-[8px] text-slate-300 tracking-widest uppercase font-black">Chiang Mai Technical College</span>
        </div>
    </div>

    <!-- ปุ่มลับสำหรับเข้าหน้า Login -->
    <a href="?login=1" class="text-slate-300 hover:text-slate-500 text-[9px] transition font-black uppercase tracking-widest opacity-50 hover:opacity-100">จัดการระบบ</a>

    <!-- Chat Widget (Persistent) -->
    <div id="chat_widget" class="fixed bottom-4 right-4 z-[100] flex flex-col items-end gap-4 max-w-[calc(100vw-2rem)]">
        <!-- Chat Window -->
        <div id="chat_window" class="hidden w-[300px] sm:w-[350px] h-[480px] max-h-[75vh] bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col border border-slate-100 transition-all duration-500 transform translate-y-4 opacity-0">
            <div class="bg-red-700 p-5 text-white flex justify-between items-center shadow-lg shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center backdrop-blur-md shadow-inner">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" /></svg>
                    </div>
                    <div>
                        <h4 class="font-black text-xs uppercase tracking-wider leading-none">แจ้งปัญหา</h4>
                        <p class="text-[8px] text-red-200 uppercase tracking-widest mt-1 flex items-center gap-1">
                            <span class="w-1 h-1 bg-green-400 rounded-full animate-pulse"></span>
                            เจ้าหน้าที่ออนไลน์
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- ปุ่มย่อแชท -->
                    <button onclick="toggleChat()" class="bg-white/5 hover:bg-white/10 p-2 rounded-lg transition-all" title="ย่อหน้าต่าง">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                    <!-- ปุ่มจบแชท (กากบาท) -->
                    <button onclick="endChat()" class="bg-white/5 hover:bg-white/10 p-2 rounded-lg transition-all" title="จบการสนทนาและลบข้อมูล">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
            
            <div id="chat_body" class="flex-1 overflow-y-auto p-5 space-y-4 bg-slate-50/50 custom-scrollbar">
                <div class="text-center py-16 text-slate-300 text-[9px] font-black uppercase tracking-widest italic">กำลังเริ่มการสนทนา...</div>
            </div>

            <form id="chat_form" class="p-4 bg-white border-t border-slate-100 flex gap-2 shrink-0 items-center">
                <input type="text" id="chat_input" placeholder="พิมพ์ข้อความที่นี่..." autocomplete="off" class="flex-1 bg-slate-50 border-2 border-slate-100 rounded-xl px-5 py-3 text-xs font-bold focus:ring-4 focus:ring-red-50 focus:border-red-600 outline-none transition-all">
                <button type="submit" class="bg-red-700 text-white w-10 h-10 rounded-xl hover:bg-red-800 transition-all shadow-lg flex items-center justify-center shrink-0 btn-hover group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform" viewBox="0 0 20 20" fill="currentColor"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" /></svg>
                </button>
            </form>
        </div>

        <!-- Floating Button -->
        <button id="chat_toggle_btn" onclick="toggleChat()" class="bg-red-600 hover:bg-red-700 text-white font-black p-3 px-5 rounded-full shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center gap-3 border border-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="text-[10px] uppercase tracking-widest font-black">ติดต่อเรา</span>
            <span id="chat_badge" class="hidden absolute -top-1 -right-1 w-4 h-4 bg-red-800 rounded-full border-2 border-white animate-pulse"></span>
        </button>
    </div>

    <!-- Modal ลงทะเบียน -->
    <div id="modal" class="fixed inset-0 bg-slate-900/60 hidden items-center justify-center p-4 backdrop-blur-md z-50">
        <div class="bg-white p-8 rounded-[2.5rem] shadow-2xl w-full max-w-sm space-y-6 transform transition-all duration-500 border-t-[10px] border-red-700 animate-fade-in">
            <h3 class="text-lg font-black text-slate-800 pb-3 text-center border-b border-slate-50 uppercase tracking-tight flex items-center justify-center gap-2.5">
                <div class="w-8 h-8 bg-red-50 rounded-xl flex items-center justify-center text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                </div>
                ตรวจสอบข้อมูลผู้ลงทะเบียน
            </h3>

            <div class="space-y-3.5 text-xs font-bold border-b border-slate-50 pb-6">
                <div class="flex items-center justify-between bg-slate-50 p-3.5 rounded-2xl">
                    <span class="text-slate-400 uppercase tracking-widest text-[8px]">National ID:</span>
                    <span id="modal_id_card" class="text-slate-800 tracking-widest font-black text-sm">1234567890123</span>
                </div>
                <div class="space-y-2.5 px-1.5">
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-slate-300 uppercase tracking-widest text-[8px] shrink-0">ชื่อนักเรียน</span>
                        <span id="modal_student_name" class="text-slate-800 font-black text-right truncate">นายสมชาย แสนดี</span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-slate-300 uppercase tracking-widest text-[8px] shrink-0">กลุ่มเรียน</span>
                        <span id="modal_student_class" class="text-slate-800 font-black text-right">IT.67.1</span>
                    </div>
                    <div class="flex justify-between items-center gap-4">
                        <span class="text-slate-300 uppercase tracking-widest text-[8px] shrink-0">แผนกวิชา</span>
                        <span id="modal_department" class="text-red-700 font-black text-right truncate">เทคโนโลยีสารสนเทศ</span>
                    </div>
                </div>
            </div>

            <form id="registerForm" class="space-y-5">
                <input type="hidden" id="modal_id_card_val" name="modal_id_card_val">
                <div class="group">
                    <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 px-1 transition-colors group-focus-within:text-red-600">ชื่อ-นามสกุล ผู้ปกครอง (แก้ไขได้)</label>
                    <input type="text" id="modal_parent_name" name="modal_parent_name" required autocomplete="off" class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl font-black focus:ring-4 focus:ring-red-50 focus:border-red-600 outline-none transition-all text-slate-800 text-sm">
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal()" class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-400 font-black py-4 rounded-xl transition-all text-[9px] uppercase tracking-widest btn-hover">ยกเลิก</button>
                    <button type="submit" class="flex-[2] bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-xl transition-all shadow-lg text-[9px] uppercase tracking-widest btn-hover">ยืนยันลงทะเบียน</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 🌟 Modal Login (สำหรับ Admin) -->
    <div id="loginModal" class="fixed inset-0 bg-slate-900/80 <?php echo $show_login ? 'flex' : 'hidden'; ?> items-center justify-center p-4 backdrop-blur-xl z-[60]">
        <div class="bg-white p-10 rounded-[2.5rem] shadow-2xl w-full max-w-sm border-t-[10px] border-slate-900 relative transform transition-all animate-fade-in">
            <button onclick="closeLoginModal()" class="absolute top-6 right-6 text-slate-300 hover:text-slate-900 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            <div class="flex flex-col items-center mb-8">
                <img src="logo.png" alt="Logo" class="h-16 mb-4 drop-shadow-lg">
                <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight">เข้าสู่ระบบจัดการ</h2>
                <p class="text-[9px] text-slate-400 font-black uppercase tracking-widest mt-1.5 italic">Authorized Access Only</p>
            </div>
            <?php if ($login_error): ?>
                <div class="bg-red-50 text-red-600 p-3 rounded-xl text-[9px] mb-6 text-center border border-red-100 font-black uppercase tracking-widest animate-pulse"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <form method="POST" class="space-y-5">
                <input type="hidden" name="login_action" value="1">
                <div class="space-y-1.5">
                    <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                    <input type="text" name="username" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl font-black focus:ring-4 focus:ring-slate-100 focus:border-slate-900 outline-none transition-all text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                    <input type="password" name="password" required class="w-full px-5 py-3.5 bg-slate-50 border-2 border-slate-100 rounded-xl font-black focus:ring-4 focus:ring-slate-100 focus:border-slate-900 outline-none transition-all text-sm">
                </div>
                <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-black py-4 rounded-xl transition-all shadow-lg mt-4 uppercase tracking-widest text-[10px] btn-hover">เข้าสู่ระบบ</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById('modal');
        function closeModal() { modal.classList.replace('flex', 'hidden'); }

        const loginModal = document.getElementById('loginModal');
        function closeLoginModal() { 
            loginModal.classList.replace('flex', 'hidden'); 
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }

        document.getElementById('checkForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const id_card = document.getElementById('id_card').value;
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'กำลังตรวจสอบ...';

            try {
                const response = await fetch('process.php?action=check&t=' + Date.now() + '&id_card=' + id_card, { 
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store' 
                });
                const text = await response.text();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                // ตรวจสอบกรณีติดระบบป้องกัน Bot ของ Hosting
                if (text.includes('aes.js') || text.includes('__test=')) {
                    throw new Error("ระบบเครือข่ายมีการบล็อก (Anti-Bot) กรุณารีเฟรชหน้าเว็บแล้วลองใหม่");
                }

                let data;
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    throw new Error("Invalid Server Response");
                }

                if (data.success) {
                    document.getElementById('modal_id_card_val').value = id_card;
                    document.getElementById('modal_id_card').innerText = data.id_card;
                    document.getElementById('modal_student_name').innerText = data.student_name;
                    document.getElementById('modal_department').innerText = data.department;
                    document.getElementById('modal_student_class').innerText = data.group_name;
                    document.getElementById('modal_parent_name').value = data.parent_name;
                    modal.classList.replace('hidden', 'flex');
                } else {
                    Swal.fire({ icon: data.message.includes('แล้ว') ? 'info' : 'error', title: 'แจ้งเตือน', text: data.message, confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' } });
                }
            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ระบบขัดข้อง: ' + error.message, confirmButtonColor: '#dc2626' });
            }
        });

        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'กำลังบันทึก...';

            try {
                const response = await fetch('process.php?action=register&t=' + Date.now(), { 
                    method: 'POST', 
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    cache: 'no-store'
                });
                const text = await response.text();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (text.includes('aes.js')) throw new Error("ติดระบบป้องกัน Bot ของ Hosting");

                let data;
                try {
                    data = JSON.parse(text);
                } catch (err) {
                    throw new Error("Invalid Server Response");
                }

                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: data.message, confirmButtonColor: '#16a34a' }).then(() => { 
                        closeModal(); 
                        document.getElementById('checkForm').reset(); 
                    });
                } else {
                    Swal.fire({ icon: 'warning', text: data.message, confirmButtonColor: '#ea580c' });
                }
            } catch (error) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                Swal.fire({ icon: 'error', text: 'ระบบขัดข้อง: ' + error.message, confirmButtonColor: '#dc2626' });
            }
        });

        // --- Chat Logic ---
        let chatPollInterval = null;
        let lastMessageCount = 0;

        function toggleChat() {
            const window = document.getElementById('chat_window');
            const isHidden = window.classList.contains('hidden');
            if (isHidden) {
                window.classList.remove('hidden');
                setTimeout(() => { window.classList.remove('translate-y-4', 'opacity-0'); window.classList.add('translate-y-0', 'opacity-100'); }, 10);
                startPolling();
                document.getElementById('chat_badge').classList.add('hidden');
            } else {
                window.classList.remove('translate-y-0', 'opacity-100');
                window.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => window.classList.add('hidden'), 300);
                stopPolling();
            }
        }

        async function endChat() {
            const result = await Swal.fire({
                title: 'จบการสนทนา?',
                text: "ข้อมูลการแชททั้งหมดจะถูกลบออกจากระบบ",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ยืนยัน จบการสนทนา',
                cancelButtonText: 'ยกเลิก',
                customClass: { popup: 'rounded-3xl' }
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch('msg_system.php?action=clear_chat');
                    const data = await res.json();
                    if (data.success) {
                        lastMessageCount = 0;
                        fetchMessages();
                        toggleChat();
                        Swal.fire({ icon: 'success', title: 'จบการสนทนาแล้ว', timer: 1500, showConfirmButton: false, customClass: { popup: 'rounded-2xl' } });
                    }
                } catch (e) {
                    console.error("End Chat Error:", e);
                }
            }
        }

        async function fetchMessages() {
            try {
                const res = await fetch('msg_system.php?action=get_messages', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const text = await res.text();
                if (text.includes('<html>')) return;
                const data = JSON.parse(text);
                if (data.success) {
                    renderMessages(data.messages, data.my_id);
                    if (data.messages.length > lastMessageCount) {
                        const body = document.getElementById('chat_body');
                        body.scrollTop = body.scrollHeight;
                        if (document.getElementById('chat_window').classList.contains('hidden') && lastMessageCount > 0) document.getElementById('chat_badge').classList.remove('hidden');
                    }
                    lastMessageCount = data.messages.length;
                }
            } catch (e) {}
        }

        function renderMessages(messages, myId) {
            const body = document.getElementById('chat_body');
            if (messages.length === 0) { body.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs italic">เริ่มการสนทนากับเราได้ที่นี่...</div>'; return; }
            body.innerHTML = messages.map(msg => {
                const isMe = msg.sender_type === 'user';
                return `<div class="flex ${isMe ? 'justify-end' : 'justify-start'} animate-fade-in"><div class="max-w-[80%] ${isMe ? 'bg-red-700 text-white rounded-2xl rounded-tr-none' : 'bg-white text-slate-700 rounded-2xl rounded-tl-none border border-slate-100'} p-3 shadow-sm relative"><p class="text-sm leading-relaxed">${linkify(escapeHtml(msg.message))}</p><span class="text-[9px] ${isMe ? 'text-red-200' : 'text-slate-400'} block mt-1 text-right">${msg.time}</span></div></div>`;
            }).join('');
        }

        function linkify(text) {
            const urlPattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            return text.replace(urlPattern, '<a href="$1" target="_blank" class="underline hover:opacity-80 transition-opacity">$1</a>');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function startPolling() { fetchMessages(); chatPollInterval = setInterval(fetchMessages, 7000); }
        function stopPolling() { clearInterval(chatPollInterval); }

        document.getElementById('chat_form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('chat_input');
            const msg = input.value.trim();
            if (!msg) return;
            input.value = '';
            const formData = new FormData();
            formData.append('message', msg);
            try {
                const res = await fetch('msg_system.php?action=send', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) fetchMessages();
            } catch (e) {}
        });

        // --- Fix Student Modal ---
        async function showFixStudentModal() {
            Swal.fire({ title: 'กำลังโหลดข้อมูล...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
            try {
                const res = await fetch('process_fix.php?action=get_options');
                const data = await res.json();
                if (!data.success) { Swal.fire('ผิดพลาด', 'โหลดข้อมูลไม่สำเร็จ', 'error'); return; }
                const deptOpts = data.departments.map(d => `<option value="${d}">${d}</option>`).join('');
                Swal.fire({
                    title: '<h3 class="text-xl font-bold">แจ้งเพิ่มรายชื่อใหม่</h3>',
                    html: `<div class="text-left space-y-4 p-2 custom-scrollbar overflow-y-auto max-h-[60vh]">
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase">เลขประจำตัวประชาชน (สำคัญ)</label><input type="text" id="fix_id_card" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-red-600" placeholder="เลขบัตรประชาชน 13 หลัก"></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase">เลขประจำตัวนักศึกษา</label><input type="text" id="fix_student_id" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-red-600" placeholder="รหัสนักศึกษา"></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase">ชื่อ-นามสกุล นักศึกษา</label><input type="text" id="fix_student_name" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-red-600" placeholder="ชื่อ-นามสกุล"></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase">ชื่อ-นามสกุล ผู้ปกครอง</label><input type="text" id="fix_parent_name" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-red-600" placeholder="ชื่อผู้ปกครอง"></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase">แผนกวิชา</label><select id="fix_department" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-red-600"><option value="">-- เลือกแผนก --</option>${deptOpts}</select></div>
                        <div><label class="block text-[10px] font-bold text-slate-400 uppercase">ชื่อกลุ่มเรียน</label><select id="fix_group_name" class="w-full px-4 py-3 border rounded-xl outline-none focus:border-red-600"><option value="">-- กรุณาเลือกแผนกก่อน --</option></select></div>
                    </div>`,
                    showCancelButton: true, confirmButtonText: 'บันทึกข้อมูล', confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-3xl' },
                    didOpen: () => {
                        const dSel = Swal.getPopup().querySelector('#fix_department');
                        const gSel = Swal.getPopup().querySelector('#fix_group_name');
                        dSel.addEventListener('change', async () => {
                            if (!dSel.value) { gSel.innerHTML = '<option value="">-- กรุณาเลือกแผนกก่อน --</option>'; return; }
                            gSel.innerHTML = '<option value="">กำลังโหลด...</option>';
                            try {
                                const gRes = await fetch('process_fix.php?action=get_options&department=' + encodeURIComponent(dSel.value));
                                const gData = await gRes.json();
                                gSel.innerHTML = '<option value="">-- เลือกกลุ่มเรียน --</option>' + gData.groups.map(g => `<option value="${g}">${g}</option>`).join('');
                            } catch (e) {}
                        });
                    },
                    preConfirm: () => {
                        const icard = Swal.getPopup().querySelector('#fix_id_card').value;
                        const sid = Swal.getPopup().querySelector('#fix_student_id').value;
                        const sname = Swal.getPopup().querySelector('#fix_student_name').value;
                        const pname = Swal.getPopup().querySelector('#fix_parent_name').value;
                        const dept = Swal.getPopup().querySelector('#fix_department').value;
                        const grp = Swal.getPopup().querySelector('#fix_group_name').value;
                        if (!icard || !sid || !sname || !pname || !dept || !grp) { Swal.showValidationMessage('กรุณากรอกข้อมูลให้ครบถ้วน'); }
                        return { id_card: icard, student_id: sid, student_name: sname, parent_name: pname, department: dept, group_name: grp };
                    }
                }).then(r => { if (r.isConfirmed) saveFixStudent(r.value); });
            } catch (e) { Swal.fire('ผิดพลาด', 'ติดต่อเซิร์ฟเวอร์ไม่ได้', 'error'); }
        }

        async function saveFixStudent(data) {
            const fd = new FormData(); for (const k in data) fd.append(k, data[k]);
            try {
                const res = await fetch('process_fix.php?action=save', { method: 'POST', body: fd });
                const rd = await res.json();
                Swal.fire({ icon: rd.success ? 'success' : 'error', text: rd.message, confirmButtonColor: rd.success ? '#16a34a' : '#dc2626' });
            } catch (e) { Swal.fire({ icon: 'error', text: 'บันทึกไม่สำเร็จ' }); }
        }

        fetchMessages();
        setInterval(fetchMessages, 15000);
    </script>
</body>
</html>
