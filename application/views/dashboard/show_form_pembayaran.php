<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bayar Iuran Lingkungan</title>
    <link href="<?php echo base_url(); ?>dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery (required by Select2) -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 CSS -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <style>
        body {
            background: #f8f9fa;
        }

        .card {
            border-radius: 16px;
        }

        .card-header {
            border-top-left-radius: 16px;
            border-top-right-radius: 16px;
        }

        .info-small {
            font-size: 0.85em;
            color: #6c757d;
        }

        .form-label {
            font-weight: 600;
        }

        .highlight {
            background-color: #e7f5ff;
            padding: 10px;
            border-left: 4px solid #0d6efd;
            margin-bottom: 1rem;
            border-radius: 6px;
        }

        .select2-container .select2-selection--single {
            height: 40px !important;
            /* sesuaikan dengan kebutuhan */
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 8px;
            /* agar teks tidak mentok kiri */
            line-height: normal !important;
            /* reset default line-height */
            flex: 1;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
        }

        .select2-container .select2-selection--multiple {
            min-height: 100px !important;
        }

        .select2-results__options {
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>

<body class="py-5">
    <div class="container">
        <div class="card shadow-lg">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center d-flex flex-column align-items-center justify-content-center p-4"
                    style="height: 160px; background: linear-gradient(135deg,rgb(209, 241, 212), #ffffff);">
                    <img src="<?php echo base_url('logo-tsi-removebg-preview.png'); ?>" alt="Logo TSI"
                        style="width: 150px; margin-bottom: 15px;">
                    <h4 class="fw-bold mb-1 text-dark">PEMBAYARAN IPL</h4>
                    <p class="mb-0 text-muted" style="font-size: 1.1rem;">
                        Perumahan Taman Sukodono Indah
                    </p>
                </div>
            </div>
            <div class="card-body">
                <div class="highlight d-none" id="infoPeriode"></div>
                <?php if ($this->session->flashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('success'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $this->session->flashdata('error'); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <form id="formBayar" method="post" action="<?php echo base_url('proses-pembayaran'); ?>"
                    enctype="multipart/form-data">
                    <div class="col-md-6">
                        <?php
                        // Ambil ID dari URL lalu decrypt
                        $id = $this->uri->segment(2);
                        $id_pembayaran = @$this->uri->segment(3);
                        $id_decrypt = decrypt_url($id);

                        // Ambil data user berdasarkan id rumah
                        $result_rumah = $this->db->where(['id_rumah' => $id_decrypt])->get("master_users")->result_array();

                        // Misalnya kita ambil data user_id yang terpilih sebelumnya
                        // Contoh: dari database atau form input sebelumnya
                        $selected_user_id = ''; // default kosong

                        // Misal ambil dari POST atau database
                        if (isset($id_decrypt)) {
                            $selected_user_id = $id_decrypt;
                        }
                        ?>
                        <label for="nomorRumah" class="form-label">Nomor Rumah</label>
                        <select class="form-select select2" id="nomorRumah" name="user_id" style="width: 100% !important; height: 100px;" required>
                            <option value="">Pilih Nomor Rumah</option>
                            <?php foreach ($result_rumah as $value) : ?>
                                <option value="<?php echo $value['id']; ?>" <?= ($value['id'] == $selected_user_id) ? 'selected' : '' ?>>
                                    <?php echo $value['id']; ?> - <?php echo $value['nama'] . " - " . $value['rumah']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php
                    $id_pembayaran_decrypt = decrypt_url($id_pembayaran);
                    if (!empty($id_pembayaran_decrypt)) {
                        $data_update = $this->db->get_where("master_pembayaran", array('id' => $id_pembayaran_decrypt))->row_array();
                        echo '<input type="text" value="' . $id_pembayaran_decrypt . '" name="id_pembayaran">';
                    }
                    ?>
                    <div class="mb-3">
                        <br>
                        <label class="form-label">Jenis Pembayaran</label>
                        <select class="form-select" name="metode" id="metode" onchange="tampilkanOpsi()" required>
                            <option value="">Pilih metode pembayaran...</option>
                            <option value="1_bulan" <?php echo (@$data_update['metode'] == '1_bulan') ? "selected" : ""; ?>>Bayar 1 Bulan</option>
                            <!-- <option value="2_bulan">Rapel 2 Bulan</option>
                            <option value="3_bulan">Rapel 3 Bulan</option>
                            <option value="4_bulan">Rapel 4 Bulan</option>
                            <option value="5_bulan">Rapel 5 Bulan</option>
                            <option value="6_bulan">Rapel 6 Bulan</option>
                            <option value="7_bulan">Rapel 7 Bulan</option>
                            <option value="8_bulan">Rapel 8 Bulan</option>
                            <option value="9_bulan">Rapel 9 Bulan</option>
                            <option value="10_bulan">Rapel 10 Bulan</option>
                            <option value="7_tahun">Bayar 1 Tahun Sekaligus</option>
                            <option value="cicilan">Cicilan Beberapa Bulan</option> -->
                        </select>
                    </div>
                    <?php if (!empty($data_update['bulan_mulai'])) :  ?>
                        <div class="mb-3">
                            <label for="bulan_mulai" class="form-label">Bulan</label>
                            <input type="month" value="<?= date('Y-m', strtotime($data_update['bulan_mulai'])) ?>" id="bulan_mulai" name="bulan_mulai" class="form-control">
                        </div>
                    <?php else : ?>
                        <div id="opsiBulan" class="mb-3 d-none">
                            <label for="bulan_mulai" class="form-label">Bulan</label>
                            <input type="month" name="bulan_mulai" id="bulan_mulai" class="form-control">
                        </div>
                    <?php endif; ?>
                    <div class="mb-3">
                        <label for="tanggal_bayar" class="form-label">Tanggal Pembayaran</label>
                        <input type="text" class="form-control" value="<?php echo $data_update['tanggal_bayar']; ?>" id="tanggal_bayar" name="tanggal_bayar" placeholder="Pilih tanggal pembayaran" required>
                    </div>
                    <div id="opsiCicilan" class="row d-none">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Berapa Bulan Ingin Dicicil?</label>
                            <input type="number" name="lama_cicilan" id="lama_cicilan" class="form-control" min="2"
                                max="12">
                            <div class="info-small">Contoh: 5 bulan</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Iuran yang Dicicil</label>
                            <input type="number" name="total_cicilan" id="total_cicilan" class="form-control"
                                placeholder="Contoh: 1500000">
                            <div class="info-small">Rp125.000 x 12 bulan = Rp1.500.000</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="total_bayar" class="form-label">Jumlah yang Dibayar Hari Ini</label>
                        <input type="number" class="form-control" name="jumlah_bayar" value="<?php echo (!empty($data_update['jumlah_bayar'])) ? $data_update['jumlah_bayar'] : "125000"; ?>" id="jumlah_bayar"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="pembayaran_via" class="form-label">Metode Pembayaran</label>
                        <select class="form-select" name="pembayaran_via" id="pembayaran_via" onchange="cekMetodeBayar()" required>
                            <option value="">Pilih cara membayar...</option>
                            <option value="koordinator" <?= ($data_update['pembayaran_via'] == 'koordinator') ? 'selected' : '' ?>>Ke Koordinator Blok</option>
                            <option value="transfer" <?= ($data_update['pembayaran_via'] == 'transfer') ? 'selected' : '' ?>>Transfer ke Bendahara</option>
                        </select>
                    </div>

                    <?php if (!empty($data_update['bukti'])): ?>
                        <div class="mb-3 d-none" id="buktiTransfer">

                            <label for="bukti" class="form-label">Upload Bukti Pembayaran</label>
                            <input type="file" class="form-control" name="bukti" id="bukti"
                                accept="image/*,application/pdf">
                            <div class="info-small">Format: JPG, PNG, atau PDF</div>
                            <label class="form-label">Bukti Sebelumnya:</label><br>
                            <?php
                            $ext = pathinfo($data_update['bukti'], PATHINFO_EXTENSION);
                            $file_url = base_url('uploads/bukti/' . $data_update['bukti']); // sesuaikan path
                            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): ?>
                                <img src="<?= $file_url ?>" alt="Bukti" class="img-fluid rounded" style="max-height: 200px;">
                            <?php elseif (strtolower($ext) === 'pdf'): ?>
                                <a href="<?= $file_url ?>" target="_blank" class="btn btn-outline-secondary btn-sm">Lihat Bukti PDF</a>
                            <?php endif; ?>

                            <!-- Simpan nama file lama dalam input hidden -->
                            <input type="hidden" name="file_kk_existing" value="<?= $data_update['bukti'] ?>">

                        </div>
                    <?php else : ?>
                        <div class="mb-3 d-none" id="buktiTransfer">
                            <label for="bukti" class="form-label">Upload Bukti Pembayaran</label>
                            <input type="file" class="form-control" name="bukti" id="bukti"
                                accept="image/*,application/pdf">
                            <div class="info-small">Format: JPG, PNG, atau PDF</div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2"
                            placeholder="Contoh: Pembayaran bulan Januari hingga Maret"><?= $data_update['keterangan'] ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Bayar Sekarang</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const metodeSelect = document.getElementById("pembayaran_via");
            const buktiTransfer = document.getElementById("buktiTransfer");

            if (metodeSelect && buktiTransfer) {
                metodeSelect.addEventListener("change", cekMetodeBayar);
                cekMetodeBayar(); // langsung cek saat load
            }

            function cekMetodeBayar() {
                if (metodeSelect.value === "transfer") {
                    buktiTransfer.classList.remove("d-none");
                } else {
                    buktiTransfer.classList.add("d-none");
                }
            }
        });

        function tampilkanOpsi() {
            const metode = document.getElementById("metode").value;
            const opsiBulan = document.getElementById("opsiBulan");
            const opsiCicilan = document.getElementById("opsiCicilan");
            const infoPeriode = document.getElementById("infoPeriode");

            opsiBulan.classList.remove("d-none");
            infoPeriode.classList.remove("d-none");
            opsiCicilan.classList.add("d-none");

            let infoText = "";

            if (metode === "cicilan") {
                opsiCicilan.classList.remove("d-none");
                infoText = "Anda memilih cicilan. Silakan tentukan mulai bulan dan total nominal yang ingin dicicil.";
            } else if (metode.includes("_bulan")) {
                let bulanCount = metode.split("_")[0];
                infoText = `Akan membayar ${bulanCount} bulan iuran berturut-turut dari bulan yang dipilih.`;
            } else if (metode === "7_tahun") {
                infoText = "Akan membayar 12 bulan iuran sekaligus mulai dari bulan yang dipilih.";
            } else {
                opsiBulan.classList.add("d-none");
                infoPeriode.classList.add("d-none");
            }

            infoPeriode.innerHTML = infoText;
        }

        // function cekMetodeBayar() {
        //     const metode = document.getElementById("pembayaran_via").value;
        //     const buktiField = document.getElementById("buktiTransfer");

        //     if (metode === "transfer") {
        //         buktiField.classList.remove("d-none");
        //     } else {
        //         buktiField.classList.add("d-none");
        //     }
        // }
        $(document).ready(function() {
            $('.select2').select2({
                width: '100%',
                placeholder: 'Pilih Nomor Rumah',
                allowClear: true
            });
        });
    </script>


    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    <script>
        flatpickr("#tanggal_bayar", {
            // enableTime: true,
            dateFormat: "Y-m-d",
            // time_24hr: true,
            locale: "id"
        });

        document.addEventListener('DOMContentLoaded', function() {
            const bulanInput = document.getElementById('bulan_mulai');
            if (bulanInput) {
                bulanInput.addEventListener('change', function() {
                    const tanggal = this.value; // format: YYYY-MM
                    const [tahun, bulan] = tanggal.split('-');

                    const namaBulan = [
                        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];

                    fetch('http://localhost/perum_tsi/dashboard/cek_pembayaran', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id_rumah=1&tanggal=' + encodeURIComponent(tanggal)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.sudah_dibayar) {
                                Swal.fire({
                                    title: 'Sudah Dibayar',
                                    text: `Pembayaran untuk bulan ${namaBulan[parseInt(bulan)]} ${tahun} sudah dilakukan.`,
                                    icon: 'info',
                                    showCancelButton: true,
                                    confirmButtonText: 'Lihat / Perbaiki',
                                    cancelButtonText: 'Tutup'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = 'http://localhost/perum_tsi/pembayaran/YkHE_seYhh-jJPiiMgZr5_g/' + data.id_pembayaran;
                                    }
                                });
                            }
                        });
                });
            } else {
                console.warn('Element #bulan_mulai tidak ditemukan');
            }
        });
    </script>
</body>

</html>