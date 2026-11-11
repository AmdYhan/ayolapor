<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../views/login.php");
    exit();
}

$username       = $_SESSION['username'];

// Ambil id user berdasarkan username yang login untuk mengisi kolom id_teknisi
$q_user = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");
$d_user = mysqli_fetch_assoc($q_user);
$id_teknisi = $d_user['id'] ?? null;

$lokasi_area    = mysqli_real_escape_string($conn, $_POST['lokasi_area']);
$latitude       = mysqli_real_escape_string($conn, $_POST['latitude']);
$longitude      = mysqli_real_escape_string($conn, $_POST['longitude']);
$akurasi_gps    = mysqli_real_escape_string($conn, $_POST['akurasi_gps']);
$kondisi_fisik  = mysqli_real_escape_string($conn, $_POST['kondisi_fisik']);
$potensi_ancaman= mysqli_real_escape_string($conn, $_POST['potensi_ancaman']);
$catatan        = mysqli_real_escape_string($conn, $_POST['catatan']);
$tanggal        = date('Y-m-d H:i:s');

// Deteksi file foto dari tombol kamera atau galeri
$foto_terpilih = null;
if (!empty($_FILES['foto_kamera']['name'])) {
    $foto_terpilih = $_FILES['foto_kamera'];
} elseif (!empty($_FILES['foto_galeri']['name'])) {
    $foto_terpilih = $_FILES['foto_galeri'];
}

if ($foto_terpilih && $foto_terpilih['error'] == 0) {
    $nama_file     = $foto_terpilih['name'];
    $tmp_file      = $foto_terpilih['tmp_name'];
    $ekstensi      = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    $nama_baru     = "patroli_" . time() . "." . $ekstensi;
    
    $folder_tujuan = "../assets/img/uploads/";

    // Buat folder uploads secara otomatis jika belum ada
    if (!is_dir($folder_tujuan)) {
        mkdir($folder_tujuan, 0777, true);
    }

    $path_file_tujuan = $folder_tujuan . $nama_baru;

    if (move_uploaded_file($tmp_file, $path_file_tujuan)) {
        // Simpan ke database dengan menyertakan id_teknisi dan username secara lengkap
        $query = "INSERT INTO laporan_patroli (id_teknisi, username, lokasi_area, latitude, longitude, akurasi_gps, kondisi_fisik, potensi_ancaman, catatan, status_laporan, foto, tanggal) VALUES 
          ('$id_teknisi', '$username', '$lokasi_area', '$latitude', '$longitude', '$akurasi_gps', '$kondisi_fisik', '$potensi_ancaman', '$catatan', 'Menunggu Verifikasi', '$nama_baru', '$tanggal')";
        
        if (mysqli_query($conn, $query)) {
            header("Location: ../views/beranda.php?status=sukses");
            exit();
        } else {
            echo "Gagal menyimpan ke database: " . mysqli_error($conn);
        }
    } else {
        echo "Gagal mengunggah foto ke direktori server.";
    }
} else {
    echo "Harap sertakan foto bukti lapangan (melalui kamera atau galeri).";
}
?>