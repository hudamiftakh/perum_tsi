<?php
$Auth = $this->session->userdata['username']; 
$this->load->library('encryption');
?>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Agenda Managements</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Agenda</li>
                        <li class="breadcrumb-item" aria-current="page">Agenda Management</li>
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
.table-card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    padding: 1rem;
    margin-bottom: 1rem;
}

.table-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.table-icon {
    width: 1.5rem;
    text-align: center;
    color: #198754;
}

.agenda-title {
    font-weight: bold;
    font-size: 1.1rem;
    color: #333;
}

.pagination {
    font-size: 1.2rem;
}

.pagination .page-item .page-link {
    border-radius: 12px;
    margin: 0 4px;
    padding: 10px 20px;
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.pagination .page-item .page-link:hover {
    background-color: #e2f0e9;
    color: #198754;
    border-color: #198754;
}

.pagination .page-item.active .page-link {
    background-color: #198754;
    border-color: #198754;
    color: white;
    font-weight: 600;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    transform: scale(1.01);
    transition: all 0.3s ease;
}

.btn-outline-teal {
    color: #20c997;
    border-color: #20c997;
}

.btn-outline-teal:hover {
    background-color: #20c997;
    color: white;
}

.rounded-4 {
    border-radius: 1.25rem !important;
}

.btn .badge {
    font-size: 0.65rem;
    padding: 0.3em 0.45em;
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
    <div class="px-4 pt-4 card-header" nowrap="">
        <div style="float: right;">
            <a href="<?php echo base_url('agenda/agenda/add'); ?>" class="btn btn-outline-primary btn-lg rounded-end"
                style="border-radius: 3px !important;">
                <i class="ti ti-users"></i> &nbsp Tambah Agenda
            </a>

            <button id="actionGroup" type="button" class="btn btn-outline-primary btn-lg rounded-end dropdown-toggle"
                style="border-radius: 3px !important;" data-bs-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                <i class="ti ti-list-details"></i> Action
            </button>
            <div class="dropdown-menu" aria-labelledby="actionGroup" style="">
                <a class="dropdown-item" href="#" id="sendToServerButton">Delete</a>
            </div>
        </div>
    </div>
    <div class="container mt-4">
        <?php 
            $no=1;
            $per_page = 5;
            $page = (int) $this->input->get('page', TRUE);
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * $per_page;

            // Hitung total data
            $total_data = $this->db->count_all('master_agenda');
            $total_page = ceil($total_data / $per_page);

            // Ambil data dengan limit
            $this->db->limit($per_page, $offset);
            $result = $this->db->order_by('tanggal', 'DESC')->get("master_agenda")->result_array();
            foreach ($result as $data) :
                $data['jumlah_hadir'] = $this->db->query("SELECT * FROM master_partisipant WHERE agenda_id='".$data['id']."'")->num_rows();
        ?>
        <div class="table-card shadow-sm p-3 rounded-3 border mb-3">
            <div class="row">
                <div class="col-md-10">
                    <h4 class="fw-bold text-success mb-3 agenda-title">
                         <span class="agenda-number"><?= $no++ ?>.</span> <i class="bi bi-pin-angle-fill me-2"></i><?php echo $data['judul']; ?>
                    </h4>

                    <div class="mb-1 text-muted">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        <?php echo format_tanggal($data['tanggal']); ?>
                    </div>

                    <div class="mb-1 text-muted">
                        <i class="bi bi-clock me-2 text-warning"></i>
                        Jam: <?php echo date('H:i', strtotime($data['jam_mulai'])); ?> -
                        <?php echo date('H:i', strtotime($data['jam_selesai'])); ?>
                    </div>

                    <div class="mb-1 text-muted">
                        <i class="bi bi-geo-alt me-2 text-danger"></i>
                        Lokasi: <?php echo $data['lokasi']; ?>
                    </div>

                    <div class="mb-2 text-muted">
                        <i class="bi bi-info-circle me-2 text-secondary"></i>
                        Keterangan: <?php echo $data['keterangan']; ?>
                    </div>

                    <!-- Jumlah Hadir -->
                    <div class="mt-2">
                        <span class="badge bg-danger rounded-pill px-3 py-2 fs-2">
                            <i class="bi bi-people-fill me-1"></i>
                            <?php echo $data['jumlah_hadir']; ?> Peserta Hadir
                        </span>
                    </div>
                </div>
                <div class="col-md-2 mt-3 mt-md-0">
                    <div class="d-flex flex-md-column flex-row flex-wrap gap-2 justify-content-md-end justify-content-center">
                        <!-- Tombol 1 -->
                         <?php 
                        $id = $data['id'];
                        $CI =& get_instance();
                        $CI->load->library('encryption');
                        $encrypted = $CI->encryption->encrypt($id);
                        $url_safe = strtr($encrypted, ['+' => '-', '/' => '_', '=' => '~']);
                        // var_dump($url_safe);
                        ?>
                        <a class="btn btn-outline-success" href="<?php echo base_url('show_form_participant/'.$url_safe); ?>">
                            <i class="bi bi-eye"></i>
                        </a>

                        <!-- Tombol 2 -->
                        <a class="btn btn-outline-success" href="<?php echo base_url('agenda/show_participant/'.$url_safe); ?>">
                            <i class="bi bi-person-lines-fill"></i>
                        </a>

                        <!-- Tombol 3 -->
                        <a class="btn btn-outline-primary" href="<?php echo base_url('agenda/agenda/add/'.$data['id']); ?>">
                            <i class="bi bi-pencil-square"></i>
                        </a>

                        <!-- Tombol Delete -->
                        <form method="POST" action="<?php echo base_url('agenda/agenda'); ?>"
                            onsubmit="return confirm('Apakah anda yakin ingin menghapus?');">
                            <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                            <button class="btn btn-outline-danger" style="width: 100%;" name="hapus" type="submit">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php endforeach; ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_page; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i ?>"><?php echo $i ?></a>
                </li>
                <?php endfor; ?>

                <?php if ($page < $total_page): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>

</div>
<?php endif; ?>
<script>
var table = $('#DataBroadcast').DataTable({});
</script>