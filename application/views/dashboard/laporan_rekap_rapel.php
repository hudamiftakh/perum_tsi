<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');

// ============================================
// PARAMETER FILTER
// ============================================
$bulan_indo_short = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
$bulan_indo_full  = [
    '01' => 'Januari',
    '02' => 'Februari',
    '03' => 'Maret',
    '04' => 'April',
    '05' => 'Mei',
    '06' => 'Juni',
    '07' => 'Juli',
    '08' => 'Agustus',
    '09' => 'September',
    '10' => 'Oktober',
    '11' => 'November',
    '12' => 'Desember'
];

$selected_tahun = $this->input->get('tahun');
$selected_tahun = (!empty($selected_tahun)) ? (int)$selected_tahun : (int)date('Y');

$keyword = $this->input->get('keyword');

if ($this->session->userdata('username')['role'] === 'koordinator') {
    $selected_koor = $this->session->userdata('username')['id'];
} else {
    $selected_koor = $this->input->get('id_koordinator');
}

// Rentang bulan — selalu mulai Januari
$bulan_awal   = 1;
$bulan_akhir  = ($selected_tahun == (int)date('Y')) ? (int)date('n') : 12;

// ============================================
// DATA RUMAH (sama persis logika laporan_pembayaran.php)
// ============================================
$filter = [];
if (!empty($selected_koor)) {
    $filter[] = "vb.id_koordinator = '" . $this->db->escape_str($selected_koor) . "'";
}
if (!empty($keyword)) {
    $filter[] = "(vb.nama LIKE '%" . $this->db->escape_str($keyword) . "%' OR vb.alamat LIKE '%" . $this->db->escape_str($keyword) . "%')";
}
$whereClause = !empty($filter) ? 'WHERE ' . implode(' AND ', $filter) : '';

$rumah = $this->db->query(
    "
    SELECT vb.* FROM (
        SELECT DISTINCT b.id, b.alamat, b.nama, c.nama as koordinator, b.id_koordinator
        FROM master_users as a
        LEFT JOIN master_rumah as b ON a.id_rumah = b.id
        LEFT JOIN master_koordinator_blok as c ON b.id_koordinator = c.id
        ORDER BY b.alamat ASC
    ) as vb " . $whereClause
)->result_array();

// Koordinator dropdown
if ($this->session->userdata('username')['role'] === 'koordinator') {
    $koordinator_list = $this->db->query(
        "SELECT id, nama FROM master_koordinator_blok WHERE id = '" . $this->db->escape_str($this->session->userdata('username')['id']) . "'"
    )->result_array();
} else {
    $koordinator_list = $this->db->query("SELECT DISTINCT id, nama FROM master_koordinator_blok ORDER BY nama")->result_array();
}

// ============================================
// TOTAL PER BULAN
// total_ipl_bulan : per bulan IPL (kewajiban) — sama seperti laporan lama
// total_tgl_bayar : per bulan uang DITERIMA (tanggal_bayar) — anti double-count
// ============================================
$total_tgl_bayar = [];   // uang masuk per bulan tanggal_bayar
$total_ipl_bulan = [];   // kewajiban IPL terpenuhi per bulan
$counted_ids     = [];   // mencegah payment rapel dihitung berkali-kali
for ($i = $bulan_awal; $i <= $bulan_akhir; $i++) {
    $total_tgl_bayar[$i] = 0;
    $total_ipl_bulan[$i] = 0;
}

// ============================================
// SUMMARY UNTUK CARD — query langsung
// ============================================
$where_koor_card = !empty($selected_koor)
    ? "AND b.id_koordinator = '" . $this->db->escape_str($selected_koor) . "'"
    : '';
$thn_awal_str = $selected_tahun . '-01-01';
$thn_akhir_str = $selected_tahun . '-12-31';

// Grand Total IPL (verified, bulan kewajiban dalam tahun ini)
$card_total_ipl = $this->db->query("
    SELECT COALESCE(SUM(a.jumlah_bayar),0) as total
    FROM master_pembayaran a
    LEFT JOIN master_users u ON a.user_id = u.id
    LEFT JOIN master_rumah b ON u.id_rumah = b.id
    WHERE a.status = 'verified'
    AND a.untuk_bulan BETWEEN '$thn_awal_str' AND '$thn_akhir_str'
    $where_koor_card
")->row();
$grand_total_ipl_card = (float)($card_total_ipl->total ?? 0);

// Grand Total Kas Masuk (verified+pending dengan tanggal_bayar, berdasarkan tanggal_bayar tahun ini)
$card_total_kas = $this->db->query("
    SELECT COALESCE(SUM(a.jumlah_bayar),0) as total
    FROM master_pembayaran a
    LEFT JOIN master_users u ON a.user_id = u.id
    LEFT JOIN master_rumah b ON u.id_rumah = b.id
    WHERE a.status IN ('verified','pending')
    AND a.tanggal_bayar BETWEEN '$thn_awal_str' AND '$thn_akhir_str'
    $where_koor_card
")->row();
$grand_total_kas_card = (float)($card_total_kas->total ?? 0);

// Jumlah rumah yang sudah lunas
$card_lunas = $this->db->query("
    SELECT COUNT(DISTINCT u.id_rumah) as total
    FROM master_pembayaran a
    LEFT JOIN master_users u ON a.user_id = u.id
    LEFT JOIN master_rumah b ON u.id_rumah = b.id
    WHERE a.status = 'verified'
    AND a.untuk_bulan BETWEEN '$thn_awal_str' AND '$thn_akhir_str'
    $where_koor_card
")->row();
$jumlah_lunas_card = (int)($card_lunas->total ?? 0);

// ============================================
// TOTAL PER METODE PEMBAYARAN (Transfer vs Koordinator)
// ============================================
$bulan_ini_str = date('Y-m');
$thn_ini = date('Y');
$bln_ini = date('m');

// Helper query function
$get_total_via = function ($via, $bulan = null, $tahun = null) use ($where_koor_card) {
    $CI = &get_instance();
    $CI->db->select("COALESCE(SUM(a.jumlah_bayar),0) as total");
    $CI->db->from('master_pembayaran a');
    $CI->db->join('master_users u', 'u.id = a.user_id', 'left');
    $CI->db->join('master_rumah b', 'u.id_rumah = b.id', 'left');
    $CI->db->where('a.status', 'verified');
    $CI->db->where('a.pembayaran_via', $via);

    if ($bulan && $tahun) {
        $CI->db->where('MONTH(a.tanggal_bayar)', $bulan);
        $CI->db->where('YEAR(a.tanggal_bayar)', $tahun);
    } else if ($tahun) {
        $CI->db->where('YEAR(a.tanggal_bayar)', $tahun);
    }

    if (!empty($where_koor_card)) {
        $CI->db->where('b.id_koordinator', trim(str_replace("AND b.id_koordinator = ", "", str_replace("'", "", $where_koor_card))));
    }

    return (float)($CI->db->get()->row()->total ?? 0);
};

// Bulan ini
$card_koor_bulan_ini = $get_total_via('koordinator', $bln_ini, $thn_ini);
$card_transfer_bulan_ini = $get_total_via('transfer', $bln_ini, $thn_ini);

// Sampai dengan (akumulasi tahun terpilih)
$card_koor_sd = $get_total_via('koordinator', null, $selected_tahun);
$card_transfer_sd = $get_total_via('transfer', null, $selected_tahun);
?>

<style>
    table {
        border-radius: 15px;
        overflow: hidden;
    }

    .table thead th {
        vertical-align: middle;
        text-align: center;
    }

    .table th {
        text-align: center;
    }

    .bulan-tags span {
        display: inline-block;
        background: #e8f4fd;
        border: 1px solid #bee3f8;
        color: #2c6ea1;
        font-size: .7rem;
        border-radius: 4px;
        padding: 1px 5px;
        margin: 1px;
    }

    /* Warna cell sama persis dengan laporan_pembayaran.php (Bootstrap subtle) */
</style>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<h5 class="mb-4 fw-semibold text-center">Rekap Pembayaran Rapel <?= $selected_tahun ?></h5>


<!-- KARTU RINCIAN METODE PEMBAYARAN -->
<div class="row mb-4">
    <!-- Total Koordinator SD -->
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-secondary shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-person-badge-fill text-primary display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Total Koordinator</h6>
                    <h5 class="fw-bold text-primary">Rp <?= number_format($card_koor_sd) ?></h5>
                    <small class="text-muted">Akumulasi <?= $selected_tahun ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Transfer SD -->
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-success shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-bank2 text-success display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Total Transfer</h6>
                    <h5 class="fw-bold text-success">Rp <?= number_format($card_transfer_sd) ?></h5>
                    <small class="text-muted">Akumulasi <?= $selected_tahun ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Koordinator Bulan Ini -->
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-warning shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-calendar-check text-warning display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Koordinator Bulan Ini</h6>
                    <h5 class="fw-bold text-warning">Rp <?= number_format($card_koor_bulan_ini) ?></h5>
                    <small class="text-muted">Periode bulan berjalan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Bulan Ini -->
    <div class="col-12 col-md-6 col-lg-3 mb-3">
        <div class="card border border-danger shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-cash-coin text-danger display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Transfer Bulan Ini</h6>
                    <h5 class="fw-bold text-danger">Rp <?= number_format($card_transfer_bulan_ini) ?></h5>
                    <small class="text-muted">Periode bulan berjalan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM FILTER — sama persis laporan_pembayaran.php -->
<form method="get" action="<?= base_url('laporan-rekap-rapel') ?>" class="mb-3 p-3 border rounded shadow-sm" style="background-color: #f0f8ff;">
    <div class="row gy-2 gx-3 align-items-end">

        <!-- Input Pencarian -->
        <div class="col-12 col-md-3">
            <input type="text" name="keyword" class="form-control rounded" placeholder="Cari NIK atau Nama..."
                style="background-color: white; color: black;"
                value="<?= htmlspecialchars($keyword ?? '') ?>">
        </div>

        <!-- Filter Tahun -->
        <div class="col-6 col-md-2">
            <select name="tahun" class="form-select rounded" style="background-color: white; color: black;">
                <option value="">Pilih Tahun</option>
                <?php for ($y = 2025; $y <= (int)date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $selected_tahun == $y ? 'selected' : '' ?>>
                        <?= $y ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Filter Koordinator -->
        <div class="col-6 col-md-2">
            <select name="id_koordinator" class="form-select rounded" style="background-color: white; color: black;">
                <?php if ($this->session->userdata('username')['role'] === 'admin'): ?>
                    <option value="">Koordinator Blok</option>
                <?php endif; ?>
                <?php foreach ($koordinator_list as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $selected_koor == $k['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($k['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-auto">
            <div class="d-flex flex-wrap gap-2 justify-content-start">
                <!-- Cari -->
                <button class="btn btn-success" type="submit">
                    <i class="fa fa-search me-1"></i> Cari
                </button>

                <!-- Reset -->
                <?php if (!empty($keyword) || !empty($selected_koor) || !empty($this->input->get('tahun'))): ?>
                    <a href="<?= base_url('laporan-rekap-rapel') ?>" class="btn btn-outline-danger">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                <?php endif; ?>

                <!-- Kembali ke Laporan Biasa -->
                <!-- <a href="<?= base_url('warga/laporan-pembayaran?tahun=' . $selected_tahun . (!empty($selected_koor) ? '&id_koordinator=' . $selected_koor : '')) ?>"
                    class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Laporan Biasa
                </a> -->

                <!-- PDF Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-danger dropdown-toggle" type="button"
                        id="pdfRapelDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Rekap Rapel PDF
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="pdfRapelDropdown">
                        <?php
                        $all_months_r = [
                            '01' => 'Januari',
                            '02' => 'Februari',
                            '03' => 'Maret',
                            '04' => 'April',
                            '05' => 'Mei',
                            '06' => 'Juni',
                            '07' => 'Juli',
                            '08' => 'Agustus',
                            '09' => 'September',
                            '10' => 'Oktober',
                            '11' => 'November',
                            '12' => 'Desember'
                        ];
                        $start_m = ($selected_tahun == 2025) ? '06' : '01';
                        foreach ($all_months_r as $km => $bm):
                            if ($km < $start_m) continue; ?>
                            <li>
                                <a class="dropdown-item"
                                    href="<?= base_url('pembayaran/laporan-rekap-rapel-pdf?bulan=' . $km . '&tahun=' . $selected_tahun . '&id_koordinator=' . encrypt_url($selected_koor)) ?>"
                                    target="_blank">
                                    <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                    <?= $bm . ' ' . $selected_tahun ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</form>

<!-- ==================
     TABEL UTAMA
     Baris = Rumah, Kolom = Bulan (sama persis laporan_pembayaran.php)
     ================== -->
<style>
    th.shrink,
    td.shrink {
        width: 1px !important;
        white-space: nowrap;
    }

    @media(min-width:768px) {

        th.shrink,
        td.shrink {
            width: auto !important;
            white-space: normal;
        }
    }
</style>

<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle" id="rekapRapelTable">
        <thead style="background:linear-gradient(to right,#1a5276,#2471a3) !important; color:white !important; vertical-align:middle !important;">
            <tr>
                <th class="shrink text-white text-center">No</th>
                <th class="shrink text-white">Rumah</th>
                <?php for ($i = $bulan_awal; $i <= $bulan_akhir; $i++): ?>
                    <th class="shrink text-white text-center"><?= $bulan_indo_short[$i] ?></th>
                <?php endfor; ?>
                <th class="text-white" width="1px" nowrap>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($rumah as $data_rumah):
                $id_rumah = $data_rumah['id'];
            ?>
                <tr>
                    <!-- No -->
                    <td class="text-center" style="width:1px;">
                        <div style="width:32px;height:32px;background:#1a5276;color:white;font-weight:bold;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,.1);">
                            <?= $no++ ?>
                        </div>
                    </td>
                    <!-- Nama Rumah -->
                    <td style="min-width:120px; padding:6px 8px;">
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill text-danger me-2" style="font-size:1.2rem;"></i>
                                <span class="text-truncate" style="max-width:150px; font-size:.95rem;"><?= htmlspecialchars($data_rumah['alamat']) ?></span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-fill text-primary me-2" style="font-size:1.2rem;"></i>
                                <span class="text-truncate fw-semibold" style="max-width:150px; font-size:1rem; text-transform:uppercase;"><?= strtoupper(htmlspecialchars($data_rumah['nama'])) ?></span>
                            </div>
                        </div>
                    </td>

                    <!-- Kolom per Bulan -->
                    <?php for ($i = $bulan_awal; $i <= $bulan_akhir; $i++):
                        $currentMonthStr = sprintf('%04d-%02d', $selected_tahun, $i);

                        // Query pembayaran untuk bulan IPL ini pada rumah ini
                        // FIX bug Bp Roni: DATE_FORMAT(bulan_mulai) hanya dipakai jika BUKAN rapel.
                        // Kasus: bayar Maret untuk Jan+Feb → bulan_mulai=Maret, bulan_rapel='2026-01,2026-02'
                        // Tanpa fix: Maret ikut muncul karena bulan_mulai=Maret cocok dengan currentMonthStr=Maret
                        // Dengan fix: Maret hanya muncul jika bulan_rapel kosong (pembayaran normal/tunggal)
                        $dp = $this->db->query("
                        SELECT a.*, b.id_rumah, a.id as id_pembayaran
                        FROM master_pembayaran as a
                        LEFT JOIN master_users as b ON a.user_id = b.id
                        WHERE b.id_rumah = '$id_rumah'
                        AND (
                            a.untuk_bulan = '$currentMonthStr'
                            OR FIND_IN_SET('$currentMonthStr', a.bulan_rapel)
                            OR (
                                DATE_FORMAT(a.bulan_mulai, '%Y-%m') = '$currentMonthStr'
                                AND (a.bulan_rapel IS NULL OR a.bulan_rapel = '')
                            )
                        )
                        ORDER BY a.status DESC
                        LIMIT 1
                    ")->row_array();

                        // Tentukan class cell & isi
                        if (!$dp) {
                            $cell_class = 'bg-danger-subtle text-danger';
                        } elseif ($dp['status'] == 'verified') {
                            // Cek apakah ini rapel (bulan_rapel tidak kosong = multi-bulan)
                            $is_rapel  = !empty($dp['bulan_rapel']);
                            // Cek bayar dimuka
                            $mulai_ym  = !empty($dp['bulan_mulai']) ? date('Y-m', strtotime($dp['bulan_mulai'])) : '';
                            $untuk_ym  = !empty($dp['untuk_bulan']) ? date('Y-m', strtotime($dp['untuk_bulan'])) : '';
                            $is_dimuka = ($mulai_ym && $untuk_ym && $mulai_ym < $untuk_ym);

                            if ($is_rapel)       $cell_class = 'bg-success-subtle text-secondary';
                            elseif ($is_dimuka)  $cell_class = 'bg-info-subtle text-info';
                            else                 $cell_class = 'bg-success-subtle text-success';

                            // TOTAL IPL: dihitung per bulan kewajiban
                            // Untuk rapel, bagi rata jumlah_bayar sesuai jumlah bulan rapel
                            $ipl_amount = (float)$dp['jumlah_bayar'];
                            if (!empty($dp['bulan_rapel'])) {
                                $rapel_count = count(explode(',', $dp['bulan_rapel']));
                                if ($rapel_count > 1) {
                                    $ipl_amount = $ipl_amount / $rapel_count;
                                }
                            }
                            $total_ipl_bulan[$i] += $ipl_amount;

                            // TOTAL TGL BAYAR: hanya dihitung SEKALI per ID payment
                            // Kasus 1 - Rapel   : bayar Maret untuk Jan+Feb → masuk ke kolom Maret
                            // Kasus 2 - Dimuka  : bayar Januari untuk Mar+Apr → masuk ke kolom Januari
                            // Edge case: jika tanggal_bayar kosong atau tahun berbeda → skip
                            $pay_id_key = $id_rumah . '_' . $dp['id_pembayaran'];
                            if (!in_array($pay_id_key, $counted_ids) && !empty($dp['tanggal_bayar'])) {
                                $counted_ids[]  = $pay_id_key;
                                $tgl_bayar_ts   = strtotime($dp['tanggal_bayar']);
                                $tahun_bayar    = (int)date('Y', $tgl_bayar_ts);
                                $bulan_bayar_i  = (int)date('n', $tgl_bayar_ts);
                                // Hanya hitung jika tahun tanggal_bayar = tahun filter
                                if (
                                    $tahun_bayar == $selected_tahun
                                    && $bulan_bayar_i >= $bulan_awal
                                    && $bulan_bayar_i <= $bulan_akhir
                                ) {
                                    $total_tgl_bayar[$bulan_bayar_i] += (float)$dp['jumlah_bayar'];
                                }
                            }
                        } elseif ($dp['status'] == 'pending') {
                            $cell_class = 'bg-warning-subtle text-warning';
                        } else {
                            $cell_class = 'bg-danger-subtle text-danger';
                        }

                        $modal_id = 'm_' . $id_rumah . '_' . $i;
                    ?>
                        <td class="text-center <?= $cell_class ?>" style="padding:4px;">
                            <?php if (!$dp): ?>
                                <div class="d-flex flex-column align-items-center">
                                    <span style="color:#e74c3c; font-size:.78rem;">❌ Belum</span>
                                </div>
                            <?php elseif ($dp['status'] == 'verified'): ?>
                                <?php
                                $is_rapel  = !empty($dp['bulan_rapel']);
                                $mulai_ym  = !empty($dp['bulan_mulai']) ? date('Y-m', strtotime($dp['bulan_mulai'])) : '';
                                $untuk_ym  = !empty($dp['untuk_bulan']) ? date('Y-m', strtotime($dp['untuk_bulan'])) : '';
                                $is_dimuka = ($mulai_ym && $untuk_ym && $mulai_ym < $untuk_ym);
                                ?>
                                <div class="d-flex flex-column align-items-center" style="cursor:pointer;"
                                    data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>">
                                    <?php if ($currentMonthStr == $mulai_ym && !$is_rapel && !$is_dimuka): ?>
                                        <!-- Pembayaran normal: tampilkan nominal -->
                                        <strong style="font-size:.8rem;"><?= number_format($dp['jumlah_bayar']) ?></strong>
                                        <small style="color:#888; font-size:.68rem;"><?= !empty($dp['tanggal_bayar']) ? date('d/m/y', strtotime($dp['tanggal_bayar'])) : '' ?></small>
                                    <?php elseif ($is_rapel): ?>
                                        <?php
                                        $bulan_indo_s = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                        $bayar_bulan = !empty($dp['tanggal_bayar']) ? (int)date('n', strtotime($dp['tanggal_bayar'])) : 0;
                                        $bayar_label = ($bayar_bulan > 0 && $bayar_bulan != $i) ? 'Bayar: ' . $bulan_indo_s[$bayar_bulan] : '';
                                        ?>
                                        <span class="text-primary fs-6"><i class="bi bi-arrow-repeat"></i></span>
                                        <small style="font-size:.7rem; color:#4527a0; font-weight:600;">Rapel</small>
                                        <?php if ($bayar_label): ?>
                                            <small style="font-size:.62rem; color:#e65100; font-weight:600;"><?= $bayar_label ?></small>
                                        <?php endif; ?>
                                        <small style="color:#888; font-size:.65rem;"><?= !empty($dp['tanggal_bayar']) ? date('d/m/y', strtotime($dp['tanggal_bayar'])) : '' ?></small>
                                    <?php elseif ($is_dimuka): ?>
                                        <span class="text-info fs-6"><i class="bi bi-arrow-up-right-circle-fill"></i></span>
                                        <small style="font-size:.7rem; color:#0c5460;">Dimuka</small>
                                    <?php else: ?>
                                        <span style="color:green; font-size:.8rem;">✅</span>
                                        <small style="color:#888; font-size:.68rem;"><?= !empty($dp['tanggal_bayar']) ? date('d/m/y', strtotime($dp['tanggal_bayar'])) : '' ?></small>
                                    <?php endif; ?>
                                </div>

                                <!-- Modal detail -->
                                <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Pembayaran</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p>
                                                    <strong style="color:black;"><?= htmlspecialchars($data_rumah['nama']) ?></strong><br>
                                                    <span class="text-muted"><?= htmlspecialchars($data_rumah['alamat']) ?></span>
                                                </p>
                                                <table class="table table-sm table-bordered text-start">
                                                    <tr>
                                                        <td class="fw-semibold">Nominal</td>
                                                        <td>Rp <?= number_format($dp['jumlah_bayar']) ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Tgl Bayar</td>
                                                        <td>
                                                            <strong style="color:#1a5276;">
                                                                <?= !empty($dp['tanggal_bayar']) ? date('d/m/Y', strtotime($dp['tanggal_bayar'])) : '-' ?>
                                                            </strong>
                                                            <?php if ($is_rapel): ?>
                                                                <span class="badge bg-primary-subtle text-primary ms-1">Rapel</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Status</td>
                                                        <td>
                                                            <span class="badge bg-success">✅ Verified</span>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Via</td>
                                                        <td><?= htmlspecialchars($dp['pembayaran_via'] ?? '-') ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Bulan IPL</td>
                                                        <td>
                                                            <?php
                                                            // Daftar bulan dicakup
                                                            $all_bln = [];
                                                            if (!empty($dp['untuk_bulan'])) $all_bln[] = $dp['untuk_bulan'];
                                                            if (!empty($dp['bulan_rapel'])) {
                                                                foreach (explode(',', $dp['bulan_rapel']) as $br) {
                                                                    $br = trim($br);
                                                                    if ($br && !in_array($br, $all_bln)) $all_bln[] = $br;
                                                                }
                                                            }
                                                            $bln_labels = [];
                                                            foreach ($all_bln as $bln) {
                                                                $bln = trim($bln);
                                                                $dt = false;
                                                                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $bln)) $dt = DateTime::createFromFormat('Y-m-d', $bln);
                                                                elseif (preg_match('/^\d{4}-\d{2}$/', $bln)) $dt = DateTime::createFromFormat('Y-m', $bln);
                                                                elseif (preg_match('/^\d{2}-\d{4}$/', $bln)) $dt = DateTime::createFromFormat('m-Y', $bln);
                                                                if ($dt) {
                                                                    $mn = str_pad($dt->format('n'), 2, '0', STR_PAD_LEFT);
                                                                    $key = $dt->format('Y-m');
                                                                    $bln_labels[$key] = ($bulan_indo_full[$mn] ?? $mn) . ' ' . $dt->format('Y');
                                                                }
                                                            }
                                                            ksort($bln_labels);
                                                            foreach ($bln_labels as $lbl): ?>
                                                                <span class="bulan-tags"><span><?= htmlspecialchars($lbl) ?></span></span>
                                                            <?php endforeach; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <a href="<?= base_url('pembayaran/' . encrypt_url($dp['id_rumah']) . '/' . encrypt_url($dp['id_pembayaran'])) ?>"
                                                    class="btn btn-warning">
                                                    <i class="bi bi-pencil-square"></i> Revisi
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php elseif ($dp['status'] == 'pending'): ?>
                                <?php
                                $is_rapel_p = !empty($dp['bulan_rapel']);
                                $bulan_indo_s2 = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                $bayar_bulan_p = !empty($dp['tanggal_bayar']) ? (int)date('n', strtotime($dp['tanggal_bayar'])) : 0;
                                $bayar_label_p = ($is_rapel_p && $bayar_bulan_p > 0 && $bayar_bulan_p != $i) ? 'Bayar: ' . $bulan_indo_s2[$bayar_bulan_p] : '';
                                ?>
                                <div class="d-flex flex-column align-items-center" style="cursor:pointer;"
                                    data-bs-toggle="modal" data-bs-target="#<?= $modal_id ?>">
                                    <span style="font-size:.75rem;">⏳ Pending</span>
                                    <?php if ($is_rapel_p): ?>
                                        <small style="font-size:.65rem; color:#4527a0; font-weight:600;">Rapel</small>
                                    <?php endif; ?>
                                    <?php if ($bayar_label_p): ?>
                                        <small style="font-size:.62rem; color:#e65100; font-weight:600;"><?= $bayar_label_p ?></small>
                                    <?php endif; ?>
                                    <small style="color:#888; font-size:.65rem;"><?= !empty($dp['tanggal_bayar']) ? date('d/m/y', strtotime($dp['tanggal_bayar'])) : '' ?></small>
                                </div>
                                <!-- Modal pending -->
                                <div class="modal fade" id="<?= $modal_id ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning-subtle">
                                                <h5 class="modal-title"><i class="bi bi-hourglass-split me-2 text-warning"></i>Pembayaran Pending</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="text-center mb-3">
                                                    <strong style="color:black;"><?= htmlspecialchars($data_rumah['nama']) ?></strong><br>
                                                    <span class="text-muted"><?= htmlspecialchars($data_rumah['alamat']) ?></span>
                                                </p>
                                                <table class="table table-sm table-bordered text-start">
                                                    <tr>
                                                        <td class="fw-semibold" style="width:38%">Nominal</td>
                                                        <td><strong>Rp <?= number_format($dp['jumlah_bayar']) ?></strong></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Tgl Bayar</td>
                                                        <td><?= !empty($dp['tanggal_bayar']) ? '<strong style="color:#1a5276;">' . date('d/m/Y', strtotime($dp['tanggal_bayar'])) . '</strong>' : '<span class="text-muted">-</span>' ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Via</td>
                                                        <td><?= htmlspecialchars($dp['pembayaran_via'] ?? '-') ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Status</td>
                                                        <td><span class="badge bg-warning text-dark">⏳ Menunggu Verifikasi</span></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-semibold">Bulan IPL</td>
                                                        <td>
                                                            <?php
                                                            $all_bln_p = [];
                                                            if (!empty($dp['untuk_bulan'])) $all_bln_p[] = $dp['untuk_bulan'];
                                                            if (!empty($dp['bulan_rapel'])) {
                                                                foreach (explode(',', $dp['bulan_rapel']) as $br) {
                                                                    $br = trim($br);
                                                                    if ($br && !in_array($br, $all_bln_p)) $all_bln_p[] = $br;
                                                                }
                                                            }
                                                            $bln_labels_p = [];
                                                            foreach ($all_bln_p as $bln) {
                                                                $bln = trim($bln);
                                                                $dt  = false;
                                                                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $bln)) $dt = DateTime::createFromFormat('Y-m-d', $bln);
                                                                elseif (preg_match('/^\d{4}-\d{2}$/', $bln))   $dt = DateTime::createFromFormat('Y-m', $bln);
                                                                elseif (preg_match('/^\d{2}-\d{4}$/', $bln))   $dt = DateTime::createFromFormat('m-Y', $bln);
                                                                if ($dt) {
                                                                    $mn = str_pad($dt->format('n'), 2, '0', STR_PAD_LEFT);
                                                                    $bln_labels_p[$dt->format('Y-m')] = ($bulan_indo_full[$mn] ?? $mn) . ' ' . $dt->format('Y');
                                                                }
                                                            }
                                                            ksort($bln_labels_p);
                                                            if (!empty($bln_labels_p)):
                                                                foreach ($bln_labels_p as $lbl): ?>
                                                                    <span class="bulan-tags"><span><?= htmlspecialchars($lbl) ?></span></span>
                                                                <?php endforeach;
                                                            else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <div class="text-center mt-1">
                                                    <small class="text-muted">Pembayaran ini perlu diverifikasi oleh admin.</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column align-items-center">
                                    <span style="color:#e74c3c; font-size:.78rem;">❌ Belum</span>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>

                    <!-- Tombol Aksi -->
                    <td class="text-center fw-bold" nowrap>
                        <a href="<?= base_url('pembayaran/' . encrypt_url($id_rumah)) ?>" class="btn btn-success btn-sm mb-1">
                            <i class="bi bi-cash-coin"></i> Bayar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>


            <!-- BARIS TOTAL -->
            <!-- Row 1: Total per bulan IPL (kewajiban) -->
            <tr style="background:#e9f7ef; font-weight:bold;">
                <td colspan="2" class="text-center">
                    <i class="bi bi-calendar-check text-success me-1"></i>TOTAL IPL
                    <small class="d-block fw-normal text-muted" style="font-size:.68rem;">per bulan kewajiban</small>
                </td>
                <?php for ($i = $bulan_awal; $i <= $bulan_akhir; $i++): ?>
                    <td class="text-center">
                        <?= $total_ipl_bulan[$i] > 0 ? number_format($total_ipl_bulan[$i]) : '-' ?>
                    </td>
                <?php endfor; ?>
                <td></td>
            </tr>

            <!-- Row 2: Total Kas Masuk berdasarkan tanggal_bayar aktual -->
            <?php
            // Siapkan label bulan untuk header tooltip
            $bln_label_total = [];
            for ($i = $bulan_awal; $i <= $bulan_akhir; $i++) {
                $bln_label_total[$i] = ($bulan_indo_short[$i] ?? $i) . ' ' . $selected_tahun;
            }
            ?>
            <tr style="background:#fff3e0; color:#e65100; font-weight:700; border-top:2px solid #ffb74d;">
                <td colspan="2" class="text-center">
                    <i class="bi bi-cash-coin text-warning me-1"></i>Total Kas Masuk
                    <small class="d-block fw-normal" style="font-size:.68rem; color:#bf360c;">
                        uang diterima per <strong>bulan bayar</strong>
                    </small>
                </td>
                <?php for ($i = $bulan_awal; $i <= $bulan_akhir; $i++): ?>
                    <td class="text-center" title="Kas diterima di <?= $bln_label_total[$i] ?>">
                        <?php if ($total_tgl_bayar[$i] > 0): ?>
                            <strong><?= number_format($total_tgl_bayar[$i]) ?></strong>
                            <small class="d-block" style="font-size:.65rem; color:#bf360c; font-weight:normal;">
                                <?= $bulan_indo_short[$i] ?>
                            </small>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                <?php endfor; ?>
                <td></td>
            </tr>

            <!-- Row 3: Kumulatif / Grand Total Kas Masuk (sum semua bulan) -->
            <?php
            $grand_total_kas  = array_sum($total_tgl_bayar);
            $grand_total_ipl  = 0;
            foreach ($total_ipl_bulan as $v) $grand_total_ipl += $v;
            ?>

        </tbody>
    </table>
</div>