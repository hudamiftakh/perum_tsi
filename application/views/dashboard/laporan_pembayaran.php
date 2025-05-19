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
    .table thead th {
        vertical-align: middle;
        text-align: center;
    }

    .table td,
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

<div class="card w-100 position-relative overflow-hidden">
    <div class="card-body">
        <form method="get" action="<?= base_url('warga/laporan-pembayaran'); ?>" class="mb-3">
            <div class="row gy-2 gx-3 align-items-end">

                <!-- Input Pencarian Keyword -->
                <div class="col-12 col-md-4">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari No NIK atau Nama..."
                        value="<?= html_escape($this->input->get('keyword')); ?>">
                </div>
                <!-- Filter Tahun -->
                <div class="col-6 col-md-2">
                    <select name="tahun" class="form-select">
                        <option value="">Semua Tahun</option>
                        <?php
                        $start = 2020;
                        $end = date('Y');
                        $selected_tahun = $this->input->get('tahun');
                        for ($tahun = $end; $tahun >= $start; $tahun--):
                        ?>
                            <option value="<?= $tahun; ?>" <?= $selected_tahun == $tahun ? 'selected' : ''; ?>>
                                <?= $tahun; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Tombol Cari & Reset -->
                <div class="col-12 col-md-auto">
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-success flex-grow-1 flex-md-grow-0" type="submit">
                            <i class="fa fa-search me-1"></i> Cari
                        </button>
                        <?php if ($this->input->get('keyword') || $this->input->get('bulan') || $this->input->get('tahun')): ?>
                            <a href="<?= base_url('warga/laporan-pembayaran'); ?>" class="btn btn-outline-danger flex-grow-1 flex-md-grow-0">
                                <i class="fa fa-times me-1"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tombol Tambah -->
                <div class="col-12 col-md-auto">
                    <a href="<?= base_url('pembayaran'); ?>" class="btn btn-success w-100">
                        <i class="fa fa-plus-circle me-1"></i> Tambah Pembayaran
                    </a>
                </div>

            </div>
        </form>


        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="laporanTable">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th class="left">Nama</th>
                        <th class="left">Rumah</th>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <th><?= date('M', mktime(0, 0, 0, $i, 1)) ?></th>
                        <?php endfor; ?>
                        <th>Total Bayar</th>
                        <th class="left">Metode Bayar</th>
                        <th class="left">Status</th>
                        <th class="left">Tanggal Bayar</th>
                        <th class="left">Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $no = 1;
                    foreach ($laporan_bulanan as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="left"><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="left"><?= htmlspecialchars($row['rt_rw']) ?></td>
                            <?php for ($i = 1; $i <= 12; $i++):
                                $key = sprintf('%d-%02d', $tahun, $i);
                            ?>
                                <td><?= number_format($row['bulanan'][$key] ?? 0, 0, ',', '.') ?></td>
                            <?php endfor; ?>
                            <td><strong><?= number_format($row['total'], 0, ',', '.') ?></strong></td>
                            <td class="left"><?= htmlspecialchars($row['metode_bayar'] ?? '-') ?></td>
                            <td class="left"><?= htmlspecialchars($row['status_pembayaran'] ?? '-') ?></td>
                            <td class="left"><?= htmlspecialchars($row['tanggal_bayar'] ?? '-') ?></td>
                            <td class="left"><?= htmlspecialchars($row['keterangan'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>