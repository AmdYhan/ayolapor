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

$query_peta = mysqli_query($conn, "SELECT l.*, u.nip FROM laporan_patroli l LEFT JOIN users u ON l.username = u.username WHERE l.latitude != '' AND l.longitude != '' ORDER BY l.tanggal DESC");

$markers = [];
$latest_report = null;
while ($row = mysqli_fetch_assoc($query_peta)) {
    if (is_numeric($row['latitude']) && is_numeric($row['longitude'])) {
        $markers[] = $row;
        if (!$latest_report) {
            $latest_report = $row;
        }
    }
}

$q_kritis = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Kritis%' OR kondisi_fisik LIKE '%Rusak%'"))['total'] ?? 0;
$q_waspada = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE potensi_ancaman LIKE '%Pohon%' OR potensi_ancaman LIKE '%Ancaman%'"))['total'] ?? 0;
$q_normal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan_patroli WHERE status_laporan = 'Disetujui' OR kondisi_fisik = 'Baik'"))['total'] ?? 0;
$total_tkp = count($markers);

$current_page = 'peta';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIS Patroli Fiber Samarinda - Lintasarta</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        #map { height: 320px; width: 100%; border-radius: 1rem; z-index: 1; transition: all 0.3s ease; }
        
        /* Sembunyikan Watermark / Atribusi Leaflet & Esri */
        .leaflet-control-attribution {
            display: none !important;
        }

        /* Pindahkan posisi tombol zoom (+/-) bawaan Leaflet ke bawah pojok kanan dengan margin aman */
        .leaflet-bottom.leaflet-right {
            bottom: 10px !important;
            right: 10px !important;
        }

        /* CSS untuk mode Full Screen */
        .fullscreen-map {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 99999 !important;
            border-radius: 0 !important;
            padding: 0 !important;
            background: white;
        }
        .fullscreen-map #map {
            height: 100vh !important;
            border-radius: 0 !important;
        }
    </style>
</head>
<body class="text-[#0d1c30] pb-28">

    <!-- HEADER TOP -->
    <header class="bg-white border-b border-slate-100 sticky top-0 z-30 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div>
                <h1 class="font-extrabold text-sm text-[#0d1c30] leading-tight">SAMARINDA OPS</h1>
                <p class="text-[10px] text-slate-400 font-medium">Peta Jalur</p>
            </div>
            <a href="profil_admin.php" class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                <span class="material-symbols-outlined text-[20px]">person</span>
            </a>
        </div>
    </header>

    <main id="mainContainer" class="max-w-md mx-auto p-4 space-y-4">
        
        <!-- TITLE GIS & FILTER BADGE -->
        <div class="bg-white p-3.5 rounded-2xl border border-slate-100 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-[#0052cc]/10 text-[#0052cc] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">satellite_alt</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h2 class="font-extrabold text-sm text-[#0d1c30]">GIS Patroli Fiber Samarinda</h2>
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>
                        <p class="text-[10px] text-slate-400 font-medium">Pusat Kendali OSP Koridor Mahakam</p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2 overflow-x-auto pb-0.5 text-xs">
                <span class="bg-[#0052cc] text-white px-3 py-1.5 rounded-xl font-bold whitespace-nowrap shadow-sm">Semua (<?php echo $total_tkp; ?>)</span>
                <span class="bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Kritis (<?php echo $q_kritis; ?>)</span>
                <span class="bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded-xl font-semibold whitespace-nowrap flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Waspada (<?php echo $q_waspada; ?>)</span>
            </div>
        </div>

        <!-- CONTAINER PETA INTERAKTIF -->
        <div id="mapWrapper" class="bg-white p-3 rounded-3xl border border-slate-100 shadow-sm relative transition-all duration-300 space-y-2.5">
            
            <!-- KONTROL ATAS PETA (Dibuat Aman & Responsif Agar Tidak Keluar Border) -->
            <div class="flex flex-wrap items-center justify-between gap-1.5 z-[1000] relative px-0.5">
                <!-- Koordinat GPS Center -->
                <div class="bg-white/95 backdrop-blur-md border border-slate-200 px-2.5 py-1 rounded-xl shadow-sm text-[10px] font-bold text-slate-700 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[12px] text-[#0052cc]">my_location</span> -0.5021, 117.1536
                </div>

                <!-- Tombol Kanan Atas (OSP, Heatmap, Tema & Fullscreen) -->
                <div class="flex flex-wrap items-center gap-1">
                    <span class="bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 text-[10px] font-bold px-2 py-1 rounded-xl shadow-sm flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[12px]">network_node</span> OSP
                    </span>
                    <span class="bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 text-[10px] font-bold px-2 py-1 rounded-xl shadow-sm flex items-center gap-0.5">
                        <span class="material-symbols-outlined text-[12px]">heatmap</span> Heatmap
                    </span>
                    
                    <!-- Dropdown Tema -->
                    <select id="mapThemeSelect" onchange="ubahTemaPeta(this.value)" class="bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 text-[10px] font-bold px-2 py-1 rounded-xl shadow-sm focus:outline-none cursor-pointer">
                        <option value="satelit">🛰️ Satelit</option>
                        <option value="standard">🗺️ Standar</option>
                        <option value="topo">⛰️ Topo</option>
                    </select>

                    <!-- Tombol Full Screen -->
                    <button onclick="toggleFullScreen()" class="w-7 h-7 bg-white/95 backdrop-blur-md border border-slate-200 text-slate-700 rounded-xl shadow-sm flex items-center justify-center hover:bg-slate-100 transition cursor-pointer" title="Layar Penuh">
                        <span id="fsIcon" class="material-symbols-outlined text-[14px]">fullscreen</span>
                    </button>
                </div>
            </div>

            <div id="map"></div>

            <!-- Floating Badge Koridor di Peta Bawah -->
            <div class="absolute bottom-5 left-5 z-[1000] bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-xl shadow border border-slate-200 flex items-center gap-1.5 text-[11px] font-bold text-slate-700">
                <span class="w-2 h-2 rounded-full bg-[#0052cc]"></span> Koridor FO: Palaran - Seberang
            </div>
        </div>

        <!-- KARTU DETAIL LAPORAN TERBARU -->
        <?php if ($latest_report) : ?>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3 relative">
            <div class="flex justify-between items-center">
                <span class="bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-extrabold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> <?php echo htmlspecialchars($latest_report['potensi_ancaman'] ?: 'KRITIS (Rawan Putus)'); ?>
                </span>
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold text-slate-400">#PAT-2026-<?php echo str_pad($latest_report['id'], 3, '0', STR_PAD_LEFT); ?></span>
                    <button class="text-slate-400 hover:text-[#0052cc]"><span class="material-symbols-outlined text-[18px]">bookmark</span></button>
                </div>
            </div>

            <div>
                <h3 class="font-extrabold text-sm text-[#0d1c30] leading-snug"><?php echo htmlspecialchars($latest_report['lokasi_area']); ?></h3>
                <p class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-rose-500">location_on</span>
                    <span><?php echo htmlspecialchars($latest_report['lokasi_area']); ?> (<?php echo $latest_report['latitude']; ?>, <?php echo $latest_report['longitude']; ?>)</span>
                </p>
            </div>

            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 grid grid-cols-2 gap-3 text-[11px] items-center">
                <?php 
                $foto_path = "../assets/img/uploads/" . $latest_report['foto'];
                if (!empty($latest_report['foto']) && file_exists($foto_path)) :
                ?>
                <div>
                    <div class="relative">
                        <img src="<?php echo $foto_path; ?>" class="w-full h-24 object-cover rounded-lg border">
                        <span class="absolute bottom-1 left-1 bg-black/60 text-white text-[9px] px-1.5 py-0.5 rounded font-bold">Bukti</span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="space-y-1">
                    <p class="text-slate-500">Pelapor: <b class="text-slate-800"><?php echo htmlspecialchars($latest_report['username']); ?> (NIP-<?php echo htmlspecialchars($latest_report['nip'] ?? '44821'); ?>)</b></p>
                    <p class="text-slate-500">Waktu Temuan: <b class="text-slate-800"><?php echo date('d M, H:i', strtotime($latest_report['tanggal'])); ?> WITA</b></p>
                    <p class="text-slate-500">Dampak FO: <b class="text-rose-600 font-bold"><?php echo htmlspecialchars($latest_report['kondisi_fisik'] ?: 'Tegangan 48 Core Main Span'); ?></b></p>
                </div>
            </div>

            <a href="detail_laporan_admin.php?id=<?php echo $latest_report['id']; ?>" class="w-full bg-[#0052cc]/10 hover:bg-[#0052cc]/20 text-[#0052cc] font-bold py-2.5 rounded-xl text-xs flex items-center justify-center gap-1.5 transition">
                <span class="material-symbols-outlined text-[18px]">visibility</span> Detail Tiket
            </a>
        </div>
        <?php endif; ?>

        <!-- RINGKASAN STATUS TITIK TKP -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 space-y-3">
            <div class="flex justify-between items-center">
                <h3 class="font-bold text-xs text-slate-700">RINGKASAN STATUS <?php echo $total_tkp; ?> TITIK TKP</h3>
                <a href="laporan_admin.php" class="text-[11px] font-bold text-[#0052cc] hover:underline flex items-center gap-0.5">
                    Audit SIKAP OSP <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                </a>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                    <div class="flex items-center justify-center gap-1 text-[11px] font-bold text-rose-600 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> 0<?php echo $q_kritis; ?>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium">Kritis Rawan</p>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                    <div class="flex items-center justify-center gap-1 text-[11px] font-bold text-amber-600 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> 0<?php echo $q_waspada; ?>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium">Waspada Rimbun</p>
                </div>
                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-center">
                    <div class="flex items-center justify-center gap-1 text-[11px] font-bold text-emerald-600 mb-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 0<?php echo $q_normal; ?>
                    </div>
                    <p class="text-[10px] text-slate-500 font-medium">Normal Selesai</p>
                </div>
            </div>
        </div>

    </main>

    <!-- BOTTOM NAVBAR ADMIN -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-md border-t border-slate-200 py-2.5 px-6 flex justify-around items-center z-30 max-w-md mx-auto">
        <a href="dashboard_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">grid_view</span><span class="text-[10px] mt-0.5">Beranda</span></a>
        <a href="pemantauan_admin.php" class="flex flex-col items-center text-[#0052cc] font-bold"><span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">map</span><span class="text-[10px] mt-0.5">Peta Jalur</span></a>
        <a href="laporan_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">assignment</span><span class="text-[10px] mt-0.5">Laporan</span></a>
        <a href="profil_admin.php" class="flex flex-col items-center text-slate-400"><span class="material-symbols-outlined text-[22px]">person</span><span class="text-[10px] mt-0.5">Profil</span></a>
    </nav>

    <!-- SCRIPT PETA LEAFLET -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var defaultLat = -0.5021;
            var defaultLng = 117.1536;

            var map = L.map('map', {
                zoomControl: false
            }).setView([defaultLat, defaultLng], 13);

            L.control.zoom({
                position: 'bottomright'
            }).addTo(map);

            var layerSatelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19
            });

            var layerStandar = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            });

            var layerTopo = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                maxZoom: 17
            });

            layerSatelit.addTo(map);

            window.ubahTemaPeta = function(tema) {
                map.eachLayer(function (layer) {
                    if (layer instanceof L.TileLayer) {
                        map.removeLayer(layer);
                    }
                });

                if (tema === 'standard') {
                    layerStandar.addTo(map);
                } else if (tema === 'topo') {
                    layerTopo.addTo(map);
                } else {
                    layerSatelit.addTo(map);
                }
            };

            window.toggleFullScreen = function() {
                var wrapper = document.getElementById('mapWrapper');
                var icon = document.getElementById('fsIcon');
                
                wrapper.classList.toggle('fullscreen-map');
                
                if (wrapper.classList.contains('fullscreen-map')) {
                    icon.innerText = 'fullscreen_exit';
                } else {
                    icon.innerText = 'fullscreen';
                }
                setTimeout(function() {
                    map.invalidateSize();
                }, 100);
            };

            var markersData = <?php echo json_encode($markers); ?>;
            var bounds = [];

            if (markersData && markersData.length > 0) {
                markersData.forEach(function(item) {
                    var lat = parseFloat(item.latitude);
                    var lng = parseFloat(item.longitude);
                    
                    if (!isNaN(lat) && !isNaN(lng)) {
                        bounds.push([lat, lng]);

                        var popupHtml = `
                            <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11px; line-height: 1.4;">
                                <b style="color: #0052cc; font-size: 12px;">${item.lokasi_area}</b><br>
                                Teknisi: ${item.username}<br>
                                Kondisi: ${item.kondisi_fisik}<br>
                                Status: <b style="color: green;">${item.status_laporan}</b>
                            </div>
                        `;

                        L.marker([lat, lng]).addons = true;
                        L.marker([lat, lng]).addTo(map).bindPopup(popupHtml);
                    }
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            }

            setTimeout(function() {
                map.invalidateSize();
            }, 200);
        });
    </script>
</body>
</html>