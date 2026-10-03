<?php if (function_exists('ensure_log_tables_exist')) { ensure_log_tables_exist(); } ?>
<div class="container-fluid">
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-1">📝 Log Verifikasi Pembayaran</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Log Aktivitas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Verifikasi</li>
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
            font-size: 1.55rem;
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
    $total_v = $this->db->count_all('log_verifikasi');
    $v_count = $this->db->where('aksi', 'verified')->count_all_results('log_verifikasi');
    $r_count = $this->db->where('aksi', 'rejected')->count_all_results('log_verifikasi');
    $wa_s = $this->db->where('wa_status', 'success')->count_all_results('log_verifikasi');
    $wa_f = $this->db->where('wa_status', 'failed')->count_all_results('log_verifikasi');
    $wa_sk = $this->db->where_in('wa_status', ['skipped', 'queue', null])->count_all_results('log_verifikasi');
    ?>

    <!-- Stats Row -->
    <div class="row mb-4">
        <!-- Total Verifikasi -->
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #4338ca !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#e0e7ff; color:#4338ca;">
                            <i class="ti ti-checklist"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#eef2ff; color:#4338ca; font-size:0.68rem; font-weight:600;">Semua</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Total Verifikasi">Total Verifikasi</div>
                        <div class="kpi-value text-dark"><?= number_format($total_v, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-activity text-muted"></i> Log transaksi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verified -->
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #15803d !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#15803d;">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#15803d; font-size:0.68rem; font-weight:600;">Disetujui</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Verified">Verified</div>
                        <div class="kpi-value text-success"><?= number_format($v_count, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-check text-success"></i> Pembayaran sah</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #b91c1c !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fee2e2; color:#b91c1c;">
                            <i class="ti ti-circle-x"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fee2e2; color:#b91c1c; font-size:0.68rem; font-weight:600;">Ditolak</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Rejected">Rejected</div>
                        <div class="kpi-value text-danger"><?= number_format($r_count, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-x text-danger"></i> Tidak valid</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WA Sukses -->
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #16a34a !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#16a34a;">
                            <i class="ti ti-brand-whatsapp"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#16a34a; font-size:0.68rem; font-weight:600;">Sent</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="WA Sukses">WA Sukses</div>
                        <div class="kpi-value" style="color:#16a34a;"><?= number_format($wa_s, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-checks text-success"></i> Terkirim warga</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WA Gagal -->
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #c2410c !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ffedd5; color:#c2410c;">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#ffedd5; color:#c2410c; font-size:0.68rem; font-weight:600;">Gagal</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="WA Gagal">WA Gagal</div>
                        <div class="kpi-value text-danger"><?= number_format($wa_f, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-alert-circle text-danger"></i> Kendala nomor</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WA Lewati / Antrean -->
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #7c3aed !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ede9fe; color:#7c3aed;">
                            <i class="ti ti-clock-pause"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#ede9fe; color:#7c3aed; font-size:0.68rem; font-weight:600;">Antrean</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="WA Antrean / Lewati">WA Antrean</div>
                        <div class="kpi-value" style="color:#7c3aed;"><?= number_format($wa_sk, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-clock text-muted"></i> Lewati / pending</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblLogVerifikasi" class="table table-striped table-hover align-middle w-100" style="font-size:0.9rem">
                    <thead class="table-dark">
                        <tr>
                            <th width="35" class="text-center">No</th>
                            <th>Waktu</th>
                            <th>Admin Verifikator</th>
                            <th class="text-center">Aksi</th>
                            <th>Warga</th>
                            <th class="text-center">Bulan Bayar</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Via</th>
                            <th class="text-center">Status WA</th>
                            <th class="text-center">No HP Tujuan</th>
                            <th width="80" class="text-center">Detail</th>
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

<!-- Modal Detail Log (Tunggal & Cepat) -->
<div class="modal fade" id="modalDetailVerifikasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="bi bi-file-text me-1 text-primary"></i> Detail Log Verifikasi
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <table class="table table-bordered table-sm align-middle mb-0">
                    <tr><th width="190" class="bg-light">Waktu Verifikasi</th><td id="d_waktu">-</td></tr>
                    <tr><th class="bg-light">Admin Verifikator</th><td id="d_admin">-</td></tr>
                    <tr><th class="bg-light">Aksi yang Dilakukan</th><td id="d_aksi">-</td></tr>
                    <tr><th class="bg-light">Status Sebelum &rarr; Sesudah</th><td><span id="d_status_sebelum">-</span> &rarr; <span id="d_status_sesudah" class="fw-bold">-</span></td></tr>
                    <tr><th class="bg-light">Nama Warga</th><td id="d_warga" class="fw-bold">-</td></tr>
                    <tr><th class="bg-light">Alamat Rumah</th><td id="d_alamat">-</td></tr>
                    <tr><th class="bg-light">Bulan Kewajiban</th><td id="d_bulan">-</td></tr>
                    <tr><th class="bg-light">Jumlah Nominal Bayar</th><td id="d_jumlah" class="fw-bold text-success">-</td></tr>
                    <tr><th class="bg-light">Metode Bayar</th><td id="d_via">-</td></tr>
                    <tr><th class="bg-light">Tanggal Bayar Warga</th><td id="d_tgl_bayar">-</td></tr>
                    <tr><th class="bg-light">Status Pengiriman WA</th><td id="d_wa_status">-</td></tr>
                    <tr><th class="bg-light">Nomor HP Tujuan WA</th><td id="d_wa_no">-</td></tr>
                    <tr><th class="bg-light">Respon WA Gateway</th><td><pre id="d_wa_response" class="mb-0 bg-light p-2 rounded small" style="max-height:120px; overflow:auto; font-size:0.75rem"></pre></td></tr>
                    <tr><th class="bg-light">Catatan Error WA</th><td><span id="d_wa_error" class="text-danger fw-semibold">-</span></td></tr>
                    <tr><th class="bg-light">IP Address Client</th><td id="d_ip">-</td></tr>
                    <tr><th class="bg-light">User Agent / Device</th><td><small id="d_ua" class="text-muted">-</small></td></tr>
                </table>
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#tblLogVerifikasi').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        pageLength: 25,
        order: [[1, 'desc']], // Urut waktu terbaru
        ajax: {
            url: "<?= base_url('ajax-log-verifikasi') ?>",
            type: "POST",
            error: function(xhr, status, error) {
                console.error("Ajax Log Verifikasi Error:", error);
            }
        },
        columns: [
            { data: 'no', className: 'text-center fw-bold text-muted', orderable: false },
            { data: 'waktu' },
            { data: 'admin' },
            { data: 'aksi', className: 'text-center' },
            { data: 'warga' },
            { data: 'bulan', className: 'text-center' },
            { data: 'jumlah', className: 'text-end' },
            { data: 'via', className: 'text-center' },
            { data: 'wa_status', className: 'text-center' },
            { data: 'wa_no_tujuan', className: 'text-center' },
            { data: 'aksi_btn', className: 'text-center', orderable: false }
        ],
        language: {
            search: "Cari Log:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ log",
            paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
            emptyTable: "Tidak ada data log verifikasi",
            zeroRecords: "Data tidak ditemukan",
            processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data dari server...'
        }
    });

    // Event Klik Detail Modal
    $(document).on('click', '.btn-detail-verifikasi', function() {
        var d = $(this).data('log');
        if (typeof d === 'string') {
            try { d = JSON.parse(d); } catch(e) { console.error(e); return; }
        }

        $('#d_waktu').text(d.created_at || '-');
        $('#d_admin').text((d.admin_nama || '-') + ' (' + (d.admin_username || '-') + ') - Role: ' + (d.admin_role || '-'));
        $('#d_aksi').html(d.aksi === 'verified' ? '<span class="badge bg-success">Verified</span>' : (d.aksi === 'rejected' ? '<span class="badge bg-danger">Rejected</span>' : '<span class="badge bg-secondary">' + d.aksi + '</span>'));
        $('#d_status_sebelum').text(d.status_sebelum || '-');
        $('#d_status_sesudah').text(d.status_sesudah || '-');
        $('#d_warga').text(d.warga_nama || '-');
        $('#d_alamat').text(d.warga_alamat || '-');
        $('#d_bulan').text(d.bulan_bayar || '-');
        $('#d_jumlah').text(d.jumlah_bayar_rp || 'Rp0');
        $('#d_via').text(d.pembayaran_via || '-');
        $('#d_tgl_bayar').text(d.tanggal_bayar || '-');
        $('#d_wa_status').html(d.wa_status === 'success' ? '<span class="badge bg-success">Sukses</span>' : (d.wa_status === 'failed' ? '<span class="badge bg-danger">Gagal</span>' : '<span class="badge bg-warning text-dark">' + (d.wa_status || '-') + '</span>'));
        $('#d_wa_no').text(d.wa_no_tujuan || '-');
        $('#d_wa_response').text(d.wa_response || '-');
        $('#d_wa_error').text(d.wa_error || '-');
        $('#d_ip').text(d.ip_address || '-');
        $('#d_ua').text(d.user_agent || '-');

        $('#modalDetailVerifikasi').modal('show');
    });
});
</script>
