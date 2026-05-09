<div class="container-fluid">
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">📋 Log Login</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Login</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Login Hari Ini</h6>
                    <?php
                    $today = date('Y-m-d');
                    $this->db->where('DATE(login_at)', $today);
                    $today_count = $this->db->count_all_results('log_login');
                    ?>
                    <h3 class="fw-bold text-primary mb-0"><?= $today_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Login Berhasil</h6>
                    <?php
                    $this->db->where('DATE(login_at)', $today);
                    $this->db->where('status', 'success');
                    $success_count = $this->db->count_all_results('log_login');
                    ?>
                    <h3 class="fw-bold text-success mb-0"><?= $success_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Login Gagal</h6>
                    <?php
                    $this->db->where('DATE(login_at)', $today);
                    $this->db->where('status', 'failed');
                    $failed_count = $this->db->count_all_results('log_login');
                    ?>
                    <h3 class="fw-bold text-danger mb-0"><?= $failed_count ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-1">Total Semua Log</h6>
                    <?php $total_count = $this->db->count_all('log_login'); ?>
                    <h3 class="fw-bold text-info mb-0"><?= $total_count ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblLogLogin" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="40">No</th>
                            <th>Waktu Login</th>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Browser / Device</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $this->db->order_by('login_at', 'DESC');
                        $logs = $this->db->get('log_login')->result();
                        $no = 1;
                        foreach ($logs as $log):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="nowrap">
                                    <i class="bi bi-clock text-muted"></i>
                                    <?= date('d/m/Y H:i:s', strtotime($log->login_at)) ?>
                                </td>
                                <td><code><?= htmlspecialchars($log->username) ?></code></td>
                                <td><?= htmlspecialchars($log->nama ?? '-') ?></td>
                                <td>
                                    <?php if ($log->role == 'admin'): ?>
                                        <span class="badge bg-primary">Admin</span>
                                    <?php elseif ($log->role == 'koordinator'): ?>
                                        <span class="badge bg-warning text-dark">Koordinator</span>
                                    <?php elseif ($log->role == 'bendahara'): ?>
                                        <span class="badge bg-info">Bendahara</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($log->role ?? '-') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log->status == 'success'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Berhasil</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Gagal</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($log->ip_address ?? '-') ?></small></td>
                                <td>
                                    <small class="text-muted" title="<?= htmlspecialchars($log->user_agent ?? '') ?>">
                                        <?= htmlspecialchars(mb_strimwidth($log->user_agent ?? '-', 0, 60, '...')) ?>
                                    </small>
                                </td>
                                <td><small><?= htmlspecialchars($log->keterangan ?? '-') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tblLogLogin').DataTable({
        order: [[1, 'desc']],
        pageLength: 25,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
            emptyTable: "Tidak ada data log login",
            zeroRecords: "Data tidak ditemukan"
        },
        dom: 'Bfrtip',
        columnDefs: [
            { targets: [7], orderable: false }
        ]
    });
});
</script>
