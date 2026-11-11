<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$id_laporan = intval($_GET['id'] ?? 0);

// Ambil data laporan berdasarkan ID dan pastikan milik user yang login
$query = mysqli_query($conn, "SELECT * FROM laporan_patroli WHERE id = $id_laporan AND username = '$username'");
if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: riwayat.php");
    exit();
}
$data = mysqli_fetch_assoc($query);

// Jika form disubmit untuk memperbarui data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lokasi_area = mysqli_real_escape_string($conn, $_POST['lokasi_area']);
    $potensi_ancaman = mysqli_real_escape_string($conn, $_POST['potensi_ancaman']);
    $kondisi_fisik = mysqli_real_escape_string($conn, $_POST['kondisi_fisik']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);
    
    // Status diubah kembali ke Menunggu Verifikasi setelah direvisi
    $status_laporan = 'Menunggu Verifikasi';

    // Handle upload foto baru jika ada
    $nama_foto = $data['foto'];
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $lokasi_file = $_FILES['foto']['tmp_name'];
        $tipe_file = $_FILES['foto']['type'];
        $nama_asli = $_FILES['foto']['name'];
        $ekstensi = strtolower(pathinfo($nama_asli, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ekstensi, $allowed_ext)) {
            $nama_foto = 'revisi_' . time() . '.' . $ekstensi;
            $tujuan = "../assets/img/uploads/" . $nama_foto;
            
            if (move_uploaded_file($lokasi_file, $tujuan)) {
                // Hapus foto lama jika ada
                if (!empty($data['foto']) && file_exists("../assets/img/uploads/" . $data['foto'])) {
                    unlink("../assets/img/uploads/" . $data['foto']);
                }
            }
        }
    }

    $update_query = "UPDATE laporan_patroli SET 
        lokasi_area = '$lokasi_area', 
        potensi_ancaman = '$potensi_ancaman', 
        kondisi_fisik = '$kondisi_fisik', 
        catatan = '$catatan', 
        foto = '$nama_foto', 
        status_laporan = '$status_laporan' 
        WHERE id = $id_laporan AND username = '$username'";

    if (mysqli_query($conn, $update_query)) {
        header("Location: riwayat.php?pesan=revisi_sukses");
        exit();
    } else {
        $error = "Gagal memperbarui laporan: " . mysqli_error($conn);
    }
}
$current_page = 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisi Laporan - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="riwayat.php" class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 hover:bg-slate-200 transition">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                    <p class="text-[10px] text-slate-400 font-medium">Form Revisi Laporan</p>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-md mx-auto p-4 space-y-4">
        <?php if (isset($error)): ?>
            <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-xl text-xs font-bold">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3.5">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Lokasi Area / Ruas Jalur</label>
                <input type="text" name="lokasi_area" value="<?php echo htmlspecialchars($data['lokasi_area']); ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#0052cc]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Potensi Ancaman</label>
                <select name="potensi_ancaman" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#0052cc]">
                    <?php 
                    $options = ['Pohon Rawan Tumbang', 'Aktivitas Galian / Konstruksi Warga', 'Kabel Kendur / Mendekati Tanah', 'Patok DATEK Hilang / Rusak', 'Lainnya'];
                    foreach($options as $opt) {
                        $selected = ($data['potensi_ancaman'] == $opt) ? 'selected' : '';
                        echo "<option value='$opt' $selected>$opt</option>";
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Dampak / Kondisi Fisik</label>
                <input type="text" name="kondisi_fisik" value="<?php echo htmlspecialchars($data['kondisi_fisik']); ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#0052cc]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Catatan Tambahan</label>
                <textarea name="catatan" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-[#0052cc]"><?php echo htmlspecialchars($data['catatan']); ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Ganti Foto Bukti (Opsional)</label>
                <?php if(!empty($data['foto'])): ?>
                    <div class="mb-2">
                        <img src="../assets/img/uploads/<?php echo $data['foto']; ?>" class="w-20 h-20 object-cover rounded-lg border">
                    </div>
                <?php endif; ?>
                <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#0052cc] hover:file:bg-blue-100">
            </div>

            <button type="submit" class="w-full bg-[#0052cc] hover:bg-blue-700 text-white font-bold py-3 rounded-xl text-xs transition shadow-md active:scale-95">
                Simpan & Kirim Ulang Revisi
            </button>
        </form>
    </main>

</body>
</html>