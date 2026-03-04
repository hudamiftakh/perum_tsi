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
// Jika ada keyword_rumah, otomatis pindah ke tab rumah
if (!empty($this->input->get('keyword_rumah'))) {
    $active_tab = 'rumah';
}

// ============================================
// DATA USERS (dari master_admin + master_koordinator_blok)
// ============================================
$admin_users = $this->db->query("SELECT id, nama, username, role, login_at FROM master_admin ORDER BY nama ASC")->result_array();
$koordinator_users = $this->db->query("SELECT id, nama, username, 'koordinator' as role, login_at FROM master_koordinator_blok ORDER BY nama ASC")->result_array();

// ============================================
// DATA RUMAH (dari master_rumah)
// ============================================
$keyword_rumah = $this->input->get('keyword_rumah');
$where_rumah = '';
if (!empty($keyword_rumah)) {
    $kw = $this->db->escape_str($keyword_rumah);
    $where_rumah = "WHERE r.nama LIKE '%$kw%' OR r.alamat LIKE '%$kw%'";
}
$data_rumah = $this->db->query("
    SELECT r.id, r.alamat, r.nama, k.nama as koordinator
    FROM master_rumah r
    LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
    $where_rumah
    ORDER BY r.alamat ASC
")->result_array();
?>

<style>
    /* Table styles matching existing theme */
    .table-striped {
        border-collapse: collapse;
        width: 100%;
    }

    .table-striped td {
        padding: 4px 8px;
        vertical-align: middle;
        font-size: 0.85rem;
    }

    .table-custom th {
        background-color: #0b0f19 !important;
        color: white !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
        text-align: center;
        padding: 10px 12px;
    }

    table tbody tr:hover {
        background-color: #f1fdf3;
    }

    table {
        border-radius: 15px;
        overflow: hidden;
    }

    .btn-edit-inline {
        border: none;
        background: none;
        color: #0d6efd;
        cursor: pointer;
        padding: 2px 6px;
    }

    .btn-edit-inline:hover {
        color: #0b5ed7;
    }

    .edit-input {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        width: 100%;
    }

    .edit-actions {
        display: flex;
        gap: 4px;
    }
</style>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h5 class="mb-4 fw-semibold text-center">⚙️ Pengaturan Data</h5>

<!-- NAV TABS -->
<ul class="nav nav-tabs mb-4" id="settingTabs" role="tablist">
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

<div class="tab-content" id="settingTabsContent">

    <!-- ============================================ -->
    <!-- TAB 1: DATA USER                            -->
    <!-- ============================================ -->
    <?php if ($is_admin): ?>
        <div class="tab-pane fade <?= $active_tab == 'users' ? 'show active' : '' ?>" id="users-panel" role="tabpanel">
            <div class="card border-0 shadow rounded-4 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Daftar User Sistem</h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-custom mb-0">
                            <thead style="background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white;">
                                <tr>
                                    <th style="width:40px;" class="text-center">No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Login Terakhir</th>
                                    <th style="width:150px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; ?>
                                <!-- ADMIN USERS -->
                                <?php foreach ($admin_users as $u): ?>
                                    <tr id="user-admin-<?= $u['id'] ?>">
                                        <td class="text-center fw-bold"><?= $no++ ?></td>
                                        <td>
                                            <span class="display-name"><?= htmlspecialchars($u['nama']) ?></span>
                                            <div class="edit-form d-none">
                                                <form method="post" action="<?= base_url('setting/update-user') ?>" class="d-flex gap-2 align-items-center">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="user_table" value="master_admin">
                                                    <input type="text" name="nama" class="edit-input" value="<?= htmlspecialchars($u['nama']) ?>">
                                                    <div class="edit-actions">
                                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit"><i class="bi bi-x-lg"></i></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                                        <td>
                                            <span class="badge bg-primary"><?= ucfirst($u['role']) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= !empty($u['login_at']) ? date('d/m/Y H:i', strtotime($u['login_at'])) : '-' ?></small>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary btn-start-edit" title="Edit Nama">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- KOORDINATOR USERS -->
                                <?php foreach ($koordinator_users as $u): ?>
                                    <tr id="user-koor-<?= $u['id'] ?>">
                                        <td class="text-center fw-bold"><?= $no++ ?></td>
                                        <td>
                                            <span class="display-name"><?= htmlspecialchars($u['nama']) ?></span>
                                            <div class="edit-form d-none">
                                                <form method="post" action="<?= base_url('setting/update-user') ?>" class="d-flex gap-2 align-items-center">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                                    <input type="hidden" name="user_table" value="master_koordinator_blok">
                                                    <input type="text" name="nama" class="edit-input" value="<?= htmlspecialchars($u['nama']) ?>">
                                                    <div class="edit-actions">
                                                        <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit"><i class="bi bi-x-lg"></i></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                        <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?= ucfirst($u['role']) ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= !empty($u['login_at']) ? date('d/m/Y H:i', strtotime($u['login_at'])) : '-' ?></small>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary btn-start-edit" title="Edit Nama">
                                                <i class="bi bi-pencil-square"></i> Edit
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
    <?php endif; ?>

    <!-- ============================================ -->
    <!-- TAB 2: DATA PEMILIK RUMAH                   -->
    <!-- ============================================ -->
    <div class="tab-pane fade <?= $active_tab == 'rumah' ? 'show active' : '' ?>" id="rumah-panel" role="tabpanel">
        <div class="card border-0 shadow rounded-4 mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Daftar Pemilik Rumah</h6>
                    <span class="badge bg-success fs-6"><?= count($data_rumah) ?> Rumah</span>
                </div>

                <!-- Search -->
                <form method="get" action="<?= base_url('setting') ?>" class="mb-3">
                    <input type="hidden" name="tab" value="rumah">
                    <div class="input-group" style="max-width:400px;">
                        <input type="text" name="keyword_rumah" class="form-control" placeholder="Cari nama/alamat rumah..."
                            value="<?= htmlspecialchars($keyword_rumah ?? '') ?>">
                        <button class="btn btn-success" type="submit"><i class="fa fa-search"></i></button>
                        <?php if (!empty($keyword_rumah)): ?>
                            <a href="<?= base_url('setting?tab=rumah') ?>" class="btn btn-outline-danger"><i class="fa fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-custom mb-0">
                        <thead style="background: linear-gradient(135deg, #198754, #157347); color: white;">
                            <tr>
                                <th style="width:40px;" class="text-center">No</th>
                                <th>Alamat Rumah</th>
                                <th>Nama Pemilik</th>
                                <th>Koordinator</th>
                                <th style="width:150px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            foreach ($data_rumah as $r): ?>
                                <tr id="rumah-<?= $r['id'] ?>">
                                    <td class="text-center fw-bold"><?= $no++ ?></td>
                                    <td>
                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                        <?= htmlspecialchars($r['alamat']) ?>
                                    </td>
                                    <td>
                                        <span class="display-name">
                                            <i class="bi bi-person-fill text-primary me-1"></i>
                                            <strong><?= htmlspecialchars($r['nama'] ?? '-') ?></strong>
                                        </span>
                                        <div class="edit-form d-none">
                                            <form method="post" action="<?= base_url('setting/update-rumah') ?>" class="d-flex gap-2 align-items-center">
                                                <input type="hidden" name="rumah_id" value="<?= $r['id'] ?>">
                                                <input type="text" name="nama" class="edit-input" value="<?= htmlspecialchars($r['nama'] ?? '') ?>" placeholder="Nama pemilik">
                                                <div class="edit-actions">
                                                    <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-cancel-edit"><i class="bi bi-x-lg"></i></button>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?= htmlspecialchars($r['koordinator'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-success btn-start-edit" title="Edit Nama Pemilik">
                                            <i class="bi bi-pencil-square"></i> Edit
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
</div>

<!-- INLINE EDIT SCRIPT -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Start Edit
        document.querySelectorAll('.btn-start-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                row.querySelector('.display-name').classList.add('d-none');
                row.querySelector('.edit-form').classList.remove('d-none');
                this.classList.add('d-none');
                const input = row.querySelector('.edit-input');
                input.focus();
                input.select();
            });
        });

        // Cancel Edit
        document.querySelectorAll('.btn-cancel-edit').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = this.closest('tr');
                row.querySelector('.display-name').classList.remove('d-none');
                row.querySelector('.edit-form').classList.add('d-none');
                row.querySelector('.btn-start-edit').classList.remove('d-none');
            });
        });
    });
</script>