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

// Filter Pencarian, Tanggal & Status
$search = $_GET['search'] ?? '';
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';
$status_filter = $_GET['status_filter'] ?? 'semua';

$where = ["1=1"];
if (!empty($search)) {
    $s = mysqli_real_escape_string($conn, $search);
    $where[] = "(l.lokasi_area LIKE '%$s%' OR l.username LIKE '%$s%' OR l.catatan LIKE '%$s%')";
}
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $where[] = "DATE(l.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
}
if ($status_filter !== 'semua') {
    $st = mysqli_real_escape_string($conn, $status_filter);
    $where[] = "l.status_laporan = '$st'";
}

$where_sql = implode(" AND ", $where);
$query_laporan = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE $where_sql ORDER BY l.tanggal DESC");

// Hitung Counter Statistik
$q_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Menunggu Verifikasi'"))['total'] ?? 0;
$q_kritis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Kritis%' OR kondisi_fisik LIKE '%Rusak%'"))['total'] ?? 0;
$q_disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Disetujui'"))['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan Admin - Lintasarta</title>
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

        <!-- TITLE BLOCK -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-base text-[#0d1c30]">Verifikasi Antrean Patroli</h2>
                <p class="text-xs text-slate-400">Hub Samarinda & Koridor Mahakam</p>
            </div>
            <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold px-2.5 py-1 rounded-full border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersinkronisasi
            </span>
        </div>

        <!-- STATS CARD -->
        <div class="grid grid-cols-3 gap-2.5">
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">Menunggu</span>
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                </div>
                <p class="text-xl font-extrabold text-[#0d1c30] mt-1"><?php echo $q_menunggu; ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5">Perlu review</p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">Kritis</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <p class="text-xl font-extrabold text-[#0d1c30] mt-1"><?php echo $q_kritis; ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5">Tindakan darurat</p>
            </div>
            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm">
                <div class="flex justify-between items-center">
                    <span class="text-[11px] font-semibold text-slate-500">Disetujui</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <p class="text-xl font-extrabold text-[#0d1c30] mt-1"><?php echo $q_disetujui; ?></p>
                <p class="text-[10px] text-slate-400 mt-0.5">Hari ini</p>
            </div>
        </div>

        <!-- FORM FILTER SEARCH & WAKTU -->
        <form method="GET" class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm space-y-3">
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[20px]">search</span>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari tiket, teknisi, atau segmen..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl py-2 pl-9 pr-3 text-xs font-semibold focus:outline-none focus:border-[#0052cc]">
            </div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Dari Tanggal</label>
                    <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Sampai Tanggal</label>
                    <input type="date" name="tgl_selesai" value="<?php echo $tgl_selesai; ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-2 text-xs">
                </div>
            </div>
            <div class="flex gap-2 pt-1">
                <button type="submit" class="flex-1 bg-[#0052cc] text-white font-bold py-2 rounded-xl text-xs flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span> Terapkan Filter
                </button>
                <a href="laporan_admin.php" class="bg-slate-100 text-slate-600 font-bold px-3 py-2 rounded-xl text-xs flex items-center">Reset</a>
            </div>
        </form>

        <!-- FORM UTAMA DENGAN CHECKBOX SELEKSI & TOMBOL EKSPOR (PDF/EXCEL) -->
        <form id="formAksi" method="POST">

            <div class="bg-white p-3 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between mb-3">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" class="w-4 h-4 text-[#0052cc] rounded">
                    <span>Pilih Semua</span>
                </label>
                <div class="flex gap-1.5">
                    <button type="button" onclick="submitAksi('pdf')" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-[15px]">picture_as_pdf</span> PDF
                    </button>
                    <button type="button" onclick="submitAksi('excel')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-[15px]">table_chart</span> Excel
                    </button>
                </div>
            </div>

            <!-- DAFTAR KARTU LAPORAN -->
            <div class="space-y-3">
                <?php if (mysqli_num_rows($query_laporan) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($query_laporan)) : 
                        $status_lap = $row['status_laporan'];
                        $badge_color = ($status_lap == 'Disetujui') ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 
                                      (($status_lap == 'Perlu Revisi') ? 'bg-amber-50 text-amber-600 border-amber-200' : 
                                      (($status_lap == 'Ditolak') ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-blue-50 text-blue-600 border-blue-200'));
                    ?>
                        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
                            
                            <div class="flex justify-between items-start">
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" name="laporan_ids[]" value="<?php echo $row['id']; ?>" class="item-check w-4 h-4 text-[#0052cc] rounded">
                                    <div>
                                        <span class="text-[10px] font-bold text-slate-400">#PAT-2026-<?php echo str_pad($row['id'], 3, '0', STR_PAD_LEFT); ?></span>
                                        <h3 class="font-extrabold text-xs text-[#0d1c30] mt-0.5 leading-snug"><?php echo htmlspecialchars($row['lokasi_area']); ?></h3>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border <?php echo $badge_color; ?>"><?php echo $status_lap; ?></span>
                            </div>

                            <p class="text-[11px] text-slate-500 font-medium pl-6">
                                Teknisi: <b><?php echo htmlspecialchars($row['username']); ?></b> (NIP: <?php echo $row['nip'] ?? '-'; ?>)<br>
                                <span class="text-blue-600 font-bold">Lat/Long: <?php echo $row['latitude']; ?>, <?php echo $row['longitude']; ?></span>
                            </p>

                            <!-- FOTO THUMBNAIL -->
                            <?php 
                            $foto_path = "../assets/img/uploads/" . $row['foto'];
                            if (!empty($row['foto']) && file_exists($foto_path)) :
                            ?>
                                <div class="pl-6">
                                    <img src="<?php echo $foto_path; ?>" class="w-full h-32 object-cover rounded-xl border border-slate-100">
                                </div>
                            <?php endif; ?>

                            <!-- TOMBOL MENUJU HALAMAN DETAIL KHUSUS -->
                            <div class="pl-6 pt-1 flex gap-2">
                                <a href="detail_laporan_admin.php?id=<?php echo $row['id']; ?>" class="w-full bg-[#0052cc]/10 hover:bg-[#0052cc]/20 text-[#0052cc] font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1 transition">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span> Lihat Detail & Verifikasi
                                </a>
                            </div>

                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="bg-white rounded-2xl p-8 text-center border border-slate-100 space-y-2">
                        <span class="material-symbols-outlined text-slate-300 text-[40px]">assignment_late</span>
                        <p class="text-xs font-bold text-slate-400">Tidak ada laporan ditemukan.</p>
                    </div>
                <?php endif; ?>
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

    <!-- SCRIPT INTERAKTIF -->
    <script>
        function toggleSelectAll(source) {
            checkboxes = document.getElementsByClassName('item-check');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function submitAksi(jenis) {
            const form = document.getElementById('formAksi');
            if (jenis === 'pdf') {
                form.action = '../process/cetak_pdf.php';
            } else {
                form.action = '../process/cetak_excel.php';
            }
            form.target = '_blank';
            form.submit();
        }
    </script>
</body>
</html>