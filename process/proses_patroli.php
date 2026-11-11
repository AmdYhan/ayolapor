<?php
session_start();
include "../config/koneksi.php";

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../views/login.php");
    exit();
}

if (isset($_POST['submit_patroli'])) {
    // Ambil data teknisi dari session
    $username = $_SESSION['username'];
    $query_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($query_user);
    $id_teknisi = $user['id'];

    // Ambil input dari form
    $nomor_tiket      = "PAT-" . date("Ymd-His");
    $lokasi_area      = mysqli_real_escape_string($conn, $_POST['lokasi_area']);
    $latitude         = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude        = mysqli_real_escape_string($conn, $_POST['longitude']);
    $akurasi_gps      = isset($_POST['akurasi_gps']) ? (int)$_POST['akurasi_gps'] : 3;
    $kondisi_fisik    = mysqli_real_escape_string($conn, $_POST['condition']);
    
    // Format pilihan ancaman (array checkbox) menjadi string dipisah koma
    $potensi_ancaman  = isset($_POST['threat']) ? implode(", ", $_POST['threat']) : 'Tidak Ada';
    $catatan_teknisi  = mysqli_real_escape_string($conn, $_POST['notes']);
    $waktu_patroli    = date("d M Y • H:i") . " WITA";

    // Simpan ke database
    $query_insert = "INSERT INTO laporan_patroli 
        (id_teknisi, nomor_tiket, lokasi_area, latitude, longitude, akurasi_gps, kondisi_fisik, potensi_ancaman, catatan_teknisi, status_laporan, waktu_patroli) 
        VALUES 
        ('$id_teknisi', '$nomor_tiket', '$lokasi_area', '$latitude', '$longitude', '$akurasi_gps', '$kondisi_fisik', '$potensi_ancaman', '$catatan_teknisi', 'pending', '$waktu_patroli')";

    if (mysqli_query($conn, $query_insert)) {
        header("Location: ../views/form_patroli.php?status=success");
        exit();
    } else {
        header("Location: ../views/form_patroli.php?status=error");
        exit();
    }
} else {
    header("Location: ../views/form_patroli.php");
    exit();
}
?>