<!-- Leaflet CSS Local -->
<link rel="stylesheet" href="<?= base_url('assets/siteplan/leaflet/leaflet.css') ?>" />
<link rel="stylesheet" href="<?= base_url('assets/siteplan/leaflet/leaflet.fullscreen.css') ?>" />

<?php
// Role check: hanya admin yang bisa edit/auto-plot/drag/reset
$isAdmin = isset($userData['role']) && $userData['role'] == 'admin';
$isKoor = isset($userData['role']) && $userData['role'] == 'koordinator';
$koorId = $isKoor ? $userData['id'] : '';
?>

<style>
    /* Styling Peta & Komponen Siteplan */
    .siteplan-card {
        border-radius: 14px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        overflow: hidden;
    }

    #siteplan-map {
        height: 720px;
        width: 100%;
        background: #f8fafc;
        border-radius: 0 0 14px 14px;
        z-index: 1;
    }

    .leaflet-interactive {
        cursor: pointer !important;
    }

    /* Floating Legend */
    .map-legend {
        position: absolute;
        bottom: 25px;
        left: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        padding: 12px 18px;
        border-radius: 10px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        font-size: 13px;
        border: 1px solid #cbd5e1;
        pointer-events: auto;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }
    .legend-item:last-child {
        margin-bottom: 0;
    }
    .legend-box {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid;
    }
    .box-menunggak {
        background: rgba(239, 68, 68, 0.6);
        border-color: #b91c1c;
    }
    .box-lunas {
        background: rgba(34, 197, 94, 0.5);
        border-color: #15803d;
    }
    .box-unplotted {
        background: rgba(148, 163, 184, 0.4);
        border-color: #64748b;
    }

    /* Floating Tool Panel untuk Plotting */
    .plotting-toolbar {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        padding: 15px 18px;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.18);
        border: 1px solid #cbd5e1;
        max-width: 340px;
        width: 100%;
        display: none;
    }

    /* Popup Leaflet Custom */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        padding: 0;
        overflow: hidden;
    }
    .leaflet-popup-content {
        margin: 0 !important;
        font-family: inherit;
        line-height: 1.4;
    }
    .popup-header-menunggak {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        padding: 12px 16px;
    }
    .popup-header-lunas {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: white;
        padding: 12px 16px;
    }
    .popup-body {
        padding: 14px 16px;
        background: #ffffff;
    }

    /* Stat Cards */
    .kpi-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }

    /* Resize handle untuk mode geser */
    .resize-handle-icon {
        background: #2563eb !important;
        border: 2px solid #ffffff !important;
        border-radius: 3px !important;
        width: 14px !important;
        height: 14px !important;
        margin-left: -7px !important;
        margin-top: -7px !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.6) !important;
        cursor: grab !important;
        z-index: 9999 !important;
        pointer-events: auto !important;
        transition: transform 0.1s ease, background 0.1s ease;
    }
    .resize-handle-icon:hover {
        background: #f59e0b !important;
        transform: scale(1.35);
    }
    .resize-handle-icon:active {
        cursor: grabbing !important;
    }
</style>

<div class="row mb-3">
    <div class="col-12">
        <div class="card bg-light-primary text-primary mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="round-48 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                            <i class="ti ti-map-2 fs-7"></i>
                        </div>
                        <div>
                            <h4 class="card-title fw-semibold mb-1 text-primary">Siteplan Interaktif Perumahan TSI</h4>
                            <p class="card-subtitle mb-0 text-muted">Integrasi Peta Kavling dengan Status Pembayaran IPL Warga (Merah = Menunggak, Hijau = Lunas)</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <?php if ($isAdmin): ?>
                        <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1" id="btn-auto-plot" onclick="autoGeneratePolygons()" title="Plot semua kavling rumah secara otomatis dan presisi">
                            <i class="ti ti-wand"></i>
                            <span>Auto-Plot Otomatis</span>
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" id="btn-toggle-red" onclick="toggleRedBoxes()" title="Tampilkan atau sembunyikan kotak merah menunggak">
                            <i class="ti ti-eye-off" id="icon-red-boxes"></i>
                            <span id="text-red-boxes">Hide Merah</span>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm d-flex align-items-center gap-1" onclick="downloadSiteplanPdf()" title="Download PDF siteplan sesuai filter aktif">
                            <i class="ti ti-file-download"></i>
                            <span>Download PDF</span>
                        </button>
                        <?php if ($isAdmin): ?>
                        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" id="btn-toggle-mode" onclick="toggleEditMode()">
                            <i class="ti ti-pencil" id="icon-mode"></i>
                            <span id="text-mode">Mode Plot Manual</span>
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm d-flex align-items-center gap-1" id="btn-toggle-drag" onclick="toggleDragMode()" title="Geser kotak rumah ke posisi yang benar">
                            <i class="ti ti-hand-move" id="icon-drag"></i>
                            <span id="text-drag">Mode Geser Kotak</span>
                        </button>
                        <button type="button" class="btn btn-success btn-sm d-flex align-items-center gap-1" id="btn-save-drag" onclick="saveAllDraggedPolygons()" style="display: none;" title="Simpan posisi kotak yang sudah digeser">
                            <i class="ti ti-device-floppy"></i>
                            <span>Simpan (<span id="drag-count">0</span>)</span>
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetMapView()" title="Reset Posisi Peta">
                            <i class="ti ti-focus-2"></i> Pusatkan Peta
                        </button>
                        <?php if ($isAdmin): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1" onclick="resetSemuaPolygon()" title="Hapus semua koordinat polygon dan mulai plotting ulang">
                            <i class="ti ti-trash"></i> Reset Semua Kotak
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Ringkasan Status IPL -->
<div class="row mb-3 g-2">
    <div class="col-6 col-md-3">
        <div class="card kpi-card bg-white p-3 mb-0 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted fs-2 text-uppercase fw-semibold">Total Rumah</span>
                    <h3 class="fw-bold mb-0 text-dark" id="stat-total-rumah"><?= $siteplan_data['total_rumah'] ?></h3>
                </div>
                <div class="p-2 bg-light-secondary rounded text-secondary">
                    <i class="ti ti-home fs-6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card kpi-card bg-light-success p-3 mb-0 shadow-sm border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-success fs-2 text-uppercase fw-semibold">Lunas IPL</span>
                    <h3 class="fw-bold mb-0 text-success" id="stat-total-lunas"><?= $siteplan_data['total_lunas'] ?></h3>
                </div>
                <div class="p-2 bg-success text-white rounded">
                    <i class="ti ti-check fs-6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card kpi-card bg-light-danger p-3 mb-0 shadow-sm border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-danger fs-2 text-uppercase fw-semibold">Menunggak (Merah)</span>
                    <h3 class="fw-bold mb-0 text-danger" id="stat-total-menunggak"><?= $siteplan_data['total_menunggak'] ?></h3>
                </div>
                <div class="p-2 bg-danger text-white rounded">
                    <i class="ti ti-alert-triangle fs-6"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card kpi-card bg-light-info p-3 mb-0 shadow-sm border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-info fs-2 text-uppercase fw-semibold">Kepatuhan IPL</span>
                    <h3 class="fw-bold mb-0 text-info" id="stat-persen"><?= $siteplan_data['persen_lunas'] ?>%</h3>
                </div>
                <div class="p-2 bg-info text-white rounded">
                    <i class="ti ti-percentage fs-6"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card siteplan-card mb-3">
    <div class="card-body p-3 bg-light">
        <form id="filter-form" class="row g-2 align-items-center" onsubmit="event.preventDefault(); applyFilter();">
            <div class="col-md-2 col-6">
                <label class="form-label fs-2 text-muted mb-1">Tahun:</label>
                <select name="tahun" id="filter-tahun" class="form-select form-select-sm">
                    <?php 
                    $curY = (int)date('Y');
                    for ($y = $curY; $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= ($y == $tahun) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fs-2 text-muted mb-1">Bulan:</label>
                <select name="bulan" id="filter-bulan" class="form-select form-select-sm">
                    <option value="" <?= ($bulan === null || $bulan === '') ? 'selected' : '' ?>>Semua Bulan (Tahun Berjalan)</option>
                    <?php 
                    $bulan_names = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    foreach ($bulan_names as $num => $bname): ?>
                        <option value="<?= $num ?>" <?= ($bulan === $num) ? 'selected' : '' ?>><?= $bname ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 col-12">
                <label class="form-label fs-2 text-muted mb-1">Koordinator Blok:</label>
                <?php if ($isKoor): ?>
                <!-- Koordinator: locked to their own area, show as read-only text -->
                <select name="id_koordinator" id="filter-koor" class="form-select form-select-sm" disabled>
                    <?php foreach ($koordinators as $k): ?>
                        <?php if ($k['id'] == $koorId): ?>
                        <option value="<?= $k['id'] ?>" selected><?= htmlspecialchars($k['nama']) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="id_koordinator" value="<?= $koorId ?>">
                <?php else: ?>
                <select name="id_koordinator" id="filter-koor" class="form-select form-select-sm">
                    <option value="">Semua Koordinator (Seluruh Blok)</option>
                    <?php foreach ($koordinators as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= ($id_koordinator == $k['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
            <div class="col-md-3 col-12 d-flex align-items-end gap-2 mt-md-4 mt-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="ti ti-filter"></i> Filter Peta
                </button>
                <div class="flex-grow-1">
                    <input type="text" id="quick-search" class="form-control form-control-sm" placeholder="Cari No. Rumah..." onkeyup="handleQuickSearch(this.value)">
                </div>
            </div>
        </form>
    </div>

    <!-- Map Wrapper -->
    <div style="position: relative;">
        <div id="siteplan-map"></div>

        <!-- Floating Legend -->
        <div class="map-legend">
            <div class="fw-bold mb-2 pb-1 border-bottom text-dark">Legenda Status IPL</div>
            <div class="legend-item">
                <div class="legend-box box-menunggak"></div>
                <span><strong>Merah:</strong> Belum Bayar IPL</span>
            </div>
            <div class="legend-item">
                <div class="legend-box box-lunas"></div>
                <span><strong>Hijau:</strong> Lunas IPL</span>
            </div>
            <div class="legend-item">
                <div class="legend-box box-unplotted"></div>
                <span class="text-muted"><small>Kotak belum di-plot (<span id="unplotted-count">0</span> rumah)</small></span>
            </div>
        </div>

        <!-- Floating Tool Panel Mode Plotting -->
        <div class="plotting-toolbar" id="plotting-toolbar">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <span class="fw-bold text-primary"><i class="ti ti-pencil"></i> Plotting Kotak Rumah</span>
                <button type="button" class="btn-close btn-sm" onclick="toggleEditMode()"></button>
            </div>
            <div class="mb-2">
                <label class="form-label fs-2 text-muted mb-1">Pilih Rumah yang Akan Diplot:</label>
                <select id="plot-select-house" class="form-select form-select-sm" onchange="onSelectPlotHouse(this.value)">
                    <option value="">-- Pilih Rumah / Kavling --</option>
                    <?php foreach ($siteplan_data['houses'] as $h): ?>
                        <option value="<?= $h['id'] ?>" data-has-coords="<?= $h['has_coords'] ? '1' : '0' ?>">
                            <?= htmlspecialchars($h['alamat']) ?> <?= $h['nama'] ? '(' . htmlspecialchars($h['nama']) . ')' : '' ?> <?= $h['has_coords'] ? '✓' : '(Belum Diplot)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="alert alert-info py-2 px-2 fs-2 mb-2" id="plot-instruction">
                <strong>Cara:</strong> Pilih rumah di atas, lalu <strong>klik 4 titik sudut</strong> pada peta yang membentuk kotak kavling rumah tersebut.
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-primary btn-sm flex-grow-1" id="btn-save-plot" onclick="saveCurrentPlot()" disabled>
                    <i class="ti ti-device-floppy"></i> Simpan
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="resetCurrentPlotPoints()" title="Ulangi Klik Titik">
                    <i class="ti ti-refresh"></i> Ulangi
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm" id="btn-delete-plot" onclick="deleteCurrentPlot()" title="Hapus Kotak Ini" disabled>
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>

        <!-- Floating Tool Panel Mode Geser (Drag) -->
        <div class="plotting-toolbar" id="drag-toolbar" style="display: none;">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <span class="fw-bold text-warning"><i class="ti ti-arrows-move"></i> Mode Geser Kotak</span>
                <button type="button" class="btn-close btn-sm" onclick="toggleDragMode()"></button>
            </div>
            <div class="alert alert-warning py-2 px-2 fs-2 mb-2">
                <strong>Cara pakai:</strong><br>
                1. <strong>Klik-tahan</strong> kotak rumah → <strong>geser</strong> mouse untuk MEMINDAH kotak<br>
                2. <strong>Klik-tahan titik biru</strong> di sudut kotak → geser untuk <strong>RESIZE</strong> (ubah ukuran)<br>
                3. Klik <strong>Simpan</strong> untuk menyimpan ke database<br>
                <hr class="my-1">
                <strong>Tips:</strong> Tahan <kbd class="px-1 bg-light border">Space</kbd> untuk menggeser peta sementara<br>
                Klik area kosong untuk menghapus titik resize
            </div>
            <button type="button" class="btn btn-success btn-sm w-100" onclick="saveAllDraggedPolygons()">
                <i class="ti ti-device-floppy"></i> Simpan Posisi (<span id="drag-count">0</span>)
            </button>
        </div>
    </div>
</div>

<!-- Modal Rincian Rumah & Pembayaran -->
<div class="modal fade" id="modalHouseDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white" id="modal-house-header">
                <h5 class="modal-title fw-bold text-white" id="modal-house-title">Detail Rumah</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="round-48 rounded-circle bg-light text-primary d-flex align-items-center justify-content-center">
                        <i class="ti ti-home fs-7"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" id="modal-alamat">-</h5>
                        <p class="text-muted mb-0" id="modal-koordinator">-</p>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Penghuni / Pemilik:</label>
                    <div class="fs-4 fw-bold text-dark" id="modal-nama">-</div>
                </div>

                <div class="mb-3">
                    <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Status IPL Periode Ini:</label>
                    <div id="modal-status-badge"></div>
                </div>

                <div class="mb-3" id="modal-tunggak-box">
                    <label class="text-muted fs-2 text-uppercase fw-semibold mb-1">Bulan Belum Terbayar:</label>
                    <div class="p-2 bg-light-danger text-danger rounded fs-3 fw-semibold" id="modal-tunggak-list">
                        -
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top">
                    <a href="#" target="_blank" id="modal-btn-wa" class="btn btn-success flex-grow-1 d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-whatsapp"></i> Chat WhatsApp
                    </a>
                    <a href="<?= base_url('pembayaran') ?>" class="btn btn-primary flex-grow-1">
                        <i class="ti ti-cash"></i> Entri Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS Local -->
<script src="<?= base_url('assets/siteplan/leaflet/leaflet.js') ?>"></script>
<script src="<?= base_url('assets/siteplan/leaflet/Leaflet.fullscreen.min.js') ?>"></script>

<script>
    // Data Rumah Awal dari Controller
    let siteplanData = <?= json_encode($siteplan_data) ?>;
    let map = null;
    let mapRenderer = null;
    let polygonLayerGroup = null;
    let activePolygons = {}; // Map id_rumah -> L.Polygon
    let showRedBoxes = true;
    let currentFilterKoor = "<?= htmlspecialchars((string)$id_koordinator, ENT_QUOTES, 'UTF-8') ?>";

    // Mode Plotting State
    let isEditMode = false;
    let currentPlotHouseId = null;
    let currentPlotPoints = [];
    let tempMarkers = [];
    let tempPolygon = null;

    // Role guard: true jika user adalah admin (dapat mengakses fitur edit)
    const IS_ADMIN = <?= $isAdmin ? 'true' : 'false' ?>;

    // Mode Geser (Drag) State
    let isDragMode = false;
    let draggingPolygon = null;
    let dragStartMouse = null;
    let dragStartCoords = null;
    let movedPolygons = {};
    let selectedPolygon = null;
    let resizeMarkers = [];
    let isDraggingBox = false;
    let isResizing = false;
    let justDragged = false;
    let justDraggedTimer = null;
    let isSpacePressed = false;

    function setJustDragged() {
        justDragged = true;
        clearTimeout(justDraggedTimer);
        justDraggedTimer = setTimeout(() => { justDragged = false; }, 250);
    }

    // Listener tombol Space untuk panning peta saat Mode Geser aktif
    document.addEventListener('keydown', function(e) {
        if (!isDragMode) return;
        if (e.code === 'Space' && !isSpacePressed && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            isSpacePressed = true;
            map.dragging.enable();
            map.getContainer().style.cursor = 'grab';
        }
    });
    document.addEventListener('keyup', function(e) {
        if (!isDragMode) return;
        if (e.code === 'Space') {
            isSpacePressed = false;
            map.dragging.disable();
            map.getContainer().style.cursor = 'move';
        }
    });

    // Inisialisasi Peta Leaflet
    function initMap() {
        const XMAP = 60;
        const YMAP = -40;

        map = L.map('siteplan-map', {
            center: [XMAP, YMAP],
            zoom: 2,
            minZoom: 2,
            maxZoom: 5,
            zoomAnimation: false,
            fullscreenControl: true,
            attributionControl: false
        });

        // Tile layer menggunakan asset lokal yang sudah didownload
        const tileUrl = "<?= base_url('assets/siteplan/tsi/tiles/{z}/{x}/{y}.png') ?>";
        L.tileLayer(tileUrl, {
            minZoom: 2,
            maxZoom: 5,
            noWrap: true,
            tms: false
        }).addTo(map);

        polygonLayerGroup = new L.LayerGroup().addTo(map);

        // Render poligon semua rumah
        renderHousePolygons();

        // Event Klik pada Peta (untuk Mode Plotting & Mode Geser)
        map.on('click', function(e) {
            if (isEditMode && currentPlotHouseId) {
                handleMapClickInEditMode(e.latlng);
            } else if (isDragMode) {
                // Jangan deselect jika baru saja selesai drag atau sedang resize
                if (justDragged || isResizing) return;
                // Klik area kosong = deselect polygon (hapus corner handles)
                deselectPolygon();
            }
        });

        // Event untuk Mode Geser (Drag Kotak)
        map.on('mousemove', handleDragMove);
        map.on('mouseup', handleDragEnd);
    }

    // Render Semua Poligon Rumah
    function renderHousePolygons() {
        // Hapus corner handles dari polygon yang dipilih sebelumnya
        if (typeof deselectPolygon === 'function') deselectPolygon();
        polygonLayerGroup.clearLayers();
        activePolygons = {};

        let unplotted = 0;

        siteplanData.houses.forEach(house => {
            if (house.has_coords && house.polygon_coords && house.polygon_coords.length >= 3) {
                const isLunas = (house.status_ipl === 'lunas');
                if (!showRedBoxes && !isLunas) {
                    return;
                }
                const isKoorHighlighted = currentFilterKoor !== '' && parseInt(currentFilterKoor) === parseInt(house.id_koordinator);
                
                // Konfigurasi Warna: Merah jika menunggak, Hijau jika lunas
                const fillColor = isLunas ? '#22c55e' : '#ef4444';
                const strokeColor = isKoorHighlighted ? '#f59e0b' : (isLunas ? '#15803d' : '#b91c1c');
                const fillOpacity = isKoorHighlighted ? 0.78 : (isLunas ? 0.45 : 0.65);
                const strokeWeight = isKoorHighlighted ? 3 : 1.5;

                const poly = L.polygon(house.polygon_coords, {
                    color: strokeColor,
                    fillColor: fillColor,
                    fillOpacity: fillOpacity,
                    weight: strokeWeight,
                    houseData: house
                });

                // Tooltip saat hover
                poly.bindTooltip(`<b>${house.alamat}</b><br>${house.nama || '-'}<br><span class="badge ${isLunas ? 'bg-success' : 'bg-danger'}">${isLunas ? 'Lunas' : 'Menunggak'}</span>`, {
                    sticky: true,
                    direction: 'top'
                });

                // Hover highlight effect
                poly.on('mouseover', function(e) {
                    if (isDragMode) return;
                    this.setStyle({ weight: 3, color: '#f59e0b', fillOpacity: 0.85 });
                });
                poly.on('mouseout', function(e) {
                    if (isDragMode) return;
                    this.setStyle({ weight: strokeWeight, color: strokeColor, fillOpacity: fillOpacity });
                });

                // Klik poligon
                poly.on('click', function(e) {
                    if (isDragMode) {
                        L.DomEvent.stopPropagation(e);
                        if (justDragged) return;
                        selectPolygonForResize(this);
                        return;
                    }
                    if (!isEditMode) {
                        showHouseDetail(house);
                    } else {
                        // Di mode edit, klik poligon untuk memilih rumah tersebut
                        document.getElementById('plot-select-house').value = house.id;
                        onSelectPlotHouse(house.id);
                    }
                });

                // Drag mode: mousedown untuk mulai menggeser kotak
                poly.on('mousedown', handleDragStart);

                poly.addTo(polygonLayerGroup);
                activePolygons[house.id] = poly;
            } else {
                unplotted++;
            }
        });

        document.getElementById('unplotted-count').textContent = unplotted;
    }

    function toggleRedBoxes() {
        showRedBoxes = !showRedBoxes;
        document.getElementById('icon-red-boxes').className = showRedBoxes ? 'ti ti-eye-off' : 'ti ti-eye';
        document.getElementById('text-red-boxes').textContent = showRedBoxes ? 'Hide Merah' : 'Show Merah';
        document.getElementById('btn-toggle-red').className = showRedBoxes
            ? 'btn btn-outline-danger btn-sm d-flex align-items-center gap-1'
            : 'btn btn-secondary btn-sm d-flex align-items-center gap-1';
        renderHousePolygons();
        focusFilteredPolygons();
    }

    function focusFilteredPolygons() {
        if (!map || !currentFilterKoor) return;

        const selectedPolys = Object.values(activePolygons).filter(poly => {
            const h = poly.options.houseData;
            return h && parseInt(h.id_koordinator) === parseInt(currentFilterKoor);
        });

        if (selectedPolys.length === 0) return;

        let bounds = selectedPolys[0].getBounds();
        selectedPolys.slice(1).forEach(poly => bounds.extend(poly.getBounds()));
        map.fitBounds(bounds, {
            padding: [35, 35],
            maxZoom: 4
        });
    }

    function downloadSiteplanPdf() {
        const params = new URLSearchParams({
            tahun: document.getElementById('filter-tahun').value,
            bulan: document.getElementById('filter-bulan').value,
            id_koordinator: document.getElementById('filter-koor').value
        });
        window.open("<?= base_url('siteplan/pdf') ?>?" + params.toString(), '_blank');
    }

    // Tampilkan Modal Detail Rumah & IPL
    function showHouseDetail(house) {
        const isLunas = (house.status_ipl === 'lunas');

        const headerEl = document.getElementById('modal-house-header');
        headerEl.className = isLunas ? 'modal-header popup-header-lunas' : 'modal-header popup-header-menunggak';

        document.getElementById('modal-house-title').textContent = house.alamat;
        document.getElementById('modal-alamat').textContent = house.alamat;
        document.getElementById('modal-koordinator').textContent = 'Koordinator: ' + house.koordinator;
        document.getElementById('modal-nama').textContent = house.nama || '(Belum terdata nama penghuni)';

        const badgeEl = document.getElementById('modal-status-badge');
        const tunggakBox = document.getElementById('modal-tunggak-box');

        if (isLunas) {
            badgeEl.innerHTML = '<span class="badge bg-success fs-3 px-3 py-2"><i class="ti ti-check"></i> LUNAS IPL</span>';
            tunggakBox.style.display = 'none';
        } else {
            badgeEl.innerHTML = `<span class="badge bg-danger fs-3 px-3 py-2"><i class="ti ti-alert-triangle"></i> MENUNGGAK (${house.tunggakan} Bulan)</span>`;
            tunggakBox.style.display = 'block';
            document.getElementById('modal-tunggak-list').textContent = house.bulan_tunggak_list || 'Periode berjalan';
        }

        // Setup WhatsApp link
        const btnWa = document.getElementById('modal-btn-wa');
        if (house.no_hp && house.no_hp.trim() !== '') {
            let phone = house.no_hp.replace(/[^0-9]/g, '');
            if (phone.startsWith('0')) {
                phone = '62' + phone.substring(1);
            }
            const pesan = encodeURIComponent(`Assalamu'alaikum Bapak/Ibu ${house.nama || ''} (${house.alamat}), kami dari pengurus Perum TSI ingin mengonfirmasi status iuran IPL.`);
            btnWa.href = `https://wa.me/${phone}?text=${pesan}`;
            btnWa.style.display = 'inline-flex';
        } else {
            btnWa.style.display = 'none';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalHouseDetail'));
        modal.show();
    }

    // Quick Search: Cari no rumah dan pan peta ke lokasinya
    function handleQuickSearch(query) {
        query = query.trim().toLowerCase();
        if (query.length < 2) return;

        const found = siteplanData.houses.find(h => 
            h.alamat.toLowerCase().includes(query) || 
            (h.nama && h.nama.toLowerCase().includes(query))
        );

        if (found && activePolygons[found.id]) {
            const poly = activePolygons[found.id];
            map.fitBounds(poly.getBounds(), { maxZoom: 4, padding: [35, 35] });
            poly.openTooltip();
        }
    }

    // Reset Map View
    function resetMapView() {
        if (map) {
            map.setView([60, -40], 2);
        }
    }

    // Toggle Mode Edit / Plotting
    function toggleEditMode() {
        if (!IS_ADMIN) return;
        if (isDragMode) toggleDragMode(); // Keluar dari mode geser jika aktif
        isEditMode = !isEditMode;
        const toolbar = document.getElementById('plotting-toolbar');
        const icon = document.getElementById('icon-mode');
        const text = document.getElementById('text-mode');
        const btn = document.getElementById('btn-toggle-mode');

        if (isEditMode) {
            toolbar.style.display = 'block';
            icon.className = 'ti ti-eye';
            text.textContent = 'Kembali ke Mode Pantau';
            btn.className = 'btn btn-warning btn-sm d-flex align-items-center gap-1';
        } else {
            toolbar.style.display = 'none';
            icon.className = 'ti ti-pencil';
            text.textContent = 'Mode Plotting Kotak';
            btn.className = 'btn btn-outline-primary btn-sm d-flex align-items-center gap-1';
            resetCurrentPlotPoints();
        }
    }

    // Handler Pilih Rumah di Mode Plotting
    function onSelectPlotHouse(houseId) {
        resetCurrentPlotPoints();
        currentPlotHouseId = houseId ? parseInt(houseId) : null;

        const btnSave = document.getElementById('btn-save-plot');
        const btnDel = document.getElementById('btn-delete-plot');

        if (!currentPlotHouseId) {
            btnSave.disabled = true;
            btnDel.disabled = true;
            return;
        }

        const house = siteplanData.houses.find(h => h.id === currentPlotHouseId);
        if (house && house.has_coords && house.polygon_coords) {
            btnDel.disabled = false;
            // Tampilkan poligon yang sudah ada
            currentPlotPoints = house.polygon_coords.map(pt => L.latLng(pt[0], pt[1]));
            updateTempPolygon();
            if (activePolygons[house.id]) {
                map.fitBounds(activePolygons[house.id].getBounds(), { maxZoom: 4, padding: [35, 35] });
            }
        } else {
            btnDel.disabled = true;
        }
    }

    // Klik Peta pada Mode Edit
    function handleMapClickInEditMode(latlng) {
        if (currentPlotPoints.length >= 8) {
            alert('Maksimal 8 titik untuk satu kavling rumah. Silakan klik "Simpan" atau "Ulangi".');
            return;
        }

        currentPlotPoints.push(latlng);

        // Marker kecil untuk setiap titik
        const m = L.circleMarker(latlng, {
            radius: 5,
            color: '#2563eb',
            fillColor: '#60a5fa',
            fillOpacity: 1
        }).addTo(map);
        tempMarkers.push(m);

        updateTempPolygon();

        if (currentPlotPoints.length >= 3) {
            document.getElementById('btn-save-plot').disabled = false;
        }
    }

    // Update Poligon Sementara saat Menggambar
    function updateTempPolygon() {
        if (tempPolygon) {
            map.removeLayer(tempPolygon);
        }

        if (currentPlotPoints.length >= 2) {
            tempPolygon = L.polygon(currentPlotPoints, {
                color: '#2563eb',
                fillColor: '#3b82f6',
                fillOpacity: 0.5,
                weight: 2,
                dashArray: '4, 4'
            }).addTo(map);
        }
    }

    // Reset Titik Plotting Sementara
    function resetCurrentPlotPoints() {
        currentPlotPoints = [];
        tempMarkers.forEach(m => map.removeLayer(m));
        tempMarkers = [];
        if (tempPolygon) {
            map.removeLayer(tempPolygon);
            tempPolygon = null;
        }
        document.getElementById('btn-save-plot').disabled = true;
    }

    // Simpan Plotting ke Server
    function saveCurrentPlot() {
        if (!currentPlotHouseId || currentPlotPoints.length < 3) {
            alert('Pilih rumah dan buat minimal 3 titik sudut.');
            return;
        }

        const coordsArray = currentPlotPoints.map(pt => [
            parseFloat(pt.lat.toFixed(6)),
            parseFloat(pt.lng.toFixed(6))
        ]);

        const btnSave = document.getElementById('btn-save-plot');
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        $.ajax({
            url: "<?= base_url('siteplan/save_polygon') ?>",
            type: "POST",
            dataType: "json",
            data: {
                id_rumah: currentPlotHouseId,
                polygon_coords: JSON.stringify(coordsArray)
            },
            success: function(res) {
                btnSave.innerHTML = '<i class="ti ti-device-floppy"></i> Simpan';
                if (res.status === 'success') {
                    // Update data lokal
                    const house = siteplanData.houses.find(h => h.id === currentPlotHouseId);
                    if (house) {
                        house.has_coords = true;
                        house.polygon_coords = coordsArray;
                    }
                    renderHousePolygons();
                    resetCurrentPlotPoints();
                    alert(res.message);
                } else {
                    alert(res.message || 'Gagal menyimpan.');
                    btnSave.disabled = false;
                }
            },
            error: function() {
                btnSave.innerHTML = '<i class="ti ti-device-floppy"></i> Simpan';
                btnSave.disabled = false;
                alert('Terjadi kesalahan jaringan.');
            }
        });
    }

    // Hapus Plotting Kotak Rumah
    function deleteCurrentPlot() {
        if (!currentPlotHouseId) return;
        if (!confirm('Apakah Anda yakin ingin menghapus plotting kotak untuk rumah ini?')) return;

        $.ajax({
            url: "<?= base_url('siteplan/delete_polygon') ?>",
            type: "POST",
            dataType: "json",
            data: { id_rumah: currentPlotHouseId },
            success: function(res) {
                if (res.status === 'success') {
                    const house = siteplanData.houses.find(h => h.id === currentPlotHouseId);
                    if (house) {
                        house.has_coords = false;
                        house.polygon_coords = null;
                    }
                    renderHousePolygons();
                    resetCurrentPlotPoints();
                    document.getElementById('btn-delete-plot').disabled = true;
                    alert(res.message);
                } else {
                    alert(res.message);
                }
            }
        });
    }

    // Filter Ajax Tanpa Reload Penuh
    function applyFilter() {
        const tahun = document.getElementById('filter-tahun').value;
        const bulan = document.getElementById('filter-bulan').value;
        const koor = document.getElementById('filter-koor').value;
        currentFilterKoor = koor;

        $.ajax({
            url: "<?= base_url('siteplan/ajax_data') ?>",
            type: "GET",
            dataType: "json",
            data: {
                tahun: tahun,
                bulan: bulan,
                id_koordinator: koor
            },
            success: function(res) {
                siteplanData = res;
                // Update KPI Cards
                document.getElementById('stat-total-rumah').textContent = res.total_rumah;
                document.getElementById('stat-total-lunas').textContent = res.total_lunas;
                document.getElementById('stat-total-menunggak').textContent = res.total_menunggak;
                document.getElementById('stat-persen').textContent = res.persen_lunas + '%';

                renderHousePolygons();
                focusFilteredPolygons();
            }
        });
    }

    // Reset semua koordinat polygon (admin utility)
    function resetSemuaPolygon() {
        if (!IS_ADMIN) return;
        if (!confirm('⚠️ PERHATIAN!\n\nAnda akan MENGHAPUS SEMUA koordinat kotak rumah yang sudah diplot.\nSetelah ini, semua kotak akan hilang dan perlu diplot ulang satu per satu.\n\nLanjutkan?')) return;

        const btn = event.currentTarget;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mereset...';

        $.ajax({
            url: "<?= base_url('siteplan/reset_all_polygon') ?>",
            type: "POST",
            dataType: "json",
            success: function(res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-trash"></i> Reset Semua Kotak';
                if (res.status === 'success') {
                    // Update data lokal - hapus semua koordinat
                    siteplanData.houses.forEach(h => {
                        h.has_coords = false;
                        h.polygon_coords = null;
                    });
                    renderHousePolygons();
                    alert('✅ ' + res.message + '\n\nSilakan gunakan Mode Plotting Kotak untuk memasang koordinat ulang.');
                } else {
                    alert('❌ ' + (res.message || 'Gagal mereset koordinat.'));
                }
            },
            error: function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-trash"></i> Reset Semua Kotak';
                alert('Terjadi kesalahan jaringan saat mereset koordinat.');
            }
        });
    }

    // Auto-Generate Plotting Seluruh Rumah secara Otomatis dan Presisi
    function autoGeneratePolygons() {
        if (!IS_ADMIN) return;
        if (!confirm('⚡ Apakah Anda ingin men-generate koordinat plotting untuk seluruh kavling rumah secara otomatis?\n\nSistem akan menghitung dan menempatkan posisi seluruh unit (Ruha, TSI Raya, dan Blok I s/d XI) secara presisi di denah.')) return;

        const btn = document.getElementById('btn-auto-plot');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

        $.ajax({
            url: "<?= base_url('siteplan/auto_generate_polygons') ?>",
            type: "POST",
            dataType: "json",
            success: function(res) {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                if (res.status === 'success') {
                    alert('✅ ' + res.message);
                    // Refresh data map via ajax
                    applyFilter();
                } else {
                    alert('❌ ' + (res.message || 'Gagal men-generate poligon otomatis.'));
                }
            },
            error: function() {
                btn.disabled = false;
                btn.innerHTML = origHtml;
                alert('Terjadi kesalahan jaringan saat memproses auto-plotting.');
            }
        });
    }

    // =============================================
    // MODE GESER (DRAG) KOTAK POLYGON
    // =============================================

    function toggleDragMode() {
        isDragMode = !isDragMode;
        const btn = document.getElementById('btn-toggle-drag');
        const icon = document.getElementById('icon-drag');
        const text = document.getElementById('text-drag');

        if (isEditMode) toggleEditMode(); // Keluar dari mode edit jika aktif

        if (isDragMode) {
            btn.className = 'btn btn-warning btn-sm d-flex align-items-center gap-1';
            icon.className = 'ti ti-arrows-move';
            text.textContent = 'Keluar Mode Geser';
            document.getElementById('btn-save-drag').style.display = 'inline-flex';
            document.getElementById('drag-toolbar').style.display = 'block';
            // DISABLE map dragging untuk seluruh mode geser — agar klik pada polygon
            // tidak memicu panning map. User bisa zoom dengan scroll wheel.
            // Untuk panning: tahan tombol Space, atau keluar mode geser.
            map.dragging.disable();
            // Ubah style semua polygon: border tebal + dash untuk indicate draggable
            Object.values(activePolygons).forEach(poly => {
                poly.setStyle({ weight: 3, dashArray: '5, 3' });
            });
            forceCanvasRedraw();
            map.getContainer().style.cursor = 'move';
        } else {
            btn.className = 'btn btn-outline-primary btn-sm d-flex align-items-center gap-1';
            icon.className = 'ti ti-hand-move';
            text.textContent = 'Mode Geser Kotak';
            document.getElementById('btn-save-drag').style.display = 'none';
            document.getElementById('drag-toolbar').style.display = 'none';
            // Hapus corner handles
            deselectPolygon();
            // RE-ENABLE map dragging
            map.dragging.enable();
            map.getContainer().style.cursor = '';
            // Auto-save jika ada kotak yang sudah digeser
            if (Object.keys(movedPolygons).length > 0) {
                saveAllDraggedPolygons();
            } else {
                renderHousePolygons();
            }
        }
    }

    // Mulai drag: mousedown pada polygon
    function handleDragStart(e) {
        if (!isDragMode || isSpacePressed || isResizing) return;
        if (e.originalEvent) {
            L.DomEvent.stopPropagation(e.originalEvent);
            L.DomEvent.preventDefault(e.originalEvent);
        }

        draggingPolygon = e.target;
        isDraggingBox = false;

        // Pilih polygon ini untuk resize (tampilkan corner handles)
        selectPolygonForResize(draggingPolygon);

        // Simpan koordinat awal polygon
        let latlngs = draggingPolygon.getLatLngs();
        if (Array.isArray(latlngs[0]) && !latlngs[0].lat) {
            latlngs = latlngs[0];
        }
        dragStartCoords = latlngs.map(pt => ({ lat: pt.lat, lng: pt.lng }));
        dragStartMouse = { lat: e.latlng.lat, lng: e.latlng.lng };

        // Highlight polygon yang sedang digeser
        draggingPolygon.setStyle({ color: '#f59e0b', weight: 4, fillOpacity: 0.85 });
        forceCanvasRedraw();
    }

    // Drag berjalan: mousemove pada map
    function handleDragMove(e) {
        if (!isDragMode || !draggingPolygon || isResizing) return;
        const deltaLat = e.latlng.lat - dragStartMouse.lat;
        const deltaLng = e.latlng.lng - dragStartMouse.lng;

        if (Math.abs(deltaLat) > 0.00005 || Math.abs(deltaLng) > 0.00005) {
            isDraggingBox = true;
        }

        const newCoords = dragStartCoords.map(pt => [pt.lat + deltaLat, pt.lng + deltaLng]);
        draggingPolygon.setLatLngs(newCoords);

        // Force redraw canvas secara sinkron agar polygon langsung terlihat bergerak
        forceCanvasRedraw();

        // Update posisi corner handles agar mengikuti polygon
        updateResizeHandles(newCoords);
        if (e.originalEvent) e.originalEvent.preventDefault();
    }

    // Selesai drag: mouseup pada map
    function handleDragEnd(e) {
        if (!draggingPolygon) return;
        if (isDraggingBox) {
            const houseData = draggingPolygon.options.houseData;
            if (houseData) {
                movedPolygons[houseData.id] = true;
                document.getElementById('drag-count').textContent = Object.keys(movedPolygons).length;
            }
            setJustDragged();
        }

        // Reset style polygon terpilih
        draggingPolygon.setStyle({ color: '#f59e0b', weight: 3, dashArray: '4, 3', fillOpacity: 0.75 });

        // Update corner handles ke posisi final
        let latlngs = draggingPolygon.getLatLngs();
        if (Array.isArray(latlngs[0]) && !latlngs[0].lat) {
            latlngs = latlngs[0];
        }
        updateResizeHandles(latlngs);

        // Pastikan canvas menampilkan keadaan final
        forceCanvasRedraw();
        draggingPolygon = null;
        dragStartMouse = null;
        dragStartCoords = null;
        isDraggingBox = false;
    }

    // === RESIZE HANDLE (corner markers) ===

    // Force canvas renderer to immediately redraw (synchronous, not async)
    function forceCanvasRedraw() {
        if (!mapRenderer) return;
        // Cancel any pending async redraw request
        if (mapRenderer._redrawRequest) {
            if (L.Util.cancelAnimFrame) {
                L.Util.cancelAnimFrame(mapRenderer._redrawRequest);
            }
            mapRenderer._redrawRequest = null;
        }
        // Synchronous full canvas clear + redraw all layers
        if (mapRenderer._redraw) {
            mapRenderer._redraw();
        }
    }

    const resizeIcon = L.divIcon({
        className: 'resize-handle-icon',
        html: '',
        iconSize: [14, 14],
        iconAnchor: [7, 7]
    });

    // Hitung posisi 4 sudut kotak saat salah satu sudut di-resize
    // Menjaga bentuk persegi panjang (arah sumbu dan sudut siku) tetap rapi
    function resizeQuad(originalLayerPoints, cornerIdx, newMouseLayerPoint) {
        if (!originalLayerPoints || originalLayerPoints.length !== 4) {
            return (originalLayerPoints || []).map((pt, i) => i === cornerIdx ? newMouseLayerPoint : pt);
        }

        const oppIdx = (cornerIdx + 2) % 4;
        const oppPt = originalLayerPoints[oppIdx];

        const adj1Idx = (cornerIdx + 1) % 4;
        const adj2Idx = (cornerIdx + 3) % 4;
        const adj1Pt = originalLayerPoints[adj1Idx];
        const adj2Pt = originalLayerPoints[adj2Idx];

        // Sumbu 1 & 2 dari sudut berlawanan (oppPt) ke sudut-sudut tetangganya
        const v1x = adj1Pt.x - oppPt.x;
        const v1y = adj1Pt.y - oppPt.y;
        const len1 = Math.sqrt(v1x * v1x + v1y * v1y);

        const v2x = adj2Pt.x - oppPt.x;
        const v2y = adj2Pt.y - oppPt.y;
        const len2 = Math.sqrt(v2x * v2x + v2y * v2y);

        if (len1 < 1 || len2 < 1) {
            return originalLayerPoints.map((pt, i) => i === cornerIdx ? newMouseLayerPoint : pt);
        }

        const u1x = v1x / len1;
        const u1y = v1y / len1;
        const u2x = v2x / len2;
        const u2y = v2y / len2;

        // Vektor dari titik tumpuan (opposite) ke kursor mouse
        const mx = newMouseLayerPoint.x - oppPt.x;
        const my = newMouseLayerPoint.y - oppPt.y;

        // Proyeksi ke masing-masing sumbu
        let proj1 = mx * u1x + my * u1y;
        let proj2 = mx * u2x + my * u2y;

        // Batas minimal ukuran kotak (agar tidak terbalik / collapse)
        const minDim = 6;
        if (proj1 < minDim) proj1 = minDim;
        if (proj2 < minDim) proj2 = minDim;

        const newAdj1 = L.point(oppPt.x + proj1 * u1x, oppPt.y + proj1 * u1y);
        const newAdj2 = L.point(oppPt.x + proj2 * u2x, oppPt.y + proj2 * u2y);
        const newCorner = L.point(newAdj1.x + newAdj2.x - oppPt.x, newAdj1.y + newAdj2.y - oppPt.y);

        const result = [];
        for (let i = 0; i < 4; i++) {
            if (i === cornerIdx) result.push(newCorner);
            else if (i === oppIdx) result.push(oppPt);
            else if (i === adj1Idx) result.push(newAdj1);
            else if (i === adj2Idx) result.push(newAdj2);
        }
        return result;
    }

    // Tampilkan corner handles pada polygon yang dipilih
    function selectPolygonForResize(poly) {
        if (!poly) return;

        // Hapus handles dari polygon sebelumnya jika ada yang beda
        if (selectedPolygon && selectedPolygon !== poly) {
            deselectPolygon();
        }

        selectedPolygon = poly;
        selectedPolygon.setStyle({ color: '#f59e0b', weight: 3, dashArray: '4, 3', fillOpacity: 0.75 });
        forceCanvasRedraw();

        // Bersihkan marker lama
        resizeMarkers.forEach(m => map.removeLayer(m));
        resizeMarkers = [];

        // Ambil corner coordinates
        let latlngs = poly.getLatLngs();
        if (Array.isArray(latlngs[0]) && !latlngs[0].lat) {
            latlngs = latlngs[0];
        }

        let origLayerPoints = null;

        latlngs.forEach((pt, idx) => {
            const marker = L.marker([pt.lat, pt.lng], {
                icon: resizeIcon,
                zIndexOffset: 10000,
                keyboard: false,
                draggable: true
            }).addTo(map);

            marker._cornerIndex = idx;

            marker.on('dragstart', function(e) {
                if (e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);
                isResizing = true;

                // Simpan koordinat layer point awal
                let coords = selectedPolygon.getLatLngs();
                if (Array.isArray(coords[0]) && !coords[0].lat) coords = coords[0];
                origLayerPoints = coords.map(c => map.latLngToLayerPoint(c));
            });

            marker.on('drag', function(e) {
                if (!selectedPolygon || !origLayerPoints) return;
                if (e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);

                const currentLayerPoint = map.latLngToLayerPoint(marker.getLatLng());
                const cornerIdx = marker._cornerIndex;
                const isShift = e.originalEvent && e.originalEvent.shiftKey;

                let newLayerPoints;
                if (!isShift && origLayerPoints.length === 4) {
                    newLayerPoints = resizeQuad(origLayerPoints, cornerIdx, currentLayerPoint);
                } else {
                    newLayerPoints = origLayerPoints.map((p, i) => i === cornerIdx ? currentLayerPoint : p);
                }

                const newLatLngCoords = newLayerPoints.map(p => map.layerPointToLatLng(p));
                selectedPolygon.setLatLngs(newLatLngCoords);
                forceCanvasRedraw();

                // Update posisi marker sudut lainnya secara real-time
                resizeMarkers.forEach((m, i) => {
                    if (i !== cornerIdx && newLatLngCoords[i]) {
                        m.setLatLng(newLatLngCoords[i]);
                    }
                });
            });

            marker.on('dragend', function(e) {
                if (e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);
                isResizing = false;
                setJustDragged();

                if (selectedPolygon) {
                    let coords = selectedPolygon.getLatLngs();
                    if (Array.isArray(coords[0]) && !coords[0].lat) coords = coords[0];

                    resizeMarkers.forEach((m, i) => {
                        if (coords[i]) m.setLatLng(coords[i]);
                    });

                    const houseData = selectedPolygon.options.houseData;
                    if (houseData) {
                        movedPolygons[houseData.id] = true;
                        document.getElementById('drag-count').textContent = Object.keys(movedPolygons).length;
                    }
                    forceCanvasRedraw();
                }
                origLayerPoints = null;
            });

            marker.on('click', function(e) {
                L.DomEvent.stopPropagation(e);
            });
            marker.on('mousedown', function(e) {
                L.DomEvent.stopPropagation(e);
            });

            resizeMarkers.push(marker);
        });
    }

    // Update posisi semua corner handles
    function updateResizeHandles(coords) {
        resizeMarkers.forEach((m, i) => {
            if (coords[i]) {
                if (coords[i].lat !== undefined) {
                    m.setLatLng(coords[i]);
                } else if (Array.isArray(coords[i])) {
                    m.setLatLng(coords[i]);
                }
            }
        });
    }

    // Hapus corner handles (deselect polygon)
    function deselectPolygon() {
        if (selectedPolygon && isDragMode) {
            selectedPolygon.setStyle({ weight: 3, dashArray: '5, 3', fillOpacity: 0.65 });
        }
        resizeMarkers.forEach(m => map.removeLayer(m));
        resizeMarkers = [];
        selectedPolygon = null;
        forceCanvasRedraw();
    }

    // Simpan semua polygon yang sudah digeser ke database
    function saveAllDraggedPolygons() {
        const movedIds = Object.keys(movedPolygons);
        if (movedIds.length === 0) {
            alert('Tidak ada kotak yang digeser.');
            return;
        }
        if (!confirm('Simpan posisi ' + movedIds.length + ' kotak yang sudah digeser ke database?')) return;

        let saveCount = 0;
        let errorCount = 0;
        const total = movedIds.length;
        const btnSave = document.getElementById('btn-save-drag');
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';

        movedIds.forEach(houseId => {
            const poly = activePolygons[houseId];
            if (!poly) { errorCount++; checkDone(); return; }

            // Ambil koordinat polygon yang sudah digeser
            let latlngs = poly.getLatLngs();
            if (Array.isArray(latlngs[0]) && !latlngs[0].lat) {
                latlngs = latlngs[0];
            }
            const coordsArray = latlngs.map(pt => [
                parseFloat(pt.lat.toFixed(6)),
                parseFloat(pt.lng.toFixed(6))
            ]);

            // Update data lokal
            const house = siteplanData.houses.find(h => h.id == houseId);
            if (house) house.polygon_coords = coordsArray;

            $.ajax({
                url: "<?= base_url('siteplan/save_polygon') ?>",
                type: "POST",
                dataType: "json",
                data: {
                    id_rumah: houseId,
                    polygon_coords: JSON.stringify(coordsArray)
                },
                success: function(res) {
                    if (res.status === 'success') saveCount++;
                    else errorCount++;
                    checkDone();
                },
                error: function() {
                    errorCount++;
                    checkDone();
                }
            });
        });

        function checkDone() {
            if (saveCount + errorCount !== total) return;
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="ti ti-device-floppy"></i><span>Simpan (<span id="drag-count">0</span>)</span>';
            movedPolygons = {};
            document.getElementById('drag-count').textContent = '0';
            if (errorCount === 0) {
                alert('✅ Berhasil menyimpan ' + saveCount + ' posisi kotak!');
            } else {
                alert('⚠️ Berhasil: ' + saveCount + ', Gagal: ' + errorCount);
            }
            renderHousePolygons();
        }
    }

    // Keyboard handler: tahan Space untuk panning sementara di mode geser
    let spaceHeld = false;
    document.addEventListener('keydown', function(e) {
        if (e.code === 'Space' && isDragMode && !spaceHeld && !draggingPolygon) {
            spaceHeld = true;
            map.dragging.enable();
            map.getContainer().style.cursor = 'grab';
            e.preventDefault();
        }
    });
    document.addEventListener('keyup', function(e) {
        if (e.code === 'Space' && isDragMode && spaceHeld) {
            spaceHeld = false;
            map.dragging.disable();
            map.getContainer().style.cursor = 'move';
            e.preventDefault();
        }
    });

    // Jalankan inisialisasi saat DOM siap
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        focusFilteredPolygons();
    });
</script>
