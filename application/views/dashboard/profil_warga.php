<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
$tahun_sekarang = (int)date('Y');
$selected_tahun = $this->input->get('tahun') ?: $tahun_sekarang;

if ($Auth['role'] === 'koordinator') {
    $koordinator_list = $this->db->query("SELECT id, nama FROM master_koordinator_blok WHERE id = '".$this->db->escape_str($Auth['id'])."'")->result_array();
    $selected_koor = $Auth['id'];
} else {
    $koordinator_list = $this->db->query("SELECT DISTINCT k.id, k.nama FROM master_koordinator_blok k WHERE EXISTS (SELECT 1 FROM master_rumah r WHERE r.id_koordinator = k.id) ORDER BY k.nama")->result_array();
    $selected_koor = $this->input->get('id_koordinator');
}
?>

<style>
/* Modern Styling Variables */
:root {
    --primary-grad: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    --danger-grad: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    --success-grad: linear-gradient(135deg, #10b981 0%, #059669 100%);
    --warning-grad: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    --info-grad: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.04), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
    --card-shadow-hover: 0 20px 30px -10px rgba(0, 0, 0, 0.08), 0 10px 15px -5px rgba(0, 0, 0, 0.04);
}

.kpi-card {
    border: none;
    border-radius: 18px;
    background: #ffffff;
    box-shadow: var(--card-shadow);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--card-shadow-hover);
}
.kpi-card .kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #fff;
    flex-shrink: 0;
}
.kpi-card .card-glow {
    position: absolute;
    right: -20px;
    bottom: -20px;
    width: 90px;
    height: 90px;
    border-radius: 50%;
    opacity: 0.06;
    pointer-events: none;
}

/* Modern Filter Card */
.filter-card-premium {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

/* Tab Styling */
.nav-pills-premium {
    gap: 8px;
    background: #f8fafc;
    padding: 6px;
    border-radius: 16px;
    display: inline-flex;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
}
.nav-pills-premium .nav-item {
    margin: 0;
    padding: 0;
}
.nav-pills-premium .nav-link {
    border: none;
    border-radius: 12px;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 10px 20px;
    transition: all 0.25s ease;
    background: transparent;
    cursor: pointer;
}
.nav-pills-premium .nav-link:hover {
    color: #1e293b;
    background: rgba(255, 255, 255, 0.6);
}
.nav-pills-premium .nav-link.active {
    background: #ffffff;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}
.nav-pills-premium .nav-link.active .badge-count-danger {
    background: #ef4444 !important;
    color: #fff !important;
}
.nav-pills-premium .nav-link.active .badge-count-success {
    background: #10b981 !important;
    color: #fff !important;
}
.nav-pills-premium .nav-link.active .badge-count-dimuka {
    background: #7c3aed !important;
    color: #fff !important;
}

/* Table Design */
.table-premium {
    border-collapse: separate;
    border-spacing: 0;
    width: 100% !important;
}
.table-premium thead th {
    background: #0f172a !important;
    color: #e2e8f0 !important;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 12px 10px;
    border: none;
    vertical-align: middle;
    white-space: nowrap !important;
}
.table-premium thead th:first-child { border-top-left-radius: 12px; }
.table-premium thead th:last-child { border-top-right-radius: 12px; }
.table-premium tbody td {
    padding: 10px 10px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.86rem;
    white-space: nowrap !important;
}
.table-premium tbody tr:hover {
    background-color: #f8fafc;
}

/* Proper Compact Checkbox Sizing & Centering */
.table-premium input[type="checkbox"],
#checkAllTunggak,
.check-tunggak,
.check-modal-koor {
    width: 15px !important;
    height: 15px !important;
    min-width: 15px !important;
    min-height: 15px !important;
    max-width: 15px !important;
    max-height: 15px !important;
    margin: 0 auto !important;
    padding: 0 !important;
    vertical-align: middle !important;
    cursor: pointer !important;
    border: 1.5px solid #94a3b8 !important;
    border-radius: 3px !important;
    background-color: #fff !important;
    display: block !important;
    box-shadow: none !important;
}

.table-premium input[type="checkbox"]:focus,
#checkAllTunggak:focus,
.check-tunggak:focus {
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
    outline: none !important;
}

.table-premium input[type="checkbox"]:checked,
#checkAllTunggak:checked,
.check-tunggak:checked {
    background-color: #ef4444 !important;
    border-color: #ef4444 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e") !important;
}

/* Hilangkan icon sorting DataTables pada kolom checkbox & no-sort */
#tblMenunggak thead th:first-child::before,
#tblMenunggak thead th:first-child::after,
#tblMenunggak thead th.no-sort::before,
#tblMenunggak thead th.no-sort::after,
#tblRajin thead th.no-sort::before,
#tblRajin thead th.no-sort::after,
#tblDimuka thead th.no-sort::before,
#tblDimuka thead th.no-sort::after {
    display: none !important;
    content: "" !important;
}
#tblMenunggak thead th:first-child {
    background-image: none !important;
    padding: 10px 4px !important;
    width: 36px !important;
    max-width: 36px !important;
    text-align: center !important;
}
</style>

<div class="container-fluid">
    <!-- Header Hero Banner -->
    <div class="card border-0 mb-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #1e3a8a 0%, #047857 100%); border-radius: 20px; box-shadow: 0 10px 25px rgba(4, 120, 87, 0.2);">
        <div class="card-body px-4 py-4 position-relative" style="z-index: 2;">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                        <i class="ti ti-shield-check text-warning"></i>
                        <span class="small fw-semibold text-white">Sistem Evaluasi &amp; Monitoring Kepatuhan Warga</span>
                    </div>
                    <h3 class="fw-bold text-white mb-1">🏠 Profil Kepatuhan Pembayaran Warga</h3>
                    <p class="text-white-50 mb-0 small">
                        Laporan otomatis status warga (Menunggak, Rajin Bayar Tepat Waktu, dan Bayar Di Muka) untuk transparansi Paguyuban TSI.
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-md-end mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>" class="text-white-50">Dashboard</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Profil Kepatuhan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <i class="ti ti-chart-donut position-absolute" style="right: -20px; bottom: -30px; font-size: 11rem; opacity: 0.08;"></i>
    </div>

    <!-- 5 KPI Cards Row -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Rumah -->
        <div class="col-6 col-lg-2-4 col-md-4">
            <div class="card kpi-card p-3 h-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: var(--info-grad);">
                        <i class="ti ti-home"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold d-block">Total Rumah</span>
                        <h4 class="fw-bold text-dark mb-0" id="statTotal">-</h4>
                        <small class="text-muted" style="font-size:0.75rem">Terdaftar di sistem</small>
                    </div>
                </div>
                <div class="card-glow" style="background: var(--info-grad);"></div>
            </div>
        </div>

        <!-- 2. Menunggak -->
        <div class="col-6 col-lg-2-4 col-md-4">
            <div class="card kpi-card p-3 h-100" style="border-bottom: 3px solid #ef4444;">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: var(--danger-grad);">
                        <i class="ti ti-alert-triangle"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold d-block">Menunggak</span>
                        <h4 class="fw-bold text-danger mb-0" id="statMenunggak">-</h4>
                        <small class="text-danger fw-semibold" style="font-size:0.75rem">Perlu ditagih</small>
                    </div>
                </div>
                <div class="card-glow" style="background: var(--danger-grad);"></div>
            </div>
        </div>

        <!-- 3. Rajin Bayar -->
        <div class="col-6 col-lg-2-4 col-md-4">
            <div class="card kpi-card p-3 h-100" style="border-bottom: 3px solid #10b981;">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: var(--success-grad);">
                        <i class="ti ti-circle-check"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold d-block">Rajin Bayar</span>
                        <h4 class="fw-bold text-success mb-0" id="statRajin">-</h4>
                        <small class="text-success fw-semibold" style="font-size:0.75rem">Lunas tepat waktu</small>
                    </div>
                </div>
                <div class="card-glow" style="background: var(--success-grad);"></div>
            </div>
        </div>

        <!-- 4. Bayar Di Muka -->
        <div class="col-6 col-lg-2-4 col-md-6">
            <div class="card kpi-card p-3 h-100" style="border-bottom: 3px solid #7c3aed;">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: var(--primary-grad);">
                        <i class="ti ti-star"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-semibold d-block">Bayar Di Muka</span>
                        <h4 class="fw-bold mb-0" style="color:#7c3aed" id="statDimuka">-</h4>
                        <small class="text-muted" style="font-size:0.75rem">Saldo surplus</small>
                    </div>
                </div>
                <div class="card-glow" style="background: var(--primary-grad);"></div>
            </div>
        </div>

        <!-- 5. Tingkat Kepatuhan -->
        <div class="col-12 col-lg-2-4 col-md-6">
            <div class="card kpi-card p-3 h-100" style="border-bottom: 3px solid #059669;">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%);">
                        <i class="ti ti-chart-line"></i>
                    </div>
                    <div class="w-100">
                        <span class="text-muted small fw-semibold d-block">Kepatuhan (%)</span>
                        <h4 class="fw-bold text-success mb-0" id="statKepatuhanPct">0%</h4>
                        <div class="progress mt-1" style="height:6px">
                            <div class="progress-bar bg-success" id="statKepatuhanBar" style="width:0%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Control Card -->
    <div class="card filter-card-premium p-4 mb-4">
        <div class="d-flex flex-wrap align-items-end gap-3">
            <!-- Filter Tahun -->
            <div style="min-width: 140px; flex: 1;">
                <label class="form-label fw-bold text-muted small mb-1">
                    <i class="ti ti-calendar me-1 text-primary"></i> Tahun Buku
                </label>
                <select id="filterTahun" class="form-select form-select-sm rounded-3">
                    <?php for ($y = $tahun_sekarang; $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= $selected_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Filter Bulan -->
            <div style="min-width: 160px; flex: 1;">
                <label class="form-label fw-bold text-muted small mb-1">
                    <i class="ti ti-calendar-event me-1 text-primary"></i> Sampai Bulan
                </label>
                <select id="filterBulan" class="form-select form-select-sm rounded-3">
                    <?php 
                    $bulans = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                    foreach($bulans as $num => $nama): 
                        $sel = ($num == (int)date('n')) ? 'selected' : '';
                    ?>
                        <option value="<?= $num ?>" <?= $sel ?>><?= $nama ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Koordinator (Jika Admin) -->
            <?php if ($Auth['role'] !== 'koordinator'): ?>
            <div style="min-width: 200px; flex: 1.5;">
                <label class="form-label fw-bold text-muted small mb-1">
                    <i class="ti ti-user-check me-1 text-primary"></i> Koordinator Blok
                </label>
                <select id="filterKoor" class="form-select form-select-sm rounded-3">
                    <option value="">Semua Koordinator</option>
                    <?php foreach ($koordinator_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selected_koor == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="d-flex gap-2">
                <button id="btnFilter" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm" style="background: var(--primary-grad); border: none;">
                    <i class="ti ti-search me-1"></i> Terapkan Filter
                </button>
                <button id="btnReset" class="btn btn-sm btn-outline-secondary rounded-3 px-3">
                    <i class="ti ti-refresh me-1"></i> Reset
                </button>
                <button type="button" onclick="downloadRekapMenunggakPdf()" class="btn btn-sm btn-danger rounded-3 px-3 shadow-sm" title="Download PDF Rekap Warga Menunggak">
                    <i class="ti ti-file-download me-1"></i> Rekap PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Main Tabs Section -->
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <!-- Nav-Pills Toolbar -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <ul class="nav nav-pills nav-pills-premium" id="profilTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-btn-menunggak" data-bs-toggle="pill" data-bs-target="#tabMenunggak" type="button" role="tab" aria-controls="tabMenunggak" aria-selected="true">
                            <i class="ti ti-alert-triangle text-danger me-1"></i> Warga Menunggak
                            <span class="badge rounded-pill bg-danger-subtle text-danger ms-1 badge-count-danger" id="badgeMenunggak">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-btn-rajin" data-bs-toggle="pill" data-bs-target="#tabRajin" type="button" role="tab" aria-controls="tabRajin" aria-selected="false">
                            <i class="ti ti-circle-check text-success me-1"></i> Warga Rajin Bayar
                            <span class="badge rounded-pill bg-success-subtle text-success ms-1 badge-count-success" id="badgeRajin">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-btn-dimuka" data-bs-toggle="pill" data-bs-target="#tabDimuka" type="button" role="tab" aria-controls="tabDimuka" aria-selected="false">
                            <i class="ti ti-star text-warning me-1"></i> Bayar Di Muka
                            <span class="badge rounded-pill ms-1 badge-count-dimuka" style="background:#ede9fe;color:#7c3aed" id="badgeDimuka">0</span>
                        </button>
                    </li>
                </ul>

                <div class="small text-muted" id="periodeInfo">-</div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content pt-2">
                <!-- TAB 1: MENUNGGAK -->
                <div class="tab-pane fade show active" id="tabMenunggak">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="fw-bold mb-0 text-dark" id="titleMenunggak">Daftar Warga Menunggak</h6>
                            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" id="btnDownloadRekapPdf" onclick="downloadRekapMenunggakPdf()">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF Rekap
                            </button>
                            <div id="batchActionContainer" class="d-none">
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-sm" onclick="generateBatchPdf()" id="btnGenerateBatchPdf">
                                    <i class="bi bi-file-earmark-zip"></i> Generate Surat (ZIP)
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-premium w-100" id="tblMenunggak">
                            <thead>
                                <tr>
                                    <th class="text-center no-sort" style="width:36px; max-width:36px; vertical-align:middle; text-align:center;">
                                        <input type="checkbox" id="checkAllTunggak" class="form-check-input" style="cursor:pointer;" title="Pilih Semua">
                                    </th>
                                    <th class="text-center" style="width:42px; vertical-align:middle;">No</th>
                                    <th style="width:135px;">Nomor Rumah / Alamat</th>
                                    <th style="width:160px;">Nama Warga</th>
                                    <th style="width:125px;">Kontak WA</th>
                                    <th style="width:150px;">Koordinator</th>
                                    <th class="text-center" style="width:85px;">Terbayar</th>
                                    <th class="text-center" style="width:90px;">Tunggakan</th>
                                    <th class="text-center" style="width:105px;">Status</th>
                                    <th class="text-center no-sort" style="width:135px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: RAJIN BAYAR -->
                <div class="tab-pane fade" id="tabRajin">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-success"><i class="ti ti-trophy me-1"></i> Warga Rajin Bayar (Tepat Waktu)</h6>
                        <small class="text-muted">Telah melunasi seluruh kewajiban iuran IPL periode ini</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium w-100" id="tblRajin">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:45px;">No</th>
                                    <th style="width:140px;">Nomor Rumah / Alamat</th>
                                    <th style="width:160px;">Nama Warga</th>
                                    <th style="width:125px;">Kontak WA</th>
                                    <th style="width:150px;">Koordinator</th>
                                    <th class="text-center" style="width:90px;">Terbayar</th>
                                    <th class="text-end" style="width:110px;">Total Nominal</th>
                                    <th class="text-center" style="width:100px;">Terakhir Bayar</th>
                                    <th class="text-center" style="width:90px;">Kitir Terkirim</th>
                                    <th class="text-center no-sort" style="width:115px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: BAYAR DI MUKA -->
                <div class="tab-pane fade" id="tabDimuka">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color:#7c3aed"><i class="ti ti-star-filled me-1"></i> Warga Bayar Di Muka (Surplus)</h6>
                        <small class="text-muted">Membayar melampaui bulan berjalan saat ini</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-premium w-100" id="tblDimuka">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:45px;">No</th>
                                    <th style="width:140px;">Nomor Rumah / Alamat</th>
                                    <th style="width:160px;">Nama Warga</th>
                                    <th style="width:125px;">Kontak WA</th>
                                    <th style="width:150px;">Koordinator</th>
                                    <th class="text-center" style="width:105px;">Bayar Sampai</th>
                                    <th class="text-center" style="width:95px;">Surplus Bulan</th>
                                    <th class="text-end" style="width:110px;">Total Nominal</th>
                                    <th class="text-center" style="width:90px;">Kitir Terkirim</th>
                                    <th class="text-center no-sort" style="width:115px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Koordinator untuk Download Rekap PDF -->
<div class="modal fade" id="modalDownloadRekap" tabindex="-1" aria-labelledby="modalDownloadRekapLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background: #f8fafc;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 p-2 bg-danger-subtle text-danger">
                        <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="modalDownloadRekapLabel">Download Rekap Warga Menunggak (PDF)</h6>
                        <small class="text-muted" id="modalPeriodeText">Tahun <?= $selected_tahun ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                    <div>
                        <span class="fw-bold text-dark small">Pilih Koordinator Blok yang Mau Dicetak:</span>
                        <div class="text-muted" style="font-size: 0.75rem;">Centang satu atau beberapa koordinator blok yang ingin disertakan ke dalam rekap PDF</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 small rounded-pill" onclick="selectAllModalKoor(true)">
                            <i class="bi bi-check-all me-1"></i>Centang Semua
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 small rounded-pill" onclick="selectAllModalKoor(false)">
                            <i class="bi bi-dash me-1"></i>Hapus Centang
                        </button>
                    </div>
                </div>

                <div class="p-3 rounded-3 border bg-light mb-3" style="max-height: 280px; overflow-y: auto;">
                    <div class="row g-2">
                        <?php foreach ($koordinator_list as $k): ?>
                            <div class="col-12 col-md-6">
                                <label class="d-flex align-items-center p-2 rounded-3 border bg-white h-100 koor-card-item" style="cursor: pointer; transition: all 0.2s ease;">
                                    <input type="checkbox" name="modal_koor_check[]" value="<?= $k['id'] ?>" class="form-check-input check-modal-koor me-2" checked onchange="updateKoorCount()">
                                    <span class="small fw-semibold text-dark"><?= htmlspecialchars($k['nama']) ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="p-3 rounded-3 border bg-white mb-2">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="checkGroupByKoor" checked style="cursor: pointer;">
                        <label class="form-check-label fw-bold text-dark small" for="checkGroupByKoor" style="cursor: pointer;">
                            Kelompokkan Rekap per Koordinator Blok (Disarankan)
                        </label>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            Tabel di PDF akan dipisahkan rapi per wilayah koordinator lengkap dengan subtotal warga menunggak masing-masing.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top py-3 px-4 d-flex justify-content-between">
                <div>
                    <span class="badge bg-danger rounded-pill px-2 py-1" id="badgeModalKoorCount"><?= count($koordinator_list) ?></span>
                    <span class="small text-muted ms-1">Koordinator dipilih</span>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" onclick="submitModalDownloadRekap()">
                        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF Rekap
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var BASE = '<?= base_url() ?>';
var dtMenunggak = null, dtRajin = null, dtDimuka = null;
var currentTahun = '<?= $selected_tahun ?>';

function formatHp(hp) {
    if (!hp) return '<span class="text-muted small">-</span>';
    var wa = hp.replace(/^0/, '62').replace(/[^0-9]/g, '');
    return '<a href="https://wa.me/'+wa+'" target="_blank" class="badge bg-success-subtle text-success text-decoration-none fw-semibold"><i class="bi bi-whatsapp me-1"></i>'+hp+'</a>';
}

function formatRp(n) {
    return 'Rp' + Number(n||0).toLocaleString('id-ID');
}

function destroyTableSafely(selector) {
    if ($.fn.DataTable.isDataTable(selector)) {
        $(selector).DataTable().clear().destroy();
        $(selector + ' tbody').empty();
    }
}

function loadData() {
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    var koor = $('#filterKoor').length ? $('#filterKoor').val() : '';
    currentTahun = tahun;

    // Destroy existing DataTables safely
    destroyTableSafely('#tblMenunggak');
    destroyTableSafely('#tblRajin');
    destroyTableSafely('#tblDimuka');
    dtMenunggak = null;
    dtRajin = null;
    dtDimuka = null;

    // Loading indicator on tables
    $('#tblMenunggak tbody, #tblRajin tbody, #tblDimuka tbody').html(
        '<tr><td colspan="10" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted small fw-semibold">Memuat data profil kepatuhan warga via AJAX...</div></td></tr>'
    );
    $('#statTotal, #statMenunggak, #statRajin, #statDimuka').text('-');

    $.ajax({
        url: BASE + 'ajax-profil-warga',
        type: 'GET',
        data: { tahun: tahun, bulan: bulan, id_koordinator: koor },
        dataType: 'json',
        success: function(res) {
            // Update stats & progress bar
            var total = res.stats.total || 0;
            var menunggak = res.stats.menunggak || 0;
            var rajin = res.stats.rajin || 0;
            var dimuka = res.stats.dimuka || 0;
            var taat = rajin + dimuka;
            var pct = (total > 0) ? Math.round((taat / total) * 100) : 0;

            $('#statTotal').text(total);
            $('#statMenunggak').text(menunggak);
            $('#statRajin').text(rajin);
            $('#statDimuka').text(dimuka);
            $('#badgeMenunggak').text(menunggak);
            $('#badgeRajin').text(rajin);
            $('#badgeDimuka').text(dimuka);

            $('#statKepatuhanPct').text(pct + '%');
            $('#statKepatuhanBar').css('width', pct + '%');

            $('#titleMenunggak').text('Daftar Warga Menunggak — Tahun ' + tahun);
            $('#periodeInfo').html('<i class="ti ti-calendar me-1"></i> Periode: <strong>' + (res.periode || '-') + '</strong> (' + (res.total_bulan_wajib || 1) + ' bulan wajib)');

            var tbw = res.total_bulan_wajib || 1;

            // Reset Select All
            $('#checkAllTunggak').prop('checked', false);
            $('#batchActionContainer').addClass('d-none');

            // 1. Data Menunggak
            var rows1 = [];
            $.each(res.menunggak || [], function(i, w) {
                var totalNominal = (w.tunggakan || 0) * 150000;
                var listBulan = w.bulan_tunggak_list || (w.tunggakan + " bulan");
                var msg = "Assalamualaikum Bapak/Ibu *" + (w.nama || '') + "*, kami dari pengurus Paguyuban TSI memberitahukan bahwa terdapat tunggakan IPL untuk rumah *" + (w.alamat || '') + "* sebesar *Rp " + totalNominal.toLocaleString('id-ID') + "* (" + listBulan + "). Mohon segera melakukan koordinasi pembayaran melalui Koordinator atau Bendahara. Terima kasih.";
                var waLink = "https://wa.me/" + (w.no_hp ? w.no_hp.replace(/^0/, '62').replace(/[^0-9]/g, '') : "") + "?text=" + encodeURIComponent(msg);

                var badgeClass = 'bg-danger';
                if (w.tunggakan <= 1) badgeClass = 'bg-warning text-dark';
                else if (w.tunggakan <= 3) badgeClass = 'bg-danger-subtle text-danger';

                rows1.push([
                    '<input type="checkbox" value="'+w.id+'" class="form-check-input check-tunggak">',
                    '<span class="text-muted fw-bold">' + (i+1) + '</span>',
                    '<div><i class="bi bi-geo-alt-fill text-danger me-1"></i><strong>' + (w.alamat||'-') + '</strong></div>',
                    '<span class="fw-bold text-dark">' + (w.nama||'-') + '</span>',
                    formatHp(w.no_hp),
                    '<span class="badge bg-light text-dark border">' + (w.koordinator||'-') + '</span>',
                    '<span class="badge bg-info-subtle text-info fw-bold px-2 py-1">' + (w.jumlah_bulan_bayar || 0) + ' / ' + tbw + ' Bln</span>',
                    '<span class="badge ' + badgeClass + ' px-2 py-1">' + (w.tunggakan || 0) + ' Bulan</span>',
                    '<small class="fw-semibold text-danger">' + (w.status_tunggak||'Nunggak') + '</small>',
                    '<div class="d-inline-flex gap-1 justify-content-center">' +
                        '<a href="'+BASE+'surat-teguran-pdf?id_rumah='+w.id+'&tahun='+tahun+'&bulan='+bulan+'" target="_blank" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;" title="Cetak Surat PDF"><i class="bi bi-file-earmark-pdf"></i></a>' +
                        '<a href="'+waLink+'" target="_blank" class="btn btn-sm btn-outline-success rounded-circle p-1" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;" title="Kirim WA Manual"><i class="bi bi-whatsapp"></i></a>' +
                        '<button onclick="sendWaOtomatis('+w.id+')" class="btn btn-sm btn-success rounded-circle p-1" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;" title="Kirim WA Otomatis (API)"><i class="bi bi-send-check"></i></button>' +
                        '<a href="'+BASE+'pembayaran/'+w.id+'" class="btn btn-sm btn-outline-primary rounded-circle p-1" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;" title="Input Pembayaran"><i class="bi bi-cash-coin"></i></a>' +
                    '</div>'
                ]);
            });

            dtMenunggak = $('#tblMenunggak').DataTable({
                data: rows1,
                destroy: true,
                autoWidth: false,
                pageLength: 25,
                order: [[1, 'asc']],
                language: {
                    search: "Cari Warga:",
                    lengthMenu: "_MENU_",
                    info: "_START_ - _END_ dari _TOTAL_",
                    paginate: { previous: "Prev", next: "Next" },
                    emptyTable: "Luar biasa! Tidak ada warga yang menunggak di periode ini 🎉",
                    zeroRecords: "Tidak ditemukan"
                },
                columnDefs: [
                    { targets: 0, orderable: false, searchable: false, width: '36px', className: 'text-center align-middle' },
                    { targets: 1, width: '42px', className: 'text-center align-middle' },
                    { targets: 2, width: '135px', className: 'align-middle' },
                    { targets: 3, width: '160px', className: 'align-middle' },
                    { targets: 4, width: '125px', className: 'align-middle' },
                    { targets: 5, width: '150px', className: 'align-middle' },
                    { targets: 6, width: '85px', className: 'text-center align-middle' },
                    { targets: 7, width: '90px', className: 'text-center align-middle' },
                    { targets: 8, width: '105px', className: 'text-center align-middle' },
                    { targets: 9, orderable: false, width: '135px', className: 'text-center align-middle' }
                ]
            });

            // 2. Data Rajin Bayar
            var rows2 = [];
            $.each(res.rajin || [], function(i, w) {
                rows2.push([
                    '<span class="text-muted fw-bold">' + (i+1) + '</span>',
                    '<div><i class="bi bi-geo-alt-fill text-success me-1"></i><strong>' + (w.alamat||'-') + '</strong></div>',
                    '<span class="fw-bold text-dark">' + (w.nama||'-') + '</span> <i class="bi bi-patch-check-fill text-success" title="Lunas"></i>',
                    formatHp(w.no_hp),
                    '<span class="badge bg-light text-dark border">' + (w.koordinator||'-') + '</span>',
                    '<span class="badge bg-success-subtle text-success fw-bold px-2 py-1">' + (w.jumlah_bulan_bayar || 0) + ' Bulan ✓</span>',
                    '<span class="fw-bold text-dark">' + formatRp(w.total_bayar) + '</span>',
                    w.terakhir_bayar ? '<span class="small">' + w.terakhir_bayar.substring(8,10)+'/'+w.terakhir_bayar.substring(5,7)+'/'+w.terakhir_bayar.substring(0,4) + '</span>' : '-',
                    (w.wa_count > 0) ? '<span class="badge bg-warning text-dark"><i class="bi bi-check-all"></i> '+w.wa_count+'x</span>' : '<span class="text-muted small">-</span>',
                    '<button onclick="sendWaKonfirmasi('+w.id+',\'lancar\')" class="btn btn-sm btn-success rounded-pill px-2 py-1 small" title="Kirim Bukti Pembayaran via WA"><i class="bi bi-send-check me-1"></i>Kirim Bukti</button>'
                ]);
            });

            dtRajin = $('#tblRajin').DataTable({
                data: rows2,
                destroy: true,
                autoWidth: false,
                pageLength: 25,
                language: {
                    search: "Cari Warga:",
                    lengthMenu: "_MENU_",
                    info: "_START_ - _END_ dari _TOTAL_",
                    paginate: { previous: "Prev", next: "Next" },
                    emptyTable: "Belum ada warga lunas semua bulan",
                    zeroRecords: "Tidak ditemukan"
                },
                columnDefs: [
                    { targets: 0, width: '45px', className: 'text-center align-middle' },
                    { targets: 1, width: '140px', className: 'align-middle' },
                    { targets: 2, width: '160px', className: 'align-middle' },
                    { targets: 3, width: '125px', className: 'align-middle' },
                    { targets: 4, width: '150px', className: 'align-middle' },
                    { targets: 5, width: '90px', className: 'text-center align-middle' },
                    { targets: 6, width: '110px', className: 'text-end align-middle' },
                    { targets: 7, width: '100px', className: 'text-center align-middle' },
                    { targets: 8, width: '90px', className: 'text-center align-middle' },
                    { targets: 9, orderable: false, width: '115px', className: 'text-center align-middle' }
                ]
            });

            // 3. Data Bayar Di Muka
            var rows3 = [];
            $.each(res.dimuka || [], function(i, w) {
                rows3.push([
                    '<span class="text-muted fw-bold">' + (i+1) + '</span>',
                    '<div><i class="bi bi-geo-alt-fill text-primary me-1"></i><strong>' + (w.alamat||'-') + '</strong></div>',
                    '<span class="fw-bold text-dark">' + (w.nama||'-') + '</span> <span title="Surplus">⭐</span>',
                    formatHp(w.no_hp),
                    '<span class="badge bg-light text-dark border">' + (w.koordinator||'-') + '</span>',
                    '<span class="badge rounded-pill" style="background:#7c3aed;color:#fff">' + (w.bayar_sampai || '-') + '</span>',
                    '<span class="badge bg-info-subtle text-info fw-bold">+' + (w.bulan_dimuka || 0) + ' Bulan</span>',
                    '<span class="fw-bold text-dark">' + formatRp(w.total_bayar) + '</span>',
                    (w.wa_count > 0) ? '<span class="badge bg-warning text-dark"><i class="bi bi-check-all"></i> '+w.wa_count+'x</span>' : '<span class="text-muted small">-</span>',
                    '<button onclick="sendWaKonfirmasi('+w.id+',\'dimuka\')" class="btn btn-sm btn-success rounded-pill px-2 py-1 small" title="Kirim Bukti Pembayaran via WA"><i class="bi bi-send-check me-1"></i>Kirim Bukti</button>'
                ]);
            });

            dtDimuka = $('#tblDimuka').DataTable({
                data: rows3,
                destroy: true,
                autoWidth: false,
                pageLength: 25,
                language: {
                    search: "Cari Warga:",
                    lengthMenu: "_MENU_",
                    info: "_START_ - _END_ dari _TOTAL_",
                    paginate: { previous: "Prev", next: "Next" },
                    emptyTable: "Belum ada warga bayar di muka",
                    zeroRecords: "Tidak ditemukan"
                },
                columnDefs: [
                    { targets: 0, width: '45px', className: 'text-center align-middle' },
                    { targets: 1, width: '140px', className: 'align-middle' },
                    { targets: 2, width: '160px', className: 'align-middle' },
                    { targets: 3, width: '125px', className: 'align-middle' },
                    { targets: 4, width: '150px', className: 'align-middle' },
                    { targets: 5, width: '105px', className: 'text-center align-middle' },
                    { targets: 6, width: '95px', className: 'text-center align-middle' },
                    { targets: 7, width: '110px', className: 'text-end align-middle' },
                    { targets: 8, width: '90px', className: 'text-center align-middle' },
                    { targets: 9, orderable: false, width: '115px', className: 'text-center align-middle' }
                ]
            });

            // Re-adjust columns on active visible table
            setTimeout(function() {
                if ($.fn.dataTable) {
                    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
                }
            }, 60);
        },
        error: function(xhr, status, error) {
            console.error("Ajax Profil Warga Error:", error);
            $('#tblMenunggak tbody, #tblRajin tbody, #tblDimuka tbody').html(
                '<tr><td colspan="10" class="text-center py-5 text-danger"><i class="bi bi-exclamation-circle" style="font-size:2rem;"></i><p class="mt-2 fw-semibold">Gagal memuat data profil kepatuhan via AJAX.</p></td></tr>'
            );
        }
    });
}

$(document).ready(function() {
    // 1. Tab Switching (Direct, foolproof click listener)
    $(document).on('click', '#profilTabs button.nav-link', function(e) {
        e.preventDefault();
        var target = $(this).attr('data-bs-target');

        $('#profilTabs button.nav-link').removeClass('active').attr('aria-selected', 'false');
        $(this).addClass('active').attr('aria-selected', 'true');

        $('.tab-content > .tab-pane').removeClass('show active');
        $(target).addClass('show active');

        // Adjust DataTables columns in the newly activated tab
        setTimeout(function() {
            if ($.fn.dataTable) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            }
        }, 50);
    });

    // 2. Also hook to Bootstrap shown.bs.tab if available
    $('button[data-bs-toggle="pill"], button[data-bs-toggle="tab"]').on('shown.bs.tab', function() {
        if ($.fn.dataTable) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        }
    });

    // 3. Initial Load
    loadData();

    // 4. Filter Buttons
    $('#btnFilter').on('click', function(e) {
        e.preventDefault();
        loadData();
    });

    $('#btnReset').on('click', function(e) {
        e.preventDefault();
        $('#filterTahun').val('<?= $tahun_sekarang ?>');
        $('#filterBulan').val('<?= (int)date('n') ?>');
        if ($('#filterKoor').length) $('#filterKoor').val('');
        loadData();
    });

    // 5. Checkbox Tunggak Handlers
    $(document).on('change', '#checkAllTunggak', function() {
        var isChecked = $(this).is(':checked');
        if (dtMenunggak) {
            dtMenunggak.$('.check-tunggak').prop('checked', isChecked);
            toggleBatchButton(dtMenunggak.$('.check-tunggak:checked').length);
        }
    });

    $('#tblMenunggak').on('change', '.check-tunggak', function() {
        if (dtMenunggak) {
            var total = dtMenunggak.$('.check-tunggak').length;
            var checked = dtMenunggak.$('.check-tunggak:checked').length;
            $('#checkAllTunggak').prop('checked', (total > 0 && total === checked));
            toggleBatchButton(checked);
        }
    });
});

function toggleBatchButton(checkedCount) {
    if (checkedCount > 0) {
        $('#batchActionContainer').removeClass('d-none');
        $('#btnGenerateBatchPdf').html('<i class="bi bi-file-earmark-zip me-1"></i> Generate Surat (ZIP) - ' + checkedCount + ' dipilih');
    } else {
        $('#batchActionContainer').addClass('d-none');
    }
}

function generateBatchPdf() {
    if (!dtMenunggak) return;
    var ids = [];
    dtMenunggak.$('.check-tunggak:checked').each(function() {
        ids.push($(this).val());
    });
    if (ids.length === 0) {
        alert('Pilih minimal satu warga terlebih dahulu.');
        return;
    }
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    window.open(BASE + 'batch-surat-teguran-zip?ids=' + ids.join(',') + '&tahun=' + tahun + '&bulan=' + bulan, '_blank');
}

function sendWaOtomatis(id_rumah) {
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    if (!confirm('Kirim surat teguran WA langsung ke nomor HP warga?')) return;

    var btn = event.currentTarget;
    $(btn).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: BASE + 'kirim-teguran-wa',
        data: { id_rumah: id_rumah, tahun: tahun, bulan: bulan },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
            $(btn).prop('disabled', false).html('<i class="bi bi-send-check"></i>');
        },
        error: function() {
            alert('Gagal mengirim WhatsApp');
            $(btn).prop('disabled', false).html('<i class="bi bi-send-check"></i>');
        }
    });
}

function sendWaKonfirmasi(id_rumah, tipe) {
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    if (!confirm('Kirim kitir konfirmasi via WhatsApp?')) return;

    var btn = event.currentTarget;
    $(btn).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    $.ajax({
        url: BASE + 'kirim-konfirmasi-wa',
        data: { id_rumah: id_rumah, tahun: tahun, bulan: bulan, tipe: tipe },
        dataType: 'json',
        success: function(res) {
            alert(res.message);
            $(btn).prop('disabled', false).html('<i class="bi bi-send-check me-1"></i>Kirim Bukti');
            loadData();
        },
        error: function() {
            alert('Gagal mengirim WhatsApp');
            $(btn).prop('disabled', false).html('<i class="bi bi-send-check me-1"></i>Kirim Bukti');
        }
    });
}

function downloadRekapMenunggakPdf() {
    $('#modalDownloadRekap').modal('show');
}
function selectAllModalKoor(check) {
    $('.check-modal-koor').prop('checked', check);
    updateKoorCount();
}
function updateKoorCount() {
    var cnt = $('.check-modal-koor:checked').length;
    $('#badgeModalKoorCount').text(cnt);
}
function submitModalDownloadRekap() {
    var selected = [];
    $('.check-modal-koor:checked').each(function() { selected.push($(this).val()); });
    if (selected.length === 0) {
        alert('Pilih minimal satu koordinator blok.');
        return;
    }
    var tahun = $('#filterTahun').val();
    var bulan = $('#filterBulan').val();
    var groupBy = $('#checkGroupByKoor').is(':checked') ? '1' : '0';
    $('#modalDownloadRekap').modal('hide');
    window.open(BASE + 'rekap-menunggak-pdf?tahun=' + tahun + '&bulan=' + bulan + '&id_koordinator=' + encodeURIComponent(selected.join(',')) + '&group_by=' + groupBy, '_blank');
}
</script>
