<?php
$Auth = $this->session->userdata['username']; 
$this->load->library('encryption');
?>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Pengeluaran Managements</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="/perum_tsi/dashboard">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Keuangan</li>
                        <li class="breadcrumb-item" aria-current="page">Pengeluaran</li>
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

<?php
$check_data = 1;
if ($check_data <= 0): ?>
<div class="card text-center">
    <div class="card-body">
        <h2> Management</h2>
        <br>
        <br>
        <img src="<?php echo base_url('assets/wabot.png') ?>" width="260px" alt="">
        <br>
        <br>
        <br>

    </div>

</div>
<?php else: ?>

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

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success">
    <?= $this->session->flashdata('success'); ?>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger">
    <?= $this->session->flashdata('error'); ?>
</div>
<?php endif; ?>

<div class="card w-100 position-relative overflow-hidden">
    <div class="px-4 pt-4 card-header " nowrap="">
        <div style="float: right; margin-right:1px;">
            <a href="<?php echo base_url('kas/form'); ?>" class="btn btn-outline-primary btn-lg rounded-end"
                style="border-radius: 3px !important;">
                <i class="bi bi-cash-coin"></i> &nbsp Tambah Pengeluaran
            </a>
        </div>
    </div>
   <!-- Tambahkan wrapper agar tabel bisa di-scroll di HP -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle text-nowrap" id="laporanTable">
            <thead class="table-primary text-center">
                <tr>
                    <th class="shrink text-center">No</th>
                    <th class="shrink">Jenis Pengeluaran</th>
                    <th class="shrink">Deskripsi</th>
                    <th>Nominal</th>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>


                    <?php 
                    $no=1;
                    foreach ($data as $key => $value) { ?>
                    <tr>
                        <td class="text-center"><?= $no ?></td>
                        <td style="width: 1px;" class="td-narrow">
                           <?= $value['jenis_pengeluaran_desc']?>
                        </td>
                        <td style="width: 1px;" class="td-narrow">
                           <?= $value['deskripsi']?>
                        </td>
                        <td class="text-center nominal">
                            <div class="d-flex flex-column align-items-center">
                                <strong>Rp<?= $value['nominal']?></strong>
                            </div>
                        </td>
                        <td class="text-center">
                                <strong><?= $value['tanggal']?></strong>
                        </td>
                        <td style="width: 1px;" class="td-narrow">
                            <?= $value['keterangan']?>
                        </td>
                        <td class="text-center fw-bold">
                            <form method="post" action="<?= base_url('kas/pengeluaran'); ?>" onsubmit="return confirm('Yakin ingin menghapus data ini?');" class="d-inline">
                                    <input type="hidden" name="id" value="<?= $value['id']; ?>">

                                    <div class="btn-group" role="group" aria-label="Aksi">
                                        <!-- Tombol Edit -->
                                        <a href="<?= base_url('edit-pengeluaran/' . encrypt_url($value['id'])); ?>" class="btn  btn-outline-success" title="Edit">
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
                   <?php $no++;
                } ?> 

            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<div class="modal" id="tambah_nomor">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Pegawai</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="<?= base_url('broadcast/create_group'); ?>" id="submitFormGroup" method="POST">
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Nama</label>
                        <input type="text" class="form-control" name="group" required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="simpan" class="btn btn-primary" id="addGroupButton">Simpan</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
      $('#pengeluaranTable').DataTable();
    });
</script>
<script>
  function formatThousand(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  document.addEventListener("DOMContentLoaded", function () {
    const nominalCell = document.querySelectorAll(".nominal");
    nominalCell.forEach(function (cell) {
      const raw = cell.textContent.replace(/\D/g, ""); // hilangkan simbol jika ada
      const formatted = formatThousand(raw);
      cell.textContent = formatted;
    });
  });
</script>