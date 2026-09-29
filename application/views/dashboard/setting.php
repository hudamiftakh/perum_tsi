<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');

// Cek role
$is_admin = ($Auth['role'] === 'admin');

// Tab aktif (default: users untuk admin, rumah untuk non-admin)
$active_tab = $this->input->get('tab');
if (empty($active_tab)) {
    $active_tab = $is_admin ? 'users' : 'rumah';
}
// Jika ada pencarian atau filter rumah, otomatis aktifkan tab rumah
if (!empty($this->input->get('keyword_rumah')) || !empty($this->input->get('filter_koordinator')) || !empty($this->input->get('filter_status'))) {
    $active_tab = 'rumah';
}

// ============================================
// DATA USERS (dari master_admin + master_koordinator_blok)
// ============================================
$admin_users = $this->db->query("SELECT id, nama, username, 'admin' as role, login_at, NULL as no_hp FROM master_admin ORDER BY nama ASC")->result_array();
$koordinator_users = $this->db->query("SELECT id, nama, username, 'koordinator' as role, login_at, no_hp FROM master_koordinator_blok ORDER BY nama ASC")->result_array();

// ============================================
// DATA RUMAH (dari master_rumah + status_rumah)
// ============================================
$keyword_rumah = $this->input->get('keyword_rumah');
$filter_koordinator = $this->input->get('filter_koordinator');
$filter_status = $this->input->get('filter_status');

$where_clauses = [];
if (!empty($keyword_rumah)) {
    $kw = $this->db->escape_str(trim($keyword_rumah));
    $where_clauses[] = "(r.nama LIKE '%$kw%' OR r.alamat LIKE '%$kw%')";
}

// Batasi data rumah sesuai role
if (!$is_admin) {
    // Jika koordinator, paksa hanya muncul rumah binaannya
    $fk = $this->db->escape_str($Auth['id']);
    $where_clauses[] = "r.id_koordinator = '$fk'";
} else {
    // Jika admin, cek filter koordinator
    if (!empty($filter_koordinator)) {
        $fk = $this->db->escape_str($filter_koordinator);
        $where_clauses[] = "r.id_koordinator = '$fk'";
    }
}

// Filter status rumah jika dipilih
if (!empty($filter_status)) {
    $fs = $this->db->escape_str(trim($filter_status));
    $where_clauses[] = "COALESCE(r.status_rumah, kl.status_rumah, 'Rumah Sendiri') = '$fs'";
}

$where_rumah = '';
if (!empty($where_clauses)) {
    $where_rumah = "WHERE " . implode(' AND ', $where_clauses);
}

$data_rumah = $this->db->query("
    SELECT r.id, r.alamat, r.nama, k.nama as koordinator, 
           COALESCE(r.status_rumah, MAX(kl.status_rumah), 'Rumah Sendiri') as status_rumah,
           COALESCE(r.no_hp, MAX(kl.no_hp)) as no_hp
    FROM master_rumah r
    LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
    LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
    $where_rumah
    GROUP BY r.id, r.alamat, r.nama, r.status_rumah, r.no_hp, k.nama
    ORDER BY r.alamat ASC
")->result_array();

// Hitung statistik untuk badge ringkasan
$count_sendiri = 0;
$count_kontrak = 0;
$count_musiman = 0;
foreach ($data_rumah as $dr) {
    $st = trim($dr['status_rumah'] ?? '');
    if (stripos($st, 'kontrak') !== false || stripos($st, 'sewa') !== false) {
        $count_kontrak++;
    } elseif (stripos($st, 'musiman') !== false) {
        $count_musiman++;
    } else {
        $count_sendiri++;
    }
}
?>

<style>
    /* Clean, modern card & tabs design */
    .setting-nav-pills .nav-link {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        color: #495057;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        margin-right: 8px;
        transition: all 0.2s ease-in-out;
    }

    .setting-nav-pills .nav-link:hover {
        background-color: #edf2f7;
        color: #1e293b;
    }

    .setting-nav-pills .nav-link.active {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #ffffff;
        border-color: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
    }

    .card-custom {
        border: none;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        background: #ffffff;
    }

    /* Filter Box */
    .filter-container {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
    }

    /* Table styles */
    .table-custom-container {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #edf2f7;
    }

    .table-custom {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin-bottom: 0;
    }

    .table-custom thead {
        background: linear-gradient(135deg, #10b981, #059669);
    }

    .table-custom thead th {
        color: #ffffff;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        padding: 12px 14px;
        vertical-align: middle;
        border: none;
    }

    .table-custom tbody tr {
        transition: background-color 0.15s ease-in-out;
    }

    .table-custom tbody tr:hover {
        background-color: #f1fdf4 !important;
    }

    .table-custom tbody td {
        padding: 10px 14px;
        vertical-align: middle;
        font-size: 0.88rem;
        border-top: 1px solid #f1f5f9;
        color: #334155;
    }

    /* Editing row highlight */
    tr.is-editing {
        background-color: #fffbeb !important;
        box-shadow: inset 3px 0 0 #f59e0b;
    }

    .badge-status-sendiri {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 8px;
    }

    .badge-status-kontrak {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 8px;
    }

    .badge-status-musiman {
        background-color: #e0f2fe;
        color: #075985;
        border: 1px solid #bae6fd;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 8px;
    }

    .stat-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.82rem;
        font-weight: 600;
    }
</style>

<div class="container-fluid px-0">
    <!-- FLASH MESSAGES -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
            <div><?= $this->session->flashdata('success') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
            <div><?= $this->session->flashdata('error') ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- HEADER TITLE & TABS -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="bi bi-gear-fill text-success me-2"></i>Pengaturan Data
            </h4>
            <p class="text-muted small mb-0">Kelola informasi akun pengguna dan data kepemilikan rumah warga</p>
        </div>

        <!-- NAV TABS -->
        <ul class="nav setting-nav-pills" id="settingTabs" role="tablist">
            <?php if ($is_admin): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $active_tab == 'users' ? 'active' : '' ?>" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-panel" type="button" role="tab">
                        <i class="bi bi-people-fill me-1"></i> Data User
                    </button>
                </li>
            <?php endif; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab == 'rumah' ? 'active' : '' ?>" id="rumah-tab" data-bs-toggle="tab" data-bs-target="#rumah-panel" type="button" role="tab">
                    <i class="bi bi-house-door-fill me-1"></i> Data Pemilik Rumah
                </button>
            </li>
        </ul>
    </div>

    <!-- TAB CONTENT -->
    <div class="tab-content" id="settingTabsContent">

        <!-- ============================================ -->
        <!-- TAB 1: DATA USER                            -->
        <!-- ============================================ -->
        <?php if ($is_admin): ?>
            <div class="tab-pane fade <?= $active_tab == 'users' ? 'show active' : '' ?>" id="users-panel" role="tabpanel">
                <div class="card card-custom mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Daftar User Sistem</h5>
                                <span class="text-muted small">Kelola akun Admin & Koordinator Blok</span>
                            </div>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fs-7 fw-semibold">
                                <i class="bi bi-people-fill me-1"></i> <?= count($admin_users) + count($koordinator_users) ?> Akun
                            </span>
                        </div>

                        <div class="table-custom-container">
                            <div class="table-responsive">
                                <table class="table table-custom mb-0">
                                    <thead style="background: linear-gradient(135deg, #0d6efd, #0b5ed7);">
                                        <tr>
                                            <th style="width: 50px;" class="text-center">No</th>
                                            <th>Nama Lengkap</th>
                                            <th>No. WhatsApp</th>
                                            <th>Username</th>
                                            <th>Role</th>
                                            <th>Login Terakhir</th>
                                            <th style="width: 130px;" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <!-- ADMIN USERS -->
                                        <?php foreach ($admin_users as $u): ?>
                                            <tr id="user-admin-<?= $u['id'] ?>">
                                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                                <td>
                                                    <span class="display-val fw-semibold">
                                                        <i class="bi bi-shield-lock-fill text-primary me-1"></i>
                                                        <?= htmlspecialchars($u['nama']) ?>
                                                    </span>
                                                    <div class="edit-val d-none">
                                                        <form id="form-user-admin-<?= $u['id'] ?>" method="post" action="<?= base_url('setting/update-user') ?>">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <input type="hidden" name="user_table" value="master_admin">
                                                        </form>
                                                        <input type="text" name="nama" form="form-user-admin-<?= $u['id'] ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($u['nama']) ?>" required>
                                                    </div>
                                                </td>
                                                <td><span class="text-muted">-</span></td>
                                                <td><code class="px-2 py-1 bg-light rounded text-primary fw-semibold"><?= htmlspecialchars($u['username']) ?></code></td>
                                                <td>
                                                    <span class="badge bg-primary px-2 py-1 rounded-pill"><?= ucfirst($u['role']) ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><i class="bi bi-clock me-1"></i><?= !empty($u['login_at']) ? date('d/m/Y H:i', strtotime($u['login_at'])) : '-' ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="display-val">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-start-edit px-2 py-1 shadow-sm" title="Edit Nama">
                                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                                        </button>
                                                    </div>
                                                    <div class="edit-val d-none d-flex gap-1 justify-content-center">
                                                        <button type="submit" form="form-user-admin-<?= $u['id'] ?>" class="btn btn-sm btn-success px-2 py-1 shadow-sm" title="Simpan">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit px-2 py-1" title="Batal">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>

                                        <!-- KOORDINATOR USERS -->
                                        <?php foreach ($koordinator_users as $u): ?>
                                            <tr id="user-koor-<?= $u['id'] ?>">
                                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                                <td>
                                                    <span class="display-val fw-semibold">
                                                        <i class="bi bi-person-fill text-warning me-1"></i>
                                                        <?= htmlspecialchars($u['nama']) ?>
                                                    </span>
                                                    <div class="edit-val d-none">
                                                        <form id="form-user-koor-<?= $u['id'] ?>" method="post" action="<?= base_url('setting/update-user') ?>">
                                                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                            <input type="hidden" name="user_table" value="master_koordinator_blok">
                                                        </form>
                                                        <input type="text" name="nama" form="form-user-koor-<?= $u['id'] ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($u['nama']) ?>" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="display-val">
                                                        <?php if (!empty($u['no_hp'])): ?>
                                                            <a href="https://wa.me/<?= preg_replace('/^0/', '62', $u['no_hp']) ?>" target="_blank" class="text-decoration-none text-success fw-medium">
                                                                <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($u['no_hp']) ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Belum ada</span>
                                                        <?php endif; ?>
                                                    </span>
                                                    <div class="edit-val d-none">
                                                        <input type="text" name="no_hp" form="form-user-koor-<?= $u['id'] ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($u['no_hp'] ?? '') ?>" placeholder="08xxx">
                                                    </div>
                                                </td>
                                                <td><code class="px-2 py-1 bg-light rounded text-secondary fw-semibold"><?= htmlspecialchars($u['username']) ?></code></td>
                                                <td>
                                                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill"><?= ucfirst($u['role']) ?></span>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><i class="bi bi-clock me-1"></i><?= !empty($u['login_at']) ? date('d/m/Y H:i', strtotime($u['login_at'])) : '-' ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="display-val">
                                                        <button type="button" class="btn btn-sm btn-outline-primary btn-start-edit px-2 py-1 shadow-sm" title="Edit Profil">
                                                            <i class="bi bi-pencil-square me-1"></i>Edit
                                                        </button>
                                                    </div>
                                                    <div class="edit-val d-none d-flex gap-1 justify-content-center">
                                                        <button type="submit" form="form-user-koor-<?= $u['id'] ?>" class="btn btn-sm btn-success px-2 py-1 shadow-sm" title="Simpan">
                                                            <i class="bi bi-check-lg"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit px-2 py-1" title="Batal">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ============================================ -->
        <!-- TAB 2: DATA PEMILIK RUMAH                   -->
        <!-- ============================================ -->
        <div class="tab-pane fade <?= $active_tab == 'rumah' ? 'show active' : '' ?>" id="rumah-panel" role="tabpanel">
            <div class="card card-custom mb-4">
                <div class="card-body p-4">
                    <!-- HEADER TABEL & RINGKASAN STATS -->
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-1">Daftar Pemilik & Status Rumah</h5>
                            <span class="text-muted small">Kelola nama penghuni, kontak WhatsApp, serta status kepemilikan rumah</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="stat-pill bg-success text-white shadow-sm">
                                <i class="bi bi-houses-fill me-1"></i> <?= count($data_rumah) ?> Total Rumah
                            </span>
                            <span class="stat-pill badge-status-sendiri shadow-sm">
                                <i class="bi bi-house-check-fill me-1"></i> <?= $count_sendiri ?> Rumah Sendiri
                            </span>
                            <span class="stat-pill badge-status-kontrak shadow-sm">
                                <i class="bi bi-key-fill me-1"></i> <?= $count_kontrak ?> Sewa / Kontrak
                            </span>
                            <?php if ($count_musiman > 0): ?>
                                <span class="stat-pill badge-status-musiman shadow-sm">
                                    <i class="bi bi-clock-history me-1"></i> <?= $count_musiman ?> Musiman
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- FILTER & PENCARIAN -->
                    <div class="filter-container mb-4">
                        <form method="get" action="<?= base_url('setting') ?>" class="m-0">
                            <input type="hidden" name="tab" value="rumah">
                            <div class="row g-2 align-items-center">
                                <!-- Search Input -->
                                <div class="col-12 col-md-4 col-lg-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                        <input type="text" name="keyword_rumah" class="form-control border-start-0 ps-0" placeholder="Cari alamat atau nama..."
                                            value="<?= htmlspecialchars($keyword_rumah ?? '') ?>">
                                    </div>
                                </div>

                                <!-- Filter Koordinator (Admin Only) -->
                                <?php if ($is_admin): ?>
                                <div class="col-12 col-md-4 col-lg-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person-badge"></i></span>
                                        <select name="filter_koordinator" class="form-select border-start-0 ps-0">
                                            <option value="">Semua Koordinator</option>
                                            <?php foreach ($koordinator_users as $ku): ?>
                                                <option value="<?= $ku['id'] ?>" <?= ($filter_koordinator == $ku['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($ku['nama']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Filter Status Rumah -->
                                <div class="col-12 col-md-4 col-lg-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-house-gear"></i></span>
                                        <select name="filter_status" class="form-select border-start-0 ps-0">
                                            <option value="">Semua Status Rumah</option>
                                            <option value="Rumah Sendiri" <?= ($filter_status === 'Rumah Sendiri') ? 'selected' : '' ?>>Rumah Sendiri</option>
                                            <option value="Sewa/Kontrak" <?= ($filter_status === 'Sewa/Kontrak') ? 'selected' : '' ?>>Sewa / Kontrak</option>
                                            <option value="Musiman" <?= ($filter_status === 'Musiman') ? 'selected' : '' ?>>Musiman</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Buttons -->
                                <div class="col-12 col-md-auto ms-auto d-flex gap-2">
                                    <button class="btn btn-sm btn-success fw-semibold px-3 shadow-sm d-flex align-items-center gap-1" type="submit" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                                        <i class="bi bi-funnel-fill"></i> Terapkan
                                    </button>
                                    <?php if (!empty($keyword_rumah) || !empty($filter_koordinator) || !empty($filter_status)): ?>
                                        <a href="<?= base_url('setting?tab=rumah') ?>" class="btn btn-sm btn-light border text-danger px-3 shadow-sm" title="Reset Filter">
                                            <i class="bi bi-x-lg"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TABEL DATA RUMAH -->
                    <div class="table-custom-container">
                        <div class="table-responsive">
                            <table class="table table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 45px;" class="text-center">No</th>
                                        <th style="width: 170px;">Alamat Rumah</th>
                                        <th>Nama Pemilik / Penghuni</th>
                                        <th style="width: 160px;">Status Rumah</th>
                                        <th style="width: 170px;">No. WhatsApp</th>
                                        <th>Koordinator</th>
                                        <th style="width: 130px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($data_rumah)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                                Tidak ada data rumah yang cocok dengan filter.
                                            </td>
                                        </tr>
                                    <?php endif; ?>

                                    <?php $no = 1;
                                    foreach ($data_rumah as $r): 
                                        $curr_status = trim($r['status_rumah'] ?? '');
                                        if (empty($curr_status)) {
                                            $curr_status = 'Rumah Sendiri';
                                        }
                                        $is_kontrak = (stripos($curr_status, 'kontrak') !== false || stripos($curr_status, 'sewa') !== false);
                                        $is_musiman = (stripos($curr_status, 'musiman') !== false);
                                    ?>
                                        <tr id="rumah-<?= $r['id'] ?>">
                                            <!-- NO -->
                                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>

                                            <!-- ALAMAT RUMAH -->
                                            <td>
                                                <span class="fw-bold text-dark">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                    <?= htmlspecialchars($r['alamat']) ?>
                                                </span>
                                            </td>

                                            <!-- NAMA PEMILIK / PENGHUNI -->
                                            <td>
                                                <!-- Hidden Form Reference -->
                                                <form id="form-rumah-<?= $r['id'] ?>" method="post" action="<?= base_url('setting/update-rumah') ?>">
                                                    <input type="hidden" name="rumah_id" value="<?= $r['id'] ?>">
                                                </form>

                                                <!-- Mode Tampilan Normal -->
                                                <span class="display-val">
                                                    <i class="bi bi-person-fill text-primary me-1"></i>
                                                    <strong><?= htmlspecialchars($r['nama'] ?? '-') ?></strong>
                                                </span>

                                                <!-- Mode Edit Inline -->
                                                <div class="edit-val d-none">
                                                    <input type="text" name="nama" form="form-rumah-<?= $r['id'] ?>" 
                                                        class="form-control form-control-sm edit-input-name" 
                                                        value="<?= htmlspecialchars($r['nama'] ?? '') ?>" 
                                                        placeholder="Nama pemilik / penghuni">
                                                </div>
                                            </td>

                                            <!-- STATUS RUMAH (KONTRAK / RUMAH SENDIRI) -->
                                            <td>
                                                <!-- Mode Tampilan Normal -->
                                                <span class="display-val">
                                                    <?php if ($is_kontrak): ?>
                                                        <span class="badge-status-kontrak">
                                                            <i class="bi bi-key-fill me-1"></i>Sewa / Kontrak
                                                        </span>
                                                    <?php elseif ($is_musiman): ?>
                                                        <span class="badge-status-musiman">
                                                            <i class="bi bi-clock-history me-1"></i>Musiman
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge-status-sendiri">
                                                            <i class="bi bi-house-check-fill me-1"></i>Rumah Sendiri
                                                        </span>
                                                    <?php endif; ?>
                                                </span>

                                                <!-- Mode Edit Inline -->
                                                <div class="edit-val d-none">
                                                    <select name="status_rumah" form="form-rumah-<?= $r['id'] ?>" class="form-select form-select-sm">
                                                        <option value="Rumah Sendiri" <?= (!$is_kontrak && !$is_musiman) ? 'selected' : '' ?>>Rumah Sendiri</option>
                                                        <option value="Sewa/Kontrak" <?= $is_kontrak ? 'selected' : '' ?>>Sewa / Kontrak</option>
                                                        <option value="Musiman" <?= $is_musiman ? 'selected' : '' ?>>Musiman</option>
                                                    </select>
                                                </div>
                                            </td>

                                            <!-- NO WHATSAPP -->
                                            <td>
                                                <!-- Mode Tampilan Normal -->
                                                <span class="display-val">
                                                    <?php if (!empty($r['no_hp'])): ?>
                                                        <a href="https://wa.me/<?= preg_replace('/^0/', '62', $r['no_hp']) ?>" target="_blank" class="text-decoration-none text-success fw-medium">
                                                            <i class="bi bi-whatsapp me-1"></i><?= htmlspecialchars($r['no_hp']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Belum ada</span>
                                                    <?php endif; ?>
                                                </span>

                                                <!-- Mode Edit Inline -->
                                                <div class="edit-val d-none">
                                                    <input type="text" name="no_hp" form="form-rumah-<?= $r['id'] ?>" 
                                                        class="form-control form-control-sm" 
                                                        value="<?= htmlspecialchars($r['no_hp'] ?? '') ?>" 
                                                        placeholder="No WhatsApp (08xxx)">
                                                </div>
                                            </td>

                                            <!-- KOORDINATOR -->
                                            <td>
                                                <span class="badge bg-light text-secondary border px-2 py-1">
                                                    <i class="bi bi-person-badge me-1"></i><?= htmlspecialchars($r['koordinator'] ?? '-') ?>
                                                </span>
                                            </td>

                                            <!-- AKSI -->
                                            <td class="text-center">
                                                <!-- Mode Normal: Tombol Edit -->
                                                <div class="display-val">
                                                    <button type="button" class="btn btn-sm btn-outline-success btn-start-edit px-2 py-1 shadow-sm" title="Edit Data Rumah">
                                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                                    </button>
                                                </div>

                                                <!-- Mode Edit: Tombol Simpan & Batal -->
                                                <div class="edit-val d-none d-flex gap-1 justify-content-center">
                                                    <button type="submit" form="form-rumah-<?= $r['id'] ?>" class="btn btn-sm btn-success px-2 py-1 shadow-sm" title="Simpan Perubahan">
                                                        <i class="bi bi-check-lg me-1"></i>Simpan
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit px-2 py-1" title="Batal">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT: CLEAN INLINE EDITING INTERACTION -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Fungsi membatalkan semua baris yang sedang diedit
        function cancelAllEdits() {
            document.querySelectorAll('tr.is-editing').forEach(row => {
                row.classList.remove('is-editing');
                row.querySelectorAll('.display-val').forEach(el => el.classList.remove('d-none'));
                row.querySelectorAll('.edit-val').forEach(el => el.classList.add('d-none'));
            });
        }

        // Mulai Edit Baris
        document.querySelectorAll('.btn-start-edit').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                
                // Tutup form edit lain jika ada yang masih aktif
                cancelAllEdits();

                // Aktifkan baris yang diklik
                row.classList.add('is-editing');
                row.querySelectorAll('.display-val').forEach(el => el.classList.add('d-none'));
                row.querySelectorAll('.edit-val').forEach(el => el.classList.remove('d-none'));

                // Auto fokus ke input nama
                const inputName = row.querySelector('.edit-input-name, input[name="nama"]');
                if (inputName) {
                    inputName.focus();
                    inputName.select();
                }
            });
        });

        // Batal Edit
        document.querySelectorAll('.btn-cancel-edit').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                row.classList.remove('is-editing');
                row.querySelectorAll('.display-val').forEach(el => el.classList.remove('d-none'));
                row.querySelectorAll('.edit-val').forEach(el => el.classList.add('d-none'));
            });
        });

        // ESC key untuk membatalkan edit aktif
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cancelAllEdits();
            }
        });
    });
</script>