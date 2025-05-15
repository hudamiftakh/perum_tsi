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
<?php if(!empty($this->session->flashdata('alert')['alert'])) :?>
<?php if($this->session->flashdata('alert')['alert']==true) : ?>
<script>
alert('Data berhasil disimpan');
</script>
<?php else : ?>
<script>
alert('Data gagal disimpan');
</script>
<?php endif;?>
<?php endif; ?>
<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Tambah Data Aktivitas</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Aktivitas</li>
                        <li class="breadcrumb-item" aria-current="page">Aktivitas Management</li>
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
$nik = $this->uri->segment(4);
if(!empty($nik)){
	$dasis = $this->db->get_where("master_aktivitas",array('id'=>$nik))->row_array();
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="px-4 py-3 border-bottom">
                <h5 class="card-title fw-semibold mb-0">Tambah-Edit Aktivitas</h5>
            </div>
            <div class="card-body p-4">

                <form action="<?php echo base_url(); ?>master/aktivitas/save" method="POST"
                    enctype="multipart/form-data">
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1" class="form-label fw-semibold col-sm-2 col-form-label"
                            style="vertical-align: top;">Aktivitas</label>
                        <div class="col-sm-9">
                            <?php if(!empty($nik)) : ?>
                            <input type="hidden" class="form-control" placeholder="id" name="id"
                                value="<?php echo $dasis['id']; ?>">
                            <?php endif; ?>
                            <textarea name="nama" class="form-control" autofocus placeholder="Nama Aktivitas"
                                rows="3"><?php echo $dasis['nama']; ?></textarea>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Bobot</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" placeholder="Bobot" name="bobot"
                                value="<?php echo $dasis['bobot']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1" class="form-label fw-semibold col-sm-2 col-form-label">Role
                            Approve</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="role_approve" style="width: 97%;">
                                <option value="">Pilih Role</option>
                                <option value="bidan" <?php echo ($dasis['role_approve']=='bidan') ? "selected": ""; ?>>Bidan kelurahan</option>
                                <option value="pkk" <?php echo ($dasis['role_approve']=='pkk') ? "selected": ""; ?>>Ketua Pkk kelurahan</option>
                                <option value="kasi_kesra" <?php echo ($dasis['role_approve']=='kasi_kesra') ? "selected": ""; ?>>Kasi kesra</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label"></label>
                        <div class="col-sm-9">
                            <a href="<?php echo base_url('master/aktivitas'); ?>" class="btn btn-danger">Kembali</a>
                            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>