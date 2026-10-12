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
        background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
        color: white;
        padding: 16px 20px;
    }
    .popup-header-lunas {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: white;
        padding: 16px 20px;
    }
    .popup-body {
        padding: 14px 16px;
        background: #ffffff;
    }

    /* Styling Elemen Anggota Keluarga & Modal Proper */
    .family-member-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 14px;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .family-member-item:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .gender-avatar-male {
        background: #e0f2fe;
        color: #0284c7;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 17px;
    }
    .gender-avatar-female {
        background: #fce7f3;
        color: #db2777;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 17px;
    }
    .tunggak-pill-item {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
        background: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fca5a5;
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
        border: 2.5px solid #ffffff !important;
        border-radius: 4px !important;
        width: 18px !important;
        height: 18px !important;
        margin-left: -9px !important;
        margin-top: -9px !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.5) !important;
        cursor: crosshair !important;
        z-index: 9999 !important;
        pointer-events: auto !important;
        touch-action: none !important;
        transition: transform 0.1s ease, background 0.1s ease;
    }
    .resize-handle-icon:hover, .resize-handle-icon:active {
        background: #f59e0b !important;
        transform: scale(1.3);
    }

    /* =========================================
       OPTIMASI KHUSUS MOBILE / KOORDINATOR KELILING
       ========================================= */
    .mobile-map-fab-container {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 8px;
        pointer-events: auto;
    }

    .mobile-fab-btn {
        border-radius: 30px !important;
        padding: 8px 14px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        border: 1px solid rgba(255,255,255,0.4) !important;
        color: #ffffff !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        backdrop-filter: blur(8px);
        min-height: 42px !important;
        cursor: pointer !important;
        user-select: none !important;
    }
    .mobile-fab-btn:active {
        transform: scale(0.92) !important;
    }
    .mobile-fab-recenter {
        background: linear-gradient(135deg, #1d4ed8, #2563eb) !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.45) !important;
    }
    .mobile-fab-toggle-red {
        background: linear-gradient(135deg, #b91c1c, #ef4444) !important;
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.45) !important;
    }

    @media (min-width: 769px) {
        .mobile-map-fab-container {
            display: none !important;
        }
        .map-legend .legend-chevron {
            display: none !important;
        }
        .map-legend .legend-content {
            display: block !important;
        }
    }

    @media (max-width: 768px) {
        /* Peta responsif di layar ponsel */
        #siteplan-map {
            height: 62vh !important;
            min-height: 420px !important;
            border-radius: 0 0 12px 12px !important;
        }

        /* Toolbar atas: horizontal scroll pill buttons agar rapi di HP */
        .siteplan-top-toolbar {
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding-bottom: 6px !important;
            width: 100% !important;
            scrollbar-width: thin;
        }
        .siteplan-top-toolbar::-webkit-scrollbar {
            height: 4px;
        }
        .siteplan-top-toolbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .siteplan-top-toolbar .btn {
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            padding: 8px 15px !important;
            font-size: 13px !important;
            border-radius: 50px !important;
            min-height: 40px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
        }

        /* Tombol Floating Mobile: Dibuat kompak melingkar (FAB icon-only) agar tidak menutupi peta */
        .mobile-map-fab-container {
            top: 10px !important;
            right: 10px !important;
            gap: 8px !important;
        }
        .mobile-fab-btn {
            width: 36px !important;
            height: 36px !important;
            min-height: 36px !important;
            padding: 0 !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: 0 3px 10px rgba(0,0,0,0.25) !important;
        }
        .mobile-fab-btn i {
            font-size: 18px !important;
            margin: 0 !important;
        }
        .mobile-fab-label {
            display: none !important; /* Sembunyikan teks di mobile agar tombol berbentuk bulatan rapi */
        }

        /* Legenda peta mobile: Collapsible & Sangat Kompak */
        .map-legend {
            bottom: 10px !important;
            left: 10px !important;
            padding: 6px 10px !important;
            font-size: 11px !important;
            max-width: 220px !important;
            background: rgba(255, 255, 255, 0.94) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18) !important;
            border-radius: 8px !important;
            transition: all 0.2s ease !important;
            user-select: none !important;
        }
        .map-legend.collapsed-mobile {
            padding: 4px 10px !important;
            border-radius: 20px !important;
            background: rgba(255, 255, 255, 0.92) !important;
        }
        .map-legend.collapsed-mobile .legend-content {
            display: none !important;
        }
        .map-legend.collapsed-mobile .legend-chevron {
            transform: rotate(180deg) !important;
        }
        .map-legend .legend-box {
            width: 13px !important;
            height: 13px !important;
        }
        .map-legend .fw-bold {
            font-size: 11px !important;
            margin-bottom: 0 !important;
        }
        .legend-item {
            margin-bottom: 2px !important;
        }

        /* Tombol Zoom Leaflet lebih kompak agar tidak memakan ruang sentuh */
        .leaflet-touch .leaflet-bar a {
            width: 32px !important;
            height: 32px !important;
            line-height: 32px !important;
            font-size: 16px !important;
            border-radius: 6px !important;
        }

        /* Bottom Sheet Modal untuk Detail Rumah */
        .modal-dialog-bottom-sheet {
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            transform: translateY(100%);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        .modal.show .modal-dialog-bottom-sheet {
            transform: translateY(0) !important;
        }
        .modal-dialog-bottom-sheet .modal-content {
            border-radius: 24px 24px 0 0 !important;
            border: none !important;
            max-height: 86vh !important;
            overflow-y: auto !important;
            box-shadow: 0 -10px 35px rgba(0,0,0,0.3) !important;
        }
        .bottom-sheet-handle-bar {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px 0 2px 0;
            background: inherit;
        }
        .bottom-sheet-handle {
            width: 44px;
            height: 5px;
            border-radius: 4px;
            background: rgba(255,255,255,0.65);
        }

        /* Tombol aksi mobile jempol-friendly */
        .mobile-touch-btn {
            min-height: 48px !important;
            border-radius: 12px !important;
            font-size: 15px !important;
            font-weight: 600 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .mobile-touch-btn-primary {
            min-height: 52px !important;
            font-size: 16px !important;
            font-weight: 700 !important;
            border-radius: 14px !important;
            box-shadow: 0 4px 14px rgba(13, 110, 253, 0.35) !important;
        }
    }

    /* =========================================
       TEXT NAMA KAVLING LANGSUNG DI DALAM KOTAK (MURNI TEKS, TEPAT DI TENGAH KOTAK)
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
        background: transparent !important; /* Tidak membuat kotak lagi */
        border: none !important;            /* Hapus border kotak */
        box-shadow: none !important;        /* Hapus shadow kotak */
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        pointer-events: none !important;
        user-select: none !important;
        
        /* Auto Wrapping agar pas di dalam kotak kavling */
        word-wrap: break-word !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
        white-space: normal !important;
        overflow: hidden !important;
        
        /* Outline teks pekat agar sangat kontras dan jelas terbaca di atas kotak kavling */
        text-shadow: 1px 1px 2px #000, -1px -1px 2px #000, 1px -1px 2px #000, -1px 1px 2px #000, 0 0 3px #000 !important;
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
                    <div class="d-flex align-items-center gap-2 flex-wrap siteplan-top-toolbar">
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
                        <?php if (!empty($log_siteplan)): ?>
                        <button type="button" class="btn btn-info btn-sm d-flex align-items-center gap-1 text-white" data-bs-toggle="modal" data-bs-target="#modalLogAksesSiteplan" title="Lihat koordinator yang sudah membuka peta siteplan">
                            <i class="ti ti-activity"></i>
                            <span>Log Akses Koordinator</span>
                            <span class="badge bg-white text-info ms-1"><?= $log_siteplan['sudah_akses'] ?>/<?= $log_siteplan['total_koor'] ?></span>
                        </button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin && !empty($log_siteplan)): ?>
<!-- Alert Monitoring Akses Koordinator (Hanya Muncul di Admin) -->
<div class="alert alert-info border-info d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3 rounded-3 mb-3 shadow-sm">
    <div class="d-flex align-items-center gap-2">
        <div class="p-2 bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
            <i class="ti ti-user-check fs-4"></i>
        </div>
        <div>
            <span class="fw-bold text-dark">Monitoring Akses Koordinator:</span>
            <span class="text-dark ms-1">
                <strong><?= $log_siteplan['sudah_akses'] ?></strong> dari <strong><?= $log_siteplan['total_koor'] ?></strong> Koordinator Blok sudah membuka siteplan (<?= $log_siteplan['persen_akses'] ?>%).
                <?php if ($log_siteplan['belum_akses'] > 0): ?>
                    <span class="text-danger fw-semibold ms-1">(<?= $log_siteplan['belum_akses'] ?> belum membuka)</span>
                <?php else: ?>
                    <span class="text-success fw-semibold ms-1">(Semua sudah membuka ✓)</span>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <button type="button" class="btn btn-info btn-sm text-white px-3 rounded-pill fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#modalLogAksesSiteplan">
        <i class="ti ti-list-details me-1"></i> Rincian Jam &amp; Device Akses
    </button>
</div>
<?php endif; ?>

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

        <!-- Mobile Floating Controls (Koordinator Keliling) -->
        <div class="mobile-map-fab-container">
            <button type="button" class="btn mobile-fab-btn mobile-fab-recenter shadow" onclick="focusFilteredPolygons()" title="Pusatkan ke Blok Saya">
                <i class="ti ti-crosshair fs-4"></i>
                <span class="mobile-fab-label">Blok Saya</span>
            </button>
            <button type="button" class="btn mobile-fab-btn mobile-fab-toggle-red shadow" id="mobile-fab-red" onclick="toggleRedBoxes()" title="Sembunyikan/Tampilkan Merah">
                <i class="ti ti-eye-off fs-4" id="mobile-fab-red-icon"></i>
                <span class="mobile-fab-label" id="mobile-fab-red-text">Menunggak</span>
            </button>
        </div>

        <!-- Floating Legend (Collapsible on Mobile) -->
        <div class="map-legend collapsed-mobile" id="map-legend">
            <div class="legend-header d-flex align-items-center justify-content-between" onclick="toggleLegendCollapse()" title="Klik untuk buka/tutup legenda" style="cursor: pointer;">
                <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 11px;">
                    <i class="ti ti-info-circle text-primary"></i> Legenda
                </span>
                <i class="ti ti-chevron-up ms-2 text-muted legend-chevron" id="legend-chevron" style="transition: transform 0.2s ease;"></i>
            </div>
            <div class="legend-content" id="legend-content">
                <div class="legend-item mt-2">
                    <div class="legend-box box-menunggak"></div>
                    <span><strong>Merah:</strong> Belum Bayar IPL</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box box-lunas"></div>
                    <span><strong>Hijau:</strong> Lunas IPL</span>
                </div>
                <div class="legend-item">
                    <div class="legend-box box-unplotted"></div>
                    <span class="text-muted"><small>Kotak belum di-plot (<span id="unplotted-count">0</span>)</small></span>
                </div>
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
                <strong>Cara Mengatur:</strong><br>
                1. <strong>Klik &amp; geser kotak</strong> untuk memindah seluruh kavling.<br>
                2. <strong>Tarik titik sudut biru</strong> secara bebas &amp; dinamis untuk menyesuaikan tiap sudut kavling (bisa persegi, trapesium, serong).<br>
                3. Klik <strong>Luruskan Kotak</strong> jika ingin merapikan bentuk menjadi persegi tegak.<br>
                4. Klik <strong>Simpan Posisi</strong> setelah selesai.
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm w-100 mb-2" id="btn-straighten" onclick="straightenSelectedPolygon()" style="display: none;">
                <i class="ti ti-layout-align-middle"></i> Luruskan Kotak Terpilih (Persegi)
            </button>
            <button type="button" class="btn btn-success btn-sm w-100" onclick="saveAllDraggedPolygons()">
                <i class="ti ti-device-floppy"></i> Simpan Posisi (<span id="drag-count">0</span>)
            </button>
        </div>
    </div>
</div>

<!-- Modal Rincian Rumah, Keluarga & Pembayaran (Optimasi Proper Desktop & Mobile Bottom-Sheet) -->
<div class="modal fade" id="modalHouseDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-bottom-sheet modal-lg">
        <div class="modal-content shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
            <!-- Drag handle bar untuk mobile -->
            <div class="bottom-sheet-handle-bar d-md-none" id="modal-handle-bar">
                <div class="bottom-sheet-handle"></div>
            </div>

            <!-- Header Modal Cantik Bergradasi -->
            <div class="modal-header text-white px-3 px-md-4 py-3" id="modal-house-header">
                <div class="d-flex align-items-center gap-3 w-100 pe-2">
                    <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center text-white flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="ti ti-home fs-6"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="modal-title fw-bold text-white mb-0" id="modal-house-title">-</h4>
                            <span id="modal-status-badge-header"></span>
                        </div>
                        <p class="text-white-50 mb-0 small" id="modal-sub-info">Perumahan Taman Sukodono Indah</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4" style="background-color: #f8fafc; max-height: calc(85vh - 110px); overflow-y: auto;">
                
                <!-- 1. KARTU PROFIL PENGHUNI UTAMA & STATUS KEPEMILIKAN -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle text-primary bg-primary-subtle border border-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold fs-5 shadow-sm" id="modal-avatar-initial" style="width: 50px; height: 50px;">
                                <i class="ti ti-user"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">
                                    <h5 class="fw-bold text-dark mb-0 text-truncate" id="modal-nama">-</h5>
                                    <span class="badge bg-light text-secondary border px-2.5 py-1 rounded-pill" id="modal-status-rumah" style="display: none;">-</span>
                                </div>
                                
                                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-2">
                                    <span class="d-inline-flex align-items-center gap-1.5" id="modal-koordinator-wrap">
                                        <i class="ti ti-shield-check text-primary fs-4"></i>
                                        <span id="modal-koordinator">-</span>
                                    </span>
                                    <span class="d-inline-flex align-items-center gap-1.5" id="modal-nokk-wrap" style="display: none;">
                                        <i class="ti ti-id-badge-2 text-info fs-4"></i>
                                        <span>No. KK: <strong id="modal-nokk" class="text-dark">-</strong></span>
                                    </span>
                                    <span class="d-inline-flex align-items-center gap-1.5" id="modal-telepon-display">
                                        <i class="ti ti-phone text-success fs-4"></i>
                                        <span id="modal-telepon-text">-</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. KARTU STATUS KEUANGAN & TUNGGAKAN IPL -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-uppercase fw-bold text-muted" style="font-size: 11px; letter-spacing: 0.5px;">
                                <i class="ti ti-receipt-2 me-1 text-primary"></i> Status Pembayaran IPL
                            </span>
                            <div id="modal-status-badge"></div>
                        </div>

                        <!-- Box Tunggakan -->
                        <div id="modal-tunggak-box" class="p-3 bg-danger-subtle border border-danger-subtle rounded-3 mt-2" style="display: none;">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-danger fw-bold fs-3">
                                    <i class="ti ti-alert-triangle me-1"></i> Rincian Bulan Tunggakan:
                                </span>
                                <span class="badge bg-danger rounded-pill px-2.5 py-1" id="modal-tunggak-count">0 Bulan</span>
                            </div>
                            <div class="d-flex flex-wrap gap-1 mt-2" id="modal-tunggak-list-pills">
                                <!-- Pills rendered via JS -->
                            </div>
                        </div>

                        <!-- Box Lunas -->
                        <div id="modal-lunas-box" class="p-2.5 bg-success-subtle border border-success-subtle rounded-3 mt-2" style="display: none;">
                            <div class="d-flex align-items-center gap-2 text-success fw-semibold">
                                <i class="ti ti-circle-check-filled fs-5"></i>
                                <span>Iuran IPL rumah ini telah <strong>LUNAS</strong> untuk periode yang dipilih.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. SECTION ANGGOTA KELUARGA (FITUR UTAMA) -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white">
                    <div class="card-header bg-transparent border-0 pt-3 pb-2 px-3 px-md-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded p-1 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                <i class="ti ti-users-group"></i>
                            </div>
                            <span class="fw-bold text-dark fs-3">Rincian Anggota Keluarga</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1" id="modal-anggota-count">0 Jiwa</span>
                    </div>
                    <div class="card-body p-3 p-md-4 pt-1">
                        <!-- List Anggota Keluarga -->
                        <div id="modal-family-list" class="d-flex flex-column gap-2">
                            <!-- Items dinamis di-render via JS -->
                        </div>
                        
                        <!-- Empty State Jika Belum Terdata -->
                        <div id="modal-family-empty" class="text-center py-4 px-3 bg-light rounded-3 border border-dashed" style="display: none;">
                            <div class="mx-auto mb-2 text-muted opacity-50">
                                <i class="ti ti-users fs-8"></i>
                            </div>
                            <h6 class="fw-semibold text-secondary mb-1">Belum Ada Data Rincian Keluarga</h6>
                            <p class="text-muted small mb-0">Rincian anggota keluarga penghuni unit kavling ini belum diinput ke database.</p>
                        </div>
                    </div>
                </div>

                <!-- 4. TOMBOL AKSI CEPAT LAPANGAN -->
                <div class="card border-0 shadow-sm rounded-3 bg-white">
                    <div class="card-body p-3 p-md-3">
                        <div class="d-flex flex-column gap-2">
                            <!-- Tombol Bayar / Kasir IPL -->
                            <a href="#" id="modal-btn-pay" class="btn btn-primary w-100 mobile-touch-btn mobile-touch-btn-primary gap-2 shadow-sm">
                                <i class="ti ti-cash fs-5"></i>
                                <span>Input Pembayaran IPL Sekarang</span>
                            </a>

                            <!-- Tombol WhatsApp & Telepon -->
                            <div class="row g-2" id="modal-action-row">
                                <div class="col-6" id="modal-wa-col">
                                    <a href="#" target="_blank" id="modal-btn-wa" class="btn btn-success w-100 mobile-touch-btn gap-2 shadow-sm" style="background-color: #25D366; border-color: #25D366;">
                                        <i class="bi bi-whatsapp fs-5"></i>
                                        <span>WhatsApp</span>
                                    </a>
                                </div>
                                <div class="col-6" id="modal-tel-col">
                                    <a href="#" id="modal-btn-call" class="btn btn-outline-primary w-100 mobile-touch-btn gap-2">
                                        <i class="ti ti-phone-call fs-5"></i>
                                        <span>Telepon</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php if ($isAdmin && !empty($log_siteplan)): ?>
<!-- Modal Log Akses Siteplan (Khusus Admin) -->
<div class="modal fade" id="modalLogAksesSiteplan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="ti ti-history fs-5"></i>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Log Akses Siteplan Koordinator</h5>
                        <p class="text-white-50 mb-0 small">Monitoring aktivitas koordinator membuka denah peta wilayah</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Ringkasan KPI Akses -->
                <div class="row g-2 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-primary border border-primary-subtle rounded-3 h-100">
                            <span class="text-muted fs-2 text-uppercase fw-semibold">Total Koordinator</span>
                            <h3 class="fw-bold mb-0 text-primary"><?= $log_siteplan['total_koor'] ?> <small class="fs-3 text-muted">Orang</small></h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-success border border-success-subtle rounded-3 h-100">
                            <span class="text-success fs-2 text-uppercase fw-semibold">Sudah Mengakses</span>
                            <h3 class="fw-bold mb-0 text-success"><?= $log_siteplan['sudah_akses'] ?> <small class="fs-3">(<?= $log_siteplan['persen_akses'] ?>%)</small></h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light-danger border border-danger-subtle rounded-3 h-100">
                            <span class="text-danger fs-2 text-uppercase fw-semibold">Belum Pernah Akses</span>
                            <h3 class="fw-bold mb-0 text-danger"><?= $log_siteplan['belum_akses'] ?> <small class="fs-3 text-muted">Orang</small></h3>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light border rounded-3 h-100">
                            <span class="text-muted fs-2 text-uppercase fw-semibold">Riwayat Kunjungan</span>
                            <h3 class="fw-bold mb-0 text-dark"><?= count($log_siteplan['riwayat_terbaru']) ?>+ <small class="fs-3 text-muted">Hits</small></h3>
                        </div>
                    </div>
                </div>

                <!-- Nav Tabs -->
                <ul class="nav nav-pills nav-fill mb-3 p-1 bg-light rounded-3" id="logTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold py-2" id="tab-status-koor" data-bs-toggle="tab" data-bs-target="#content-status-koor" type="button" role="tab">
                            <i class="ti ti-users me-1"></i> Status Keaktifan Koordinator
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-semibold py-2" id="tab-riwayat-akses" data-bs-toggle="tab" data-bs-target="#content-riwayat-akses" type="button" role="tab">
                            <i class="ti ti-clock-history me-1"></i> Riwayat Akses Real-Time
                        </button>
                    </li>
                </ul>

                <!-- Tab Contents -->
                <div class="tab-content" id="logTabsContent">
                    <!-- Tab 1: Status Per Koordinator -->
                    <div class="tab-pane fade show active" id="content-status-koor" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 border">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama Koordinator Blok</th>
                                        <th class="text-center" style="width: 140px;">Status</th>
                                        <th>Kunjungan Terakhir (WIB)</th>
                                        <th>Perangkat / Device</th>
                                        <th class="text-center" style="width: 90px;">Total Buka</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $noKoor = 1;
                                    foreach ($log_siteplan['daftar_koor'] as $dk): 
                                        $sudahBuka = !empty($dk['terakhir_akses']);
                                    ?>
                                    <tr>
                                        <td><?= $noKoor++ ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($dk['nama_koordinator']) ?></div>
                                            <small class="text-muted">ID: #<?= $dk['id_koordinator'] ?></small>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($sudahBuka): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                    <i class="ti ti-check me-1"></i> Sudah Akses
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                    <i class="ti ti-x me-1"></i> Belum Buka
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sudahBuka): ?>
                                                <div class="fw-semibold text-dark">
                                                    <i class="ti ti-calendar-time text-primary me-1"></i>
                                                    <?= date('d M Y, H:i', strtotime($dk['terakhir_akses'])) ?> WIB
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">- Belum pernah membuka -</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($dk['last_device'])): ?>
                                                <span class="badge bg-light text-dark border">
                                                    <?= htmlspecialchars($dk['last_device']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?= $dk['total_akses'] > 0 ? 'bg-primary' : 'bg-secondary' ?> rounded-pill px-2">
                                                <?= (int)$dk['total_akses'] ?>x
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Riwayat Real-Time -->
                    <div class="tab-pane fade" id="content-riwayat-akses" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 border">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Pengguna / Koordinator</th>
                                        <th>Role</th>
                                        <th>Waktu Akses (WIB)</th>
                                        <th>Perangkat</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $noRiw = 1;
                                    foreach ($log_siteplan['riwayat_terbaru'] as $rw): 
                                    ?>
                                    <tr>
                                        <td><?= $noRiw++ ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($rw['nama']) ?></div>
                                            <?php if (!empty($rw['blok'])): ?>
                                                <small class="text-primary"><i class="ti ti-map-pin me-1"></i><?= htmlspecialchars($rw['blok']) ?></small>
                                            <?php else: ?>
                                                <small class="text-muted"><?= htmlspecialchars($rw['username'] ?? '-') ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $rw['role'] == 'koordinator' ? 'bg-success' : 'bg-primary' ?> text-uppercase" style="font-size:0.7rem;">
                                                <?= htmlspecialchars($rw['role']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">
                                                <i class="ti ti-clock text-info me-1"></i>
                                                <?= date('d M Y, H:i:s', strtotime($rw['accessed_at'])) ?> WIB
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($rw['device'] ?: 'Browser') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code class="small text-muted"><?= htmlspecialchars($rw['ip_address'] ?: '-') ?></code>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Leaflet JS Local -->
<script src="<?= base_url('assets/siteplan/leaflet/leaflet.js') ?>"></script>
<script src="<?= base_url('assets/siteplan/leaflet/Leaflet.fullscreen.min.js') ?>"></script>

<script>
    // Data Rumah Awal dari Controller
    let siteplanData = <?= json_encode($siteplan_data) ?>;
    let map = null;
    let mapRenderer = null;
    let polygonLayerGroup = null;
    let labelLayerGroup = null;
    let activePolygons = {}; // Map id_rumah -> L.Polygon
    let showRedBoxes = true;
    let currentFilterKoor = "<?= htmlspecialchars((string)($id_koordinator ?: $koorId), ENT_QUOTES, 'UTF-8') ?>";
    const isKoorUser = <?= $isKoor ? 'true' : 'false' ?>;
    const koorUserId = "<?= $koorId ?>";
    if (isKoorUser && !currentFilterKoor && koorUserId) {
        currentFilterKoor = koorUserId;
    }

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

    // Listener tombol Space (opsional, map dragging sudah aktif pada area kosong)
    document.addEventListener('keydown', function(e) {
        if (!isDragMode) return;
        if (e.code === 'Space' && !isSpacePressed && e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            isSpacePressed = true;
            map.getContainer().style.cursor = 'grab';
        }
    });
    document.addEventListener('keyup', function(e) {
        if (!isDragMode) return;
        if (e.code === 'Space') {
            isSpacePressed = false;
            map.getContainer().style.cursor = 'grab';
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
        labelLayerGroup = new L.LayerGroup().addTo(map);

        // Render poligon semua rumah
        renderHousePolygons();

        // Event Zoom: Update label nama agar otomatis muncul & pas saat di-zoom
        map.on('zoomend', updateHouseLabels);

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
        updateHouseLabels();
    }

    // Helper Escape HTML
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Tampilkan Nama Warga di Dalam Kotak Kavling Saat Di-zoom (Teks Murni, Wrapping & Pas di Kotak)
    function updateHouseLabels() {
        if (!map || !labelLayerGroup) return;
        labelLayerGroup.clearLayers();

        const currentZoom = map.getZoom();
        // Hanya muncul saat di-zoom agar tidak padat/menumpuk di overview peta
        if (currentZoom < 3) return;

        let fontSize = (currentZoom >= 5) ? '11px' : (currentZoom >= 4 ? '9px' : '7.5px');

        siteplanData.houses.forEach(house => {
            if (!house.has_coords || !house.polygon_coords || house.polygon_coords.length < 3) return;
            const poly = activePolygons[house.id];
            if (!poly) return;

            const bounds = poly.getBounds();
            if (!bounds || !bounds.isValid || !bounds.isValid()) return;

            // Hitung ukuran dimensi kotak di layar (pixel)
            const nw = map.latLngToLayerPoint(bounds.getNorthWest());
            const se = map.latLngToLayerPoint(bounds.getSouthEast());
            const boxW = Math.abs(se.x - nw.x);
            const boxH = Math.abs(se.y - nw.y);

            // Jika kotak terlalu kecil pada zoom saat ini, jangan render teks
            if (boxW < 20 || boxH < 14) return;

            // Batasi ukuran teks agar strictly berada di dalam kotak poligon (tidak tembus kotak)
            const labelW = Math.max(16, Math.floor(boxW * 0.92));
            const labelH = Math.max(14, Math.floor(boxH * 0.90));

            // Ambil nama warga (jika kosong atau belum berpenghuni, gunakan alamat/nomor rumah)
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

    function toggleLegendCollapse() {
        const leg = document.getElementById('map-legend');
        if (!leg) return;
        leg.classList.toggle('collapsed-mobile');
    }

    function toggleRedBoxes() {
        showRedBoxes = !showRedBoxes;
        document.getElementById('icon-red-boxes').className = showRedBoxes ? 'ti ti-eye-off' : 'ti ti-eye';
        document.getElementById('text-red-boxes').textContent = showRedBoxes ? 'Hide Merah' : 'Show Merah';
        document.getElementById('btn-toggle-red').className = showRedBoxes
            ? 'btn btn-outline-danger btn-sm d-flex align-items-center gap-1'
            : 'btn btn-secondary btn-sm d-flex align-items-center gap-1';

        // Sinkronisasi tombol FAB mobile jika ada
        const fabIcon = document.getElementById('mobile-fab-red-icon');
        const fabText = document.getElementById('mobile-fab-red-text');
        const fabBtn = document.getElementById('mobile-fab-red');
        if (fabIcon) fabIcon.className = showRedBoxes ? 'ti ti-eye-off fs-4' : 'ti ti-eye fs-4';
        if (fabText) fabText.textContent = showRedBoxes ? 'Menunggak' : 'Semua';
        if (fabBtn) {
            fabBtn.className = showRedBoxes
                ? 'btn mobile-fab-btn mobile-fab-toggle-red shadow'
                : 'btn mobile-fab-btn btn-secondary shadow';
        }

        renderHousePolygons();
        focusFilteredPolygons();
    }

    function focusFilteredPolygons() {
        if (!map) return;

        let targetKoor = currentFilterKoor;
        if (!targetKoor && typeof koorUserId !== 'undefined' && koorUserId) {
            targetKoor = koorUserId;
        }
        if (!targetKoor) return;

        try {
            map.invalidateSize();
        } catch (e) {}

        const allPolys = Object.values(activePolygons);
        if (allPolys.length === 0) return;

        const selectedPolys = allPolys.filter(poly => {
            const h = poly.options && poly.options.houseData;
            return h && parseInt(h.id_koordinator) === parseInt(targetKoor);
        });

        const polysToFit = (selectedPolys.length > 0) ? selectedPolys : allPolys;
        if (polysToFit.length === 0) return;

        let bounds = polysToFit[0].getBounds();
        polysToFit.slice(1).forEach(poly => {
            if (poly.getBounds) {
                bounds.extend(poly.getBounds());
            }
        });

        if (bounds && bounds.isValid && bounds.isValid()) {
            map.fitBounds(bounds, {
                padding: [45, 45],
                maxZoom: 4,
                animate: true
            });
        }
    }

    function downloadSiteplanPdf() {
        const params = new URLSearchParams({
            tahun: document.getElementById('filter-tahun').value,
            bulan: document.getElementById('filter-bulan').value,
            id_koordinator: document.getElementById('filter-koor').value
        });
        window.open("<?= base_url('siteplan/pdf') ?>?" + params.toString(), '_blank');
    }

    // Tampilkan Modal Detail Rumah, Anggota Keluarga & IPL (Optimasi Proper Touch-Friendly)
    function showHouseDetail(house) {
        const isLunas = (house.status_ipl === 'lunas');

        // Header Style & Badges
        const headerEl = document.getElementById('modal-house-header');
        headerEl.className = isLunas ? 'modal-header popup-header-lunas' : 'modal-header popup-header-menunggak';

        const handleBar = document.getElementById('modal-handle-bar');
        if (handleBar) {
            handleBar.className = isLunas ? 'bottom-sheet-handle-bar d-md-none bg-success' : 'bottom-sheet-handle-bar d-md-none bg-danger';
        }

        document.getElementById('modal-house-title').textContent = house.alamat;
        document.getElementById('modal-koordinator').textContent = house.koordinator || 'Tanpa Koordinator';

        // Avatar Inisial & Nama Penghuni Utama
        const rawNama = (house.nama && house.nama.trim() !== '' && !house.nama.toLowerCase().includes('belum')) ? house.nama.trim() : '(Belum Terdata Nama)';
        document.getElementById('modal-nama').textContent = rawNama;

        const avatarInitial = document.getElementById('modal-avatar-initial');
        if (avatarInitial) {
            if (rawNama.startsWith('(')) {
                avatarInitial.innerHTML = '<i class="ti ti-user-x"></i>';
                avatarInitial.className = 'rounded-circle text-muted bg-light border d-flex align-items-center justify-content-center flex-shrink-0 fw-bold fs-5 shadow-sm';
            } else {
                const words = rawNama.split(' ').filter(w => w.length > 0);
                let initial = words[0] ? words[0].substring(0, 1).toUpperCase() : 'H';
                if (words.length > 1 && words[1]) {
                    initial += words[1].substring(0, 1).toUpperCase();
                }
                avatarInitial.textContent = initial;
                avatarInitial.className = 'rounded-circle text-primary bg-primary-subtle border border-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold fs-5 shadow-sm';
            }
        }

        // Status Kepemilikan Rumah & No. KK
        const statusRumahEl = document.getElementById('modal-status-rumah');
        if (statusRumahEl) {
            if (house.status_rumah && house.status_rumah.trim() !== '') {
                statusRumahEl.textContent = house.status_rumah;
                statusRumahEl.style.display = 'inline-block';
            } else {
                statusRumahEl.style.display = 'none';
            }
        }

        const noKkWrap = document.getElementById('modal-nokk-wrap');
        const noKkEl = document.getElementById('modal-nokk');
        if (noKkWrap && noKkEl) {
            if (house.no_kk && house.no_kk.trim() !== '') {
                noKkEl.textContent = house.no_kk;
                noKkWrap.style.display = 'inline-flex';
            } else {
                noKkWrap.style.display = 'none';
            }
        }

        // Status Keuangan & Tunggakan IPL
        const badgeHeaderEl = document.getElementById('modal-status-badge-header');
        const badgeEl = document.getElementById('modal-status-badge');
        const tunggakBox = document.getElementById('modal-tunggak-box');
        const lunasBox = document.getElementById('modal-lunas-box');

        if (isLunas) {
            if (badgeHeaderEl) badgeHeaderEl.innerHTML = '<span class="badge bg-white text-success fw-bold px-2.5 py-1 rounded-pill"><i class="ti ti-check me-1"></i>Lunas</span>';
            badgeEl.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle fs-3 px-3 py-1.5 rounded-pill"><i class="ti ti-circle-check-filled me-1"></i> LUNAS</span>';
            if (tunggakBox) tunggakBox.style.display = 'none';
            if (lunasBox) lunasBox.style.display = 'block';
        } else {
            const countStr = (house.tunggakan || 1) + ' Bulan';
            if (badgeHeaderEl) badgeHeaderEl.innerHTML = `<span class="badge bg-white text-danger fw-bold px-2.5 py-1 rounded-pill"><i class="ti ti-alert-triangle me-1"></i>${countStr}</span>`;
            badgeEl.innerHTML = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-3 px-3 py-1.5 rounded-pill"><i class="ti ti-alert-triangle-filled me-1"></i> BELUM BAYAR</span>`;
            if (lunasBox) lunasBox.style.display = 'none';
            if (tunggakBox) {
                tunggakBox.style.display = 'block';
                const countBadge = document.getElementById('modal-tunggak-count');
                if (countBadge) countBadge.textContent = countStr;

                const pillsContainer = document.getElementById('modal-tunggak-list-pills');
                if (pillsContainer) {
                    pillsContainer.innerHTML = '';
                    if (house.bulan_tunggak_list) {
                        const bulanArr = house.bulan_tunggak_list.split(',');
                        bulanArr.forEach(b => {
                            const bName = b.trim();
                            if (bName) {
                                const pill = document.createElement('span');
                                pill.className = 'tunggak-pill-item';
                                pill.innerHTML = `<i class="ti ti-calendar me-1"></i>${escapeHtml(bName)}`;
                                pillsContainer.appendChild(pill);
                            }
                        });
                    } else {
                        pillsContainer.innerHTML = '<span class="tunggak-pill-item">Periode Berjalan</span>';
                    }
                }
            }
        }

        // =============================================
        // RENDER DAFTAR ANGGOTA KELUARGA (FITUR UTAMA)
        // =============================================
        const anggotaList = house.anggota_keluarga || [];
        const countAnggotaEl = document.getElementById('modal-anggota-count');
        const listAnggotaEl = document.getElementById('modal-family-list');
        const emptyAnggotaEl = document.getElementById('modal-family-empty');

        if (countAnggotaEl) {
            countAnggotaEl.textContent = anggotaList.length + ' Jiwa';
        }

        if (listAnggotaEl) {
            listAnggotaEl.innerHTML = '';
            if (anggotaList.length > 0) {
                if (emptyAnggotaEl) emptyAnggotaEl.style.display = 'none';

                anggotaList.forEach(member => {
                    const isFemale = (member.jenis_kelamin && member.jenis_kelamin.toLowerCase().includes('perempuan'));
                    const avatarClass = isFemale ? 'gender-avatar-female' : 'gender-avatar-male';
                    const genderIcon = isFemale ? '<i class="ti ti-gender-female"></i>' : '<i class="ti ti-gender-male"></i>';

                    // Hubungan badge color logic
                    let relationBadge = '';
                    const hub = (member.hubungan || '').trim();
                    if (hub.toLowerCase() === 'kepala keluarga') {
                        relationBadge = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">Kepala Keluarga</span>';
                    } else if (hub.toLowerCase() === 'istri') {
                        relationBadge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Istri</span>';
                    } else if (hub.toLowerCase() === 'suami') {
                        relationBadge = '<span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2.5 py-1">Suami</span>';
                    } else if (hub.toLowerCase() === 'anak') {
                        relationBadge = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">Anak</span>';
                    } else if (hub.toLowerCase() === 'orang tua' || hub.toLowerCase() === 'mertua') {
                        relationBadge = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">' + escapeHtml(hub) + '</span>';
                    } else {
                        relationBadge = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">' + escapeHtml(hub || 'Anggota') + '</span>';
                    }

                    // Info pekerjaan
                    let pekerjaanInfo = '';
                    if (member.pekerjaan && member.pekerjaan !== '-' && member.pekerjaan.trim() !== '') {
                        pekerjaanInfo = `<span class="d-inline-flex align-items-center gap-1 text-muted small"><i class="ti ti-briefcase text-muted"></i> ${escapeHtml(member.pekerjaan)}</span>`;
                    }

                    const card = document.createElement('div');
                    card.className = 'family-member-item d-flex align-items-center justify-content-between gap-2';
                    card.innerHTML = `
                        <div class="d-flex align-items-center gap-2.5 min-w-0">
                            <div class="${avatarClass}" title="${isFemale ? 'Perempuan' : 'Laki-laki'}">
                                ${genderIcon}
                            </div>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">${escapeHtml(member.nama)}</div>
                                ${pekerjaanInfo}
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            ${relationBadge}
                        </div>
                    `;
                    listAnggotaEl.appendChild(card);
                });
            } else {
                if (emptyAnggotaEl) emptyAnggotaEl.style.display = 'block';
            }
        }

        // Direct payment target
        const payTarget = house.encrypted_user_id || house.encrypted_id;
        const btnPay = document.getElementById('modal-btn-pay');
        if (btnPay) {
            btnPay.href = "<?= base_url('pembayaran/') ?>" + (payTarget ? payTarget : '');
        }

        // Contact info: WhatsApp & Phone Call
        const telText = document.getElementById('modal-telepon-text');
        const btnWa = document.getElementById('modal-btn-wa');
        const btnCall = document.getElementById('modal-btn-call');
        const waCol = document.getElementById('modal-wa-col');
        const telCol = document.getElementById('modal-tel-col');

        if (house.no_hp && house.no_hp.trim() !== '') {
            let cleanPhone = house.no_hp.replace(/[^0-9]/g, '');
            let waPhone = cleanPhone;
            if (waPhone.startsWith('0')) {
                waPhone = '62' + waPhone.substring(1);
            }

            if (telText) {
                telText.textContent = house.no_hp;
            }

            // Pesan WA santun untuk koordinator keliling di lapangan
            let pesanWA = `Assalamu'alaikum Wr. Wb. Bapak/Ibu *${rawNama}* (${house.alamat}), mohon maaf mengganggu waktunya sebentar.\n\nSaya selaku Koordinator Lingkungan Perumahan TSI ingin mengonfirmasikan perihal iuran paguyuban/IPL`;
            if (!isLunas && house.bulan_tunggak_list) {
                pesanWA += ` untuk periode *${house.bulan_tunggak_list}* (${house.tunggakan} Bulan).\n\nSaat ini kami sedang penarikan keliling di blok. Pembayaran dapat diserahkan langsung secara tunai atau melalui transfer aplikasi. Terima kasih banyak. 🙏`;
            } else {
                pesanWA += `.\n\nTerima kasih atas partisipasi dan kerjasamanya. 🙏`;
            }

            if (btnWa) {
                btnWa.href = `https://wa.me/${waPhone}?text=${encodeURIComponent(pesanWA)}`;
            }
            if (btnCall) {
                btnCall.href = `tel:${cleanPhone}`;
            }
            if (waCol) waCol.style.display = 'block';
            if (telCol) telCol.style.display = 'block';
        } else {
            if (telText) {
                telText.innerHTML = '<span class="text-muted fst-italic">No. HP belum ada</span>';
            }
            if (waCol) waCol.style.display = 'none';
            if (telCol) telCol.style.display = 'none';
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
            if (currentFilterKoor) {
                focusFilteredPolygons();
            } else {
                map.setView([60, -40], 2);
            }
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

            // Map dragging & scroll wheel zoom SELALU AKTIF untuk navigasi & scroll bebas
            map.dragging.enable();
            map.scrollWheelZoom.enable();

            // Ubah style semua polygon: border tebal + dash untuk indicate draggable
            Object.values(activePolygons).forEach(poly => {
                poly.setStyle({ weight: 3, dashArray: '5, 3' });
            });
            forceCanvasRedraw();
            map.getContainer().style.cursor = 'grab';
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
            map.scrollWheelZoom.enable();
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

        // Matikan map dragging selama menggeser kotak rumah
        map.dragging.disable();

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
        // Re-enable map dragging
        if (map) map.dragging.enable();

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

        // Perbarui posisi label nama kavling
        updateHouseLabels();
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

        // Tampilkan tombol luruskan jika ada kotak yang dipilih
        const btnStraighten = document.getElementById('btn-straighten');
        if (btnStraighten) btnStraighten.style.display = 'block';

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
                if (map) map.dragging.disable();

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

                // Mode Dinamis Bebas: Setiap sudut bergerak mandiri dan presisi mengikuti kursor/jari
                // Tidak lagi dipaksa menjadi jajar genjang, sehingga bentuk kavling apapun (persegi, trapesium, serong) bisa diatur dengan sempurna
                const newLayerPoints = origLayerPoints.map((p, i) => i === cornerIdx ? currentLayerPoint : p);

                const newLatLngCoords = newLayerPoints.map(p => map.layerPointToLatLng(p));
                selectedPolygon.setLatLngs(newLatLngCoords);
                forceCanvasRedraw();
            });

            marker.on('dragend', function(e) {
                if (e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);
                isResizing = false;
                if (map) map.dragging.enable();
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
                    updateHouseLabels();
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

    // Luruskan polygon terpilih menjadi persegi tegak sejajar
    function straightenSelectedPolygon() {
        if (!selectedPolygon) return;
        const bounds = selectedPolygon.getBounds();
        const sw = bounds.getSouthWest();
        const ne = bounds.getNorthEast();
        // 4 titik persegi tegak: NW, NE, SE, SW
        const rectCoords = [
            L.latLng(ne.lat, sw.lng),
            L.latLng(ne.lat, ne.lng),
            L.latLng(sw.lat, ne.lng),
            L.latLng(sw.lat, sw.lng)
        ];
        selectedPolygon.setLatLngs(rectCoords);
        updateResizeHandles(rectCoords);
        forceCanvasRedraw();

        const houseData = selectedPolygon.options.houseData;
        if (houseData) {
            movedPolygons[houseData.id] = true;
            document.getElementById('drag-count').textContent = Object.keys(movedPolygons).length;
        }
        updateHouseLabels();
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
        const btnStraighten = document.getElementById('btn-straighten');
        if (btnStraighten) btnStraighten.style.display = 'none';

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

    // Safety window listener: jika mouseup terjadi di luar elemen, pastikan map dragging selalu dipulihkan
    window.addEventListener('mouseup', function(e) {
        if (isDragMode) {
            if (draggingPolygon) {
                handleDragEnd(e);
            }
            if (map && !map.dragging.enabled()) {
                map.dragging.enable();
            }
        }
    });

    // Jalankan inisialisasi saat DOM siap
    document.addEventListener('DOMContentLoaded', function() {
        initMap();
        if (currentFilterKoor) {
            setTimeout(focusFilteredPolygons, 150);
            setTimeout(focusFilteredPolygons, 400);
        }
    });
</script>
