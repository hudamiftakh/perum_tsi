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
                <h4 class="fw-semibold mb-8">Tambah Data KSH</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">KSH</li>
                        <li class="breadcrumb-item" aria-current="page">KSH Management</li>
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
	$dasis = $this->db->get_where("master_pegawai",array('id'=>$nik))->row_array();
}
?>
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="px-4 py-3 border-bottom">
                <h5 class="card-title fw-semibold mb-0">Tambah-Edit Pegawai KSH</h5>
            </div>
            <div class="card-body p-4">

                <form action="<?php echo base_url(); ?>master/pegawai/save" method="POST" enctype="multipart/form-data">
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">NIK</label>
                        <div class="col-sm-9">
							<?php if(!empty($nik)) : ?>
								<input type="hidden" class="form-control" placeholder="id" name="id"
                                value="<?php echo $dasis['id']; ?>">
							<?php endif; ?>
                            <input type="number" class="form-control" name="nik" value="<?php echo $dasis['nik']; ?>"
                                placeholder="NIK" autofocus>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Nama</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Nama" name="nama"
                                value="<?php echo $dasis['nama']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Usia</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Usia" name="usia"
                                value="<?php echo $dasis['usia']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1" class="form-label fw-semibold col-sm-2 col-form-label">Jenis
                            Kelamin</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="jk">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L" <?php echo ($dasis['jk']=='L') ? "selected": ""; ?>>L</option>
                                <option value="P" <?php echo ($dasis['jk']=='P') ? "selected": ""; ?>>P</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Alamat</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" placeholder="Alamat"
                                name="alamat"><?php echo $dasis['alamat']; ?></textarea>
                        </div>
                    </div>

                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">HP</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="HP" name="hp"
                                value="<?php echo $dasis['hp']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1" class="form-label fw-semibold col-sm-2 col-form-label">Jenis
                            Keanggotaan</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="jenis_anggota">
                                <option value="">Pilih Jenis Keanggotaan</option>
                                <option value="anggota"
                                    <?php echo ($dasis['jenis_anggota']=='anggota') ? "selected": ""; ?>>
                                    Anggota
                                </option>
                                <option value="koordinator"
                                    <?php echo ($dasis['jenis_anggota']=='koordinator') ? "selected": ""; ?>>Koordinator
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Rekening</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Rekening" name="rekening"
                                value="<?php echo $dasis['rekening']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">RW</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="rw">
                                <option value="">Pilih RW</option>
                                <?php for ($i=1; $i <=12 ; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php echo ($dasis['rw']==$i) ? "selected": ""; ?>>
                                    <?php echo $i ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">RT</label>
                        <div class="col-sm-9">
                            <select class="form-control" name="rt">
                                <option value="">Pilih RT</option>
                                <?php for ($i=1; $i <=12 ; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php echo ($dasis['rt']==$i) ? "selected": ""; ?>>
                                    <?php echo $i ?>
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Username" name="username"
                                value="<?php echo $dasis['username']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label">Password</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" placeholder="Password" name="password"
                                value="<?php echo $dasis['password_text']; ?>">
                        </div>
                    </div>
                    <div class="mb-4 row align-items-center">
                        <label for="exampleInputPassword1"
                            class="form-label fw-semibold col-sm-2 col-form-label"></label>
                        <div class="col-sm-9">
                            <a href="<?php echo base_url(); ?>master/pegawai" class="btn btn-danger">Kembali</a>
                            <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
