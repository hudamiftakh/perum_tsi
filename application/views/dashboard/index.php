<!-- <h4 class="fs-5 mt-5 mb-3">E-kinerja Kader Surabaya Hebat | Kelurahan Pradah Kalikendal | Dukuh Pakis</h4> -->
<!-- Row -->
<?php if($_SESSION['username']['role']=='admin') : ?>
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="card border-bottom border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h2 class="fs-7">
							<?php 
								// echo $this->db->get("master_pegawai")->num_rows();
							?>
						</h2>
                        <h6 class="fw-medium text-info mb-0">TOTAL ANGGOTA</h6>
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
                        <h2 class="fs-7">
							<?php 
								// echo $this->db->query("
								// SELECT 
								// 	b.*, 
								// 	a.*, 
								// 	b.nama as pegawai,
								// 	b.nik as nik_pegawai,
								// 	ROW_NUMBER() OVER (ORDER BY a.total DESC) AS ranking
								// FROM 
								// 	master_pegawai AS b
								// LEFT JOIN 
								// 	report AS a ON a.nik = b.nik
								//  WHERE 
								// a.total > 1
								// ORDER BY 
								// 	ranking
								// ")->num_rows();
							?>
						</h2>
                        <h6 class="fw-medium text-primary mb-0">ANGGOTA AKTIF</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-primary display-6"><i class="ti ti-users"></i></span>
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
                        <h2 class="fs-7">
						<?php 
                            // echo $this->db->query("
                            // SELECT 
                            //     b.*, 
                            //     a.*, 
                            //     b.nama as pegawai,
                            //     b.nik as nik_pegawai,
                            //     ROW_NUMBER() OVER (ORDER BY a.total DESC) AS ranking
                            // FROM 
                            //     master_pegawai AS b
                            // LEFT JOIN 
                            //     report AS a ON a.nik = b.nik
                            //     WHERE 
                            //     -- kondisi yang Anda inginkan, misalnya:
                            // a.total is null
                            // ORDER BY 
                            //     ranking
                            // ")->num_rows();
							?>
						</h2>
                        <h6 class="fw-medium text-success mb-0">ANGGOTA TIDAK AKTIF</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-success display-6"><i class="ti ti-phone"></i></span>
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
                        <h2 class="fs-7"><?php // echo $this->db->get("master_transaksi_aktivitas")->num_rows()?></h2>
                        <h6 class="fw-medium text-danger mb-0">TOTAL AKTIVITAS</h6>
                    </div>
                    <div class="ms-auto">
                        <span class="text-danger display-6"><i class="ti ti-send"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php else : ?>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <h4>Halo <?php echo $_SESSION['username']['nama']; ?> !!</h4>
            <ul class="list-unstyled">
                <li>Mohon ksh selalu aktif mengisi aktivitas sebagai acuan untuk pemberian penilaian individu</li>
                <li>Mohon foto yang di upload menyesuakan kondisi yang saat ini dilakukan;</li>
                <li>Informasi akan ditampilkan aplikasi;</li>
            </ul>
        </div>

        <div class="alert alert-info">
            <h4>Penyelia</h4>
            <ul class="list-unstyled">
                <li><b>Penyelia 1:</b> , Telp: , e-mail:</li>
                <li><b>Penyelia 2:</b> , Telp: , e-mail: </li>
            </ul>
        </div>
    </div>
</div>

<?php endif;?>
