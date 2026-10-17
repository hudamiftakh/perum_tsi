<?php
$cur_bulan = (int)date('m');
$cur_tahun = (int)date('Y');
$periode_cur = sprintf('%04d-%02d', $cur_tahun, $cur_bulan);
$nama_bulan_arr = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$nama_bulan_ini = $nama_bulan_arr[$cur_bulan] ?? '';

// Ambil ringkasan kas
$pemasukan_bulan_ini = $this->db->query("
    SELECT COALESCE(SUM(jumlah_bayar), 0) as total
    FROM master_pembayaran
    WHERE status = 'verified'
    AND DATE_FORMAT((CASE WHEN tanggal_bayar IS NOT NULL AND tanggal_bayar > '2000-01-01' THEN tanggal_bayar ELSE DATE(created_at) END), '%Y-%m') = ?
", [$periode_cur])->row_array()['total'] ?? 0;

$pengeluaran_bulan_ini = $this->db->query("
    SELECT COALESCE(SUM(nominal), 0) as total
    FROM t_pengeluaran
    WHERE active_status = 1
    AND DATE_FORMAT(tanggal, '%Y-%m') = ?
", [$periode_cur])->row_array()['total'] ?? 0;

// Total saldo akumulatif
$total_all_debit = $this->db->query("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM master_pembayaran WHERE status = 'verified'")->row_array()['total'] ?? 0;
$total_all_kredit = $this->db->query("SELECT COALESCE(SUM(nominal), 0) as total FROM t_pengeluaran WHERE active_status = 1")->row_array()['total'] ?? 0;
$saldo_sekarang = (float)$total_all_debit - (float)$total_all_kredit;

// Ambil data status sampah bulan berjalan
$all_rumah = $this->db->query("
    SELECT r.id, r.alamat, r.nama, k.nama as koordinator
    FROM master_rumah r
    LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
    ORDER BY r.alamat ASC
")->result_array();

$pembayaran_bulan = $this->db->query("
    SELECT u.id_rumah
    FROM master_pembayaran p
    LEFT JOIN master_users u ON p.user_id = u.id
    WHERE p.status IN ('verified', 'pending')
    AND (
        DATE_FORMAT(p.untuk_bulan, '%Y-%m') = ?
        OR FIND_IN_SET(?, p.bulan_rapel) > 0
        OR (
            DATE_FORMAT(p.bulan_mulai, '%Y-%m') = ?
            AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
            AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
        )
    )
", [$periode_cur, $periode_cur, $periode_cur])->result_array();

$lunas_map = [];
foreach ($pembayaran_bulan as $p) {
    if (!empty($p['id_rumah'])) $lunas_map[$p['id_rumah']] = true;
}

$status_sampah_raw = $this->db->query("
    SELECT id_rumah, status_sampah, keterangan
    FROM status_sampah_rumah
    WHERE periode_bulan = ?
", [$periode_cur])->result_array();

$sampah_override_map = [];
foreach ($status_sampah_raw as $s) {
    $sampah_override_map[$s['id_rumah']] = $s;
}

$total_ambil = 0;
$total_tahan = 0;
$list_tahan = [];

foreach ($all_rumah as $r) {
    $idr = (int)$r['id'];
    $is_lunas = isset($lunas_map[$idr]);
    $override = $sampah_override_map[$idr] ?? null;

    if ($override) {
        $st = $override['status_sampah'];
        $ket = $override['keterangan'];
    } else {
        $st = $is_lunas ? 'ambil' : 'tahan';
        $ket = $is_lunas ? 'Lunas IPL' : 'Menunggak IPL';
    }

    if ($st === 'ambil') {
        $total_ambil++;
    } else {
        $total_tahan++;
        $list_tahan[] = [
            'id'          => $idr,
            'alamat'      => $r['alamat'],
            'nama'        => $r['nama'] ?: 'Warga',
            'koordinator' => $r['koordinator'] ?: '-',
            'ket'         => $ket
        ];
    }
}
$total_rumah = count($all_rumah);
$persen_ambil = ($total_rumah > 0) ? round(($total_ambil / $total_rumah) * 100) : 0;
?>

<div class="container-fluid px-0">
    <!-- Hero Banner Koordinator Lingkungan -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" style="background: linear-gradient(135deg, #008d4c 0%, #059669 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-12 col-md-8">
                    <span class="badge bg-white text-success fw-bold px-3 py-1 rounded-pill mb-2 shadow-xs">
                        <i class="ti ti-shield-check me-1"></i> PANEL KOORDINATOR LINGKUNGAN TSI
                    </span>
                    <h2 class="fw-bold text-white mb-1">
                        Selamat Datang, <?= htmlspecialchars($_SESSION['username']['nama'] ?? 'Koordinator Lingkungan') ?>!
                    </h2>
                    <p class="text-white text-opacity-90 mb-3 small">
                        Pusat kendali operasional pengangkutan sampah lingkungan pos satpam &amp; pencatatan arus kas (debit-kredit) paguyuban untuk periode <strong><?= $nama_bulan_ini ?> <?= $cur_tahun ?></strong>.
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('siteplan-sampah') ?>" class="btn btn-warning fw-bold text-dark rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                            <i class="ti ti-map-pin"></i> Buka Siteplan Sampah
                        </a>
                        <a href="<?= base_url('siteplan-sampah/pdf?tahun=' . $cur_tahun . '&bulan=' . $cur_bulan) ?>" target="_blank" class="btn btn-danger fw-bold rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                            <i class="ti ti-printer"></i> Cetak Landscape Pos Satpam
                        </a>
                        <a href="<?= base_url('keuangan/buku-kas') ?>" class="btn btn-light fw-bold text-success rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1">
                            <i class="ti ti-wallet"></i> Buku Kas
                        </a>
                    </div>
                </div>
                <div class="col-12 col-md-4 text-center d-none d-md-block">
                    <div class="p-3 bg-white bg-opacity-10 rounded-4 border border-white border-opacity-25 d-inline-block text-center">
                        <i class="ti ti-trash fs-8 text-white mb-2 d-block"></i>
                        <span class="fw-bold d-block fs-3"><?= $total_ambil ?> / <?= $total_rumah ?> Rumah</span>
                        <small class="text-white text-opacity-80">Boleh Diambil Sampahnya</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrik Cepat -->
    <div class="row g-3 mb-4">
        <!-- Saldo Kas -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-bold small text-uppercase">Saldo Kas Paguyuban</span>
                    <span class="p-2 rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="ti ti-wallet fs-5"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-1">Rp <?= number_format($saldo_sekarang, 0, ',', '.') ?></h3>
                <small class="text-muted">Total saldo kas berjalan</small>
            </div>
        </div>

        <!-- Pemasukan Bulan Ini (Debit) -->
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-success fw-bold small text-uppercase">Debit (IPL Masuk)</span>
                    <span class="badge bg-success-subtle text-success rounded-pill">+<?= $nama_bulan_ini ?></span>
                </div>
                <h3 class="fw-bold text-success mb-1">Rp <?= number_format($pemasukan_bulan_ini, 0, ',', '.') ?></h3>
                <small class="text-muted">Pemasukan verified bulan ini</small>
            </div>
        </div>

        <!-- Pengeluaran Bulan Ini (Kredit) -->
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-danger fw-bold small text-uppercase">Kredit (Pengeluaran)</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill">-<?= $nama_bulan_ini ?></span>
                </div>
                <h3 class="fw-bold text-danger mb-1">Rp <?= number_format($pengeluaran_bulan_ini, 0, ',', '.') ?></h3>
                <small class="text-muted">Operasional &amp; sampah bulan ini</small>
            </div>
        </div>
    </div>

    <!-- Daftar Rumah Ditahan (Blacklist Sampah) Bulan Ini -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-danger">
                    <i class="ti ti-alert-triangle me-1"></i> Daftar Rumah Ditahan Sampahnya (<?= $nama_bulan_ini ?> <?= $cur_tahun ?>)
                </h5>
                <small class="text-muted">Rumah-rumah berikut <strong>JANGAN DIAMBIL</strong> sampahnya oleh petugas</small>
            </div>
            <div class="d-flex gap-2">
                <span class="badge bg-danger rounded-pill px-3 py-2 fw-bold"><?= $total_tahan ?> Unit Rumah</span>
                <a href="<?= base_url('siteplan-sampah') ?>" class="btn btn-sm btn-outline-success rounded-pill fw-semibold">
                    <i class="ti ti-adjustments me-1"></i> Sesuaikan di Peta
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($list_tahan)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-circle-check fs-9 text-success mb-2 d-block"></i>
                    <h5 class="fw-bold text-success mb-1">Luar Biasa!</h5>
                    <p class="mb-0 text-muted">Seluruh rumah warga TSI boleh diambil sampahnya pada bulan ini.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="6%">No</th>
                                <th width="25%">Blok &amp; No. Rumah</th>
                                <th width="25%">Nama Warga</th>
                                <th width="20%">Koordinator Blok</th>
                                <th width="24%">Alasan / Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($list_tahan as $lt): ?>
                                <tr>
                                    <td class="text-center text-muted small"><?= $no++ ?></td>
                                    <td>
                                        <strong class="text-dark fs-3"><?= htmlspecialchars($lt['alamat']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($lt['nama']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($lt['koordinator']) ?></span></td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                            <?= htmlspecialchars($lt['ket']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-light py-2 text-center">
            <small class="text-muted">Untuk mengubah status rumah menjadi boleh diambil (dispensasi), silakan buka menu <a href="<?= base_url('siteplan-sampah') ?>" class="fw-bold text-success">Siteplan Sampah</a>.</small>
        </div>
    </div>
</div>
