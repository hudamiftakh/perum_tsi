<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');

// ============================================
// PARAMETER
// ============================================
$bulan_indo = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];
$bulan_indo_short = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];

$selected_tahun = $this->input->get('tahun');
$selected_tahun = (!empty($selected_tahun)) ? (int)$selected_tahun : (int)date('Y');

$selected_bulan = $this->input->get('bulan');
$selected_bulan = (!empty($selected_bulan)) ? (int)$selected_bulan : (int)date('n');

if ($this->session->userdata('username')['role'] === 'koordinator') {
    $selected_koor = $this->session->userdata('username')['id'];
} else {
    $selected_koor = $this->input->get('id_koordinator');
}

// Koordinator dropdown
if ($this->session->userdata('username')['role'] === 'koordinator') {
    $koordinator_list = $this->db->query(
        "SELECT id, nama FROM master_koordinator_blok WHERE id = '" . $this->db->escape_str($this->session->userdata('username')['id']) . "'"
    )->result_array();
} else {
    $koordinator_list = $this->db->query("SELECT DISTINCT id, nama FROM master_koordinator_blok ORDER BY nama")->result_array();
}

$where_koor = !empty($selected_koor)
    ? "AND b.id_koordinator = '" . $this->db->escape_str($selected_koor) . "'"
    : '';

// ============================================
// 1. DATA TUNGGAKAN
// ============================================
$bulan_str = sprintf('%04d-%02d', $selected_tahun, $selected_bulan);

// Semua rumah
$all_rumah = $this->db->query("
    SELECT r.id, r.alamat, r.nama, MAX(kl.no_hp) as no_hp, MAX(k.nama) as koordinator, r.id_koordinator
    FROM master_users u
    LEFT JOIN master_rumah r ON u.id_rumah = r.id
    LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
    LEFT JOIN master_keluarga kl ON kl.nomor_rumah = r.alamat AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
    WHERE r.id IS NOT NULL
    $where_koor
    GROUP BY r.id, r.alamat, r.nama, r.id_koordinator
    ORDER BY r.alamat ASC
")->result_array();

// Rumah yang sudah bayar bulan tersebut
$sudah_bayar_ids = $this->db->query("
    SELECT DISTINCT u.id_rumah
    FROM master_pembayaran p
    LEFT JOIN master_users u ON p.user_id = u.id
    LEFT JOIN master_rumah b ON u.id_rumah = b.id
    WHERE p.status IN ('verified','pending')
    AND (
        DATE_FORMAT(p.untuk_bulan, '%Y-%m') = '$bulan_str'
        OR FIND_IN_SET('$bulan_str', p.bulan_rapel) > 0
        OR (
            DATE_FORMAT(p.bulan_mulai, '%Y-%m') = '$bulan_str'
            AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
        )
    )
    $where_koor
")->result_array();

$paid_ids = array_column($sudah_bayar_ids, 'id_rumah');
$tunggakan = [];
foreach ($all_rumah as $r) {
    if (!in_array($r['id'], $paid_ids)) {
        $tunggakan[] = $r;
    }
}

// ============================================
// 2. DATA GRAFIK - Tren Pembayaran Per Bulan
// ============================================
$bulan_akhir_chart = ($selected_tahun == (int)date('Y')) ? (int)date('n') : 12;

$chart_transfer = [];
$chart_koor = [];
$chart_labels = [];

for ($m = 1; $m <= $bulan_akhir_chart; $m++) {
    $chart_labels[] = $bulan_indo_short[$m];

    // Transfer
    $row_t = $this->db->query("
        SELECT COALESCE(SUM(p.jumlah_bayar),0) as total
        FROM master_pembayaran p
        LEFT JOIN master_users u ON p.user_id = u.id
        LEFT JOIN master_rumah b ON u.id_rumah = b.id
        WHERE p.status = 'verified'
        AND p.pembayaran_via = 'transfer'
        AND MONTH(p.tanggal_bayar) = $m
        AND YEAR(p.tanggal_bayar) = $selected_tahun
        $where_koor
    ")->row();
    $chart_transfer[] = (float)($row_t->total ?? 0);

    // Koordinator
    $row_k = $this->db->query("
        SELECT COALESCE(SUM(p.jumlah_bayar),0) as total
        FROM master_pembayaran p
        LEFT JOIN master_users u ON p.user_id = u.id
        LEFT JOIN master_rumah b ON u.id_rumah = b.id
        WHERE p.status = 'verified'
        AND p.pembayaran_via = 'koordinator'
        AND MONTH(p.tanggal_bayar) = $m
        AND YEAR(p.tanggal_bayar) = $selected_tahun
        $where_koor
    ")->row();
    $chart_koor[] = (float)($row_k->total ?? 0);
}

// Pie chart data
$pie_transfer = array_sum($chart_transfer);
$pie_koor = array_sum($chart_koor);

// ============================================
// 3. REKAP TAHUNAN (Year-over-Year)
// ============================================
$tahun_mulai = 2025;
$tahun_sekarang = (int)date('Y');
$yoy_data = [];

for ($y = $tahun_mulai; $y <= $tahun_sekarang; $y++) {
    $row_y = $this->db->query("
        SELECT COALESCE(SUM(p.jumlah_bayar),0) as total
        FROM master_pembayaran p
        LEFT JOIN master_users u ON p.user_id = u.id
        LEFT JOIN master_rumah b ON u.id_rumah = b.id
        WHERE p.status = 'verified'
        AND YEAR(p.tanggal_bayar) = $y
        $where_koor
    ")->row();
    $yoy_data[$y] = (float)($row_y->total ?? 0);
}

// Jumlah rumah yg sudah bayar di bulan terpilih
$jumlah_bayar = count($paid_ids);
$jumlah_belum = count($tunggakan);
$total_rumah = count($all_rumah);
$persen_bayar = ($total_rumah > 0) ? round(($jumlah_bayar / $total_rumah) * 100) : 0;
?>

<style>
    /* Existing Theme Table Styles */
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

    /* Specific components */
    .progress-ring {
        width: 80px;
        height: 80px;
    }

    .chart-container {
        position: relative;
        height: 320px;
    }

    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
        }
    }
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<h5 class="mb-4 fw-semibold text-center">📊 Analisis Pembayaran <?= $selected_tahun ?></h5>

<!-- FILTER -->
<form method="get" action="<?= base_url('analisis-pembayaran') ?>" class="mb-4 p-3 border rounded shadow-sm" style="background-color: #f0f8ff;">
    <div class="row gy-2 gx-3 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.8rem;">Bulan</label>
            <select name="bulan" class="form-select rounded">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $selected_bulan == $m ? 'selected' : '' ?>><?= $bulan_indo[$m] ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label fw-semibold" style="font-size:.8rem;">Tahun</label>
            <select name="tahun" class="form-select rounded">
                <?php for ($y = $tahun_sekarang; $y >= 2025; $y--): ?>
                    <option value="<?= $y ?>" <?= $selected_tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <?php if ($this->session->userdata('username')['role'] !== 'koordinator'): ?>
            <div class="col-6 col-md-3">
                <label class="form-label fw-semibold" style="font-size:.8rem;">Koordinator</label>
                <select name="id_koordinator" class="form-select rounded">
                    <option value="">Semua Koordinator</option>
                    <?php foreach ($koordinator_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $selected_koor == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
        <div class="col-6 col-md-2">
            <button class="btn btn-success w-100" type="submit"><i class="fa fa-search me-1"></i> Filter</button>
        </div>
        <div class="col-6 col-md-2">
            <a href="<?= base_url('analisis-pembayaran') ?>" class="btn btn-outline-secondary w-100"><i class="fa fa-times me-1"></i> Reset</a>
        </div>
    </div>
</form>

<!-- RINGKASAN CARD -->
<div class="row mb-4">
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-secondary shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-houses-fill text-primary display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Total Rumah</h6>
                    <h5 class="fw-bold text-primary"><?= $total_rumah ?></h5>
                    <small class="text-muted">Terdaftar di sistem</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-success shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-check-circle-fill text-success display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Sudah Bayar</h6>
                    <h5 class="fw-bold text-success"><?= $jumlah_bayar ?></h5>
                    <small class="text-muted"><?= $bulan_indo[$selected_bulan] ?> <?= $selected_tahun ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-danger shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill text-danger display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Belum Bayar</h6>
                    <h5 class="fw-bold text-danger"><?= $jumlah_belum ?></h5>
                    <small class="text-muted"><?= $bulan_indo[$selected_bulan] ?> <?= $selected_tahun ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-warning shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <div class="progress-ring me-3">
                    <svg viewBox="0 0 36 36" class="w-100 h-100">
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="#e9ecef" stroke-width="3"></path>
                        <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                            fill="none" stroke="<?= $persen_bayar >= 80 ? '#198754' : ($persen_bayar >= 50 ? '#ffc107' : '#dc3545') ?>"
                            stroke-width="3" stroke-dasharray="<?= $persen_bayar ?>, 100" stroke-linecap="round"></path>
                        <text x="18" y="20.35" class="percentage" text-anchor="middle"
                            style="font-size:8px; font-weight:700; fill:#333;"><?= $persen_bayar ?>%</text>
                    </svg>
                </div>
                <div>
                    <h6 class="mb-1">Persentase Bayar</h6>
                    <h5 class="fw-bold text-warning"><?= $persen_bayar ?>%</h5>
                    <small class="text-muted">Bulan <?= $bulan_indo[$selected_bulan] ?></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 1: GRAFIK TREN PEMBAYARAN           -->
<!-- ============================================ -->
<div class="row mb-4">
    <!-- Line Chart -->
    <div class="col-12 col-lg-8 mb-3">
        <div class="card border-0 shadow rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Tren Pembayaran Per Bulan <?= $selected_tahun ?></h6>
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Pie Chart -->
    <div class="col-12 col-lg-4 mb-3">
        <div class="card border-0 shadow rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Transfer vs Koordinator</h6>
                <div class="chart-container" style="height:280px;">
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">Total: <strong>Rp <?= number_format($pie_transfer + $pie_koor) ?></strong></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 2: REKAP TAHUNAN (Year-over-Year)   -->
<!-- ============================================ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow rounded-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">Rekap Tahunan (Year-over-Year)</h6>
                <div class="row">
                    <div class="col-12 col-lg-8">
                        <div class="chart-container" style="height:260px;">
                            <canvas id="yoyChart"></canvas>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <table class="table table-sm table-bordered mt-2">
                            <thead style="background:#6f42c1; color:white;">
                                <tr>
                                    <th>Tahun</th>
                                    <th class="text-end">Total Pembayaran</th>
                                    <th class="text-center">Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $prev_total = 0;
                                foreach ($yoy_data as $yr => $total):
                                    $change = ($prev_total > 0) ? round((($total - $prev_total) / $prev_total) * 100, 1) : 0;
                                    $change_class = $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted');
                                    $change_icon = $change > 0 ? '▲' : ($change < 0 ? '▼' : '—');
                                ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $yr ?></td>
                                        <td class="text-end">Rp <?= number_format($total) ?></td>
                                        <td class="text-center <?= $change_class ?> fw-bold">
                                            <?php if ($prev_total > 0): ?>
                                                <?= $change_icon ?> <?= abs($change) ?>%
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php $prev_total = $total;
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION 3: LAPORAN TUNGGAKAN                -->
<!-- ============================================ -->
<div class="card border-0 shadow rounded-4 mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">
                Daftar Tunggakan — <?= $bulan_indo[$selected_bulan] ?> <?= $selected_tahun ?>
            </h6>
            <span class="badge bg-danger fs-6"><?= $jumlah_belum ?> Rumah</span>
        </div>

        <?php if (empty($tunggakan)): ?>
            <div class="text-center py-4">
                <i class="bi bi-emoji-smile text-success" style="font-size:3rem;"></i>
                <p class="text-muted mt-2">Semua rumah sudah membayar bulan <?= $bulan_indo[$selected_bulan] ?> <?= $selected_tahun ?> 🎉</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width:40px;" class="text-center">No</th>
                            <th>Alamat</th>
                            <th>Nama Penghuni</th>
                            <th>No. HP</th>
                            <th>Koordinator</th>
                            <th style="width:120px;" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($tunggakan as $t): ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                    <?= htmlspecialchars($t['alamat'] ?? '-') ?>
                                </td>
                                <td>
                                    <i class="bi bi-person-fill text-primary me-1"></i>
                                    <strong><?= htmlspecialchars($t['nama'] ?? '-') ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($t['no_hp'])): ?>
                                        <a href="https://wa.me/<?= preg_replace('/^0/', '62', $t['no_hp']) ?>" target="_blank" class="text-decoration-none">
                                            <i class="bi bi-whatsapp text-success me-1"></i><?= htmlspecialchars($t['no_hp']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($t['koordinator'] ?? '-') ?></td>
                                <td class="text-center">
                                    <a href="<?= base_url('pembayaran/' . encrypt_url($t['id'])) ?>" class="btn btn-sm btn-success">
                                        <i class="bi bi-cash-coin"></i> Bayar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- CHART.JS SCRIPTS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== LINE CHART: Tren Per Bulan ==========
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                        label: 'Transfer',
                        data: <?= json_encode($chart_transfer) ?>,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25,135,84,0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#198754',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Koordinator',
                        data: <?= json_encode($chart_koor) ?>,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#0d6efd',
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + (v / 1000000).toFixed(1) + 'jt'
                        }
                    }
                }
            }
        });

        // ========== PIE CHART: Transfer vs Koordinator ==========
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: ['Transfer', 'Koordinator'],
                datasets: [{
                    data: [<?= $pie_transfer ?>, <?= $pie_koor ?>],
                    backgroundColor: ['#198754', '#0d6efd'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': Rp ' + ctx.parsed.toLocaleString('id-ID') + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });

        // ========== BAR CHART: Year-over-Year ==========
        const yoyCtx = document.getElementById('yoyChart').getContext('2d');
        new Chart(yoyCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map('strval', array_keys($yoy_data))) ?>,
                datasets: [{
                    label: 'Total Pembayaran',
                    data: <?= json_encode(array_values($yoy_data)) ?>,
                    backgroundColor: [
                        <?php
                        $colors = ['rgba(111,66,193,0.7)', 'rgba(13,110,253,0.7)', 'rgba(25,135,84,0.7)', 'rgba(255,193,7,0.7)'];
                        $ci = 0;
                        foreach ($yoy_data as $yr => $t) {
                            echo $colors[$ci % count($colors)] . ',';
                            $ci++;
                        }
                        ?>
                    ],
                    borderColor: [
                        <?php
                        $borders = ['#6f42c1', '#0d6efd', '#198754', '#ffc107'];
                        $ci = 0;
                        foreach ($yoy_data as $yr => $t) {
                            echo "'" . $borders[$ci % count($borders)] . "',";
                            $ci++;
                        }
                        ?>
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => 'Rp ' + (v / 1000000).toFixed(0) + 'jt'
                        }
                    }
                }
            }
        });
    });
</script>