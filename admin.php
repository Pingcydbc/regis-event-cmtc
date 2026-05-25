<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard ผู้ดูแลระบบ - วิทยาลัยเทคนิคเชียงใหม่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            <a href="index.php" class="bg-red-800 hover:bg-red-900 text-xs px-3 py-2 rounded-xl transition font-medium border border-red-600">ไปหน้าแรกเว็บ</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-10 space-y-8">
        
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
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: data.message, confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' } });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'เชื่อมต่อล้มเหลว', text: 'ไม่สามารถติดต่อไฟล์หลังบ้านได้', confirmButtonColor: '#dc2626', customClass: { popup: 'rounded-2xl' } });
            }
        });
    </script>
</body>
</html>