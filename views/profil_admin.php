<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$role = strtolower($_SESSION['role'] ?? '');
if (!in_array($role, ['admin', 'pimpinan', 'koordinator', 'engginer'])) {
    header("Location: beranda.php");
    exit();
}

$username = $_SESSION['username'];$pesan_sukses = "";
$pesan_error = "";

// Proses ketika form disubmit untuk memperbarui profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_baru = mysqli_real_escape_string($conn, $_POST['email']);$telepon_baru = mysqli_real_escape_string($conn,$_POST['telepon']);

    $update_query = mysqli_query($conn, "UPDATE users SET email = '$email_baru', telepon = '$telepon_baru' WHERE username = '$username'");
    
    if ($update_query) {$pesan_sukses = "Profil berhasil diperbarui!";
    } else {
        $pesan_error = "Gagal memperbarui profil.";
    }
}

// Ambil data detail pengguna terbaru dari database[cite: 3]
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
$data_user = mysqli_fetch_assoc($query_user);

$nama_lengkap = $data_user['nama_lengkap'] ?? ucwords($username);
$nip =$data_user['nip'] ?? '44821';
$email =$data_user['email'] ?? 'admin.samarinda@lintasarta.co.id';
$telepon =$data_user['telepon'] ?? '+62 811-550-9988';
$jabatan = ucwords($role);

$current_page = 'profil';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - GIS Patroli Fiber Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER / LATAR ATAS LEGA & ELEGAN -->
    <div class="bg-gradient-to-br from-[#0d1c30] via-[#0052cc] to-indigo-800 text-white pb-24 pt-4 px-4 relative">
        <div class="max-w-md mx-auto flex items-center justify-between relative z-10">
            <a href="dashboard_admin.php" class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center text-white active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
            <h1 class="font-extrabold text-base tracking-wide text-white">Profil Administrator</h1>
            <div class="w-10"></div>
        </div>
    </div>

    <!-- KONTEN UTAMA -->
    <main class="max-w-md mx-auto px-4 -mt-12 space-y-4 relative z-20">
        
        <?php if (!empty($pesan_sukses)): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                <?php echo $pesan_sukses; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($pesan_error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2 shadow-xs">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <?php echo $pesan_error; ?>
            </div>
        <?php endif; ?>

        <!-- KARTU PROFIL UTAMA (FLOATING CARD) -->
        <div class="bg-white rounded-3xl p-6 pt-14 shadow-xl shadow-slate-200/60 border border-slate-100 text-center relative space-y-3">
            
            <div class="absolute -top-12 left-1/2 -translate-x-1/2">
                <div class="w-24 h-24 bg-gradient-to-tr from-blue-50 to-blue-100 text-[#0052cc] rounded-full flex items-center justify-center border-4 border-white shadow-md">
                    <span class="material-symbols-outlined text-[48px]" style="font-variation-settings: 'FILL' 1;">manage_accounts</span>
                </div>
            </div>

            <div class="space-y-0.5">
                <h2 class="font-extrabold text-xl text-[#0d1c30] tracking-tight uppercase"><?php echo htmlspecialchars($nama_lengkap); ?></h2>
                <p class="text-xs text-slate-400 font-semibold">NIP: <strong class="text-slate-700"><?php echo htmlspecialchars($nip); ?></strong> • OSP Samarinda</p>
            </div>

            <!-- Badge Role (Hanya Muncul Tulisan ROLE: ADMIN) -->
            <div class="pt-1">
                <span class="inline-block px-3.5 py-1 bg-blue-50 text-[#0052cc] text-xs font-extrabold rounded-full border border-blue-100 shadow-xs">
                    ROLE: <?php echo strtoupper(htmlspecialchars($role)); ?>
                </span>
            </div>
        </div>

        <!-- FORM EDIT EMAIL & TELEPON -->
        <form action="" method="POST" class="bg-white rounded-3xl p-5 shadow-sm border border-slate-100 space-y-4">
            <h3 class="font-extrabold text-xs text-slate-400 uppercase tracking-wider">Pengaturan Informasi Kontak</h3>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="block font-extrabold text-slate-700 mb-1">Username Akun (Tetap)</label>
                    <input type="text" value="<?php echo htmlspecialchars($username); ?>" disabled class="w-full p-3 bg-slate-100 border border-slate-200 rounded-xl text-slate-500 font-bold cursor-not-allowed">
                </div>

                <div>
                    <label class="block font-extrabold text-slate-700 mb-1">Email Kedinasan</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-[#0d1c30] font-semibold focus:outline-none focus:border-[#0052cc] transition">
                </div>

                <div>
                    <label class="block font-extrabold text-slate-700 mb-1">No. Telepon / WhatsApp Hotline</label>
                    <input type="text" name="telepon" value="<?php echo htmlspecialchars($telepon); ?>" required class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-[#0d1c30] font-semibold focus:outline-none focus:border-[#0052cc] transition">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-[#0052cc] hover:bg-blue-700 text-white font-extrabold text-xs rounded-2xl transition shadow-md active:scale-95 flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">save</span> Simpan Perubahan Kontak
            </button>
        </form>

        <!-- TOMBOL LOGOUT -->
        <a href="../process/proses_logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari sesi sistem?')" class="w-full py-3.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 font-extrabold text-xs rounded-2xl transition flex items-center justify-center gap-1.5 shadow-xs">
            <span class="material-symbols-outlined text-[18px]">logout</span>
            Keluar Sistem (Logout)
        </a>

    </main>

    <!-- BOTTOM NAVBAR ADMIN -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200/80 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto shadow-lg">
        <a href="dashboard_admin.php" class="flex flex-col items-center <?php echo ($current_page == 'dashboard') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]">grid_view</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center <?php echo ($current_page == 'peta') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]">map</span>
            <span class="text-[10px] mt-0.5">Peta Jalur</span>
        </a>
        <a href="laporan_admin.php" class="flex flex-col items-center <?php echo ($current_page == 'laporan') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]">assignment</span>
            <span class="text-[10px] mt-0.5">Laporan</span>
        </a>
        <a href="profil_admin.php" class="flex flex-col items-center <?php echo ($current_page == 'profil') ? 'text-[#0052cc] font-bold' : 'text-slate-400 hover:text-slate-600'; ?>">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">person</span>
            <span class="text