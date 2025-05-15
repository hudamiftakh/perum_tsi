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
                            <form action="">
                                <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin?')"
                                    name="hapus" type="submit">
                                    <i class="fa fa-trash"></i>
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
<div id="pdfArea" style="display: none;">
    <h5>Daftar Hadir Peserta</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>TTD</th>
                <th>Tanggal Hadir</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $result = $this->db->get("master_partisipant")->result_array();
            foreach ($result as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $data['nama'] ?></td>
                <td><img src="<?= $data['ttd_base64'] ?>" style="height:50px;"></td>
                <td><?= $data['hadir_pada'] ?></td>
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
        margin:       0.5,
        filename:     'daftar_hadir_ksh.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().from(element).set(opt).save().then(() => {
        element.style.display = 'none'; // Sembunyikan lagi setelah selesai
    });
}

</script>