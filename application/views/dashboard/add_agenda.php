<?php $Auth = $this->session->userdata['username']; ?>
<style>
    table.dataTable thead>tr>th.sorting,
    table.dataTable thead>tr>th.sorting_asc,
    table.dataTable thead>tr>th.sorting_desc,
    table.dataTable thead>tr>th.sorting_asc_disabled,
    table.dataTable thead>tr>th.sorting_desc_disabled,
    table.dataTable thead>tr>td.sorting,
    table.dataTable thead>tr>td.sorting_asc,
    table.dataTable thead>tr>td.sorting_desc,
    table.dataTable thead>tr>td.sorting_asc_disabled,
    table.dataTable thead>tr>td.sorting_desc_disabled {
        cursor: pointer;
        position: relative;
        /* padding-right: 26px; */
        padding: 30px;
    }
</style>
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
<style>
.form-container {
      max-width: 100%;
      margin: 60px auto;
      background: #fff;
      padding: 45px 40px;
      border-radius: 16px;
      border: 1px solid #dee2e6;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07);
      animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(30px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .form-title {
      font-weight: 600;
      font-size: 1.75rem;
      margin-bottom: 30px;
      text-align: center;
      color: #2c3e50;
    }

    .form-label {
      font-weight: 500;
      color: #2f3640;
    }

    .form-control {
      border-radius: 8px;
      border: 1px solid #ced4da;
    }

    textarea.form-control {
      resize: vertical;
    }

    .btn-submit {
      background: #1e88e5;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      padding: 10px 0;
      transition: background 0.3s ease;
    }

    .btn-submit:hover {
      background: #1565c0;
    }

    .info-text {
      font-size: 0.9rem;
      color: #888;
      text-align: center;
      margin-top: 20px;
    }
  </style>
<div class="container">
<?php 
if(isset($_REQUEST['simpan'])) :
    $id = $_REQUEST['id'];
    $judul = $_REQUEST['judul'];
    $tanggal = $_REQUEST['tanggal'];
    $jam_mulai = $_REQUEST['jam_mulai'];
    $jam_selesai = $_REQUEST['jam_selesai'];
    $lokasi = $_REQUEST['lokasi'];
    $keterangan = $_REQUEST['keterangan'];
    $dibuat_oleh = $_REQUEST['dibuat_oleh'];

    $data = array(
        'judul' => $judul,
        'tanggal' => $tanggal,
        'jam_mulai' => $jam_mulai,
        'jam_selesai' => $jam_selesai,
        'lokasi' => $lokasi,
        'keterangan' => $keterangan,
        'dibuat_oleh' => $dibuat_oleh
    );
    
    if(isset($id)){
        $result = $this->db->where(array('id'=>$id))->update('master_agenda', $data);
    }else{
        $result = $this->db->insert('master_agenda', $data);
    }
    $current_url = $_SERVER['REQUEST_URI'];
    if ($result) {
        echo '<div class="alert alert-success mt-3">✅ Data berhasil disimpan!</div>';
        echo '<meta http-equiv="refresh" content="2;url=' . htmlspecialchars($current_url) . '">';
    } else {
        echo '<div class="alert alert-danger mt-3">❌ Gagal menyimpan data!</div>';
        echo '<meta http-equiv="refresh" content="2;url=' . htmlspecialchars($current_url) . '">';
    }
endif; 

$id = $this->uri->segment(4);

if ($id !== null && is_numeric($id)) {
    $id = (int) $id; // cast ke integer

    $result = $this->db->get_where("master_agenda", array('id' => $id))->row_array();

    // lanjut proses $result sesuai kebutuhan, misal:
    if ($result) {
        // data ditemukan, proses data
    } else {
        // data tidak ditemukan, bisa kasih pesan
        show_error('Data agenda tidak ditemukan.');
    }

    $result = $this->db->get_where("master_agenda",array('id'=>$id))->row_array();
} 
?>
  <div class="form-container">
    <h3 class="form-title">🗓️ Form Input Agenda Rapat</h3>
    <form action="" method="POST">
        <?php if(isset($id)) : ?>
            <input type="hidden" name="id" value="<?php echo isset($result['id']) ? htmlspecialchars($result['id']) : ''; ?>" class="form-control" placeholder="Contoh: Rapat Koordinasi Kegiatan RW" required>
        <?php endif; ?>
        <div class="mb-3">
            <label class="form-label">Judul Agenda</label>
            <input type="text" name="judul" value="<?php echo isset($result['judul']) ? htmlspecialchars($result['judul']) : ''; ?>" class="form-control" placeholder="Contoh: Rapat Koordinasi Kegiatan RW" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" value="<?php echo isset($result['tanggal']) ? $result['tanggal'] : ''; ?>" class="form-control" required>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
            <label class="form-label">Jam Mulai</label>
            <input type="time" name="jam_mulai" value="<?php echo isset($result['jam_mulai']) ? substr($result['jam_mulai'], 0, 5) : ''; ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
            <label class="form-label">Jam Selesai</label>
            <input type="time" name="jam_selesai" value="<?php echo isset($result['jam_selesai']) ? substr($result['jam_selesai'], 0, 5) : ''; ?>" class="form-control" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <input type="text" name="lokasi" value="<?php echo isset($result['lokasi']) ? htmlspecialchars($result['lokasi']) : ''; ?>" class="form-control" placeholder="Contoh: Balai RW 05 atau Aula Serbaguna">
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Misalnya: Agenda membahas kegiatan 17 Agustus, pembentukan panitia, dll."><?php echo isset($result['keterangan']) ? htmlspecialchars($result['keterangan']) : ''; ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Dibuat Oleh</label>
            <input type="text" name="dibuat_oleh" value="<?php echo isset($result['dibuat_oleh']) ? htmlspecialchars($result['dibuat_oleh']) : ''; ?>" class="form-control" placeholder="Nama pembuat agenda (misalnya: Ketua RW)">
        </div>
        <button type="submit" class="btn btn-submit w-100" name="simpan" style="color : white">💾 Simpan Agenda</button>
    </form>
    <div class="info-text">Pastikan data yang Anda isi sudah benar sebelum disimpan.</div>
  </div>
</div>
<script>
    var table = $('#DataBroadcast').DataTable({});
</script>
