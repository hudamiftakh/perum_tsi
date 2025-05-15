<?php $Auth = $this->session->userdata['username']; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet" />
</script>


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
#editor-container {
      height: 200px; /* Ubah tinggi editor di sini */
      border: 1px solid #ccc;
      border-radius: 4px;
    }
</style>



<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Entry Aktivitas</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Entry Aktivitas</li>
                        <li class="breadcrumb-item" aria-current="page">Entry Aktivitas Management</li>
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
$id = $this->uri->segment(3);
if(!empty($id)){
	$dasis = $this->db->get_where("master_transaksi_aktivitas",array('id'=>$id))->row_array();
}
?>

<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?php echo $this->session->flashdata('success'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo $this->session->flashdata('error'); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-12">
        <div class="card border-top border-primary">
            <div class="card-body p-4">
                <?php if(!empty($id)) : ?>
                <form action="<?php echo base_url(); ?>aktivitas-pegawai/edit" method="POST"
                    enctype="multipart/form-data">
                    <?php else : ?>
                    <form action="<?php echo base_url(); ?>aktivitas-pegawai/save_data" method="POST"
                        enctype="multipart/form-data">
                        <?php endif; ?>
                        <div class="col-sm-12">
                            <label for="exampleInputPassword1"
                                class="form-label fw-semibold col-sm-2 col-form-label">Kader KSH <label
                                    style="color: red;">*</label></label>
                            <?php if(!empty($id)) : ?>
                            <input type="hidden" class="form-control" placeholder="id" name="id"
                                value="<?php echo $dasis['id']; ?>">
                            <?php endif; ?>
                            <select class="js-example-basic-single" name="id_pegawai" style="width: 100%;" required>
                                <option value="">Pilih KSH</option>
                                <?php 
							if($_SESSION['username']['role']=='admin') { 
								$ksh = $this->db->get("master_pegawai")->result_array();
							}else{
								$id = @$_SESSION['username']['id'];
								$ksh = $this->db->where(array('id'=>$id))->get("master_pegawai")->result_array();
							};
							foreach ($ksh as $key => $value) :
						?>
                                <option value="<?php echo $value['id']; ?>"
                                    <?php echo ($dasis['id_pegawai']==$value['id']) ? "selected": ""; ?>>
                                    <?php echo $value['nik']." - ".$value['nama']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-12">
                            <label class="form-label fw-semibold col-sm-2 col-form-label">Tanggal <label
                                    style="color: red;">*</label></label>
                            <input type="date" class="form-control" placeholder="Tanggal" name="tanggal"
                                value="<?php echo $dasis['tanggal']; ?>" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-sm-12">
                            <label class="form-label fw-semibold col-sm-2 col-form-label">Aktivitas <label
                                    style="color: red;">*</label></label>
                            <select class="js-example-basic-single" name="jenis_aktivitas"
                                style="width: 100%; height: 10px !mportant;" required>
                                <option value="">Pilih Aktivitas</option>
                                <?php 
							$aktivitas = $this->db->get("master_aktivitas")->result_array();
							foreach ($aktivitas as $key => $value) :
						?>
                                <option value="<?php echo $value['id']; ?>"
                                    <?php echo ($dasis['id_aktivitas']==$value['id']) ? "selected": ""; ?>>
                                    <?php echo $value['nama'].""; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <!-- <div class="col-sm-12">
                            <div class="col-xs-6" style="margin-top: 10px">
                                <label class="form-label fw-semibold col-sm-2 col-form-label">Waktu Pelaksanaan <label
                                        style="color: red;">*</label></label>
                                <div class="input-group">
                                    <label>
                                        <input checked type="radio" class="minimal-red" id="jenis_waktu_pengerjaan_0"
                                            name="input[jenis_waktu_pengerjaan]" value="0"
                                            <?php echo ($dasis['jenis_waktu_pengerjaan']==0) ? "checked": ""; ?>>&nbsp;&nbsp;Pada
                                        Jam
                                        Kerja&nbsp;&nbsp;
                                    </label>
                                    <label class="pull-right">
                                        <input type="radio" class="minimal-red" id="jenis_waktu_pengerjaan_1"
                                            name="input[jenis_waktu_pengerjaan]" value="1"
                                            <?php echo ($dasis['jenis_waktu_pengerjaan']==1) ? "checked": ""; ?> />&nbsp;&nbsp;Diluar
                                        Jam Kerja
                                    </label>
                                </div>
                            </div>
                        </div> -->
                        <div class="col-sm-12">
                            <div class="col-xs-6" style="margin-top: 10px">
                                <input type="hidden" value="0" name="jenis_waktu_pengerjaan">
                                <label class="form-label fw-semibold col-sm-2 col-form-label">Catatan <label
                                style="color: red;">*</label></label>
                                <!-- <textarea id="editor" name="catatan"><?php echo $dasis['catatan'].""; ?></textarea> -->
								<!-- <div id="editor-container"><?php echo $dasis['catatan'].""; ?></div> -->
								<!-- <textarea id="hidden-textarea" name="catatan" style="display:none;" required><?php echo $dasis['catatan'].""; ?></textarea> -->
                                 <br>
                                <!-- <textarea name="catatan" class="form-control" cols="10" placeholder="catatan" required><?php echo $dasis['catatan'].""; ?></textarea> -->
                                <textarea name="catatan" class="form-control"  rows="3" placeholder="catatan" required><?php echo $dasis['catatan'].""; ?></textarea>
                                
								<!-- <textarea  name="catatan" class="form-control"  cols="10" style="height: 100px;" placeholder="catatan" required><?php echo $dasis['catatan'].""; ?></textarea> -->
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="col-xs-6" style="margin-top: 10px">
                                <label class="form-label fw-semibold col-sm-2 col-form-label">File Pendukung <label
                                style="color: red;">*</label></label>
                                <?php if(!empty($this->uri->segment(3))) : ?>
                                <input type="file" name="file" class="form-control" accept="image/*">
                                <input type="hidden" name="file_lama"
                                    value="<?php echo base_url() ?>uploads/<?php echo $dasis['file_path'] ?>"
                                    class="form-control" accept="image/*" required>
                                <img data-duration="700" style="transform: scale(1) translate(0px, 0px);"
                                    class="img-thumbnail img-responsive zoomify"
                                    src="<?php echo base_url() ?>uploads/<?php echo $dasis['file_path'] ?>"
                                    width="10%"><br>
                                <a href="<?php echo base_url() ?>uploads/<?php echo $dasis['file_path'] ?>">Lihat
                                    File</a>
                                <?php else : ?>
                                <input type="file" name="file" class="form-control" accept="image/*" required>
                                <?php endif; ?>
                                Maksimal file 10MB
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <br>
                            <br>
                            <a href="<?php echo base_url(); ?>aktivitas-pegawai" class="btn btn-danger">Kembali</a>
                            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</div>


<style>
.select2-selection {
    height: 40px !important;
}

.form-control {
    height: 40px !important;
}
</style>
<script>
$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
// new FroalaEditor('textarea');
// const quill = new Quill('#editor-container', {
//     theme: 'snow'
// });

// Sync content with hidden textarea
// const textarea = document.getElementById('hidden-textarea');
// quill.on('text-change', () => {
//     textarea.value = quill.root.innerHTML;
// });
</script>
