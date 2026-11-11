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

$username = $_SESSION['username'];

$q_kritis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Kritis%' OR kondisi_fisik LIKE '%Rusak%'"))['total'] ?? 0;
$q_waspada = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Pohon%' OR potensi_ancaman LIKE '%Ancaman%'"))['total'] ?? 0;
$q_normal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Disetujui' OR kondisi_fisik = 'Baik'"))['total'] ?? 0;
$total_laporan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli"))['total'] ?? 0;

$query_aktivitas = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username ORDER BY l.tanggal DESC LIMIT 3");

$current_page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Admin - GIS Patroli Fiber Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER TOP -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div>
                <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                <p class="text-[10px] text-slate-400 font-medium">Dashboard Admin</p>
            </div>
            <a href="profil_admin.php" class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                <span class="material-symbols-outlined text-[20px]">person</span>
            </a>
        </div>
    </header>

    <main class="max-w-md mx-auto p-4 space-y-4">
        
        <div class="bg-gradient-to-r from-[#0b192c] to-[#0052cc] text-white p-4 rounded-3xl shadow-md space-y-3">
            <div class="flex justify-between items-center">
                <span class="bg-white/20 backdrop-blur-md text-[10px] font-bold px-2.5 py-0.5 rounded-full">Sistem Aktif</span>
                <span class="text-[10px] text-blue-200 font-medium"><?php echo date('d M Y'); ?></span>
            </div>
            <div>
                <h2 class="font-extrabold text-base">Halo, <?php echo htmlspecialchars($username); ?>! 👋</h2>
                <p class="text-[11px] text-blue-100 mt-0.5">Pusat Kendali OSP Koridor Mahakam siap dipantau hari ini.</p>
            </div>
            <div class="pt-1 flex gap-2">
                <a href="cetak_pdf.php" target="_blank" class="bg-white text-[#0052cc] hover:bg-blue-50 font-bold text-xs px-3.5 py-2 rounded-xl flex items-center gap-1.5 shadow-sm transition">
                    <span class="material-symbols-outlined text-[16px]">print</span> Cetak PDF Laporan
                </a>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-2">
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center">
                <div class="text-rose-600 font-extrabold text-base mb-0.5">0<?php echo $q_kritis; ?></div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Kritis Rawan</p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center">
                <div class="text-amber-600 font-extrabold text-base mb-0.5">0<?php echo $q_waspada; ?></div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Waspada Rimbun</p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm text-center">
                <div class="text-emerald-600 font-extrabold text-base mb-0.5">0<?php echo $q_normal; ?></div>
                <p class="text-[10px] text-slate-400 font-bold uppercase">Normal Selesai</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="pemantauan_admin.php" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2 hover:border-blue-200 transition">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-[#0052cc] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">map</span>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-[#0d1c30]">Peta GIS Jalur</h4>
                    <p class="text-[10px] text-slate-400">Pantau koordinat lapangan</p>
                </div>
            </a>
            <a href="laporan_admin.php" class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col gap-2 hover:border-blue-200 transition">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[18px]">assignment</span>
                </div>
                <div>
                    <h4 class="font-bold text-xs text-[#0d1c30]">Audit Tiket</h4>
                    <p class="text-[10px] text-slate-400">Periksa laporan teknisi</p>
                </div>
            </a>
        </div>

        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="font-extrabold text-sm text-[#0d1c30]">Tren Temuan Mingguan</h3>
                    <p class="text-[11px] text-slate-400 font-medium">Total <?php echo $total_laporan; ?> temuan terdeteksi pekan ini</p>
                </div>
                <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2.5 py-1 rounded-xl">Sen - Min</span>
            </div>

            <div class="flex items-end justify-between h-32 pt-6 px-2 border-b border-slate-100 pb-2">
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] font-bold text-slate-600">3</span>
                    <div class="w-7 bg-blue-100 hover:bg-blue-200 rounded-t-lg transition-all" style="height: 42%;"></div>
                    <span class="text-[10px] font-semibold text-slate-400">Sen</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] font-bold text-slate-600">5</span>
                    <div class="w-7 bg-blue-100 hover:bg-blue-200 rounded-t-lg transition-all" style="height: 70%;"></div>
                    <span class="text-[10px] font-semibold text-slate-400">Sel</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] font-bold text-slate-600">2</span>
                    <div class="w-7 bg-blue-100 hover:bg-blue-200 rounded-t-lg transition-all" style="height: 28%;"></div>
                    <span class="text-[10px] font-semibold text-slate-400">Rab</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] font-bold text-slate-600">7</span>
                    <div class="w-7 bg-blue-100 hover:bg-blue-200 rounded-t-lg transition-all" style="height: 95%;"></div>
                    <span class="text-[10px] font-semibold text-slate-400">Kam</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] font-extrabold text-[#0052cc]">6</span>
                    <div class="w-7 bg-[#0052cc] rounded-t-lg shadow-md transition-all" style="height: 85%;"></div>
                    <span class="text-[10px] font-bold text-[#0052cc]">Jum</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] text-slate-300">-</span>
                    <div class="w-7 bg-slate-50 rounded-t-lg" style="height: 10%;"></div>
                    <span class="text-[10px] font-semibold text-slate-300">Sab</span>
                </div>
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[10px] text-slate-300">-</span>
                    <div class="w-7 bg-slate-50 rounded-t-lg" style="height: 10%;"></div>
                    <span class="text-[10px] font-semibold text-slate-300">Min</span>
                </div>
            </div>

            <div class="flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                <span class="w-3 h-3 rounded bg-[#0052cc] inline-block"></span> Hari Ini: 6 Temuan
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h3 class="font-extrabold text-sm text-[#0d1c30]">Aktivitas Laporan Terbaru</h3>
                <a href="laporan_admin.php" class="text-[11px] font-bold text-[#0052cc] hover:underline flex items-center gap-0.5">
                    Lihat Semua <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                </a>
            </div>

            <?php if (mysqli_num_rows($query_aktivitas) > 0): ?>
                <?php while ($lap = mysqli_fetch_assoc($query_aktivitas)): ?>
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 relative">
                        <div class="flex justify-between items-center">
                            <span class="bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> <?php echo htmlspecialchars($lap['potensi_ancaman'] ?: 'KRITIS (Rawan Putus)'); ?>
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-bold text-slate-400">#PAT-2026-<?php echo str_pad($lap['id'], 3, '0', STR_PAD_LEFT); ?></span>
                                <button class="text-slate-400 hover:text-[#0052cc]"><span class="material-symbols-outlined text-[18px]">bookmark</span></button>
                            </div>
                        </div>

                        <div>
                            <h3 class="font-extrabold text-sm text-[#0d1c30] leading-snug"><?php echo htmlspecialchars($lap['lokasi_area']); ?></h3>
                            <p class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px] text-rose-500">location_on</span>
                                <span><?php echo htmlspecialchars($lap['lokasi_area']); ?> (<?php echo $lap['latitude']; ?>, <?php echo $lap['longitude']; ?>)</span>
                            </p>
                        </div>

                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 grid grid-cols-2 gap-3 text-[11px] items-center">
                            <?php 
                            $foto_path = "../assets/img/uploads/" . $lap['foto'];
                            if (!empty($lap['foto']) && file_exists($foto_path)) :
                            ?>
                            <div>
                                <div class="relative">
                                    <img src="<?php echo $foto_path; ?>" class="w-full h-24 object-cover rounded-lg border">
                                    <span class="absolute bottom-1 left-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded font-bold">Bukti</span>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div class="space-y-1">
                                <p class="text-slate-500">Pelapor: <b class="text-slate-800"><?php echo htmlspecialchars($lap['username']); ?> (NIP-<?php echo htmlspecialchars($lap['nip'] ?? '44821'); ?>)</b></p>
                                <p class="text-slate-500">Waktu Temuan: <b class="text-slate-800"><?php echo date('d M, H:i', strtotime($lap['tanggal'])); ?> WITA</b></p>
                                <p class="text-slate-500">Dampak FO: <b class="text-rose-600 font-bold"><?php echo htmlspecialchars($lap['kondisi_fisik'] ?: 'Tegangan 48 Core Main Span'); ?></b></p>
                            </div>
                        </div>

                        <a href="detail_laporan_admin.php?id=<?php echo $lap['id']; ?>" class="w-full bg-[#0052cc]/10 hover:bg-[#0052cc]/20 text-[#0052cc] font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                            <span class="material-symbols-outlined text-[18px]">visibility</span> Detail Tiket
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-6 rounded-2xl border border-slate-100 text-center text-xs text-slate-400">
                    Belum ada aktivitas laporan terbaru.
                </div>
            <?php endif; ?>
        </div>

    </main>

    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-[#0052cc] font-bold"><span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>

</body>
</html>