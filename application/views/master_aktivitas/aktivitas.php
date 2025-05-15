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
                <h4 class="fw-semibold mb-8">Aktivitas Management</h4>
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
<div class="modal" id="tambah_nomor">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Tambah Aktivitas</h4>
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
<!-- <div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card border-bottom border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h2 class="fs-7">120</h2>
                        <h6 class="fw-medium text-info mb-0">Total Broadcast</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-info display-6"><i class="ti ti-file-text"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-bottom border-primary">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div>
                        <h2 class="fs-7">150</h2>
                        <h6 class="fw-medium text-primary mb-0">Contact</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-primary display-6"><img
                                src="https://bootstrapdemos.adminmart.com/modernize/dist/assets/images/svgs/icon-user-male.svg"
                                alt=""></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-bottom border-success">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div>
                        <h2 class="fs-7">450</h2>
                        <h6 class="fw-medium text-success mb-0">Success Sent</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-success display-6"><i class="ti ti-checkup-list"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-bottom border-danger">
            <div class="card-body">
                <div class="d-flex no-block align-items-center">
                    <div>
                        <h2 class="fs-7">100</h2>
                        <h6 class="fw-medium text-danger mb-0">Failed</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-danger display-6"><img
                                src="https://bootstrapdemos.adminmart.com/modernize/dist/assets/images/svgs/icon-speech-bubble.svg"
                                alt=""></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> -->
<?php
$check_data = 1;
// $check_data = $this->db->get_where('broadcast', array('id' => $Auth['email']))->num_rows();
if ($check_data <= 0): ?>
<div class="card text-center">
    <div class="card-body">
        <h2>Master Aktivitas Management</h2>
        <br>
        <br>
        <img src="<?php echo base_url('assets/wabot.png') ?>" width="260px" alt="">
        <br>
        <br>
        <br>
        <div class="row">
            <div class="col-3"></div>
            <div class="col">
                <div class="row">
                    <div class="col-md-12 d-grid gap-6">
                        <a href="<?php echo base_url('master/aktivitas/add'); ?>" style="border-radius: 3px !important;"
                            class="btn waves-effect waves-light btn-lg  btn-outline-primary">
                            <i class="ti ti-plus"></i> Create New
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-3"></div>
        </div>
    </div>

</div>
<?php else: ?>
<?php if(!empty($this->session->flashdata('alert')['alert'])) :?>
<?php if($this->session->flashdata('alert')['alert']==true) : ?>
<script>
alert('Data berhasil diupdate');
</script>
<?php else : ?>
<script>
alert('Data gagal diupdate');
</script>
<?php endif;?>
<?php endif; ?>
<div class="card w-100 position-relative overflow-hidden">
    <div class="px-4 pt-4 card-header" nowrap="">
        <div style="float: right;">
            <a href="<?php echo base_url('master/aktivitas/add'); ?>" class="btn btn-outline-primary btn-lg rounded-end"
                style="border-radius: 3px !important;">
                <i class="ti ti-users"></i> &nbsp Tambah Aktivitas
            </a>

        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table border table-striped display" id="DataBroadcast"
                style="width: 100%; table-layout:fixed">
                <thead class="bg-success text-white">
                    <tr>
                        <th width="1%">#</th>
                        <th width="30%" nowrap="">AKTIVITAS</th>
                        <th width="10%" nowrap="">BOBOT</th>
                        <th width="10%" nowrap="">APPROVAL</th>
                        <th nowrap="" width="10%">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        function show_role($role){
                            if($role=='bidan'){
                                return "Bidan kelurahan";
                            }elseif($role=='pkk'){
                                return "Ketua PKK kelurahan";
                            }else{
                                return "Kasie Kesra kelurahan";
                            }
                        }
						$no=1;
						$result = $this->db->get("master_aktivitas")->result_array();
						foreach ($result as $key => $data) :
					?>
                    <tr>
                        <td><?php echo $no++ ?></td>
                        <td><b><?php echo $data['nama'] ?></b></td>
                        <td><?php echo $data['bobot'] ?></td>
                        <td><i class="fa fa-users"></i> <?php echo show_role($data['role_approve']); ?></td>
                        <td nowrap="">
                            <form action="" method="POST">
                                <a class="btn btn-primary btn-sm"
                                    href="<?php echo base_url('master/aktivitas/add/'.$data['id']); ?>"><i
                                        class="fa fa-edit"></i></a>
                                <input type="hidden" name="id" value="<?php echo $data['id'] ?>">
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Apakah anda yakin ?')"
                                    name="hapus" type="submit" name="hapus"><i class="fa fa-trash"></i></button>
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
<script>
var table = $('#DataBroadcast').DataTable({});
</script>