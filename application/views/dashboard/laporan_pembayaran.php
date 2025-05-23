<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
?>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Laporan Pembayaran</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Laporan</li>
                        <li class="breadcrumb-item" aria-current="page">Laporan Pembayaran</li>
                    </ol>
                </nav>
            </div>
            <div class="col-3">
                <div class="text-center mb-n5">
                    <img src="<?php echo base_url(); ?>dist/images/backgrounds/welcome-bg.svg" alt=""
                        class="img-fluid mb-n4" />
                </div>
            </div>
        </div>
    </div>
</div>
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


<form method="get" action="<?= base_url('warga/laporan-pembayaran'); ?>" class="mb-3 p-3 border rounded shadow-sm" style="background-color: #f0f8ff;">
    <div class="row gy-2 gx-3 align-items-end">

        <!-- Input Pencarian Keyword -->
        <div class="col-12 col-md-3">
            <input type="text" name="keyword" class="form-control rounded" placeholder="Cari NIK atau Nama..."
                value="<?= html_escape($this->input->get('keyword')); ?>">
        </div>

        <!-- Filter Tahun -->
        <div class="col-6 col-md-2">
            <select name="tahun" class="form-select rounded">
                <option value="">Pilih Tahun</option>
                <?php
                $start = 2020;
                $end = date('Y');
                $tahun_get = $this->input->get('tahun');
                $selected_tahun = (empty($tahun_get)) ? date('Y') : $tahun_get;
                for ($tahun = $end; $tahun >= $start; $tahun--):
                ?>
                    <option value="<?= $tahun; ?>" <?= $selected_tahun == $tahun ? 'selected' : ''; ?>>
                        <?= $tahun; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Filter Koordinator -->
        <div class="col-6 col-md-2">
            <select name="id_koordinator" class="form-select rounded">
                <option value="">Koordinator Blok</option>
                <?php
                $selected_koordinator = $_REQUEST['id_koordinator'];
                foreach ($koordinator as $key => $value) : ?>
                    <option value="<?php echo $value['id']; ?>" <?php echo ($value['id'] == $selected_koordinator) ? 'selected' : ''; ?>>
                        <?php echo $value['nama']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tombol Cari & Reset -->
        <div class="col-12 col-md-auto">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-success rounded flex-grow-1 flex-md-grow-0" type="submit">
                    <i class="fa fa-search me-1"></i> Cari
                </button>
                <?php if ($this->input->get('keyword') || $this->input->get('bulan') || $this->input->get('tahun')): ?>
                    <a href="<?= base_url('warga/laporan-pembayaran'); ?>" class="btn btn-outline-danger rounded flex-grow-1 flex-md-grow-0">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Tambah -->
        <div class="col-12 col-md-auto">
            <a href="<?= base_url('pembayaran'); ?>" class="btn btn-success rounded w-100">
                <i class="fa fa-plus-circle me-1"></i> Tambah Pembayaran
            </a>
        </div>

        <!-- Tombol Download -->
        <div class="col-12 col-md-auto">
            <div class="btn-group w-100" role="group">
                <a href="#" class="btn btn-outline-success">
                    <i class="fa fa-file-excel-o me-1"></i> Excel
                </a>
                <a href="#" class="btn btn-outline-danger">
                    <i class="fa fa-file-pdf-o me-1"></i> PDF
                </a>
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
    <table class="table table-bordered align-middle text-nowrap" id="laporanTable">
        <thead class="table-primary text-center">
            <tr>
                <th class="shrink text-center">No</th>
                <th class="shrink">Rumah</th>
                <?php
                $bulan_indonesia = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                $tahun_terpilih = $this->input->get('tahun') ?: date('Y');
                $tahun_sekarang = date('Y');
                $bulan_terakhir = ($tahun_terpilih == $tahun_sekarang) ? date('n') : 12;

                for ($i = 1; $i <= $bulan_terakhir; $i++): ?>
                    <th class="shrink text-center"><?= $bulan_indonesia[$i] ?></th>
                <?php endfor; ?>
                <th>Aksi</th>
            </tr>

        </thead>

        <tbody>
            <?php $no = 1;
            foreach ($rumah as $key => $data_bulanan): ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td style="width: 1px;" class="td-narrow">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        <?php echo $data_bulanan['alamat'] ?> <br>
                        <i class="bi bi-person-fill text-primary"></i>
                        <?php echo $data_bulanan['nama'] ?>
                    </td>

                    <?php for ($i = 1; $i <= $bulan_terakhir; $i++):
                        $data_pembayaran = $this->db->query("
                            SELECT * FROM master_pembayaran as a 
                            LEFT JOIN master_users as b ON a.user_id = b.id
                            WHERE MONTH(a.bulan_mulai)='$i'
                            AND YEAR(a.bulan_mulai)='$tahun_terpilih'
                            AND b.id_rumah='" . $data_bulanan['id'] . "'
                        ")->row_array();
                    ?>
                        <td class="text-center <?= $data_pembayaran ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?>">
                            <?php if ($data_pembayaran): ?>
                                <div class="d-flex flex-column align-items-center">
                                    <strong>Rp125.000</strong>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column align-items-center">
                                    <span>❌ Belum</span>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>

                    <td class="text-center fw-bold">
                        <a href="<?php echo base_url('pembayaran/' . encrypt_url($data_bulanan['id'])); ?>" class="btn btn-sm btn-success">
                            <i class="bi bi-cash-coin"></i> Bayar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>