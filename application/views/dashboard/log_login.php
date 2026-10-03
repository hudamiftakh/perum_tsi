<?php if (function_exists('ensure_log_tables_exist')) { ensure_log_tables_exist(); } ?>
<div class="container-fluid">
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-1">📋 Log Login</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Log Aktivitas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Login</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <style>
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
    </style>

    <?php
    $today = date('Y-m-d');
    $this->db->where('DATE(login_at)', $today);
    $today_count = $this->db->count_all_results('log_login');

    $this->db->where('DATE(login_at)', $today);
    $this->db->where('status', 'success');
    $success_count = $this->db->count_all_results('log_login');

    $this->db->where('DATE(login_at)', $today);
    $this->db->where('status', 'failed');
    $failed_count = $this->db->count_all_results('log_login');

    $total_count = $this->db->count_all('log_login');
    ?>

    <!-- Filter & Stats Row -->
    <div class="row mb-4">
        <!-- Total Login Hari Ini -->
        <div class="col-xl-3 col-md-6 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #0284c7 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#e0f2fe; color:#0284c7;">
                            <i class="ti ti-calendar-event"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#e0f2fe; color:#0284c7; font-size:0.68rem; font-weight:600;">Hari Ini</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Total Login Hari Ini">Total Login Hari Ini</div>
                        <div class="kpi-value text-dark"><?= number_format($today_count, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-clock-hour-4 text-muted"></i> Aktivitas hari ini</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Berhasil Hari Ini -->
        <div class="col-xl-3 col-md-6 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #15803d !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#15803d;">
                            <i class="ti ti-shield-check"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#15803d; font-size:0.68rem; font-weight:600;">Sukses</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Login Berhasil Hari Ini">Login Berhasil</div>
                        <div class="kpi-value text-success"><?= number_format($success_count, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-check text-success"></i> Autentikasi valid</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Login Gagal Hari Ini -->
        <div class="col-xl-3 col-md-6 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #b91c1c !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fee2e2; color:#b91c1c;">
                            <i class="ti ti-shield-x"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fee2e2; color:#b91c1c; font-size:0.68rem; font-weight:600;">Gagal</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Login Gagal Hari Ini">Login Gagal</div>
                        <div class="kpi-value text-danger"><?= number_format($failed_count, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-alert-circle text-danger"></i> Sandi/user keliru</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Semua Log -->
        <div class="col-xl-3 col-md-6 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #7c3aed !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ede9fe; color:#7c3aed;">
                            <i class="ti ti-history"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#ede9fe; color:#7c3aed; font-size:0.68rem; font-weight:600;">Akumulasi</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Total Semua Log">Total Semua Log</div>
                        <div class="kpi-value" style="color:#7c3aed;"><?= number_format($total_count, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-database text-muted"></i> Seluruh riwayat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblLogLogin" class="table table-striped table-hover align-middle w-100" style="font-size:0.9rem">
                    <thead class="table-dark">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th>Waktu Login</th>
                            <th>Username</th>
                            <th>Nama User</th>
                            <th class="text-center">Role</th>
                            <th class="text-center">Status</th>
                            <th>IP Address</th>
                            <th>Browser / Device</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Diisi via DataTables Server-side AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tblLogLogin').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        pageLength: 25,
        order: [[1, 'desc']], // Urut berdasarkan login_at terbaru
        ajax: {
            url: "<?= base_url('ajax-log-login') ?>",
            type: "POST",
            error: function(xhr, status, error) {
                console.error("Ajax Log Login Error:", error);
            }
        },
        columns: [
            { data: 'no', className: 'text-center fw-bold text-muted', orderable: false },
            { data: 'waktu' },
            { data: 'username' },
            { data: 'nama' },
            { data: 'role', className: 'text-center' },
            { data: 'status', className: 'text-center' },
            { data: 'ip_address' },
            { data: 'user_agent', orderable: false },
            { data: 'keterangan' }
        ],
        language: {
            search: "Cari Log:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ log",
            paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
            emptyTable: "Tidak ada data log login",
            zeroRecords: "Data tidak ditemukan",
            processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data dari server...'
        }
    });
});
</script>
