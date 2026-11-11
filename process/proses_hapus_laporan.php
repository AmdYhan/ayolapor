<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: ../views/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_laporan = intval($_GET['id']);
    $username = $_SESSION['username'];

    // Ambil data laporan untuk memastikan milik user yang bersangkutan & mendapatkan nama foto
    $query_cek = mysqli_query($conn, "SELECT * FROM laporan_patroli WHERE id = $id_laporan AND username = '$username'");
    
    if ($query_cek && mysqli_num_rows($query_cek) > 0) {
        $data = mysqli_fetch_assoc($query_cek);
        $foto = $data['foto'];

        // Hapus file fisik foto jika ada di folder uploads
        if (!empty($foto)) {
            $path_foto = "../assets/img/uploads/" . $foto;
            if (file_exists($path_foto)) {
                unlink($path_foto);
            }
        }

        // Hapus data dari database
        $query_hapus = mysqli_query($conn, "DELETE FROM laporan_patroli WHERE id = $id_laporan");

        if ($query_hapus) {
            header("Location: ../views/riwayat.php?pesan=hapus_sukses");
            exit();
        } else {
            echo "Gagal menghapus data dari database: " . mysqli_error($conn);
        }
    } else {
        echo "Laporan tidak ditemukan atau Anda tidak memiliki hak akses untuk menghapusnya.";
    }
} else {
    header("Location: ../views/riwayat.php");
    exit();
}
?>