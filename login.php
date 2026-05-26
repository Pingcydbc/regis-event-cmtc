<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hardcoded credentials for simplicity
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['loggedin'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ผู้ดูแลระบบ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border-t-8 border-red-600">
        <div class="flex flex-col items-center mb-6">
            <img src="logo.png" alt="Logo" class="h-20 mb-4">
            <h2 class="text-2xl font-bold text-slate-800">เข้าสู่ระบบจัดการ</h2>
            <p class="text-sm text-slate-500">วิทยาลัยเทคนิคเชียงใหม่</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm mb-4 text-center border border-red-100">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">ชื่อผู้ใช้</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-red-100 focus:border-red-600 outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-1">รหัสผ่าน</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-xl focus:ring-2 focus:ring-red-100 focus:border-red-600 outline-none transition">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-xl transition shadow-lg shadow-red-100 mt-4 text-center">
                เข้าสู่ระบบจัดการ
            </button>
        </form>
        
        <div class="mt-6 flex flex-col gap-3">
            <a href="index.php" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-3 rounded-xl transition text-center text-sm">
                กลับสู่หน้าลงทะเบียน
            </a>
        </div>
    </div>
</body>
</html>