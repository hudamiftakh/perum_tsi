<?php
$nama_bulan = [
    '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
    '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
    '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
];
$bulan = str_pad($bulan_filter, 2, '0', STR_PAD_LEFT);
$tahun = $tahun_filter;
$bulan_label = $nama_bulan[$bulan] ?? $bulan;
$total_koordinator = count($koordinator_list);
$rajin = array_filter($koordinator_list, function($k){ return $k['total_entry'] > 0; });
$tidak_rajin = array_filter($koordinator_list, function($k){ return $k['total_entry'] == 0; });
$total_entry_all = array_sum(array_column($koordinator_list, 'total_entry'));
$total_nominal_all = array_sum(array_column($koordinator_list, 'total_nominal'));

// Sort untuk ranking
$sorted_desc = $koordinator_list;
usort($sorted_desc, function($a,$b){ return $b['total_entry'] - $a['total_entry']; });
$top3 = array_slice($sorted_desc, 0, 3);
$sorted_asc = $koordinator_list;
usort($sorted_asc, function($a,$b){ return $a['total_entry'] - $b['total_entry']; });
$bottom3 = array_slice($sorted_asc, 0, 3);
?>

<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="card bg-light-info shadow-none position-relative overflow-hidden mb-4">
        <div class="card-body px-4 py-3">
            <div class="row align-items-center">
                <div class="col-9">
                    <h4 class="fw-semibold mb-8">📊 Log Laporan Koordinator</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Log Koordinator</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= base_url('log-koordinator') ?>" class="row align-items-end g-2">
                <div class="col-auto">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.82rem">📅 Bulan</label>
                    <select name="bulan" class="form-select form-select-sm" style="min-width:140px">
                        <?php foreach ($nama_bulan as $bk => $bv): ?>
                            <option value="<?= $bk ?>" <?= ($bk==$bulan)?'selected':'' ?>><?= $bv ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label fw-semibold mb-1" style="font-size:0.82rem">📆 Tahun</label>
                    <select name="tahun" class="form-select form-select-sm" style="min-width:100px">
                        <?php for ($y = date('Y'); $y >= 2024; $y--): ?>
                            <option value="<?= $y ?>" <?= ($y==$tahun)?'selected':'' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-success fw-semibold">
                        <i class="ti ti-filter"></i> Filter
                    </button>
                </div>
                <div class="col-auto ms-auto">
                    <span class="badge bg-dark fs-6"><?= $bulan_label ?> <?= $tahun ?></span>
                </div>
            </form>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #0ea5e9 !important">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:46px;height:46px;background:#e0f2fe;color:#0ea5e9;font-size:1.3rem;flex-shrink:0">
                            <i class="ti ti-users"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px">Total Koordinator</h6>
                            <h3 class="fw-bold mb-0" style="color:#0ea5e9"><?= $total_koordinator ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #10b981 !important">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:46px;height:46px;background:#d1fae5;color:#10b981;font-size:1.3rem;flex-shrink:0">
                            <i class="ti ti-mood-happy"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px">Rajin Entry</h6>
                            <h3 class="fw-bold mb-0" style="color:#10b981"><?= count($rajin) ?></h3>
                            <small class="text-muted">dari <?= $total_koordinator ?> koordinator</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f43f5e !important">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:46px;height:46px;background:#ffe4e6;color:#f43f5e;font-size:1.3rem;flex-shrink:0">
                            <i class="ti ti-mood-sad"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px">Belum Entry</h6>
                            <h3 class="fw-bold mb-0" style="color:#f43f5e"><?= count($tidak_rajin) ?></h3>
                            <small class="text-muted">perlu follow up</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #f59e0b !important">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:46px;height:46px;background:#fef3c7;color:#f59e0b;font-size:1.3rem;flex-shrink:0">
                            <i class="ti ti-receipt-2"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-0" style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.5px">Entry <?= $bulan_label ?></h6>
                            <h3 class="fw-bold mb-0" style="color:#f59e0b"><?= $total_entry_all ?></h3>
                            <small class="text-muted">Rp<?= number_format($total_nominal_all, 0, ',', '.') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">📈 Komposisi <?= $bulan_label ?> <?= $tahun ?></h6>
                    <div style="position:relative;height:220px;display:flex;align-items:center;justify-content:center">
                        <canvas id="donutChart"></canvas>
                    </div>
                    <div class="text-center mt-3">
                        <span class="badge bg-success me-1">● Rajin: <?= count($rajin) ?></span>
                        <span class="badge bg-danger">● Belum: <?= count($tidak_rajin) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">📊 Tren Entry Koordinator — Tahun <?= $tahun ?></h6>
                    <div style="position:relative;height:260px">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ranking Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">🏆 Top 3 Koordinator Terajin</h6>
                    <?php foreach ($top3 as $i => $tk):
                        $medal = ['🥇','🥈','🥉'][$i] ?? '';
                        $pct = ($tk['jml_rumah'] > 0) ? min(round(($tk['total_entry']/$tk['jml_rumah'])*100), 100) : 0;
                    ?>
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2" style="background:#f0fdf4">
                        <span style="font-size:1.5rem;width:36px;text-align:center"><?= $medal ?></span>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($tk['nama']) ?></strong>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div class="progress flex-grow-1" style="height:6px">
                                    <div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div>
                                </div>
                                <small class="fw-bold text-success text-nowrap"><?= $tk['total_entry'] ?> entry</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($top3)): ?>
                        <p class="text-muted text-center py-3">Belum ada data entry</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">⚠️ Koordinator Perlu Perhatian</h6>
                    <?php foreach ($bottom3 as $i => $bk):
                        $pct = ($bk['jml_rumah'] > 0) ? min(round(($bk['total_entry']/$bk['jml_rumah'])*100), 100) : 0;
                        $is_zero = ($bk['total_entry'] == 0);
                    ?>
                    <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2" style="background:<?= $is_zero ? '#fff1f2' : '#fefce8' ?>">
                        <span style="font-size:1.3rem;width:36px;text-align:center"><?= $is_zero ? '🔴' : '🟡' ?></span>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($bk['nama']) ?></strong>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <div class="progress flex-grow-1" style="height:6px">
                                    <div class="progress-bar <?= $is_zero ? 'bg-danger' : 'bg-warning' ?>" style="width:<?= max($pct,3) ?>%"></div>
                                </div>
                                <small class="fw-bold text-nowrap <?= $is_zero ? 'text-danger' : 'text-warning' ?>"><?= $bk['total_entry'] ?> entry</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="fw-bold mb-3"><i class="ti ti-list-details"></i> Detail Aktivitas Entry Koordinator</h6>
            <div class="table-responsive">
                <table id="tblLogKoordinator" class="table table-striped table-hover align-middle" style="width:100%">
                    <thead class="table-dark">
                        <tr>
                            <th width="35" class="text-center">No</th>
                            <th>Koordinator</th>
                            <th class="text-center">Binaan</th>
                            <th class="text-center">Total Entry</th>
                            <th>Capaian</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center">Verified</th>
                            <th class="text-center">Pending</th>
                            <th class="text-center">Via</th>
                            <th>Entry Terakhir</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        usort($koordinator_list, function($a,$b){ return $b['total_entry'] - $a['total_entry']; });
                        $no = 1;
                        foreach ($koordinator_list as $k):
                            $persen = ($k['jml_rumah'] > 0) ? round(($k['total_entry'] / $k['jml_rumah']) * 100) : 0;
                            if ($persen > 100) $persen = 100;
                            $bar_class = $persen >= 70 ? 'bg-success' : ($persen >= 30 ? 'bg-warning' : 'bg-danger');
                        ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++ ?></td>
                            <td>
                                <strong><?= htmlspecialchars($k['nama']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($k['username'] ?? '') ?></small>
                            </td>
                            <td class="text-center"><span class="badge bg-secondary"><?= $k['jml_rumah'] ?></span></td>
                            <td class="text-center">
                                <span class="fw-bold fs-5 <?= $k['total_entry']>0 ? 'text-success' : 'text-danger' ?>"><?= $k['total_entry'] ?></span>
                            </td>
                            <td style="min-width:120px">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:7px">
                                        <div class="progress-bar <?= $bar_class ?>" style="width:<?= $persen ?>%"></div>
                                    </div>
                                    <small class="fw-bold text-nowrap"><?= $persen ?>%</small>
                                </div>
                            </td>
                            <td class="text-end text-nowrap"><strong>Rp<?= number_format($k['total_nominal'],0,',','.') ?></strong></td>
                            <td class="text-center">
                                <?= $k['verified']>0 ? '<span class="badge bg-success">'.$k['verified'].'</span>' : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="text-center">
                                <?= $k['pending']>0 ? '<span class="badge bg-warning text-dark">'.$k['pending'].'</span>' : '<span class="text-muted">—</span>' ?>
                            </td>
                            <td class="text-center text-nowrap">
                                <?php if ($k['via_koordinator'] > 0 || $k['via_transfer'] > 0): ?>
                                    <?php if ($k['via_koordinator'] > 0): ?><span class="badge bg-info"><?= $k['via_koordinator'] ?> Koor</span><?php endif; ?>
                                    <?php if ($k['via_transfer'] > 0): ?><span class="badge bg-primary"><?= $k['via_transfer'] ?> Tf</span><?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <?php if ($k['last_entry']): ?>
                                    <small><i class="bi bi-clock text-muted"></i> <?= date('d/m/Y H:i', strtotime($k['last_entry'])) ?></small>
                                <?php else: ?>
                                    <small class="text-danger">Belum ada</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center text-nowrap">
                                <?php if ($k['total_entry']==0): ?>
                                    <span class="badge bg-danger" style="font-size:0.75rem">❌ Belum Entry</span>
                                <?php elseif ($persen >= 70): ?>
                                    <span class="badge bg-success" style="font-size:0.75rem">✅ Rajin</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark" style="font-size:0.75rem">⚠️ Kurang</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function(){
    $('#tblLogKoordinator').DataTable({
        order:[[3,'desc']],
        pageLength:25,
        language:{
            search:"Cari:",
            lengthMenu:"Tampilkan _MENU_ data",
            info:"Menampilkan _START_ - _END_ dari _TOTAL_ koordinator",
            paginate:{previous:"Sebelumnya",next:"Selanjutnya"},
            emptyTable:"Tidak ada data koordinator",
            zeroRecords:"Data tidak ditemukan"
        },
        columnDefs:[{targets:[4,8],orderable:false}]
    });

    // Donut Chart
    new Chart(document.getElementById('donutChart'),{
        type:'doughnut',
        data:{
            labels:['Rajin Entry','Belum Entry'],
            datasets:[{
                data:[<?= count($rajin) ?>,<?= count($tidak_rajin) ?>],
                backgroundColor:['#10b981','#f43f5e'],
                borderWidth:3,
                borderColor:'#fff',
                hoverOffset:8
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            cutout:'65%',
            plugins:{legend:{display:false}}
        }
    });

    // Bar Chart Trend
    <?php
    $tp = [];
    foreach ($trend_data as $td) {
        $b = $td['bulan'];
        $tp[$b] = ($tp[$b] ?? 0) + $td['total'];
    }
    ksort($tp);
    $tl = []; $tv = [];
    foreach ($tp as $b => $t) {
        $p = explode('-', $b);
        $tl[] = ($nama_bulan[$p[1]] ?? $p[1]);
        $tv[] = $t;
    }
    ?>
    new Chart(document.getElementById('trendChart'),{
        type:'bar',
        data:{
            labels:<?= json_encode($tl) ?>,
            datasets:[{
                label:'Total Entry',
                data:<?= json_encode($tv) ?>,
                backgroundColor:'rgba(0,141,76,0.7)',
                borderColor:'#008d4c',
                borderWidth:1,
                borderRadius:8,
                borderSkipped:false
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            scales:{
                y:{beginAtZero:true,ticks:{stepSize:1},grid:{color:'#f0f0f0'}},
                x:{grid:{display:false}}
            },
            plugins:{legend:{display:false}}
        }
    });
});
</script>
