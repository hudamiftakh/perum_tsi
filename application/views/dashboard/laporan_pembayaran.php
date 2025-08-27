<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
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

    .left {
        text-align: left !important;
    }

    .report-title {
        text-align: center;
        margin: 30px 0;
    }
</style>
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('success'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $this->session->flashdata('error'); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<h5 class="mb-4 fw-semibold text-center">Ringkasan Pembayaran Warga</h5>

<div class="row">
    <!-- Total Pembayaran Koordinator -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border border-secondary shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-person-badge-fill text-primary display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Total Koordinator</h6>
                    <h5 class="fw-bold text-primary">Rp <?php echo number_format($bulan_sd_koor['jumlah_bayar']); ?></h5>
                    <small class="text-muted">Akumulasi sampai bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pembayaran Transfer -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border border-success shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-bank2 text-success display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Total Transfer</h6>
                    <h5 class="fw-bold text-success">Rp <?php echo number_format($bulan_sd_transfer['jumlah_bayar']); ?></h5>
                    <small class="text-muted">Akumulasi sampai bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pembayaran Koordinator Bulan Ini -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border border-warning shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-calendar-check text-warning display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Koordinator Bulan Ini</h6>
                    <h5 class="fw-bold text-warning">Rp <?php echo number_format($bulan_ini_koor['jumlah_bayar']); ?></h5>
                    <small class="text-muted">Periode bulan berjalan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pembayaran Transfer Bulan Ini -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card border border-danger shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-cash-coin text-danger display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Transfer Bulan Ini</h6>
                    <h5 class="fw-bold text-danger">Rp <?php echo number_format($bulan_ini_traansfer['jumlah_bayar']); ?></h5>
                    <small class="text-muted">Periode bulan berjalan</small>
                </div>
            </div>
        </div>
    </div>

</div>
<form method="get" action="<?= base_url('warga/laporan-pembayaran'); ?>" class="mb-3 p-3 border rounded shadow-sm" style="background-color: #f0f8ff;">
    <div class="row gy-2 gx-3 align-items-end">

        <!-- Input Pencarian -->
        <div class="col-12 col-md-3">
            <input type="text" name="keyword" class="form-control rounded" placeholder="Cari NIK atau Nama..."
                style="background-color: white; color: black;"
                value="<?= html_escape($this->input->get('keyword')); ?>">
        </div>

        <!-- Filter Tahun -->
        <div class="col-6 col-md-2">
            <select name="tahun" class="form-select rounded" style="background-color: white; color: black;">
                <option value="">Pilih Tahun</option>
                <?php
                $start = 2025;
                $end = date('Y');
                $tahun_get = $this->input->get('tahun');
                $selected_tahun = empty($tahun_get) ? date('Y') : $tahun_get;
                for ($tahun = $end; $tahun >= $start; $tahun--): ?>
                    <option value="<?= $tahun; ?>" <?= $selected_tahun == $tahun ? 'selected' : ''; ?>>
                        <?= $tahun; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Filter Koordinator -->
        <div class="col-6 col-md-2">
            <select name="id_koordinator" class="form-select rounded" style="background-color: white; color: black;">
                <?php if ($this->session->userdata('role') == 'admin'): ?>
                    <option value="">Koordinator Blok</option>
                <?php endif; ?>
                <?php if ($this->session->userdata('role') == 'koordinator'): ?>
                    <!-- Tidak tampilkan opsi kosong, langsung foreach -->
                <?php endif; ?>
                <?php
                $selected_koordinator = $_REQUEST['id_koordinator'] ?? '';
                foreach ($koordinator as $value): ?>
                    <option value="<?= $value['id']; ?>" <?= $value['id'] == $selected_koordinator ? 'selected' : ''; ?>>
                        <?= $value['nama']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-12 col-md-5">
            <div class="d-flex flex-wrap gap-2 justify-content-start">
                <!-- Tombol Cari -->
                <button class="btn btn-success" type="submit">
                    <i class="fa fa-search me-1"></i> Cari
                </button>

                <!-- Tombol Reset -->
                <?php if ($this->input->get('keyword') || $this->input->get('tahun') || $this->input->get('id_koordinator')): ?>
                    <a href="<?= base_url('warga/laporan-pembayaran'); ?>" class="btn btn-outline-danger">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                <?php endif; ?>

                <!-- Tombol Tambah -->
                <!-- <a href="<?= base_url('pembayaran'); ?>" class="btn btn-primary">
                    <i class="fa fa-plus me-1"></i> Tambah
                </a> -->
                
                <?php if ($this->session->userdata('username')['role'] == 'admin'): ?>
                    <a href="<?= base_url('pembayaran/kirim_ipl'); ?>" class="btn btn-danger">
                        <i class="fa fa-bell me-1"></i> Kirim Peringatan
                    </a>
                <?php endif; ?>

                <!-- Tombol PDF Dropdown -->
                <!-- <div class="dropdown">
                    <button class="btn btn-outline-danger dropdown-toggle" type="button" id="pdfDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-file-pdf-o me-1"></i> PDF
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="pdfDropdown">
                        <?php
                        $bulan_arr = [
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
                        foreach ($bulan_arr as $key => $bulan): ?>
                            <li>
                                <a class="dropdown-item" href="<?= base_url('warga/laporan-pembayaran-pdf?bulan=' . $key . '&tahun=' . $selected_tahun); ?>" target="_blank">
                                    <?= $bulan . ' ' . $selected_tahun; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div> -->
            </div>
        </div>

    </div>
</form>


<style>
    /* Default: sempit untuk mobile */
    th.shrink,
    td.shrink {
        width: 1px !important;
        white-space: nowrap;
    }

    /* Desktop: biarkan browser atur lebar */
    @media (min-width: 768px) {

        th.shrink,
        td.shrink {
            width: auto !important;
            white-space: normal;
        }
    }
</style>

<!-- Tambahkan wrapper agar tabel bisa di-scroll di HP -->
<div class="table-responsive mb-4">
    <table class="table table-bordered align-middle" id="laporanTable">
        <thead style="background: linear-gradient(to right, #28a745, #218838) !important; color: white !important; vertical-align: middle !important;">
            <tr>
                <th class="shrink text-center">No</th>
                <th class="shrink">Rumah</th>
                <?php
                $bulan_indonesia = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                $tahun_terpilih = $this->input->get('tahun') ?: date('Y');
                $tahun_sekarang = date('Y');
                // $bulan_terakhir = ($tahun_terpilih == $tahun_sekarang) ? date('n') : 12;
                $bulan_terakhir = ($tahun_terpilih == $tahun_sekarang) ? date('n') : 12;

                for ($i = 6; $i <= $bulan_terakhir; $i++): ?>
                    <th class="shrink text-center"><?= $bulan_indonesia[$i] ?></th>
                <?php endfor; ?>
                <th width="1px" nowrap="">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1;
            foreach ($rumah as $key => $data_bulanan): ?>
                <tr>
                    <td class="text-center" style="width: 1px;">
                          <div style="width: 32px;
                                        height: 32px;
                                        background-color: #198754;
                                        color: white;
                                        font-weight: bold;
                                        border-radius: 50%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        font-size: 14px;
                                        box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                            <?php echo $no++; ?>
                        </div>
                    </td>
                    <td style="min-width: 120px; padding: 6px 8px;">
                        <div class="d-flex flex-column gap-1">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-geo-alt-fill text-danger me-2" style="font-size: 1.2rem;"></i>
                                <span class="text-truncate" style="max-width: 150px; font-weight: 400; font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($data_bulanan['alamat']); ?>
                                </span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-person-fill text-primary me-2" style="font-size: 1.2rem;"></i>
                                <span class="text-truncate" style="max-width: 150px; font-weight: 600; font-size: 1rem; text-transform: uppercase;">
                                    <?php echo strtoupper(htmlspecialchars($data_bulanan['nama'])); ?>
                                </span>
                            </div>
                        </div>
                    </td>
                    <?php for ($i = 6; $i <= $bulan_terakhir; $i++):
                            $currentMonthStr = sprintf('%04d-%02d', $tahun_terpilih, $i);

                            // Ambil data pembayaran yang terkait dengan bulan saat ini
                            $data_pembayaran = $this->db->query("
                                SELECT a.*, b.*, a.id as id_pembayaran 
                                FROM master_pembayaran as a 
                                LEFT JOIN master_users as b ON a.user_id = b.id
                                WHERE b.id_rumah = '" . $data_bulanan['id'] . "'
                                AND (
                                    a.untuk_bulan = '$currentMonthStr' 
                                    OR FIND_IN_SET('$currentMonthStr', a.bulan_rapel)
                                    OR DATE_FORMAT(a.bulan_mulai, '%Y-%m') = '$currentMonthStr'
                                )
                                ORDER BY a.status DESC
                            ")->row_array();
                        ?>
                            <td class="text-center 
                                <?php 
                                    if ($data_pembayaran) {
                                        // Tentukan warna latar belakang berdasarkan status pembayaran
                                        if ($data_pembayaran['status'] == 'verified') {
                                        $bulan_mulai_pembayaran = date('Y-m', strtotime($data_pembayaran['bulan_mulai']));
                                        $bulan_untuk = $data_pembayaran['untuk_bulan'];
                                        $is_bayar_dimuka = ($bulan_mulai_pembayaran < $bulan_untuk);
                                        $is_rapel = ($bulan_mulai_pembayaran > $bulan_untuk);

                                        // Gabungkan bulan utama dan bulan rapel menjadi satu array
                                        $all_months_covered = explode(',', $data_pembayaran['bulan_rapel']);
                                        $all_months_covered[] = $data_pembayaran['untuk_bulan'];

                                        // Tentukan warna berdasarkan tipe pembayaran (Bayar Dimuka atau Rapel)
                                        if ($currentMonthStr == $bulan_mulai_pembayaran || in_array($currentMonthStr, $all_months_covered)) {
                                            if ($is_bayar_dimuka) {
                                                echo 'bg-info-subtle text-info';
                                            } elseif ($is_rapel) {
                                                echo 'bg-success-subtle text-secondary';
                                            } else {
                                                // Pembayaran normal (bukan bayar dimuka/rapel)
                                                echo 'bg-success-subtle text-success';
                                            }
                                        } else {
                                            // Ini kasus jika ada pembayaran, tapi bulan saat ini tidak tercover
                                            echo 'bg-success-subtle text-success';
                                        }

                                        } elseif ($data_pembayaran['status'] == 'pending') {
                                            echo 'bg-warning-subtle text-warning';
                                        } else {
                                            echo 'bg-danger-subtle text-danger';
                                        }
                                    } else {
                                        echo 'bg-danger-subtle text-danger';
                                    }
                                ?>">
                                
                                <?php if ($data_pembayaran): ?>
                                    <?php if ($data_pembayaran['status'] == 'verified'): ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <?php 
                                                $bulan_mulai_pembayaran = date('Y-m', strtotime($data_pembayaran['bulan_mulai']));
                                                $bulan_untuk = date('Y-m', strtotime($data_pembayaran['untuk_bulan']));
                                                $is_bayar_dimuka = ($bulan_mulai_pembayaran < $bulan_untuk);
                                                $is_rapel = ($bulan_mulai_pembayaran > $bulan_untuk);
                                            ?>

                                            <?php if ($currentMonthStr == $bulan_mulai_pembayaran): ?>
                                                <strong style="cursor:pointer; color: black;"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#revisiModal<?= $data_bulanan['id'] . '_' . $i; ?>">
                                                    <?= number_format($data_pembayaran['jumlah_bayar']); ?>
                                                </strong>
                                            <?php elseif ($is_bayar_dimuka || $is_rapel): ?>
                                                <div class="d-flex flex-column align-items-center">
                                                    <?php if ($is_bayar_dimuka): ?>
                                                        <span class="text-info fs-6">
                                                            <i class="bi bi-arrow-up-right-circle-fill"></i>
                                                        </span>
                                                        <span class="text-muted">Bayar Dimuka</span>
                                                    <?php else: ?>
                                                        <span class="text-primary fs-6">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </span>
                                                        <span class="text-muted">Rapel</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="modal fade" id="revisiModal<?= $data_bulanan['id'] . '_' . $i; ?>" tabindex="-1" aria-labelledby="revisiModalLabel<?= $data_bulanan['id'] . '_' . $i; ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="revisiModalLabel<?= $data_bulanan['id'] . '_' . $i; ?>">Revisi Pembayaran</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body text-center">
                                                        <p>
                                                            <strong style="color: black;"><?= htmlspecialchars($data_bulanan['nama']); ?></strong><br>
                                                            <span class="text-muted"><?= htmlspecialchars($data_bulanan['alamat']); ?></span>
                                                        </p>
                                                        <p>
                                                            Nominal: <strong>Rp <?= number_format($data_pembayaran['jumlah_bayar']); ?></strong><br>
                                                            Bulan Bayar: <strong style="color: <?= ($data_pembayaran['status'] == 'pending') ? 'orange' : 'green'; ?>;">
                                                                <?php
                                                                    $bulan_indonesia = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

                                                                    // Gabungkan semua bulan yang dicakup, termasuk bulan utama
                                                                    $all_months_covered = [$data_pembayaran['untuk_bulan']];

                                                                    if (!empty($data_pembayaran['bulan_rapel'])) {
                                                                        $all_months_covered = array_merge(
                                                                            $all_months_covered,
                                                                            explode(',', $data_pembayaran['bulan_rapel'])
                                                                        );
                                                                    }

                                                                    // Hilangkan duplikat
                                                                    $all_months_covered = array_unique($all_months_covered);

                                                                    // Urutkan berdasarkan bulan-tahun
                                                                    usort($all_months_covered, function ($a, $b) {
                                                                        $da = DateTime::createFromFormat('m-Y', $a);
                                                                        $db = DateTime::createFromFormat('m-Y', $b);
                                                                        return $da <=> $db;
                                                                    });

                                                                    // Reset index array biar rapi
                                                                    $all_months_covered = array_values($all_months_covered);

                                                                    $bulan_text = [];
                                                                    foreach ($all_months_covered as $bln) {
                                                                        list($thn, $angka) = explode('-', $bln);
                                                                        $bulan_text[] = $bulan_indonesia[(int)$angka] . " " . $thn;
                                                                    }
                                                                    echo implode(', ', $bulan_text);
                                                                ?>
                                                            </strong>
                                                        </p>
                                                        <a href="<?php echo base_url('pembayaran/'.encrypt_url($data_pembayaran['id_rumah']))."/".encrypt_url($data_pembayaran['id_pembayaran']); ?>" class="btn btn-warning">
                                                            <i class="bi bi-pencil-square"></i> Revisi
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <?php elseif ($data_pembayaran['status'] == 'pending'): ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <span>⏳ Pending</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <span>❌ Belum</span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center">
                                        <span>❌ Belum</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    <td class="text-center fw-bold" nowrap="">
                        <a href="<?php echo base_url('pembayaran/' . encrypt_url($data_bulanan['id'])); ?>" class="btn btn-success">
                            <i class="bi bi-cash-coin"></i> Bayar
                        </a>
                        <!-- Tombol Cetak Kitir -->
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#kitirModal<?= $data_bulanan['id']; ?>">
                            <i class="bi bi-printer"></i> Cetak Kitir
                        </button>

                        <!-- Modal Cetak Kitir -->
                        <div class="modal fade" id="kitirModal<?= $data_bulanan['id']; ?>" tabindex="-1" aria-labelledby="kitirModalLabel<?= $data_bulanan['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="<?= base_url('dashboard/download_invoice'); ?>" method="get" target="_blank">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="kitirModalLabel<?= $data_bulanan['id']; ?>">Cetak Kitir Pembayaran</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="id_rumah" value="<?= $data_bulanan['id']; ?>">
                                            <div class="mb-3">
                                                <label for="bulanCetak<?= $data_bulanan['id']; ?>" class="form-label">Pilih Bulan</label>
                                                <select class="form-select" name="bulan" id="bulanCetak<?= $data_bulanan['id']; ?>" required>
                                                    <option value="">-- Pilih Bulan --</option>
                                                    <?php
                                                    for ($i = 6; $i <= $bulan_terakhir; $i++):
                                                        $bulan_value = str_pad($i, 2, '0', STR_PAD_LEFT);
                                                        $bulan_label = $bulan_indonesia[$i];
                                                        // Format database: YYYY-MM-01
                                                        $tanggal_format = $tahun_terpilih . '-' . $bulan_value . '-01';
                                                    ?>
                                                        <option value="<?= $tanggal_format; ?>"><?= $bulan_label . ' ' . $tahun_terpilih; ?></option>
                                                    <?php endfor; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-printer"></i> Cetak
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php
            // Hitung total pembayaran per bulan sesuai filter
            $total_per_bulan = [];
            for ($i = 1; $i <= $bulan_terakhir; $i++) {
                $total_per_bulan[$i] = 0;
            }
            foreach ($rumah as $data_bulanan) {
                for ($i = 1; $i <= $bulan_terakhir; $i++) {
                    $data_pembayaran = $this->db->query("
                        SELECT * FROM master_pembayaran as a 
                        LEFT JOIN master_users as b ON a.user_id = b.id
                        WHERE MONTH(a.bulan_mulai)='$i'
                        AND YEAR(a.bulan_mulai)='$tahun_terpilih'
                        AND b.id_rumah='" . $data_bulanan['id'] . "'
                        AND a.status = 'verified'
                    ")->row_array();
                    if ($data_pembayaran) {
                        $total_per_bulan[$i] += $data_pembayaran['jumlah_bayar'];
                    }
                }
            }
            ?>
            <tr style="background: #e9f7ef; font-weight: bold;">
                <td colspan="2" class="text-center">TOTAL</td>
                <?php for ($i = 6; $i <= $bulan_terakhir; $i++): ?>
                    <td class="text-center">
                        <?= $total_per_bulan[$i] > 0 ? number_format($total_per_bulan[$i]) : '-'; ?>
                    </td>
                <?php endfor; ?>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>