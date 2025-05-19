<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
?>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Verifikasi Pembayaran</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Verifikasi</li>
                        <li class="breadcrumb-item" aria-current="page">Verifikasi Pembayaran</li>
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
                            <a href="<?= base_url('warga/laporan-pembayaran'); ?>"
                                class="btn btn-outline-danger flex-grow-1 flex-md-grow-0">
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

            table thead th {
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 0.85rem;
            }

            table tbody tr:hover {
                background-color: #f1fdf3;
            }

            table {
                border-radius: 15px;
                overflow: hidden;
            }
        </style>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="laporanTable">
                <thead style="background: linear-gradient(to right, #28a745, #218838) !important; color: white !important; vertical-align: middle !important;">
                    <tr>
                        <th>No</th>
                        <th>Nama Warga</th>
                        <th>Rumah</th>
                        <th>Metode</th>
                        <th>Jumlah Bayar</th>
                        <th>Tanggal Bayar</th>
                        <th>Status Verifikasi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pembayaran)): ?>
                        <?php $no = 1;
                        foreach ($pembayaran as $row): ?>
                            <tr>
                                <td style="vertical-align: middle;">
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
                                <td><?= htmlspecialchars($row->nama) ?></td>
                                <td nowrap=""><?= htmlspecialchars($row->rumah) ?></td>
                                <td><?= $row->metode ?></td>
                                <td><?= number_format($row->jumlah_bayar, 0, ',', '.') ?></td>
                                <td><?= date('Y-m-d H:i', strtotime($row->created_at)) ?></td>
                                <td><?= htmlspecialchars($row->keterangan) ?></td>
                                <td>...</td>
                                <td nowrap="">
                                    <!-- Tombol Verifikasi -->
                                    <a href="<?= site_url('pembayaran/aksi_verifikasi/' . $row->id) ?>"
                                        class="btn btn-success btn-sm d-inline-flex align-items-center">
                                        <i class="bi bi-check-circle me-1"></i> Verifikasi
                                    </a>

                                    <!-- Tombol Tolak -->
                                    <a href="<?= site_url('pembayaran/aksi_tolak/' . $row->id) ?>"
                                        class="btn btn-danger btn-sm d-inline-flex align-items-center">
                                        <i class="bi bi-x-circle me-1"></i> Tolak
                                    </a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align:center;">Tidak ada pembayaran menunggu verifikasi</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>