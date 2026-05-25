<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบลงทะเบียนประชุมผู้ปกครอง - วิทยาลัยเทคนิคเชียงใหม่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>

<body class="bg-gradient-to-br from-slate-100 via-stone-50 to-red-50 flex items-center justify-center min-h-screen p-4">

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
                    
                    // 🌟 [จุดแก้ไขสำคัญ] ดึงข้อมูลสลับคืนให้ตรงตามช่องเป๊ะๆ ไม่สลับค่ากันอีกต่อไป
                    document.getElementById('modal_student_class').innerText = data.level + ' / ห้อง ' + data.room;
                    document.getElementById('modal_parent_name').value = data.parent_name;
                    
                    modal.classList.replace('hidden', 'flex');
                } else {
                    Swal.fire({
                        icon: 'error', title: 'ไม่พบข้อมูล', text: data.message,
                        confirmButtonText: 'ลองอีกครั้ง', confirmButtonColor: '#dc2626',
                        customClass: { popup: 'rounded-2xl' }
                    });
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
                    Swal.fire({
                        icon: 'warning', title: 'ปฏิเสธการลงทะเบียน', text: data.message,
                        confirmButtonColor: '#ea580c', confirmButtonText: 'ปิดหน้าต่าง',
                        customClass: { popup: 'rounded-2xl' }
                    });
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