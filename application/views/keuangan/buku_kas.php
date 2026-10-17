<?php
$Auth = $this->session->userdata['username'];
$nama_bulan_arr = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>

<style>
    /* Styling Buku Kas */
    .buku-kas-stat-card {
        border-radius: 14px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
        transition: transform 0.2s ease;
    }
    .buku-kas-stat-card:hover {
        transform: translateY(-2px);
    }
    .badge-debit {
        background-color: #dcfce7;
        color: #15803d;
        font-weight: 700;
        border: 1px solid #bbf7d0;
    }
    .badge-kredit {
        background-color: #fee2e2;
        color: #b91c1c;
        font-weight: 700;
        border: 1px solid #fecaca;
    }
    .text-debit {
        color: #16a34a !important;
        font-weight: 700;
    }
    .text-kredit {
        color: #dc2626 !important;
        font-weight: 700;
    }

    /* Mobile Card Feed */
    @media (max-width: 767.98px) {
        .desktop-table-view {
            display: none !important;
        }
        .mobile-feed-view {
            display: block !important;
        }
        .filter-panel .col-auto, .filter-panel .col-12 {
            margin-bottom: 8px;
        }
    }
    @media (min-width: 768px) {
        .desktop-table-view {
            display: block !important;
        }
        .mobile-feed-view {
            display: none !important;
        }
    }

    .kas-item-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #fff;
        margin-bottom: 12px;
        padding: 14px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    }
</style>

<!-- Header Breadcrumb -->
<div class="card w-100 bg-success-subtle shadow-none position-relative overflow-hidden mb-4 border-0">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-bold mb-1 text-success">
                    <i class="ti ti-report-money me-1"></i> Buku Kas Umum (Debit - Kredit)
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                        <li class="breadcrumb-item text-muted">Keuangan</li>
                        <li class="breadcrumb-item text-success active" aria-current="page">Buku Kas</li>
                    </ol>
                </nav>
            </div>
            <div class="col-3 text-end d-none d-sm-block">
                <span class="badge bg-success fs-3 px-3 py-2 rounded-pill shadow-sm">
                    <i class="ti ti-check me-1"></i> <?= htmlspecialchars($periode_label) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Ringkasan Metrik (Total Debit, Kredit, Saldo) -->
<div class="row mb-4">
    <!-- Saldo Akhir -->
    <div class="col-12 col-md-4 mb-3">
        <div class="card buku-kas-stat-card h-100 text-white" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-uppercase fw-semibold" style="letter-spacing: 0.5px; opacity: 0.9; font-size: 0.85rem;">Saldo Kas Saat Ini</span>
                    <span class="p-2 rounded-circle bg-white bg-opacity-25 d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="ti ti-wallet fs-5 text-white"></i>
                    </span>
                </div>
                <h2 class="fw-bold text-white mb-2">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h2>
                <div class="d-flex align-items-center small" style="opacity: 0.9;">
                    <span>Saldo awal: <strong>Rp <?= number_format($saldo_awal, 0, ',', '.') ?></strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pemasukan (Debit) -->
    <div class="col-6 col-md-4 mb-3">
        <div class="card buku-kas-stat-card h-100 bg-white border-start border-4 border-success">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold small text-uppercase">Pemasukan (Debit)</span>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                        <i class="ti ti-arrow-down-left"></i> Kas Masuk
                    </span>
                </div>
                <h3 class="fw-bold text-success mb-1">Rp <?= number_format($total_debit, 0, ',', '.') ?></h3>
                <small class="text-muted">Total penerimaan periode ini</small>
            </div>
        </div>
    </div>

    <!-- Total Pengeluaran (Kredit) -->
    <div class="col-6 col-md-4 mb-3">
        <div class="card buku-kas-stat-card h-100 bg-white border-start border-4 border-danger">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-semibold small text-uppercase">Pengeluaran (Kredit)</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1">
                        <i class="ti ti-arrow-up-right"></i> Kas Keluar
                    </span>
                </div>
                <h3 class="fw-bold text-danger mb-1">Rp <?= number_format($total_kredit, 0, ',', '.') ?></h3>
                <small class="text-muted">Total pengeluaran operasional</small>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Toolbar Aksi Cepat -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="<?= base_url('keuangan/buku-kas') ?>" class="row g-2 align-items-end filter-panel">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Tahun</label>
                <select name="tahun" class="form-select form-select-sm rounded-3">
                    <?php 
                    $cur_y = (int)date('Y');
                    for ($y = $cur_y; $y >= 2024; $y--): ?>
                        <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-muted mb-1">Bulan</label>
                <select name="bulan" class="form-select form-select-sm rounded-3">
                    <option value="semua" <?= ($bulan === 'semua') ? 'selected' : '' ?>>Semua Bulan (Setahun)</option>
                    <?php foreach ($nama_bulan_arr as $k => $nm): ?>
                        <option value="<?= (int)$k ?>" <?= ((int)$bulan === (int)$k && $bulan !== 'semua') ? 'selected' : '' ?>>
                            <?= $nm ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted mb-1">Jenis Kas</label>
                <select name="kategori" class="form-select form-select-sm rounded-3">
                    <option value="semua" <?= ($kategori_filter == 'semua') ? 'selected' : '' ?>>Semua</option>
                    <option value="debit" <?= ($kategori_filter == 'debit') ? 'selected' : '' ?>>Debit (Masuk)</option>
                    <option value="kredit" <?= ($kategori_filter == 'kredit') ? 'selected' : '' ?>>Kredit (Keluar)</option>
                </select>
            </div>

            <div class="col-6 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success rounded-3 px-3 w-100 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="ti ti-filter"></i> Filter
                </button>
                <a href="<?= base_url('keuangan/buku-kas/pdf?tahun=' . $tahun . '&bulan=' . $bulan) ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-3 px-3 w-100 d-inline-flex align-items-center justify-content-center gap-1">
                    <i class="ti ti-printer"></i> Cetak PDF
                </a>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top flex-wrap gap-2">
            <span class="small text-muted">
                Ditemukan <strong><?= count($transaksi) ?></strong> transaksi pada periode <strong><?= htmlspecialchars($periode_label) ?></strong>
            </span>
            <div class="d-flex gap-2">
                <a href="<?= base_url('kas/form') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="ti ti-plus"></i> Catat Pengeluaran
                </a>
                <a href="<?= base_url('siteplan-sampah') ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                    <i class="ti ti-map-pin"></i> Siteplan Sampah
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 1. TAMPILAN DESKTOP: TABEL JURNAL BUKU KAS -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm rounded-4 desktop-table-view mb-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelBukuKas">
                <thead style="background-color: #008d4c; color: #ffffff;">
                    <tr>
                        <th class="text-center text-white" width="5%">No</th>
                        <th class="text-center text-white" width="11%">Tanggal</th>
                        <th class="text-white" width="40%">Uraian Transaksi</th>
                        <th class="text-center text-white" width="10%">Via / Metode</th>
                        <th class="text-end text-white" width="11%">Debit (Masuk)</th>
                        <th class="text-end text-white" width="11%">Kredit (Keluar)</th>
                        <th class="text-end text-white pe-3" width="12%">Saldo (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Baris Saldo Awal -->
                    <tr class="table-light fw-bold" style="border-bottom: 2px solid #cbd5e1;">
                        <td class="text-center"><i class="ti ti-flag text-muted"></i></td>
                        <td class="text-center text-muted"><?= date('d/m/Y', strtotime($tgl_awal)) ?></td>
                        <td colspan="4" class="text-end text-dark">SALDO AWAL PERIODE:</td>
                        <td class="text-end pe-3 fw-bold text-dark">
                            Rp <?= number_format($saldo_awal, 0, ',', '.') ?>
                        </td>
                    </tr>

                    <?php if (empty($transaksi)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="ti ti-file-search fs-8 d-block mb-2 text-muted"></i>
                                Belum ada transaksi kas pada periode <?= htmlspecialchars($periode_label) ?>.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($transaksi as $tr): ?>
                            <tr>
                                <td class="text-center text-muted small"><?= $no++ ?></td>
                                <td class="text-center text-nowrap">
                                    <span class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($tr['tanggal'])) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge <?= ($tr['tipe'] === 'debit') ? 'badge-debit' : 'badge-kredit' ?> px-2 py-1 rounded-pill" style="font-size: 0.7rem;">
                                            <?= ($tr['tipe'] === 'debit') ? 'DEBIT' : 'KREDIT' ?>
                                        </span>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= htmlspecialchars($tr['uraian']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($tr['kategori']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 small">
                                        <?= htmlspecialchars($tr['via']) ?>
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if ($tr['debit'] > 0): ?>
                                        <span class="text-debit">+Rp <?= number_format($tr['debit'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if ($tr['kredit'] > 0): ?>
                                        <span class="text-kredit">-Rp <?= number_format($tr['kredit'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3 fw-bold text-dark text-nowrap">
                                    Rp <?= number_format($tr['saldo'], 0, ',', '.') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold" style="border-top: 2px solid #cbd5e1;">
                        <td colspan="4" class="text-end text-uppercase">Total Transaksi:</td>
                        <td class="text-end text-debit text-nowrap">+Rp <?= number_format($total_debit, 0, ',', '.') ?></td>
                        <td class="text-end text-kredit text-nowrap">-Rp <?= number_format($total_kredit, 0, ',', '.') ?></td>
                        <td class="text-end pe-3 text-nowrap">Surplus: Rp <?= number_format($surplus_defisit, 0, ',', '.') ?></td>
                    </tr>
                    <tr style="background-color: #008d4c; color: #ffffff;" class="fw-bold fs-4">
                        <td colspan="6" class="text-end text-white text-uppercase">Saldo Kas Akhir Periode:</td>
                        <td class="text-end pe-3 text-white text-nowrap">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. TAMPILAN MOBILE: FEED KARTU KHUSUS HP   -->
<!-- ========================================== -->
<div class="mobile-feed-view mb-4">
    <!-- Card Saldo Awal Mobile -->
    <div class="card p-3 mb-3 border-0 bg-light rounded-3 text-center shadow-sm">
        <span class="small text-muted text-uppercase fw-bold">Saldo Awal (Sebelum <?= date('d/m/Y', strtotime($tgl_awal)) ?>)</span>
        <h5 class="fw-bold text-dark mb-0">Rp <?= number_format($saldo_awal, 0, ',', '.') ?></h5>
    </div>

    <?php if (empty($transaksi)): ?>
        <div class="card p-4 text-center border-0 shadow-sm rounded-3">
            <i class="ti ti-file-search fs-8 text-muted mb-2"></i>
            <span class="text-muted">Tidak ada transaksi pada periode ini.</span>
        </div>
    <?php else: ?>
        <?php foreach ($transaksi as $tr): ?>
            <div class="kas-item-card border-start border-4 <?= ($tr['tipe'] === 'debit') ? 'border-success' : 'border-danger' ?>">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge <?= ($tr['tipe'] === 'debit') ? 'badge-debit' : 'badge-kredit' ?> rounded-pill">
                        <?= ($tr['tipe'] === 'debit') ? 'DEBIT (MASUK)' : 'KREDIT (KELUAR)' ?>
                    </span>
                    <span class="small text-muted fw-semibold">
                        <i class="ti ti-calendar me-1"></i><?= date('d/m/Y', strtotime($tr['tanggal'])) ?>
                    </span>
                </div>

                <div class="fw-bold text-dark fs-3 mb-1">
                    <?= htmlspecialchars($tr['uraian']) ?>
                </div>

                <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                    <span><i class="ti ti-tag me-1"></i><?= htmlspecialchars($tr['kategori']) ?></span>
                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($tr['via']) ?></span>
                </div>

                <div class="d-flex justify-content-between align-items-end pt-2 border-top">
                    <div>
                        <small class="text-muted d-block">Nominal</small>
                        <?php if ($tr['tipe'] === 'debit'): ?>
                            <span class="text-debit fs-4">+Rp <?= number_format($tr['debit'], 0, ',', '.') ?></span>
                        <?php else: ?>
                            <span class="text-kredit fs-4">-Rp <?= number_format($tr['kredit'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Saldo Kas</small>
                        <span class="fw-bold text-dark">Rp <?= number_format($tr['saldo'], 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Card Saldo Akhir Mobile -->
    <div class="card p-3 mt-3 border-0 text-white rounded-3 text-center shadow" style="background: #008d4c;">
        <span class="small text-uppercase fw-semibold" style="opacity: 0.9;">Saldo Kas Akhir Periode</span>
        <h3 class="fw-bold text-white mb-0">Rp <?= number_format($saldo_akhir, 0, ',', '.') ?></h3>
    </div>
</div>
