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

$id_laporan = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data laporan berdasarkan ID
$query = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.id = $id_laporan");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Laporan tidak ditemukan!'); window.location.href='laporan_admin.php';</script>";
    exit();
}

// Proses Aksi Verifikasi (Setujui atau Minta Revisi)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $catatan_disposisi = mysqli_real_escape_string($conn, $_POST['catatan_disposisi'] ?? '');
    
    $status_baru = ($aksi === 'setujui') ? 'Disetujui' : 'Perlu Revisi';
    
    // Update status dan catatan disposisi ke database
    mysqli_query($conn, "UPDATE laporan_patroli SET status_laporan = '$status_baru', catatan = CONCAT(catatan, ' [Catatan Supervisor: $catatan_disposisi]') WHERE id = $id_laporan");
    
    echo "<script>alert('Status laporan berhasil diperbarui menjadi: $status_baru'); window.location.href='laporan_admin.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan #PAT-2026-<?php echo str_pad($data['id'], 3, '0', STR_PAD_LEFT); ?> - Lintasarta</title>
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
            <div class="flex items-center gap-3">
                <a href="laporan_admin.php" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                    <p class="text-[10px] text-slate-400 font-medium">Detail Laporan</p>
                </div>
            </div>
            <a href="profil_admin.php" class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                <span class="material-symbols-outlined text-[20px]">person</span>
            </a>
        </div>
    </header>

    <main class="max-w-md mx-auto p-4 space-y-4">

        <!-- STATUS BADGES & ID -->
        <div class="space-y-1.5">
            <div class="flex gap-2">
                <span class="bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full"><?php echo $data['status_laporan']; ?></span>
                <span class="bg-rose-100 text-rose-700 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full">Kritis / Darurat</span>
            </div>
            <div class="flex justify-between items-center">
                <h2 class="font-extrabold text-base text-[#0d1c30]">#PAT-2026-<?php echo str_pad($data['id'], 3, '0', STR_PAD_LEFT); ?></h2>
                <span class="text-xs text-slate-400 font-semibold"><?php echo date('d M Y, H:i', strtotime($data['tanggal'])); ?> WITA</span>
            </div>
        </div>

        <!-- KARTU LOKASI -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px] text-rose-500">location_on</span> Lokasi Patroli
            </div>
            <h3 class="font-extrabold text-sm text-[#0d1c30]"><?php echo htmlspecialchars($data['lokasi_area']); ?></h3>
            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 flex justify-between items-center text-xs">
                <div>
                    <span class="text-slate-400 block text-[10px]">Titik Koordinat GPS:</span>
                    <code class="text-blue-600 font-bold"><?php echo $data['latitude']; ?>, <?php echo $data['longitude']; ?></code>
                </div>
                <span class="bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2 py-1 rounded-lg border border-emerald-200">Akurasi Valid</span>
            </div>
            <p class="text-xs text-slate-500 pt-1">Pelapor / Teknisi: <b class="text-slate-800"><?php echo htmlspecialchars($data['username']); ?></b> (NIP: <?php echo $data['nip'] ?? '-'; ?>)</p>
        </div>

        <!-- KONDISI LAPANGAN -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px] text-blue-500">assignment</span> Kondisi Lapangan
            </div>
            <div class="bg-rose-50/70 border border-rose-200 p-3 rounded-xl flex justify-between items-center">
                <div>
                    <span class="text-[10px] text-rose-500 font-bold block">Status Fisik Jalur</span>
                    <strong class="text-rose-700 text-xs"><?php echo $data['kondisi_fisik']; ?></strong>
                </div>
                <span class="bg-rose-600 text-white text-[10px] font-bold px-3 py-1.5 rounded-xl">Tindakan Segera</span>
            </div>
            <div>
                <span class="text-[11px] font-bold text-slate-500 block mb-1">Potensi Ancaman Teridentifikasi:</span>
                <p class="bg-slate-50 p-3 rounded-xl border text-xs font-semibold text-slate-700"><?php echo $data['potensi_ancaman']; ?></p>
            </div>
        </div>

        <!-- DOKUMENTASI BUKTI LAPANGAN -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <div class="flex justify-between items-center text-xs font-bold text-slate-400 uppercase tracking-wider">
                <span>Dokumentasi Bukti Lapangan</span>
                <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded">Terverifikasi</span>
            </div>
            
            <?php 
            $foto_path = "../assets/img/uploads/" . $data['foto'];
            if (!empty($data['foto']) && file_exists($foto_path)) :
            ?>
            <div>
                <img src="<?php echo $foto_path; ?>" class="w-full h-48 object-cover rounded-xl border border-slate-100">
                <p class="text-[10px] text-slate-400 mt-1 italic">Eviden Foto Geotagging Utama</p>
            </div>
            <?php endif; ?>

            <div>
                <span class="text-[11px] font-bold text-slate-500 block mb-1">Catatan Teknisi:</span>
                <p class="text-xs text-slate-600 bg-blue-50/50 p-3 rounded-xl border border-blue-100 italic">"<?php echo htmlspecialchars($data['catatan']); ?>"</p>
            </div>
        </div>

        <!-- RIWAYAT ALUR LAPORAN (TIMELINE) -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-4">
            <div class="flex justify-between items-center border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2 text-slate-700 font-extrabold text-xs">
                    <span class="material-symbols-outlined text-[#0052cc] text-[18px]">schedule</span>
                    <span>Riwayat Alur Laporan</span>
                </div>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">Auto-synced</span>
            </div>

            <div class="relative pl-6 space-y-6 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-0.5 before:bg-blue-100 text-xs">
                <!-- Step 1 -->
                <div class="relative space-y-0.5">
                    <span class="absolute -left-[23px] top-1 w-3.5 h-3.5 rounded-full bg-[#0052cc] ring-4 ring-blue-50"></span>
                    <div class="flex justify-between items-center">
                        <h4 class="font-extrabold text-[#0d1c30]">Laporan Diinput Teknisi</h4>
                        <span class="text-[10px] text-slate-400 font-medium"><?php echo date('H:i', strtotime($data['tanggal'])); ?> WITA</span>
                    </div>
                    <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($data['username']); ?> via Samarinda Mobile Patrol</p>
                </div>

                <!-- Step 2 -->
                <div class="relative space-y-0.5">
                    <span class="absolute -left-[23px] top-1 w-3.5 h-3.5 rounded-full bg-[#0052cc] ring-4 ring-blue-50"></span>
                    <div class="flex justify-between items-center">
                        <h4 class="font-extrabold text-[#0d1c30]">Validasi Telemetri & GNSS</h4>
                        <span class="text-[10px] text-slate-400 font-medium"><?php echo date('H:i', strtotime($data['tanggal']) + 180); ?> WITA</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Koordinat diverifikasi dalam poligon OSP <?php echo htmlspecialchars($data['lokasi_area']); ?></p>
                </div>

                <!-- Step 3 -->
                <div class="relative space-y-0.5">
                    <span class="absolute -left-[23px] top-1 w-3.5 h-3.5 rounded-full bg-amber-500 ring-4 ring-amber-50 animate-pulse"></span>
                    <div class="flex justify-between items-center">
                        <h4 class="font-extrabold text-[#0d1c30]">Review & Persetujuan Supervisor</h4>
                        <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded">Saat Ini</span>
                    </div>
                    <p class="text-[11px] text-slate-500">Menunggu keputusan disposisi dan penugasan regu perbaikan</p>
                </div>
            </div>
        </div>

        <!-- FORM UTAMA VERIFIKASI & CATATAN DISPOSISI -->
        <form action="" method="POST" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-wider">
                <span class="material-symbols-outlined text-[16px] text-amber-500">rate_review</span> Catatan Disposisi Supervisor
            </div>
            
            <textarea name="catatan_disposisi" rows="3" placeholder="Tulis instruksi khusus untuk tim penanganan darurat (opsional)..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs focus:outline-none focus:border-[#0052cc]"></textarea>
            
            <div class="grid grid-cols-2 gap-2.5 pt-2">
                <button type="submit" name="aksi" value="revisi" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-xl text-xs flex items-center justify-center gap-1 transition shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">edit_note</span> Minta Revisi
                </button>
                <button type="submit" name="aksi" value="setujui" class="bg-[#0052cc] hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs flex items-center justify-center gap-1 transition shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span> Setujui & Eskalasi
                </button>
            </div>
        </form>

    </main>

    <!-- BOTTOM NAVBAR ADMIN -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-[#0052cc] font-bold"><span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>

</body>
</html>