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

$cur_m = (int)date('m');
$cur_y = (int)date('Y');

$prev_m1 = date('m', strtotime('-1 month'));
$prev_y1 = date('Y', strtotime('-1 month'));

$prev_m2 = date('m', strtotime('-2 months'));
$prev_y2 = date('Y', strtotime('-2 months'));
?>

<div class="container-fluid">
    <!-- Header Page -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-md-8 col-12">
                    <h4 class="fw-semibold mb-1"><i class="ti ti-report-analytics text-primary me-2"></i> Log Monitoring Entri Koordinator</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="javascript:void(0)">Log Aktivitas</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Log Koordinator</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-md-4 col-12 text-md-end mt-2 mt-md-0">
                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="reloadTableKoor()">
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

        #tblLogKoordinator_wrapper .dataTables_filter {
            display: none;
        }
    </style>

    <!-- 4 KPI Summary Cards -->
    <div class="row mb-4 g-3">
        <!-- Card 1: Total Koordinator -->
        <div class="col-6 col-lg-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #0284c7 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#e0f2fe; color:#0284c7;">
                            <i class="ti ti-users"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#e0f2fe; color:#0284c7; font-size:0.68rem; font-weight:700;">Wilayah</span>
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
        <div class="col-6 col-lg-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #16a34a !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#dcfce7; color:#16a34a;">
                            <i class="ti ti-trophy"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#dcfce7; color:#16a34a; font-size:0.68rem; font-weight:700;">Disiplin</span>
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
        <div class="col-6 col-lg-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #dc2626 !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#fee2e2; color:#dc2626;">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <span class="badge rounded-pill" style="background:#fee2e2; color:#dc2626; font-size:0.68rem; font-weight:700;">Pending</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Belum Entri">Belum Entri</div>
                        <div class="kpi-value text-danger" id="kpiBelumEntry">-</div>
                        <div class="kpi-sub"><i class="ti ti-clock text-danger"></i> 0% entri di bulan ini</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Capaian Total Perumahan -->
        <div class="col-6 col-lg-3">
            <div class="card kpi-card h-100" style="border-top: 3.5px solid #7c3aed !important;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="kpi-icon-box" style="background:#ede9fe; color:#7c3aed;">
                            <i class="ti ti-chart-pie"></i>
                        </div>
                        <span class="badge rounded-pill" id="kpiPersenTotalBadge" style="background:#ede9fe; color:#7c3aed; font-size:0.68rem; font-weight:700;">Capaian</span>
                    </div>
                    <div>
                        <div class="kpi-title" title="Capaian Total Perumahan">Capaian Perumahan</div>
                        <div class="kpi-value" style="color:#7c3aed;" id="kpiPersenTotal">-</div>
                        <div class="kpi-sub"><i class="ti ti-cash text-muted"></i> <span id="kpiTotalNominal">-</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard / Ranking Section -->
    <div class="row mb-4 g-3">
        <!-- Koordinator Teladan (Top 3) -->
        <div class="col-lg-6">
            <div class="card kpi-card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                        <h6 class="fw-bold mb-0 text-success"><i class="ti ti-crown me-1"></i> Koordinator Teladan (Top Entri)</h6>
                        <small class="text-muted">Kelengkapan entri tertinggi</small>
                    </div>
                    <div id="containerTop3">
                        <div class="text-center py-3 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Koordinator Perlu Diingatkan / Menunggak -->
        <div class="col-lg-6">
            <div class="card kpi-card h-100">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
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

    <!-- Main Table Card with Integrated Toolbar -->
    <div class="card shadow-sm rounded-4 mb-4" style="border: 1.5px solid #cbd5e1 !important;">
        <!-- Header with Filter Controls -->
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <!-- Row 1: Segmented Button Tabs for Periode & Status Badge -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <div class="filter-period-tabs" id="groupPeriodeTabs">
                    <button type="button" class="period-btn active" data-bulan="<?= sprintf('%02d', $cur_m) ?>" data-tahun="<?= $cur_y ?>" data-label="Bulan Ini (<?= $nama_bulan[sprintf('%02d', $cur_m)] . ' ' . $cur_y ?>)">
                        <i class="ti ti-calendar"></i> Bulan Ini (<?= $nama_bulan[sprintf('%02d', $cur_m)] ?>)
                    </button>
                    <button type="button" class="period-btn" data-bulan="<?= $prev_m1 ?>" data-tahun="<?= $prev_y1 ?>" data-label="Bulan Lalu (<?= $nama_bulan[$prev_m1] . ' ' . $prev_y1 ?>)">
                        <i class="ti ti-calendar-minus"></i> Bulan Lalu (<?= $nama_bulan[$prev_m1] ?>)
                    </button>
                    <button type="button" class="period-btn" data-bulan="<?= $prev_m2 ?>" data-tahun="<?= $prev_y2 ?>" data-label="2 Bulan Lalu (<?= $nama_bulan[$prev_m2] . ' ' . $prev_y2 ?>)">
                        <i class="ti ti-history"></i> <?= $nama_bulan[$prev_m2] ?>
                    </button>
                    <button type="button" class="period-btn" data-custom="true" id="btnCustomTrigger">
                        <i class="ti ti-calendar-search"></i> Pilih Bulan & Tahun 📅
                    </button>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark border px-3 py-2 fw-semibold" id="activeFilterBadge">
                        <i class="ti ti-calendar-check text-success me-1"></i> Periode: <span id="labelPeriodeAktif"><?= $nama_bulan[sprintf('%02d', $cur_m)] . ' ' . $cur_y ?></span>
                    </span>
                </div>
            </div>

            <!-- Custom Month & Year Range Bar (Muncul saat klik Pilih Bulan & Tahun) -->
            <div id="boxCustomMonthYear" class="p-3 mb-3 rounded-3" style="display:none; background:#f8fafc; border:1px dashed #cbd5e1;">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="small fw-bold text-dark"><i class="ti ti-calendar-event text-primary me-1"></i>Pilih Periode:</span>
                    <div class="d-flex align-items-center gap-2">
                        <select id="filterBulan" class="form-select form-select-sm" style="width:140px;">
                            <?php foreach ($nama_bulan as $k => $v): ?>
                                <option value="<?= $k ?>" <?= ($bulan_filter == $k) ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="filterTahun" class="form-select form-select-sm" style="width:100px;">
                            <?php for ($y = date('Y'); $y >= 2023; $y--): ?>
                                <option value="<?= $y ?>" <?= ($tahun_filter == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary px-3 fw-bold shadow-sm" onclick="applyCustomMonthYear()">
                        <i class="ti ti-check me-1"></i> Terapkan
                    </button>
                    <button type="button" class="btn btn-sm btn-light border px-3" onclick="cancelCustomMonthYear()">
                        Batal
                    </button>
                </div>
            </div>

            <!-- Row 2: Secondary Dropdowns (Filter Status Kerajinan & Live Search) -->
            <div class="row g-2 align-items-center pt-2 border-top">
                <div class="col-md-4 col-sm-6 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-award"></i></span>
                        <select id="filterRajin" class="form-select form-select-sm border-start-0 fw-semibold text-dark">
                            <option value="">Semua Status Kerajinan</option>
                            <option value="rajin">🏆 Rajin / Disiplin (&ge; 75%)</option>
                            <option value="cukup">⚠️ Cukup (50% - 74%)</option>
                            <option value="kurang">❌ Perlu Diingatkan (&lt; 50%)</option>
                            <option value="belum">🚫 Belum Entri (0%)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-8 col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" id="customSearchInput" class="form-control form-control-sm border-start-0" placeholder="Ketik nama koordinator, username, atau no HP untuk mencari...">
                        <button class="btn btn-outline-secondary" type="button" onclick="clearSearch()" title="Hapus Pencarian"><i class="ti ti-backspace"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Body -->
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table id="tblLogKoordinator" class="table table-hover align-middle w-100" style="font-size:0.88rem">
                    <thead class="table-dark">
                        <tr>
                            <th width="35" class="text-center text-white" style="border-top-left-radius: 8px;">No</th>
                            <th class="text-white">Koordinator Blok</th>
                            <th class="text-center text-white" width="80">Binaan</th>
                            <th class="text-center text-white" width="95">Sudah Entri</th>
                            <th class="text-center text-white" width="95">Nunggak</th>
                            <th class="text-white" width="140">Capaian (%)</th>
                            <th class="text-end text-white" width="120">Nominal Masuk</th>
                            <th class="text-white">Terakhir Entri</th>
                            <th class="text-center text-white" width="120">Status Kerajinan</th>
                            <th class="text-center text-white" width="110" style="border-top-right-radius: 8px;">Aksi</th>
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

<!-- Modal Detail Rekap Koordinator -->
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
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
var dtKoordinator;
var currentBulan = "<?= sprintf('%02d', $cur_m) ?>";
var currentTahun = "<?= $cur_y ?>";
var searchTimer = null;

$(document).ready(function(){
    function initTable() {
        dtKoordinator = $('#tblLogKoordinator').DataTable({
            processing: true,
            serverSide: false,
            destroy: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[5, 'desc']], // Urut berdasarkan Capaian % tertinggi
            dom: '<"row align-items-center mb-3"<"col-md-6 col-12"l><"col-md-6 col-12 text-md-end">>rt<"row align-items-center mt-3"<"col-md-6 col-12"i><"col-md-6 col-12 text-md-end"p>>',
            ajax: {
                url: "<?= base_url('ajax-log-koordinator') ?>",
                type: "GET",
                data: function(d) {
                    d.bulan = currentBulan;
                    d.tahun = currentTahun;
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
                            html += '<br><a href="' + waUrl + '" target="_blank" class="small text-success text-decoration-none fw-semibold"><i class="bi bi-whatsapp"></i> ' + escapeHtml(row.no_hp) + '</a>';
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
                    render: function(data) {
                        if (!data || data === '-') return '<span class="text-muted small">-</span>';
                        return '<small class="text-nowrap"><i class="ti ti-clock me-1 text-muted"></i>' + data + '</small>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return '<span class="badge ' + row.badge_class + ' px-2 py-1 fw-bold">' + row.label_rajin + '</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row) {
                        return '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 btn-detail-koor fw-semibold" data-id="' + row.id + '"><i class="ti ti-eye me-1"></i>Detail</button>';
                    }
                }
            ],
            language: {
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ - _END_ dari _TOTAL_ koordinator",
                infoEmpty: "Menampilkan 0 data",
                paginate: { previous: "Sebelumnya", next: "Selanjutnya" },
                emptyTable: "Tidak ada data koordinator pada periode ini",
                zeroRecords: "Koordinator tidak ditemukan",
                processing: '<div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat data...'
            }
        });
    }

    initTable();

    // Event Handler segmented tabs periode
    $('.period-btn').on('click', function() {
        var isCustom = $(this).data('custom');
        $('.period-btn').removeClass('active');
        $(this).addClass('active');

        if (isCustom) {
            $('#boxCustomMonthYear').slideDown(180);
        } else {
            $('#boxCustomMonthYear').slideUp(150);
            currentBulan = $(this).data('bulan');
            currentTahun = $(this).data('tahun');
            $('#labelPeriodeAktif').text($(this).data('label'));
            dtKoordinator.ajax.reload();
        }
    });

    // Custom filtering by Status Kerajinan
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'tblLogKoordinator') return true;
        var filterVal = $('#filterRajin').val();
        if (!filterVal) return true;

        var rowData = dtKoordinator.row(dataIndex).data();
        if (!rowData) return true;
        var status = rowData.status_rajin; // 'rajin', 'cukup', 'kurang', 'belum'

        if (filterVal === 'rajin') return status === 'rajin';
        if (filterVal === 'cukup') return status === 'cukup';
        if (filterVal === 'kurang') return status === 'kurang';
        if (filterVal === 'belum') return status === 'belum';
        return true;
    });

    $('#filterRajin').on('change', function() {
        dtKoordinator.draw();
    });

    // Unified Live Search with debounce
    $('#customSearchInput').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            dtKoordinator.search($('#customSearchInput').val()).draw();
        }, 300);
    });

    // Modal Detail Handler
    $(document).on('click', '.btn-detail-koor', function(){
        var id = $(this).data('id');
        openDetailModal(id);
    });
});

function applyCustomMonthYear() {
    currentBulan = $('#filterBulan').val();
    currentTahun = $('#filterTahun').val();
    var bulanText = $('#filterBulan option:selected').text();
    $('#labelPeriodeAktif').text(bulanText + ' ' + currentTahun);
    dtKoordinator.ajax.reload();
}

function cancelCustomMonthYear() {
    $('#boxCustomMonthYear').slideUp(150);
    $('.period-btn').removeClass('active');
    $('[data-custom!="true"]').first().addClass('active');
    currentBulan = $('[data-custom!="true"]').first().data('bulan');
    currentTahun = $('[data-custom!="true"]').first().data('tahun');
    $('#labelPeriodeAktif').text($('[data-custom!="true"]').first().data('label'));
    dtKoordinator.ajax.reload();
}

function clearSearch() {
    $('#customSearchInput').val('');
    if (dtKoordinator) {
        dtKoordinator.search('').draw();
    }
}

function reloadTableKoor() {
    if (dtKoordinator) {
        dtKoordinator.ajax.reload(null, false);
    }
}

function updateKPI(kpi) {
    $('#kpiTotalKoor').text(kpi.total_koordinator);
    $('#kpiTotalRajin').text(kpi.total_rajin);
    $('#kpiBelumEntry').text(kpi.total_belum_entry);
    $('#kpiPersenTotal').text(kpi.persen_total_perumahan + '%');
    $('#kpiTotalNominal').text(kpi.total_nominal_all_rp);
}

function updateLeaderboard(kpi) {
    // Top 3
    var topHtml = '';
    if (kpi.top3 && kpi.top3.length > 0) {
        $.each(kpi.top3, function(i, k){
            var medal = (i === 0) ? '🥇' : ((i === 1) ? '🥈' : '🥉');
            topHtml += '<div class="d-flex align-items-center justify-content-between py-2 border-bottom">' +
                '<div class="d-flex align-items-center gap-2">' +
                    '<span class="fs-5">' + medal + '</span>' +
                    '<div>' +
                        '<strong class="text-dark">' + escapeHtml(k.nama) + '</strong>' +
                        '<div class="small text-muted">' + k.jml_lunas + ' dari ' + k.jml_rumah + ' rumah tuntas</div>' +
                    '</div>' +
                '</div>' +
                '<div class="text-end">' +
                    '<span class="badge bg-success-subtle text-success fw-bold fs-6">' + k.persen_lunas + '%</span>' +
                '</div>' +
            '</div>';
        });
    } else {
        topHtml = '<div class="text-muted text-center py-2">Belum ada data capaian</div>';
    }
    $('#containerTop3').html(topHtml);

    // Bottom 3
    var botHtml = '';
    if (kpi.bottom3 && kpi.bottom3.length > 0) {
        $.each(kpi.bottom3, function(i, k){
            botHtml += '<div class="d-flex align-items-center justify-content-between py-2 border-bottom">' +
                '<div class="d-flex align-items-center gap-2">' +
                    '<span class="badge bg-danger-subtle text-danger rounded-circle p-2"><i class="ti ti-bell-ringing"></i></span>' +
                    '<div>' +
                        '<strong class="text-dark">' + escapeHtml(k.nama) + '</strong>' +
                        '<div class="small text-danger">' + k.jml_nunggak + ' rumah belum entri</div>' +
                    '</div>' +
                '</div>' +
                '<div class="text-end">' +
                    '<span class="badge bg-danger-subtle text-danger fw-bold fs-6">' + k.persen_lunas + '%</span>' +
                '</div>' +
            '</div>';
        });
    } else {
        botHtml = '<div class="text-muted text-center py-2 text-success"><i class="ti ti-circle-check me-1"></i>Semua koordinator tertib entri</div>';
    }
    $('#containerBottom3').html(botHtml);
}

function openDetailModal(id) {
    $('#modalKoorTitle').html('<i class="ti ti-user-check text-primary me-1"></i> Rincian Entri Koordinator');
    $('#modalKoorSubtitle').text('Periode: ' + $('#labelPeriodeAktif').text());
    $('#modalKoorBody').html('<div class="text-center py-5"><div class="spinner-border text-primary mb-2"></div><div class="text-muted">Memuat rincian data rumah...</div></div>');
    $('#modalKoorWaBtn').html('');
    $('#modalDetailKoor').modal('show');

    $.ajax({
        url: "<?= base_url('ajax-detail-log-koordinator') ?>",
        type: "GET",
        data: {
            id: id,
            bulan: currentBulan,
            tahun: currentTahun
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                renderDetailModal(res);
            } else {
                $('#modalKoorBody').html('<div class="alert alert-danger mb-0">' + (res.message || 'Gagal memuat detail') + '</div>');
            }
        },
        error: function() {
            $('#modalKoorBody').html('<div class="alert alert-danger mb-0">Terjadi kesalahan koneksi ke server</div>');
        }
    });
}

function renderDetailModal(res) {
    var k = res.koordinator;
    $('#modalKoorTitle').html('<i class="ti ti-user-check text-primary me-1"></i> Rincian Entri: <strong>' + escapeHtml(k.nama) + '</strong>');
    $('#modalKoorSubtitle').text('Periode: ' + k.bulan_label + ' ' + k.tahun);

    var html = '<div class="row g-2 mb-3">' +
        '<div class="col-sm-3 col-6"><div class="p-2 border rounded bg-light text-center"><small class="text-muted d-block">Binaan</small><strong>' + k.jml_rumah + ' Rumah</strong></div></div>' +
        '<div class="col-sm-3 col-6"><div class="p-2 border rounded bg-light text-center"><small class="text-success d-block">Sudah Lunas</small><strong class="text-success">' + k.jml_lunas + '</strong></div></div>' +
        '<div class="col-sm-3 col-6"><div class="p-2 border rounded bg-light text-center"><small class="text-danger d-block">Nunggak</small><strong class="text-danger">' + k.jml_nunggak + '</strong></div></div>' +
        '<div class="col-sm-3 col-6"><div class="p-2 border rounded bg-light text-center"><small class="text-primary d-block">Capaian</small><strong class="text-primary">' + k.persen_lunas + '%</strong></div></div>' +
    '</div>';

    html += '<ul class="nav nav-tabs mb-3" role="tablist">' +
        '<li class="nav-item"><button class="nav-link active fw-bold text-danger" data-bs-toggle="tab" data-bs-target="#tabNunggak"><i class="ti ti-alert-circle me-1"></i>Belum Bayar (' + (res.list_nunggak ? res.list_nunggak.length : 0) + ')</button></li>' +
        '<li class="nav-item"><button class="nav-link fw-bold text-success" data-bs-toggle="tab" data-bs-target="#tabLunas"><i class="ti ti-circle-check me-1"></i>Sudah Lunas (' + (res.list_lunas ? res.list_lunas.length : 0) + ')</button></li>' +
    '</ul>';

    html += '<div class="tab-content">';
    // Tab Nunggak
    html += '<div class="tab-pane fade show active" id="tabNunggak">';
    if (res.list_nunggak && res.list_nunggak.length > 0) {
        html += '<div class="table-responsive" style="max-height:350px"><table class="table table-sm table-hover align-middle"><thead class="table-light"><tr><th>Alamat</th><th>Nama Warga</th><th>No HP</th><th>Aksi</th></tr></thead><tbody>';
        $.each(res.list_nunggak, function(i, r){
            var waWargaBtn = '-';
            if (r.no_hp) {
                var cleanHp = r.no_hp.replace(/^0/, '62').replace(/[^0-9]/g, '');
                var textWa = encodeURIComponent("Assalamu'alaikum/Salam sejahtera Bapak/Ibu " + r.nama + " (" + r.alamat + "),\n\nMengingatkan perihal iuran IPL Paguyuban TSI untuk bulan " + k.bulan_label + " " + k.tahun + " tercatat belum lunas.\nMohon dapat segera diselesaikan melalui Koordinator Blok atau Transfer ke Bendahara.\n\nTerima kasih.");
                waWargaBtn = '<a href="https://wa.me/' + cleanHp + '?text=' + textWa + '" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0"><i class="bi bi-whatsapp"></i> Chat</a>';
            }
            html += '<tr><td><strong>' + escapeHtml(r.alamat) + '</strong></td><td>' + escapeHtml(r.nama) + '</td><td><small class="text-muted">' + (r.no_hp || '-') + '</small></td><td>' + waWargaBtn + '</td></tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<div class="alert alert-success mb-0 py-3 text-center"><i class="ti ti-circle-check fs-5 me-1"></i>Luar biasa! Seluruh rumah binaan sudah lunas pada periode ini.</div>';
    }
    html += '</div>';

    // Tab Lunas
    html += '<div class="tab-pane fade" id="tabLunas">';
    if (res.list_lunas && res.list_lunas.length > 0) {
        html += '<div class="table-responsive" style="max-height:350px"><table class="table table-sm table-hover align-middle"><thead class="table-light"><tr><th>Alamat</th><th>Nama Warga</th><th>Nominal</th><th>Tanggal Bayar</th><th>Via</th></tr></thead><tbody>';
        $.each(res.list_lunas, function(i, r){
            html += '<tr><td><strong>' + escapeHtml(r.alamat) + '</strong></td><td>' + escapeHtml(r.nama) + '</td><td class="text-success fw-bold">Rp' + numberFormat(r.jumlah_bayar) + '</td><td><small class="text-muted">' + (r.tanggal_bayar || '-') + '</small></td><td><span class="badge bg-light text-dark border">' + r.pembayaran_via + '</span></td></tr>';
        });
        html += '</tbody></table></div>';
    } else {
        html += '<div class="alert alert-warning mb-0 py-3 text-center">Belum ada pembayaran yang tercatat lunas pada periode ini.</div>';
    }
    html += '</div>';
    html += '</div>';

    $('#modalKoorBody').html(html);

    if (k.wa_link) {
        $('#modalKoorWaBtn').html('<a href="' + k.wa_link + '" target="_blank" class="btn btn-sm btn-success fw-semibold rounded-pill px-3 shadow-sm"><i class="bi bi-whatsapp me-1"></i> Kirim Rekap ke WhatsApp Koordinator</a>');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

function numberFormat(val) {
    if (!val) return '0';
    return Number(val).toLocaleString('id-ID');
}
</script>
