<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);
$current_page = 'profil';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Akun - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER / LATAR ATAS DENGAN WARNA BIRU GRADASI -->
    <div class="bg-gradient-to-br from-[#0052cc] via-blue-600 to-indigo-800 text-white pb-20 pt-4 px-4 relative">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <a href="beranda.php" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
        </div>
    </div>

    <!-- KONTEN UTAMA (DENGAN EFEK FLOATING CARD) -->
    <main class="max-w-md mx-auto px-4 -mt-12 space-y-4">
        
        <!-- KARTU PROFIL UTAMA (MELAYANG / FLOATING) -->
        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/60 border border-slate-100 text-center relative space-y-3">
            
            <!-- Avatar Bundar Besar Menjorok ke Atas -->
            <div class="absolute -top-12 left-1/2 -translate-x-1/2">
                <div class="w-24 h-24 bg-gradient-to-tr from-blue-50 to-blue-100 text-[#0052cc] rounded-full flex items-center justify-center border-4 border-white shadow-md">
                    <span class="material-symbols-outlined text-[48px]">engineering</span>
                </div>
            </div>

            <!-- Jarak kosong untuk mengakomodasi avatar yang menjorok keluar -->
            <div class="pt-10">
                <h2 class="font-extrabold text-xl text-[#0d1c30] tracking-tight"><?php echo htmlspecialchars(ucfirst($user['username'] ?? 'Teknisi')); ?></h2>
                <p class="text-xs text-slate-400 font-semibold mt-0.5">NIP: <strong class="text-slate-700"><?php echo htmlspecialchars($user['nip'] ?? '101'); ?></strong> • OSP Samarinda</p>
            </div>

            <!-- Badge Role -->
            <div class="pt-1">
                <span class="inline-block px-3 py-1 bg-blue-50 text-[#0052cc] text-xs font-extrabold rounded-full border border-blue-100">
                    ROLE: <?php echo strtoupper(htmlspecialchars($user['role'] ?? 'Teknisi')); ?>
                </span>
            </div>
        </div>

        <!-- MENU / DAFTAR INFORMASI (GAYA LIST SETTINGS CLEAN) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden divide-y divide-slate-100">
            
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0052cc] flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-[20px]">badge</span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-[#0d1c30]">Username Akun</p>
                        <p class="text-[11px] text-slate-400"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
            </div>

            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0052cc] flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-[20px]">pin</span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-[#0d1c30]">Nomor Induk Pegawai (NIP)</p>
                        <p class="text-[11px] text-slate-400"><?php echo htmlspecialchars($user['nip'] ?? '101'); ?></p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
            </div>

            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#0052cc] flex items-center justify-center border border-blue-100">
                        <span class="material-symbols-outlined text-[20px]">domain</span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-[#0d1c30]">Wilayah Operasional</p>
                        <p class="text-[11px] text-slate-400">Samarinda OSP Koridor</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
            </div>

        </div>

        <!-- TOMBOL LOGOUT -->
        <a href="../process/proses_logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari aplikasi?')" class="w-full py-3.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-extrabold text-xs rounded-2xl transition flex items-center justify-center gap-1.5 shadow-xs">
            <span class="material-symbols-outlined text-[18px]">logout</span>
            Keluar dari Sistem (Logout)
        </a>

    </main>

    <!-- BOTTOM NAVBAR -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="beranda.php" class="flex flex-col items-center <?php echo ($current_page == 'beranda') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'beranda') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>grid_view</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="form_patroli.php" class="flex flex-col items-center <?php echo ($current_page == 'patroli') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'patroli') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>assignment</span>
            <span class="text-[10px] mt-0.5">Patroli</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center <?php echo ($current_page == 'riwayat') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'riwayat') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>history</span>
            <span class="text-[10px] mt-0.5">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center <?php echo ($current_page == 'profil') ? 'text-[#0052cc] font-bold' : 'text-slate-400'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'profil') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>person</span>
            <span class="text-[10px] mt-0.5">Profil</span>
        </a>
    </nav>

</body>
</html>