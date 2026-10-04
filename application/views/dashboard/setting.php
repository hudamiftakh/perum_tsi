<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');

// Cek role
$is_admin = ($Auth['role'] === 'admin');

// Tab aktif (default: rumah untuk koordinator & admin)
$active_tab = $this->input->get('tab');
if (empty($active_tab)) {
    $active_tab = 'rumah';
}

// ============================================
// DATA USERS (Admin & Koordinator)
// ============================================
$admin_users = $this->db->query("SELECT id, nama, username, 'admin' as role, login_at, NULL as no_hp FROM master_admin ORDER BY nama ASC")->result_array();
$koordinator_users = $this->db->query("SELECT id, nama, username, 'koordinator' as role, login_at, no_hp FROM master_koordinator_blok ORDER BY nama ASC")->result_array();
$total_users = count($admin_users) + count($koordinator_users);

// ============================================
// DATA SETTING TARIF IPL
// ============================================
$tarif_baru = get_setting('nominal_ipl', '140000');
$bulan_mulai_berlaku = get_setting('bulan_berlaku_nominal', '2026-11');
$tarif_lama = get_setting('nominal_ipl_lama', '125000');
$catatan_tarif = get_setting('catatan_tarif', 'Penyesuaian tarif iuran IPL dari Rp 125.000 menjadi Rp 140.000');
$info_tarif = get_info_tarif_aktif();
$list_tarif = get_all_tarif_ipl();

// ============================================
// STATISTIK KPI RUMAH
// ============================================
$where_base = [];
if (!$is_admin) {
    $where_base['r.id_koordinator'] = $Auth['id'];
}

// Total Rumah
$this->db->from('master_rumah r');
if (!empty($where_base)) $this->db->where($where_base);
$total_rumah = $this->db->count_all_results();

// Rumah Sendiri
$this->db->from('master_rumah r');
if (!empty($where_base)) $this->db->where($where_base);
$this->db->group_start();
$this->db->where('r.status_rumah', 'Rumah Sendiri');
$this->db->or_where('r.status_rumah IS NULL');
$this->db->or_where('r.status_rumah', '');
$this->db->group_end();
$count_sendiri = $this->db->count_all_results();

// Sewa / Kontrak
$this->db->from('master_rumah r');
if (!empty($where_base)) $this->db->where($where_base);
$this->db->group_start();
$this->db->like('r.status_rumah', 'kontrak');
$this->db->or_like('r.status_rumah', 'sewa');
$this->db->group_end();
$count_kontrak = $this->db->count_all_results();

// Musiman
$this->db->from('master_rumah r');
if (!empty($where_base)) $this->db->where($where_base);
$this->db->like('r.status_rumah', 'musiman');
$count_musiman = $this->db->count_all_results();
?>

<style>
    /* =========================================
       KPI CARD SYSTEM (Proper 1.5px Outline)
       ========================================= */
    .kpi-card {
        background: #ffffff;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 16px;
        box-shadow: 0 4px 14px -2px rgba(15, 23, 42, 0.06);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }
    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -4px rgba(15, 23, 42, 0.12);
        border-color: #64748b !important;
    }
    .kpi-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .kpi-title {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kpi-value {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.5px;
        line-height: 1.2;
        margin-bottom: 2px;
    }
    .kpi-sub {
        font-size: 0.72rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
        white-space: nowrap;
    }

    /* =========================================
       MODERN SEGMENTED TAB NAV
       ========================================= */
    .setting-tab-nav {
        display: inline-flex;
        background: #f1f5f9;
        padding: 5px;
        border-radius: 14px;
        border: 1.5px solid #cbd5e1;
        gap: 6px;
    }
    .setting-tab-nav .nav-link {
        border: none;
        border-radius: 10px;
        padding: 8px 18px;
        font-weight: 600;
        font-size: 0.88rem;
        color: #64748b;
        background: transparent;
        transition: all 0.2s ease-in-out;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .setting-tab-nav .nav-link:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.6);
    }
    .setting-tab-nav .nav-link.active {
        background: #ffffff;
        color: #0f172a;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    }

    @media (max-width: 768px) {
        .setting-tab-nav {
            display: flex;
            width: 100%;
        }
        .setting-tab-nav .nav-item {
            flex: 1;
        }
        .setting-tab-nav .nav-link {
            width: 100%;
            justify-content: center;
            padding: 9px 10px;
            font-size: 0.82rem;
        }
    }

    /* =========================================
       CARD CONTAINER & TABLE STYLING
       ========================================= */
    .card-custom {
        background: #ffffff;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 18px;
        box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }
    .panel-header-soft {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1.5px solid #cbd5e1 !important;
        padding: 16px 20px;
    }
    .table-soft thead th {
        background-color: #f8fafc !important;
        color: #334155 !important;
        font-weight: 700 !important;
        font-size: 0.8rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        border-bottom: 2px solid #cbd5e1 !important;
        border-top: none !important;
        padding: 12px 14px !important;
    }
    .filter-container {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px;
    }

    .badge-status-sendiri {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
    }
    .badge-status-kontrak {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
    }
    .badge-status-musiman {
        background-color: #ede9fe;
        color: #6d28d9;
        border: 1px solid #ddd6fe;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
    }

    /* =========================================
       MODAL SOFT STYLING & LAYOUT
       ========================================= */
    .modal-custom .modal-content {
        border-radius: 20px !important;
        border: 1.5px solid #cbd5e1 !important;
        box-shadow: 0 20px 40px -8px rgba(15, 23, 42, 0.16) !important;
        overflow: hidden;
    }
    .modal-header-soft {
        background: linear-gradient(135deg, #f0f7ff 0%, #f8fafc 100%);
        border-bottom: 1.5px solid #e2e8f0 !important;
        padding: 18px 24px;
    }
    .modal-footer-soft {
        background: #f8fafc;
        border-top: 1.5px solid #e2e8f0 !important;
        padding: 14px 24px;
    }
    .modal-icon-box {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        background: #e0f2fe;
        color: #0284c7;
    }
    .modal-info-panel {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        padding: 12px 16px;
    }
    .form-control-modal, .form-select-modal {
        border-radius: 12px;
        border: 1.5px solid #cbd5e1;
        padding: 10px 14px;
        font-size: 0.92rem;
        background-color: #ffffff;
        transition: all 0.2s ease;
    }
    .form-control-modal:focus, .form-select-modal:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        background-color: #ffffff;
    }

    /* Toast Notification */
    #settingToast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        min-width: 300px;
        max-width: 90vw;
    }
</style>

<div class="container-fluid px-0">
    <!-- FLASH MESSAGES (Fallback) -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
            <div><?= $this->session->flashdata('success') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
            <div><?= $this->session->flashdata('error') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- HEADER TITLE & TABS (SOFT PANEL) -->
    <div class="card border-0 mb-4" style="background: linear-gradient(135deg, #f0f7ff 0%, #f8fafc 100%); border: 1.5px solid #cbd5e1 !important; border-radius: 18px;">
        <div class="card-body px-4 py-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                        <span class="rounded-3 p-2 d-inline-flex" style="background:#e0f2fe; color:#0284c7;">
                            <i class="ti ti-settings fs-5"></i>
                        </span>
                        <span>Pengaturan Data Master</span>
                    </h4>
                    <p class="text-muted small mb-0">Kelola informasi kepemilikan rumah warga serta akun pengguna sistem</p>
                </div>

                <!-- NAV TABS -->
                <ul class="nav setting-tab-nav" id="settingTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $active_tab == 'rumah' ? 'active' : '' ?>" id="rumah-tab" data-bs-toggle="tab" data-bs-target="#rumah-panel" type="button" role="tab">
                            <i class="ti ti-home"></i>
                            <span>Data Rumah</span>
                            <span class="badge rounded-pill bg-light text-dark ms-1" style="font-size:0.7rem"><?= number_format($total_rumah, 0, ',', '.') ?></span>
                        </button>
                    </li>
                    <?php if ($is_admin): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $active_tab == 'users' ? 'active' : '' ?>" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-panel" type="button" role="tab">
                                <i class="ti ti-users"></i>
                                <span>Data User</span>
                                <span class="badge rounded-pill bg-light text-dark ms-1" style="font-size:0.7rem"><?= $total_users ?></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?= $active_tab == 'tarif' ? 'active' : '' ?>" id="tarif-tab" data-bs-toggle="tab" data-bs-target="#tarif-panel" type="button" role="tab">
                                <i class="ti ti-coin"></i>
                                <span>Tarif Iuran IPL</span>
                                <span class="badge rounded-pill bg-primary ms-1" style="font-size:0.7rem">Setting</span>
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <!-- 4 KPI SUMMARY CARDS -->
    <div class="row mb-4">
        <!-- Total Rumah -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #0284c7 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#e0f2fe; color:#0284c7;">
                            <i class="ti ti-home"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#e0f2fe; color:#0284c7; font-size:0.68rem; font-weight:600;">Terdaftar</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Total Unit Rumah">Total Unit Rumah</div>
                        <div class="kpi-value text-dark"><?= number_format($total_rumah, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-building-community text-muted"></i> Unit rumah binaan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rumah Sendiri -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #15803d !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#15803d;">
                            <i class="ti ti-home-check"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#15803d; font-size:0.68rem; font-weight:600;">Pemilik</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Rumah Sendiri">Rumah Sendiri</div>
                        <div class="kpi-value text-success"><?= number_format($count_sendiri, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-check text-success"></i> Milik pribadi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sewa / Kontrak -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #d97706 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fef3c7; color:#d97706;">
                            <i class="ti ti-key"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fef3c7; color:#d97706; font-size:0.68rem; font-weight:600;">Penyewa</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Sewa / Kontrak">Sewa / Kontrak</div>
                        <div class="kpi-value" style="color:#d97706;"><?= number_format($count_kontrak, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-clock text-warning"></i> Status kontrak</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Musiman -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #7c3aed !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ede9fe; color:#7c3aed;">
                            <i class="ti ti-building-community"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#ede9fe; color:#7c3aed; font-size:0.68rem; font-weight:600;">Musiman</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Musiman / Singgah">Musiman / Singgah</div>
                        <div class="kpi-value" style="color:#7c3aed;"><?= number_format($count_musiman, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-clock-pause text-primary"></i> Penghuni singgah</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB CONTENT -->
    <div class="tab-content" id="settingTabsContent">

        <!-- ============================================ -->
        <!-- TAB 1: DATA PEMILIK RUMAH (SERVER-SIDE)     -->
        <!-- ============================================ -->
        <div class="tab-pane fade <?= $active_tab == 'rumah' ? 'show active' : '' ?>" id="rumah-panel" role="tabpanel">
            <div class="card card-custom mb-4">
                <div class="panel-header-soft d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                            <i class="ti ti-home text-primary"></i> Daftar Pemilik & Status Rumah
                        </h5>
                        <span class="text-muted small">Kelola nama penghuni, kontak WhatsApp, serta status kepemilikan rumah</span>
                    </div>
                </div>
                <div class="card-body p-3 p-md-4">
                    <!-- FILTER & PENCARIAN -->
                    <div class="filter-container mb-4">
                        <div class="row g-2 align-items-center">
                            <!-- Filter Koordinator (Admin Only) -->
                            <?php if ($is_admin): ?>
                            <div class="col-12 col-md-5 col-lg-4">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="ti ti-user-check me-1"></i>Filter Koordinator</label>
                                <select id="filterKoordinator" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Koordinator Blok</option>
                                    <?php foreach ($koordinator_users as $ku): ?>
                                        <option value="<?= $ku['id'] ?>"><?= htmlspecialchars($ku['nama']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php endif; ?>

                            <!-- Filter Status Rumah -->
                            <div class="col-12 col-md-5 col-lg-4">
                                <label class="form-label small fw-bold text-muted mb-1"><i class="ti ti-home-cog me-1"></i>Filter Status Rumah</label>
                                <select id="filterStatus" class="form-select form-select-sm rounded-3">
                                    <option value="">Semua Status Rumah</option>
                                    <option value="Rumah Sendiri">Rumah Sendiri</option>
                                    <option value="kontrak">Sewa / Kontrak</option>
                                    <option value="musiman">Musiman</option>
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 col-md-auto ms-auto d-flex align-items-end gap-2 mt-md-4">
                                <button type="button" id="btnResetFilter" class="btn btn-sm btn-light border text-danger px-3 rounded-pill shadow-sm" title="Reset Filter">
                                    <i class="ti ti-refresh me-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- TABEL SERVER-SIDE DATA RUMAH -->
                    <div class="table-responsive">
                        <table id="tblSettingRumah" class="table table-hover align-middle w-100 table-soft" style="font-size:0.88rem">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45px;" class="text-center">No</th>
                                    <th style="width: 160px;">Alamat Rumah</th>
                                    <th>Nama Pemilik / Penghuni</th>
                                    <th style="width: 140px;">Status Rumah</th>
                                    <th style="width: 150px;">No. WhatsApp</th>
                                    <th>Koordinator</th>
                                    <th style="width: 100px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables server-side populates here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- TAB 2: DATA USER (ADMIN ONLY)               -->
        <!-- ============================================ -->
        <?php if ($is_admin): ?>
            <div class="tab-pane fade <?= $active_tab == 'users' ? 'show active' : '' ?>" id="users-panel" role="tabpanel">
                <div class="card card-custom mb-4">
                    <div class="panel-header-soft d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="ti ti-users text-primary"></i> Daftar Akun Pengguna
                            </h5>
                            <span class="text-muted small">Kelola data profil Admin & Koordinator Blok</span>
                        </div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-7 fw-semibold">
                            <i class="ti ti-users me-1"></i> <?= $total_users ?> Akun
                        </span>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="table-responsive">
                            <table id="tblSettingUser" class="table table-hover align-middle w-100 table-soft" style="font-size:0.88rem">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">No</th>
                                        <th>Nama Lengkap</th>
                                        <th>No. WhatsApp</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Login Terakhir</th>
                                        <th style="width: 100px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1; ?>
                                    <!-- ADMIN USERS -->
                                    <?php foreach ($admin_users as $u): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <i class="ti ti-shield-lock text-primary me-1"></i>
                                                    <?= htmlspecialchars($u['nama']) ?>
                                                </span>
                                            </td>
                                            <td><span class="text-muted">-</span></td>
                                            <td><code class="px-2 py-1 bg-light rounded text-primary fw-semibold"><?= htmlspecialchars($u['username']) ?></code></td>
                                            <td><span class="badge bg-primary rounded-pill px-3 py-1">Admin</span></td>
                                            <td>
                                                <small class="text-muted"><i class="ti ti-clock me-1"></i><?= !empty($u['login_at']) ? date('d/m/Y H:i', strtotime($u['login_at'])) : '-' ?></small>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-user rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1"
                                                    data-id="<?= $u['id'] ?>"
                                                    data-table="master_admin"
                                                    data-username="<?= htmlspecialchars($u['username']) ?>"
                                                    data-role="Admin"
                                                    data-nama="<?= htmlspecialchars($u['nama']) ?>"
                                                    data-nohp="">
                                                    <i class="ti ti-pencil"></i> <span>Edit</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <!-- KOORDINATOR USERS -->
                                    <?php foreach ($koordinator_users as $u): ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <i class="ti ti-user text-warning me-1"></i>
                                                    <?= htmlspecialchars($u['nama']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($u['no_hp'])): ?>
                                                    <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $u['no_hp'])) ?>" target="_blank" class="text-decoration-none text-success fw-semibold">
                                                        <i class="ti ti-brand-whatsapp me-1"></i><?= htmlspecialchars($u['no_hp']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted small">Belum ada</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><code class="px-2 py-1 bg-light rounded text-secondary fw-semibold"><?= htmlspecialchars($u['username']) ?></code></td>
                                            <td><span class="badge bg-warning text-dark rounded-pill px-3 py-1">Koordinator</span></td>
                                            <td>
                                                <small class="text-muted"><i class="ti ti-clock me-1"></i><?= !empty($u['login_at']) ? date('d/m/Y H:i', strtotime($u['login_at'])) : '-' ?></small>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-user rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1"
                                                    data-id="<?= $u['id'] ?>"
                                                    data-table="master_koordinator_blok"
                                                    data-username="<?= htmlspecialchars($u['username']) ?>"
                                                    data-role="Koordinator"
                                                    data-nama="<?= htmlspecialchars($u['nama']) ?>"
                                                    data-nohp="<?= htmlspecialchars($u['no_hp'] ?? '') ?>">
                                                    <i class="ti ti-pencil"></i> <span>Edit</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB 3: TARIF IURAN IPL (ADMIN ONLY)          -->
            <!-- ============================================ -->
            <div class="tab-pane fade <?= $active_tab == 'tarif' ? 'show active' : '' ?>" id="tarif-panel" role="tabpanel">
                
                <!-- 1. KARTU STATUS TAGIHAN AKTIF SAAT INI & MENDATANG -->
                <div class="row g-3 mb-4">
                    <!-- Status Tagihan Bulan Berjalan -->
                    <div class="col-12 col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border: 1.5px solid #86efac !important;">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5" style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:0.75rem;">
                                            <span class="spinner-grow spinner-grow-sm text-success" role="status" style="width:7px; height:7px;"></span>
                                            STATUS TAGIHAN AKTIF SAAT INI
                                        </span>
                                        <small class="text-muted fw-semibold"><i class="ti ti-calendar-event me-1"></i>Bulan Ini: <?= formatBulanTahun(date('Y-m-01')) ?></small>
                                    </div>

                                    <div class="d-flex align-items-baseline gap-2 mb-2">
                                        <h2 class="fw-bold mb-0 text-dark font-monospace" style="letter-spacing: -0.5px;">
                                            Rp <?= number_format($info_tarif['tarif_sekarang'], 0, ',', '.') ?>
                                        </h2>
                                        <span class="text-muted fw-semibold">/ bulan</span>
                                    </div>

                                    <div class="p-2.5 rounded-3 mb-2" style="background: rgba(220, 252, 231, 0.45); border: 1px dashed #86efac;">
                                        <div class="small text-dark fw-bold mb-1">
                                            <i class="ti ti-bookmark text-success me-1"></i><?= htmlspecialchars($info_tarif['record_sekarang']['nama_tarif'] ?? 'Tarif Standar Paguyuban') ?>
                                        </div>
                                        <div class="small text-muted">
                                            Periode Berlaku: 
                                            <strong class="text-dark">
                                                <?= $info_tarif['record_sekarang']['periode_mulai'] ?? 'Awal' ?> 
                                                s.d 
                                                <?= !empty($info_tarif['record_sekarang']['periode_selesai']) ? $info_tarif['record_sekarang']['periode_selesai'] : 'Seterusnya' ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="small text-success d-flex align-items-center gap-1.5 mt-2">
                                    <i class="ti ti-check-circle-fill"></i>
                                    <span>Digunakan otomatis untuk entri pembayaran bulan berjalan & tunggakan lama</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Tagihan Periode Baru / Mendatang -->
                    <div class="col-12 col-md-6">
                        <div class="card border-0 rounded-4 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border: 1.5px solid #93c5fd !important;">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5" style="background:#e0f2fe; color:#0284c7; border:1px solid #bae6fd; font-size:0.75rem;">
                                            <i class="ti ti-calendar-up"></i>
                                            TAGIHAN PERIODE BERIKUTNYA
                                        </span>
                                        <small class="text-primary fw-semibold"><i class="ti ti-clock me-1"></i>Mulai <?= formatBulanTahun($info_tarif['bulan_berikutnya'] . '-01') ?></small>
                                    </div>

                                    <div class="d-flex align-items-baseline gap-2 mb-2">
                                        <h2 class="fw-bold mb-0 text-primary font-monospace" style="letter-spacing: -0.5px;">
                                            Rp <?= number_format($info_tarif['tarif_berikutnya'], 0, ',', '.') ?>
                                        </h2>
                                        <span class="text-muted fw-semibold">/ bulan</span>
                                    </div>

                                    <div class="p-2.5 rounded-3 mb-2" style="background: rgba(224, 242, 254, 0.45); border: 1px dashed #93c5fd;">
                                        <div class="small text-dark fw-bold mb-1">
                                            <i class="ti ti-bookmark text-primary me-1"></i><?= htmlspecialchars($info_tarif['record_berikutnya']['nama_tarif'] ?? 'Penyesuaian Tarif Baru IPL') ?>
                                        </div>
                                        <div class="small text-muted">
                                            Periode Berlaku: 
                                            <strong class="text-dark">
                                                <?= $info_tarif['record_berikutnya']['periode_mulai'] ?? $info_tarif['bulan_berikutnya'] ?> 
                                                s.d 
                                                <?= !empty($info_tarif['record_berikutnya']['periode_selesai']) ? $info_tarif['record_berikutnya']['periode_selesai'] : 'Seterusnya' ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="small text-primary d-flex align-items-center gap-1.5 mt-2">
                                    <i class="ti ti-info-circle-filled"></i>
                                    <span>Otomatis aktif saat warga/koordinator memilih bulan periode tersebut</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. TABEL CRUD RIWAYAT PERIODE TARIF IPL (DATABASE RECORD) -->
                <div class="card card-custom mb-4">
                    <div class="panel-header-soft d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="ti ti-database text-primary"></i> Riwayat & Kebijakan Tarif IPL di Database
                            </h5>
                            <span class="text-muted small">Tercatat permanen di tabel database <code>master_tarif_ipl</code>. Bebas tambah & atur tanggal berlaku jika ada perubahan nominal.</span>
                        </div>
                        <button type="button" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-1.5 btn-add-tarif">
                            <i class="ti ti-plus fs-5"></i> <span>Tambah Periode Tarif</span>
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tableMasterTarif">
                                <thead style="background:#f8fafc; border-bottom: 1.5px solid #e2e8f0;">
                                    <tr>
                                        <th class="text-center py-3" style="width: 50px;">No</th>
                                        <th class="py-3">Kebijakan / Nama Tarif</th>
                                        <th class="py-3">Besaran Tagihan</th>
                                        <th class="py-3">Periode Berlaku</th>
                                        <th class="py-3 text-center">Status Periode</th>
                                        <th class="py-3 text-center">Status</th>
                                        <th class="py-3">Keterangan</th>
                                        <th class="py-3 text-center" style="width: 140px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    $bulan_current = date('Y-m');
                                    foreach ($list_tarif as $t): 
                                        $mulai = $t['periode_mulai'];
                                        $selesai = $t['periode_selesai'];
                                        $is_active = (int)$t['is_active'];

                                        // Cek status periode
                                        if (!$is_active) {
                                            $badge_periode = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">Dinonaktifkan</span>';
                                        } elseif ($mulai <= $bulan_current && (empty($selesai) || $selesai >= $bulan_current)) {
                                            $badge_periode = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold"><i class="ti ti-player-play me-1"></i>Aktif Berjalan</span>';
                                        } elseif ($mulai > $bulan_current) {
                                            $badge_periode = '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold"><i class="ti ti-clock me-1"></i>Akan Datang</span>';
                                        } else {
                                            $badge_periode = '<span class="badge bg-light text-muted border rounded-pill px-2.5 py-1"><i class="ti ti-history me-1"></i>Riwayat Lampau</span>';
                                        }
                                    ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                            <td>
                                                <div class="fw-bold text-dark d-flex align-items-center gap-1.5">
                                                    <i class="ti ti-file-text text-primary"></i>
                                                    <span><?= htmlspecialchars($t['nama_tarif']) ?></span>
                                                </div>
                                                <small class="text-muted font-monospace" style="font-size:0.75rem;">ID Record: #<?= $t['id'] ?></small>
                                            </td>
                                            <td>
                                                <span class="fs-6 fw-bold font-monospace text-dark">
                                                    Rp <?= number_format((float)$t['nominal'], 0, ',', '.') ?>
                                                </span>
                                                <small class="text-muted d-block" style="font-size:0.75rem;">/ bulan per rumah</small>
                                            </td>
                                            <td>
                                                <div class="d-inline-flex align-items-center gap-1.5 p-1.5 px-2.5 rounded-3 bg-light border text-dark font-monospace small">
                                                    <strong><?= htmlspecialchars($mulai) ?></strong>
                                                    <i class="ti ti-arrow-right text-muted"></i>
                                                    <strong><?= !empty($selesai) ? htmlspecialchars($selesai) : 'Seterusnya' ?></strong>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <?= $badge_periode ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($is_active): ?>
                                                    <span class="badge rounded-pill bg-success px-2.5 py-1">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-secondary px-2.5 py-1">Non-Aktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-secondary"><?= htmlspecialchars($t['keterangan'] ?? '-') ?></small>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-1">
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-tarif rounded-pill px-2.5 shadow-sm d-inline-flex align-items-center gap-1"
                                                        data-id="<?= $t['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($t['nama_tarif'], ENT_QUOTES) ?>"
                                                        data-nominal="<?= $t['nominal'] ?>"
                                                        data-mulai="<?= htmlspecialchars($t['periode_mulai'], ENT_QUOTES) ?>"
                                                        data-selesai="<?= htmlspecialchars($t['periode_selesai'] ?? '', ENT_QUOTES) ?>"
                                                        data-active="<?= $t['is_active'] ?>"
                                                        data-keterangan="<?= htmlspecialchars($t['keterangan'] ?? '', ENT_QUOTES) ?>"
                                                        title="Edit Periode Tarif">
                                                        <i class="ti ti-pencil"></i> <span>Edit</span>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-tarif rounded-pill px-2.5 shadow-sm"
                                                        data-id="<?= $t['id'] ?>"
                                                        data-nama="<?= htmlspecialchars($t['nama_tarif'], ENT_QUOTES) ?>"
                                                        title="Hapus Kebijakan Tarif">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Panel Edukasi & Keamanan Data -->
                    <div class="card-footer bg-light p-3 p-md-4 border-top">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 col-md-8">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="ti ti-shield-check-filled text-success fs-4 mt-0.5"></i>
                                    <div class="small text-muted">
                                        <strong class="text-dark">Sistem Multi-Periode Otomatis & Terproteksi:</strong><br>
                                        Semua riwayat transaksi pembayaran lama warga tetap tersimpan utuh sesuai nominal saat bayar. Ketika koordinator mengentri pembayaran rapel atau bulan lampau, tarif otomatis disesuaikan dengan periode berlakunya di tabel database ini.
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4 text-md-end">
                                <span class="badge bg-white border text-secondary p-2 rounded-3 shadow-2xs font-monospace small">
                                    <i class="ti ti-database me-1 text-primary"></i> Tabel: master_tarif_ipl (<?= count($list_tarif) ?> Periode)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL: EDIT DATA RUMAH (MOBILE FRIENDLY)     -->
<!-- ============================================ -->
<div class="modal fade modal-custom" id="modalEditRumah" tabindex="-1" aria-labelledby="modalEditRumahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <!-- Header Soft -->
            <div class="modal-header-soft d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon-box shadow-sm">
                        <i class="ti ti-pencil fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditRumahLabel">Edit Data Rumah</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge rounded-pill fw-semibold" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.76rem; padding: 2px 10px;">
                                <i class="ti ti-map-pin me-1"></i><span id="modalAlamatBadge">-</span>
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formEditRumah" method="post" action="<?= base_url('setting/update-rumah') ?>">
                <input type="hidden" name="rumah_id" id="editRumahId">
                <input type="hidden" id="editRumahAlamat">
                
                <div class="modal-body p-4">
                    <!-- Info Panel Ringkasan Unit -->
                    <div class="modal-info-panel mb-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-map-pin text-primary fs-5"></i>
                            <div>
                                <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Unit Rumah / Alamat</div>
                                <div class="fw-bold text-dark font-monospace" id="displayRumahAlamat" style="font-size: 0.95rem;">-</div>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-muted px-2.5 py-1" style="background:#f1f5f9; border:1px solid #cbd5e1; font-size:0.72rem;">
                            ID: #<span id="displayRumahId">-</span>
                        </span>
                    </div>

                    <!-- Nama Pemilik / Penghuni -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-user text-primary"></i> Nama Pemilik / Penghuni <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama" id="editRumahNama" class="form-control form-control-modal" placeholder="Contoh: Bpk. Bambang Sutrisno" required>
                        <div class="form-text small text-muted mt-1" style="font-size: 0.75rem;">
                            <i class="ti ti-info-circle me-1"></i>Nama kepala keluarga atau penanggung jawab pembayaran IPL
                        </div>
                    </div>

                    <!-- Row 2 Kolom: Status Rumah & No WhatsApp -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-home-cog text-info"></i> Status Rumah
                            </label>
                            <select name="status_rumah" id="editRumahStatus" class="form-select form-select-modal">
                                <option value="Rumah Sendiri">Rumah Sendiri</option>
                                <option value="kontrak">Sewa / Kontrak</option>
                                <option value="musiman">Musiman</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-brand-whatsapp text-success"></i> No. WhatsApp Aktif
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-success border-end-0" style="border: 1.5px solid #cbd5e1; border-radius: 12px 0 0 12px; border-right: none !important;">
                                    <i class="ti ti-brand-whatsapp fs-5"></i>
                                </span>
                                <input type="tel" name="no_hp" id="editRumahNoHp" class="form-control form-control-modal border-start-0" style="border-radius: 0 12px 12px 0;" placeholder="08xxxxxxxxxx">
                            </div>
                        </div>
                    </div>

                    <!-- Koordinator Blok (Admin Only) -->
                    <?php if ($is_admin): ?>
                    <div class="mb-1">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-user-check text-warning"></i> Koordinator Blok Wilayah
                        </label>
                        <select name="id_koordinator" id="editRumahIdKoor" class="form-select form-select-modal">
                            <option value="">-- Pilih Koordinator Blok --</option>
                            <?php foreach ($koordinator_users as $ku): ?>
                                <option value="<?= $ku['id'] ?>"><?= htmlspecialchars($ku['nama']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer Soft -->
                <div class="modal-footer-soft d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitRumah" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="ti ti-device-floppy fs-6"></i> <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- MODAL: EDIT DATA USER                        -->
<!-- ============================================ -->
<?php if ($is_admin): ?>
<div class="modal fade modal-custom" id="modalEditUser" tabindex="-1" aria-labelledby="modalEditUserLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <!-- Header Soft -->
            <div class="modal-header-soft d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon-box shadow-sm" style="background:#ede9fe; color:#7c3aed;">
                        <i class="ti ti-user-edit fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalEditUserLabel">Edit Akun Pengguna</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span id="modalUserRoleBadge" class="badge rounded-pill fw-semibold" style="background:#ede9fe; color:#7c3aed; border:1px solid #ddd6fe; font-size: 0.76rem; padding: 2px 10px;">Role</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditUser" method="post" action="<?= base_url('setting/update-user') ?>">
                <input type="hidden" name="user_id" id="editUserId">
                <input type="hidden" name="user_table" id="editUserTable">
                <div class="modal-body p-4">
                    <!-- Username (Readonly Info Panel) -->
                    <div class="modal-info-panel mb-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-shield-lock text-primary fs-5"></i>
                            <div>
                                <div class="text-muted" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Username Akun</div>
                                <code class="fw-bold text-primary font-monospace" id="displayUserUsername" style="font-size: 0.95rem;">-</code>
                            </div>
                        </div>
                        <span class="badge rounded-pill text-muted px-2.5 py-1" style="background:#f1f5f9; border:1px solid #cbd5e1; font-size:0.72rem;">Permanen</span>
                    </div>
                    <input type="hidden" id="editUserUsername">

                    <!-- Nama Lengkap -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-user text-primary"></i> Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama" id="editUserNama" class="form-control form-control-modal" placeholder="Nama Lengkap" required>
                    </div>

                    <!-- Nomor WhatsApp (Khusus Koordinator) -->
                    <div class="mb-2" id="divUserNoHp">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-brand-whatsapp text-success"></i> No. WhatsApp Aktif
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-success border-end-0" style="border: 1.5px solid #cbd5e1; border-radius: 12px 0 0 12px; border-right: none !important;">
                                <i class="ti ti-brand-whatsapp fs-5"></i>
                            </span>
                            <input type="tel" name="no_hp" id="editUserNoHp" class="form-control form-control-modal border-start-0" style="border-radius: 0 12px 12px 0;" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                </div>
                <!-- Footer Soft -->
                <div class="modal-footer-soft d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitUser" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="ti ti-device-floppy fs-6"></i> <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============================================ -->
<!-- MODAL: TAMBAH / EDIT PERIODE TARIF IPL       -->
<!-- ============================================ -->
<?php if ($is_admin): ?>
<div class="modal fade modal-custom" id="modalTarif" tabindex="-1" aria-labelledby="modalTarifLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">
            <!-- Header Soft -->
            <div class="modal-header-soft d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-icon-box shadow-sm" style="background:#e0f2fe; color:#0284c7;">
                        <i class="ti ti-coin fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="modalTarifLabel">Tambah Periode Tarif Baru</h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge rounded-pill fw-semibold" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 0.76rem; padding: 2px 10px;">
                                Database <code>master_tarif_ipl</code>
                            </span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formModalTarif" method="post" action="<?= base_url('setting/save-tarif') ?>">
                <input type="hidden" name="id_tarif" id="tarifId">

                <div class="modal-body p-4">
                    <!-- Kebijakan / Nama Tarif -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-file-text text-primary"></i> Nama Kebijakan / Label Tarif <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama_tarif" id="tarifNama" class="form-control form-control-modal" placeholder="Contoh: Penyesuaian Tarif Baru IPL 2026" required>
                        <div class="form-text small text-muted mt-1" style="font-size: 0.75rem;">
                            Nama penanda kebijakan penyesuaian tarif iuran bulanan
                        </div>
                    </div>

                    <!-- Nominal Tagihan (Rp) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-cash text-success"></i> Besaran Nominal Tagihan (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold" style="border: 1.5px solid #cbd5e1; border-radius: 12px 0 0 12px; border-right: none !important;">Rp</span>
                            <input type="number" name="nominal" id="tarifNominal" class="form-control form-control-modal border-start-0" style="border-radius: 0 12px 12px 0;" placeholder="Contoh: 140000" min="1000" step="1000" required>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span class="badge bg-light text-success border font-monospace fw-bold" id="previewModalNominal">Rp 0</span>
                            <span class="small text-muted" style="font-size: 0.75rem;">/ bulan per rumah</span>
                        </div>
                    </div>

                    <!-- Range Periode: Mulai s.d Selesai -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-calendar-event text-primary"></i> Periode Mulai (Bulan) <span class="text-danger">*</span>
                            </label>
                            <input type="month" name="periode_mulai" id="tarifMulai" class="form-control form-control-modal" required>
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Bulan pertama tarif berlaku</div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                                <i class="ti ti-calendar-event text-secondary"></i> Periode Selesai (Bulan)
                            </label>
                            <input type="month" name="periode_selesai" id="tarifSelesai" class="form-control form-control-modal">
                            <div class="small text-muted mt-1" style="font-size: 0.72rem;">Kosongkan jika berlaku seterusnya</div>
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-toggle-right text-info"></i> Status Kebijakan
                        </label>
                        <select name="is_active" id="tarifIsActive" class="form-select form-select-modal">
                            <option value="1">Aktif (Dapat digunakan dalam perhitungan & tagihan)</option>
                            <option value="0">Non-Aktif (Diarsipkan / tidak diterapkan)</option>
                        </select>
                    </div>

                    <!-- Keterangan / Catatan Kebijakan -->
                    <div class="mb-1">
                        <label class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1">
                            <i class="ti ti-notes text-secondary"></i> Keterangan / Catatan
                        </label>
                        <textarea name="keterangan" id="tarifKeterangan" class="form-control form-control-modal" rows="2" placeholder="Catatan hasil musyawarah warga atau dasar penetapan nominal..."></textarea>
                    </div>
                </div>

                <!-- Footer Soft -->
                <div class="modal-footer-soft d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-light border rounded-pill px-4 py-2 text-secondary fw-semibold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btnSubmitTarifModal" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="ti ti-device-floppy fs-6"></i> <span>Simpan Periode Tarif</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- TOAST NOTIFICATION CONTAINER -->
<div id="settingToast" class="toast align-items-center text-white bg-success border-0 shadow-lg rounded-4" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex p-2">
        <div class="toast-body d-flex align-items-center gap-2 fs-6">
            <i class="ti ti-circle-check fs-4"></i>
            <span id="toastMsg">Data berhasil diperbarui!</span>
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
</div>

<!-- ============================================ -->
<!-- JAVASCRIPT: DATATABLES & MODAL HANDLERS      -->
<!-- ============================================ -->
<script>
$(document).ready(function() {
    // Inisialisasi Toast
    const toastEl = document.getElementById('settingToast');
    const toastObj = toastEl ? new bootstrap.Toast(toastEl, { delay: 4000 }) : null;

    function showToast(message, isSuccess = true) {
        if (!toastEl || !toastObj) return;
        $('#toastMsg').text(message);
        if (isSuccess) {
            $('#settingToast').removeClass('bg-danger').addClass('bg-success');
        } else {
            $('#settingToast').removeClass('bg-success').addClass('bg-danger');
        }
        toastObj.show();
    }

    // ============================================
    // 1. DATATABLES SERVER-SIDE (DATA RUMAH)
    // ============================================
    const tblRumah = $('#tblSettingRumah').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        order: [[1, 'asc']], // Order by Alamat ASC
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: "<?= base_url('ajax-setting-rumah') ?>",
            type: "POST",
            data: function(d) {
                d.filter_koordinator = $('#filterKoordinator').val();
                d.filter_status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 0, orderable: false, className: "text-center" },
            { data: 1 },
            { data: 2 },
            { data: 3 },
            { data: 4 },
            { data: 5 },
            { data: 6, orderable: false, className: "text-center" }
        ],
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Memuat data rumah...',
            search: "Cari:",
            searchPlaceholder: "Ketik alamat / nama...",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ s/d _END_ dari _TOTAL_ rumah",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            zeroRecords: "Tidak ada data rumah yang cocok",
            paginate: {
                first: "«",
                previous: "‹",
                next: "›",
                last: "»"
            }
        }
    });

    // Sesuaikan lebar kolom saat ganti tab
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // Filter Trigger
    $('#filterKoordinator, #filterStatus').on('change', function() {
        tblRumah.ajax.reload();
    });

    $('#btnResetFilter').on('click', function() {
        $('#filterKoordinator').val('');
        $('#filterStatus').val('');
        tblRumah.search('').draw();
        tblRumah.ajax.reload();
    });

    // Inisialisasi DataTable User (Client-side fast)
    if ($('#tblSettingUser').length) {
        $('#tblSettingUser').DataTable({
            pageLength: 15,
            language: {
                search: "Cari:",
                searchPlaceholder: "Cari user...",
                lengthMenu: "_MENU_",
                info: "_TOTAL_ akun",
                zeroRecords: "Data user tidak ditemukan"
            }
        });
    }

    // ============================================
    // 2. MODAL EDIT RUMAH
    // ============================================
    $(document).on('click', '.btn-edit-rumah', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const alamat = $(this).data('alamat');
        const nama = $(this).data('nama');
        const status = $(this).data('status');
        const nohp = $(this).data('nohp');
        const idkoor = $(this).data('idkoor');

        $('#editRumahId').val(id);
        $('#displayRumahId').text(id);
        $('#editRumahAlamat').val(alamat);
        $('#displayRumahAlamat').text(alamat);
        $('#modalAlamatBadge').text(alamat);
        $('#editRumahNama').val(nama);

        let st = (status || 'Rumah Sendiri').toString().toLowerCase();
        if (st.indexOf('kontrak') !== -1 || st.indexOf('sewa') !== -1) {
            $('#editRumahStatus').val('kontrak');
        } else if (st.indexOf('musiman') !== -1) {
            $('#editRumahStatus').val('musiman');
        } else {
            $('#editRumahStatus').val('Rumah Sendiri');
        }

        $('#editRumahNoHp').val(nohp || '');
        if ($('#editRumahIdKoor').length) {
            $('#editRumahIdKoor').val(idkoor || '');
        }

        $('#modalEditRumah').modal('show');
    });

    // Submit Edit Rumah via AJAX
    $('#formEditRumah').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnSubmitRumah');
        const oldHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html(oldHtml);
                if (res.status === 'success') {
                    $('#modalEditRumah').modal('hide');
                    tblRumah.ajax.reload(null, false);
                    showToast(res.message, true);
                } else {
                    alert(res.message || 'Gagal menyimpan perubahan.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(oldHtml);
                alert('Terjadi kendala saat menghubungi server.');
            }
        });
    });

    // ============================================
    // 3. MODAL EDIT USER
    // ============================================
    $(document).on('click', '.btn-edit-user', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const table = $(this).data('table');
        const username = $(this).data('username');
        const role = $(this).data('role');
        const nama = $(this).data('nama');
        const nohp = $(this).data('nohp');

        $('#editUserId').val(id);
        $('#editUserTable').val(table);
        $('#editUserUsername').val(username);
        $('#displayUserUsername').text(username);
        $('#modalUserRoleBadge').text(role);
        $('#editUserNama').val(nama);
        $('#editUserNoHp').val(nohp || '');

        if (table === 'master_koordinator_blok') {
            $('#divUserNoHp').show();
        } else {
            $('#divUserNoHp').hide();
        }

        $('#modalEditUser').modal('show');
    });

    // Submit Edit User via AJAX
    $('#formEditUser').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnSubmitUser');
        const oldHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html(oldHtml);
                if (res.status === 'success') {
                    $('#modalEditUser').modal('hide');
                    showToast(res.message, true);
                    // Reload page smoothly to reflect user name update
                    setTimeout(function() {
                        location.reload();
                    }, 800);
                } else {
                    alert(res.message || 'Gagal menyimpan perubahan.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(oldHtml);
                alert('Terjadi kendala saat menghubungi server.');
            }
        });
    });

    // ============================================
    // 4. CRUD MASTER TARIF IPL (DATABASE)
    // ============================================
    // Live Format Preview Rupiah di Modal
    $('#tarifNominal').on('input', function() {
        const val = parseFloat($(this).val()) || 0;
        $('#previewModalNominal').text('Rp ' + val.toLocaleString('id-ID'));
    });

    // Tambah Tarif Baru
    $(document).on('click', '.btn-add-tarif', function(e) {
        e.preventDefault();
        $('#formModalTarif')[0].reset();
        $('#tarifId').val('');
        $('#modalTarifLabel').text('Tambah Periode Tarif Baru');
        $('#previewModalNominal').text('Rp 0');
        $('#tarifIsActive').val('1');
        $('#modalTarif').modal('show');
    });

    // Edit Periode Tarif
    $(document).on('click', '.btn-edit-tarif', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const nominal = parseFloat($(this).data('nominal')) || 0;
        const mulai = $(this).data('mulai');
        const selesai = $(this).data('selesai') || '';
        const active = $(this).data('active');
        const keterangan = $(this).data('keterangan') || '';

        $('#tarifId').val(id);
        $('#tarifNama').val(nama);
        $('#tarifNominal').val(nominal);
        $('#previewModalNominal').text('Rp ' + nominal.toLocaleString('id-ID'));
        $('#tarifMulai').val(mulai);
        $('#tarifSelesai').val(selesai);
        $('#tarifIsActive').val(active ? '1' : '0');
        $('#tarifKeterangan').val(keterangan);

        $('#modalTarifLabel').text('Edit Periode Kebijakan Tarif');
        $('#modalTarif').modal('show');
    });

    // Submit Form Tambah / Edit Tarif via AJAX
    $('#formModalTarif').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnSubmitTarifModal');
        const oldHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html(oldHtml);
                if (res.status === 'success') {
                    $('#modalTarif').modal('hide');
                    showToast(res.message, true);
                    setTimeout(function() {
                        window.location.href = '<?= base_url("setting?tab=tarif") ?>';
                    }, 700);
                } else {
                    alert(res.message || 'Gagal menyimpan periode tarif.');
                }
            },
            error: function() {
                btn.prop('disabled', false).html(oldHtml);
                alert('Terjadi kendala saat menghubungi server.');
            }
        });
    });

    // Hapus Periode Tarif
    $(document).on('click', '.btn-delete-tarif', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const nama = $(this).data('nama');

        if (!confirm('Apakah Anda yakin ingin menghapus kebijakan tarif "' + nama + '"?\nData yang dihapus tidak dapat dikembalikan.')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '<?= base_url("setting/delete-tarif/") ?>' + id,
            type: 'POST',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    showToast(res.message, true);
                    setTimeout(function() {
                        window.location.href = '<?= base_url("setting?tab=tarif") ?>';
                    }, 700);
                } else {
                    btn.prop('disabled', false);
                    alert(res.message || 'Gagal menghapus tarif.');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Terjadi kendala saat menghubungi server.');
            }
        });
    });
});
</script>