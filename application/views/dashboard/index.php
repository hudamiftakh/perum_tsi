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
<?php if ($_SESSION['username']['role'] == 'admin' || $_SESSION['username']['role'] == 'bendahara') : ?>
    <?php $this->load->view('dashboard/admin_dashboard'); ?>
<?php endif; ?>





