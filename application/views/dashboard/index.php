<!-- <h4 class="fs-5 mt-5 mb-3">E-kinerja Kader Surabaya Hebat | Kelurahan Pradah Kalikendal | Dukuh Pakis</h4> -->
<!-- Row -->
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
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
                            <h6 class="fw-medium text-info mb-0">TOTAL RUMAH</h6>
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
                            <h6 class="fw-medium text-primary mb-0">TOTAL RUMAH BAYAR IPL</h6>
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
                            <h6 class="fw-medium text-success mb-0">IPL TERKUMPUL</h6>
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
                            <h2 class="fs-7">110.000<?php // echo $this->db->get("master_transaksi_aktivitas")->num_rows()
                                                    ?></h2>
                            <h6 class="fw-medium text-danger mb-0">TOTAL SALDO</h6>
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
    <style>
        body {
            background-color: #f0f2f5;
        }

        .profile-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px 20px;
            text-align: center;
        }

        .profile-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #198754;
            margin-bottom: 15px;
        }

        .role-badge {
            font-size: 0.9rem;
            background-color: #e6ffed;
            color: #198754;
            border-radius: 12px;
            padding: 6px 12px;
            display: inline-block;
            margin-top: 10px;
        }

        .welcome-msg {
            font-weight: 500;
            font-size: 1.1rem;
            margin-top: 15px;
            color: #333;
        }
    </style>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="profile-card w-100" style="max-width: 400px;">
            <img src="<?php echo base_url(); ?>logo-tsi-removebg-preview.png" alt="Foto Karikatur" class="profile-img">
            <h4 class="mb-0"><?php echo $_SESSION['username']['nama']; ?></h4>
            <div class="role-badge">
                <i class="bi bi-person-badge"></i> Koordinator Blok
            </div>
            <p class="welcome-msg">
                Selamat datang di <br><strong>APLIKASI MANAJEMEN PERUMAHAN TSI</strong><br>
                Terima kasih telah menjadi bagian dari paguyuban kami.
            </p>
            <br>
            <a class="btn btn-success w-100 d-flex flex-column justify-content-center align-items-center gap-2 p-3 rounded-2 shadow-sm" 
                href="<?= base_url('pendataan-keluarga'); ?>" style="transition: 0.2s;">
                    
                    <i class="fa fa-users fs-10 text-white"></i>

                    <div class="text-center">
                        <div class="fw-bold fs-5 text-white">Pendataan Warga</div>
                        <small class="text-white-50">Kelola data keluarga & anggota</small>
                    </div>
            </a>
        </div>
    </div>
<?php endif; ?>