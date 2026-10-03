<?php
defined('BASEPATH') or exit('No direct script access allowed');

// ==========================================
// 1. FILTER TANGGAL & PERIODE
// ==========================================
$start_date = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

$bulan_ini      = date('m');
$tahun_ini      = date('Y');
$bulan_str      = date('Y-m');
$nama_bulan_arr = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
$nama_bulan_ini = $nama_bulan_arr[date('m')];

// Identifikasi Sesi & Role Pengguna (Admin / Bendahara)
$user_session = $_SESSION['username'] ?? [];
$user_role    = $user_session['role'] ?? 'admin';
$user_nama    = $user_session['nama'] ?? 'Administrator';

// Saldo Kas Kumulatif & Pengeluaran Kas
$keluar_bulan_ini = 0;
if ($this->db->table_exists('t_pengeluaran')) {
    $q_keluar = $this->db->query("
        SELECT COALESCE(SUM(nominal), 0) as total, COUNT(*) as transaksi
        FROM t_pengeluaran
        WHERE active_status = 1
        AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
    ", [$bulan_ini, $tahun_ini])->row();
    $keluar_bulan_ini = (float)($q_keluar->total ?? 0);
}

$pemasukan_all = (float)($this->db->query("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM master_pembayaran WHERE status = 'verified'")->row()->total ?? 0);
$pengeluaran_all = 0;
if ($this->db->table_exists('t_pengeluaran')) {
    $pengeluaran_all = (float)($this->db->query("SELECT COALESCE(SUM(nominal), 0) as total FROM t_pengeluaran WHERE active_status = 1")->row()->total ?? 0);
}
$saldo_kas_aktif = $pemasukan_all - $pengeluaran_all;

// ==========================================
// 2. QUERY METRIK & KPI UTAMA
// ==========================================

// 2.1 Total Kas Masuk Periode Filter (Verified)
$q_penerimaan_filter = $this->db->query("
    SELECT COALESCE(SUM(jumlah_bayar), 0) as total, COUNT(*) as transaksi
    FROM master_pembayaran
    WHERE status = 'verified'
    AND tanggal_bayar >= ? AND tanggal_bayar <= ?
", [$start_date, $end_date])->row();
$kas_masuk_periode = (float)($q_penerimaan_filter->total ?? 0);
$tx_masuk_periode  = (int)($q_penerimaan_filter->transaksi ?? 0);

// 2.2 Total Kas Masuk Bulan Ini (Verified)
$q_penerimaan_bln = $this->db->query("
    SELECT COALESCE(SUM(jumlah_bayar), 0) as total, COUNT(*) as transaksi
    FROM master_pembayaran
    WHERE status = 'verified'
    AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?
", [$bulan_ini, $tahun_ini])->row();
$kas_masuk_bln = (float)($q_penerimaan_bln->total ?? 0);
$tx_masuk_bln  = (int)($q_penerimaan_bln->transaksi ?? 0);

// 2.3 Antrean Pending Verifikasi
$q_pending = $this->db->query("
    SELECT COUNT(*) as total, COALESCE(SUM(jumlah_bayar), 0) as nominal
    FROM master_pembayaran
    WHERE status = 'pending'
")->row();
$jml_pending = (int)($q_pending->total ?? 0);
$nom_pending = (float)($q_pending->nominal ?? 0);

// 2.4 Total Unit Rumah & Kepatuhan Lunas Bulan Berjalan
$total_rumah = (int)$this->db->count_all('master_rumah');

// Query rumah yang lunas bulan ini
$rumah_bayar_bln = (int)($this->db->query("
    SELECT COUNT(DISTINCT r.id) as total
    FROM master_pembayaran p
    JOIN master_users u ON u.id = p.user_id
    JOIN master_rumah r ON r.id = u.id_rumah
    WHERE p.status = 'verified'
    AND (
        DATE_FORMAT(p.untuk_bulan, '%Y-%m') = '$bulan_str'
        OR FIND_IN_SET('$bulan_str', p.bulan_rapel) > 0
        OR (
            DATE_FORMAT(p.bulan_mulai, '%Y-%m') = '$bulan_str'
            AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
            AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
        )
        OR (MONTH(p.tanggal_bayar) = '$bulan_ini' AND YEAR(p.tanggal_bayar) = '$tahun_ini')
    )
")->row()->total ?? 0);

$rumah_nunggak_bln = max(0, $total_rumah - $rumah_bayar_bln);
$persen_kepatuhan  = ($total_rumah > 0) ? round(($rumah_bayar_bln / $total_rumah) * 100, 1) : 0;

// Total Warga / Keluarga
$total_keluarga = (int)$this->db->count_all('master_keluarga');
$total_koor     = (int)$this->db->count_all('master_koordinator_blok');

// ==========================================
// 3. TREN KAS 12 BULAN TERAKHIR (APEXCHARTS)
// ==========================================
$tren_12bln = [];
for ($i = 11; $i >= 0; $i--) {
    $ts      = strtotime("-$i months");
    $m_num   = date('n', $ts);
    $y_num   = date('Y', $ts);
    $m_label = date('M Y', $ts);
    $m_key   = date('Y-m', $ts);

    $row_m = $this->db->query("
        SELECT COALESCE(SUM(jumlah_bayar), 0) as total, COUNT(*) as tx
        FROM master_pembayaran
        WHERE status = 'verified'
        AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?
    ", [$m_num, $y_num])->row();

    $tren_12bln[] = [
        'key'       => $m_key,
        'label'     => $m_label,
        'total'     => (float)($row_m->total ?? 0),
        'transaksi' => (int)($row_m->tx ?? 0)
    ];
}

// ==========================================
// 4. KINERJA SETORAN PER KOORDINATOR BLOK
// ==========================================
$koor_perf = $this->db->query("
    SELECT k.id, k.nama, k.no_hp,
           COUNT(DISTINCT r.id) as jml_rumah,
           COALESCE(COUNT(DISTINCT CASE WHEN p.id IS NOT NULL THEN r.id END), 0) as sudah_bayar,
           COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN p.jumlah_bayar ELSE 0 END), 0) as nominal_terkumpul
    FROM master_koordinator_blok k
    LEFT JOIN master_rumah r ON r.id_koordinator = k.id
    LEFT JOIN (
        SELECT p.id, u.id_rumah, p.jumlah_bayar
        FROM master_pembayaran p
        JOIN master_users u ON u.id = p.user_id
        WHERE p.status = 'verified'
        AND (
            DATE_FORMAT(p.untuk_bulan, '%Y-%m') = '$bulan_str'
            OR FIND_IN_SET('$bulan_str', p.bulan_rapel) > 0
            OR (
                DATE_FORMAT(p.bulan_mulai, '%Y-%m') = '$bulan_str'
                AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
                AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
            )
            OR (MONTH(p.tanggal_bayar) = '$bulan_ini' AND YEAR(p.tanggal_bayar) = '$tahun_ini')
        )
    ) p ON p.id_rumah = r.id
    WHERE r.id IS NOT NULL
    GROUP BY k.id, k.nama, k.no_hp
    ORDER BY sudah_bayar DESC, k.nama ASC
")->result_array();

// ==========================================
// 5. TRANSAKSI VERIFIED TERBARU (10 DATA)
// ==========================================
$transaksi_terbaru = $this->db->query("
    SELECT p.id, p.jumlah_bayar, p.pembayaran_via, p.tanggal_bayar, p.created_at, p.status,
           u.nama as warga_nama, r.alamat as warga_alamat, k.nama as koordinator_nama
    FROM master_pembayaran p
    LEFT JOIN master_users u ON u.id = p.user_id
    LEFT JOIN master_rumah r ON r.id = u.id_rumah
    LEFT JOIN master_koordinator_blok k ON k.id = r.id_koordinator
    WHERE p.status = 'verified'
    ORDER BY p.tanggal_bayar DESC, p.id DESC
    LIMIT 10
")->result_array();

// ==========================================
// 6. DETAIL DATA UNTUK MODAL INTERAKTIF
// ==========================================

// 6.1 Daftar Seluruh Rumah & Status Kepatuhan Bulan Ini
$raw_rumah_status = $this->db->query("
    SELECT r.id as id_rumah, r.alamat as nomor_rumah, r.nama as pemilik_rumah,
           k.nama as koordinator_nama, k.id as koordinator_id,
           COALESCE(MAX(kl.no_hp), '') as no_hp,
           CASE 
               WHEN MAX(CASE WHEN p.status = 'verified' THEN 1 ELSE 0 END) = 1 THEN 'lunas'
               WHEN MAX(CASE WHEN p.status = 'pending' THEN 1 ELSE 0 END) = 1 THEN 'pending'
               ELSE 'nunggak'
           END as status_bayar,
           COALESCE(MAX(CASE WHEN p.status = 'verified' THEN p.jumlah_bayar ELSE NULL END), 0) as nominal_bayar,
           COALESCE(MAX(CASE WHEN p.status = 'verified' THEN p.tanggal_bayar ELSE NULL END), '') as tanggal_bayar,
           COALESCE(MAX(CASE WHEN p.status = 'verified' THEN p.pembayaran_via ELSE NULL END), '-') as via
    FROM master_rumah r
    LEFT JOIN master_koordinator_blok k ON k.id = r.id_koordinator
    LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
    LEFT JOIN master_users u ON u.id_rumah = r.id
    LEFT JOIN master_pembayaran p ON p.user_id = u.id AND (
        DATE_FORMAT(p.untuk_bulan, '%Y-%m') = '$bulan_str'
        OR FIND_IN_SET('$bulan_str', p.bulan_rapel) > 0
        OR (
            DATE_FORMAT(p.bulan_mulai, '%Y-%m') = '$bulan_str'
            AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
            AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
        )
        OR (MONTH(p.tanggal_bayar) = '$bulan_ini' AND YEAR(p.tanggal_bayar) = '$tahun_ini')
    )
    GROUP BY r.id, r.alamat, r.nama, k.nama, k.id
    ORDER BY r.alamat ASC
")->result_array();

// 6.2 Daftar Antrean Pending untuk Modal
$raw_pending_list = $this->db->query("
    SELECT p.id, p.jumlah_bayar, p.pembayaran_via, p.tanggal_bayar, p.created_at, p.bulan_mulai,
           u.nama as warga_nama, r.alamat as warga_alamat, k.nama as koordinator_nama,
           COALESCE(kl.no_hp, '') as no_hp
    FROM master_pembayaran p
    LEFT JOIN master_users u ON u.id = p.user_id
    LEFT JOIN master_rumah r ON r.id = u.id_rumah
    LEFT JOIN master_koordinator_blok k ON k.id = r.id_koordinator
    LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
    WHERE p.status = 'pending'
    ORDER BY p.id DESC
")->result_array();

// 6.3 Daftar Pembayaran Verified Periode Filter
$raw_verified_list = $this->db->query("
    SELECT p.id, p.jumlah_bayar, p.pembayaran_via, p.tanggal_bayar, p.created_at,
           DATE_FORMAT(p.tanggal_bayar, '%Y-%m') as bulan_key,
           u.nama as warga_nama, r.alamat as warga_alamat, k.nama as koordinator_nama
    FROM master_pembayaran p
    LEFT JOIN master_users u ON u.id = p.user_id
    LEFT JOIN master_rumah r ON r.id = u.id_rumah
    LEFT JOIN master_koordinator_blok k ON k.id = r.id_koordinator
    WHERE p.status = 'verified'
    AND p.tanggal_bayar >= ? AND p.tanggal_bayar <= ?
    ORDER BY p.tanggal_bayar DESC, p.id DESC
    LIMIT 300
", [$start_date, $end_date])->result_array();
?>

<!-- ==========================================
     CSS CUSTOM STYLING - EXECUTIVE DASHBOARD
========================================== -->
<style>
    :root {
        --kpi-border: 1.5px solid #cbd5e1;
        --kpi-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        --kpi-radius: 16px;
    }

    .dashboard-container {
        font-family: inherit;
    }

    /* Outlined Professional Card */
    .card-pro {
        border: var(--kpi-border) !important;
        border-radius: var(--kpi-radius) !important;
        background: #ffffff;
        box-shadow: var(--kpi-shadow);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .card-pro:hover {
        border-color: #94a3b8 !important;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.09);
    }

    /* Clickable KPI Card */
    .kpi-card-interactive {
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .kpi-card-interactive:hover {
        transform: translateY(-3px);
        border-color: #10b981 !important;
        box-shadow: 0 10px 25px rgba(16, 185, 129, 0.14) !important;
    }

    .kpi-card-interactive .kpi-click-hint {
        opacity: 0;
        transform: translateY(4px);
        transition: all 0.2s ease;
        font-size: 0.72rem;
    }

    .kpi-card-interactive:hover .kpi-click-hint {
        opacity: 1;
        transform: translateY(0);
    }

    /* Soft Icon Badges */
    .kpi-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    .kpi-card-interactive:hover .kpi-icon-box {
        transform: scale(1.08);
    }

    /* Filter Bar Soft */
    .filter-bar-pro {
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 20px;
    }

    /* Soft Modal Header */
    .modal-header-soft {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border-bottom: 1.5px solid #a7f3d0;
        padding: 18px 24px;
    }

    /* Badges */
    .badge-soft-success {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .badge-soft-warning {
        background-color: #fef3c7;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .badge-soft-info {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }

    /* Pulse animation for pending */
    @keyframes pulse-ring {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }
    .pulse-warning {
        animation: pulse-ring 2s infinite ease-in-out;
    }

    /* Quick Action Buttons */
    .btn-quick-action {
        border: 1.5px solid #e2e8f0;
        background: #ffffff;
        border-radius: 12px;
        padding: 10px 14px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #1e293b;
        transition: all 0.2s ease;
        text-decoration: none !important;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-quick-action:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #0f172a;
        transform: translateY(-1px);
    }
</style>

<div class="dashboard-container mb-5">

    <!-- ==========================================
         HERO BANNER - EXECUTIVE DASHBOARD
    ========================================== -->
    <div class="card border-0 mb-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #065f46 0%, #047857 60%, #059669 100%); border-radius: 20px; box-shadow: 0 10px 25px rgba(4, 120, 87, 0.25);">
        <div class="card-body p-4 position-relative" style="z-index: 2;">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <?php if ($user_role === 'bendahara'): ?>
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px);">
                            <i class="bi bi-wallet2 text-warning"></i>
                            <span class="small fw-semibold text-white">Dashboard Keuangan &amp; Kas &bull; Bendahara Paguyuban TSI</span>
                        </div>
                        <h2 class="fw-bold text-white mb-2" style="letter-spacing: -0.5px;">
                            Selamat Datang, <?= htmlspecialchars($user_nama) ?>! 💼
                        </h2>
                        <p class="text-white-50 mb-0" style="max-width: 650px; font-size: 0.95rem;">
                            Kelola verifikasi pembayaran iuran, pantau arus kas kas paguyuban, dan monitor setoran koordinator blok secara real-time.
                        </p>
                    <?php else: ?>
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px);">
                            <i class="bi bi-shield-check text-warning"></i>
                            <span class="small fw-semibold text-white">Dashboard Eksekutif &bull; Administrator Paguyuban TSI</span>
                        </div>
                        <h2 class="fw-bold text-white mb-2" style="letter-spacing: -0.5px;">
                            Statistik &amp; Monitoring IPL Perumahan TSI
                        </h2>
                        <p class="text-white-50 mb-0" style="max-width: 650px; font-size: 0.95rem;">
                            Pantau penerimaan iuran, tingkat kepatuhan warga secara real-time, verifikasi antrean setoran, dan lakukan drilldown detail data hanya dengan satu klik.
                        </p>
                    <?php endif; ?>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="d-inline-flex flex-column align-items-lg-end gap-2">
                        <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill shadow-sm fw-bold">
                            <i class="bi bi-calendar3 me-1 text-success"></i> <?= $nama_bulan_ini ?> <?= $tahun_ini ?>
                        </span>
                        <?php if ($user_role === 'bendahara' || $saldo_kas_aktif > 0): ?>
                            <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 px-3 py-1 rounded-pill small">
                                <i class="bi bi-cash-stack me-1 text-warning"></i> Saldo Kas Riil: <strong>Rp <?= number_format($saldo_kas_aktif, 0, ',', '.') ?></strong>
                            </span>
                        <?php endif; ?>
                        <div class="small text-white-50">
                            Terakhir disinkronkan: <?= date('d M Y, H:i') ?> WIB
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Toolbar -->
            <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top border-white border-opacity-10">
                <a href="<?= base_url('pembayaran/verifikasi-pembayaran'); ?>" class="btn-quick-action position-relative">
                    <i class="bi bi-patch-check-fill text-success"></i>
                    <span>Verifikasi Pembayaran</span>
                    <?php if ($jml_pending > 0): ?>
                        <span class="badge bg-danger rounded-pill px-2 py-1"><?= $jml_pending ?></span>
                    <?php endif; ?>
                </a>
                <?php if ($user_role === 'bendahara'): ?>
                    <a href="<?= base_url('kas/pengeluaran'); ?>" class="btn-quick-action">
                        <i class="bi bi-file-earmark-plus-fill text-danger"></i>
                        <span>Catat Pengeluaran</span>
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('laporan-rekap-rapel'); ?>" class="btn-quick-action">
                    <i class="bi bi-file-earmark-spreadsheet-fill text-primary"></i>
                    <span>Rekap &amp; Rapel IPL</span>
                </a>
                <a href="<?= base_url('log-verifikasi'); ?>" class="btn-quick-action">
                    <i class="bi bi-journal-text text-info"></i>
                    <span>Log Verifikasi &amp; WA</span>
                </a>
                <a href="<?= base_url('dashboard/warga'); ?>" class="btn-quick-action">
                    <i class="bi bi-people-fill text-secondary"></i>
                    <span>Data Warga &amp; Rumah</span>
                </a>
                <button type="button" class="btn-quick-action ms-auto" onclick="location.reload();">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
        <i class="bi bi-graph-up-arrow position-absolute" style="right: -20px; bottom: -30px; font-size: 11rem; opacity: 0.08;"></i>
    </div>

    <!-- Alert Notifikasi Jika Ada Antrean Pending -->
    <?php if ($jml_pending > 0): ?>
        <div class="alert border-0 card-pro p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2 pulse-warning" 
             style="background: #fffbeb; border: 1.5px solid #fde68a !important;">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning text-white" style="width:44px;height:44px;font-size:1.3rem;flex-shrink:0">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div>
                    <strong class="text-dark d-block">Perhatian: Ada <?= $jml_pending ?> Transaksi Pembayaran Menunggu Verifikasi!</strong>
                    <small class="text-muted">Total nominal tertunda: <strong>Rp <?= number_format($nom_pending, 0, ',', '.') ?></strong>. Segera tinjau bukti transfer untuk memperbarui status warga.</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold" onclick="showDashboardDetail('antrean_pending')">
                    <i class="bi bi-eye me-1"></i> Intip Data
                </button>
                <a href="<?= base_url('pembayaran/verifikasi-pembayaran'); ?>" class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm fw-bold">
                    <i class="bi bi-check2-circle me-1"></i> Buka Verifikasi &rarr;
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ==========================================
         FILTER BAR PERIODE & PENCARIAN
    ========================================== -->
    <div class="filter-bar-pro mb-4">
        <form method="get" id="dashboardFilterForm" class="row g-2 align-items-center mb-0">
            <div class="col-12 col-md-auto d-flex align-items-center gap-2">
                <i class="bi bi-funnel-fill text-success fs-5"></i>
                <span class="fw-bold text-dark">Filter Periode:</span>
            </div>
            <div class="col-6 col-md-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-1 text-muted"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" name="start_date" id="filter_start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>" required>
                </div>
            </div>
            <div class="col-auto text-muted small fw-semibold text-center">s/d</div>
            <div class="col-6 col-md-auto">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-1 text-muted"><i class="bi bi-calendar-check"></i></span>
                    <input type="date" name="end_date" id="filter_end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>" required>
                </div>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success px-3 fw-bold rounded-pill">
                    <i class="bi bi-search me-1"></i> Terapkan
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="setFilterQuick('bulan_ini')">Bulan Ini</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="setFilterQuick('bulan_lalu')">Bulan Lalu</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2" onclick="setFilterQuick('tahun_ini')">Tahun Ini</button>
            </div>
            <div class="col-12 col-md text-md-end text-muted small mt-2 mt-md-0">
                <span class="badge bg-light text-dark border">
                    Rentang: <?= date('d M Y', strtotime($start_date)) ?> - <?= date('d M Y', strtotime($end_date)) ?>
                </span>
            </div>
        </form>
    </div>

    <!-- ==========================================
         5 EXECUTIVE KPI CARDS (INTERACTIVE DRILLDOWN)
    ========================================== -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Kas Masuk Periode Ini -->
        <div class="col-12 col-sm-6 col-xl">
            <div class="card-pro p-3 h-100 kpi-card-interactive" onclick="showDashboardDetail('penerimaan_ipl')" title="Klik untuk rincian data penerimaan">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Kas Masuk Periode</span>
                    <span class="badge badge-soft-success rounded-pill px-2 py-1 small">
                        <i class="bi bi-check-circle me-1"></i>Verified
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold text-dark mb-0 text-truncate">Rp <?= number_format($kas_masuk_periode, 0, ',', '.') ?></h4>
                        <small class="text-muted d-block text-truncate"><?= $tx_masuk_periode ?> transaksi disetujui</small>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                    <small class="text-muted" style="font-size:0.75rem;">Bulan ini: Rp <?= number_format($kas_masuk_bln, 0, ',', '.') ?><?= $saldo_kas_aktif > 0 ? ' &bull; Saldo: Rp ' . number_format($saldo_kas_aktif, 0, ',', '.') : '' ?></small>
                    <span class="text-success fw-semibold kpi-click-hint">Lihat Rincian &rarr;</span>
                </div>
            </div>
        </div>

        <!-- 2. Tingkat Kepatuhan Lunas -->
        <div class="col-12 col-sm-6 col-xl">
            <div class="card-pro p-3 h-100 kpi-card-interactive" onclick="showDashboardDetail('warga_lunas')" title="Klik untuk daftar rumah yang lunas">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Kepatuhan Lunas</span>
                    <span class="badge badge-soft-info rounded-pill px-2 py-1 small">
                        <?= $nama_bulan_ini ?>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-0"><?= $persen_kepatuhan ?>%</h4>
                        <small class="text-muted d-block"><?= $rumah_bayar_bln ?> dari <?= $total_rumah ?> unit rumah</small>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                    <div class="progress flex-grow-1 me-2" style="height: 6px; border-radius: 4px;">
                        <div class="progress-bar bg-info" style="width: <?= min(100, $persen_kepatuhan) ?>%"></div>
                    </div>
                    <span class="text-info fw-semibold kpi-click-hint">Daftar Lunas &rarr;</span>
                </div>
            </div>
        </div>

        <!-- 3. Warga Menunggak -->
        <div class="col-12 col-sm-6 col-xl">
            <div class="card-pro p-3 h-100 kpi-card-interactive" onclick="showDashboardDetail('warga_menunggak')" title="Klik untuk rincian rumah yang belum bayar">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Belum Bayar (<?= $nama_bulan_ini ?>)</span>
                    <span class="badge badge-soft-danger rounded-pill px-2 py-1 small">
                        Tunggakan
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-danger mb-0"><?= $rumah_nunggak_bln ?> <small class="fs-6 text-muted">Rumah</small></h4>
                        <small class="text-muted d-block">Perlu tindak lanjut / teguran</small>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                    <small class="text-danger fw-semibold" style="font-size:0.75rem;"><i class="bi bi-whatsapp me-1"></i>Reminder WA</small>
                    <span class="text-danger fw-semibold kpi-click-hint">Lihat Daftar &rarr;</span>
                </div>
            </div>
        </div>

        <!-- 4. Antrean Pending Verifikasi -->
        <div class="col-12 col-sm-6 col-xl">
            <div class="card-pro p-3 h-100 kpi-card-interactive" onclick="showDashboardDetail('antrean_pending')" title="Klik untuk melihat antrean verifikasi">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Antrean Validasi</span>
                    <span class="badge <?= $jml_pending > 0 ? 'badge-soft-warning text-dark fw-bold' : 'badge-soft-success' ?> rounded-pill px-2 py-1 small">
                        <?= $jml_pending > 0 ? $jml_pending . ' Menunggu' : 'Bersih' ?>
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div class="overflow-hidden">
                        <h4 class="fw-bold text-dark mb-0 text-truncate"><?= $jml_pending ?> <small class="fs-6 text-muted">Bukti</small></h4>
                        <small class="text-muted d-block text-truncate">Rp <?= number_format($nom_pending, 0, ',', '.') ?></small>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                    <small class="text-muted" style="font-size:0.75rem;">Konfirmasi transfer</small>
                    <span class="text-warning fw-semibold kpi-click-hint">Periksa &rarr;</span>
                </div>
            </div>
        </div>

        <!-- 5. Total Rumah & Koordinator Blok -->
        <div class="col-12 col-sm-6 col-xl">
            <div class="card-pro p-3 h-100 kpi-card-interactive" onclick="showDashboardDetail('total_rumah')" title="Klik untuk melihat seluruh master rumah">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Master Rumah</span>
                    <span class="badge badge-soft-info rounded-pill px-2 py-1 small">
                        <?= $total_koor ?> Blok
                    </span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-houses-fill"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold text-dark mb-0"><?= $total_rumah ?> <small class="fs-6 text-muted">Unit</small></h4>
                        <small class="text-muted d-block"><?= $total_keluarga ?> KK terdata</small>
                    </div>
                </div>
                <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top">
                    <small class="text-muted" style="font-size:0.75rem;">Seluruh Perum TSI</small>
                    <span class="text-primary fw-semibold kpi-click-hint">Lihat Master &rarr;</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         BAGIAN CHART UTAMA (APEXCHARTS)
    ========================================== -->
    <div class="row g-4 mb-4">
        <!-- Chart 1: Tren Penerimaan Kas IPL (Area Chart Spline) -->
        <div class="col-12 col-xl-8">
            <div class="card-pro p-4 h-100">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-graph-up text-success me-2"></i>Tren Penerimaan Kas IPL (12 Bulan)
                        </h5>
                        <p class="text-muted small mb-0">Klik pada titik bulan mana pun pada grafik untuk melihat rincian transaksi bulan tersebut.</p>
                    </div>
                    <span class="badge badge-soft-success px-3 py-2 rounded-pill small">
                        <i class="bi bi-hand-index-thumb me-1"></i>Interaktif Klik Drilldown
                    </span>
                </div>
                <!-- Container ApexChart Tren -->
                <div id="chartTrenKas" style="min-height: 330px;"></div>
            </div>
        </div>

        <!-- Chart 2: Komposisi Kepatuhan Pembayaran Bulan Ini (Donut Chart) -->
        <div class="col-12 col-xl-4">
            <div class="card-pro p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-pie-chart text-primary me-2"></i>Status Kepatuhan
                        </h5>
                        <p class="text-muted small mb-0">Bulan <?= $nama_bulan_ini ?> <?= $tahun_ini ?></p>
                    </div>
                    <span class="badge bg-light text-dark border small rounded-pill">Total <?= $total_rumah ?> Rumah</span>
                </div>
                <!-- Container ApexChart Donut -->
                <div id="chartDonutKepatuhan" style="min-height: 250px;"></div>
                
                <!-- Quick Legend Drilldown -->
                <div class="row g-2 mt-3 pt-3 border-top text-center">
                    <div class="col-4">
                        <a href="javascript:void(0)" onclick="showDashboardDetail('warga_lunas')" class="text-decoration-none">
                            <div class="p-2 rounded-3 bg-light border">
                                <span class="badge badge-soft-success rounded-pill px-2 mb-1 d-inline-block">Lunas</span>
                                <h6 class="fw-bold text-dark mb-0"><?= $rumah_bayar_bln ?></h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="javascript:void(0)" onclick="showDashboardDetail('antrean_pending')" class="text-decoration-none">
                            <div class="p-2 rounded-3 bg-light border">
                                <span class="badge badge-soft-warning rounded-pill px-2 mb-1 d-inline-block">Pending</span>
                                <h6 class="fw-bold text-dark mb-0"><?= $jml_pending ?></h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-4">
                        <a href="javascript:void(0)" onclick="showDashboardDetail('warga_menunggak')" class="text-decoration-none">
                            <div class="p-2 rounded-3 bg-light border">
                                <span class="badge badge-soft-danger rounded-pill px-2 mb-1 d-inline-block">Nunggak</span>
                                <h6 class="fw-bold text-danger mb-0"><?= $rumah_nunggak_bln ?></h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================
         BAGIAN KEDUA: PROGRESS KOORDINATOR & TRANSAKSI TERBARU
    ========================================== -->
    <div class="row g-4 mb-4">
        <!-- Chart 3: Capaian & Kinerja Setoran per Koordinator Blok (Horizontal Bar) -->
        <div class="col-12 col-xl-7">
            <div class="card-pro p-4 h-100">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-bar-chart-steps text-info me-2"></i>Kinerja Setoran Koordinator Blok (<?= $nama_bulan_ini ?>)
                        </h5>
                        <p class="text-muted small mb-0">Klik pada baris koordinator untuk membuka daftar rumah binaannya beserta status iurannya.</p>
                    </div>
                    <a href="<?= base_url('log-koordinator'); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        Log Koordinator &rarr;
                    </a>
                </div>
                <!-- Container ApexChart Koordinator -->
                <div id="chartKoorProgress" style="min-height: 320px;"></div>
            </div>
        </div>

        <!-- Tabel Transaksi Verified Terbaru -->
        <div class="col-12 col-xl-5">
            <div class="card-pro p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            <i class="bi bi-clock-history text-success me-2"></i>Transaksi Terbaru
                        </h5>
                        <p class="text-muted small mb-0">10 Penerimaan Terakhir yang Disetujui</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="showDashboardDetail('penerimaan_ipl')">
                        Semua &rarr;
                    </button>
                </div>

                <div class="table-responsive" style="max-height: 330px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Warga / Rumah</th>
                                <th>Tanggal</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-center">Via</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transaksi_terbaru)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">Belum ada transaksi verified tercatat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transaksi_terbaru as $tx): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-dark d-block text-truncate" style="max-width: 130px;"><?= htmlspecialchars($tx['warga_nama'] ?? 'Warga') ?></strong>
                                            <small class="text-muted"><?= htmlspecialchars($tx['warga_alamat'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <span class="text-muted small text-nowrap"><?= date('d/m/Y', strtotime($tx['tanggal_bayar'])) ?></span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success text-nowrap">Rp <?= number_format($tx['jumlah_bayar'], 0, ',', '.') ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($tx['pembayaran_via'] == 'koordinator'): ?>
                                                <span class="badge badge-soft-info rounded-pill px-2">Koor</span>
                                            <?php else: ?>
                                                <span class="badge badge-soft-success rounded-pill px-2">Transfer</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ==========================================
     POPUP MODAL DETAIL DASHBOARD (DRILLDOWN)
========================================== -->
<div class="modal fade" id="modalDetailDashboard" tabindex="-1" aria-labelledby="modalDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <!-- Modal Header Soft -->
            <div class="modal-header-soft d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-success text-white shadow-sm" style="width:46px;height:46px;font-size:1.3rem;">
                        <i id="modalHeaderIcon" class="bi bi-list-check"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0" id="modalDetailTitle">Rincian Data Dashboard</h5>
                        <small class="text-muted" id="modalDetailSubtitle">Informasi detail berdasarkan pilihan Anda</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Search & Stat Filter Bar -->
            <div class="bg-light p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 450px;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-1 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="modalSearchInput" class="form-control" placeholder="Cari nama warga, nomor rumah, koordinator, status..." onkeyup="filterModalTable()">
                        <button class="btn btn-outline-secondary" type="button" onclick="$('#modalSearchInput').val(''); filterModalTable();"><i class="bi bi-x"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-white text-dark border px-3 py-2 fw-semibold" id="modalRecordCount">
                        Menampilkan 0 data
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="exportModalToCSV()">
                        <i class="bi bi-file-earmark-excel me-1"></i> Unduh CSV
                    </button>
                </div>
            </div>

            <!-- Modal Body Table -->
            <div class="modal-body p-0">
                <div class="table-responsive" style="min-height: 300px; max-height: 520px;">
                    <table class="table table-hover align-middle mb-0" id="modalTableDetail">
                        <thead class="table-light sticky-top" style="z-index: 10;">
                            <tr id="modalTableHead">
                                <!-- Dynamic Headers -->
                            </tr>
                        </thead>
                        <tbody id="modalTableBody">
                            <!-- Dynamic Rows -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light p-3 d-flex justify-content-between align-items-center">
                <div class="small text-muted" id="modalFooterSummary">
                    Klik tombol aksi jika ingin berinteraksi dengan warga melalui WhatsApp.
                </div>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ==========================================
     DATASETS CLIENT-SIDE (ZERO-LATENCY DRILLDOWN)
========================================== -->
<script>
    // Embedded Data Stores
    window.DASHBOARD_DATA = {
        rumahStatus: <?= json_encode($raw_rumah_status, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        pendingList: <?= json_encode($raw_pending_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        verifiedList: <?= json_encode($raw_verified_list, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        tren12Bln: <?= json_encode($tren_12bln, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        koorPerf: <?= json_encode($koor_perf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        bulanIniName: <?= json_encode($nama_bulan_ini) ?>,
        tahunIni: <?= json_encode($tahun_ini) ?>
    };
</script>

<!-- Load ApexCharts -->
<script src="<?= base_url('dist/libs/apexcharts/dist/apexcharts.min.js') ?>"></script>
<script>
    // Fallback CDN jika file lokal tidak termuat
    if (typeof ApexCharts === 'undefined') {
        document.write('<script src="https://cdn.jsdelivr.net/npm/apexcharts"><\/script>');
    }
</script>

<!-- ==========================================
     APEXCHARTS INITIALIZATION & INTERACTION
========================================== -->
<script>
$(document).ready(function() {
    initTrenChart();
    initDonutChart();
    initKoorChart();
});

// 1. Inisialisasi Chart Tren Penerimaan Kas IPL (Area Spline)
function initTrenChart() {
    const dataTren = window.DASHBOARD_DATA.tren12Bln || [];
    const categories = dataTren.map(item => item.label);
    const totals = dataTren.map(item => item.total);

    const options = {
        series: [{
            name: 'Penerimaan IPL',
            data: totals
        }],
        chart: {
            type: 'area',
            height: 330,
            fontFamily: 'inherit',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false
                }
            },
            events: {
                // Interactive Click on data point triggers drilldown modal
                dataPointSelection: function(event, chartContext, config) {
                    const selectedIndex = config.dataPointIndex;
                    if (selectedIndex >= 0 && dataTren[selectedIndex]) {
                        const item = dataTren[selectedIndex];
                        showDashboardDetail('bulan_drilldown', item.key, item.label);
                    }
                }
            }
        },
        colors: ['#059669'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.65,
                opacityTo: 0.05,
                stops: [0, 95, 100]
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: {
            enabled: false
        },
        markers: {
            size: 5,
            colors: ['#059669'],
            strokeColors: '#ffffff',
            strokeWidth: 2,
            hover: {
                size: 7
            }
        },
        xaxis: {
            categories: categories,
            labels: {
                style: {
                    fontSize: '11px',
                    fontWeight: 500
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    if (value >= 1000000) {
                        return 'Rp ' + (value / 1000000).toFixed(1) + ' jt';
                    } else if (value >= 1000) {
                        return 'Rp ' + (value / 1000).toFixed(0) + ' rb';
                    }
                    return 'Rp ' + value;
                },
                style: {
                    fontSize: '11px'
                }
            }
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function (value) {
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                }
            }
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 4
        }
    };

    const chartTren = new ApexCharts(document.querySelector("#chartTrenKas"), options);
    chartTren.render();
}

// 2. Inisialisasi Chart Donut Status Kepatuhan
function initDonutChart() {
    const lunas = <?= (int)$rumah_bayar_bln ?>;
    const pending = <?= (int)$jml_pending ?>;
    const nunggak = <?= (int)$rumah_nunggak_bln ?>;

    const options = {
        series: [lunas, pending, nunggak],
        labels: ['Lunas', 'Menunggu Validasi', 'Belum Bayar'],
        colors: ['#10b981', '#f59e0b', '#ef4444'],
        chart: {
            type: 'donut',
            height: 270,
            fontFamily: 'inherit',
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    const idx = config.dataPointIndex;
                    if (idx === 0) showDashboardDetail('warga_lunas');
                    else if (idx === 1) showDashboardDetail('antrean_pending');
                    else if (idx === 2) showDashboardDetail('warga_menunggak');
                }
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: { show: true, fontSize: '12px' },
                        value: {
                            show: true,
                            fontSize: '22px',
                            fontWeight: 700,
                            formatter: function (val) {
                                return val + ' Unit';
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Rumah',
                            color: '#64748b',
                            fontSize: '11px',
                            formatter: function (w) {
                                return <?= (int)$total_rumah ?> + ' Unit';
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'bottom',
            fontSize: '12px',
            markers: { radius: 12 }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    const pct = (val / <?= max(1, (int)$total_rumah) ?> * 100).toFixed(1);
                    return val + ' Rumah (' + pct + '%)';
                }
            }
        }
    };

    const chartDonut = new ApexCharts(document.querySelector("#chartDonutKepatuhan"), options);
    chartDonut.render();
}

// 3. Inisialisasi Chart Capaian Koordinator Blok (Horizontal Bar)
function initKoorChart() {
    const dataKoor = window.DASHBOARD_DATA.koorPerf || [];
    const names = dataKoor.map(k => k.nama);
    const percentages = dataKoor.map(k => {
        const total = parseInt(k.jml_rumah) || 0;
        const bayar = parseInt(k.sudah_bayar) || 0;
        return total > 0 ? Math.round((bayar / total) * 100) : 0;
    });

    const options = {
        series: [{
            name: 'Kepatuhan (%)',
            data: percentages
        }],
        chart: {
            type: 'bar',
            height: Math.max(300, dataKoor.length * 34),
            fontFamily: 'inherit',
            toolbar: { show: false },
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    const idx = config.dataPointIndex;
                    if (idx >= 0 && dataKoor[idx]) {
                        showDashboardDetail('koordinator_drilldown', dataKoor[idx].id, dataKoor[idx].nama);
                    }
                }
            }
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 6,
                barHeight: '55%',
                distributed: true,
                dataLabels: { position: 'top' }
            }
        },
        colors: ['#0d9488', '#0284c7', '#16a34a', '#e11d48', '#8b5cf6', '#d97706', '#475569'],
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val + "%";
            },
            offsetX: 28,
            style: {
                fontSize: '11px',
                fontWeight: 600,
                colors: ['#334155']
            }
        },
        xaxis: {
            categories: names,
            max: 100,
            labels: {
                formatter: function(val) { return val + '%'; }
            }
        },
        legend: { show: false },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function(val, opt) {
                    const k = dataKoor[opt.dataPointIndex];
                    return val + '% (' + k.sudah_bayar + ' dari ' + k.jml_rumah + ' rumah lunas - Rp ' + Number(k.nominal_terkumpul).toLocaleString('id-ID') + ')';
                }
            }
        }
    };

    const chartKoor = new ApexCharts(document.querySelector("#chartKoorProgress"), options);
    chartKoor.render();
}

// ==========================================
// DRILLDOWN POPUP MODAL ENGINE
// ==========================================
let currentModalRows = [];

function showDashboardDetail(type, param1 = null, param2 = null) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetailDashboard'));
    const titleEl = document.getElementById('modalDetailTitle');
    const subtitleEl = document.getElementById('modalDetailSubtitle');
    const iconEl = document.getElementById('modalHeaderIcon');
    const headEl = document.getElementById('modalTableHead');
    const bodyEl = document.getElementById('modalTableBody');
    const footerSummary = document.getElementById('modalFooterSummary');
    
    $('#modalSearchInput').val('');
    bodyEl.innerHTML = '';
    headEl.innerHTML = '';
    currentModalRows = [];

    const rumahData = window.DASHBOARD_DATA.rumahStatus || [];
    const pendingData = window.DASHBOARD_DATA.pendingList || [];
    const verifiedData = window.DASHBOARD_DATA.verifiedList || [];
    const bulanIni = window.DASHBOARD_DATA.bulanIniName;
    const tahunIni = window.DASHBOARD_DATA.tahunIni;

    // --- CASE 1: PENERIMAAN KAS MASUK (VERIFIED) ---
    if (type === 'penerimaan_ipl') {
        titleEl.textContent = 'Rincian Penerimaan Kas Masuk IPL (Verified)';
        subtitleEl.textContent = 'Menampilkan seluruh setoran IPL yang telah disetujui dalam periode filter.';
        iconEl.className = 'bi bi-wallet2';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Warga &amp; Rumah</th>
            <th>Koordinator</th>
            <th>Tanggal Bayar</th>
            <th class="text-end">Jumlah Bayar</th>
            <th class="text-center">Via</th>
            <th class="text-center">Status</th>
        `;

        currentModalRows = verifiedData.map((item, idx) => {
            const viaBadge = item.pembayaran_via === 'koordinator'
                ? '<span class="badge badge-soft-info rounded-pill px-2">Koordinator</span>'
                : '<span class="badge badge-soft-success rounded-pill px-2">Transfer</span>';

            return {
                searchText: `${item.warga_nama} ${item.warga_alamat} ${item.koordinator_nama} ${item.pembayaran_via}`.toLowerCase(),
                csvData: [idx + 1, item.warga_nama || '-', item.warga_alamat || '-', item.koordinator_nama || '-', item.tanggal_bayar, item.jumlah_bayar, item.pembayaran_via, 'verified'],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>
                            <strong class="text-dark d-block">${escapeHtml(item.warga_nama || 'Warga')}</strong>
                            <small class="text-muted">${escapeHtml(item.warga_alamat || '-')}</small>
                        </td>
                        <td><small class="fw-semibold">${escapeHtml(item.koordinator_nama || '-')}</small></td>
                        <td><span class="text-nowrap">${formatDate(item.tanggal_bayar)}</span></td>
                        <td class="text-end fw-bold text-success">Rp ${formatRupiah(item.jumlah_bayar)}</td>
                        <td class="text-center">${viaBadge}</td>
                        <td class="text-center"><span class="badge badge-soft-success rounded-pill px-2"><i class="bi bi-check-circle me-1"></i>Verified</span></td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Total data: <strong>${currentModalRows.length}</strong> transaksi verified.`;
    }

    // --- CASE 2: WARGA LUNAS BULAN INI ---
    else if (type === 'warga_lunas') {
        titleEl.textContent = `Daftar Unit Rumah Lunas IPL (${bulanIni} ${tahunIni})`;
        subtitleEl.textContent = 'Seluruh rumah yang telah menyelesaikan iuran IPL pada bulan berjalan.';
        iconEl.className = 'bi bi-check-circle-fill';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Nomor Rumah</th>
            <th>Nama Pemilik / Penghuni</th>
            <th>Koordinator Blok</th>
            <th>Tanggal Setor</th>
            <th class="text-end">Nominal</th>
            <th class="text-center">Status</th>
        `;

        const lunasHouses = rumahData.filter(r => r.status_bayar === 'lunas');
        currentModalRows = lunasHouses.map((item, idx) => {
            return {
                searchText: `${item.nomor_rumah} ${item.pemilik_rumah} ${item.koordinator_nama}`.toLowerCase(),
                csvData: [idx + 1, item.nomor_rumah, item.pemilik_rumah, item.koordinator_nama, item.tanggal_bayar, item.nominal_bayar, 'LUNAS'],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><span class="badge bg-light text-dark border fw-bold fs-6">${escapeHtml(item.nomor_rumah)}</span></td>
                        <td><strong>${escapeHtml(item.pemilik_rumah || '-')}</strong></td>
                        <td><small class="text-muted">${escapeHtml(item.koordinator_nama || '-')}</small></td>
                        <td><small class="text-nowrap">${item.tanggal_bayar ? formatDate(item.tanggal_bayar) : '-'}</small></td>
                        <td class="text-end fw-bold text-success">Rp ${formatRupiah(item.nominal_bayar)}</td>
                        <td class="text-center"><span class="badge badge-soft-success rounded-pill px-3 py-1 fw-bold">Lunas</span></td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Total: <strong>${currentModalRows.length}</strong> unit rumah lunas bulan ini.`;
    }

    // --- CASE 3: WARGA MENUNGGAK BULAN INI ---
    else if (type === 'warga_menunggak') {
        titleEl.textContent = `Daftar Rumah Belum Bayar / Menunggak (${bulanIni} ${tahunIni})`;
        subtitleEl.textContent = 'Daftar rumah yang belum melakukan pembayaran IPL bulan ini. Tersedia tombol WA untuk pengingat.';
        iconEl.className = 'bi bi-exclamation-triangle-fill';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Nomor Rumah</th>
            <th>Nama Pemilik / Penghuni</th>
            <th>Koordinator Blok</th>
            <th>Kontak HP</th>
            <th class="text-center">Aksi Reminder</th>
        `;

        const nunggakHouses = rumahData.filter(r => r.status_bayar === 'nunggak');
        currentModalRows = nunggakHouses.map((item, idx) => {
            let waBtn = '<span class="text-muted small">Tanpa HP</span>';
            if (item.no_hp && item.no_hp.trim() !== '') {
                const cleanPhone = item.no_hp.replace(/^0/, '62').replace(/[^0-9]/g, '');
                const waText = encodeURIComponent(`Halo Bpk/Ibu ${item.pemilik_rumah} (${item.nomor_rumah}), kami menginformasikan tagihan iuran IPL Perum TSI periode ${bulanIni} ${tahunIni} saat ini belum terdata. Mohon kesediaannya untuk melakukan pembayaran. Terima kasih.`);
                waBtn = `
                    <a href="https://wa.me/${cleanPhone}?text=${waText}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1">
                        <i class="bi bi-whatsapp me-1"></i> Ingatkan WA
                    </a>
                `;
            }

            return {
                searchText: `${item.nomor_rumah} ${item.pemilik_rumah} ${item.koordinator_nama} ${item.no_hp}`.toLowerCase(),
                csvData: [idx + 1, item.nomor_rumah, item.pemilik_rumah, item.koordinator_nama, item.no_hp || '-', 'BELUM BAYAR'],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold fs-6">${escapeHtml(item.nomor_rumah)}</span></td>
                        <td><strong class="text-dark">${escapeHtml(item.pemilik_rumah || 'Warga')}</strong></td>
                        <td><small class="text-muted">${escapeHtml(item.koordinator_nama || '-')}</small></td>
                        <td><small class="text-muted">${escapeHtml(item.no_hp || '-')}</small></td>
                        <td class="text-center">${waBtn}</td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Total: <strong>${currentModalRows.length}</strong> unit rumah belum melunasi iuran ${bulanIni}.`;
    }

    // --- CASE 4: ANTREAN PENDING VERIFIKASI ---
    else if (type === 'antrean_pending') {
        titleEl.textContent = 'Antrean Pembayaran Menunggu Verifikasi';
        subtitleEl.textContent = 'Daftar konfirmasi pembayaran yang belum divalidasi oleh administrator/bendahara.';
        iconEl.className = 'bi bi-hourglass-split';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Warga &amp; Rumah</th>
            <th>Periode Iuran</th>
            <th>Tanggal Upload</th>
            <th class="text-end">Jumlah Bayar</th>
            <th class="text-center">Via</th>
            <th class="text-center">Aksi</th>
        `;

        currentModalRows = pendingData.map((item, idx) => {
            return {
                searchText: `${item.warga_nama} ${item.warga_alamat} ${item.koordinator_nama} ${item.bulan_mulai}`.toLowerCase(),
                csvData: [idx + 1, item.warga_nama, item.warga_alamat, item.bulan_mulai, item.created_at, item.jumlah_bayar, item.pembayaran_via, 'PENDING'],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>
                            <strong class="text-dark d-block">${escapeHtml(item.warga_nama || 'Warga')}</strong>
                            <small class="text-muted">${escapeHtml(item.warga_alamat || '-')}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border">${escapeHtml(item.bulan_mulai || '-')}</span></td>
                        <td><small class="text-muted">${formatDate(item.created_at)}</small></td>
                        <td class="text-end fw-bold text-warning">Rp ${formatRupiah(item.jumlah_bayar)}</td>
                        <td class="text-center"><span class="badge badge-soft-warning rounded-pill px-2">${escapeHtml(item.pembayaran_via || '-')}</span></td>
                        <td class="text-center">
                            <a href="<?= base_url('pembayaran/verifikasi-pembayaran') ?>" class="btn btn-sm btn-dark rounded-pill px-3 py-1">
                                <i class="bi bi-shield-check me-1"></i> Validasi
                            </a>
                        </td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Terdapat <strong>${currentModalRows.length}</strong> pembayaran menunggu verifikasi.`;
    }

    // --- CASE 5: TOTAL MASTER RUMAH ---
    else if (type === 'total_rumah') {
        titleEl.textContent = 'Master Data Rumah Perumahan TSI';
        subtitleEl.textContent = 'Seluruh nomor unit rumah yang terdaftar dalam sistem beserta status bulan ini.';
        iconEl.className = 'bi bi-houses-fill';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Nomor Rumah</th>
            <th>Nama Warga</th>
            <th>Koordinator Blok</th>
            <th>Kontak HP</th>
            <th class="text-center">Status (${bulanIni})</th>
        `;

        currentModalRows = rumahData.map((item, idx) => {
            let statusBadge = '<span class="badge badge-soft-danger rounded-pill px-2">Nunggak</span>';
            if (item.status_bayar === 'lunas') statusBadge = '<span class="badge badge-soft-success rounded-pill px-2">Lunas</span>';
            else if (item.status_bayar === 'pending') statusBadge = '<span class="badge badge-soft-warning rounded-pill px-2">Pending</span>';

            return {
                searchText: `${item.nomor_rumah} ${item.pemilik_rumah} ${item.koordinator_nama} ${item.no_hp}`.toLowerCase(),
                csvData: [idx + 1, item.nomor_rumah, item.pemilik_rumah, item.koordinator_nama, item.no_hp, item.status_bayar],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><span class="badge bg-light text-dark border fw-bold">${escapeHtml(item.nomor_rumah)}</span></td>
                        <td><strong>${escapeHtml(item.pemilik_rumah || '-')}</strong></td>
                        <td><small class="text-muted">${escapeHtml(item.koordinator_nama || '-')}</small></td>
                        <td><small class="text-muted">${escapeHtml(item.no_hp || '-')}</small></td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Total unit rumah terdaftar: <strong>${currentModalRows.length}</strong> unit.`;
    }

    // --- CASE 6: BULAN DRILLDOWN (CLICK FROM 12-MONTH AREA CHART) ---
    else if (type === 'bulan_drilldown') {
        const monthKey = param1;
        const monthLabel = param2 || monthKey;
        titleEl.textContent = `Rincian Pembayaran IPL Periode ${monthLabel}`;
        subtitleEl.textContent = `Daftar transaksi yang disetujui pada bulan ${monthLabel}.`;
        iconEl.className = 'bi bi-calendar3';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Warga &amp; Rumah</th>
            <th>Koordinator</th>
            <th>Tanggal Setor</th>
            <th class="text-end">Jumlah Bayar</th>
            <th class="text-center">Via</th>
        `;

        const filteredByMonth = verifiedData.filter(v => v.bulan_key === monthKey);
        currentModalRows = filteredByMonth.map((item, idx) => {
            return {
                searchText: `${item.warga_nama} ${item.warga_alamat} ${item.koordinator_nama}`.toLowerCase(),
                csvData: [idx + 1, item.warga_nama, item.warga_alamat, item.koordinator_nama, item.tanggal_bayar, item.jumlah_bayar, item.pembayaran_via],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td>
                            <strong class="text-dark d-block">${escapeHtml(item.warga_nama || 'Warga')}</strong>
                            <small class="text-muted">${escapeHtml(item.warga_alamat || '-')}</small>
                        </td>
                        <td><small class="fw-semibold">${escapeHtml(item.koordinator_nama || '-')}</small></td>
                        <td><span class="text-nowrap">${formatDate(item.tanggal_bayar)}</span></td>
                        <td class="text-end fw-bold text-success">Rp ${formatRupiah(item.jumlah_bayar)}</td>
                        <td class="text-center"><span class="badge badge-soft-info rounded-pill px-2">${escapeHtml(item.pembayaran_via || '-')}</span></td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Total ${currentModalRows.length} transaksi pada bulan <strong>${monthLabel}</strong>.`;
    }

    // --- CASE 7: KOORDINATOR DRILLDOWN (CLICK FROM BAR CHART) ---
    else if (type === 'koordinator_drilldown') {
        const koorId = param1;
        const koorName = param2 || 'Koordinator';
        titleEl.textContent = `Rumah Binaan: ${koorName}`;
        subtitleEl.textContent = `Daftar rumah dan status iuran bulan ${bulanIni} ${tahunIni} di bawah koordinator ${koorName}.`;
        iconEl.className = 'bi bi-person-badge';

        headEl.innerHTML = `
            <th style="width:50px;">No</th>
            <th>Nomor Rumah</th>
            <th>Nama Warga</th>
            <th>Kontak HP</th>
            <th class="text-end">Setoran</th>
            <th class="text-center">Status</th>
        `;

        const koorHouses = rumahData.filter(r => String(r.koordinator_id) === String(koorId));
        currentModalRows = koorHouses.map((item, idx) => {
            let statusBadge = '<span class="badge badge-soft-danger rounded-pill px-2">Nunggak</span>';
            if (item.status_bayar === 'lunas') statusBadge = '<span class="badge badge-soft-success rounded-pill px-2">Lunas</span>';
            else if (item.status_bayar === 'pending') statusBadge = '<span class="badge badge-soft-warning rounded-pill px-2">Pending</span>';

            return {
                searchText: `${item.nomor_rumah} ${item.pemilik_rumah} ${item.no_hp}`.toLowerCase(),
                csvData: [idx + 1, item.nomor_rumah, item.pemilik_rumah, item.no_hp, item.nominal_bayar, item.status_bayar],
                html: `
                    <tr>
                        <td>${idx + 1}</td>
                        <td><span class="badge bg-light text-dark border fw-bold">${escapeHtml(item.nomor_rumah)}</span></td>
                        <td><strong>${escapeHtml(item.pemilik_rumah || '-')}</strong></td>
                        <td><small class="text-muted">${escapeHtml(item.no_hp || '-')}</small></td>
                        <td class="text-end fw-bold text-success">Rp ${formatRupiah(item.nominal_bayar)}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `
            };
        });

        footerSummary.innerHTML = `Total ${currentModalRows.length} rumah binaan <strong>${escapeHtml(koorName)}</strong>.`;
    }

    renderModalRows();
    modal.show();
}

// Render filtered rows inside modal
function renderModalRows() {
    const bodyEl = document.getElementById('modalTableBody');
    const query = ($('#modalSearchInput').val() || '').trim().toLowerCase();

    let renderedCount = 0;
    let htmlOutput = '';

    currentModalRows.forEach(item => {
        if (!query || item.searchText.includes(query)) {
            htmlOutput += item.html;
            renderedCount++;
        }
    });

    if (renderedCount === 0) {
        bodyEl.innerHTML = `<tr><td colspan="10" class="text-center py-5 text-muted"><i class="bi bi-search fs-2 d-block mb-2"></i>Tidak ada data yang cocok dengan pencarian Anda.</td></tr>`;
    } else {
        bodyEl.innerHTML = htmlOutput;
    }

    document.getElementById('modalRecordCount').textContent = `Menampilkan ${renderedCount} data`;
}

function filterModalTable() {
    renderModalRows();
}

// Unduh CSV dari isi modal yang sedang tampil
function exportModalToCSV() {
    if (!currentModalRows || currentModalRows.length === 0) {
        alert('Tidak ada data untuk diekspor.');
        return;
    }

    let csvContent = "data:text/csv;charset=utf-8,";
    // Ambil header
    const headers = [];
    $('#modalTableHead th').each(function() {
        headers.push('"' + $(this).text().replace(/"/g, '""') + '"');
    });
    csvContent += headers.join(",") + "\r\n";

    // Ambil baris
    const query = ($('#modalSearchInput').val() || '').trim().toLowerCase();
    currentModalRows.forEach(item => {
        if (!query || item.searchText.includes(query)) {
            const escapedRow = item.csvData.map(val => `"${String(val).replace(/"/g, '""')}"`);
            csvContent += escapedRow.join(",") + "\r\n";
        }
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `laporan_detail_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Quick Date Range Filter
function setFilterQuick(period) {
    const today = new Date();
    const pad = n => String(n).padStart(2, '0');
    let start, end;

    if (period === 'bulan_ini') {
        start = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-01`;
        end = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
    } else if (period === 'bulan_lalu') {
        const prevMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        const lastDayPrev = new Date(today.getFullYear(), today.getMonth(), 0);
        start = `${prevMonth.getFullYear()}-${pad(prevMonth.getMonth() + 1)}-01`;
        end = `${lastDayPrev.getFullYear()}-${pad(lastDayPrev.getMonth() + 1)}-${pad(lastDayPrev.getDate())}`;
    } else if (period === 'tahun_ini') {
        start = `${today.getFullYear()}-01-01`;
        end = `${today.getFullYear()}-${pad(today.getMonth() + 1)}-${pad(today.getDate())}`;
    }

    if (start && end) {
        document.getElementById('filter_start_date').value = start;
        document.getElementById('filter_end_date').value = end;
        document.getElementById('dashboardFilterForm').submit();
    }
}

// Helper Formatters
function formatRupiah(val) {
    return Number(val || 0).toLocaleString('id-ID');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const pad = n => String(n).padStart(2, '0');
    return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()}`;
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Trigger WA Sync otomatis
function triggerSyncWA() {
    $.ajax({
        url: '<?= base_url("syncwa/public") ?>',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (res && (res.status === 'success' || (res.message && res.message.includes('terkirim')))) {
                setTimeout(triggerSyncWA, 2000);
            }
        },
        error: function() {
            setTimeout(triggerSyncWA, 30000);
        }
    });
}
triggerSyncWA();
</script>
