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

    .table-striped tr:nth-child(odd) {
        background-color: #f2f2f2;
    }

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
    <div class="card-body p-4">
        <!-- Form pencarian -->
        <form method="get" action="<?= base_url('warga/warga'); ?>" class="mb-3 row g-3 align-items-center">
            <div class="col-auto">
                <a href="<?= base_url('pendataan-keluarga'); ?>" class="btn btn-success">
                    <i class="fa fa-plus-circle me-1"></i> Tambah Pendataan
                </a>
            </div>

            <div class="col">
                <div class="input-group">
                    <input type="text" name="keyword" class="form-control" placeholder="Cari No NIK atau Nama..."
                        value="<?= html_escape($this->input->get('keyword')); ?>">
                    <button class="btn btn-success" type="submit">
                        <i class="fa fa-search me-1"></i> Cari
                    </button>
                    <?php if ($this->input->get('keyword')): ?>
                        <a href="<?= base_url('warga/warga'); ?>" class="btn btn-outline-danger">
                            <i class="fa fa-times me-1"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tombol Export -->
            <div class="col-auto">
                <div class="btn-group" role="group" aria-label="Export options">
                    <a href="<?= base_url('warga/export_excel'); ?>" class="btn btn-outline-success">
                        <i class="fa fa-file-excel-o me-1"></i> Excel
                    </a>
                    <a href="<?= base_url('warga/export_pdf'); ?>" class="btn btn-outline-danger">
                        <i class="fa fa-file-pdf-o me-1"></i> PDF
                    </a>
                </div>
            </div>
        </form>
        <div class="table-responsive mt-4">
            <table class="table table-striped table-custom table-bordered-dark" id="DataBroadcast" style="width: 100%;">
                <thead class="bg-success" style="height: 60px; vertical-align: middle;">
                    <tr>
                        <th>No</th>
                        <th>RUMAH</th>
                        <th>NO KK</th>
                        <th>NIK</th>
                        <th>NAMA</th>
                        <th>JENIS KELAMIN</th>
                        <th>TANGGAL LAHIR</th>
                        <th>SDHK</th>
                        <th>GOLDAR</th>
                        <th>PEKERJAAN</th>
                        <th>AGAMA</th>
                        <th>ALAMAT</th>
                        <th>KELURAHAN</th>
                        <th>KECAMATAN</th>
                        <th>KABUPATEN/KOTA</th>
                        <th>PROVINSI</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $this->uri->segment(3, 0) + 1;
                    foreach ($keluarga as $value):
                        $anggota_keluarga = $value['anggota'];
                    ?>
                        <tr>
                            <td style="vertical-align: middle;"><?php echo $no++ ?></td>
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
                            <td style="vertical-align: middle;">
                                <?php echo $value['no_kk']; ?>
                                <a href="#" data-bs-toggle="modal" data-bs-target="#modalKK<?php echo $value['id']; ?>">
                                    <i class="bi bi-image"></i>
                                </a>
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
                                    <?php foreach ($anggota_keluarga as $dt_anggota): ?>
                                        <tr>
                                            <td nowrap=""><?php echo strtoupper($dt_anggota['nama']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
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
                                <form method="post" action="<?= base_url('warga/warga'); ?>" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                    <input type="hidden" name="id" value="<?= $value['id']; ?>">
                                    <a class="btn btn-success" href="<?php echo base_url('edit-pendataan-keluarga/' . $value['id']); ?>"><i class="fa fa-pencil"></i></a>
                                    <button type="submit" name="hapus" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Tampilkan Pagination -->
        <div class="row mt-4 align-items-center">
            <div class="col-md-4" style="vertical-align: top;">
                <div class="text-muted">
                    <i class="fa fa-database me-1"></i>
                    Menampilkan <strong><?= ($start + 1) ?> - <?= min($start + $per_page, $total_rows) ?></strong> dari <strong><?= $total_rows ?></strong> data
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex justify-content-end gap-2">
                    <div class="dropdown d-inline-block me-2">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fa fa-chevron-down me-1"></i> Halaman <?= $current_page ?>
                        </button>
                        <ul class="dropdown-menu">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li><a class="dropdown-item <?= $i == $current_page ? 'active' : '' ?>"
                                        href="<?= base_url('warga/warga/' . (($i - 1) * $per_page)) ?>">
                                        Halaman <?= $i ?>
                                    </a></li>
                            <?php endfor; ?>
                        </ul>
                    </div>
                    <?= $pagination ?>
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

    </div>
</div>