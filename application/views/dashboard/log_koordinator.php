<?php
if (function_exists('ensure_log_tables_exist')) {
    ensure_log_tables_exist();
}
$bulan_filter = $bulan_filter ?? date('m');
$tahun_filter = $tahun_filter ?? date('Y');
$nama_bulan = [
    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
];
?>

<div class="container-fluid">
    <!-- Header Page -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-md-7 col-12">
                    <h4 class="fw-semibold mb-1"><i class="ti ti-report-analytics text-primary me-2"></i> Log Monitoring Entri Koordinator</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Log Aktivitas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Koordinator</li>
                        </ol>
                    </nav>
                </div>
                <!-- Filter Bulan & Tahun -->
                <div class="col-md-5 col-12 mt-3 mt-md-0">
                    <form id="formFilterKoor" class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                        <div>
                            <select id="filterBulan" name="bulan" class="form-select form-select-sm shadow-sm" style="min-width:130px">
                                <?php foreach ($nama_bulan as $k => $v): ?>
                                    <option value="<?= $k ?>" <?= ($bulan_filter == $k) ? 'selected' : '' ?>><?= $v ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <select id="filterTahun" name="tahun" class="form-select form-select-sm shadow-sm" style="min-width:90px">
                                <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
                                    <option value="<?= $y ?>" <?= ($tahun_filter == $y) ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm px-3">
                            <i class="ti ti-filter"></i> <span>Terapkan</span>
                        </button>
                    </form>
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

    <!-- 4 KPI Summary Cards -->
    <div class="row mb-4">
        <!-- Card 1: Total Koordinator -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #0284c7 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#e0f2fe; color:#0284c7;">
                            <i class="ti ti-users"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#e0f2fe; color:#0284c7; font-size:0.68rem; font-weight:600;">Wilayah</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Total Koordinator">Total Koordinator</div>
                        <div class="kpi-value text-dark" id="kpiTotalKoor">-</div>
                        <div class="kpi-sub"><i class="ti ti-building-community text-muted"></i> Blok Perumahan</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Koordinator Rajin -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #15803d !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#15803d;">
                            <i class="ti ti-trophy"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#15803d; font-size:0.68rem; font-weight:600;">Disiplin</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Koordinator Rajin">Koordinator Rajin</div>
                        <div class="kpi-value text-success" id="kpiTotalRajin">-</div>
                        <div class="kpi-sub"><i class="ti ti-circle-check text-success"></i> &ge; 75% entri tuntas</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Belum Entri -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #b91c1c !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fee2e2; color:#b91c1c;">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fee2e2; color:#b91c1c; font-size:0.68rem; font-weight:600;">Pending</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Belum Entri">Belum Entri</div>
                        <div class="kpi-value text-danger" id="kpiBelumEntry">-</div>
                        <div class="kpi-sub"><i class="ti ti-clock text-danger"></i> 0% entri di periode ini</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Progress Entri Perumahan -->
        <div class="col-6 col-lg-3 mb-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #7c3aed !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ede9fe; color:#7c3aed;">
                            <i class="ti ti-chart-pie"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#ede9fe; color:#7c3aed; font-size:0.68rem; font-weight:600;">Capaian</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Terkumpul Perumahan">Terkumpul Perumahan</div>
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="kpi-value" style="color:#7c3aed;" id="kpiPersenPerumahan">0%</div>
                            <small class="text-muted" id="kpiRumahRatio" style="font-size:0.75rem;">(0/0 rumah)</small>
                        </div>
                        <div class="progress mt-2" style="height:6px; border-radius:10px; background:#f1f5f9;">
                            <div id="kpiProgressBar" class="progress-bar rounded-pill" style="width:0%; background:#7c3aed;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Highlight Row -->
    <div class="row mb-4">
        <!-- Top 3 Koordinator Terajin -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0 text-success"><i class="ti ti-trophy me-1"></i> Top 3 Koordinator Terajin Entri</h6>
                        <small class="text-muted">Kelengkapan entri tertinggi</small>
                    </div>
                    <div id="containerTop3">
                        <div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Koordinator Perlu Diingatkan / Menunggak -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm border-0 h-100 rounded-4">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0 text-danger"><i class="ti ti-alert-circle me-1"></i> Koordinator Perlu Diingatkan (Follow-Up)</h6>
                        <small class="text-muted">Banyak rumah binaan belum entri</small>
                    </div>
                    <div id="containerBottom3">
                        <div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="ti ti-list-check me-1 text-primary"></i> Tabel Rekap Kerajinan Koordinator</h5>
                    <small class="text-muted">Klik tombol <strong>"Detail Rekap"</strong> di setiap koordinator untuk melihat daftar rumah yang sudah lunas vs yang masih nunggak.</small>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tblLogKoordinator" class="table table-hover align-middle w-100" style="font-size:0.92rem">
                    <thead class="table-dark">
                        <tr>
                            <th width="35" class="text-center">No</th>
                            <th>Koordinator Blok</th>
                            <th class="text-center" width="80">Binaan</th>
                            <th class="text-center" width="95">Sudah Entri</th>
                            <th class="text-center" width="95">Nunggak</th>
                            <th width="140">Capaian (%)</th>
                            <th class="text-end" width="120">Nominal Masuk</th>
                            <th>Terakhir Entri</th>
                            <th class="text-center" width="120">Status Kerajinan</th>
                            <th class="text-center" width="110">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Diisi via DataTables AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Rekap Koordinator (Tunggal & Bersih di Luar Tabel) -->
<div class="modal fade" id="modalDetailKoor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4" style="background:#f8fafc">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-0" id="modalKoorTitle">
                        <i class="ti ti-user-check text-primary me-1"></i> Rincian Entri Koordinator
                    </h5>
                    <small class="text-muted" id="modalKoorSubtitle">Periode: -</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalKoorBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="text-muted fw-semibold">Memuat rincian data rumah...</div>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-4 d-flex justify-content-between" id="modalKoorFooter">
                <div id="modalKoorWaBtn"></div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    var dtKoordinator;

    function initTable() {
        dtKoordinator = $('#tblLogKoordinator').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            pageLength: 25,
            order: [[5, 'desc']], // Urut berdasarkan Capaian % tertinggi
            ajax: {
                url: "<?= base_url('ajax-log-koordinator') ?>",
                type: "GET",
                data: function(d) {
                    d.bulan = $('#filterBulan').val();
                    d.tahun = $('#filterTahun').val();
                },
                dataSrc: function(json) {
                    if (json && json.kpi) {
                        updateKPI(json.kpi);
                        updateLeaderboard(json.kpi);
                    }
                    return json.data || [];
                },
                error: function(xhr, status, error) {
                    console.error("Ajax Log Koordinator Error:", error);
                }
            },
            columns: [
                {
                    data: 'no',
                    className: 'text-center fw-bold text-muted'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        var html = '<strong class="text-dark">' + escapeHtml(row.nama) + '</strong>';
                        if (row.no_hp) {
                            var waUrl = 'https://wa.me/' + row.no_hp.replace(/^0/, '62').replace(/[^0-9]/g, '');
                            html += '<br><a href="' + waUrl + '" target="_blank" class="small text-success text-decoration-none"><i class="bi bi-whatsapp"></i> ' + escapeHtml(row.no_hp) + '</a>';
                        }
                        return html;
                    }
                },
                {
                    data: 'jml_rumah',
                    className: 'text-center',
                    render: function(data) {
                        return '<span class="badge bg-light text-dark border px-2 py-1">' + data + ' Rumah</span>';
                    }
                },
                {
                    data: 'jml_lunas',
                    className: 'text-center',
                    render: function(data) {
                        return '<span class="badge bg-success-subtle text-success fw-bold px-2 py-1 fs-6"><i class="ti ti-check me-1"></i>' + data + '</span>';
                    }
                },
                {
                    data: 'jml_nunggak',
                    className: 'text-center',
                    render: function(data) {
                        if (data > 0) {
                            return '<span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1 fs-6"><i class="ti ti-x me-1"></i>' + data + '</span>';
                        }
                        return '<span class="badge bg-light text-success fw-bold px-2 py-1">0 (Tuntas)</span>';
                    }
                },
                {
                    data: 'persen_lunas',
                    render: function(data) {
                        var barColor = data >= 75 ? 'bg-success' : (data >= 50 ? 'bg-warning' : 'bg-danger');
                        return '<div class="d-flex align-items-center gap-2">' +
                            '<div class="progress flex-grow-1" style="height:8px">' +
                                '<div class="progress-bar ' + barColor + '" style="width:' + data + '%"></div>' +
                            '</div>' +
                            '<span class="fw-bold small text-nowrap">' + data + '%</span>' +
                        '</div>';
                    }
                },
                {
                    data: 'total_nominal_rp',
                    className: 'text-end text-nowrap fw-bold text-dark'
                },
                {
                    data: 'last_entry_formatted',
                    className: 'small text-nowrap',
                    render: function(data) {
                        if (data && data !== '-') {
                            return '<span class="text-dark"><i class="bi bi-clock text-muted me-1"></i>' + data + '</span>';
                        }
                        return '<span class="text-danger"><i class="bi bi-dash-circle me-1"></i>Belum ada</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center text-nowrap',
                    render: function(data, type, row) {
                        return '<span class="badge ' + row.badge_class + ' px-2 py-1" style="font-size:0.75rem">' + escapeHtml(row.label_rajin) + '</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center text-nowrap',
                    orderable: false,
                    render: function(data, type, row) {
                        return '<button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm btn-detail-koor" data-id="' + row.id + '">' +
                            '<i class="ti ti-eye me-1"></i> Detail Rekap' +
                        '</button>';
                    }
                }
            ],
            language: {
                search: "Cari Koordinator:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ koordinator",
                paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
                emptyTable: "Tidak ada data koordinator",
                zeroRecords: "Koordinator tidak ditemukan",
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...'
            }
        });
    }

    // Helper escape html
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Update 4 KPI Cards
    function updateKPI(kpi) {
        $('#kpiTotalKoor').text(kpi.total_koordinator || 0);
        $('#kpiTotalRajin').text(kpi.total_rajin || 0);
        $('#kpiBelumEntry').text(kpi.total_belum_entry || 0);
        $('#kpiPersenPerumahan').text((kpi.persen_total_perumahan || 0) + '%');
        $('#kpiRumahRatio').text('(' + (kpi.total_lunas_all || 0) + '/' + (kpi.total_rumah_all || 0) + ' rumah)');
        $('#kpiProgressBar').css('width', (kpi.persen_total_perumahan || 0) + '%');
    }

    // Update Leaderboard Cards
    function updateLeaderboard(kpi) {
        var medals = ['🥇', '🥈', '🥉'];

        // 1. Top 3
        var htmlTop = '';
        if (!kpi.top3 || kpi.top3.length === 0 || kpi.top3[0].jml_lunas === 0) {
            htmlTop = '<div class="text-center py-4 text-muted"><i class="ti ti-clipboard-x fs-8 d-block mb-1"></i>Belum ada entri tercatat di periode ini.</div>';
        } else {
            $.each(kpi.top3, function(i, tk) {
                if (tk.jml_lunas === 0) return true;
                var medal = medals[i] || '⭐';
                htmlTop += '<div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2" style="background:#f0fdf4; border: 1px solid #bbf7d0;">' +
                    '<span style="font-size:1.5rem;width:36px;text-align:center">' + medal + '</span>' +
                    '<div class="flex-grow-1">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<strong>' + escapeHtml(tk.nama) + '</strong>' +
                            '<span class="badge bg-success">' + tk.persen_lunas + '% Tuntas</span>' +
                        '</div>' +
                        '<div class="d-flex align-items-center gap-2 mt-1">' +
                            '<div class="progress flex-grow-1" style="height:7px">' +
                                '<div class="progress-bar bg-success" style="width:' + tk.persen_lunas + '%"></div>' +
                            '</div>' +
                            '<small class="text-muted text-nowrap">' + tk.jml_lunas + ' dari ' + tk.jml_rumah + ' rumah</small>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
        }
        $('#containerTop3').html(htmlTop);

        // 2. Bottom 3
        var htmlBottom = '';
        if (!kpi.bottom3 || kpi.bottom3.length === 0) {
            htmlBottom = '<div class="text-center py-4 text-muted">Semua koordinator sudah tuntas entri.</div>';
        } else {
            $.each(kpi.bottom3, function(i, bk) {
                var isZero = (bk.jml_lunas === 0);
                var icon = isZero ? '🔴' : '🟡';
                var bg = isZero ? '#fff1f2' : '#fefce8';
                var border = isZero ? '#fecdd3' : '#fef08a';
                var badgeClass = isZero ? 'bg-danger' : 'bg-warning text-dark';
                var barClass = isZero ? 'bg-danger' : 'bg-warning';

                htmlBottom += '<div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2" style="background:' + bg + '; border: 1px solid ' + border + ';">' +
                    '<span style="font-size:1.3rem;width:36px;text-align:center">' + icon + '</span>' +
                    '<div class="flex-grow-1">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                            '<strong>' + escapeHtml(bk.nama) + '</strong>' +
                            '<span class="badge ' + badgeClass + '">' + bk.jml_nunggak + ' Rumah Belum Entri (' + bk.persen_lunas + '%)</span>' +
                        '</div>' +
                        '<div class="d-flex align-items-center gap-2 mt-1">' +
                            '<div class="progress flex-grow-1" style="height:7px">' +
                                '<div class="progress-bar ' + barClass + '" style="width:' + Math.max(bk.persen_lunas, 4) + '%"></div>' +
                            '</div>' +
                            '<small class="text-muted text-nowrap">' + bk.jml_lunas + '/' + bk.jml_rumah + ' rumah</small>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            });
        }
        $('#containerBottom3').html(htmlBottom);
    }

    // Submit Filter
    $('#formFilterKoor').on('submit', function(e){
        e.preventDefault();
        if (dtKoordinator) {
            dtKoordinator.ajax.reload();
        }
    });

    // Inisialisasi awal
    initTable();

    // Event Klik "Detail Rekap"
    $(document).on('click', '.btn-detail-koor', function(){
        var koorId = $(this).data('id');
        var bulan = $('#filterBulan').val();
        var tahun = $('#filterTahun').val();

        $('#modalKoorBody').html('<div class="text-center py-5"><div class="spinner-border text-primary mb-2" role="status"></div><div class="text-muted fw-semibold">Memuat rincian data rumah...</div></div>');
        $('#modalKoorWaBtn').html('');
        $('#modalDetailKoor').modal('show');

        $.ajax({
            url: "<?= base_url('ajax-detail-log-koordinator') ?>",
            type: "GET",
            data: { id: koorId, bulan: bulan, tahun: tahun },
            dataType: "json",
            success: function(res) {
                if (res.status !== 'success') {
                    $('#modalKoorBody').html('<div class="alert alert-danger mb-0">' + escapeHtml(res.message || 'Gagal mengambil data.') + '</div>');
                    return;
                }

                var k = res.koordinator;
                $('#modalKoorTitle').html('<i class="ti ti-user-check text-primary me-1"></i> Rincian Entri: ' + escapeHtml(k.nama));
                $('#modalKoorSubtitle').html('Periode: <strong>' + escapeHtml(k.bulan_label) + ' ' + k.tahun + '</strong> &bull; Total ' + k.jml_rumah + ' Rumah Binaan');

                // WhatsApp Button
                if (k.wa_link) {
                    $('#modalKoorWaBtn').html('<a href="' + k.wa_link + '" target="_blank" class="btn btn-sm btn-success"><i class="bi bi-whatsapp me-1"></i> Ingatkan Koordinator via WA</a>');
                } else {
                    $('#modalKoorWaBtn').html('');
                }

                // Render Modal Body
                var bodyHtml = '';

                // Ringkasan Mini
                bodyHtml += '<div class="row g-2 mb-3">' +
                    '<div class="col-4">' +
                        '<div class="p-2 rounded-3 text-center" style="background:#ecfdf5; border:1px solid #a7f3d0">' +
                            '<small class="text-muted d-block" style="font-size:0.72rem">SUDAH ENTRI / LUNAS</small>' +
                            '<span class="fw-bold text-success fs-5">' + k.jml_lunas + ' Rumah</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-4">' +
                        '<div class="p-2 rounded-3 text-center" style="background:#fff1f2; border:1px solid #fecdd3">' +
                            '<small class="text-muted d-block" style="font-size:0.72rem">MASIH NUNGGAK</small>' +
                            '<span class="fw-bold text-danger fs-5">' + k.jml_nunggak + ' Rumah</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-4">' +
                        '<div class="p-2 rounded-3 text-center" style="background:#f8fafc; border:1px solid #e2e8f0">' +
                            '<small class="text-muted d-block" style="font-size:0.72rem">PERSENTASE</small>' +
                            '<span class="fw-bold text-primary fs-5">' + k.persen_lunas + '%</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';

                // Tabs
                bodyHtml += '<ul class="nav nav-pills mb-3 gap-2" role="tablist">' +
                    '<li class="nav-item">' +
                        '<button class="nav-link active rounded-pill px-3 py-1 fw-semibold small" data-bs-toggle="pill" data-bs-target="#tabDetailNunggak" type="button">' +
                            '<i class="ti ti-x text-danger me-1"></i> Masih Nunggak (' + k.jml_nunggak + ')' +
                        '</button>' +
                    '</li>' +
                    '<li class="nav-item">' +
                        '<button class="nav-link rounded-pill px-3 py-1 fw-semibold small" data-bs-toggle="pill" data-bs-target="#tabDetailLunas" type="button">' +
                            '<i class="ti ti-check text-success me-1"></i> Sudah Entri (' + k.jml_lunas + ')' +
                        '</button>' +
                    '</li>' +
                '</ul>';

                bodyHtml += '<div class="tab-content">';

                // TAB 1: NUNGGAK
                bodyHtml += '<div class="tab-pane fade show active" id="tabDetailNunggak">';
                if (!res.list_nunggak || res.list_nunggak.length === 0) {
                    bodyHtml += '<div class="alert alert-success d-flex align-items-center mb-0">' +
                        '<i class="ti ti-circle-check fs-5 me-2"></i>' +
                        '<div>Luar biasa! Semua warga di binaan koordinator ini telah lunas/dientri untuk bulan ' + escapeHtml(k.bulan_label) + ' ' + k.tahun + '.</div>' +
                    '</div>';
                } else {
                    bodyHtml += '<div class="table-responsive"><table class="table table-sm table-bordered table-striped align-middle mb-0">' +
                        '<thead class="table-danger text-dark">' +
                            '<tr>' +
                                '<th width="30" class="text-center">No</th>' +
                                '<th>Nomor Rumah / Alamat</th>' +
                                '<th>Nama Warga</th>' +
                                '<th>Kontak WA</th>' +
                                '<th class="text-center">Status</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';
                    $.each(res.list_nunggak, function(idx, ngk){
                        var waCell = '<span class="text-muted small">-</span>';
                        if (ngk.no_hp) {
                            var linkWa = 'https://wa.me/' + ngk.no_hp.replace(/^0/, '62').replace(/[^0-9]/g, '');
                            waCell = '<a href="' + linkWa + '" target="_blank" class="badge bg-success-subtle text-success text-decoration-none"><i class="bi bi-whatsapp"></i> ' + escapeHtml(ngk.no_hp) + '</a>';
                        }
                        bodyHtml += '<tr>' +
                            '<td class="text-center small">' + (idx + 1) + '</td>' +
                            '<td><strong>' + escapeHtml(ngk.alamat) + '</strong></td>' +
                            '<td>' + escapeHtml(ngk.nama || '-') + '</td>' +
                            '<td>' + waCell + '</td>' +
                            '<td class="text-center"><span class="badge bg-danger">Belum Bayar</span></td>' +
                        '</tr>';
                    });
                    bodyHtml += '</tbody></table></div>';
                }
                bodyHtml += '</div>';

                // TAB 2: SUDAH ENTRI
                bodyHtml += '<div class="tab-pane fade" id="tabDetailLunas">';
                if (!res.list_lunas || res.list_lunas.length === 0) {
                    bodyHtml += '<div class="alert alert-warning mb-0"><i class="ti ti-alert-triangle me-1"></i> Belum ada data entri pembayaran untuk bulan ' + escapeHtml(k.bulan_label) + ' ' + k.tahun + '.</div>';
                } else {
                    bodyHtml += '<div class="table-responsive"><table class="table table-sm table-bordered table-striped align-middle mb-0">' +
                        '<thead class="table-success text-dark">' +
                            '<tr>' +
                                '<th width="30" class="text-center">No</th>' +
                                '<th>Nomor Rumah</th>' +
                                '<th>Nama Warga</th>' +
                                '<th class="text-end">Jumlah</th>' +
                                '<th>Tgl Bayar</th>' +
                                '<th class="text-center">Via</th>' +
                                '<th class="text-center">Status</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>';
                    $.each(res.list_lunas, function(idx, lns){
                        var viaBadge = (lns.pembayaran_via === 'koordinator') ? 'bg-info' : 'bg-primary';
                        var statusBadge = (lns.status === 'verified') ? 'bg-success' : 'bg-warning text-dark';
                        var nominalFmt = 'Rp' + (Number(lns.jumlah_bayar) || 0).toLocaleString('id-ID');
                        bodyHtml += '<tr>' +
                            '<td class="text-center small">' + (idx + 1) + '</td>' +
                            '<td><strong>' + escapeHtml(lns.alamat) + '</strong></td>' +
                            '<td>' + escapeHtml(lns.nama || '-') + '</td>' +
                            '<td class="text-end text-nowrap fw-semibold">' + nominalFmt + '</td>' +
                            '<td class="small">' + (lns.tanggal_bayar || '-') + '</td>' +
                            '<td class="text-center"><span class="badge ' + viaBadge + '">' + escapeHtml(lns.pembayaran_via || '-') + '</span></td>' +
                            '<td class="text-center"><span class="badge ' + statusBadge + '">' + escapeHtml(lns.status || '-') + '</span></td>' +
                        '</tr>';
                    });
                    bodyHtml += '</tbody></table></div>';
                }
                bodyHtml += '</div>';

                bodyHtml += '</div>'; // Tutup tab-content

                $('#modalKoorBody').html(bodyHtml);
            },
            error: function(xhr, status, error) {
                $('#modalKoorBody').html('<div class="alert alert-danger mb-0">Terjadi kesalahan koneksi server: ' + escapeHtml(error) + '</div>');
            }
        });
    });
});
</script>
