<?php
session_start();
include "../config/koneksi.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$query_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query_user);

$current_page = 'patroli';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Patroli - Lintasarta Samarinda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-[#0d1c30] pb-24">

    <!-- HEADER -->
   <header class="bg-white/95 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-30 px-4 py-4 shadow-xs">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="beranda.php" class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[#0d1c30] active:scale-95 transition">
                    <span class="material-symbols-outlined text-[22px]">arrow_back</span>
                </a>
                <div>
                    <h1 class="font-extrabold text-base tracking-wide text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                    <p class="text-xs text-slate-500 font-semibold tracking-wide">Form Patroli OSP</p>
                </div>
            </div>
            
            <a href="profil.php" class="w-10 h-10 rounded-full bg-blue-50 border border-blue-100 flex items-center justify-center text-[#0052cc] shadow-xs active:scale-95 transition">
                <span class="material-symbols-outlined text-[22px]">person</span>
            </a>
        </div>
    </header>

    <!-- KONTEN UTAMA FORM -->
    <main class="max-w-md mx-auto p-4 space-y-4">
        
        <!-- Action diarahkan ke folder process/ -->
        <form action="../process/proses_tambah_patroli.php" method="POST" enctype="multipart/form-data" class="space-y-4">
            
            <!-- Lokasi / Segment Patroli dengan Format Alamat Lengkap -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-extrabold text-[#0d1c30]">Lokasi / Area Patroli *</label>
                    <span class="text-[10px] text-slate-400 font-medium">Otomatis dari GPS / Edit Manual</span>
                </div>
                <div class="relative">
                    <textarea name="lokasi_area" id="lokasi_area" rows="3" required placeholder="Contoh: Jl. Houling Batubara, Jawa, Kec. Sanga Sanga, Kabupaten Kutai Kartanegara, Kalimantan Timur 75265" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] transition"></textarea>
                </div>
            </div>

            <!-- KOORDINAT REALTIME GNSS / GPS -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 relative">
                            <span class="material-symbols-outlined text-[22px]">satellite_alt</span>
                            <span id="gps-indicator" class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white animate-pulse"></span>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-slate-400">Koordinat Realtime GNSS</p>
                            <h3 id="koordinat-text" class="font-extrabold text-sm text-[#0d1c30] font-mono">--.-----, ---.-----</h3>
                        </div>
                    </div>
                    
                    <button type="button" onclick="aktifkanGPS()" class="px-3.5 py-2 bg-blue-50 text-[#0052cc] border border-blue-100 rounded-xl text-xs font-bold flex items-center gap-1.5 active:scale-95 transition shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">sync</span> Perbarui
                    </button>
                </div>

                <div class="bg-slate-50/80 p-3 rounded-xl border border-slate-100 space-y-2">
                    <div class="flex items-center gap-1.5 text-xs font-bold text-[#0d1c30]">
                        <span class="material-symbols-outlined text-[16px] text-[#0052cc]">location_on</span>
                        <span id="lokasi-detil-text">Menunggu deteksi lokasi GPS...</span>
                    </div>
                    
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-[11px] text-slate-500">
                        <span id="waktu-gps-text" class="font-medium">--:-- WITA</span>
                        <span id="akurasi-badge" class="px-2.5 py-0.5 bg-blue-50 text-[#0052cc] font-bold rounded-full border border-blue-100">Akurasi --</span>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="latitude" required>
                <input type="hidden" name="longitude" id="longitude" required>
                <input type="hidden" name="akurasi_gps" id="akurasi_gps" required>
            </div>
            
            <!-- KONDISI FISIK JALUR -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-extrabold text-[#0d1c30]">Kondisi Fisik Jalur *</label>
                    <span id="badge-kondisi" class="text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-slate-100 text-slate-500">Pilih Status</span>
                </div>
                <p class="text-[11px] text-slate-500">Pilih status inspeksi tiang/kabel di lokasi hari ini</p>
                
                <input type="hidden" name="kondisi_fisik" id="input_kondisi" required>

                <div class="grid grid-cols-3 gap-2 pt-1">
                    <button type="button" onclick="pilihKondisi('Baik')" id="btn-Baik" class="py-2.5 px-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 flex items-center justify-center gap-1 transition hover:bg-slate-100">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600">check_circle</span> Baik
                    </button>
                    <button type="button" onclick="pilihKondisi('Waspada')" id="btn-Waspada" class="py-2.5 px-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 flex items-center justify-center gap-1 transition hover:bg-slate-100">
                        <span class="material-symbols-outlined text-[16px] text-amber-500">warning</span> Waspada
                    </button>
                    <button type="button" onclick="pilihKondisi('Rawan')" id="btn-Rawan" class="py-2.5 px-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 flex items-center justify-center gap-1 transition hover:bg-slate-100">
                        <span class="material-symbols-outlined text-[16px] text-rose-600">error</span> Rawan
                    </button>
                </div>
            </div>

            <!-- POTENSI ANCAMAN / JENIS KENDALA -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2">
                <label class="block text-xs font-extrabold text-[#0d1c30]">Potensi Ancaman / Jenis Kendala</label>
                <select name="potensi_ancaman" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] transition">
                    <option value="">-- Pilih Potensi Ancaman (Opsional) --</option>
                    <option value="Pohon Rawan Tumbang">Pohon Rawan Tumbang</option>
                    <option value="Aktivitas Galian / Konstruksi Warga">Aktivitas Galian / Konstruksi Warga</option>
                    <option value="Kabel Kendur / Mendekati Tanah">Kabel Kendur / Mendekati Tanah</option>
                    <option value="Patok DATEK Hilang / Rusak">Patok DATEK Hilang / Rusak</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>

            <!-- CATATAN TAMBAHAN -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-2">
                <label class="block text-xs font-extrabold text-[#0d1c30]">Catatan Tambahan / Temuan Lapangan</label>
                <textarea name="catatan" rows="3" placeholder="Tuliskan detail temuan, kendala, atau kondisi patok DATEK..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-[#0d1c30] focus:outline-none focus:border-[#0052cc] transition"></textarea>
            </div>

            <!-- FOTO BUKTI LAPANGAN -->
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
                <label class="block text-xs font-extrabold text-[#0d1c30]">Foto Bukti Lapangan *</label>
                
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer bg-blue-50 hover:bg-blue-100 text-[#0052cc] border border-blue-200 py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-1.5 transition active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">photo_camera</span>
                        Buka Kamera
                        <input type="file" name="foto_kamera" id="foto_kamera" accept="image/*" capture="environment" class="hidden" onchange="previewImage(event, 'kamera')">
                    </label>

                    <label class="cursor-pointer bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 py-2.5 px-3 rounded-xl text-xs font-extrabold flex items-center justify-center gap-1.5 transition active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">photo_library</span>
                        Pilih Galeri
                        <input type="file" name="foto_galeri" id="foto_galeri" accept="image/*" class="hidden" onchange="previewImage(event, 'galeri')">
                    </label>
                </div>

                <div id="upload-placeholder" class="border-2 border-dashed border-slate-200 rounded-xl p-3 text-center bg-slate-50/50">
                    <p class="text-[11px] text-slate-400 font-medium">Belum ada foto yang dipilih atau diambil.</p>
                </div>

                <div id="image-preview-container" class="hidden relative mt-2 space-y-2 text-center">
                    <img id="preview-img" src="#" alt="Preview Foto" class="mx-auto h-36 object-cover rounded-xl border border-slate-200 shadow-sm">
                    <p id="file-name" class="text-[11px] font-semibold text-slate-600 truncate"></p>
                    <span class="inline-block text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">✓ Foto Siap Diunggah</span>
                </div>
            </div>

            <!-- Tombol Kirim -->
            <button type="submit" id="btn-submit" class="w-full py-3.5 bg-[#0052cc] text-white font-extrabold text-xs rounded-2xl shadow-md active:scale-95 transition flex items-center justify-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">send</span>
                Kirim Laporan Patroli
            </button>

        </form>

    </main>

    <!-- SCRIPT JAVASCRIPT -->
    <script>
        let watchId = null;

        function aktifkanGPS() {
            const koordinatText = document.getElementById('koordinat-text');
            const lokasiDetilText = document.getElementById('lokasi-detil-text');
            const waktuGpsText = document.getElementById('waktu-gps-text');
            const akurasiBadge = document.getElementById('akurasi-badge');
            const gpsIndicator = document.getElementById('gps-indicator');
            
            if (!navigator.geolocation) {
                lokasiDetilText.textContent = "Geolocation tidak didukung oleh browser ini.";
                return;
            }

            lokasiDetilText.textContent = "Mengunci sinyal satelit GNSS (Mohon tunggu)...";
            gpsIndicator.className = "absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white animate-ping";

            const options = {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            };

            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
            }

            let bestPosition = null;

            watchId = navigator.geolocation.watchPosition(function(position) {
                const akurasi = position.coords.accuracy;

                if (!bestPosition || akurasi < bestPosition.coords.accuracy) {
                    bestPosition = position;
                }

                if (akurasi <= 25 || bestPosition.coords.accuracy <= 15) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                    prosesTitikGPS(bestPosition);
                }
            }, function(error) {
                let pesan = "Gagal mendeteksi GPS.";
                if (error.code === error.PERMISSION_DENIED) {
                    pesan = "Izin lokasi ditolak pada browser.";
                } else if (error.code === error.TIMEOUT) {
                    if (bestPosition) {
                        prosesTitikGPS(bestPosition);
                        return;
                    }
                    pesan = "Waktu habis. Pastikan berada di area terbuka.";
                }
                lokasiDetilText.textContent = pesan;
                gpsIndicator.className = "absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white";
            }, options);

            setTimeout(() => {
                if (watchId !== null) {
                    navigator.geolocation.clearWatch(watchId);
                    watchId = null;
                    if (bestPosition) {
                        prosesTitikGPS(bestPosition);
                    } else {
                        lokasiDetilText.textContent = "Gagal mengunci satelit. Coba 'Perbarui'.";
                        gpsIndicator.className = "absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white";
                    }
                }
            }, 8000);
        }

        function prosesTitikGPS(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            const akurasiMeter = Math.round(position.coords.accuracy);
            
            const koordinatText = document.getElementById('koordinat-text');
            const lokasiDetilText = document.getElementById('lokasi-detil-text');
            const waktuGpsText = document.getElementById('waktu-gps-text');
            const akurasiBadge = document.getElementById('akurasi-badge');
            const gpsIndicator = document.getElementById('gps-indicator');
            const inputLokasiArea = document.getElementById('lokasi_area');

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lon;
            document.getElementById('akurasi_gps').value = akurasiMeter;
            
            koordinatText.textContent = lat.toFixed(5) + ", " + lon.toFixed(5);
            
            const sekarang = new Date();
            const jam = String(sekarang.getHours()).padStart(2, '0');
            const menit = String(sekarang.getMinutes()).padStart(2, '0');
            waktuGpsText.textContent = jam + ":" + menit + " WITA";
            
            if (akurasiMeter > 50) {
                akurasiBadge.className = "px-2.5 py-0.5 bg-amber-50 text-amber-600 font-bold rounded-full border border-amber-200";
                akurasiBadge.textContent = "Akurasi Lemah (±" + akurasiMeter + "m)";
                lokasiDetilText.textContent = "⚠️ Akurasi rendah. Mohon pindah ke area terbuka.";
                gpsIndicator.className = "absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-amber-500 ring-2 ring-white";
            } else {
                akurasiBadge.className = "px-2.5 py-0.5 bg-emerald-50 text-emerald-600 font-bold rounded-full border border-emerald-200";
                akurasiBadge.textContent = "Akurasi Tinggi (±" + akurasiMeter + "m)";
                gpsIndicator.className = "absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white";
            }

            // Reverse Geocoding OpenStreetMap untuk merangkai format alamat lengkap mitra
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`, {
                headers: { 'Accept-Language': 'id' }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.address) {
                    const addr = data.address;
                    
                    let jalan = addr.road || addr.suburb || addr.hamlet || '';
                    let desaKel = addr.village || addr.town || addr.city_district || addr.suburb || '';
                    let kecamatan = addr.county || addr.state_district || addr.municipality || '';
                    let kabupaten = addr.city || addr.county || '';
                    let provinsi = addr.state || '';
                    let kodepos = addr.postcode || '';

                    // Menyusun format teks lokasi selaras dengan standar mitra
                    let alamatLengkap = `${jalan ? jalan + ', ' : ''}${desaKel ? desaKel + ', ' : ''}${kecamatan ? 'Kec. ' + kecamatan + ', ' : ''}${kabupaten ? 'Kabupaten ' + kabupaten + ', ' : ''}${provinsi} ${kodepos}`.trim();
                    
                    inputLokasiArea.value = alamatLengkap;
                    lokasiDetilText.textContent = "Auto Geocoding Berhasil";
                } else {
                    lokasiDetilText.textContent = "Auto: Koordinat " + lat.toFixed(4) + ", " + lon.toFixed(4);
                }
            })
            .catch(() => {
                lokasiDetilText.textContent = "Auto: Lat: " + lat.toFixed(4) + ", Lon: " + lon.toFixed(4);
            });
        }

        window.onload = function() {
            aktifkanGPS();
        };

        function pilihKondisi(nilai) {
            document.getElementById('input_kondisi').value = nilai;
            ['Baik', 'Waspada', 'Rawan'].forEach(function(kondisi) {
                const btn = document.getElementById('btn-' + kondisi);
                btn.className = "py-2.5 px-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-600 flex items-center justify-center gap-1 transition hover:bg-slate-100";
            });

            const btnAktif = document.getElementById('btn-' + nilai);
            const badge = document.getElementById('badge-kondisi');
            
            if(nilai === 'Baik') {
                btnAktif.className = "py-2.5 px-2 bg-emerald-50 border border-emerald-300 rounded-xl text-xs font-bold text-emerald-700 flex items-center justify-center gap-1 transition shadow-sm";
                badge.className = "text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-600 border border-emerald-200";
            } else if(nilai === 'Waspada') {
                btnAktif.className = "py-2.5 px-2 bg-amber-50 border border-amber-300 rounded-xl text-xs font-bold text-amber-700 flex items-center justify-center gap-1 transition shadow-sm";
                badge.className = "text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-amber-50 text-amber-600 border border-amber-200";
            } else if(nilai === 'Rawan') {
                btnAktif.className = "py-2.5 px-2 bg-rose-50 border border-rose-300 rounded-xl text-xs font-bold text-rose-700 flex items-center justify-center gap-1 transition shadow-sm";
                badge.className = "text-[10px] px-2.5 py-0.5 rounded-full font-bold bg-rose-50 text-rose-600 border border-rose-200";
            }
            badge.textContent = nilai;
        }

        function previewImage(event, sumber) {
            const fileInput = event.target;
            const placeholder = document.getElementById('upload-placeholder');
            const previewContainer = document.getElementById('image-preview-container');
            const previewImg = document.getElementById('preview-img');
            const fileNameText = document.getElementById('file-name');

            if (sumber === 'kamera') {
                document.getElementById('foto_galeri').value = '';
            } else {
                document.getElementById('foto_kamera').value = '';
            }

            if (fileInput.files && fileInput.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    fileNameText.textContent = fileInput.files[0].name;
                    placeholder.classList.add('hidden');
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(fileInput.files[0]);
            }
        }
    </script>

    <!-- BOTTOM NAVBAR 4 MENU -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 py-2 px-6 flex justify-around items-center z-30 max-w-md mx-auto">
        <a href="beranda.php" class="flex flex-col items-center <?php echo ($current_page == 'beranda') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'beranda') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>grid_view</span>
            <span class="text-[10px] mt-0.5">Beranda</span>
        </a>
        <a href="form_patroli.php" class="flex flex-col items-center <?php echo ($current_page == 'patroli') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'patroli') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>assignment</span>
            <span class="text-[10px] mt-0.5">Patroli</span>
        </a>
        <a href="riwayat.php" class="flex flex-col items-center <?php echo ($current_page == 'riwayat') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'riwayat') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>history</span>
            <span class="text-[10px] mt-0.5">Riwayat</span>
        </a>
        <a href="profil.php" class="flex flex-col items-center <?php echo ($current_page == 'profil') ? 'text-[#0052cc] font-bold' : 'text-[#5E6C84]'; ?>">
            <span class="material-symbols-outlined text-[22px]" <?php echo ($current_page == 'profil') ? 'style="font-variation-settings: \'FILL\' 1;"' : ''; ?>>person</span>
            <span class="text-[10px] mt-0.5">Profil</span>
        </a>
    </nav>

</body>
</html>