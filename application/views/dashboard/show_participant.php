<!-- Tambahkan di HEAD Anda -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
table.dataTable thead>tr>th {
    cursor: pointer;
    padding: 20px;
    text-transform: uppercase;
    font-size: 14px;
}

.btn-download {
    float: right;
    margin-bottom: 15px;
}

.img-ttd {
    max-height: 60px;
    object-fit: contain;
}

.table thead {
    background-color: #00C49A;
    color: white;
}

.table td,
.table th {
    vertical-align: middle;
    text-align: center;
}

.img-ttd {
    height: 50px;
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 3px;
    background: #fff;
}

.btn-icon {
    padding: 6px 10px;
    font-size: 14px;
}

.btn-download {
    float: right;
}
</style>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Detail Participant</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Detail Participant</li>
                        <li class="breadcrumb-item" aria-current="page">Detail Participant Management</li>
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
        <h4 class="text-muted">Belum Ada Data</h4>
    </div>
</div>
<?php else: ?>
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
<button class="btn btn-outline-primary btn-download" onclick="downloadPDF()">
    <i class="bi bi-download"></i> Download PDF
</button>
<div class="card w-100 position-relative overflow-hidden">
    <div class="card-body p-4">
        <div class="table-responsive mt-4">
            <table class="table border table-striped display" id="DataBroadcast" style="width: 100%;">
                <thead class="bg-success text-white">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>TTD</th>
                        <th>Tanggal Hadir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                            $no = 1;
                            $result = $this->db->get("master_partisipant")->result_array();
                            foreach ($result as $key => $data): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><b><?= $data['nama'] ?></b></td>
                        <td><img src="<?= $data['ttd_base64'] ?>" class="img-ttd" alt="TTD"></td>
                        <td><?= $data['hadir_pada'] ?></td>
                        <td>
                            <form action="" method="POST">
                                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                <button class="btn btn-danger" onclick="return confirm('Apakah anda yakin?')"
                                    name="hapus" type="submit">
                                    <i class="fa fa-trash"></i> Batal
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php 
    $CI =& get_instance();
    $CI->load->library('encryption');
    $id = $this->uri->segment(3);
    $encrypted_id = strtr($id, ['-' => '+', '_' => '/', '~' => '=']);
    $agenda_id = $CI->encryption->decrypt($encrypted_id);
    $data = $this->db->where(array('id'=>$agenda_id))->get("master_agenda")->row_array();
?>
<div id="pdfArea"
    style="font-family: 'Arial', sans-serif; padding: 30px; color: #000; background-color: #fff; display: none;">
    <style>
    #pdfArea {
        font-family: 'Arial', sans-serif;
        padding: 30px;
        color: #000;
        background-color: #fff;
        display: none;
        /* pastikan tidak ada overflow tersembunyi */
        overflow: visible;
    }

    #pdfArea h2 {
        text-align: center;
        margin-bottom: 10px;
    }

    #pdfArea .meta-info {
        margin-bottom: 20px;
        font-size: 14px;
    }

    #pdfArea .meta-info div {
        margin-bottom: 5px;
    }

    #pdfArea table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        page-break-inside: auto;
    }

    #pdfArea thead {
        display: table-header-group;
        /* header tabel muncul di tiap halaman */
    }

    #pdfArea tbody {
        display: table-row-group;
    }

    #pdfArea tr {
        page-break-inside: avoid;
        /* hindari potong baris */
        page-break-after: auto;
    }

    #pdfArea th,
    #pdfArea td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
        vertical-align: middle;
    }

    #pdfArea th {
        background-color: #f2f2f2;
    }

    #pdfArea img {
        max-height: 60px;
    }
    </style>


    <h2>DAFTAR HADIR RAPAT</h2>

    <div class="meta-info">
        <div><strong>Judul:</strong> <?= $data['judul']; ?></div>
        <div><strong>Tanggal:</strong> <?= format_tanggal($data['tanggal']); ?></div>
        <div><strong>Waktu:</strong> <?= date('H:i', strtotime($data['jam_mulai'])); ?> -
            <?= date('H:i', strtotime($data['jam_selesai'])); ?></div>
        <div><strong>Lokasi:</strong> <?= $data['lokasi']; ?></div>
        <div><strong>Keterangan:</strong> <?= $data['keterangan']; ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Tanda Tangan</th>
                <th>Tanggal Hadir</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $result = $this->db->order_by('created_at','ASC')->get("master_partisipant")->result_array();
            foreach ($result as $row): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td style="text-align: left;"><?= strtoupper($row['nama']) ?></td>
                <td><img src="<?= $row['ttd_base64'] ?>" alt="TTD"></td>
                <td><?= format_tanggal_jam($row['hadir_pada']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<script>
var table = $('#DataBroadcast').DataTable();

function downloadPDF() {
    const element = document.getElementById('pdfArea');
    element.style.display = 'block';

    console.log('coba');
    const opt = {
        margin: 0.5,
        filename: 'daftar_hadir_rapat.pdf',
        image: {
            type: 'jpeg',
            quality: 0.98
        },
        html2canvas: {
            scale: 2
        },
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf().from(element).set(opt).save().then(() => {
        element.style.display = 'none'; // Sembunyikan lagi setelah selesai
    });
}
</script>