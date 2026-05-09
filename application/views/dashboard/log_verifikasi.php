<div class="container-fluid">
    <div class="card bg-light-info shadow-none position-relative overflow-hidden">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">📝 Log Verifikasi Pembayaran</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Verifikasi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1" style="font-size:0.75rem">Total Verifikasi</h6>
                    <?php $total_v = $this->db->count_all('log_verifikasi'); ?>
                    <h4 class="fw-bold text-primary mb-0"><?= $total_v ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1" style="font-size:0.75rem">Verified</h6>
                    <?php
                    $this->db->where('aksi', 'verified');
                    $v_count = $this->db->count_all_results('log_verifikasi');
                    ?>
                    <h4 class="fw-bold text-success mb-0"><?= $v_count ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1" style="font-size:0.75rem">Rejected</h6>
                    <?php
                    $this->db->where('aksi', 'rejected');
                    $r_count = $this->db->count_all_results('log_verifikasi');
                    ?>
                    <h4 class="fw-bold text-danger mb-0"><?= $r_count ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1" style="font-size:0.75rem">WA Sukses</h6>
                    <?php
                    $this->db->where('wa_status', 'success');
                    $wa_s = $this->db->count_all_results('log_verifikasi');
                    ?>
                    <h4 class="fw-bold text-success mb-0"><?= $wa_s ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1" style="font-size:0.75rem">WA Gagal</h6>
                    <?php
                    $this->db->where('wa_status', 'failed');
                    $wa_f = $this->db->count_all_results('log_verifikasi');
                    ?>
                    <h4 class="fw-bold text-danger mb-0"><?= $wa_f ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="text-muted mb-1" style="font-size:0.75rem">WA Skip</h6>
                    <?php
                    $this->db->where('wa_status', 'skipped');
                    $wa_sk = $this->db->count_all_results('log_verifikasi');
                    ?>
                    <h4 class="fw-bold text-warning mb-0"><?= $wa_sk ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblLogVerifikasi" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="35">No</th>
                            <th>Waktu</th>
                            <th>Admin</th>
                            <th>Aksi</th>
                            <th>Warga</th>
                            <th>Bulan</th>
                            <th>Jumlah</th>
                            <th>Via</th>
                            <th>Status WA</th>
                            <th>No HP Tujuan</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $this->db->order_by('created_at', 'DESC');
                        $logs = $this->db->get('log_verifikasi')->result();
                        $no = 1;
                        foreach ($logs as $log):
                        ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td class="nowrap">
                                    <small>
                                        <i class="bi bi-clock text-muted"></i>
                                        <?= date('d/m/Y H:i:s', strtotime($log->created_at)) ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($log->admin_nama ?? '-') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($log->admin_username ?? '') ?></small>
                                </td>
                                <td>
                                    <?php if ($log->aksi == 'verified'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>
                                    <?php elseif ($log->aksi == 'rejected'): ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($log->aksi) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($log->warga_nama ?? '-') ?></strong>
                                    <br><small class="text-muted"><?= htmlspecialchars($log->warga_alamat ?? '') ?></small>
                                </td>
                                <td class="nowrap">
                                    <?= $log->bulan_bayar ? date('M Y', strtotime($log->bulan_bayar)) : '-' ?>
                                </td>
                                <td class="nowrap text-end">
                                    <strong>Rp<?= number_format($log->jumlah_bayar ?? 0, 0, ',', '.') ?></strong>
                                </td>
                                <td>
                                    <?php if ($log->pembayaran_via == 'koordinator'): ?>
                                        <span class="badge bg-warning text-dark">Koordinator</span>
                                    <?php elseif ($log->pembayaran_via == 'transfer'): ?>
                                        <span class="badge bg-info">Transfer</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($log->pembayaran_via ?? '-') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log->wa_status == 'success'): ?>
                                        <span class="badge bg-success"><i class="bi bi-whatsapp"></i> Sukses</span>
                                    <?php elseif ($log->wa_status == 'failed'): ?>
                                        <span class="badge bg-danger" title="<?= htmlspecialchars($log->wa_error ?? '') ?>">
                                            <i class="bi bi-whatsapp"></i> Gagal
                                        </span>
                                    <?php elseif ($log->wa_status == 'skipped'): ?>
                                        <span class="badge bg-warning text-dark" title="<?= htmlspecialchars($log->wa_error ?? '') ?>">
                                            <i class="bi bi-whatsapp"></i> Skip
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><small class="text-muted"><?= htmlspecialchars($log->wa_no_tujuan ?? '-') ?></small></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                        data-bs-target="#detailModal<?= $log->id ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Modal Detail -->
                                    <div class="modal fade" id="detailModal<?= $log->id ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white">
                                                    <h5 class="modal-title">Detail Log Verifikasi #<?= $log->id ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <table class="table table-bordered table-sm">
                                                        <tr><th width="180">Waktu</th><td><?= $log->created_at ?></td></tr>
                                                        <tr><th>ID Pembayaran</th><td><?= $log->pembayaran_id ?></td></tr>
                                                        <tr><th>Admin</th><td><?= htmlspecialchars($log->admin_nama) ?> (<?= htmlspecialchars($log->admin_username) ?>) - Role: <?= htmlspecialchars($log->admin_role) ?></td></tr>
                                                        <tr><th>Aksi</th><td><span class="badge bg-<?= $log->aksi == 'verified' ? 'success' : 'danger' ?>"><?= $log->aksi ?></span></td></tr>
                                                        <tr><th>Status Sebelum</th><td><?= htmlspecialchars($log->status_sebelum ?? '-') ?></td></tr>
                                                        <tr><th>Status Sesudah</th><td><?= htmlspecialchars($log->status_sesudah ?? '-') ?></td></tr>
                                                        <tr><th>Warga</th><td><?= htmlspecialchars($log->warga_nama) ?></td></tr>
                                                        <tr><th>Alamat</th><td><?= htmlspecialchars($log->warga_alamat ?? '-') ?></td></tr>
                                                        <tr><th>Bulan Bayar</th><td><?= $log->bulan_bayar ?></td></tr>
                                                        <tr><th>Jumlah Bayar</th><td>Rp<?= number_format($log->jumlah_bayar ?? 0, 0, ',', '.') ?></td></tr>
                                                        <tr><th>Via Pembayaran</th><td><?= htmlspecialchars($log->pembayaran_via ?? '-') ?></td></tr>
                                                        <tr><th>Tanggal Bayar</th><td><?= $log->tanggal_bayar ?? '-' ?></td></tr>
                                                        <tr>
                                                            <th>Status Kirim WA</th>
                                                            <td>
                                                                <?php if ($log->wa_status == 'success'): ?>
                                                                    <span class="badge bg-success">Sukses</span>
                                                                <?php elseif ($log->wa_status == 'failed'): ?>
                                                                    <span class="badge bg-danger">Gagal</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($log->wa_status ?? '-') ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                        <tr><th>No HP Tujuan WA</th><td><?= htmlspecialchars($log->wa_no_tujuan ?? '-') ?></td></tr>
                                                        <tr><th>WA Response</th><td><pre class="mb-0" style="max-height:150px; overflow:auto; font-size:0.75rem"><?= htmlspecialchars($log->wa_response ?? '-') ?></pre></td></tr>
                                                        <tr><th>WA Error</th><td><span class="text-danger"><?= htmlspecialchars($log->wa_error ?? '-') ?></span></td></tr>
                                                        <tr><th>IP Address</th><td><?= htmlspecialchars($log->ip_address ?? '-') ?></td></tr>
                                                        <tr><th>User Agent</th><td><small><?= htmlspecialchars($log->user_agent ?? '-') ?></small></td></tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
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

<script>
$(document).ready(function() {
    $('#tblLogVerifikasi').DataTable({
        order: [[1, 'desc']],
        pageLength: 25,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
            paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
            emptyTable: "Tidak ada data log verifikasi",
            zeroRecords: "Data tidak ditemukan"
        },
        columnDefs: [
            { targets: [10], orderable: false }
        ]
    });
});
</script>
