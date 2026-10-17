<!-- Leaflet CSS Local -->
<link rel="stylesheet" href="<?= base_url('assets/siteplan/leaflet/leaflet.css') ?>" />
<link rel="stylesheet" href="<?= base_url('assets/siteplan/leaflet/leaflet.fullscreen.css') ?>" />

<?php
$nama_bulan_arr = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$periode_label = $siteplan_data['periode_label'];
$houses_json = json_encode($siteplan_data['houses']);
?>

<style>
    /* Styling Peta Siteplan Sampah */
    .siteplan-card {
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        border: 1px solid #e2e8f0;
        background: #ffffff;
        overflow: hidden;
    }

    #siteplan-map {
        height: 720px;
        width: 100%;
        background: #f8fafc;
        border-radius: 0 0 16px 16px;
        z-index: 1;
    }

    .leaflet-interactive {
        cursor: pointer !important;
    }

    /* Floating Legend */
    .map-legend-sampah {
        position: absolute;
        bottom: 25px;
        left: 20px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(8px);
        padding: 12px 18px;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        font-size: 13px;
        border: 1px solid #cbd5e1;
        pointer-events: auto;
    }

    .legend-box-sampah {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid;
        display: inline-block;
    }
    .box-ambil {
        background: rgba(34, 197, 94, 0.7);
        border-color: #15803d;
    }
    .box-tahan {
        background: rgba(239, 68, 68, 0.85);
        border-color: #991b1b;
    }

    /* =========================================
       TEXT NAMA KAVLING LANGSUNG DI DALAM KOTAK (PERSIS SEPERTI DI SITEPLAN IPL)
       ========================================= */
    .house-map-label-wrapper.leaflet-div-icon,
    .house-map-label-wrapper {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        pointer-events: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .house-map-label {
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        text-align: center !important;
        line-height: 1.15 !important;
        font-weight: 700 !important;
        color: #ffffff !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        pointer-events: none !important;
        user-select: none !important;
        
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        overflow: hidden !important;
        
        text-shadow: 1px 1px 2px #000, -1px -1px 2px #000, 1px -1px 2px #000, -1px 1px 2px #000, 0 0 3px #000 !important;
    }

    /* Toggle Switch Custom */
    .switch-sampah {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 32px;
    }
    .switch-sampah input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider-sampah {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background-color: #ef4444; /* Merah: Tahan */
        transition: .3s;
        border-radius: 34px;
    }
    .slider-sampah:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .3s;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.25);
    }
    input:checked + .slider-sampah {
        background-color: #16a34a; /* Hijau: Ambil */
    }
    input:checked + .slider-sampah:before {
        transform: translateX(28px);
    }

    /* Bottom Sheet Modal Khusus HP */
    .mobile-bottom-sheet {
        position: fixed;
        left: 0;
        right: 0;
        bottom: -100%;
        background: #ffffff;
        border-radius: 20px 20px 0 0;
        box-shadow: 0 -8px 30px rgba(0,0,0,0.25);
        z-index: 1050;
        transition: bottom 0.35s ease-in-out;
        padding: 20px;
        max-height: 85vh;
        overflow-y: auto;
    }
    .mobile-bottom-sheet.show {
        bottom: 0;
    }
    .bottom-sheet-backdrop {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1040;
        display: none;
        backdrop-filter: blur(2px);
    }
    .bottom-sheet-backdrop.show {
        display: block;
    }
    .sheet-drag-handle {
        width: 44px;
        height: 5px;
        background: #cbd5e1;
        border-radius: 3px;
        margin: 0 auto 15px auto;
    }

    /* Checklist Card Item */
    .card-rumah-item {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        padding: 14px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    .card-rumah-item.item-ambil {
        border-left: 5px solid #16a34a;
    }
    .card-rumah-item.item-tahan {
        border-left: 5px solid #dc2626;
        background: #fffafa;
    }

    @media (max-width: 767.98px) {
        #siteplan-map {
            height: 560px;
        }
        .map-legend-sampah {
            bottom: 15px;
            left: 10px;
            padding: 8px 12px;
            font-size: 11px;
        }
    }
</style>

<!-- Header Breadcrumb & Toolbar -->
<div class="card w-100 bg-success-subtle shadow-none position-relative overflow-hidden mb-3 border-0">
    <div class="card-body px-3 px-md-4 py-3">
        <div class="row align-items-center">
            <div class="col-12 col-md-7 mb-2 mb-md-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="ti ti-trash fs-5"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold mb-0 text-success">Siteplan Operasional Sampah TSI</h4>
                        <small class="text-muted">Kendali status pengambilan sampah pos satpam &amp; petugas</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-5 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                <a href="<?= base_url('siteplan-sampah/pdf?tahun=' . $tahun . '&bulan=' . $bulan) ?>" target="_blank" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="ti ti-printer"></i> Cetak Landscape Pos
                </a>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" onclick="openWhatsAppModal()">
                    <i class="bi bi-whatsapp"></i> WA Petugas
                </button>
            </div>
        </div>
    </div>
</div>

<!-- KPI Status Sampah -->
<div class="row g-2 mb-3">
    <div class="col-4">
        <div class="card p-2 p-md-3 border-0 shadow-sm rounded-4 text-center bg-white h-100">
            <span class="text-muted small text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Rumah</span>
            <h3 class="fw-bold mb-0 text-dark" id="stat-total-rumah"><?= $siteplan_data['total_rumah'] ?></h3>
            <small class="text-muted d-none d-sm-block">Kavling TSI</small>
        </div>
    </div>
    <div class="col-4">
        <div class="card p-2 p-md-3 border-0 shadow-sm rounded-4 text-center bg-white border-start border-4 border-success h-100">
            <span class="text-success small text-uppercase fw-bold" style="font-size: 0.75rem;">[✓] Boleh Diambil</span>
            <h3 class="fw-bold mb-0 text-success" id="stat-total-ambil"><?= $siteplan_data['total_ambil'] ?></h3>
            <small class="text-success fw-semibold" id="stat-persen-ambil"><?= $siteplan_data['persen_ambil'] ?>% Wilayah</small>
        </div>
    </div>
    <div class="col-4">
        <div class="card p-2 p-md-3 border-0 shadow-sm rounded-4 text-center bg-white border-start border-4 border-danger h-100">
            <span class="text-danger small text-uppercase fw-bold" style="font-size: 0.75rem;">[✗] JANGAN DIAMBIL</span>
            <h3 class="fw-bold mb-0 text-danger" id="stat-total-tahan"><?= $siteplan_data['total_tahan'] ?></h3>
            <small class="text-danger fw-semibold">Stop / Ditahan</small>
        </div>
    </div>
</div>

<!-- Filter Periode & Switch Mode Tampilan -->
<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6 d-flex gap-2">
                <form method="GET" action="<?= base_url('siteplan-sampah') ?>" id="formFilterSampah" class="d-flex gap-2 w-100">
                    <select name="bulan" class="form-select form-select-sm rounded-pill fw-semibold" onchange="document.getElementById('formFilterSampah').submit();">
                        <?php foreach ($nama_bulan_arr as $b_num => $b_nama): ?>
                            <option value="<?= $b_num ?>" <?= ($bulan == $b_num) ? 'selected' : '' ?>>
                                Bulan: <?= $b_nama ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="tahun" class="form-select form-select-sm rounded-pill fw-semibold" style="max-width: 110px;" onchange="document.getElementById('formFilterSampah').submit();">
                        <?php 
                        $cy = (int)date('Y');
                        for ($y = $cy; $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
            <div class="col-12 col-md-6 text-md-end">
                <ul class="nav nav-pills justify-content-center justify-content-md-end gap-1" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active btn-sm rounded-pill px-3 py-1 fw-bold" id="pills-map-tab" data-bs-toggle="pill" data-bs-target="#pills-map" type="button" role="tab">
                            <i class="ti ti-map me-1"></i> Peta Siteplan
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link btn-sm rounded-pill px-3 py-1 fw-bold" id="pills-list-tab" data-bs-toggle="pill" data-bs-target="#pills-list" type="button" role="tab">
                            <i class="ti ti-list-check me-1"></i> Checklist HP (<span id="badge-count-tahan"><?= $siteplan_data['total_tahan'] ?> Ditahan</span>)
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="tab-content" id="pills-tabContent">
    <!-- ========================================== -->
    <!-- TAB 1: PETA SITEPLAN INTERAKTIF (LEAFLET)  -->
    <!-- ========================================== -->
    <div class="tab-pane fade show active" id="pills-map" role="tabpanel">
        <div class="siteplan-card position-relative mb-4">
            <!-- Peta Leaflet Map -->
            <div id="siteplan-map"></div>

            <!-- Floating Legend -->
            <div class="map-legend-sampah">
                <div class="fw-bold mb-1 text-dark" style="font-size: 11px;">KENDALI SAMPAH:</div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="legend-box-sampah box-ambil"></span>
                    <span class="fw-semibold text-dark">Boleh Diambil (Hijau)</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="legend-box-sampah box-tahan"></span>
                    <span class="fw-bold text-danger">STOP JANGAN DIAMBIL (Merah)</span>
                </div>
            </div>

            <!-- Floating Hint -->
            <div class="position-absolute top-0 start-0 m-3 z-3 bg-white bg-opacity-90 px-3 py-1 rounded-pill border shadow-sm small text-dark d-none d-sm-block">
                <i class="ti ti-pointer text-success me-1"></i> Klik kotak rumah di peta untuk ubah status sampah
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: DAFTAR CHECKLIST KHUSUS HP          -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="pills-list" role="tabpanel">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <!-- Search Box Cepat -->
                <div class="mb-3 position-relative">
                    <input type="text" id="searchHouseInput" class="form-control rounded-pill ps-4 py-2" placeholder="🔍 Cari nomor rumah / nama warga (contoh: I-3, Raya 5, Denny)..." onkeyup="filterHouseList()">
                </div>

                <!-- Tombol Filter Cepat -->
                <div class="d-flex gap-1 overflow-auto pb-2 mb-3" style="white-space: nowrap;">
                    <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 active btn-filter-pill" onclick="setQuickFilter('semua', this)">Semua (<?= $siteplan_data['total_rumah'] ?>)</button>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-filter-pill" onclick="setQuickFilter('tahan', this)">🔴 Jangan Diambil (<?= $siteplan_data['total_tahan'] ?>)</button>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 btn-filter-pill" onclick="setQuickFilter('ambil', this)">🟢 Boleh Diambil (<?= $siteplan_data['total_ambil'] ?>)</button>
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 btn-filter-pill" onclick="setQuickFilter('menunggak', this)">Iuran Nunggak</button>
                </div>

                <!-- Container List Kartu Rumah -->
                <div id="houseListContainer">
                    <?php foreach ($siteplan_data['houses'] as $h): ?>
                        <div class="card-rumah-item <?= ($h['status_sampah'] === 'ambil') ? 'item-ambil' : 'item-tahan' ?>" 
                             id="card-h-<?= $h['id'] ?>"
                             data-alamat="<?= strtolower($h['alamat']) ?>"
                             data-nama="<?= strtolower($h['nama']) ?>"
                             data-sampah="<?= $h['status_sampah'] ?>"
                             data-ipl="<?= $h['status_ipl'] ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($h['alamat']) ?></h5>
                                    <span class="text-muted small d-block"><?= htmlspecialchars($h['nama']) ?></span>
                                    <div class="mt-1">
                                        <span class="badge <?= ($h['status_ipl'] === 'lunas') ? 'bg-success' : 'bg-danger' ?> rounded-pill" style="font-size: 0.65rem;">
                                            IPL: <?= strtoupper($h['status_ipl']) ?>
                                        </span>
                                        <span class="badge bg-light text-dark border rounded-pill ms-1" style="font-size: 0.65rem;" id="badge-ket-<?= $h['id'] ?>">
                                            <?= htmlspecialchars($h['ket_sampah']) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-end ps-2">
                                    <label class="switch-sampah mb-1">
                                        <input type="checkbox" id="toggle-list-<?= $h['id'] ?>" <?= ($h['status_sampah'] === 'ambil') ? 'checked' : '' ?> onchange="onToggleSwitchChange(<?= $h['id'] ?>, this.checked)">
                                        <span class="slider-sampah"></span>
                                    </label>
                                    <span class="d-block small fw-bold" id="status-label-<?= $h['id'] ?>" style="color: <?= ($h['status_sampah'] === 'ambil') ? '#16a34a' : '#dc2626' ?>; font-size: 0.72rem;">
                                        <?= ($h['status_sampah'] === 'ambil') ? 'BOLEH' : 'TAHAN [X]' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- BOTTOM SHEET MODAL (RAMAH JEMPOL DI HP)    -->
<!-- ========================================== -->
<div class="bottom-sheet-backdrop" id="sheetBackdrop" onclick="closeBottomSheet()"></div>
<div class="mobile-bottom-sheet" id="houseBottomSheet">
    <div class="sheet-drag-handle"></div>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h4 class="fw-bold mb-0 text-dark" id="sheetAlamat">Alamat Rumah</h4>
            <span class="text-muted small" id="sheetNama">Nama Warga</span>
        </div>
        <button type="button" class="btn-close" onclick="closeBottomSheet()"></button>
    </div>

    <div class="p-2 rounded-3 bg-light d-flex justify-content-between align-items-center mb-3">
        <span class="small text-muted fw-semibold">Status IPL Bulan Ini:</span>
        <span class="badge rounded-pill px-2 py-1" id="sheetStatusIPL">Lunas</span>
    </div>

    <!-- Toggle Switch Besar -->
    <div class="card p-3 border rounded-3 mb-3 text-center" style="background: #fdfdfd;">
        <span class="text-muted small fw-bold text-uppercase mb-2 d-block">Status Pengambilan Sampah</span>
        <div class="d-flex align-items-center justify-content-center gap-3">
            <span class="fw-bold text-danger fs-3">TAHAN 🔴</span>
            <label class="switch-sampah">
                <input type="checkbox" id="sheetToggleSwitch" onchange="onSheetToggleChange(this.checked)">
                <span class="slider-sampah"></span>
            </label>
            <span class="fw-bold text-success fs-3">🟢 AMBIL</span>
        </div>
        <small class="text-muted mt-2 d-block" id="sheetStatusText">Ketuk tombol saklar untuk mengubah</small>
    </div>

    <div class="mb-3">
        <label class="form-label small fw-bold text-muted mb-1">Catatan Dispensasi / Alasan (Opsional)</label>
        <input type="text" id="sheetKeterangan" class="form-control form-control-sm rounded-3" placeholder="Contoh: Janji transfer tgl 20 / Rumah kosong">
    </div>

    <div class="d-grid gap-2">
        <button type="button" class="btn btn-success fw-bold py-2 rounded-3" onclick="saveSheetStatus()">
            <i class="ti ti-check me-1"></i> Simpan Status Rumah
        </button>
        <button type="button" class="btn btn-outline-secondary py-2 rounded-3" onclick="closeBottomSheet()">
            Tutup
        </button>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL WHATSAPP PESAN KE PETUGAS            -->
<!-- ========================================== -->
<div class="modal fade" id="modalWhatsApp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-whatsapp me-1"></i> Kirim Laporan ke Petugas Sampah
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-2">Teks berikut otomatis merangkum seluruh rumah yang <strong>JANGAN DIAMBIL</strong> sampahnya pada periode <?= htmlspecialchars($periode_label) ?>:</p>
                <textarea id="waMessageText" class="form-control rounded-3 mb-3 small" rows="8" readonly style="background: #f8fafc; font-family: monospace;"></textarea>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary w-50 rounded-pill fw-semibold" onclick="copyWhatsAppText()">
                        <i class="ti ti-copy me-1"></i> Salin Teks
                    </button>
                    <a id="btnSendWA" href="#" target="_blank" class="btn btn-success w-50 rounded-pill fw-bold">
                        <i class="bi bi-whatsapp me-1"></i> Buka WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notifikasi Cepat -->
<div class="position-fixed top-0 end-0 p-3" style="z-index: 1100">
    <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0 rounded-3 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="toastMessage">
                Status sampah berhasil diperbarui!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

<!-- Leaflet JS Local (Sesuai dengan nama file di assets/siteplan/leaflet) -->
<script src="<?= base_url('assets/siteplan/leaflet/leaflet.js') ?>"></script>
<script src="<?= base_url('assets/siteplan/leaflet/Leaflet.fullscreen.min.js') ?>"></script>

<script>
    // Data Rumah Awal dari Controller
    let housesData = <?= $houses_json ?>;
    const currentPeriode = "<?= sprintf('%04d-%02d', $tahun, $bulan) ?>";
    const currentPeriodeLabel = "<?= $periode_label ?>";

    let map = null;
    let polygonLayerGroup = null;
    let labelLayerGroup = null;
    let housePolygonsMap = {};
    let activeSheetHouseId = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function showToast(msg, isSuccess = true) {
        const toastEl = document.getElementById('toastSuccess');
        if (!toastEl) return;
        document.getElementById('toastMessage').innerText = msg;
        toastEl.className = 'toast align-items-center text-white border-0 rounded-3 shadow ' + (isSuccess ? 'bg-success' : 'bg-danger');
        const toastInstance = new bootstrap.Toast(toastEl, { delay: 2500 });
        toastInstance.show();
    }

    // Inisialisasi Peta Leaflet
    function initMap() {
        if (map !== null) return;

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

        const tileUrl = "<?= base_url('assets/siteplan/tsi/tiles/{z}/{x}/{y}.png') ?>";
        L.tileLayer(tileUrl, {
            minZoom: 2,
            maxZoom: 5,
            noWrap: true,
            tms: false
        }).addTo(map);

        polygonLayerGroup = new L.LayerGroup().addTo(map);
        labelLayerGroup = new L.LayerGroup().addTo(map);

        renderPolygons();

        // Update teks label saat di-zoom
        map.on('zoomend', updateHouseLabels);

        // Jika tab di-switch, pastikan Leaflet invalidateSize agar ukuran peta pas
        if (window.jQuery) {
            $('button[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
                if (map) {
                    map.invalidateSize();
                    updateHouseLabels();
                }
            });
        }
    }

    function getHouseColor(status_sampah) {
        if (status_sampah === 'ambil') {
            return {
                fillColor: '#22c55e',
                color: '#15803d',
                fillOpacity: 0.65,
                weight: 1.8
            };
        } else {
            return {
                fillColor: '#ef4444',
                color: '#b91c1c',
                fillOpacity: 0.78,
                weight: 2
            };
        }
    }

    // Render Poligon Seluruh Rumah
    function renderPolygons() {
        if (!polygonLayerGroup) return;
        polygonLayerGroup.clearLayers();
        housePolygonsMap = {};

        housesData.forEach(h => {
            if (!h.has_coords || !h.polygon_coords || !Array.isArray(h.polygon_coords) || h.polygon_coords.length < 3) {
                return;
            }

            const style = getHouseColor(h.status_sampah);
            const polygon = L.polygon(h.polygon_coords, style);

            const isAmbil = (h.status_sampah === 'ambil');
            polygon.bindTooltip(`<b>${escapeHtml(h.alamat)}</b><br>${escapeHtml(h.nama || '-')}<br><span class="badge ${isAmbil ? 'bg-success' : 'bg-danger'}">${isAmbil ? 'Sampah Boleh Diambil' : 'STOP JANGAN DIAMBIL'}</span>`, {
                sticky: true,
                direction: 'top'
            });

            // Hover highlight effect
            polygon.on('mouseover', function(e) {
                this.setStyle({ weight: 3, color: '#f59e0b', fillOpacity: 0.88 });
            });
            polygon.on('mouseout', function(e) {
                const curH = housesData.find(item => item.id == h.id) || h;
                this.setStyle(getHouseColor(curH.status_sampah));
            });

            // Klik Rumah: Buka Editor Status Sampah
            polygon.on('click', function(e) {
                L.DomEvent.stopPropagation(e);
                openHouseEditor(h.id);
            });

            polygon.addTo(polygonLayerGroup);
            housePolygonsMap[h.id] = polygon;
        });

        updateHouseLabels();
    }

    // Tampilkan Nama Warga / Nomor Rumah di dalam Kotak Kavling (Persis Seperti di Siteplan IPL)
    function updateHouseLabels() {
        if (!map || !labelLayerGroup) return;
        labelLayerGroup.clearLayers();

        const currentZoom = map.getZoom();
        // Pada zoom 2 sampai 5, render label agar selalu terbaca
        let fontSize = (currentZoom >= 5) ? '11px' : (currentZoom >= 4 ? '9px' : (currentZoom >= 3 ? '7.5px' : '6.5px'));

        housesData.forEach(house => {
            if (!house.has_coords || !house.polygon_coords || house.polygon_coords.length < 3) return;
            const poly = housePolygonsMap[house.id];
            if (!poly) return;

            const bounds = poly.getBounds();
            if (!bounds || !bounds.isValid()) return;

            const nw = map.latLngToLayerPoint(bounds.getNorthWest());
            const se = map.latLngToLayerPoint(bounds.getSouthEast());
            const boxW = Math.abs(se.x - nw.x);
            const boxH = Math.abs(se.y - nw.y);

            if (boxW < 14 || boxH < 10) return;

            const labelW = Math.max(14, Math.floor(boxW * 0.92));
            const labelH = Math.max(10, Math.floor(boxH * 0.90));

            // Ambil nama warga (jika kosong atau belum ada, gunakan alamat/nomor rumah)
            let rawName = (house.nama && house.nama.trim() !== '' && !house.nama.toLowerCase().includes('belum')) 
                ? house.nama.trim() 
                : house.alamat;

            const safeName = escapeHtml(rawName);
            const center = bounds.getCenter();

            const labelIcon = L.divIcon({
                className: 'house-map-label-wrapper',
                html: `<div class="house-map-label" 
                            style="width: ${labelW}px; height: ${labelH}px; max-width: ${labelW}px; max-height: ${labelH}px; font-size: ${fontSize};"
                            title="${escapeHtml(house.alamat + ' - ' + (house.nama || ''))}">
                            <span style="display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word; line-height: 1.15; text-align: center;">${safeName}</span>
                       </div>`,
                iconSize: [labelW, labelH],
                iconAnchor: [labelW / 2, labelH / 2]
            });

            const marker = L.marker(center, {
                icon: labelIcon,
                interactive: false,
                keyboard: false,
                zIndexOffset: 400
            });

            marker.addTo(labelLayerGroup);
        });
    }

    // Buka Editor / Bottom Sheet Rumah
    function openHouseEditor(houseId) {
        const house = housesData.find(item => item.id == houseId);
        if (!house) return;

        activeSheetHouseId = houseId;

        document.getElementById('sheetAlamat').innerText = house.alamat;
        document.getElementById('sheetNama').innerText = house.nama + ' (' + house.koordinator + ')';

        const iplBadge = document.getElementById('sheetStatusIPL');
        iplBadge.innerText = (house.status_ipl === 'lunas') ? 'LUNAS IPL ✓' : 'MENUNGGAK IPL ✗';
        iplBadge.className = 'badge rounded-pill px-2 py-1 ' + ((house.status_ipl === 'lunas') ? 'bg-success' : 'bg-danger');

        const switchEl = document.getElementById('sheetToggleSwitch');
        switchEl.checked = (house.status_sampah === 'ambil');

        document.getElementById('sheetKeterangan').value = house.ket_sampah || '';
        updateSheetStatusText(switchEl.checked);

        // Buka panel
        document.getElementById('sheetBackdrop').classList.add('show');
        document.getElementById('houseBottomSheet').classList.add('show');
    }

    function closeBottomSheet() {
        document.getElementById('sheetBackdrop').classList.remove('show');
        document.getElementById('houseBottomSheet').classList.remove('show');
        activeSheetHouseId = null;
    }

    function onSheetToggleChange(isChecked) {
        updateSheetStatusText(isChecked);
    }

    function updateSheetStatusText(isAmbil) {
        const textEl = document.getElementById('sheetStatusText');
        if (isAmbil) {
            textEl.innerHTML = '<span class="text-success fw-bold">Status: BOLEH DIAMBIL</span> (Petugas akan mengangkut sampah)';
        } else {
            textEl.innerHTML = '<span class="text-danger fw-bold">Status: STOP JANGAN DIAMBIL</span> (Petugas akan melewati rumah ini)';
        }
    }

    function saveSheetStatus() {
        if (!activeSheetHouseId) return;

        const isChecked = document.getElementById('sheetToggleSwitch').checked;
        const statusSampah = isChecked ? 'ambil' : 'tahan';
        const keterangan = document.getElementById('sheetKeterangan').value.trim();

        executeToggleSampah(activeSheetHouseId, statusSampah, keterangan, function() {
            closeBottomSheet();
        });
    }

    // Toggle dari Tab Checklist HP
    function onToggleSwitchChange(houseId, isChecked) {
        const statusSampah = isChecked ? 'ambil' : 'tahan';
        executeToggleSampah(houseId, statusSampah, '', null);
    }

    // Eksekusi AJAX Toggle Status Sampah
    function executeToggleSampah(houseId, statusSampah, keterangan, callback) {
        const formData = new FormData();
        formData.append('id_rumah', houseId);
        formData.append('periode_bulan', currentPeriode);
        formData.append('status_sampah', statusSampah);
        formData.append('keterangan', keterangan);

        fetch("<?= base_url('siteplan-sampah/toggle') ?>", {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                // 1. Update data lokal
                const house = housesData.find(item => item.id == houseId);
                if (house) {
                    house.status_sampah = statusSampah;
                    house.has_override = true;
                    if (keterangan) house.ket_sampah = keterangan;
                }

                // 2. Update warna poligon di peta secara langsung
                if (housePolygonsMap[houseId]) {
                    const newStyle = getHouseColor(statusSampah);
                    housePolygonsMap[houseId].setStyle(newStyle);
                }

                // 3. Update elemen di Checklist HP
                const card = document.getElementById('card-h-' + houseId);
                if (card) {
                    card.className = 'card-rumah-item ' + ((statusSampah === 'ambil') ? 'item-ambil' : 'item-tahan');
                    card.setAttribute('data-sampah', statusSampah);
                }
                const toggleList = document.getElementById('toggle-list-' + houseId);
                if (toggleList) {
                    toggleList.checked = (statusSampah === 'ambil');
                }
                const labelStatus = document.getElementById('status-label-' + houseId);
                if (labelStatus) {
                    labelStatus.innerText = (statusSampah === 'ambil') ? 'BOLEH' : 'TAHAN [X]';
                    labelStatus.style.color = (statusSampah === 'ambil') ? '#16a34a' : '#dc2626';
                }

                // 4. Hitung ulang KPI
                recalculateKPI();

                showToast(res.message, true);
                if (callback) callback();
            } else {
                showToast(res.message || 'Gagal mengubah status', false);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Terjadi kesalahan koneksi server.', false);
        });
    }

    function recalculateKPI() {
        let totalAmbil = 0;
        let totalTahan = 0;
        housesData.forEach(h => {
            if (h.status_sampah === 'ambil') totalAmbil++;
            else totalTahan++;
        });

        const totalRumah = housesData.length;
        const persenAmbil = (totalRumah > 0) ? Math.round((totalAmbil / totalRumah) * 100) : 0;

        document.getElementById('stat-total-ambil').innerText = totalAmbil;
        document.getElementById('stat-total-tahan').innerText = totalTahan;
        document.getElementById('stat-persen-ambil').innerText = persenAmbil + '% Wilayah';
        document.getElementById('badge-count-tahan').innerText = totalTahan + ' Ditahan';
    }

    // Filter Cepat Checklist HP
    function filterHouseList() {
        const query = document.getElementById('searchHouseInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.card-rumah-item');

        cards.forEach(card => {
            const alamat = card.getAttribute('data-alamat') || '';
            const nama = card.getAttribute('data-nama') || '';
            const match = (alamat.includes(query) || nama.includes(query));
            card.style.display = match ? 'block' : 'none';
        });
    }

    function setQuickFilter(type, btnEl) {
        document.querySelectorAll('.btn-filter-pill').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');

        const cards = document.querySelectorAll('.card-rumah-item');
        cards.forEach(card => {
            const sampah = card.getAttribute('data-sampah');
            const ipl = card.getAttribute('data-ipl');

            if (type === 'semua') {
                card.style.display = 'block';
            } else if (type === 'ambil') {
                card.style.display = (sampah === 'ambil') ? 'block' : 'none';
            } else if (type === 'tahan') {
                card.style.display = (sampah === 'tahan') ? 'block' : 'none';
            } else if (type === 'menunggak') {
                card.style.display = (ipl === 'menunggak') ? 'block' : 'none';
            }
        });
    }

    // Modal WhatsApp Generator
    function openWhatsAppModal() {
        const tahanList = housesData.filter(h => h.status_sampah === 'tahan');
        tahanList.sort((a, b) => a.alamat.localeCompare(b.alamat, undefined, { numeric: true, sensitivity: 'base' }));

        let text = "*PENGUMUMAN OPERASIONAL PENGANGKUTAN SAMPAH TSI*\n";
        text += "Periode: *" + currentPeriodeLabel + "*\n";
        text += "Tanggal: " + new Date().toLocaleDateString('id-ID') + "\n\n";
        text += "Kepada Yth. Petugas Kebersihan / Pos Satpam TSI,\n";
        text += "Berikut adalah daftar rumah yang *JANGAN DIAMBIL SAMPAHNYA* (Stop/Ditahan):\n\n";

        if (tahanList.length === 0) {
            text += "✓ *NIL* (Alhamdulillah semua rumah boleh diambil sampahnya)\n\n";
        } else {
            tahanList.forEach((h, idx) => {
                text += (idx + 1) + ". *" + h.alamat + "* - " + h.nama;
                if (h.ket_sampah) text += " (" + h.ket_sampah + ")";
                text += "\n";
            });
            text += "\n*Total Rumah Ditahan: " + tahanList.length + " Unit.*\n";
        }

        text += "\nMohon petugas hanya mengambil rumah yang bertanda hijau di peta/daftar. Terima kasih atas kerjasamanya! 🙏";

        document.getElementById('waMessageText').value = text;
        document.getElementById('btnSendWA').href = "https://wa.me/?text=" + encodeURIComponent(text);

        const waModal = new bootstrap.Modal(document.getElementById('modalWhatsApp'));
        waModal.show();
    }

    function copyWhatsAppText() {
        const copyText = document.getElementById("waMessageText");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(copyText.value);
        showToast("Teks WhatsApp berhasil disalin!", true);
    }

    // Jalankan inisialisasi peta secara andal
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMap);
    } else {
        initMap();
    }
</script>
