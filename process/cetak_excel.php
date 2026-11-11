<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../views/login.php");
    exit();
}

$laporan_ids = $_POST['laporan_ids'] ?? [];

// Jika tidak ada yang dicentang, tarik semua data, jika ada ambil berdasarkan ID yang dicentang
if (empty($laporan_ids)) {
    $query = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username ORDER BY l.tanggal ASC");
} else {
    $ids_clean = array_map('intval', $laporan_ids);
    $ids_sql = implode(",", $ids_clean);
    $query = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.id IN ($ids_sql) ORDER BY l.tanggal ASC");
}

$total_giat = mysqli_num_rows($query);
$arr_tanggal = [];
$arr_waktu = [];
$lokasi_array = [];
$data_laporan = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data_laporan[] = $row;
    $tgl_obj = strtotime($row['tanggal']);
    $arr_tanggal[] = date('Y-m-d', $tgl_obj);
    $arr_waktu[] = date('H:i', $tgl_obj);
    $lokasi_array[] = $row['lokasi_area'];
}

// Hitung rentang tanggal & waktu secara dinamis
if ($total_giat > 0) {
    $min_tgl = min($arr_tanggal);
    $max_tgl = max($arr_tanggal);
    $formatted_tanggal = ($min_tgl === $max_tgl) ? date('d F Y', strtotime($min_tgl)) : date('d M Y', strtotime($min_tgl)) . ' s.d. ' . date('d M Y', strtotime($max_tgl));

    sort($arr_waktu);
    $waktu_operasional = reset($arr_waktu) . ' - ' . end($arr_waktu) . ' WITA';
    $wilayah_cakupan = implode(', ', array_unique($lokasi_array));
    if(strlen($wilayah_cakupan) > 50) { $wilayah_cakupan = "Samarinda & Koridor Mahakam"; }
} else {
    $formatted_tanggal = date('d F Y');
    $waktu_operasional = "08:00 - 17:00 WITA";
    $wilayah_cakupan = "Samarinda";
}

// Set header file Excel (.xls)
header("Content-Type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Laporan_Patroli_Lintasarta_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/1999/xlink">
<head>
    <meta charset="UTF-8">
    <style>
        .title { font-size: 16px; font-weight: bold; color: #0d1c30; font-family: Arial, sans-serif; }
        .subtitle { font-size: 11px; color: #555; font-family: Arial, sans-serif; }
        .header-box { background-color: #f8fafc; border: 1px solid #cbd5e1; font-family: Arial, sans-serif; font-size: 11px; }
        .tbl-header { background-color: #0052cc; color: #ffffff; font-weight: bold; font-family: Arial, sans-serif; font-size: 11px; text-align: center; height: 25px; }
        .tbl-data { font-family: Arial, sans-serif; font-size: 10.5px; vertical-align: middle; }
    </style>
</head>
<body>

    <!-- KOP / JUDUL UTAMA -->
    <table>
        <tr>
            <td colspan="10" class="title">PT APLIKANUSA LINTASARTA</td>
        </tr>
        <tr>
            <td colspan="10" class="subtitle">Laporan Rekapitulasi Hasil Patroli Lapangan OSP - Area Samarinda</td>
        </tr>
        <tr><td colspan="10"></td></tr>
    </table>

    <!-- KOTAK INFORMASI RINGKASAN -->
    <table border="1" cellspacing="0" cellpadding="4">
        <tr class="header-box">
            <td style="font-weight: bold; width: 130px;">Tanggal Patroli</td>
            <td colspan="4"><?php echo $formatted_tanggal; ?></td>
            <td style="font-weight: bold; width: 130px;">Fokus Patroli</td>
            <td colspan="4">Jalur Backbone & Patok Fiber Optik</td>
        </tr>
        <tr class="header-box">
            <td style="font-weight: bold;">Total Giat</td>
            <td colspan="4"><?php echo $total_giat; ?> Titik Pengamatan</td>
            <td style="font-weight: bold;">Wilayah Cakupan</td>
            <td colspan="4"><?php echo htmlspecialchars($wilayah_cakupan); ?></td>
        </tr>
        <tr class="header-box">
            <td style="font-weight: bold;">Waktu Operasional</td>
            <td colspan="4"><?php echo $waktu_operasional; ?></td>
            <td style="font-weight: bold;">Status Ringkasan</td>
            <td colspan="4" style="color: green; font-weight: bold;">Aman & Terkendali</td>
        </tr>
    </table>

    <br>

    <!-- TABEL UTAMA REKAPITULASI -->
    <table border="1" cellspacing="0" cellpadding="5">
        <thead>
            <tr class="tbl-header">
                <th>NO</th>
                <th>WAKTU (WITA)</th>
                <th>NIP TEKNISI</th>
                <th>NAMA TEKNISI</th>
                <th>LOKASI / AREA</th>
                <th>TITIK KOORDINAT (LAT, LONG)</th>
                <th>KONDISI FISIK</th>
                <th>POTENSI ANCAMAN</th>
                <th>CATATAN / HASIL LAPANGAN</th>
                <th>STATUS LAPORAN</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($data_laporan as $row) : 
            ?>
            <tr class="tbl-data">
                <td align="center" style="font-weight: bold;"><?php echo $no++; ?></td>
                <td align="center"><?php echo date('d-m-Y H:i', strtotime($row['tanggal'])); ?></td>
                <td align="center">='<?php echo $row['nip']; ?></td>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><b><?php echo htmlspecialchars($row['lokasi_area']); ?></b></td>
                <td><?php echo $row['latitude'] . ', ' . $row['longitude']; ?></td>
                <td align="center"><?php echo $row['kondisi_fisik']; ?></td>
                <td><?php echo $row['potensi_ancaman']; ?></td>
                <td><?php echo htmlspecialchars($row['catatan']); ?></td>
                <td align="center" style="font-weight: bold;"><?php echo $row['status_laporan']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br><br>
    <!-- TANDA TANGAN PENGESAHAN -->
    <table border="0">
        <tr>
            <td colspan="6" align="center">Dibuat Oleh,<br><br><br><br><b>( Tim Teknisi Lapangan OSP )</b></td>
            <td colspan="4" align="center">Mengetahui / Disetujui,<br><br><br><br><b>( Koordinator / Pimpinan Admin )</b></td>
        </tr>
    </table>

</body>
</html>