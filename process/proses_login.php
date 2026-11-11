<?php
session_start();
include "../config/koneksi.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Ambil data user dari database
    $query = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username' AND password = '$password'");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        // Simpan session dasar
        $_SESSION['id_user']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nip']      = $user['nip'];
        
        // UBAH SEMUA ROLE MENJADI HURUF KECIL (Mengatasi 'Admin', 'ADMIN', atau 'admin')
        $role = strtolower(trim($user['role']));
        $_SESSION['role'] = $role;
        
        // ANALISIS REDIREKSI BERDASARKAN ROLE
        if ($role === 'admin' || $role === 'koordinator' || $role === 'engginer' || $role === 'pimpinan') {
            // Jika rolenya termasuk admin/pimpinan, lempar ke dashboard admin
            header("Location: ../views/dashboard_admin.php");
            exit();
        } else {
            // Jika teknisi biasa, lempar ke beranda teknisi
            header("Location: ../views/beranda.php");
            exit();
        }
        
    } else {
        // Jika gagal login
        echo "<script>
                alert('Username atau Password salah!');
                window.location.href='../views/login.php';
              </script>";
        exit();
    }
} else {
    header("Location: ../views/login.php");
    exit();
}
?>