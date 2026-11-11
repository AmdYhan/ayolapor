<?php
session_start();
// Jika sudah login, langsung arahkan sesuai rolenya
if (isset($_SESSION['username'])) {
    $role = strtolower($_SESSION['role'] ?? '');
    if ($role === 'admin' || $role === 'koordinator' || $role === 'engginer' || $role === 'pimpinan') {
        header("Location: dashboard_admin.php");
        exit();
    } else {
        header("Location: beranda.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    </style>
</head>
<body class="text-[#0d1c30] min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-6">
        
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <img src="../assets/img/Lintasarta_logo.png" alt="Logo Lintasarta" class="h-12 mx-auto object-contain">
            <h1 class="font-extrabold text-lg text-[#0d1c30]">SAMARINDA OPS</h1>
            <p class="text-xs text-slate-400">Sistem Patroli & Monitoring OSP</p>
        </div>

        <!-- FORM LOGIN -->
        <form action="../process/proses_login.php" method="POST" class="space-y-4">
            
            <!-- INPUT TERSEMBUNYI UNTUK MENYIMPAN ROLE YANG DIPILIH -->
            <input type="hidden" name="selected_role" id="selectedRole" value="teknisi">

            <!-- TAB SWITCHER DENGAN ANIMASI BERGESER -->
            <div class="relative flex bg-slate-100 p-1 rounded-2xl">
                <!-- Latar Belakang Geser (Sliding Pill) -->
                <div id="sliderPill" class="absolute top-1 bottom-1 w-[calc(50%-4px)] bg-[#0052cc] rounded-xl shadow-md transition-all duration-300 ease-in-out left-1"></div>

                <!-- Tombol Teknisi -->
                <button type="button" onclick="setRole('teknisi')" id="btnTeknisi" class="relative z-10 flex-1 flex items-center justify-center gap-2 py-2.5 text-xs font-bold transition-colors duration-200 text-white">
                    <span class="material-symbols-outlined text-[18px]">engineering</span>
                    Teknisi Lapangan
                </button>

                <!-- Tombol Admin -->
                <button type="button" onclick="setRole('admin')" id="btnAdmin" class="relative z-10 flex-1 flex items-center justify-center gap-2 py-2.5 text-xs font-bold transition-colors duration-200 text-slate-500">
                    <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                    Admin / Pimpinan
                </button>
            </div>

            <!-- Input Username -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">NIP / Username <span class="text-rose-500">*</span></label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-400 material-symbols-outlined text-[20px]">badge</span>
                    <input type="text" name="username" required placeholder="Masukkan username Anda" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 pl-11 pr-4 text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                </div>
            </div>

            <!-- Input Password -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 block">Password <span class="text-rose-500">*</span></label>
                <div class="relative flex items-center">
                    <span class="absolute left-3.5 text-slate-400 material-symbols-outlined text-[20px]">lock</span>
                    <input type="password" name="password" id="passwordInput" required placeholder="••••••••" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3 pl-11 pr-11 text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] focus:bg-white transition">
                    <button type="button" onclick="togglePassword()" class="absolute right-3.5 text-slate-400 hover:text-slate-600 focus:outline-none">
                        <span class="material-symbols-outlined text-[20px]" id="eyeIcon">visibility</span>
                    </button>
                </div>
            </div>

            <!-- Opsi Tambahan -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#0052cc] focus:ring-blue-500">
                    <span class="text-slate-500 font-medium">Ingat sesi ini</span>
                </label>
                <a href="#" class="text-[#0052cc] font-bold hover:underline">Bantuan NOC</a>
            </div>

            <!-- Tombol Submit -->
            <button type="submit" class="w-full bg-[#0052cc] hover:bg-blue-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition flex items-center justify-center gap-2 text-xs">
                <span>Masuk ke Sistem</span>
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </button>

        </form>
    </div>

    <!-- SCRIPT ANIMASI BERGESER & VISIBILITY PASSWORD -->
    <script>
        function setRole(role) {
            const slider = document.getElementById('sliderPill');
            const btnTeknisi = document.getElementById('btnTeknisi');
            const btnAdmin = document.getElementById('btnAdmin');
            const selectedRoleInput = document.getElementById('selectedRole');

            selectedRoleInput.value = role;

            if (role === 'admin') {
                // Geser pil ke kanan
                slider.style.transform = 'translateX(100%)';
                btnAdmin.classList.remove('text-slate-500');
                btnAdmin.classList.add('text-white');
                btnTeknisi.classList.remove('text-white');
                btnTeknisi.classList.add('text-slate-500');
            } else {
                // Kembalikan pil ke kiri
                slider.style.transform = 'translateX(0)';
                btnTeknisi.classList.remove('text-slate-500');
                btnTeknisi.classList.add('text-white');
                btnAdmin.classList.remove('text-white');
                btnAdmin.classList.add('text-slate-500');
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                eyeIcon.textContent = 'visibility';
            }
        }
    </script>
</body>
</html>