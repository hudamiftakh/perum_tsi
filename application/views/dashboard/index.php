<!-- <h4 class="fs-5 mt-5 mb-3">E-kinerja Kader Surabaya Hebat | Kelurahan Pradah Kalikendal | Dukuh Pakis</h4> -->
<!-- Row -->

<?php if ($_SESSION['username']['role'] == 'koordinator') : ?>
    <?php
    $id_koor = $_SESSION['username']['id'];
    $bulan_ini = date('m');
    $tahun_ini = date('Y');
    $bulan_str = date('Y-m');
    $nama_bulan_ini = [
        '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
        '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
        '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
    ][date('m')];

    // 1. Ambil seluruh rumah binaan koordinator ini
    $rumah_binaan = $this->db->query("
        SELECT r.id, r.alamat, r.nama, MAX(kl.no_hp) as no_hp
        FROM master_rumah r
        LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
        WHERE r.id_koordinator = ?
        GROUP BY r.id, r.alamat, r.nama
        ORDER BY r.alamat ASC
    ", [$id_koor])->result_array();

    // 2. Ambil pembayaran bulan berjalan untuk rumah binaan koordinator ini
    $pembayaran_bulan = $this->db->query("
        SELECT p.id, u.id_rumah, p.status, p.jumlah_bayar, p.pembayaran_via, p.tanggal_bayar, p.created_at
        FROM master_pembayaran p
        LEFT JOIN master_users u ON p.user_id = u.id
        LEFT JOIN master_rumah r ON u.id_rumah = r.id
        WHERE r.id_koordinator = ?
        AND p.status IN ('verified', 'pending')
        AND (
            DATE_FORMAT(p.untuk_bulan, '%Y-%m') = ?
            OR FIND_IN_SET(?, p.bulan_rapel) > 0
            OR (
                DATE_FORMAT(p.bulan_mulai, '%Y-%m') = ?
                AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
                AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
            )
        )
    ", [$id_koor, $bulan_str, $bulan_str, $bulan_str])->result_array();

    $pay_map = [];
    $total_nominal_bulan = 0;
    foreach ($pembayaran_bulan as $p) {
        if (!isset($pay_map[$p['id_rumah']])) {
            $pay_map[$p['id_rumah']] = $p;
            $total_nominal_bulan += (float)$p['jumlah_bayar'];
        }
    }

    $list_lunas = [];
    $list_nunggak = [];
    foreach ($rumah_binaan as $rm) {
        if (isset($pay_map[$rm['id']])) {
            $list_lunas[] = array_merge($rm, $pay_map[$rm['id']]);
        } else {
            $list_nunggak[] = $rm;
        }
    }

    $total_binaan = count($rumah_binaan);
    $total_lunas = count($list_lunas);
    $total_nunggak = count($list_nunggak);
    $persen_capaian = ($total_binaan > 0) ? round(($total_lunas / $total_binaan) * 100) : 0;

    // 3. Ambil 5 riwayat entri terakhir koordinator ini
    $riwayat_terbaru = $this->db->query("
        SELECT p.id, p.jumlah_bayar, p.tanggal_bayar, p.status, p.pembayaran_via, p.created_at,
               u.nama as warga_nama, r.alamat as warga_alamat
        FROM master_pembayaran p
        LEFT JOIN master_users u ON p.user_id = u.id
        LEFT JOIN master_rumah r ON u.id_rumah = r.id
        WHERE r.id_koordinator = ?
        ORDER BY p.id DESC
        LIMIT 5
    ", [$id_koor])->result_array();
    ?>

    <div class="koordinator-dashboard">
        <!-- Hero Header -->
        <div class="card border-0 mb-4 position-relative overflow-hidden text-white" 
             style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-radius: 20px; box-shadow: 0 10px 25px rgba(5, 150, 105, 0.25);">
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                            <i class="ti ti-user-check text-warning"></i>
                            <span class="small fw-semibold text-white">Koordinator Blok &bull; Paguyuban TSI</span>
                        </div>
                        <h3 class="fw-bold text-white mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['username']['nama']); ?>! 👋</h3>
                        <p class="text-white-50 mb-0 small">
                            Pantau setoran iuran warga binaan Anda untuk periode <strong><?= $nama_bulan_ini ?> <?= $tahun_ini ?></strong>.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-white text-success px-3 py-2 fs-6 rounded-pill shadow-sm fw-bold">
                            <i class="ti ti-calendar me-1"></i> <?= $nama_bulan_ini ?> <?= $tahun_ini ?>
                        </span>
                    </div>
                </div>
            </div>
            <i class="ti ti-home position-absolute" style="right: -15px; bottom: -25px; font-size: 10rem; opacity: 0.1;"></i>
        </div>

        <!-- 4 Metrik Utama Blok Koordinator -->
        <div class="row g-3 mb-4">
            <!-- 1. Total Rumah Binaan -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #0ea5e9 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e0f2fe;color:#0ea5e9;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-home"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Rumah Binaan</span>
                            <h3 class="fw-bold text-dark mb-0"><?= $total_binaan ?></h3>
                            <small class="text-muted">Total di wilayah blok</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Sudah Bayar Bulan Ini -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #10b981 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#d1fae5;color:#10b981;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Sudah Bayar</span>
                            <h3 class="fw-bold text-success mb-0"><?= $total_lunas ?> <small class="fs-6 text-muted">/ <?= $total_binaan ?></small></h3>
                            <small class="text-success fw-semibold">Rp<?= number_format($total_nominal_bulan, 0, ',', '.') ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Masih Nunggak Bulan Ini -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #ef4444 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fee2e2;color:#ef4444;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-alert-triangle"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Belum Bayar</span>
                            <h3 class="fw-bold text-danger mb-0"><?= $total_nunggak ?></h3>
                            <small class="text-danger fw-semibold">Rumah belum lunas</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Capaian Entri Blok -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #8b5cf6 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#ede9fe;color:#8b5cf6;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-chart-pie"></i>
                        </div>
                        <div class="w-100">
                            <span class="text-muted small fw-semibold d-block">Capaian Entri</span>
                            <h3 class="fw-bold mb-0" style="color:#8b5cf6"><?= $persen_capaian ?>%</h3>
                            <div class="progress mt-1" style="height:6px">
                                <div class="progress-bar" style="width:<?= $persen_capaian ?>%;background:#8b5cf6"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Cepat (Quick Actions) -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-3"><i class="ti ti-bolt text-warning me-1"></i> Aksi Cepat Koordinator</h6>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('pembayaran'); ?>" class="btn btn-success w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-cash-coin fs-4"></i>
                            <span class="fw-bold small">Input Pembayaran</span>
                            <small class="text-white-50" style="font-size:0.72rem">Catat iuran warga</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('laporan-rekap-rapel'); ?>" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-table fs-4"></i>
                            <span class="fw-bold small">Rekap Rapel Blok</span>
                            <small class="text-white-50" style="font-size:0.72rem">Tabel setoran tahunan</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('log-koordinator'); ?>" class="btn btn-info text-white w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-graph-up fs-4"></i>
                            <span class="fw-bold small">Log Kinerja Entri</span>
                            <small class="text-white-50" style="font-size:0.72rem">Cek rekap nunggak</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('pendataan-keluarga'); ?>" class="btn btn-warning text-dark w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-people fs-4"></i>
                            <span class="fw-bold small">Pendataan Warga</span>
                            <small class="text-dark-50" style="font-size:0.72rem">Kelola kontak & keluarga</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Kolom Kiri: Warga yang Belum Bayar Bulan Ini -->
            <div class="col-lg-7 mb-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold text-danger mb-0">
                                    <i class="ti ti-alert-circle me-1"></i> Warga Blok Belum Bayar (<?= $nama_bulan_ini ?>)
                                </h6>
                                <small class="text-muted">Terdapat <?= $total_nunggak ?> rumah binaan yang belum lunas</small>
                            </div>
                            <span class="badge bg-danger rounded-pill px-2 py-1"><?= $total_nunggak ?> Rumah</span>
                        </div>

                        <?php if (empty($list_nunggak)): ?>
                            <div class="alert alert-success d-flex align-items-center mb-0 rounded-3" role="alert">
                                <i class="ti ti-circle-check fs-5 me-2"></i>
                                <div><strong>Luar Biasa!</strong> Semua warga binaan Anda sudah lunas untuk bulan <?= $nama_bulan_ini ?> <?= $tahun_ini ?>.</div>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive" style="max-height: 320px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Rumah</th>
                                            <th>Nama Warga</th>
                                            <th class="text-center">Aksi Cepat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($list_nunggak as $ngk): 
                                            $wa_clean = !empty($ngk['no_hp']) ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $ngk['no_hp'])) : '';
                                            $wa_text = "Assalamu'alaikum Bapak/Ibu *" . $ngk['nama'] . "*,\n\nMohon izin menginfokan dari Koordinator Blok perihal iuran IPL Paguyuban TSI untuk bulan *" . $nama_bulan_ini . " " . $tahun_ini . "* yang belum tercatat.\n\nBisa dibayarkan melalui kami (Koordinator) atau transfer rekening paguyuban. Terima kasih banyak atas kerjasamanya! 🙏";
                                            $wa_url = $wa_clean ? "https://wa.me/" . $wa_clean . "?text=" . urlencode($wa_text) : '';
                                        ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($ngk['alamat']) ?></strong></td>
                                                <td>
                                                    <span class="d-block text-truncate" style="max-width:160px;"><?= htmlspecialchars($ngk['nama']) ?></span>
                                                    <?php if ($wa_clean): ?>
                                                        <small class="text-muted"><i class="bi bi-whatsapp text-success"></i> <?= htmlspecialchars($ngk['no_hp']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group">
                                                        <?php if ($wa_url): ?>
                                                            <a href="<?= $wa_url ?>" target="_blank" class="btn btn-sm btn-outline-success px-2 py-1" title="Kirim Pengingat WA">
                                                                <i class="bi bi-whatsapp"></i> Ingatkan
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="<?= base_url('pembayaran/' . encrypt_url($ngk['id'])) ?>" class="btn btn-sm btn-success px-2 py-1" title="Bayar Sekarang">
                                                            <i class="bi bi-cash"></i> Bayar
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: 5 Riwayat Entri Terakhir Koordinator Ini -->
            <div class="col-lg-5 mb-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="ti ti-history me-1 text-primary"></i> 5 Riwayat Entri Terakhir Anda
                        </h6>
                        <?php if (empty($riwayat_terbaru)): ?>
                            <p class="text-muted text-center py-4 small">Belum ada riwayat entri pembayaran tercatat.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($riwayat_terbaru as $rt): ?>
                                    <div class="p-2 rounded-3 border d-flex justify-content-between align-items-center" style="background:#f8fafc">
                                        <div>
                                            <strong class="d-block text-dark small"><?= htmlspecialchars($rt['warga_alamat'] ?? '-') ?> &bull; <?= htmlspecialchars($rt['warga_nama'] ?? '-') ?></strong>
                                            <small class="text-muted">
                                                <i class="bi bi-calendar me-1"></i><?= !empty($rt['tanggal_bayar']) ? date('d/m/Y', strtotime($rt['tanggal_bayar'])) : date('d/m/Y', strtotime($rt['created_at'])) ?>
                                                &bull; <?= ucfirst($rt['pembayaran_via']) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold text-success d-block small">Rp<?= number_format($rt['jumlah_bayar'], 0, ',', '.') ?></span>
                                            <span class="badge <?= $rt['status'] === 'verified' ? 'bg-success' : 'bg-warning text-dark' ?>" style="font-size:0.68rem">
                                                <?= ucfirst($rt['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="<?= base_url('laporan-rekap-rapel') ?>" class="small text-primary fw-semibold">Lihat Seluruh Rekapan &rarr;</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
    <style>
        .card-header.bg-primary,
        .btn-success,
        .table thead th,
        .role-badge {
            background-color: #008d4c !important;
            border-color: #008d4c !important;
        }
        .btn-success {
            color: #fff !important;
        }
        .table thead th {
            color: #fff;
        }
        .filter-bar {
            background: #e6f7ee;
            border-radius: 14px;
            padding: 18px 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,141,76,0.08);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .export-btns .btn {
            margin-right: 10px;
        }
        .dashboard-header {
            background: linear-gradient(90deg, #008d4c 60%, #00b86b 100%);
            color: #fff;
            border-radius: 18px;
            padding: 32px 18px 24px 18px;
            margin-bottom: 28px;
            box-shadow: 0 4px 18px rgba(0,141,76,0.13);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .dashboard-header h2 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .dashboard-header p {
            font-size: 1.1rem;
            margin-bottom: 0;
            opacity: 0.93;
        }
        .dashboard-header .header-icon {
            position: absolute;
            right: 24px;
            top: 24px;
            font-size: 3.5rem;
            opacity: 0.13;
        }
        @media (max-width: 767.98px) {
            .dashboard-header {
                padding: 22px 8px 18px 8px;
                font-size: 1rem;
            }
            .dashboard-header h2 {
                font-size: 1.2rem;
            }
            .dashboard-header .header-icon {
                font-size: 2.2rem;
                right: 10px;
                top: 10px;
            }
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.7rem;
                padding: 12px 6px;
            }
            .export-btns {
                justify-content: flex-start !important;
            }
            .table-responsive {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 575.98px) {
            .dashboard-header {
                padding: 12px 4px 10px 4px;
            }
            .filter-bar {
                padding: 8px 2px;
            }
            .table-responsive {
                font-size: 0.89rem;
            }
        }
        .filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    width: 100%;
  }
  .filter-item {
    flex: 1 1 auto;
  }
  @media (max-width: 768px) {
    .filter-bar {
      flex-direction: column;
    }
    .filter-item {
      width: 100%;
    }
  }
    </style>
    <div class="dashboard-header mb-4">
        <h2 style="color: #fff; font-weight: 600;">
            <i class="fa fa-home me-2"></i>
            Statistik IPL Perumahan TSI
        </h2>
        <p>
            Pantau pembayaran IPL, filter data sesuai periode, dan ekspor laporan dengan mudah.
        </p>
        <span class="header-icon">
            <i class="fa fa-bar-chart"></i>
        </span>
    </div>
    <div class="filter-bar d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-between gap-3" 
     style="background: linear-gradient(90deg, #e6f7ee 70%, #d2f4e3 100%);
            box-shadow: 0 2px 10px rgba(0,141,76,0.09);
            border-radius: 16px; padding: 22px 18px; margin-bottom: 28px;">

    <!-- Filter Form -->
    <form class="d-flex flex-wrap flex-md-nowrap align-items-center gap-2 mb-0" method="get" id="filterForm">
        <label class="mb-0 fw-bold text-success" for="start_date" style="font-size:1.05rem;">
            <i class="fa fa-calendar-alt me-1"></i> Periode:
        </label>
       <input type="date" class="form-control form-control-lg border-success" 
       name="start_date" id="start_date" 
       value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>" 
       style="min-width:140px;">

        <span class="fw-bold text-secondary">s/d</span>

        <input type="date" class="form-control form-control-lg border-success" 
            name="end_date" id="end_date" 
            value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>" 
            style="min-width:140px;">

        <!-- Button Group -->
        <div class="btn-group ms-2" style="gap: 6px;">
            <button type="submit" class="btn btn-success px-3 py-1">
            <i class="fa fa-filter me-2"></i> Filter
            </button>
            <button type="button" class="btn btn-outline-success px-3 py-1" id="exportExcel">
            <i class="fa fa-file-excel-o"></i> Export Excel
            </button>
            <button type="button" class="btn btn-outline-success px-3 py-1" id="exportImage">
            <i class="fa fa-image me-2"></i> Export Gambar
            </button>
        </div>
    </form>
</div>

    <!-- Auto Sync WA Trigger -->
<script>
$(document).ready(function() {
    // Fungsi untuk memicu pengiriman WA di antrian
    function triggerSyncWA() {
        $.ajax({
            url: '<?= base_url("syncwa/public") ?>',
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                // Jika masih ada antrian (status success dari syncwa), panggil lagi sampai habis
                if(res.status === 'success' || res.message.includes('terkirim')) {
                    setTimeout(triggerSyncWA, 2000); // Tunggu 2 detik lalu proses antrian berikutnya
                }
            },
            error: function() {
                // Jika error (mungkin internet putus), coba lagi nanti
                setTimeout(triggerSyncWA, 30000); 
            }
        });
    }

    // Jalankan pemicu otomatis saat dashboard dibuka
    triggerSyncWA();
});
</script>
</body>
</html>

    <style>
        .filter-bar input[type="date"]::-webkit-input-placeholder { color: #198754; }
        .filter-bar input[type="date"]::-moz-placeholder { color: #198754; }
        .filter-bar input[type="date"]:-ms-input-placeholder { color: #198754; }
        .filter-bar input[type="date"]::placeholder { color: #198754; }
        .filter-bar input[type="date"]:focus {
            border-color: #00b86b;
            box-shadow: 0 0 0 0.15rem rgba(0,184,107,.15);
        }
        .filter-bar label {
            letter-spacing: 0.5px;
        }
        .export-btns .btn {
            transition: background 0.18s, color 0.18s;
        }
        .export-btns .btn:hover {
            background: #00b86b !important;
            color: #fff !important;
            border-color: #00b86b !important;
        }
        @media (max-width: 767.98px) {
            .filter-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 1rem !important;
                padding: 14px 8px !important;
            }
            .export-btns {
                justify-content: flex-start !important;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        // Export Excel
        document.getElementById('exportExcel').onclick = function() {
            var table = document.querySelector('.table');
            var wb = XLSX.utils.table_to_book(table, {sheet:"Statistik IPL"});
            XLSX.writeFile(wb, 'statistik_ipl.xlsx');
        };
        // Export Image
        document.getElementById('exportImage').onclick = function() {
            html2canvas(document.querySelector('.card-body')).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'statistik_ipl.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        };
        // Filter tanggal reload page
        document.getElementById('filterForm').onsubmit = function() {
            // Let form submit normally (GET)
        };
    </script>
    <?php
    // Filter tanggal PHP
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $statistik = [];
    $period = new DatePeriod(
        new DateTime($start_date),
        new DateInterval('P1M'),
        (new DateTime($end_date))->modify('+1 month')
    );
    foreach ($period as $dt) {
        $bulan = $dt->format('n');
        $tahun = $dt->format('Y');
        $this->db->where('status', 'verified');
        $this->db->where('MONTH(tanggal_bayar)', $bulan);
        $this->db->where('YEAR(tanggal_bayar)', $tahun);
        $this->db->where('tanggal_bayar >=', $start_date);
        $this->db->where('tanggal_bayar <=', $end_date);
        $this->db->select_sum('jumlah_bayar');
        $total = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
        $statistik[] = [
            'bulan' => $dt->format('M Y'),
            'total' => $total ? $total : 0
        ];
    }
    ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex flex-wrap align-items-center justify-content-between">
                    <span class="fw-bold fs-5 text-white"><i class="fa fa-calendar-check me-2"></i>Statistik Pembayaran IPL Per Bulan</span>
                    <span class="d-none d-md-inline text-white-50 fs-6">Periode: <?= date('d M Y', strtotime($start_date)); ?> - <?= date('d M Y', strtotime($end_date)); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-striped align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <th><?= $s['bulan']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <td>
                                            <span class="fw-bold text-success">
                                                Rp <?= number_format($s['total'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <canvas id="statistikChartFilter" height="80"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx2 = document.getElementById('statistikChartFilter').getContext('2d');
                        new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($statistik, 'bulan')); ?>,
                                datasets: [{
                                    label: 'Total IPL (Rp)',
                                    data: <?= json_encode(array_column($statistik, 'total')); ?>,
                                    backgroundColor: 'rgba(0,141,76,0.7)',
                                    borderColor: 'rgba(0,141,76,1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Statistik Pembayaran IPL Per Bulan</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Ambil data 12 bulan terakhir
                    $bulan_ini = date('n');
                    $tahun_ini = date('Y');
                    $statistik = [];
                    for ($i = 11; $i >= 0; $i--) {
                        $bulan = $bulan_ini - $i;
                        $tahun = $tahun_ini;
                        if ($bulan <= 0) {
                            $bulan += 12;
                            $tahun -= 1;
                        }
                        $this->db->where('status', 'verified');
                        $this->db->where('MONTH(tanggal_bayar)', $bulan);
                        $this->db->where('YEAR(tanggal_bayar)', $tahun);
                        $this->db->select_sum('jumlah_bayar');
                        $total = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
                        $statistik[] = [
                            'bulan' => date('M Y', strtotime("$tahun-$bulan-01")),
                            'total' => $total ? $total : 0
                        ];
                    }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <th><?= $s['bulan']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <td>
                                            <span class="fw-bold text-success">
                                                Rp <?= number_format($s['total'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Optional: Chart.js for better visualization -->
                    <canvas id="statistikChart" height="80"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('statistikChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($statistik, 'bulan')); ?>,
                                datasets: [{
                                    label: 'Total IPL (Rp)',
                                    data: <?= json_encode(array_column($statistik, 'total')); ?>,
                                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($_SESSION['username']['role'] == 'bendahara') : ?>
    <?php
    $bulan_ini = date('m');
    $tahun_ini = date('Y');
    $nama_bulan_ini = [
        '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
        '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
        '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
    ][date('m')];

    // 1. Total Kas Masuk IPL Bulan Ini (Verified)
    $masuk_bulan_ini = $this->db->query("
        SELECT COALESCE(SUM(jumlah_bayar), 0) as total, COUNT(*) as transaksi
        FROM master_pembayaran
        WHERE status = 'verified'
        AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?
    ", [$bulan_ini, $tahun_ini])->row();
    $kas_masuk_bln = (float)($masuk_bulan_ini->total ?? 0);
    $tx_masuk_bln = (int)($masuk_bulan_ini->transaksi ?? 0);

    // 2. Total Pengeluaran Kas Bulan Ini
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

    // 3. Saldo Kas Kumulatif (All-Time Pemasukan - All-Time Pengeluaran)
    $pemasukan_all = (float)($this->db->query("SELECT COALESCE(SUM(jumlah_bayar), 0) as total FROM master_pembayaran WHERE status = 'verified'")->row()->total ?? 0);
    $pengeluaran_all = 0;
    if ($this->db->table_exists('t_pengeluaran')) {
        $pengeluaran_all = (float)($this->db->query("SELECT COALESCE(SUM(nominal), 0) as total FROM t_pengeluaran WHERE active_status = 1")->row()->total ?? 0);
    }
    $saldo_kas_aktif = $pemasukan_all - $pengeluaran_all;

    // 4. Antrian Pembayaran Pending (Menunggu Verifikasi)
    $pending_data = $this->db->query("
        SELECT COUNT(*) as total, COALESCE(SUM(jumlah_bayar), 0) as nominal
        FROM master_pembayaran
        WHERE status = 'pending'
    ")->row();
    $jml_pending = (int)($pending_data->total ?? 0);
    $nom_pending = (float)($pending_data->nominal ?? 0);

    // 5. Total Rumah & Kepatuhan Bulan Ini
    $total_rumah = (int)$this->db->count_all('master_rumah');
    $rumah_bayar_bln = (int)($this->db->query("
        SELECT COUNT(DISTINCT id_rumah) as total
        FROM master_pembayaran
        WHERE status = 'verified'
        AND MONTH(tanggal_bayar) = ? AND YEAR(tanggal_bayar) = ?
    ", [$bulan_ini, $tahun_ini])->row()->total ?? 0);
    $rumah_nunggak_bln = max(0, $total_rumah - $rumah_bayar_bln);

    // 6. Monitoring Setoran per Koordinator Bulan Ini
    $bulan_str = date('Y-m');
    $koor_monitor = $this->db->query("
        SELECT k.id, k.nama, k.no_hp,
               COUNT(r.id) as jml_rumah,
               COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN 1 ELSE 0 END), 0) as sudah_bayar,
               COALESCE(SUM(CASE WHEN p.id IS NOT NULL THEN p.jumlah_bayar ELSE 0 END), 0) as nominal_terkumpul
        FROM master_koordinator_blok k
        LEFT JOIN master_rumah r ON r.id_koordinator = k.id
        LEFT JOIN (
            SELECT DISTINCT p.id, u.id_rumah, p.jumlah_bayar
            FROM master_pembayaran p
            JOIN master_users u ON u.id = p.user_id
            WHERE p.status IN ('verified', 'pending')
            AND (
                DATE_FORMAT(p.untuk_bulan, '%Y-%m') = '$bulan_str'
                OR FIND_IN_SET('$bulan_str', p.bulan_rapel) > 0
                OR (
                    DATE_FORMAT(p.bulan_mulai, '%Y-%m') = '$bulan_str'
                    AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
                    AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
                )
            )
        ) p ON p.id_rumah = r.id
        WHERE r.id IS NOT NULL
        GROUP BY k.id, k.nama, k.no_hp
        ORDER BY sudah_bayar DESC, k.nama ASC
    ")->result_array();
    ?>

    <div class="bendahara-dashboard">
        <!-- Hero Header Bendahara -->
        <div class="card border-0 mb-4 position-relative overflow-hidden text-white" 
             style="background: linear-gradient(135deg, #0f766e 0%, #047857 100%); border-radius: 20px; box-shadow: 0 10px 25px rgba(4, 120, 87, 0.25);">
            <div class="card-body p-4 position-relative" style="z-index: 2;">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255,255,255,0.15); backdrop-filter: blur(8px);">
                            <i class="ti ti-wallet text-warning"></i>
                            <span class="small fw-semibold text-white">Dashboard Keuangan &amp; Kas &bull; Bendahara Paguyuban TSI</span>
                        </div>
                        <h3 class="fw-bold text-white mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['username']['nama']); ?>! 💼</h3>
                        <p class="text-white-50 mb-0 small">
                            Kelola verifikasi pembayaran iuran, pantau arus kas kas paguyuban, dan monitor setoran koordinator blok.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="badge bg-white text-dark px-3 py-2 fs-6 rounded-pill shadow-sm fw-bold">
                            <i class="ti ti-calendar me-1 text-primary"></i> <?= $nama_bulan_ini ?> <?= $tahun_ini ?>
                        </span>
                    </div>
                </div>
            </div>
            <i class="ti ti-cash position-absolute" style="right: -15px; bottom: -25px; font-size: 10rem; opacity: 0.1;"></i>
        </div>

        <!-- Alert Banner Jika Ada Antrian Pending -->
        <?php if ($jml_pending > 0): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background: #fef3c7;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning text-white" style="width:42px;height:42px;font-size:1.2rem;flex-shrink:0">
                        <i class="ti ti-bell-ringing"></i>
                    </div>
                    <div>
                        <strong class="text-dark d-block">Perhatian: Ada <?= $jml_pending ?> Pembayaran Menunggu Verifikasi Anda!</strong>
                        <small class="text-muted">Total nominal antrian pending: <strong>Rp<?= number_format($nom_pending, 0, ',', '.') ?></strong>.</small>
                    </div>
                </div>
                <a href="<?= base_url('pembayaran/verifikasi-pembayaran'); ?>" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm fw-bold">
                    <i class="ti ti-check me-1"></i> Verifikasi Sekarang &rarr;
                </a>
            </div>
        <?php endif; ?>

        <!-- 4 KPI Finansial Utama -->
        <div class="row g-3 mb-4">
            <!-- 1. Pemasukan IPL Bulan Ini -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #10b981 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#d1fae5;color:#10b981;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-arrow-down-left"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Kas Masuk IPL (Bulan Ini)</span>
                            <h4 class="fw-bold text-success mb-0">Rp<?= number_format($kas_masuk_bln, 0, ',', '.') ?></h4>
                            <small class="text-muted"><?= $tx_masuk_bln ?> transaksi verified</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Pengeluaran Kas Bulan Ini -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #ef4444 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fee2e2;color:#ef4444;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-arrow-up-right"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Pengeluaran (Bulan Ini)</span>
                            <h4 class="fw-bold text-danger mb-0">Rp<?= number_format($keluar_bulan_ini, 0, ',', '.') ?></h4>
                            <small class="text-muted">Operasional &amp; belanja</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Sisa Saldo Kas Aktif Kumulatif -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #0ea5e9 !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#e0f2fe;color:#0ea5e9;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-wallet"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Sisa Saldo Kas Riil</span>
                            <h4 class="fw-bold text-primary mb-0">Rp<?= number_format($saldo_kas_aktif, 0, ',', '.') ?></h4>
                            <small class="text-muted">Saldo bersih kumulatif</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Antrian Pending Verifikasi -->
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100" style="border-left: 4px solid #f59e0b !important;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;background:#fef3c7;color:#f59e0b;font-size:1.4rem;flex-shrink:0">
                            <i class="ti ti-hourglass"></i>
                        </div>
                        <div>
                            <span class="text-muted small fw-semibold d-block">Menunggu Validasi</span>
                            <h4 class="fw-bold text-warning mb-0"><?= $jml_pending ?> <small class="fs-6 text-muted">antrian</small></h4>
                            <small class="text-muted">Rp<?= number_format($nom_pending, 0, ',', '.') ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Bendahara -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-3"><i class="ti ti-bolt text-warning me-1"></i> Aksi Cepat Bendahara</h6>
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('pembayaran/verifikasi-pembayaran'); ?>" class="btn btn-success w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1 position-relative">
                            <?php if ($jml_pending > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light">
                                    <?= $jml_pending ?>
                                </span>
                            <?php endif; ?>
                            <i class="bi bi-patch-check fs-4"></i>
                            <span class="fw-bold small">Verifikasi Pembayaran</span>
                            <small class="text-white-50" style="font-size:0.72rem"><?= $jml_pending ?> menunggu validasi</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('kas/pengeluaran'); ?>" class="btn btn-danger w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-file-earmark-plus fs-4"></i>
                            <span class="fw-bold small">Catat Pengeluaran</span>
                            <small class="text-white-50" style="font-size:0.72rem">Tambah kas keluar</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('laporan-rekap-rapel'); ?>" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-table fs-4"></i>
                            <span class="fw-bold small">Rekap Pembayaran</span>
                            <small class="text-white-50" style="font-size:0.72rem">Laporan setoran rapel</small>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="<?= base_url('log-verifikasi'); ?>" class="btn btn-info text-white w-100 py-3 rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center gap-1">
                            <i class="bi bi-journal-text fs-4"></i>
                            <span class="fw-bold small">Log Verifikasi</span>
                            <small class="text-white-50" style="font-size:0.72rem">Riwayat aksi &amp; WA</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Kolom Kiri: Monitoring Setoran per Koordinator Blok Bulan Ini -->
            <div class="col-lg-7 mb-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="ti ti-users me-1 text-primary"></i> Progress Setoran Koordinator (<?= $nama_bulan_ini ?>)
                                </h6>
                                <small class="text-muted">Pantau blok mana yang koordinatornya sudah mengumpulkan iuran</small>
                            </div>
                            <a href="<?= base_url('log-koordinator'); ?>" class="btn btn-sm btn-outline-primary rounded-pill">
                                Lihat Lengkap &rarr;
                            </a>
                        </div>

                        <div class="table-responsive" style="max-height: 330px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Koordinator</th>
                                        <th class="text-center">Binaan</th>
                                        <th class="text-center">Lunas</th>
                                        <th class="text-end">Nominal Masuk</th>
                                        <th class="text-center">Capaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($koor_monitor as $km): 
                                        $persen_k = ($km['jml_rumah'] > 0) ? round(($km['sudah_bayar'] / $km['jml_rumah']) * 100) : 0;
                                        $bar_color = $persen_k >= 75 ? 'bg-success' : ($persen_k >= 50 ? 'bg-warning' : 'bg-danger');
                                    ?>
                                        <tr>
                                            <td>
                                                <strong class="text-dark"><?= htmlspecialchars($km['nama']) ?></strong>
                                            </td>
                                            <td class="text-center"><span class="badge bg-light text-dark border"><?= $km['jml_rumah'] ?></span></td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success fw-bold"><?= $km['sudah_bayar'] ?></span>
                                            </td>
                                            <td class="text-end fw-semibold text-dark">
                                                Rp<?= number_format($km['nominal_terkumpul'], 0, ',', '.') ?>
                                            </td>
                                            <td class="text-center" style="min-width:90px;">
                                                <div class="d-flex align-items-center gap-1">
                                                    <div class="progress flex-grow-1" style="height:6px">
                                                        <div class="progress-bar <?= $bar_color ?>" style="width:<?= $persen_k ?>%"></div>
                                                    </div>
                                                    <small class="fw-bold" style="font-size:0.75rem"><?= $persen_k ?>%</small>
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

            <!-- Kolom Kanan: Statistik 6 Bulan Terakhir & Ekspor -->
            <div class="col-lg-5 mb-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="ti ti-chart-bar me-1 text-success"></i> Tren Pemasukan 6 Bulan
                            </h6>
                            <small class="text-muted">Kas IPL Masuk</small>
                        </div>
                        <?php
                        $bulan_ini = date('n');
                        $tahun_ini = date('Y');
                        $statistik = [];
                        for ($i = 5; $i >= 0; $i--) {
                            $b = $bulan_ini - $i;
                            $t = $tahun_ini;
                            if ($b <= 0) {
                                $b += 12;
                                $t -= 1;
                            }
                            $this->db->where('status', 'verified');
                            $this->db->where('MONTH(tanggal_bayar)', $b);
                            $this->db->where('YEAR(tanggal_bayar)', $t);
                            $this->db->select_sum('jumlah_bayar');
                            $tot = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
                            $statistik[] = [
                                'bulan' => date('M Y', strtotime("$t-$b-01")),
                                'total' => $tot ? (float)$tot : 0
                            ];
                        }
                        ?>
                        <div style="position:relative;height:180px">
                            <canvas id="bendaharaChart"></canvas>
                        </div>
                        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                        <script>
                            const ctxB = document.getElementById('bendaharaChart').getContext('2d');
                            new Chart(ctxB, {
                                type: 'bar',
                                data: {
                                    labels: <?= json_encode(array_column($statistik, 'bulan')); ?>,
                                    datasets: [{
                                        label: 'Kas IPL (Rp)',
                                        data: <?= json_encode(array_column($statistik, 'total')); ?>,
                                        backgroundColor: 'rgba(16, 185, 129, 0.75)',
                                        borderColor: 'rgba(16, 185, 129, 1)',
                                        borderWidth: 1,
                                        borderRadius: 6,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                callback: function(val) {
                                                    return 'Rp ' + (val/1000).toLocaleString('id-ID') + 'k';
                                                },
                                                font: { size: 10 }
                                            }
                                        },
                                        x: { ticks: { font: { size: 10 } } }
                                    }
                                }
                            });
                        </script>

                        <!-- Ekspor Cepat -->
                        <div class="mt-3 pt-3 border-top">
                            <span class="small fw-bold text-muted d-block mb-2">Ekspor Laporan Keuangan IPL:</span>
                            <div class="d-flex gap-2">
                                <a href="<?= base_url('laporan-ipl/excel'); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                    <i class="fa fa-file-excel me-1"></i> Unduh Excel
                                </a>
                                <a href="<?= base_url('laporan-ipl/pdf'); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                    <i class="fa fa-file-pdf me-1"></i> Unduh PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
