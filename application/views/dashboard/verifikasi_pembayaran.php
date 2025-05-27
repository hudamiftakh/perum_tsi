<?php
$Auth = $this->session->userdata['username'];
$this->load->library('encryption');
?>

<figure class="text-center">
    <blockquote class="blockquote">
        <p>Halaman Verifikasi Pembayaran IPL</p>
    </blockquote>
    <figcaption class="blockquote-footer">
        Perumahan taman sukodono indah
    </figcaption>
</figure>

<div class="row">

    <!-- Koordinator Belum Verifikasi -->
    <div class="col-12 col-md-4 col-lg-4 mb-3">
        <div class="card border border-warning shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-person-x-fill text-warning display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Koordinator Belum Verifikasi</h6>
                    <h5 class="fw-bold text-warning">Rp <?php echo number_format($total_koordinator); ?></h5>
                    <small class="text-muted">Belum diverifikasi oleh koordinator</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Belum Verifikasi -->
    <div class="col-12 col-md-4 col-lg-4 mb-3">
        <div class="card border border-info shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-bank text-info display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Transfer Belum Verifikasi</h6>
                    <h5 class="fw-bold text-info">Rp <?php echo number_format($total_transfer); ?></h5>
                    <small class="text-muted">Dana transfer yang belum diverifikasi</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Semua Belum Verifikasi -->
    <div class="col-12 col-md-4 col-lg-4 mb-3">
        <div class="card border border-success shadow rounded-4 bg-light">
            <div class="card-body d-flex align-items-start">
                <i class="bi bi-cash-stack text-success display-6 me-3"></i>
                <div>
                    <h6 class="mb-1">Total Belum Diverifikasi</h6>
                    <h5 class="fw-bold text-success">Rp
                        <?php echo number_format($total_transfer + $total_koordinator); ?></h5>
                    <small class="text-muted">Gabungan semua yang belum diverifikasi</small>
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


<form method="get" action="<?= base_url('warga/verifikasi-pembayaran'); ?>" class="mb-3 p-3 border rounded shadow-sm"
    style="background-color: #f0f8ff;">
    <div class="row gy-2 gx-3 align-items-end">

        <!-- Input Pencarian Keyword -->
        <div class="col-12 col-md-4">
            <input type="text" name="keyword" class="form-control" style="background-color: white;"
                placeholder="Cari No NIK atau Nama..." value="<?= html_escape($this->input->get('keyword')); ?>">
        </div>
        <!-- Filter Metode -->
        <div class="col-6 col-md-2">
            <select name="pembayaran_via" class="form-select" style="background-color: white;">
                <option value="">Pembayaran Via</option>
                <option value="koordinator" <?= @$_REQUEST['pembayaran_via'] == 'koordinator' ? 'selected' : ''; ?>>
                    Koordinator</option>
                <option value="transfer" <?= @$_REQUEST['pembayaran_via'] == 'transfer' ? 'selected' : ''; ?>>Transfer
                </option>
            </select>
        </div>

        <!-- Filter Koordinator -->
        <div class="col-6 col-md-2">
            <select name="id_koordinator" class="form-select rounded" style="background-color: white; color: black;">
                <option value="">Koordinator Blok</option>
                <?php
                $selected_koordinator = @$_REQUEST['id_koordinator'] ?? '';
                foreach ($koordinator as $value): ?>
                <option value="<?= $value['id']; ?>" <?= $value['id'] == $selected_koordinator ? 'selected' : ''; ?>>
                    <?= $value['nama']; ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <!-- Tombol Cari & Reset -->
        <div class="col-12 col-md-auto">
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-success flex-grow-1 flex-md-grow-0" type="submit">
                    <i class="fa fa-search me-1"></i> Cari
                </button>
                <?php if ($this->input->get('keyword') || $this->input->get('pembayaran_via') || $this->input->get('id_koordinator')): ?>
                <a href="<?= base_url('warga/verifikasi-pembayaran'); ?>"
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
<div class="card w-100 position-relative overflow-hidden">
    <div class="card-body">
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

        input[type="checkbox"] {
            width: 16px;
            /* Lebar checkbox */
            height: 16px;
            /* Tinggi checkbox */
        }

        #submitBtn {
            display: none;
        }
        </style>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="laporanTable">
                <thead
                    style="background: linear-gradient(to right, #28a745, #218838) !important; color: white !important; vertical-align: middle !important;">
                    <tr>
                        <th><input type="checkbox" name="selected_ids[]" id="checkAll"></th>
                        <th>Nama / Alamat</th>
                        <th>Bulan</th>
                        <th>Tanggal Bayar</th>
                        <th>Status Verifikasi</th>
                        <th>Bukti Pembayaran</th>
                        <th>Keterangan</th>
                        <th>Jumlah Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pembayaran)): ?>
                    <?php
                        $no = 1;
                        $total = 0;
                        foreach ($pembayaran as $row):
                            $total += $row->jumlah_bayar;
                        ?>
                    <tr>
                        <td style="vertical-align: middle;">
                            <input type="checkbox" class="checkItem" name="selected_ids[]" value="<?= $row->id ?>">
                        </td>
                        <td nowrap><?= htmlspecialchars($row->nama) ?> <br>
                            <?= htmlspecialchars($row->rumah) ?>
                        </td>
                        <td><?= formatBulanTahun($row->bulan_mulai); ?></td>
                        <td><?= date('Y-m-d H:i', strtotime($row->created_at)) ?></td>
                        <td><?= htmlspecialchars($row->status); ?></td>
                        <td><?= htmlspecialchars($row->keterangan) ?></td>
                        <td>
                            <?php if ($row->pembayaran_via == 'transfer') : ?>
                            <a href="#"
                                onclick="openPopup('<?php echo base_url('uploads/bukti/' . $row->bukti) ?>'); return false;"
                                title="Lihat Bukti Pembayaran">
                                <img src="https://cdn-icons-png.flaticon.com/512/337/337946.png" alt="Bukti Pembayaran"
                                    style="width: 20px; height: 20px;">
                                Bukti Pembayaran
                            </a>
                            <?php endif; ?>
                        </td>
                        <td><?= number_format($row->jumlah_bayar, 0, ',', '.') ?></td>
                        <td nowrap>
                            <a href="<?= site_url('pembayaran/aksi_verifikasi/' . $row->id) ?>"
                                class="btn btn-success btn-sm d-inline-flex align-items-center">
                                <i class="bi bi-check-circle me-1"></i> Verifikasi
                            </a>
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
                <tfoot style="background-color: rgb(223, 229, 223);">
                    <tr>
                        <td colspan="7">Total</td>
                        <td><?= number_format($total, 0, ',', '.') ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <button id="submitBtn" type="submit" class="btn btn-success">Verifikasi Semua</button>
        </div>
    </div>
</div>

<script>
// Ketika checkbox #checkAll diklik
const checkAll = document.getElementById('checkAll');
const checkboxes = document.querySelectorAll('.checkItem');
const submitBtn = document.getElementById('submitBtn');

// Fungsi cek apakah ada checkbox yang dicentang
function toggleSubmitBtn() {
    const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
    submitBtn.style.display = anyChecked ? 'inline-block' : 'none';
}

// Event ketika klik checkbox "Pilih Semua"
checkAll.addEventListener('change', function() {
    checkboxes.forEach(cb => cb.checked = this.checked);
    toggleSubmitBtn();
});

// Event ketika klik checkbox item satu per satu
checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        // Jika semua item sudah dicentang, centang juga checkAll
        checkAll.checked = Array.from(checkboxes).every(cb => cb.checked);
        toggleSubmitBtn();
    });
});

function openPopup(url) {
    window.open(url, 'popupWindow', 'width=800,height=600,scrollbars=yes');
}
</script>