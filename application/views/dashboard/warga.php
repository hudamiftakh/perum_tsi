<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
?>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Manajemen Warga</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Warga</li>
                        <li class="breadcrumb-item" aria-current="page">Manajemen System</li>
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
    .table-striped {
        border-collapse: collapse;
        width: 100%;
    }

    /* .table-striped tr:nth-child(odd) {
        background-color: #f2f2f2;
    } */

    .table-striped td {
        padding: 4px 8px;
        border: 1px solid #ddd;
    }

    .table-custom td,
    .table-custom th {
        padding: 4px 8px;
        /* atas-bawah 4px, kiri-kanan 8px */
    }

    .table-bordered-dark td,
    .table-bordered-dark th {
        border: 2px solidrgb(244, 244, 244);
        /* Abu-abu gelap */
    }

    .table-bordered-dark {
        border-color: rgb(205, 195, 195);
        /* Untuk elemen luar */
    }

    .dataTables_info {
        padding-top: 0.5rem;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .pagination li a {
        border-radius: 1rem !important;
    }

    .pagination .active a {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #fff !important;
    }

    .pagination a:hover {
        background-color: #f0f0f0;
        color: #0d6efd;
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

<!-- <div class="card w-100 position-relative overflow-hidden">
    <div class="card-body p-4"> -->
<!-- Form pencarian -->
<form method="get" action="<?= base_url('warga/data-warga'); ?>" class="mb-3">
    <div class="row gy-2 gx-3 align-items-center">

        <!-- Tombol Tambah -->
        <div class="col-12 col-md-auto">
            <a href="<?= base_url('pendataan-keluarga'); ?>" class="btn btn-success w-100">
                <i class="fa fa-plus-circle me-1"></i> Tambah Pendataan
            </a>
        </div>

        <!-- Input Pencarian -->
        <div class="col-12 col-md">
            <div class="input-group">
                <input type="text" name="keyword" class="form-control" placeholder="Cari No NIK atau Nama..."
                    value="<?= html_escape($this->input->get('keyword')); ?>">
                <button class="btn btn-success" type="submit">
                    <i class="fa fa-search me-1"></i> Cari
                </button>
                <?php if ($this->input->get('keyword')): ?>
                    <a href="<?= base_url('warga/data-warga'); ?>" class="btn btn-outline-danger">
                        <i class="fa fa-times me-1"></i> Reset
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol Export -->
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

<div class="table-responsive mt-4">
    <table class="table table-striped table-custom table-bordered-dark" id="DataBroadcast" style="width: 100%;">
        <thead style="background: linear-gradient(to right, #28a745, #218838) !important; color: white !important; vertical-align: middle !important;">
            <tr style="height: 60px;" class="text-center align-middle">
                <th style="min-width: 40px;">No</th>
                <th>Rumah</th>
                <th>No KK</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Jenis Kelamin</th>
                <th>Tanggal Lahir</th>
                <th>SDHK</th>
                <th>Gol. Darah</th>
                <th>Pekerjaan</th>
                <th>Agama</th>
                <th>Alamat</th>
                <th>Kelurahan</th>
                <th>Kecamatan</th>
                <th>Kab/Kota</th>
                <th>Provinsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = $this->uri->segment(3, 0) + 1;
            foreach ($keluarga as $value):
                $anggota_keluarga = $value['anggota'];
            ?>
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
                    <td style="vertical-align: top;" nowrap="">
                        <table class="table-striped">
                            <?php
                            $nomor_rumah_list = explode('|', $value['nomor_rumah']);
                            foreach ($nomor_rumah_list as $nr): ?>
                                <tr>
                                    <td nowrap=""><?php echo trim($nr); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td style="vertical-align: middle;" nowrap="">
                        <div>
                            <!-- Nomor KK -->
                            <span style="font-weight: bold;"><?= htmlspecialchars($value['no_kk']) ?></span>
                        </div>
                        <div class="mt-1 align-items-center gap-3">
                            <!-- Icon Gambar KK, klik buka modal -->
                            <a href="#" data-bs-toggle="modal" data-bs-target="#modalKK<?= $value['id'] ?>" class="text-primary d-flex align-items-center gap-1" title="Lihat Foto KK">
                                <i class="bi bi-image fs-5"></i> <span>Lihat KK</span>
                            </a>

                            <!-- Icon WhatsApp dengan nomor HP -->
                            <?php if (!empty($value['no_hp'])): ?>
                                <a href="https://wa.me/<?= preg_replace('/\D/', '', hp($value['no_hp'])) ?>" target="_blank" class="text-success d-flex align-items-center gap-1" title="Chat WhatsApp" rel="noopener">
                                    <i class="bi bi-whatsapp fs-5"></i> <span><?= htmlspecialchars(hp($value['no_hp'])) ?></span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td style="vertical-align: top;">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td><?php echo $dt_anggota['nik']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td style="vertical-align: top;">
                        <table class="table-striped">
                            <?php if (empty($anggota_keluarga)) :  ?>
                                <?php echo strtoupper($value['nama_pemilik']); ?>
                            <?php else : ?>
                                <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                    <tr>
                                        <td nowrap=""><?php echo strtoupper($dt_anggota['nama']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </table>
                    </td>
                    <td style="vertical-align: top;">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td nowrap=""><?php echo $dt_anggota['jenis_kelamin']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>

                    <td style="vertical-align: top;" nowrap="">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td><?php echo format_tanggal_v2($dt_anggota['tgl_lahir']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>

                    <td style="vertical-align: top;" nowrap="">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td nowrap=""><?php echo $dt_anggota['hubungan']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>

                    <td style="vertical-align: top;" nowrap="">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td nowrap=""><?php echo $dt_anggota['golongan_darah']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>

                    <td style="vertical-align: top;" nowrap="">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td nowrap=""><?php echo $dt_anggota['pekerjaan']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>

                    <td style="vertical-align: top;" nowrap="">
                        <table class="table-striped">
                            <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                <tr>
                                    <td nowrap=""><?php echo $dt_anggota['agama']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                    <td style="vertical-align: middle;"><?php echo $value['alamat']; ?></td>
                    <td style="vertical-align: middle;"><?php echo $value['kelurahan']; ?></td>
                    <td style="vertical-align: middle;"><?php echo $value['kecamatan']; ?></td>
                    <td style="vertical-align: middle;"><?php echo $value['kota']; ?></td>
                    <td style="vertical-align: middle;"><?php echo $value['provinsi']; ?></td>
                    <td style="vertical-align: middle;" nowrap="">
                        <form method="post" action="<?= base_url('warga/data-warga'); ?>" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="d-inline">
                            <input type="hidden" name="id" value="<?= $value['id']; ?>">

                            <div class="btn-group" role="group" aria-label="Aksi">
                                <!-- Tombol Edit -->
                                <a href="<?= base_url('edit-pendataan-keluarga/' . encrypt_url($value['id'])); ?>" class="btn  btn-outline-success" title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </a>

                                <!-- Tombol Hapus -->
                                <button type="submit" name="hapus" class="btn  btn-outline-danger" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!-- Tampilkan Pagination -->
<div class="row mt-4 align-items-center">
    <!-- Keterangan Jumlah Data -->
    <div class="col-12 col-md-4 mb-2 mb-md-0">
        <div class="text-muted">
            <i class="fa fa-database me-1"></i>
            Menampilkan <strong><?= ($start + 1) ?> - <?= min($start + $per_page, $total_rows) ?></strong> dari <strong><?= $total_rows ?></strong> data
        </div>
    </div>

    <!-- Navigasi Halaman -->
    <div class="col-12 col-md-8">
        <div class="d-flex flex-wrap justify-content-md-end gap-2">
            <!-- Dropdown Halaman -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle w-100 w-md-auto" type="button" data-bs-toggle="dropdown">
                    <i class="fa fa-chevron-down me-1"></i> Halaman <?= $current_page ?>
                </button>
                <ul class="dropdown-menu">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li>
                            <a class="dropdown-item <?= $i == $current_page ? 'active' : '' ?>"
                                href="<?= base_url('warga/data-warga/' . (($i - 1) * $per_page)) ?>">
                                Halaman <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>

            <!-- Pagination -->
            <div>
                <?= $pagination ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview KK -->
<?php foreach ($keluarga as $value): ?>
    <div class="modal fade" id="modalKK<?php echo $value['id']; ?>" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview KK</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body text-center">
                    <?php
                    $file = $value['file_kk'];
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $file_url = base_url('uploads/' . $file);
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?php echo $file_url; ?>" alt="Foto KK" class="img-fluid" width="100%"
                            style="max-height: 80vh; object-fit: contain;">
                    <?php elseif (strtolower($ext) === 'pdf'): ?>
                        <iframe src="<?php echo $file_url; ?>" width="100%" height="500px"
                            style="border:none;"></iframe>
                    <?php else: ?>
                        <p class="text-danger">Format file tidak didukung untuk preview.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<!-- </div>
</div> -->