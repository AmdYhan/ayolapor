<?php
session_start(); // Memulai sesi yang sedang aktif

// Membersihkan seluruh variabel sesi
$_SESSION = array();

// Menghapus cookie sesi jika ada (opsional untuk keamanan tambahan)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Menghancurkan sesi di server
session_destroy();

// Mengarahkan kembali pengguna ke halaman login di dalam folder views
header("Location: ../views/login.php");
exit();
?>