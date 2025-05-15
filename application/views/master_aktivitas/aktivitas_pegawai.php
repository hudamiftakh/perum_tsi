<?php $Auth = $this->session->userdata['username']; ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
</style>


<div class="card border-top border-success">
    <div class="card-header" style="font-size: 20px;">
        <i class="ti ti-filter"></i> Filter
    </div>
    <div class="card-body">
        <form action="" method="GET">
            <div class="row">
                <div class="col">
                    <label for="">Bulan</label>
                    <select class="form-control js-example-basic-single" name="bulan" style="width: 97%;" required>
                        <option value="">Pilih Bulan</option>
                        <?php 
						$bulan_nama = [
							1 => 'Januari', 
							2 => 'Februari', 
							3 => 'Maret', 
							4 => 'April', 
							5 => 'Mei', 
							6 => 'Juni', 
							7 => 'Juli', 
							8 => 'Agustus', 
							9 => 'September', 
							10 => 'Oktober', 
							11 => 'November', 
							12 => 'Desember'
						];

						for ($i = 1; $i <= 12; $i++) : ?>
                        <option value="<?php echo $i; ?>" <?php echo ($_REQUEST['bulan'] == $i) ? "selected" : ""; ?>>
                            <?php echo $bulan_nama[$i]; ?>
                        </option>
                        <?php endfor; ?>

                    </select>
                </div>
                <div class="col">
                    <label for="">Jenis Aktivitas</label>
                    <select class="form-control js-example-basic-single" name="jenis_aktivitas" style="width: 97%;">
                        <option value="">Pilih Aktivitas</option>
                        <?php 
							$aktivitas = $this->db->get("master_aktivitas")->result_array();
							foreach ($aktivitas as $key => $value) :
						?>
                        <option value="<?php echo $value['id']; ?>"
                            <?php echo ($_REQUEST['jenis_aktivitas']==$value['id']) ? "selected": ""; ?>>
                            <?php echo $value['nama'].""; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <label for="">Kader - KSH</label>
                    <select class="form-control js-example-basic-single" name="id_pegawai" style="width: 97%;">
                        <option value="">Pilih KSH</option>
                        <?php 
							if(in_array($_SESSION['username']['role'],array('admin','approval'))) {
								$ksh = $this->db->get("master_pegawai")->result_array();
							}else{
								$id = $_SESSION['username']['id'];
								$ksh = $this->db->where(array('id'=>$id))->get("master_pegawai")->result_array();
							};
							foreach ($ksh as $key => $value) :
						?>
                        <option value="<?php echo $value['id']; ?>"
                            <?php echo ($_REQUEST['id_pegawai']==$value['id']) ? "selected": ""; ?>>
                            <?php echo $value['nik']." -".$value['nama']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <label for="">Aprroval</label>
                    <select class="form-control js-example-basic-single" name="approval" style="width: 97%;">
                        <option value="">Pilih Aktivitas</option>
                        <?php 
							if(in_array($_SESSION['username']['role'],array('admin','approval'))) {
								$approval = $this->db->get("master_admin")->result_array();
							}else{
								$id = $_SESSION['username']['id'];
								$approval = $this->db->where(array('id'=>$id))->get("master_admin")->result_array();
							};
							foreach ($approval as $key => $value) :
						?>
                        <option value="<?php echo $value['username']; ?>"
                            <?php echo ($_REQUEST['approval']==$value['username']) ? "selected" : ""; ?>>Menunggu
                            Approve <?php echo $value['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="padding-top: 12px;">
                <button type="submit" class="btn btn-success" name="filter"><i class="ti ti-filter"></i> Filter</button>
                <a href="<?php echo base_url('aktivitas-pegawai'); ?>" class="btn btn-warning" name=""><i
                        class="ti ti-refresh"></i> Reset</a>
            </div>
        </form>
    </div>
</div>
<?php if ($this->session->flashdata('success')): ?>
<div class="alert alert-success">
    <?php echo $this->session->flashdata('success'); ?>
</div>
<?php endif; ?>

<?php if ($this->session->flashdata('error')): ?>
<div class="alert alert-danger">
    <?php echo $this->session->flashdata('error'); ?>
</div>
<?php endif; ?>

Keterangan status :
<img height="15" width="15"
style="width: 15px !important; height: 15px !important;"
src="<?php echo base_url('assets/approved.png') ?>" alt="">
Disetujui
<img height="20" width="20" style="width: 22px !important; height: 22px !important;"
    src="<?php echo base_url('assets/icon_watch.png') ?>" alt="">
Menunggu Persetujuan 
<img height="20" width="20"
style="width: 22px !important; height: 22px !important;"
src="<?php echo base_url('assets/rejected.png') ?>" alt="">
Ditolak 

<br>
<br>
<div class="card border-top border-success">
    <div class="card-body">
        <form action="" method="POST">
            <input type="hidden" name="approval" value="<?php echo $_REQUEST['approval']; ?>">
            <input type="hidden" name="id_pegawai" value="<?php echo $_REQUEST['id_pegawai']; ?>">
            <input type="hidden" name="jenis_aktivitas" value="<?php echo $_REQUEST['jenis_aktivitas']; ?>">
            <input type="hidden" name="bulan" value="<?php echo $_REQUEST['bulan']; ?>">
            <div class="table-responsive">
                <table class="table border table-striped display" id="DataAktivitas"
                    style="width: 100%; table-layout:fixed">
                    <thead class="bg-success text-white">
                        <tr>
                            <th width="3px" style="padding-left: 14px;">
                                <?php if(in_array($_SESSION['username']['role'],array('admin','approval'))) : ?>
                                <center><input type="checkbox" style="width: 15px;" id="checkAll"></center>
                                <?php endif; ?>
                            </th>
                            <th width="30px" nowrap="">STATUS</th>
                            <th width="30px" nowrap="">TANGGAL</th>
                            <th width="200px" nowrap="">AKTIVITAS</th>
                            <th width="250px" nowrap="">CATATAN</th>
                            <th width="60px" nowrap="">UPDATE AT</th>
                            <th nowrap="" width="60px">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
						$no=1;
						if(in_array($_SESSION['username']['role'], array('admin','approval'))) { 
							if(isset($_REQUEST['filter'])){
								$bulan = $_REQUEST['bulan'];
								$id_aktivitas = (!empty($_REQUEST['jenis_aktivitas'])) ? "AND id_aktivitas='".$_REQUEST['jenis_aktivitas']."'" : "";
								$id_pegawai = (!empty($_REQUEST['id_pegawai'])) ? "AND id_pegawai='".$_REQUEST['id_pegawai']."'" : "";
								$approval = (!empty($_REQUEST['approval'])) ? "AND c.role_approve='".$_REQUEST['approval']."' AND status='1'" : "";
								
								$filter = "WHERE MONTH(a.created_at) = '".$bulan."'".$id_aktivitas.$id_pegawai.$approval;
							}
							$result = $this->db->query("
									SELECT a.tanggal, a.log_catatan, a.status,  b.nama as kader, a.catatan, c.nama as nama_aktivitas, c.bobot, a.file_path , a.catatan, a.id, a.created_at FROM master_transaksi_aktivitas as a
									LEFT JOIN master_pegawai as b ON a.id_pegawai = b.id
									LEFT JOIN master_aktivitas as c ON a.id_aktivitas = c.id
									".$filter."
									ORDER BY a.created_at DESC
							")->result_array();
						}else{
							$role = $_SESSION['username']['id'];
							if(isset($_REQUEST['filter'])){
							    $bulan_filter = (!empty($_REQUEST['bulan'])) ? " MONTH(a.created_at)='".$_REQUEST['bulan']."'" : "";;
								$id_aktivitas = (!empty($_REQUEST['jenis_aktivitas'])) ? "AND id_aktivitas='".$_REQUEST['jenis_aktivitas']."'" : "";
								$id_pegawai = (!empty($_REQUEST['id_pegawai'])) ? "AND id_pegawai='".$_REQUEST['id_pegawai']."'" : "";
                                $approval = (!empty($_REQUEST['approval'])) ? "AND c.role_approve='".$_REQUEST['approval']."' AND status='1'" : "";

                                if($_SESSION['username']['role']=='approval') {
                                    $filter = "WHERE ".$bulan_filter.$id_aktivitas.$approval."AND c.role_approve='".$_SESSION['username']['username']."' AND status='1'";
                                }else{
                                    $filter = "WHERE a.id_pegawai='".$role."' AND MONTH(a.created_at) = '".$bulan."'".$id_aktivitas.$id_pegawai;
                                }
							}else{
                                if($_SESSION['username']['role']=='approval') {
                                    $filter = "WHERE c.role_approve='".$_SESSION['username']['username']."'";
                                }else{
                                    $filter = "WHERE a.id_pegawai='".$role."'";
                                }
                            }
                            
							$result = $this->db->query("
								SELECT a.tanggal, a.log_catatan, a.status,b.nama as kader, a.catatan, c.nama as nama_aktivitas, c.bobot, a.file_path , a.catatan, a.id, a.created_at FROM master_transaksi_aktivitas as a
								LEFT JOIN master_pegawai as b ON a.id_pegawai = b.id
								LEFT JOIN master_aktivitas as c ON a.id_aktivitas = c.id
								".$filter."
								ORDER BY a.created_at DESC
							")->result_array();
						}
						foreach ($result as $key => $data) :
					?>
                        <tr>
                            <td>
                                <?php if(in_array($_SESSION['username']['role'],array('admin','approval'))) : ?>
                                <?php if($data['status']==1) : ?>
                                <center><input type="checkbox" name="id_list[]" value="<?php echo $data['id']; ?>"
                                        style="width: 15px;" class="row-check"></center>
                                <?php endif;?>
                                <?php else :  echo $no++; endif;?>
                            </td>
                            
                            <td>
                                <?php if($data['status']=='1') :  ?>
                                <center><img height="20" width="20"
                                        style="width: 22px !important; height: 22px !important;"
                                        src="<?php echo base_url('assets/icon_watch.png') ?>" alt=""></center>
                                <?php elseif($data['status']=='2') : ?>
                                <center><img height="15" width="15"
                                        style="width: 25px !important; height: 25px !important;"
                                        src="<?php echo base_url('assets/rejected.png') ?>" alt="">
                                    <br>
                                    Ditolak
                                    <br>
                                    <u>Alasan :</u><br>
                                    <?php echo $data['log_catatan']; ?>
                                </center>

                                <?php elseif($data['status']=='3') : ?>
                                <center><img height="15" width="15"
                                        style="width: 15px !important; height: 15px !important;"
                                        src="<?php echo base_url('assets/approved.png') ?>" alt=""></center>
                                <?php endif;?>
                            </td>
                            <td><?php echo date("d-m-Y", strtotime($data['tanggal'])); ?></td>
                            <td>
                                <u>Nama Kader</u> :
                                <b><?php echo $data['kader'] ?></b> <br>
                                <?php echo $data['nama_aktivitas'] ?> <br>
                                <!--Beban : <?php echo ($data['bobot']) ?>-->
                                <!--(<i><?php echo ($data['jenis_waktu_pengerjaan']==0) ? "Pada Jam Kerja": "Diluar Jam Kerja"; ?></i>)-->
                                <u>Data Pendukung :</u>
                                <br>
                                <label style="cursor: pointer;"
                                    onclick="javascript:void(window.open('<?php echo base_url() ?>uploads/<?php echo $data['file_path'] ?>', 'tadrgetWindow', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1024, height=700'))">
                                    <img data-duration="700" style="transform: scale(1) translate(0px, 0px);"
                                        class="img-thumbnail img-responsive zoomify"
                                        src="<?php echo base_url() ?>uploads/<?php echo $data['file_path'] ?>"
                                        width="40%">
                                </label>
                            </td>

                            <td>
                                <div><?php echo $data['catatan'];  ?></div>
                            </td>
                            <td><?php echo $data['created_at'];  ?></td>
                            <td nowrap="">
                                <?php if(in_array($_SESSION['username']['role'], array('pengguna')) and in_array($data['status'], array('1'))) : ?>
                                <form action="<?php echo base_url('aktivitas-pegawai-hapus') ?>" method="POST">
                                    <a class="btn btn-primary btn-sm" title="Edit Aktivitas"
                                        href="<?php echo base_url('aktivitas-pegawai/entry/'.$data['id']); ?>"><i
                                            class="fa fa-edit"></i></a>
                                    <input type="hidden" name="id" value="<?php echo $data['id'] ?>">
                                    <button class="btn btn-danger btn-sm" title="Hapus Aktivitas"
                                        onclick="return confirm('Apakah anda yakin ?')" name="hapus" type="submit"
                                        name="hapus"><i class="fa fa-trash"></i></button>
                                </form>
                                <?php else : ?>

                                <?php if(in_array($data['status'], array('3','1'))): ?>
                                <?php if(in_array($_SESSION['username']['role'], array('admin'))) : ?>
                                <form action="<?php echo base_url('aktivitas-pegawai-hapus') ?>" method="POST">
                                    <a class="btn btn-primary btn-sm"
                                        href="<?php echo base_url('aktivitas-pegawai/entry/'.$data['id']); ?>"><i
                                            class="fa fa-edit"></i></a>
                                    <input type="hidden" name="id" value="<?php echo $data['id'] ?>">
                                    <button class="btn btn-danger btn-sm" title="Tolak Aktivitas"
                                        onclick="return confirm('Apakah anda yakin ?')" name="hapus" type="submit"
                                        name="hapus"><i class="fa fa-trash"></i></button>
                                </form>
                                <?php endif ?>
                                <?php endif ?>
                                <form action="" method="POST">
                                    <?php if(in_array($data['status'], array('3','1')) and in_array($_SESSION['username']['role'], array('approval','admin'))): ?>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#tolak<?php echo $data['id'] ?>"><i class="fa fa-ban"
                                            aria-hidden="true"></i></button>
                                    <?php endif; ?>
                                    <div class="modal" id="tolak<?php echo $data['id'] ?>" tabindex="-1"
                                        aria-labelledby="tolak<?php echo $data['id'] ?>Label" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tolak Aktivitas</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label for="">Catatan</label>
                                                    <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                                    <textarea name="catatan_log" class="form-control"
                                                        style="width: 80%;"></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" name="tolak"
                                                        class="btn btn-primary">Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <br>
            <?php if(in_array($_SESSION['username']['role'],array('admin','approval'))) : ?>
            <button type="submit" name="approve" id="submitButton" class="btn btn-success"><i
                    class="fa fa-fw fa-cloud-upload"></i> Simpan
                Pengesahan</button>
            <?php endif; ?>
        </form>
    </div>
</div>


<style>
.select2-selection {
    height: 40px !important;
}
</style>
<script>
$(document).ready(function() {
    $('.js-example-basic-single').select2();
});

var table = $('#DataAktivitas').DataTable({
    columnDefs: [{
            targets: 0, // Kolom pertama
            orderable: false, // Sorting diaktifkan untuk kolom pertama
        },
        {
            targets: '_all', // Semua kolom lainnya
            orderable: true, // Menonaktifkan sorting
        },
    ],
});



$(document).ready(function() {
    // Inisialisasi DataTable
    var table = $('#DataAktivitas').DataTable();

    // Fungsi untuk "Check All"
    $('#checkAll').on('click', function() {
        var isChecked = $(this).is(':checked');
        $('.row-check').prop('checked', isChecked);
        toggleSubmitButton(); // Memanggil fungsi untuk toggle tombol submit
    });

    // Fungsi untuk uncheck "Check All" jika salah satu checkbox tidak dicentang
    $('#DataAktivitas').on('change', '.row-check', function() {
        if (!$(this).is(':checked')) {
            $('#checkAll').prop('checked', false);
        }

        // Jika semua checkbox dicentang, centang "Check All"
        if ($('.row-check:checked').length === $('.row-check').length) {
            $('#checkAll').prop('checked', true);
        }

        toggleSubmitButton(); // Memanggil fungsi untuk toggle tombol submit
    });

    // Fungsi untuk men-toggle tombol submit
    function toggleSubmitButton() {
        var anyChecked = $('.row-check:checked').length > 0;
        $('#submitButton').prop('disabled', !anyChecked);
        $('#tolakButton').prop('disabled', !anyChecked);
    }

    // Inisialisasi state tombol submit saat halaman pertama kali dimuat
    toggleSubmitButton();
});
</script>