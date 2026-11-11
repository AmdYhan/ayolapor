<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../views/login.php");
    exit();
}

$laporan_ids = $_POST['laporan_ids'] ?? [];

if (empty($laporan_ids)) {
    echo "<script>alert('Harap centang minimal satu laporan yang ingin dicetak!'); window.history.back();</script>";
    exit();
}

$ids_clean = array_map('intval', $laporan_ids);
$ids_sql = implode(",", $ids_clean);

// Ambil data laporan yang dipilih, urutkan dari yang terlama ke terbaru
$query_laporan = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.id IN ($ids_sql) ORDER BY l.tanggal ASC");
$total_giat = mysqli_num_rows($query_laporan);

// Array untuk menampung tanggal dan waktu guna mencari rentang (min & max)
$arr_tanggal = [];
$arr_waktu = [];
$lokasi_array = [];

while ($row = mysqli_fetch_assoc($query_laporan)) {
    $data_laporan[] = $row;
    $tgl_obj = strtotime($row['tanggal']);
    $arr_tanggal[] = date('Y-m-d', $tgl_obj);
    $arr_waktu[] = date('H:i', $tgl_obj);
    $lokasi_array[] = $row['lokasi_area'];
}

// Reset pointer query atau looping ulang dengan data_laporan array
// Tentukan Tanggal Patroli (Jika rentang, tampilkan dari tgl awal ke tgl akhir. Jika 1 hari, tampilkan tanggal itu)
$min_tgl = min($arr_tanggal);
$max_tgl = max($arr_tanggal);
if ($min_tgl === $max_tgl) {
    $formatted_tanggal = date('d F Y', strtotime($min_tgl));
} else {
    $formatted_tanggal = date('d M Y', strtotime($min_tgl)) . ' s.d. ' . date('d M Y', strtotime($max_tgl));
}

// Tentukan Waktu Operasional (Dari jam laporan paling awal ke jam laporan paling akhir)
sort($arr_waktu);
$min_jam = reset($arr_waktu);
$max_jam = end($arr_waktu);
$waktu_operasional = $min_jam . ' - ' . $max_jam . ' WITA';

// Cakupan wilayah unik
$wilayah_cakupan = implode(', ', array_unique($lokasi_array));
if(strlen($wilayah_cakupan) > 50) {
    $wilayah_cakupan = "Samarinda & Koridor Mahakam";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Rekapitulasi Patroli - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #ffffff; color: #0d1c30; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #94a3b8; padding: 6px 8px; vertical-align: top; font-size: 10.5px; }
        th { background-color: #f1f5f9; font-weight: 700; text-align: center; color: #0f172a; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0.8cm; background: white; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
    </style>
</head>
<body class="p-4 max-w-4xl mx-auto">

    <!-- TOMBOL PRATINJAU CETAK -->
    <div class="no-print flex justify-between items-center mb-6 bg-slate-100 p-4 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <h2 class="font-bold text-sm text-slate-800">Dokumen PDF Siap Dicetak</h2>
            <p class="text-xs text-slate-500">Mencetak <?php echo $total_giat; ?> laporan terpilih dengan penyesuaian waktu dinamis.</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.close()" class="bg-white border border-slate-300 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold">Tutup</button>
            <button onclick="window.print()" class="bg-[#0052cc] hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- KOP LAPORAN TASK FORCE -->
    <div class="border-b-2 border-[#0052cc] pb-3 mb-4 flex justify-between items-end">
        <div>
            <h1 class="font-extrabold text-base text-[#0d1c30] tracking-wide">LAPORAN REKAPITULASI PATROLI LAPANGAN</h1>
            <p class="text-[11px] text-slate-500 font-medium mt-0.5">Sistem Pemeliharaan Infrastruktur OSP Lintasarta Samarinda</p>
        </div>
        <img src="../assets/img/Lintasarta_logo.png" alt="Logo Lintasarta" class="h-8 object-contain">
    </div>

    <!-- SUMMARY INFORMASI DINAMIS -->
    <div class="grid grid-cols-2 gap-4 bg-slate-50 border border-slate-300 p-3 rounded-lg text-[11px] mb-4">
        <div class="space-y-1">
            <div class="flex"><span class="w-36 font-bold text-slate-600">Tanggal Patroli:</span> <span class="font-semibold text-slate-900"><?php echo $formatted_tanggal; ?></span></div>
            <div class="flex"><span class="w-36 font-bold text-slate-600">Total Giat:</span> <span class="font-semibold text-slate-900"><?php echo $total_giat; ?> Titik Pengamatan</span></div>
            <div class="flex"><span class="w-36 font-bold text-slate-600">Waktu Operasional:</span> <span class="font-semibold text-slate-900"><?php echo $waktu_operasional; ?></span></div>
        </div>
        <div class="space-y-1">
            <div class="flex"><span class="w-36 font-bold text-slate-600">Fokus Patroli:</span> <span class="font-semibold text-slate-900">Jalur Backbone & Patok Fiber Optik</span></div>
            <div class="flex"><span class="w-36 font-bold text-slate-600">Wilayah Cakupan:</span> <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($wilayah_cakupan); ?></span></div>
            <div class="flex"><span class="w-36 font-bold text-slate-600">Status Ringkasan:</span> <span class="font-semibold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded text-[10px]">Aman & Terkendali</span></div>
        </div>
    </div>

    <h3 class="font-extrabold text-xs text-slate-700 uppercase tracking-wider mb-1.5">DETAIL HASIL PEMANTAUAN LAPANGAN</h3>

    <!-- TABEL HASIL DENGAN FOTO EVIDEN -->
    <table>
        <thead>
            <tr>
                <th class="w-8">NO</th>
                <th class="w-32">EVIDEN FOTO</th>
                <th class="w-24">WAKTU (WITA)</th>
                <th class="w-28">TITIK KOORDINAT</th>
                <th class="w-36">LOKASI / AREA</th>
                <th>KONDISI & HASIL PATROLI</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($data_laporan as $row) {
                $foto_path = "../assets/img/uploads/" . $row['foto'];
                $has_foto = (!empty($row['foto']) && file_exists($foto_path));
            ?>
            <tr>
                <td class="text-center font-bold"><?php echo $no++; ?></td>
                <td class="text-center">
                    <?php if ($has_foto) { ?>
                        <img src="<?php echo $foto_path; ?>" class="w-28 h-20 object-cover rounded border border-slate-300 mx-auto block">
                    <?php } else { ?>
                        <span class="text-[9px] text-slate-400 italic block py-4">Tidak ada foto</span>
                    <?php } ?>
                </td>
                <td class="text-center">
                    <span class="font-bold text-slate-800 block"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></span>
                    <span class="text-[10px] text-slate-500 block mt-0.5"><?php echo date('H:i', strtotime($row['tanggal'])); ?> WITA</span>
                </td>
                <td>
                    <code class="text-[10px] text-blue-700 font-bold block"><?php echo $row['latitude']; ?>,</code>
                    <code class="text-[10px] text-blue-700 font-bold block"><?php echo $row['longitude']; ?></code>
                </td>
                <td>
                    <strong class="text-slate-900 block leading-tight"><?php echo htmlspecialchars($row['lokasi_area']); ?></strong>
                    <span class="text-[9.5px] text-slate-500 block mt-1">Teknisi: <b><?php echo htmlspecialchars($row['username']); ?></b></span>
                </td>
                <td>
                    <p class="text-slate-800 font-medium leading-normal"><?php echo htmlspecialchars($row['catatan']); ?></p>
                    <div class="mt-1.5 pt-1 border-t border-slate-200 flex flex-wrap gap-x-3 gap-y-0.5 text-[9.5px] text-slate-600">
                        <span>Fisik: <b><?php echo $row['kondisi_fisik']; ?></b></span>
                        <span>•</span>
                        <span>Potensi Ancaman: <b><?php echo $row['potensi_ancaman']; ?></b></span>
                    </div>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- SIGNATURE FOOTER -->
    <div class="mt-8 pt-4 border-t border-slate-300 grid grid-cols-2 text-xs text-center page-break-inside-avoid">
        <div>
            <p class="text-slate-500">Dibuat Oleh,</p>
            <p class="font-bold text-slate-800 mt-10">( Tim Teknisi Lapangan OSP )</p>
        </div>
        <div>
            <p class="text-slate-500">Mengetahui / Disetujui,</p>
            <p class="font-bold text-slate-800 mt-10">( Koordinator / Pimpinan Admin )</p>
        </div>
    </div>

</body>
</html>