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
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f8fafc;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
        }

        .card-shadow {
            box-shadow: 0 4px 15px -2px rgba(0, 0, 0, 0.04), 0 2px 8px -2px rgba(0, 0, 0, 0.02);
        }

        .nav-gradient {
            background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%);
        }

        .animate-fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-hover {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-hover:hover {
            transform: translateY(-1px);
        }

        .btn-hover:active {
            transform: translateY(0);
            scale: 0.98;
        }
    </style>
</head>

<body class="bg-[#f8fafc] min-h-screen pb-8">

    <!-- Navigation -->
    <nav class="nav-gradient text-white shadow-md sticky top-0 z-[50]">
        <div class="max-w-7xl mx-auto px-4 py-2 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="bg-white p-0.5 rounded-xl shadow-inner">
                    <img src="logo.png" alt="Logo" class="h-8 w-auto">
                </div>
                <div class="hidden sm:block">
                    <h1 class="font-extrabold text-base tracking-tight leading-none">ระบบจัดการข้อมูล</h1>
                    <p class="text-[9px] text-red-200 uppercase tracking-widest mt-0.5 font-bold">Chiang Mai Technical
                        College</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div id="refresh_timer"
                    class="hidden md:flex items-center bg-black/10 px-3 py-1.5 rounded-xl text-[9px] font-black gap-2 border border-white/5 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                    รีเฟรช: <span id="timer_sec" class="w-3 inline-block">30</span>วินาที
                </div>
                <button onclick="switchView('stats')" id="nav_stats"
                    class="bg-white/10 hover:bg-white/20 text-[10px] px-4 py-2 rounded-xl transition-all font-black border border-white/10 hidden btn-hover">แดชบอร์ด</button>
                <button onclick="switchView('chats')" id="nav_chats"
                    class="bg-white text-red-900 text-[10px] px-4 py-2 rounded-xl transition-all font-black border border-white shadow-lg flex items-center gap-2 btn-hover">
                    ศูนย์รับเรื่องแชท
                    <span id="total_unread"
                        class="hidden bg-red-600 text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-black animate-bounce shadow-md">0</span>
                </button>
                <button onclick="handleLogout()"
                    class="bg-slate-900/40 hover:bg-slate-900 text-[10px] px-4 py-2 rounded-xl transition-all font-black border border-white/10 uppercase btn-hover">ออกจากระบบ</button>
            </div>
        </div>
    </nav>

    <!-- หน้า Dashboard สถิติ -->
    <div id="stats_view" class="max-w-6xl mx-auto px-4 py-6 space-y-6 animate-fade-in">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-end gap-4 border-b border-slate-200 pb-4">
            <div class="space-y-0.5">
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">สถิติการลงทะเบียน</h2>
                <p class="text-slate-400 font-bold text-xs flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                    ภาพรวมข้อมูลแบบเรียลไทม์ (Real-time Analytics)
                </p>
            </div>
            <div class="bg-white px-4 py-2.5 rounded-2xl card-shadow border border-slate-100 flex items-center gap-4">
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5">ยอดรวมทั้งหมด</p>
                    <h4 class="text-xl font-black text-blue-600 tracking-tighter leading-none"><span
                            id="all_reg_label">0/0</span></h4>
                </div>
                <div
                    class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                            stroke-width="2.5"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Filter Card -->
            <div
                class="bg-white p-6 rounded-[2rem] card-shadow border border-slate-50 space-y-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-slate-50 rounded-full -mr-12 -mt-12"></div>
                <h3 class="font-black text-lg text-slate-800 flex items-center gap-3 relative z-10">
                    <div
                        class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-red-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                                stroke-width="2.5"></path>
                        </svg>
                    </div>
                    ตัวกรองข้อมูล
                </h3>
                <div class="space-y-4 relative z-10">
                    <div class="group">
                        <label
                            class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1 transition-colors group-focus-within:text-red-600">แผนกวิชา
                            (Department)</label>
                        <select id="filter_dept" onchange="loadGroupsByDept()"
                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-700 text-xs font-bold rounded-xl p-3 outline-none focus:ring-4 focus:ring-red-50/50 focus:border-red-600 transition-all cursor-pointer">
                            <option value="">ทุกแผนกวิชา</option>
                        </select>
                    </div>
                    <div class="group">
                        <label
                            class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1.5 px-1 transition-colors group-focus-within:text-red-600">กลุ่มเรียน
                            (Class Group)</label>
                        <select id="filter_group" onchange="updateStats()"
                            class="w-full bg-slate-50 border-2 border-slate-100 text-slate-700 text-xs font-bold rounded-xl p-3 outline-none focus:ring-4 focus:ring-red-50/50 focus:border-red-600 transition-all cursor-pointer">
                            <option value="">ทุกกลุ่มเรียน</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Stats Card -->
            <div
                class="lg:col-span-2 bg-white p-6 rounded-[2rem] card-shadow border border-slate-50 grid grid-cols-1 md:grid-cols-2 gap-8 items-center relative overflow-hidden">
                <div class="relative h-[180px] flex items-center justify-center">
                    <canvas id="regChart"></canvas>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span id="stat_percent"
                            class="text-3xl font-black text-slate-800 leading-none tracking-tighter">0%</span>
                        <span
                            class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1">อัตราสำเร็จ</span>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="bg-emerald-50/50 p-4 rounded-[1.5rem] border border-emerald-100/50 shadow-inner group">
                            <p class="text-[8px] font-black text-emerald-600 uppercase tracking-widest mb-1">
                                ลงทะเบียนแล้ว</p>
                            <h4 class="text-2xl font-black text-emerald-700 tracking-tighter group-hover:scale-105 transition-transform"
                                id="stat_registered">0</h4>
                            <span
                                class="text-[8px] font-black text-emerald-400 uppercase tracking-widest mt-0.5 block">คน
                                (Persons)</span>
                        </div>
                        <div class="bg-slate-50/50 p-4 rounded-[1.5rem] border border-slate-100/50 shadow-inner group">
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                เป้าหมายทั้งหมด</p>
                            <h4 class="text-2xl font-black text-slate-800 tracking-tighter group-hover:scale-105 transition-transform"
                                id="stat_total">0</h4>
                            <span class="text-[8px] font-black text-slate-300 uppercase tracking-widest mt-0.5 block">คน
                                (Total)</span>
                        </div>
                    </div>
                    <div
                        class="bg-slate-900 p-5 rounded-[1.5rem] shadow-xl shadow-slate-900/10 relative overflow-hidden">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white/5 rounded-full -mr-12 -mt-12"></div>
                        <div class="flex justify-between items-end mb-2 relative z-10">
                            <span
                                class="text-[8px] font-black text-slate-400 uppercase tracking-widest">ความคืบหน้าภาพรวม</span>
                            <span class="text-xs font-black text-white" id="all_progress_val">0%</span>
                        </div>
                        <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden relative z-10">
                            <div id="all_progress_bar"
                                class="bg-gradient-to-r from-red-600 to-red-400 h-full rounded-full transition-all duration-1000"
                                style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Search -->
        <div class="bg-white p-6 rounded-[2rem] card-shadow border border-slate-50 space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="space-y-0.5">
                    <h3 class="font-black text-xl text-slate-800 flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"></path>
                            </svg>
                        </div>
                        ค้นหาข้อมูลด่วน
                    </h3>
                    <p class="text-slate-400 text-[10px] font-bold pl-12">ค้นหาด้วยชื่อ-นามสกุล หรือ รหัสนักศึกษา</p>
                </div>
                <div class="relative w-full md:w-[28rem] group">
                    <input type="text" id="searchInput" placeholder="พิมพ์ชื่อ หรือ รหัสประจำตัว..."
                        class="w-full bg-slate-50 border-2 border-slate-100 rounded-xl px-6 py-4 pr-14 outline-none focus:ring-8 focus:ring-slate-50 focus:border-slate-900 transition-all font-black text-slate-700 text-sm placeholder:text-slate-300">
                    <div
                        class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-slate-900 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2.5"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div id="searchResults"
                class="hidden overflow-hidden rounded-2xl border border-slate-100 shadow-inner bg-slate-50/50">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="text-[9px] font-black text-slate-400 uppercase tracking-widest">
                            <tr>
                                <th class="px-6 py-4 border-b border-slate-100">แหล่งข้อมูล</th>
                                <th class="px-6 py-4 border-b border-slate-100">รหัสประจำตัว</th>
                                <th class="px-6 py-4 border-b border-slate-100">ชื่อ-นามสกุล</th>
                                <th class="px-6 py-4 border-b border-slate-100">กลุ่มเรียน</th>
                                <th class="px-6 py-4 border-b border-slate-100 text-right">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="searchResultsBody"
                            class="divide-y divide-slate-100 bg-white font-bold text-slate-700 text-xs">
                            <!-- Results go here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Management Tools -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
                class="bg-white p-6 rounded-[2rem] card-shadow border border-slate-50 space-y-6 relative overflow-hidden">
                <h3 class="font-black text-lg text-slate-800 flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" stroke-width="2.5">
                            </path>
                        </svg>
                    </div>
                    นำเข้าข้อมูล (CSV)
                </h3>
                <form id="importForm" class="flex flex-col gap-4">
                    <div class="relative group">
                        <input type="file" name="excel_file" accept=".csv" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div
                            class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-6 text-center group-hover:border-blue-500 group-hover:bg-blue-50/30 transition-all">
                            <div class="flex flex-col items-center gap-1.5">
                                <svg class="w-6 h-6 text-slate-300 group-hover:text-blue-500 transition-colors"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        stroke-width="2"></path>
                                </svg>
                                <span
                                    class="text-[9px] font-black text-slate-400 group-hover:text-blue-600 uppercase tracking-widest">คลิกเพื่อเลือกไฟล์
                                    .CSV</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-xl transition-all shadow-lg text-[10px] uppercase tracking-widest btn-hover">นำเข้าและประมวลผล</button>
                        <a href="process_admin.php?action=download_template"
                            class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-black px-6 py-4 rounded-xl transition-all text-[10px] uppercase tracking-widest flex items-center justify-center btn-hover">เทมเพลต</a>
                    </div>
                </form>
            </div>

            <div
                class="bg-white p-6 rounded-[2rem] card-shadow border border-slate-50 space-y-6 relative overflow-hidden">
                <h3 class="font-black text-lg text-slate-800 flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                stroke-width="2.5"></path>
                        </svg>
                    </div>
                    ส่งออกรายงาน
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="exportData('pdf')"
                        class="bg-slate-900 hover:bg-black text-white font-black py-5 rounded-xl transition-all shadow-lg text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 btn-hover">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z">
                            </path>
                            <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"></path>
                        </svg>
                        รายงาน PDF
                    </button>
                    <button onclick="exportData('excel')"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-black py-5 rounded-xl transition-all shadow-lg text-[10px] uppercase tracking-widest flex items-center justify-center gap-2 btn-hover">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm2 10a1 1 0 10-2 0v3a1 1 0 102 0v-3zm2-3a1 1 0 011 1v5a1 1 0 11-2 0v-5a1 1 0 011-1zm4-1a1 1 0 10-2 0v7a1 1 0 102 0V8z"
                                clip-rule="evenodd"></path>
                        </svg>
                        ไฟล์ EXCEL
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- หน้า ศูนย์รับเรื่องแชท -->
    <div id="chats_view" class="hidden max-w-7xl mx-auto px-4 py-6 h-[calc(100vh-100px)] min-h-[500px] animate-fade-in">
        <div
            class="bg-white rounded-[2rem] shadow-2xl border border-slate-100 flex h-full overflow-hidden relative glass-effect card-shadow">

            <!-- Sidebar -->
            <div id="chat_sidebar"
                class="w-full sm:w-[300px] border-r border-slate-100 flex flex-col bg-slate-50/30 transition-all duration-300">
                <div class="p-6 border-b border-slate-100 bg-white shrink-0">
                    <h3 class="font-black text-slate-800 text-xl flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-red-700 rounded-xl flex items-center justify-center text-white shadow-lg">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z">
                                </path>
                            </svg>
                        </div>
                        ห้องสนทนา
                    </h3>
                </div>
                <div id="chat_user_list" class="flex-1 overflow-y-auto p-3 space-y-2 custom-scrollbar">
                    <div
                        class="py-16 text-center text-slate-300 font-black italic text-[9px] uppercase tracking-widest">
                        กำลังโหลดรายชื่อ...</div>
                </div>
            </div>

            <!-- Chat Window -->
            <div id="chat_main"
                class="absolute inset-0 z-10 bg-white flex flex-col translate-x-full sm:relative sm:translate-x-0 sm:flex-1 transition-transform duration-500">
                <div id="chat_header"
                    class="p-4 border-b border-slate-100 flex justify-between items-center bg-white shrink-0 shadow-sm relative z-10">
                    <div class="flex items-center gap-4">
                        <button onclick="closeChatMobile()"
                            class="sm:hidden p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition text-slate-500 shadow-inner">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M15 19l-7-7 7-7" stroke-width="3"></path>
                            </svg>
                        </button>
                        <div
                            class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-300 shadow-inner border border-slate-100">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h4 id="active_user_name"
                                class="font-black text-slate-800 text-base truncate tracking-tight">เลือกบทสนทนา</h4>
                            <div class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-slate-200 rounded-full" id="status_dot"></span>
                                <p id="active_user_status"
                                    class="text-[8px] text-slate-400 uppercase tracking-widest font-black">แสตนด์บาย</p>
                            </div>
                        </div>
                    </div>
                    <button onclick="loadChatUsers()"
                        class="p-3 bg-slate-50 hover:bg-slate-100 rounded-xl transition text-slate-400 group">
                        <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                stroke-width="2.5"></path>
                        </svg>
                    </button>
                </div>

                <div id="admin_chat_body" class="flex-1 overflow-y-auto p-6 space-y-6 bg-slate-50/50 custom-scrollbar">
                    <div class="h-full flex flex-col items-center justify-center text-slate-200 space-y-6">
                        <div
                            class="w-24 h-24 bg-white rounded-[2rem] shadow-2xl border border-slate-50 flex items-center justify-center">
                            <svg class="w-12 h-12 text-slate-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                                    stroke-width="1.5"></path>
                            </svg>
                        </div>
                        <p class="font-bold italic text-slate-300 text-xs">คลิกเลือกห้องแชทเพื่อเริ่มสนทนา</p>
                    </div>
                </div>

                <form id="admin_chat_form"
                    class="p-4 border-t border-slate-100 bg-white hidden flex gap-3 shrink-0 items-center">
                    <input type="text" id="admin_chat_input" placeholder="พิมพ์ข้อความตอบกลับ..." autocomplete="off"
                        class="flex-1 bg-slate-50 border-2 border-slate-100 rounded-xl px-5 py-3 text-sm font-bold outline-none focus:ring-4 focus:ring-red-50/50 focus:border-red-700 transition-all">
                    <button type="submit"
                        class="bg-red-700 hover:bg-red-800 text-white w-12 h-12 rounded-xl transition shadow-lg flex items-center justify-center shrink-0 group btn-hover">
                        <svg class="w-5 h-5 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"
                            fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z">
                            </path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden Export Form -->
    <form id="hiddenExportForm" method="POST" target="_blank" class="hidden">
        <input type="hidden" name="department" id="exp_dept">
        <input type="hidden" name="group_name" id="exp_group">
        <input type="hidden" name="chart_image" id="exp_chart">
        <input type="hidden" name="stats" id="exp_stats">
    </form>

    <script>
        let regChart = null;
        let timer = 30;
        let searchTimeout = null;
        let adminChatPollInterval = null;
        let activeChatUser = null;
        let lastAdminMsgCount = 0;
        let currentStats = { registered: 0, total: 0, percent: '0%' };

        // ฟังก์ชันช่วยตรวจสอบและจัดการปัญหา Cookie/Anti-Bot ของ Hosting
        async function handleResponse(response) {
            const text = await response.text();
            if (text.includes('aes.js') || text.includes('__test=') || text.includes('Cookies are not enabled')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'การเชื่อมต่อไม่เสถียร',
                    text: 'ระบบความปลอดภัยของ Hosting บล็อกการเชื่อมต่อชั่วคราว กรุณารีเฟรชหน้าเว็บเพื่อใช้งานต่อ',
                    confirmButtonText: 'รีเฟรชหน้าเว็บ',
                    confirmButtonColor: '#0f172a',
                    allowOutsideClick: false
                }).then(() => { window.location.reload(); });
                return null;
            }
            try {
                return JSON.parse(text);
            } catch (e) {
                // กรณีที่เป็น Redirect ไปหน้า Login
                if (text.includes('login') || text.includes('Direct access')) {
                    window.location.href = 'index.php?login=1';
                }
                return null;
            }
        }

        async function initDashboard() {
            try {
                const res = await fetch('process_admin.php?action=get_filters');
                const data = await handleResponse(res);
                if (data && data.success) {
                    const dSel = document.getElementById('filter_dept');
                    data.departments.forEach(d => { const opt = document.createElement('option'); opt.value = opt.textContent = d; dSel.appendChild(opt); });
                    updateGroupDropdown(data.groups);
                }
            } catch (e) { console.error(e); }
            updateStats();
        }

        async function loadGroupsByDept() {
            const dept = document.getElementById('filter_dept').value;
            try {
                const res = await fetch(`process_admin.php?action=get_filters&department=${encodeURIComponent(dept)}`);
                const data = await handleResponse(res);
                if (data && data.success) updateGroupDropdown(data.groups);
            } catch (e) { console.error(e); }
            updateStats();
        }

        function updateGroupDropdown(groups) {
            const gSel = document.getElementById('filter_group');
            gSel.innerHTML = '<option value="">ทุกกลุ่มเรียน</option>';
            groups.forEach(g => { const opt = document.createElement('option'); opt.value = opt.textContent = g; gSel.appendChild(opt); });
        }

        async function updateStats(isAuto = false) {
            const dept = document.getElementById('filter_dept').value;
            const grp = document.getElementById('filter_group').value;
            try {
                const res = await fetch(`process_admin.php?action=get_stats&department=${encodeURIComponent(dept)}&group_name=${encodeURIComponent(grp)}`);
                const data = await handleResponse(res);
                if (data && data.success) {
                    currentStats = { registered: data.registered, total: data.total, percent: data.percent };
                    document.getElementById('stat_total').innerText = data.total;
                    document.getElementById('stat_registered').innerText = data.registered;
                    document.getElementById('stat_percent').innerText = data.percent;
                    document.getElementById('all_reg_label').innerText = `${data.all_reg} / ${data.all_total}`;
                    const p = data.all_total > 0 ? (data.all_reg / data.all_total) * 100 : 0;
                    document.getElementById('all_progress_bar').style.width = p + '%';
                    document.getElementById('all_progress_val').innerText = Math.round(p) + '%';
                    renderChart(data.registered, data.total - data.registered);
                    if (isAuto) timer = 30;
                }
            } catch (e) { }
        }

        setInterval(() => {
            timer--;
            if (timer <= 0) { updateStats(true); timer = 30; }
            const t = document.getElementById('timer_sec'); if (t) t.innerText = timer;
        }, 1000);

        document.getElementById('searchInput').addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            const q = e.target.value.trim();
            if (q.length < 2) { document.getElementById('searchResults').classList.add('hidden'); return; }
            searchTimeout = setTimeout(async () => {
                try {
                    const res = await fetch(`process_admin.php?action=search_students&query=${encodeURIComponent(q)}`);
                    const data = await handleResponse(res);
                    const tbody = document.getElementById('searchResultsBody');
                    tbody.innerHTML = '';
                    if (data && data.success && data.list.length > 0) {
                        data.list.forEach(item => {
                            const badge = '<span class="bg-slate-100 text-slate-500 px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-tighter border border-slate-200">Main</span>';
                            tbody.insertAdjacentHTML('beforeend', `<tr class="hover:bg-slate-50/80 transition-colors group"><td class="px-6 py-4">${badge}</td><td class="px-6 py-4 font-mono text-xs text-slate-400 group-hover:text-slate-900 transition-colors">${escapeHtml(item.student_id)}</td><td class="px-6 py-4 font-black text-slate-800">${escapeHtml(item.student_name)}</td><td class="px-6 py-4 text-slate-400 font-bold text-[10px] group-hover:text-slate-600 transition-colors">${escapeHtml(item.group_name)}</td><td class="px-6 py-4 text-right"><span class="${item.status === 'ลงทะเบียนแล้ว' ? 'text-emerald-600' : 'text-slate-200'} font-black text-[10px] uppercase">${escapeHtml(item.status)}</span></td></tr>`);
                        });
                        document.getElementById('searchResults').classList.remove('hidden');
                    } else if (data) { tbody.innerHTML = '<tr><td colspan="5" class="p-8 text-center text-slate-300 italic font-black uppercase tracking-widest text-[9px]">ไม่พบข้อมูลที่ค้นหา</td></tr>'; document.getElementById('searchResults').classList.remove('hidden'); }
                } catch (e) { }
            }, 300);
        });

        function renderChart(reg, nreg) {
            const ctx = document.getElementById('regChart').getContext('2d');
            const colors = ['#dc2626', '#f1f5f9'];
            if (regChart) { regChart.data.datasets[0].data = [reg, nreg]; regChart.update(); return; }
            regChart = new Chart(ctx, { type: 'doughnut', data: { labels: ['ลงทะเบียนแล้ว', 'รอการลงทะเบียน'], datasets: [{ data: [reg, nreg], backgroundColor: colors, borderWidth: 0, hoverOffset: 4, weight: 2 }] }, options: { cutout: '80%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: true, backgroundColor: '#0f172a', titleFont: { family: 'Sarabun', weight: 'bold', size: 10 }, bodyFont: { family: 'Sarabun', size: 10 }, padding: 10, cornerRadius: 10 } }, animation: { duration: 1500, easing: 'easeOutQuart' } } });
        }

        function switchView(view) {
            const stats = document.getElementById('stats_view');
            const chats = document.getElementById('chats_view');
            stats.classList.add('hidden');
            chats.classList.add('hidden');
            document.getElementById('nav_stats').classList.remove('hidden');
            document.getElementById('nav_chats').classList.remove('hidden');
            document.getElementById('refresh_timer').classList.add('hidden');

            if (view === 'stats') {
                stats.classList.remove('hidden');
                document.getElementById('nav_stats').classList.add('hidden');
                document.getElementById('refresh_timer').classList.remove('hidden');
                stopAdminChatPolling(); closeChatMobile();
            } else if (view === 'chats') {
                chats.classList.remove('hidden');
                document.getElementById('nav_chats').classList.add('hidden');
                startAdminChatPolling();
            }
        }

        function closeChatMobile() {
            const m = document.getElementById('chat_main'); if (m) m.classList.add('translate-x-full');
            const dot = document.getElementById('status_dot');
            if (dot) dot.className = 'w-1.5 h-1.5 bg-slate-200 rounded-full';
            activeChatUser = null;
        }

        async function loadChatUsers() {
            try {
                const res = await fetch('msg_system.php?action=get_chat_users', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await handleResponse(res);
                if (data && data.success) {
                    const listDiv = document.getElementById('chat_user_list');
                    if (data.users.length === 0) { listDiv.innerHTML = '<div class="py-16 text-center text-slate-300 font-black italic text-[9px] uppercase tracking-widest">ไม่มีข้อความใหม่</div>'; return; }
                    listDiv.innerHTML = data.users.map(u => {
                        const active = activeChatUser === u.sender_id;
                        return `<button onclick="selectChatUser('${u.sender_id}')" class="w-full text-left p-4 rounded-2xl transition-all duration-300 ${active ? 'bg-red-700 text-white shadow-xl shadow-red-600/20 scale-[0.98]' : 'bg-white hover:bg-slate-50 border border-slate-100'} flex items-center gap-3 group">
                            <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center ${active ? 'bg-white/20' : 'bg-red-50 text-red-600'} font-black text-[10px] uppercase shadow-inner border border-black/5">${u.sender_id.substring(5, 7)}</div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center mb-0.5"><span class="font-black text-xs truncate tracking-tight">${u.sender_id}</span><span class="text-[7px] font-black ${active ? 'text-red-100' : 'text-slate-300'} uppercase tracking-tighter">${u.time_fmt}</span></div>
                                <p class="text-[10px] truncate ${active ? 'text-red-50' : 'text-slate-400'} font-bold">${u.last_message || '...'}</p>
                            </div>
                            ${u.unread_count > 0 && !active ? `<span class="bg-red-500 text-white text-[7px] font-black w-5 h-5 rounded-full flex items-center justify-center shadow-lg border-2 border-white animate-pulse">${u.unread_count}</span>` : ''}
                        </button>`;
                    }).join('');
                    let total = data.users.reduce((acc, u) => acc + parseInt(u.unread_count), 0);
                    const badge = document.getElementById('total_unread');
                    if (total > 0) { badge.innerText = total; badge.classList.remove('hidden'); } else { badge.classList.add('hidden'); }
                }
            } catch (e) { }
        }

        function selectChatUser(uid) {
            activeChatUser = uid;
            document.getElementById('active_user_name').innerText = uid;
            document.getElementById('active_user_status').innerText = 'ออนไลน์ - กำลังตอบกลับ';
            const dot = document.getElementById('status_dot');
            if (dot) dot.className = 'w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse shadow-[0_0_8px_rgba(34,197,94,0.5)]';
            document.getElementById('admin_chat_form').classList.remove('hidden');
            document.getElementById('chat_main').classList.remove('translate-x-full');
            lastAdminMsgCount = 0; fetchAdminMessages(); loadChatUsers();
        }

        async function fetchAdminMessages() {
            if (!activeChatUser) return;
            try {
                const res = await fetch(`msg_system.php?action=get_messages&target_user=${activeChatUser}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const data = await handleResponse(res);
                if (data && data.success) {
                    const body = document.getElementById('admin_chat_body');
                    body.innerHTML = data.messages.map(m => {
                        const isAdmin = m.sender_type === 'admin';
                        return `<div class="flex ${isAdmin ? 'justify-end' : 'justify-start'} animate-fade-in">
                            <div class="max-w-[75%] ${isAdmin ? 'bg-red-700 text-white rounded-2xl rounded-tr-none shadow-xl shadow-red-700/10' : 'bg-white text-slate-700 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm'} p-4">
                                <p class="text-xs font-bold leading-relaxed">${linkify(escapeHtml(m.message))}</p>
                                <span class="text-[7px] font-black block mt-2.5 text-right ${isAdmin ? 'text-red-200' : 'text-slate-300'} uppercase tracking-widest">${m.time}</span>
                            </div>
                        </div>`;
                    }).join('');
                    if (data.messages.length > lastAdminMsgCount) body.scrollTop = body.scrollHeight;
                    lastAdminMsgCount = data.messages.length;
                }
            } catch (e) { }
        }

        function linkify(text) {
            const urlPattern = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
            return text.replace(urlPattern, '<a href="$1" target="_blank" class="underline hover:opacity-80 transition-opacity">$1</a>');
        }

        document.getElementById('admin_chat_form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('admin_chat_input');
            const msg = input.value.trim();
            if (!msg || !activeChatUser) return;
            input.value = '';
            const fd = new FormData(); fd.append('message', msg); fd.append('target_user', activeChatUser);
            try {
                const res = await fetch('msg_system.php?action=send', { method: 'POST', body: fd });
                const data = await handleResponse(res);
                if (data && data.success) fetchAdminMessages();
            } catch (e) { }
        });

        function escapeHtml(t) { const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
        function startAdminChatPolling() { loadChatUsers(); fetchAdminMessages(); adminChatPollInterval = setInterval(() => { loadChatUsers(); fetchAdminMessages(); }, 7000); }
        function stopAdminChatPolling() { clearInterval(adminChatPollInterval); }

        async function handleLogout() {
            const result = await Swal.fire({
                title: 'ยืนยันการออกจากระบบ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0f172a',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'ตกลง',
                cancelButtonText: 'ยกเลิก'
            });

            if (result.isConfirmed) {
                try {
                    const res = await fetch('process_admin.php?action=logout');
                    const data = await handleResponse(res);
                    if (data && data.success) {
                        window.location.href = 'index.php?login=1';
                    }
                } catch (e) {
                    window.location.href = 'index.php?login=1';
                }
            }
        }

        function exportData(type) {
            const form = document.getElementById('hiddenExportForm');
            if (!form) return;
            form.action = type === 'pdf' ? 'export_report.php' : 'export_report.php?format=excel';
            document.getElementById('exp_dept').value = document.getElementById('filter_dept').value;
            document.getElementById('exp_group').value = document.getElementById('filter_group').value;
            document.getElementById('exp_stats').value = JSON.stringify(currentStats);
            if (type === 'pdf') {
                const canvas = document.getElementById('regChart');
                if (canvas) {
                    try { document.getElementById('exp_chart').value = canvas.toDataURL('image/png', 0.8); } catch (e) { document.getElementById('exp_chart').value = ""; }
                }
            } else { document.getElementById('exp_chart').value = ""; }
            form.submit();
        }

        document.getElementById('importForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            Swal.fire({ title: 'กำลังนำเข้าข้อมูล...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            const fd = new FormData(e.target);
            try {
                const res = await fetch('process_admin.php?action=import_data', { method: 'POST', body: fd });
                const data = await handleResponse(res);
                if (data) {
                    Swal.fire({ icon: data.success ? 'success' : 'error', title: data.success ? 'สำเร็จ' : 'ผิดพลาด', text: data.message }).then(() => { if (data.success) window.location.reload(); });
                }
            } catch (e) { Swal.fire('ผิดพลาด', 'ติดต่อเซิร์ฟเวอร์ไม่ได้', 'error'); }
        });

        initDashboard();
        setInterval(loadChatUsers, 15000);
    </script>
</body>

</html>