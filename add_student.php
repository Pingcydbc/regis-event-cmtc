<?php
require_once 'config.php';
// ไม่มีการตรวจสอบสิทธิ์ เพื่อให้ user เข้าถึงได้ทันทีจากลิงก์ที่ Admin ส่งให้
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งเพิ่มรายชื่อนักเรียน - วิทยาลัยเทคนิคเชียงใหม่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .card-shadow { box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08), 0 4px 15px -8px rgba(0, 0, 0, 0.04); }
        .animate-fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-4">

    <div class="bg-white p-8 rounded-[2rem] card-shadow w-full max-w-md border-t-[8px] border-red-700 animate-fade-in">
        <div class="flex flex-col items-center mb-6">
            <img src="logo.png" alt="Logo" class="h-20 mb-4 drop-shadow-md">
            <h2 class="text-xl font-black text-slate-800">แจ้งเพิ่มรายชื่อใหม่</h2>
            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1">กรณีไม่พบข้อมูลในระบบลงทะเบียน</p>
        </div>

        <form id="fixStudentForm" class="space-y-4">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">เลขประจำตัวประชาชน (13 หลัก)</label>
                    <input type="text" name="id_card" required maxlength="13" oninput="this.value = this.value.replace(/[^0-9]/g, '');" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-red-600 outline-none transition-all font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">เลขประจำตัวนักศึกษา</label>
                    <input type="text" name="student_id" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-red-600 outline-none transition-all font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ชื่อ-นามสกุล นักศึกษา</label>
                    <input type="text" name="student_name" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-red-600 outline-none transition-all font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">ชื่อ-นามสกุล ผู้ปกครอง</label>
                    <input type="text" name="parent_name" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-red-600 outline-none transition-all font-bold">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">แผนกวิชา</label>
                    <select id="department" name="department" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-red-600 outline-none transition-all font-bold">
                        <option value="">-- เลือกแผนก --</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">กลุ่มเรียน</label>
                    <select id="group_name" name="group_name" required class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:border-red-600 outline-none transition-all font-bold">
                        <option value="">-- เลือกแผนกก่อน --</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full bg-red-700 hover:bg-red-800 text-white font-black py-4 rounded-2xl transition-all shadow-lg mt-4 uppercase tracking-widest text-xs">
                บันทึกข้อมูลและแจ้งเจ้าหน้าที่
            </button>
            <a href="index.php" class="block text-center text-[10px] text-slate-400 font-bold uppercase hover:text-red-700 transition-colors">กลับหน้าหลัก</a>
        </form>
    </div>

    <script>
        // โหลดข้อมูลแผนก
        async function loadOptions() {
            try {
                const res = await fetch('process_fix.php?action=get_options');
                const text = await res.text();
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.error("Server Error:", text);
                    Swal.fire('ผิดพลาด', 'ไม่สามารถอ่านข้อมูลจากเซิร์ฟเวอร์ได้', 'error');
                    return;
                }

                if (data.success) {
                    const dSel = document.getElementById('department');
                    
                    if (data.departments.length === 0) {
                        dSel.innerHTML = '<option value="">-- ไม่พบข้อมูลแผนกในระบบ --</option>';
                        Swal.fire({
                            icon: 'info',
                            title: 'ไม่พบข้อมูลตั้งต้น',
                            text: 'ระบบยังไม่มีรายชื่อแผนกในฐานข้อมูล กรุณาแจ้งแอดมินให้นำเข้าข้อมูลก่อน',
                            confirmButtonColor: '#64748b'
                        });
                        return;
                    }

                    dSel.innerHTML = '<option value="">-- เลือกแผนก --</option>' + 
                        data.departments.map(d => `<option value="${d}">${d}</option>`).join('');
                    
                    dSel.addEventListener('change', async () => {
                        const gSel = document.getElementById('group_name');
                        if (!dSel.value) { gSel.innerHTML = '<option value="">-- เลือกแผนกก่อน --</option>'; return; }
                        gSel.innerHTML = '<option value="">กำลังโหลด...</option>';
                        try {
                            const gRes = await fetch('process_fix.php?action=get_options&department=' + encodeURIComponent(dSel.value));
                            const gData = await gRes.json();
                            gSel.innerHTML = '<option value="">-- เลือกกลุ่มเรียน --</option>' + 
                                gData.groups.map(g => `<option value="${g}">${g}</option>`).join('');
                        } catch (e) {
                            console.error("Load Groups Error:", e);
                        }
                    });
                }
            } catch (e) { 
                console.error("Load Options Error:", e);
                Swal.fire('ผิดพลาด', 'การเชื่อมต่อเซิร์ฟเวอร์ล้มเหลว', 'error');
            }
        }

        document.getElementById('fixStudentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const btn = e.target.querySelector('button');
            btn.disabled = true;
            btn.innerText = 'กำลังบันทึก...';

            try {
                const res = await fetch('process_fix.php?action=save', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'ส่งเรื่องสำเร็จ', text: 'ข้อมูลของคุณถูกส่งให้เจ้าหน้าที่ตรวจสอบแล้ว', confirmButtonColor: '#16a34a' })
                    .then(() => { window.location.href = 'index.php'; });
                } else {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: data.message, confirmButtonColor: '#dc2626' });
                    btn.disabled = false;
                    btn.innerText = 'บันทึกข้อมูลและแจ้งเจ้าหน้าที่';
                }
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'ไม่สามารถติดต่อเซิร์ฟเวอร์ได้' });
                btn.disabled = false;
                btn.innerText = 'บันทึกข้อมูลและแจ้งเจ้าหน้าที่';
            }
        });

        loadOptions();
    </script>
</body>
</html>
