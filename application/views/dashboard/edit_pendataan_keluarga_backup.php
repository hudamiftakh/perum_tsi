<!DOCTYPE html>
<html lang="id">
<?php
$Auth = $this->session->userdata['username'];
?>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Form Pendataan Keluarga</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>dist/logo_2.png" />
    <!-- Icon bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>


    <style>
        body {
            background-color: #d4edda;
        }

        .card {
            border-radius: 1rem;
        }

        .remove-member-btn {
            position: absolute;
            right: 10px;
            top: 10px;
        }

        .form-label {
            font-weight: 500;
        }

        .anggota-header {
            font-size: 1.1rem;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        @media (max-width: 576px) {
            .form-label {
                font-size: 0.9rem;
            }

            .form-control,
            .form-select {
                font-size: 0.9rem;
            }

            .remove-member-btn {
                right: 5px;
                top: 5px;
                font-size: 0.75rem;
                padding: 2px 6px;
            }

            .anggota-header {
                font-size: 1rem;
            }
        }

        /* Select2 full width fix */
        .select2-container {
            width: 100% !important;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div id="countdown-timer"
            class="position-fixed top-0 start-0 w-100 bg-white shadow-sm d-flex align-items-center justify-content-center gap-2 px-3 py-2"
            style="z-index: 1050; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
            <!-- <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
        style="width: 32px; height: 32px;">
        <i class="bi bi-clock-history fs-6"></i>
      </div> -->
            <div class="text-center">
                <div class="fw-bold text-dark small">Berakhir pada </div>
                <div id="timer-display" class="fw-semibold text-danger small">Loading...</div>
            </div>
        </div>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden mt-5">
            <div class="card-header text-center d-flex flex-column align-items-center justify-content-center p-4"
                style="height: 160px; background: linear-gradient(135deg,rgb(225, 248, 227), #ffffff);">
                <img src="<?php echo base_url('logo-tsi-removebg-preview.png'); ?>" alt="Logo TSI"
                    style="width: 150px; margin-bottom: 15px;">
                <h4 class="fw-bold mb-1 text-dark">PENDATAAN WARGA</h4>
                <p class="mb-0 text-muted" style="font-size: 1.1rem;">
                    Perumahan Taman Sukodono Indah
                </p>
            </div>
            <?php
            if (!empty($Auth['username'])) {
                $_REQUEST['verifikasi'] = 'admin_ok';
            } else {
                $_REQUEST['verifikasi'] = $_REQUEST['verifikasi'];
            }
            if (!empty($_REQUEST['verifikasi'])) :
                $no_kk_request = $_REQUEST['no_kk'];
                $id = decrypt_url($this->uri->segment(2));
                $data_kk = $this->db->get_where("master_keluarga", array('id' => $id))->row_array();
                $data_keluarga = $this->db->get_where("master_anggota_keluarga", array('keluarga_id' => $id))->result_array();
                $selected_nomor_rumah = explode('| ', $data_kk['nomor_rumah']);
                if ($Auth['username'] != 'admin') {
                    if ($no_kk_request != $data_kk['no_kk']) {
                        // Jika no_kk tidak cocok dan bukan admin, tolak
                        echo '<br>
                        <div class="alert alert-danger" role="alert">
                        <strong>Nomor KK tidak sesuai!</strong><br>
                        Maaf, nomor Kartu Keluarga yang Anda masukkan tidak cocok dengan data kami. 
                        <br>Untuk menjaga keamanan data, Anda tidak diizinkan melanjutkan proses pengeditan.
                        <br><br>Silakan periksa kembali Nomor KK Anda atau hubungi pengurus paguyuban jika perlu bantuan. 
                        <br><br><em>Terima kasih atas pengertiannya.</em>
                        </div>';
                        exit;
                    }
                }
            ?>
                <div class="card-body">
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
                    <form id="formKeluarga" enctype="multipart/form-data">
                        <h6 class="mb-3">Data Kartu Keluarga</h6>
                        <div class="row g-3 mb-3">
                            <input type="hidden" name="keluarga_id" value="<?php echo $data_kk['id']; ?>">
                            <!-- Nomor Rumah pakai select2 -->
                            <div class="col-md-6">
                                <label for="nomorRumah" class="form-label">Nomor Rumah</label>
                                <select class="form-select select2" id="nomorRumah" name="nomor_rumah[]"
                                    value="<?php echo $data_kk['nomor_rumah']; ?>" multiple="multiple" required>
                                    <option value="">Pilih Nomor Rumah</option>
                                    <?php
                                    $result_rumah = $this->db->get("master_rumah")->result_array();
                                    foreach ($result_rumah as $key => $value) :

                                    ?>
                                        <option value="<?php echo $value['alamat']; ?>"
                                            <?= in_array($value['alamat'], $selected_nomor_rumah) ? 'selected' : '' ?>>
                                            <?php echo $value['alamat']; ?> - <?php echo $value['nama']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="noKK" class="form-label">Nomor KK</label>
                                <input type="text" class="form-control" id="noKK" value="<?php echo $data_kk['no_kk']; ?>"
                                    name="no_kk" required />
                            </div>
                            <!-- Status Tempat Tinggal -->
                            <div class="col-md-6">
                                <label for="statusRumah" class="form-label">Status Tempat Tinggal (Rumah TSI)</label>
                                <select class="form-select" id="statusRumah" name="status_rumah" required>
                                    <option value="">Pilih Status</option>
                                    <option value="Rumah Sendiri"
                                        <?= $data_kk['status_rumah'] == 'Rumah Sendiri' ? 'selected' : '' ?>>Rumah Sendiri
                                    </option>
                                    <option value="Sewa/Kontrak"
                                        <?= $data_kk['status_rumah'] == 'Sewa/Kontrak' ? 'selected' : '' ?>>Sewa/Kontrak
                                    </option>
                                    <option value="Musiman" <?= $data_kk['status_rumah'] == 'Musiman' ? 'selected' : '' ?>>
                                        Musiman</option>
                                    <option value="Usaha" <?= $data_kk['status_rumah'] == 'Usaha' ? 'selected' : '' ?>>Usaha
                                    </option>
                                </select>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div class="col-md-6">
                                <label for="alamat" class="form-label">Alamat Lengkap (KK)</label>
                                <input type="text" class="form-control" id="alamat" name="alamat"
                                    value="<?= $data_kk['alamat'] ?>" required />
                            </div>

                            <!-- Provinsi -->
                            <div class="col-sm-6 col-md-3">
                                <label for="provinsi" class="form-label">Provinsi (KK)</label>
                                <select class="form-select select2" id="provinsi" name="provinsi" required>
                                    <option value="<?= $data_kk['provinsi'] ?>" selected><?= $data_kk['provinsi'] ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Kota/Kabupaten -->
                            <div class="col-sm-6 col-md-3">
                                <label for="kota" class="form-label">Kota/Kabupaten (KK)</label>
                                <select class="form-select select2" id="kota" name="kota" required>
                                    <option value="<?= $data_kk['kota'] ?>" selected><?= $data_kk['kota'] ?></option>
                                </select>
                            </div>

                            <!-- Kecamatan -->
                            <div class="col-sm-6 col-md-3">
                                <label for="kecamatan" class="form-label">Kecamatan (KK)</label>
                                <select class="form-select select2" id="kecamatan" name="kecamatan" required>
                                    <option value="<?= $data_kk['kecamatan'] ?>" selected><?= $data_kk['kecamatan'] ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Kelurahan -->
                            <div class="col-sm-6 col-md-3">
                                <label for="kelurahan" class="form-label">Kelurahan (KK)</label>
                                <select class="form-select select2" id="kelurahan" name="kelurahan" required>
                                    <option value="<?= $data_kk['kelurahan'] ?>" selected><?= $data_kk['kelurahan'] ?>
                                    </option>
                                </select>
                            </div>

                            <!-- Upload KK -->
                            <div class="col-md-6 mt-3">
                                <label for="fileKK" class="form-label">Upload Kartu Keluarga</label>
                                <input type="file" class="form-control" id="fileKK" name="file_kk"
                                    accept=".pdf,.jpg,.jpeg,.png" />

                                <?php if (!empty($data_kk['file_kk'])): ?>
                                    <small class="text-muted">
                                        File saat ini:
                                        <a href="<?= base_url('uploads/' . $data_kk['file_kk']) ?>" target="_blank">Lihat</a>
                                    </small>
                                    <!-- Simpan nama file lama dalam input hidden -->
                                    <input type="hidden" name="file_kk_existing" value="<?= $data_kk['file_kk'] ?>">
                                <?php endif; ?>
                                <small class="form-text text-muted">Maksimal 5Mb.</small>
                            </div>


                            <!-- Nomor Whatsapp -->
                            <div class="col-md-6 mt-3">
                                <label for="noHp" class="form-label">Nomor Whatsapp</label>
                                <input type="text" class="form-control" id="noHp" name="no_hp"
                                    value="<?= $data_kk['no_hp'] ?>" required />
                                <small class="form-text text-muted">Nomor ini akan digunakan untuk menerima notifikasi IPL dan informasi paguyuban melalui WhatsApp.</small>
                            </div>
                        </div>

                        <h6 class="mt-4 mb-3">Anggota Keluarga</h6>
                        <?php
                        $no = 1;
                        $data_keluarga = $this->db->get_where("master_anggota_keluarga", array('keluarga_id' => $id))->result_array();
                        $jumlah_rows = $this->db->get_where("master_anggota_keluarga", array('keluarga_id' => $id))->num_rows();
                        foreach ($data_keluarga as $datanya) :
                        ?>
                            <div class="card border rounded p-3 mt-3 position-relative anggota-keluarga">
                                <a type="submit"
                                    href="<?php echo base_url('dashboard/hapus_anggota_keluarga?id=' . $datanya['id'] . "&id_keluarga=" . $datanya['keluarga_id']); ?>"
                                    onclick="return confirm('Apakah anda yakin');"
                                    class="btn btn-sm btn-danger remove-member-btn">X</a>
                                <div class="anggota-header">Anggota Ke-<span><?= $no; ?></span></div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <input type="hidden" name="anggota_id[]" value="<?php echo $datanya['id']; ?>">
                                        <input type="hidden" name="keluarga_id" value="<?php echo $datanya['keluarga_id']; ?>">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control"
                                            value="<?= htmlspecialchars($datanya['nama']); ?>" name="nama[]" required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">NIK</label>
                                        <input type="text" class="form-control"
                                            value="<?= htmlspecialchars($datanya['nik']); ?>" name="nik[]" required />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Jenis Kelamin</label>
                                        <select class="form-select" name="jenis_kelamin[]" required>
                                            <option value="">Pilih Jenis Kelamin...</option>
                                            <option value="Laki-laki"
                                                <?= ($datanya['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki
                                            </option>
                                            <option value="Perempuan"
                                                <?= ($datanya['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Agama</label>
                                        <select class="form-select" name="agama[]" required>
                                            <option value="">Pilih Agama...</option>
                                            <option value="Islam" <?= ($datanya['agama'] == 'Islam') ? 'selected' : ''; ?>>Islam
                                            </option>
                                            <option value="Kristen Protestan"
                                                <?= ($datanya['agama'] == 'Kristen Protestan') ? 'selected' : ''; ?>>Kristen
                                                Protestan</option>
                                            <option value="Katolik" <?= ($datanya['agama'] == 'Katolik') ? 'selected' : ''; ?>>
                                                Katolik</option>
                                            <option value="Hindu" <?= ($datanya['agama'] == 'Hindu') ? 'selected' : ''; ?>>Hindu
                                            </option>
                                            <option value="Buddha" <?= ($datanya['agama'] == 'Buddha') ? 'selected' : ''; ?>>
                                                Buddha</option>
                                            <option value="Konghucu"
                                                <?= ($datanya['agama'] == 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                                            <option value="Lainnya" <?= ($datanya['agama'] == 'Lainnya') ? 'selected' : ''; ?>>
                                                Lainnya</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Status Perkawinan</label>
                                        <select class="form-select" name="status_perkawinan[]" required>
                                            <option value="">Pilih Status Perkawinan...</option>
                                            <option value="Belum Kawin"
                                                <?= ($datanya['status_perkawinan'] == 'Belum Kawin') ? 'selected' : ''; ?>>Belum
                                                Kawin</option>
                                            <option value="Kawin"
                                                <?= ($datanya['status_perkawinan'] == 'Kawin') ? 'selected' : ''; ?>>Kawin
                                            </option>
                                            <option value="Cerai Hidup"
                                                <?= ($datanya['status_perkawinan'] == 'Cerai Hidup') ? 'selected' : ''; ?>>Cerai
                                                Hidup</option>
                                            <option value="Cerai Mati"
                                                <?= ($datanya['status_perkawinan'] == 'Cerai Mati') ? 'selected' : ''; ?>>Cerai
                                                Mati</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Hubungan</label>
                                        <select class="form-select" name="hubungan[]" required>
                                            <option value="">Pilih...</option>
                                            <option value="Kepala Keluarga"
                                                <?= ($datanya['hubungan'] == 'Kepala Keluarga') ? 'selected' : ''; ?>>Kepala
                                                Keluarga</option>
                                            <option value="Istri" <?= ($datanya['hubungan'] == 'Istri') ? 'selected' : ''; ?>>
                                                Istri</option>
                                            <option value="Anak" <?= ($datanya['hubungan'] == 'Anak') ? 'selected' : ''; ?>>Anak
                                            </option>
                                            <option value="Orang Tua"
                                                <?= ($datanya['hubungan'] == 'Orang Tua') ? 'selected' : ''; ?>>Orang Tua
                                            </option>
                                            <option value="Lainnya"
                                                <?= ($datanya['hubungan'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Lahir</label>
                                        <input type="date" class="form-control"
                                            value="<?= htmlspecialchars($datanya['tgl_lahir']); ?>" name="tgl_lahir[]"
                                            required />
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Pekerjaan</label>
                                        <select class="form-select" name="pekerjaan[]" required>
                                            <option value="">Pilih Pekerjaan...</option>
                                            <option value="Belum Bekerja"
                                                <?= ($datanya['pekerjaan'] == 'Belum Bekerja') ? 'selected' : ''; ?>>Belum
                                                Bekerja (Balita/Anak-anak)</option>
                                            <option value="Pelajar/Mahasiswa"
                                                <?= ($datanya['pekerjaan'] == 'Pelajar/Mahasiswa') ? 'selected' : ''; ?>>
                                                Pelajar/Mahasiswa</option>
                                            <option value="ASN" <?= ($datanya['pekerjaan'] == 'ASN') ? 'selected' : ''; ?>>ASN
                                            </option>
                                            <option value="TNI/Polri"
                                                <?= ($datanya['pekerjaan'] == 'TNI/Polri') ? 'selected' : ''; ?>>TNI/Polri
                                            </option>
                                            <option value="Pegawai BUMN"
                                                <?= ($datanya['pekerjaan'] == 'Pegawai BUMN') ? 'selected' : ''; ?>>Pegawai BUMN
                                            </option>
                                            <option value="Pegawai Swasta"
                                                <?= ($datanya['pekerjaan'] == 'Pegawai Swasta') ? 'selected' : ''; ?>>Pegawai
                                                Swasta</option>
                                            <option value="Wiraswasta"
                                                <?= ($datanya['pekerjaan'] == 'Wiraswasta') ? 'selected' : ''; ?>>Wiraswasta
                                            </option>
                                            <option value="Petani"
                                                <?= ($datanya['pekerjaan'] == 'Petani') ? 'selected' : ''; ?>>Petani</option>
                                            <option value="Nelayan"
                                                <?= ($datanya['pekerjaan'] == 'Nelayan') ? 'selected' : ''; ?>>Nelayan</option>
                                            <option value="Ibu Rumah Tangga"
                                                <?= ($datanya['pekerjaan'] == 'Ibu Rumah Tangga') ? 'selected' : ''; ?>>Ibu
                                                Rumah Tangga</option>
                                            <option value="Pensiunan"
                                                <?= ($datanya['pekerjaan'] == 'Pensiunan') ? 'selected' : ''; ?>>Pensiunan
                                            </option>
                                            <option value="Ahli IT"
                                                <?= ($datanya['pekerjaan'] == 'Ahli IT') ? 'selected' : ''; ?>>Ahli IT</option>
                                            <option value="Programmer"
                                                <?= ($datanya['pekerjaan'] == 'Programmer') ? 'selected' : ''; ?>>Programmer
                                            </option>
                                            <option value="Desainer Grafis"
                                                <?= ($datanya['pekerjaan'] == 'Desainer Grafis') ? 'selected' : ''; ?>>Desainer
                                                Grafis</option>
                                            <option value="Dokter"
                                                <?= ($datanya['pekerjaan'] == 'Dokter') ? 'selected' : ''; ?>>Dokter</option>
                                            <option value="Perawat"
                                                <?= ($datanya['pekerjaan'] == 'Perawat') ? 'selected' : ''; ?>>Perawat</option>
                                            <option value="Guru/Dosen"
                                                <?= ($datanya['pekerjaan'] == 'Guru/Dosen') ? 'selected' : ''; ?>>Guru/Dosen
                                            </option>
                                            <option value="Pengacara"
                                                <?= ($datanya['pekerjaan'] == 'Pengacara') ? 'selected' : ''; ?>>Pengacara
                                            </option>
                                            <option value="Insinyur"
                                                <?= ($datanya['pekerjaan'] == 'Insinyur') ? 'selected' : ''; ?>>Insinyur
                                            </option>
                                            <option value="Arsitek"
                                                <?= ($datanya['pekerjaan'] == 'Arsitek') ? 'selected' : ''; ?>>Arsitek</option>
                                            <option value="Seniman"
                                                <?= ($datanya['pekerjaan'] == 'Seniman') ? 'selected' : ''; ?>>Seniman</option>
                                            <option value="Pengusaha"
                                                <?= ($datanya['pekerjaan'] == 'Pengusaha') ? 'selected' : ''; ?>>Pengusaha
                                            </option>
                                            <option value="Wartawan"
                                                <?= ($datanya['pekerjaan'] == 'Wartawan') ? 'selected' : ''; ?>>Wartawan
                                            </option>
                                            <option value="Atlet" <?= ($datanya['pekerjaan'] == 'Atlet') ? 'selected' : ''; ?>>
                                                Atlet</option>
                                            <option value="Sopir" <?= ($datanya['pekerjaan'] == 'Sopir') ? 'selected' : ''; ?>>
                                                Sopir</option>
                                            <option value="Montir"
                                                <?= ($datanya['pekerjaan'] == 'Montir') ? 'selected' : ''; ?>>Montir</option>
                                            <option value="Petugas Keamanan"
                                                <?= ($datanya['pekerjaan'] == 'Petugas Keamanan') ? 'selected' : ''; ?>>Petugas
                                                Keamanan</option>
                                            <option value="Karyawan Swasta"
                                                <?= ($datanya['pekerjaan'] == 'Karyawan Swasta') ? 'selected' : ''; ?>>Karyawan
                                                Swasta</option>
                                            <option value="Sales Marketing"
                                                <?= ($datanya['pekerjaan'] == 'Sales Marketing') ? 'selected' : ''; ?>>Sales
                                                Marketing</option>
                                            <option value="Tukang Bangunan"
                                                <?= ($datanya['pekerjaan'] == 'Tukang Bangunan') ? 'selected' : ''; ?>>Tukang
                                                Bangunan</option>
                                            <option value="Tukang Kayu"
                                                <?= ($datanya['pekerjaan'] == 'Tukang Kayu') ? 'selected' : ''; ?>>Tukang Kayu
                                            </option>
                                            <option value="Tukang Las"
                                                <?= ($datanya['pekerjaan'] == 'Tukang Las') ? 'selected' : ''; ?>>Tukang Las
                                            </option>
                                            <option value="Peternak"
                                                <?= ($datanya['pekerjaan'] == 'Peternak') ? 'selected' : ''; ?>>Peternak
                                            </option>
                                            <option value="Penjahit"
                                                <?= ($datanya['pekerjaan'] == 'Penjahit') ? 'selected' : ''; ?>>Penjahit
                                            </option>
                                            <option value="Karyawan Bank"
                                                <?= ($datanya['pekerjaan'] == 'Karyawan Bank') ? 'selected' : ''; ?>>Karyawan
                                                Bank</option>
                                            <option value="Pengusaha Kecil"
                                                <?= ($datanya['pekerjaan'] == 'Pengusaha Kecil') ? 'selected' : ''; ?>>Pengusaha
                                                Kecil</option>
                                            <option value="Buruh" <?= ($datanya['pekerjaan'] == 'Buruh') ? 'selected' : ''; ?>>
                                                Buruh</option>
                                            <option value="Pelaut"
                                                <?= ($datanya['pekerjaan'] == 'Pelaut') ? 'selected' : ''; ?>>Pelaut</option>
                                            <option value="Fotografer"
                                                <?= ($datanya['pekerjaan'] == 'Fotografer') ? 'selected' : ''; ?>>Fotografer
                                            </option>
                                            <option value="Penulis"
                                                <?= ($datanya['pekerjaan'] == 'Penulis') ? 'selected' : ''; ?>>Penulis</option>
                                            <option value="Penyiar"
                                                <?= ($datanya['pekerjaan'] == 'Penyiar') ? 'selected' : ''; ?>>Penyiar</option>
                                            <option value="Psikolog"
                                                <?= ($datanya['pekerjaan'] == 'Psikolog') ? 'selected' : ''; ?>>Psikolog
                                            </option>

                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Instansi / Perusahaan / Usaha</label>
                                        <input type="text" class="form-control"
                                            value="<?= htmlspecialchars($datanya['tempat_bekerja']); ?>" name="tempat_bekerja[]"
                                            required />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Golongan Darah</label>
                                        <select class="form-select" name="golongan_darah[]">
                                            <option value="">Pilih Golongan Darah...</option>
                                            <option value="A" <?= ($datanya['golongan_darah'] == 'A') ? 'selected' : ''; ?>>A
                                            </option>
                                            <option value="B" <?= ($datanya['golongan_darah'] == 'B') ? 'selected' : ''; ?>>B
                                            </option>
                                            <option value="AB" <?= ($datanya['golongan_darah'] == 'AB') ? 'selected' : ''; ?>>AB
                                            </option>
                                            <option value="O" <?= ($datanya['golongan_darah'] == 'O') ? 'selected' : ''; ?>>O
                                            </option>
                                            <option value="Tidak Tahu"
                                                <?= ($datanya['golongan_darah'] == 'Tidak Tahu') ? 'selected' : ''; ?>>Tidak
                                                Tahu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php
                            $no++;
                        endforeach;
                        ?>

                        <div id="anggotaKeluargaWrapper"></div>
                        <button type="button" class="btn btn-outline-primary mt-3 w-100" onclick="tambahAnggota()">+ Tambah
                            Anggota Keluarga</button>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-success w-100">Simpan Data</button>
                        </div>
                    </form>
                </div>
        </div>
    <?php else : ?>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="no_kk" class="form-label">Verifikasi Nomor KK</label>
                    <input type="text" class="form-control" id="no_kk" name="no_kk"
                        maxlength="16" pattern="\d{16}" required
                        placeholder="Masukkan 16 digit Nomor KK">
                    <div class="form-text">Pastikan nomor terdiri dari 16 digit angka. <br>Verifikasi ini untuk menjaga keamanan data masing2 individu</div>
                </div>

                <div class="d-grid">
                    <button type="submit" name="verifikasi" value="Verifikasi" class="btn btn-primary">
                        Verifikasi
                    </button>
                </div>
            </form>
        <?php endif; ?>
        </div>

        <!-- Template anggota keluarga -->
        <template id="templateAnggota">
            <div class="card border rounded p-3 mt-3 position-relative anggota-keluarga">
                <button type="button" class="btn btn-sm btn-danger remove-member-btn"
                    onclick="hapusAnggota(this)">X</button>
                <div class="anggota-header">Anggota Ke-<span class="nomor-anggota"></span></div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama[]" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">NIK</label>
                        <input type="text" class="form-control" name="nik[]" required />
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin[]" required>
                            <option value="">Pilih Jenis Kelamin...</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Agama</label>
                        <select class="form-select" name="agama[]" required>
                            <option value="">Pilih Agama...</option>
                            <option value="Islam">Islam</option>
                            <option value="Kristen Protestan">Kristen Protestan</option>
                            <option value="Katolik">Katolik</option>
                            <option value="Hindu">Hindu</option>
                            <option value="Buddha">Buddha</option>
                            <option value="Konghucu">Konghucu</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status Perkawinan</label>
                        <select class="form-select" name="status_perkawinan[]" required>
                            <option value="">Pilih Status Perkawinan...</option>
                            <option value="Belum Kawin">Belum Kawin</option>
                            <option value="Kawin">Kawin</option>
                            <option value="Cerai Hidup">Cerai Hidup</option>
                            <option value="Cerai Mati">Cerai Mati</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hubungan</label>
                        <select class="form-select" name="hubungan[]" required>
                            <option value="">Pilih...</option>
                            <option value="Kepala Keluarga">Kepala Keluarga</option>
                            <option value="Istri">Istri</option>
                            <option value="Anak">Anak</option>
                            <option value="Orang Tua">Orang Tua</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tgl_lahir[]" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pekerjaan</label>
                        <select class="form-select" name="pekerjaan[]" required>
                            <option value="">Pilih Pekerjaan...</option>
                            <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
                            <option value="ASN">ASN</option>
                            <option value="TNI/Polri">TNI/Polri</option>
                            <option value="Pegawai Swasta">Pegawai Swasta</option>
                            <option value="Wiraswasta">Wiraswasta</option>
                            <option value="Petani">Petani</option>
                            <option value="Nelayan">Nelayan</option>
                            <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                            <option value="Pensiunan">Pensiunan</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Golongan Darah</label>
                        <select class="form-select" name="golongan_darah[]">
                            <option value="">Pilih Golongan Darah...</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                            <option value="Tidak Tahu">Tidak Tahu</option>
                        </select>
                    </div>
                </div>
            </div>
        </template>

        <!-- jQuery (required by Select2) -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <!-- Bootstrap 5 JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // Init Select2 for nomor rumah
            $(document).ready(function() {
                $('.select2').select2({
                    placeholder: 'Pilih Nomor Rumah',
                    allowClear: true
                });

                $('#provinsi').select2({
                    placeholder: 'Pilih Provinsi',
                    allowClear: true
                });

                $('#kota').select2({
                    placeholder: 'Pilih Kota/Kabupaten',
                    allowClear: true
                });

                $('#kecamatan').select2({
                    placeholder: 'Pilih Kecamatan',
                    allowClear: true
                });

                $('#kelurahan').select2({
                    placeholder: 'Pilih Kelurahan',
                    allowClear: true
                });
            });

            // Tambah anggota keluarga
            function tambahAnggota() {
                const wrapper = document.getElementById('anggotaKeluargaWrapper');
                const template = document.getElementById('templateAnggota').content.cloneNode(true);
                const count = <?php echo (int)$jumlah_rows; ?> + wrapper.children.length + 1;
                template.querySelector('.nomor-anggota').textContent = count;
                wrapper.appendChild(template);
                updateNomorAnggota();
            }

            // Hapus anggota keluarga
            function hapusAnggota(btn) {
                const wrapper = document.getElementById('anggotaKeluargaWrapper');
                const cards = wrapper.querySelectorAll('.anggota-keluarga');

                if (cards.length <= 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tidak bisa dihapus',
                        text: 'Minimal harus ada satu anggota keluarga (Kepala Keluarga) !',
                    });
                    return;
                }

                btn.closest('.anggota-keluarga').remove();
                updateNomorAnggota();
            }

            // Update nomor anggota
            function updateNomorAnggota() {
                const cards = document.querySelectorAll('.anggota-keluarga');
                cards.forEach((card, index) => {
                    card.querySelector('.nomor-anggota').textContent = <?php echo $jumlah_rows; ?> + index + 1;
                });
            }

             // API wilayah Indonesia
            const selectedProvinsi = "<?= $data_kk['provinsi'] ?>".toUpperCase();
            const selectedKota = "<?= str_replace('KABUPATEN', 'KAB.', $data_kk['kota']) ?>".toUpperCase();
            const selectedKecamatan = "<?= $data_kk['kecamatan'] ?>".toUpperCase();
            const selectedKelurahan = "<?= $data_kk['kelurahan'] ?>".toUpperCase();

            const provinsi = $('#provinsi');
            const kota = $('#kota');
            const kecamatan = $('#kecamatan');
            const kelurahan = $('#kelurahan');

            // Load Provinsi
            fetch("https://ibnux.github.io/data-indonesia/provinsi.json")
                .then(res => res.json())
                .then(data => {
                    provinsi.empty().append('<option value="">Pilih Provinsi</option>');
                    data.forEach(p => {
                        const nama = p.nama.toUpperCase();
                        const selected = (nama === selectedProvinsi) ? 'selected' : '';
                        provinsi.append(`<option value="${nama}" data-id="${p.id}" ${selected}>${nama}</option>`);
                    });

                    // Cari ID Provinsi yang dipilih sebelumnya
                    const prov = data.find(p => p.nama.toUpperCase() === selectedProvinsi);
                    if (prov) {
                        loadKota(prov.id);
                    }
                });

            function loadKota(provId) {
                fetch(`https://ibnux.github.io/data-indonesia/kabupaten/${provId}.json`)
                    .then(res => res.json())
                    .then(data => {
                        kota.empty().append('<option value="">Pilih Kota/Kabupaten</option>');
                        data.forEach(k => {
                            const nama = k.nama.toUpperCase();
                            const selected = (nama === selectedKota) ? 'selected' : '';
                            kota.append(`<option value="${nama}" data-id="${k.id}" ${selected}>${nama}</option>`);
                        });

                        const kab = data.find(k => k.nama.toUpperCase() === selectedKota);
                        if (kab) {
                            loadKecamatan(kab.id);
                        }
                    });
            }

            function loadKecamatan(kabId) {
                fetch(`https://ibnux.github.io/data-indonesia/kecamatan/${kabId}.json`)
                    .then(res => res.json())
                    .then(data => {
                        kecamatan.empty().append('<option value="">Pilih Kecamatan</option>');
                        data.forEach(kec => {
                            const nama = kec.nama.toUpperCase();
                            const selected = (nama === selectedKecamatan) ? 'selected' : '';
                            kecamatan.append(`<option value="${nama}" data-id="${kec.id}" ${selected}>${nama}</option>`);
                        });

                        const kec = data.find(k => k.nama.toUpperCase() === selectedKecamatan);
                        if (kec) {
                            loadKelurahan(kec.id);
                        }
                    });
            }

            function loadKelurahan(kecId) {
                fetch(`https://ibnux.github.io/data-indonesia/kelurahan/${kecId}.json`)
                    .then(res => res.json())
                    .then(data => {
                        kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');
                        data.forEach(kel => {
                            const nama = kel.nama.toUpperCase();
                            const selected = (nama === selectedKelurahan) ? 'selected' : '';
                            kelurahan.append(`<option value="${nama}" data-id="${kel.id}" ${selected}>${nama}</option>`);
                        });

                        // Tambahkan manual kelurahan PEPE jika kecamatan ID 3515130
                        if (kecId == 3515130) {
                            const namaManual = "PEPE";
                            const selected = (namaManual === selectedKelurahan) ? 'selected' : '';
                            kelurahan.append(`<option value="${namaManual}" data-id="manual-pepelegi" ${selected}>${namaManual}</option>`);
                        }
                    });
            }

            // Events
            provinsi.on('change', function() {
                const id = provinsi.find(':selected').data('id');
                kota.empty().append('<option>Loading...</option>');
                kecamatan.empty().append('<option value="">Pilih Kecamatan</option>');
                kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');
                if (id) loadKota(id);
            });

            kota.on('change', function() {
                const id = kota.find(':selected').data('id');
                kecamatan.empty().append('<option>Loading...</option>');
                kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');
                if (id) loadKecamatan(id);
            });

            kecamatan.on('change', function() {
                const id = kecamatan.find(':selected').data('id');
                kelurahan.empty().append('<option>Loading...</option>');
                if (id) loadKelurahan(id);
            });
            
        </script>

        <script>
            $(document).ready(function() {
                $('#formKeluarga').on('submit', function(e) {
                    e.preventDefault();
                    var formData = new FormData(this);

                    $.ajax({
                        url: '<?php echo base_url('dashboard/update_pendataan_keluarga'); ?>',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        dataType: 'json',
                        beforeSend: function() {
                            Swal.fire({
                                title: 'Mengirim data...',
                                text: 'Mohon tunggu sebentar...',
                                icon: 'info',
                                showConfirmButton: false,
                                allowOutsideClick: false
                            });
                        },
                        success: function(response) {
                            Swal.close();
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Sukses!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            Swal.close();
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan: ' + error,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                });
            });
        </script>
        <script>
            // 🕒 Atur tanggal & waktu akhir di sini
            const countdownEndTime = new Date("2025-05-30T23:59:00").getTime();

            function updateCountdown() {
                const now = new Date().getTime();
                const distance = countdownEndTime - now;

                if (distance <= 0) {
                    document.getElementById("timer-display").innerHTML = "Waktu Habis!";
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                document.getElementById("timer-display").innerHTML =
                    `${days} hari ${hours} jam ${minutes} menit ${seconds} detik`;
            }

            updateCountdown(); // Inisialisasi
            setInterval(updateCountdown, 1000); // Update tiap detik
        </script>
</body>

</html>