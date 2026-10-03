<?php if (function_exists('ensure_log_tables_exist')) { ensure_log_tables_exist(); } ?>
<div class="container-fluid">
    <!-- Header Page -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-md-8 col-12">
                    <h4 class="fw-semibold mb-1"><i class="ti ti-checklist text-primary me-2"></i> Log Verifikasi Pembayaran</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Log Aktivitas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Verifikasi</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="reloadTableLog()">
                        <i class="ti ti-refresh me-1"></i> Refresh Data
                    </button>
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

        /* Segmented Button Group untuk Filter Periode */
        .filter-period-tabs {
            display: inline-flex;
            background: #f1f5f9;
            border-radius: 10px;
            padding: 3px;
            gap: 2px;
            border: 1px solid #cbd5e1;
            flex-wrap: wrap;
        }
        .period-btn {
            border: none;
            background: transparent;
            padding: 6px 14px;
            border-radius: 7px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            transition: all 0.15s ease;
            cursor: pointer;
            white-space: nowrap;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .period-btn:hover {
            color: #0f172a;
            background: rgba(255, 255, 255, 0.7);
        }
        .period-btn.active {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 1px 4px rgba(2, 132, 199, 0.25);
        }

        #tblLogVerifikasi_wrapper .dataTables_filter {
            display: none;
        }
    </style>

    <?php
    $total_v = $this->db->count_all('log_verifikasi');
    ?>

    <!-- 5 KPI Cards Row -->
    <div class="row mb-4 g-3">
        <!-- Card 1: Total Verifikasi -->
        <div class="col-xl col-md-4 col-6">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #0284c7 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#e0f2fe; color:#0284c7;">
                            <i class="ti ti-checklist"></i>
                        </div>
                        <span class="badge rounded-pill" id="kpiBadgePeriode" style="background:#e0f2fe; color:#0284c7; font-size:0.68rem; font-weight:700;">Semua Log</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Total Verifikasi">Total Verifikasi</div>
                        <div class="kpi-value text-dark" id="kpiTotal"><?= number_format($total_v, 0, ',', '.') ?></div>
                        <div class="kpi-sub"><i class="ti ti-activity text-muted"></i> Log transaksi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Disetujui (Verified) -->
        <div class="col-xl col-md-4 col-6">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #16a34a !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#16a34a;">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#16a34a; font-size:0.68rem; font-weight:700;">Disetujui</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Verified">Verified</div>
                        <div class="kpi-value text-success" id="kpiVerified">-</div>
                        <div class="kpi-sub"><i class="ti ti-check text-success"></i> Pembayaran sah</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Ditolak (Rejected) -->
        <div class="col-xl col-md-4 col-6">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #dc2626 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fee2e2; color:#dc2626;">
                            <i class="ti ti-circle-x"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fee2e2; color:#dc2626; font-size:0.68rem; font-weight:700;">Ditolak</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Rejected">Rejected</div>
                        <div class="kpi-value text-danger" id="kpiRejected">-</div>
                        <div class="kpi-sub"><i class="ti ti-x text-danger"></i> Tidak valid</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: WA Terkirim -->
        <div class="col-xl col-md-6 col-6">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #0d9488 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ccfbf1; color:#0d9488;">
                            <i class="ti ti-brand-whatsapp"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#ccfbf1; color:#0d9488; font-size:0.68rem; font-weight:700;">Terkirim</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="WA Terkirim">WA Sukses</div>
                        <div class="kpi-value text-teal" style="color:#0d9488;" id="kpiWaSuccess">-</div>
                        <div class="kpi-sub"><i class="ti ti-check-double text-teal"></i> Pesan terkirim</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 5: WA Gagal -->
        <div class="col-xl col-md-6 col-6">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #d97706 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fef3c7; color:#d97706;">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fef3c7; color:#d97706; font-size:0.68rem; font-weight:700;">Gagal</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="WA Gagal">WA Gagal</div>
                        <div class="kpi-value text-warning" id="kpiWaFailed">-</div>
                        <div class="kpi-sub"><i class="ti ti-clock text-warning"></i> Error / antrean</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card: Table with Clean Integrated Toolbar -->
    <div class="card shadow-sm rounded-4 mb-4" style="border: 1.5px solid #cbd5e1 !important;">
        <!-- Header with Filter Controls -->
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <!-- Row 1: Period Segmented Tabs & Active Info -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="filter-period-tabs" id="groupPeriodeTabs">
                    <button type="button" class="period-btn active" data-periode="semua">
                        <i class="ti ti-database"></i> Semua Log
                    </button>
                    <button type="button" class="period-btn" data-periode="hari_ini">
                        <i class="ti ti-calendar"></i> Hari Ini
                    </button>
                    <button type="button" class="period-btn" data-periode="kemarin">
                        <i class="ti ti-calendar-minus"></i> Kemarin
                    </button>
                    <button type="button" class="period-btn" data-periode="minggu_ini">
                        <i class="ti ti-calendar-event"></i> Minggu Ini
                    </button>
                    <button type="button" class="period-btn" data-periode="bulan_ini">
                        <i class="ti ti-calendar-stats"></i> Bulan Ini
                    </button>
                    <button type="button" class="period-btn" data-periode="custom" id="btnCustomTrigger">
                        <i class="ti ti-calendar-search"></i> Custom 📅
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2 fw-semibold" id="activeFilterBadge">
                        <i class="ti ti-check text-success me-1"></i> Semua Riwayat
                    </span>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold d-none" id="btnResetFilter" onclick="resetAllFilters()">
                        <i class="ti ti-rotate-clockwise me-1"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Custom Date Range Bar (Muncul saat klik Custom) -->
            <div id="boxCustomDate" class="p-3 mb-3 rounded-3" style="display:none; background:#f8fafc; border:1px dashed #cbd5e1;">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small fw-bold text-dark"><i class="ti ti-calendar-event text-primary me-1"></i>Pilih Rentang:</span>
                    <div class="d-flex align-items-center gap-2">
                        <input type="date" id="tglMulai" class="form-control form-control-sm" style="width:145px;" value="<?= date('Y-m-01') ?>">
                        <span class="text-muted small fw-semibold">s/d</span>
                        <input type="date" id="tglSelesai" class="form-control form-control-sm" style="width:145px;" value="<?= date('Y-m-d') ?>">
                    </div>
                    <button type="button" class="btn btn-sm btn-primary px-3 fw-bold shadow-sm" onclick="applyCustomDate()">
                        <i class="ti ti-check me-1"></i> Terapkan
                    </button>
                    <button type="button" class="btn btn-sm btn-light border px-3" onclick="cancelCustomDate()">
                        Batal
                    </button>
                </div>
            </div>

            <!-- Row 2: Secondary Dropdowns (Aksi, Status WA, Real-Time Search) -->
            <div class="row g-2 align-items-center pt-2 border-top">
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-check-circle"></i></span>
                        <select id="filterAksi" class="form-select form-select-sm border-start-0 fw-semibold text-dark">
                            <option value="">Semua Aksi</option>
                            <option value="verified">✅ Verified (Disetujui)</option>
                            <option value="rejected">❌ Rejected (Ditolak)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-brand-whatsapp"></i></span>
                        <select id="filterWaStatus" class="form-select form-select-sm border-start-0 fw-semibold text-dark">
                            <option value="">Semua Status WA</option>
                            <option value="success">✅ Terkirim (Sukses)</option>
                            <option value="failed">❌ Gagal Kirim</option>
                            <option value="queue">⏳ Dalam Antrean</option>
                            <option value="skipped">⚪ Dilewati / Tanpa WA</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" id="customSearchInput" class="form-control form-control-sm border-start-0" placeholder="Ketik nama admin, warga, alamat, atau no HP untuk mencari...">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()" title="Hapus Pencarian"><i class="ti ti-backspace"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Body -->
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table id="tblLogVerifikasi" class="table table-striped table-hover align-middle w-100" style="font-size:0.88rem;">
                    <thead class="table-dark">
                        <tr>
                            <th width="35" class="text-center text-white" style="border-top-left-radius: 8px;">No</th>
                            <th class="text-white text-nowrap">Waktu</th>
                            <th class="text-white">Admin Verifikator</th>
                            <th class="text-center text-white">Aksi</th>
                            <th class="text-white">Warga</th>
                            <th class="text-center text-white">Bulan Bayar</th>
                            <th class="text-end text-white">Jumlah</th>
                            <th class="text-center text-white">Via</th>
                            <th class="text-center text-white">Status WA</th>
                            <th class="text-center text-white">No HP Tujuan</th>
                            <th width="80" class="text-center text-white" style="border-top-right-radius: 8px;">Detail</th>
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

<!-- Modal Detail Log Verifikasi -->
<div class="modal fade" id="modalDetailVerifikasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="ti ti-file-text me-1 text-primary"></i> Detail Log Verifikasi Pembayaran
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
var tblLogVerifikasi = null;
var currentPeriode = 'semua';
var searchTimer = null;

$(document).ready(function() {
    // Inisialisasi DataTables Server-Side
    tblLogVerifikasi = $('#tblLogVerifikasi').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'desc']], // Urut waktu terbaru
        dom: '<"row align-items-center mb-3"<"col-md-6 col-12"l><"col-md-6 col-12 text-md-end">>rt<"row align-items-center mt-3"<"col-md-6 col-12"i><"col-md-6 col-12 text-md-end"p>>',
        ajax: {
            url: "<?= base_url('ajax-log-verifikasi') ?>",
            type: "POST",
            data: function(d) {
                d.periode     = currentPeriode;
                d.tgl_mulai   = $('#tglMulai').val();
                d.tgl_selesai = $('#tglSelesai').val();
                d.aksi        = $('#filterAksi').val();
                d.wa_status   = $('#filterWaStatus').val();
                d.search.value = $('#customSearchInput').val();
            },
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
            { data: 'jumlah', className: 'text-end fw-bold text-dark' },
            { data: 'via', className: 'text-center' },
            { data: 'wa_status', className: 'text-center' },
            { data: 'wa_no_tujuan', className: 'text-center' },
            { data: 'aksi_btn', className: 'text-center', orderable: false }
        ],
        drawCallback: function(settings) {
            var json = settings.json;
            if (json && json.kpi) {
                $('#kpiTotal').text(json.kpi.total);
                $('#kpiVerified').text(json.kpi.verified);
                $('#kpiRejected').text(json.kpi.rejected);
                $('#kpiWaSuccess').text(json.kpi.wa_success);
                $('#kpiWaFailed').text(json.kpi.wa_failed);
                $('#kpiBadgePeriode').text(json.kpi.periode_label);
                $('#activeFilterBadge').html('<i class="ti ti-check text-success me-1"></i> ' + json.kpi.periode_label);
            }

            // Tampilkan tombol reset jika bukan filter default
            if (currentPeriode !== 'semua' || $('#filterAksi').val() !== '' || $('#filterWaStatus').val() !== '' || $('#customSearchInput').val() !== '') {
                $('#btnResetFilter').removeClass('d-none');
            } else {
                $('#btnResetFilter').addClass('d-none');
            }
        },
        language: {
            lengthMenu: "Tampilkan _MENU_ baris",
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ log",
            infoEmpty: "Menampilkan 0 data",
            paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
            emptyTable: "Tidak ada data log verifikasi untuk filter yang dipilih",
            zeroRecords: "Data log verifikasi tidak ditemukan",
            processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...'
        }
    });

    // Event Handler tombol segmented tabs periode
    $('.period-btn').on('click', function() {
        var selected = $(this).data('periode');
        $('.period-btn').removeClass('active');
        $(this).addClass('active');

        if (selected === 'custom') {
            $('#boxCustomDate').slideDown(180);
        } else {
            $('#boxCustomDate').slideUp(150);
            currentPeriode = selected;
            tblLogVerifikasi.ajax.reload();
        }
    });

    // Event Handler filter dropdown Aksi & Status WA
    $('#filterAksi, #filterWaStatus').on('change', function() {
        tblLogVerifikasi.ajax.reload();
    });

    // Event Handler live search dengan debounce
    $('#customSearchInput').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            tblLogVerifikasi.ajax.reload();
        }, 300);
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

// Terapkan rentang tanggal kustom
function applyCustomDate() {
    var start = $('#tglMulai').val();
    var end = $('#tglSelesai').val();
    if (!start || !end) {
        alert('Harap pilih Tanggal Mulai dan Tanggal Selesai.');
        return;
    }
    if (start > end) {
        alert('Tanggal Mulai tidak boleh lebih besar dari Tanggal Selesai.');
        return;
    }
    currentPeriode = 'custom';
    $('.period-btn').removeClass('active');
    $('#btnCustomTrigger').addClass('active');
    tblLogVerifikasi.ajax.reload();
}

// Batal rentang kustom
function cancelCustomDate() {
    $('#boxCustomDate').slideUp(150);
    $('.period-btn').removeClass('active');
    $('[data-periode="semua"]').addClass('active');
    currentPeriode = 'semua';
    tblLogVerifikasi.ajax.reload();
}

// Reset semua filter
function resetAllFilters() {
    $('#boxCustomDate').slideUp(150);
    $('.period-btn').removeClass('active');
    $('[data-periode="semua"]').addClass('active');
    $('#filterAksi').val('');
    $('#filterWaStatus').val('');
    $('#customSearchInput').val('');
    currentPeriode = 'semua';
    tblLogVerifikasi.ajax.reload();
}

// Clear search input
function clearSearch() {
    $('#customSearchInput').val('').trigger('keyup');
}

// Reload table manual
function reloadTableLog() {
    if (tblLogVerifikasi) {
        tblLogVerifikasi.ajax.reload(null, false);
    }
}
</script>
