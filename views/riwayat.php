<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Ambil data user yang sedang login beserta NIP
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);
$id_teknisi = $user['id'] ?? 0;

// Ambil daftar riwayat laporan berdasarkan id_teknisi atau username
$query_riwayat = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.id_teknisi='$id_teknisi' OR l.username='$username' ORDER BY l.id DESC");
$current_page = 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Patroli - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-4 py-4 shadow-xs">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="beranda.php" class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[#0d1c30] active:scale-95 transition">
                    <span class="material-symbols-outlined text-[22px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-extrabold text-base tracking-wide text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                    <p class="text-xs text-slate-500 font-semibold tracking-wide">Riwayat Laporan</p>
                </div>
            </div>
            
            <a href="profil.php" class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0052cc] shadow-xs active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <!-- KONTEN UTAMA RIWAYAT -->
    <main class="max-w-md mx-auto p-4 space-y-3.5">
        <div class="flex items-center justify-between mb-1 px-1">
            <h2 class="font-extrabold text-sm text-[#0d1c30] tracking-tight">Daftar Laporan Saya</h2>
            <span class="text-[10px] font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-xl">
                <?php echo mysqli_num_rows($query_riwayat); ?> Laporan
            </span>
        </div>

        <?php if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($query_riwayat)): ?>
                <?php 
                    $potensi = $row['potensi_ancaman'] ?: 'KRITIS (Rawan Putus)';
                    $nomor_tiket = '#PAT-2026-' . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                    $status_laporan = trim($row['status_laporan'] ?? 'Menunggu Verifikasi');
                    
                    // Logika Pewarnaan Status: Disetujui = Hijau | Ditolak = Merah | Pending = Oren
                    $status_lower = strtolower($status_laporan);
                    if ($status_lower == 'disetujui' || $status_lower == 'approved' || $status_lower == 'selesai') {
                        $status_class = "bg-emerald-50 text-emerald-700 border-emerald-200";
                    } elseif ($status_lower == 'ditolak' || $status_lower == 'rejected') {
                        $status_class = "bg-rose-50 text-rose-700 border-rose-200";
                    } else {
                        $status_class = "bg-amber-50 text-amber-700 border-amber-200";
                    }

                    // Logika Pewarnaan Kondisi Fisik
                    $kondisi_fisik = $row['kondisi_fisik'] ?: 'Tegangan 48 Core Main Span';
                    $kondisi_lower = strtolower($kondisi_fisik);
                    if ($kondisi_lower == 'baik' || strpos($kondisi_lower, 'normal') !== false) {
                        $kondisi_class = "text-emerald-600 font-bold";
                    } else {
                        $kondisi_class = "text-rose-600 font-bold";
                    }
                ?>
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 relative hover:shadow-md transition">
                    
                    <!-- KELOMPOK HEADER: Badge Ancaman & Nomor Tiket + Status -->
                    <div class="flex items-start justify-between gap-2">
                        <span class="bg-blue-50 text-[#0052cc] border border-blue-200 text-[10px] font-extrabold px-2.5 py-1 rounded-lg flex items-center gap-1.5 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#0052cc]"></span> 
                            <?php echo htmlspecialchars($potensi); ?>
                        </span>
                        
                        <div class="text-right flex-shrink-0">
                            <span class="block text-[10px] font-bold text-slate-400"><?php echo $nomor_tiket; ?></span>
                            <span class="inline-block mt-0.5 text-[10px] font-extrabold px-2 py-0.5 rounded-md border <?php echo $status_class; ?>">
                                <?php echo htmlspecialchars($status_laporan); ?>
                            </span>
                        </div>
                    </div>

                    <!-- LOKASI & TITIK KOORDINAT -->
                    <div class="space-y-0.5">
                        <h3 class="font-extrabold text-sm text-[#0d1c30] leading-snug"><?php echo htmlspecialchars($row['lokasi_area']); ?></h3>
                        <p class="text-[11px] text-slate-500 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px] text-rose-500 flex-shrink-0">location_on</span>
                            <span class="truncate"><?php echo htmlspecialchars($row['lokasi_area']); ?> (<?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?>)</span>
                        </p>
                    </div>

                    <!-- KOTAK INFORMASI DETAIL & FOTO BUKTI -->
                    <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 grid grid-cols-5 gap-3 items-center">
                        <?php 
                        $foto_path = "../assets/img/uploads/" . $row['foto'];
                        if (!empty($row['foto']) && file_exists($foto_path)) :
                        ?>
                        <div class="col-span-2 relative">
                            <img src="<?php echo $foto_path; ?>" class="w-full h-24 object-cover rounded-lg border border-slate-200 shadow-xs">
                            <span class="absolute bottom-1 left-1 bg-black/60 backdrop-blur-xs text-white text-[9px] px-1.5 py-0.5 rounded font-bold">Bukti</span>
                        </div>
                        <div class="col-span-3 space-y-1 text-[11px]">
                        <?php else: ?>
                        <div class="col-span-5 space-y-1 text-[11px]">
                        <?php endif; ?>
                            <p class="text-slate-500">Pelapor: <b class="text-slate-800"><?php echo htmlspecialchars($row['username']); ?> (NIP-<?php echo htmlspecialchars($row['nip'] ?? '44821'); ?>)</b></p>
                            <p class="text-slate-500">Waktu: <b class="text-slate-800"><?php echo date('d M, H:i', strtotime($row['tanggal'])); ?> WITA</b></p>
                            <p class="text-slate-500">Dampak FO: <span class="<?php echo $kondisi_class; ?>"><?php echo htmlspecialchars($kondisi_fisik); ?></span></p>
                        </div>
                    </div>

                    <!-- BARIS TOMBOL AKSI YANG BERSIH & PROPORSIONAL -->
                    <div class="flex items-center gap-2 pt-1">
                        <!-- Tombol Utama: Detail Tiket -->
                        <a href="detail_laporan.php?id=<?php echo $row['id']; ?>" class="flex-1 bg-blue-50 hover:bg-blue-100 text-[#0052cc] font-bold py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                            <span class="material-symbols-outlined text-[16px]">visibility</span> Detail Tiket
                        </a>

                        <!-- Tombol Revisi (Hanya muncul jika status ditolak / perlu revisi) -->
                        <?php if ($status_lower == 'ditolak' || $status_lower == 'rejected' || $status_lower == 'perlu revisi'): ?>
                            <a href="edit_laporan.php?id=<?php echo $row['id']; ?>" class="bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold px-3 py-2.5 rounded-xl text-xs flex items-center gap-1 transition" title="Revisi Laporan">
                                <span class="material-symbols-outlined text-[16px]">edit</span> Revisi
                            </a>
                        <?php endif; ?>

                        <!-- Tombol Hapus (Dibuat minimalis berbentuk ikon berbingkai rapi) -->
                        <a href="../process/proses_hapus_laporan.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus laporan patroli ini?')" class="w-10 h-10 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center transition border border-rose-100 flex-shrink-0" title="Hapus Laporan">
                            <span class="material-symbols-outlined text-[18px]">delete</span>
                        </a>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-2xl border border-slate-100 p-6 space-y-3">
                <div class="w-16 h-16 bg-blue-50 text-[#0052cc] rounded-full flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined text-[32px]">history_toggle_off</span>
                </div>
                <h3 class="font-bold text-sm text-[#0d1c30]">Belum Ada Laporan</h3>
                <p class="text-xs text-slate-500 max-w-[200px] mx-auto">Riwayat patroli yang telah Anda kirimkan akan muncul di sini.</p>
                <a href="form_patroli.php" class="inline-block px-5 py-2.5 bg-[#0052cc] text-white text-xs font-bold rounded-xl shadow-md active:scale-95 transition">Buat Patroli Baru</a>
            </div>
        <?php endif; ?>
    </main>

    <!-- BOTTOM NAVBAR -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto">
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