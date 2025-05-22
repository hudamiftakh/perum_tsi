<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Form Pendataan Warga Perumahan TSI</title>
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>dist/logo_2.png" />
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
            min-height: 40px !important;
        }

        .select2-results__options {
            max-height: 200px;
            overflow-y: auto;
        }

        .timer-display {
            font-size: 1.25rem;
            /* Lebih besar dari small */
            font-weight: 600;
            color: #0d6efd;
            /* Bootstrap primary */
            background-color: #e9f2ff;
            /* Soft blue background */
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            display: inline-block;
            text-align: center;
            min-width: 120px;
        }

        .floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #5C913B;
            /* Hijau natural sesuai tema */
            color: white;
            font-size: 14px;
            padding: 10px 14px;
            opacity: 0.95;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.25);
            z-index: 1000;
            transition: background-color 0.3s ease;
        }

        .floating-button:hover {
            background-color: #4A7C2C;
            /* Warna hover lebih gelap */
        }

        form {
            padding-bottom: 35px;
            /* atau cukup besar agar tombol simpan tidak tertutup */
        }

        .status-belum {
            color: #dc3545;
            font-weight: 600;
        }

        .status-sudah {
            color: #28a745;
            font-weight: 600;
        }

        .card-custom {
            border-radius: 1rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div id="countdown-timer"
            class="position-fixed top-0 start-0 w-100 bg-white shadow-sm d-flex align-items-center justify-content-center gap-2 px-3 py-2"
            style="z-index: 1050; border-bottom-left-radius: 1rem; border-bottom-right-radius: 1rem;">
            <div class="text-center">
                <div class="fw-bold text-dark small">Berakhir pada </div>
                <div id="timer-display" class="fw-semibold text-danger small">Loading...</div>
            </div>
        </div>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden mt-5">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="card-header text-center d-flex flex-column align-items-center justify-content-center p-4"
                    style="height: 160px; background: linear-gradient(135deg,rgb(225, 248, 227), #ffffff);">
                    <img src="<?php echo base_url('logo-tsi-removebg-preview.png'); ?>" alt="Logo TSI"
                        style="width: 150px; margin-bottom: 15px;">
                    <h4 class="fw-bold mb-1 text-dark">PENDATAAN WARGA</h4>
                    <p class="mb-0 text-muted" style="font-size: 1.1rem;">
                        Perumahan Taman Sukodono Indah
                    </p>
                </div>
            </div>
            <div class="card-body" style="padding-top: 1rem;">

                <!-- Container pencarian + daftar rumah -->
                <div style="height: 65vh; display: flex; flex-direction: column;">
                    <!-- Input pencarian sticky -->
                    <div style="position: sticky; top: 0; z-index: 10; background: white; padding-bottom: 1rem;">
                        <input type="text" class="form-control" id="searchInput" style="height: 50px; border-radius: 10px;" placeholder="Cari berdasarkan blok atau nomor rumah...">
                    </div>

                    <!-- Daftar rumah scrollable -->
                    <div id="houseList" style="overflow-y: auto; flex-grow: 1; margin-top: 1rem;">
                        <?php
                        $result = $this->db->query("
                    SELECT 
                        mr.*,
                        CASE 
                            WHEN mk.nomor_rumah IS NOT NULL THEN 'sudah'
                            ELSE 'belum'
                        END AS status_pengisian,
                        ma.nama as koordinator,
                        mk.id as id_keluarga
                    FROM master_rumah mr
                    LEFT JOIN master_keluarga mk 
                        ON CONCAT('|', mk.nomor_rumah, '|') LIKE CONCAT('%|', mr.alamat, '|%')
                    LEFT JOIN master_koordinator_blok as ma ON mr.id_koordinator = ma.id;
                ")->result_array();
                        foreach ($result as $key => $data) :
                        ?>
                            <div class="card card-custom mb-3">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                                            <?php echo $data['alamat'] ?>
                                        </h6>
                                        <h6 class="mb-1">
                                            <i class="bi bi-person-fill text-primary me-2"></i>
                                            <?php echo $data['nama'] ?>
                                        </h6>

                                        <?php if ($data['status_pengisian'] == 'sudah') : ?>
                                            <small>Status: <span class="status-sudah">Sudah mengisi</span></small>
                                        <?php else : ?>
                                            <small>Status: <span class="status-belum">Belum mengisi</span></small>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($data['status_pengisian'] == 'sudah') : ?>
                                        <a href="<?php echo base_url('edit-pendataan-keluarga/' . encrypt_url($data['id_keluarga'])); ?>"
                                            class="btn btn-success btn-sm rounded-pill shadow-sm"
                                            style="transition: background-color 0.3s ease;">
                                            Revisi Isian
                                        </a>
                                    <?php else : ?>
                                        <a href="<?php echo base_url('pendataan-keluarga'); ?>"
                                            class="btn btn-primary btn-sm rounded-pill shadow-sm"
                                            style="transition: background-color 0.3s ease;">
                                            Isi Sekarang
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <script>
        // Search filter sederhana
        const searchInput = document.getElementById('searchInput');
        const houseList = document.getElementById('houseList');

        searchInput.addEventListener('input', () => {
            const filter = searchInput.value.toLowerCase();
            const cards = houseList.getElementsByClassName('card');

            Array.from(cards).forEach(card => {
                const text = card.innerText.toLowerCase();
                card.style.display = text.includes(filter) ? '' : 'none';
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>