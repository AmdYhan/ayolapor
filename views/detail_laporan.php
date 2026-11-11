<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$id_laporan = $_GET['id'] ?? '';
$query = mysqli_query($conn, "SELECT laporan_patroli.*, users.username, users.nip, users.role FROM laporan_patroli JOIN users ON laporan_patroli.id_teknisi = users.id WHERE laporan_patroli.id='$id_laporan'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: riwayat.php");
    exit();
}

$current_page = 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#0d1c30] pb-24">

    <!-- HEADER -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-30 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="riwayat.php" class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center text-[#0d1c30]">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <h1 class="font-bold text-base">Detail Laporan Patroli</h1>
        </div>
    </header>

    <!-- CONTENT -->
    <main class="max-w-md mx-auto p-4 space-y-4">
        
        <!-- STATUS & TIKET CARD -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <span class="text-[10px] text-[#5E6C84] uppercase font-bold tracking-wider">Nomor Tiket</span>
                    <h2 class="font-bold text-sm text-[#0052cc]"><?php echo htmlspecialchars($data['id'] ?? ''); ?></h2>
                </div>
                <div class="text-right">
                    <span class="text-[10px] text-[#5E6C84] uppercase font-bold tracking-wider block">Status</span>
                    <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200 mt-0.5">
                        <?php echo ucfirst($data['status_laporan'] ?? 'Menunggu Verifikasi'); ?>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <span class="text-[#5E6C84] block">Waktu Patroli</span>
                    <strong class="font-semibold text-[#0d1c30]"><?php echo htmlspecialchars($data['tanggal'] ?? '-'); ?></strong>
                </div>
                <div>
                    <span class="text-[#5E6C84] block">Teknisi Pelapor</span>
                    <strong class="font-semibold text-[#0d1c30]"><?php echo htmlspecialchars(ucfirst($data['username'])); ?> (NIP: <?php echo htmlspecialchars($data['nip']); ?>)</strong>
                </div>
            </div>
        </div>

        <!-- LOKASI & GPS -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2">
            <h3 class="font-bold text-xs text-[#5E6C84] uppercase tracking-wider">Lokasi & Koordinat</h3>
            <div class="flex items-start gap-2 pt-1">
                <span class="material-symbols-outlined text-[#0052cc] text-[20px] mt-0.5">location_on</span>
                <div>
                    <strong class="text-xs text-[#0d1c30] block"><?php echo htmlspecialchars($data['lokasi_area']); ?></strong>
                    <span class="text-[11px] text-[#5E6C84] block mt-0.5">Lat/Lng: <?php echo htmlspecialchars($data['latitude']); ?>, <?php echo htmlspecialchars($data['longitude']); ?></span>
                    <span class="text-[10px] text-emerald-600 font-semibold block mt-1">Akurasi GPS: ±<?php echo htmlspecialchars($data['akurasi_gps']); ?>m</span>
                </div>
            </div>
        </div>

        <!-- KONDISI & ANCAMAN -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <h3 class="font-bold text-xs text-[#5E6C84] uppercase tracking-wider">Hasil Pemeriksaan Lapangan</h3>
            
            <div>
                <span class="text-[11px] text-[#5E6C84] block">Kondisi Fisik Kabel/Tiang</span>
                <span class="inline-block px-3 py-1 rounded-lg bg-slate-100 font-bold text-xs text-[#0d1c30] mt-1">
                    <?php echo htmlspecialchars($data['kondisi_fisik']); ?>
                </span>
            </div>

            <div>
                <span class="text-[11px] text-[#5E6C84] block mb-1">Potensi Ancaman Dilaporkan</span>
                <div class="flex flex-wrap gap-1.5">
                    <?php 
                        $ancaman_list = explode(', ', $data['potensi_ancaman']);
                        foreach($ancaman_list as $ancaman) {
                            if(!empty($ancaman)) {
                                echo '<span class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-100 text-[11px] font-semibold">'.htmlspecialchars($ancaman).'</span>';
                            }
                        }
                    ?>
                </div>
            </div>

            <div>
                <span class="text-[11px] text-[#5E6C84] block">Catatan Tambahan</span>
                <p class="text-xs text-[#0d1c30] bg-slate-50 p-2.5 rounded-xl mt-1 border border-slate-100">
                    <?php echo nl2br(htmlspecialchars($data['catatan'] ?? 'Tidak ada catatan.')); ?>
                </p>
            </div>
        </div>

        <!-- FOTO BUKTI LAPANGAN -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <h3 class="font-bold text-xs text-[#5E6C84] uppercase tracking-wider">Foto Bukti Lapangan</h3>
            <?php if (!empty($data['foto'])): ?>
                <div class="text-center">
                    <img src="../assets/img/uploads/<?php echo htmlspecialchars($data['foto']); ?>" alt="Foto Bukti Patroli" class="mx-auto h-48 object-cover rounded-xl border border-slate-200 shadow-sm">
                </div>
            <?php else: ?>
                <p class="text-xs text-slate-400 italic">Tidak ada foto yang dilampirkan.</p>
            <?php endif; ?>
        </div>

    </main>

    <!-- BOTTOM NAVBAR -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 py-2 px-6 flex justify-around items-center z-30 max-w-md mx-auto">
        <a href="beranda.php" class="flex flex-col items-center <?php echo ($current_page == 'beranda') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'beranda') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>grid_view</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="form_patroli.php" class="flex flex-col items-center <?php echo ($current_page == 'patroli') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'patroli') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>assignment</span>
            <span class="text-[10px] mt-0.5">Patroli</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center <?php echo ($current_page == 'riwayat') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'riwayat') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>history</span>
            <span class="text-[10px] mt-0.5">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center <?php echo ($current_page == 'profil') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'profil') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>person</span>
            <span class="text-[10px] mt-0.5">Profil</span>
        </a>
    </nav>

</body>
</html>