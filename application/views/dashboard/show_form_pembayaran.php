<?php
// Note: This is a corrected PHP code snippet for the user's file.
// The code is written assuming that the `decrypt_url` function is defined and available elsewhere in the application.

// PHP Logic
$Auth = $this->session->userdata['username'];
$id = $this->uri->segment(2);
$id_pembayaran = $this->uri->segment(3);

$id_decrypt = !empty($id) ? decrypt_url($id) : null;
$result_rumah = [];

$level = $Auth['role'];

if ($level == 'koordinator') {
    $id_koordinator = $Auth['id'];
    $result_rumah = $this->db
        ->where('id_koordinator', $id_koordinator)
        ->get('master_users')
        ->result_array();
} else {
    if (empty($id)) {
        if (!empty($Auth['username'])) {
            $result_rumah = $this->db->get("master_users")->result_array();
        }
    } else {
        if (!empty($id_decrypt)) {
            $result_rumah = $this->db
                ->where('id_rumah', $id_decrypt)
                ->get("master_users")
                ->result_array();
        } else {
            log_message('error', 'Failed to decrypt house ID from URI.');
        }
    }
}

$selected_user_id = '';
if (isset($id_decrypt)) {
    $selected_user_id = $id_decrypt;
}

$data_update = [];
$id_pembayaran_decrypt = !empty($id_pembayaran) ? decrypt_url($id_pembayaran) : null;
if (!empty($id_pembayaran_decrypt)) {
    $data_update = $this->db->get_where("master_pembayaran", array('id' => $id_pembayaran_decrypt))->row_array();
}

// Prepare data for JavaScript
$bulan_rapel_checked = [];
if (isset($data_update['bulan_rapel']) && !empty($data_update['bulan_rapel'])) {
    $bulan_rapel_checked = explode(',', $data_update['bulan_rapel']);
}

$bulan_rapel_selected = '';
if (isset($data_update['untuk_bulan']) && !empty($data_update['untuk_bulan'])) {
    $bulan_rapel_selected = date("Y-m", strtotime($data_update['untuk_bulan']));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bayar Iuran Lingkungan</title>
    <link href="<?php echo base_url(); ?>dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/style.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/confirmDate/confirmDate.js"></script>

    <style>
        /* CSS styles remain the same */
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
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 8px;
            line-height: normal !important;
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

        .flatpickr-confirm {
            background-color: #c2241fff !important;
            color: #fff !important;
            border: none !important;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s ease-in-out;
        }

        .flatpickr-confirm:hover {
            background-color: #0b5ed7 !important;
        }

        .flatpickr-confirm:disabled {
            background-color: #ccc !important;
            cursor: not-allowed;
        }

        .flatpickr-cancel {
            background-color: #dc3545 !important;
            color: #fff !important;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 14px;
            font-weight: 500;
            margin-left: 8px;
            cursor: pointer;
        }

        .bulan-rapel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.6rem;
        }

        .bulan-rapel-item {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 10px 12px;
            background: #f9f9f9;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bulan-rapel-item:hover {
            background: #eaf4ff;
            border-color: #0d6efd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .bulan-rapel-item input[type="checkbox"] {
            width: 1.1em;
            height: 1.1em;
            accent-color: #0d6efd;
            cursor: pointer;
        }

        .bulan-rapel-label {
            font-size: 0.95rem;
            font-weight: 500;
            color: #333;
            margin: 0;
        }

        @media (max-width: 600px) {
            .bulan-rapel-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .bulan-rapel-label {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 400px) {
            .bulan-rapel-grid {
                grid-template-columns: 1fr;
            }
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
                    <?php if (!empty($id_pembayaran_decrypt)): ?>
                    <input type="hidden" value="<?= $id_pembayaran_decrypt ?>" name="id_pembayaran">
                    <?php endif; ?>
                    <div class="mb-3">
                        <br>
                        <label class="form-label">Jenis Pembayaran</label>
                        <select class="form-select" name="metode" style="width: 100%; height: 40px; font-size: 17px;"
                            id="metode" required onchange="handleMetodeChange()">
                            <option value="">Pilih metode pembayaran...</option>
                            <option value="1_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '1_bulan') ? "selected" : ""; ?>>Bayar 1 Bulan</option>
                            <option value="2_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '2_bulan') ? "selected" : ""; ?>>Rapel 2 Bulan</option>
                            <option value="3_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '3_bulan') ? "selected" : ""; ?>>Rapel 3 Bulan</option>
                            <option value="4_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '4_bulan') ? "selected" : ""; ?>>Rapel 4 Bulan</option>
                            <option value="5_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '5_bulan') ? "selected" : ""; ?>>Rapel 5 Bulan</option>
                            <option value="6_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '6_bulan') ? "selected" : ""; ?>>Rapel 6 Bulan</option>
                            <option value="7_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '7_bulan') ? "selected" : ""; ?>>Rapel 7 Bulan</option>
                            <option value="8_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '8_bulan') ? "selected" : ""; ?>>Rapel 8 Bulan</option>
                            <option value="9_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '9_bulan') ? "selected" : ""; ?>>Rapel 9 Bulan</option>
                            <option value="10_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '10_bulan') ? "selected" : ""; ?>>Rapel 10 Bulan</option>
                            <option value="11_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '11_bulan') ? "selected" : ""; ?>>Rapel 11 Bulan</option>
                            <option value="12_bulan" <?php echo (isset($data_update['metode']) && $data_update['metode'] == '12_bulan') ? "selected" : ""; ?>>Rapel 12 Bulan</option>
                        </select>
                        <div id="bulanRapelContainer" class="mt-2" style="display:none;">
                            <div id="bulanRapelCheckboxes" class="bulan-rapel-grid"></div>
                            <div id="bulanRapelSelect" style="display:none;">
                                <label for="bulan_single" class="form-label">Untuk Bulan</label>
                                <select id="bulan_single" name="untuk_bulan" class="form-select"></select>
                            </div>
                        </div>

                        <script>
                            // PHP data passed to JavaScript
                            const bulanRapelChecked = <?php echo json_encode($bulan_rapel_checked); ?>;
                            const bulanRapelSelected = "<?php echo $bulan_rapel_selected; ?>";

                            function getBulanPilihan2025() {
                                const bulan = [
                                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                ];
                                let options = [];
                                let tahun = new Date().getFullYear(); // Use current year
                                for (let i = 0; i < 12; i++) {
                                    let val = tahun + '-' + String(i + 1).padStart(2, '0');
                                    options.push({
                                        value: val,
                                        label: bulan[i] + ' ' + tahun
                                    });
                                }
                                return options;
                            }

                            function handleMetodeChange() {
                                const metode = document.getElementById('metode').value;
                                const bulanRapelContainer = document.getElementById('bulanRapelContainer');
                                const bulanRapelCheckboxes = document.getElementById('bulanRapelCheckboxes');
                                const bulanRapelSelect = document.getElementById('bulanRapelSelect');
                                const bulanSingle = document.getElementById('bulan_single');
                                let rapelMatch = metode.match(/^(\d+)_bulan$/);

                                if (rapelMatch) {
                                    let jumlahRapel = parseInt(rapelMatch[1]);
                                    bulanRapelContainer.style.display = '';

                                    let options = getBulanPilihan2025();

                                    bulanRapelCheckboxes.innerHTML = '';
                                    bulanSingle.innerHTML = '<option value="">-- Pilih Bulan --</option>';

                                    if (jumlahRapel > 1) {
                                        bulanRapelCheckboxes.style.display = '';
                                        bulanRapelSelect.style.display = 'none';

                                        options.forEach((opt, idx) => {
                                            let id = 'bulan_rapel_' + idx;
                                            let wrapper = document.createElement('label');
                                            wrapper.className = 'bulan-rapel-item';

                                            let checkbox = document.createElement('input');
                                            checkbox.type = 'checkbox';
                                            checkbox.name = 'bulan_rapel[]';
                                            checkbox.value = opt.value;
                                            checkbox.id = id;
                                            checkbox.checked = bulanRapelChecked.includes(opt.value);

                                            let span = document.createElement('span');
                                            span.className = 'bulan-rapel-label';
                                            span.textContent = opt.label;

                                            wrapper.appendChild(checkbox);
                                            wrapper.appendChild(span);
                                            bulanRapelCheckboxes.appendChild(wrapper);
                                        });

                                        bulanRapelCheckboxes.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                                            cb.addEventListener('change', function() {
                                                let checked = bulanRapelCheckboxes.querySelectorAll('input[type="checkbox"]:checked');
                                                if (checked.length > jumlahRapel) {
                                                    this.checked = false;
                                                }
                                            });
                                        });

                                        bulanSingle.required = false;
                                        bulanSingle.disabled = true;

                                    } else {
                                        bulanRapelCheckboxes.style.display = 'none';
                                        bulanRapelSelect.style.display = '';

                                        options.forEach(opt => {
                                            let option = document.createElement('option');
                                            option.value = opt.value;
                                            option.textContent = opt.label;
                                            if (bulanRapelSelected === opt.value) {
                                                option.selected = true;
                                            }
                                            bulanSingle.appendChild(option);
                                        });

                                        bulanSingle.required = true;
                                        bulanSingle.disabled = false;
                                    }

                                } else {
                                    bulanRapelContainer.style.display = 'none';
                                    bulanRapelCheckboxes.innerHTML = '';
                                    bulanSingle.innerHTML = '';
                                    bulanSingle.required = false;
                                    bulanSingle.disabled = true;
                                }
                                updateJumlahBayar();
                            }

                            document.addEventListener('DOMContentLoaded', handleMetodeChange);
                        </script>
                    </div>

                    <?php
                    // Simplified bulan_mulai input logic
                    $bulan_mulai_value = isset($data_update['bulan_mulai']) ? date('Y-m', strtotime($data_update['bulan_mulai'])) : '';
                    ?>
                    <div id="opsiBulan" class="mb-3">
                        <label for="bulan_mulai" class="form-label">Bulan Bayar</label>
                        <input type="text" placeholder="Bulan Pembayaran" value="<?= $bulan_mulai_value ?>" id="bulan_mulai" name="bulan_mulai" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_bayar" class="form-label">Tanggal Pembayaran</label>
                        <input type="text" class="form-control" value="<?php echo isset($data_update['tanggal_bayar']) ? $data_update['tanggal_bayar'] : ''; ?>" id="tanggal_bayar" name="tanggal_bayar" placeholder="Pilih tanggal pembayaran" required>
                    </div>

                    <div id="opsiCicilan" class="row d-none">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Berapa Bulan Ingin Dicicil?</label>
                            <input type="number" name="lama_cicilan" id="lama_cicilan" class="form-control" min="2" max="12">
                            <div class="info-small">Contoh: 5 bulan</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Iuran yang Dicicil</label>
                            <input type="text" name="total_cicilan" id="total_cicilan" class="form-control" placeholder="Contoh: 1500000">
                            <div class="info-small">Rp125.000 x 12 bulan = Rp1.500.000</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_bayar" class="form-label">Jumlah yang Dibayar Hari Ini</label>
                        <?php
                        $jumlah_bayar_value = isset($data_update['jumlah_bayar']) ? number_format($data_update['jumlah_bayar'], 0, ',', '.') : '125.000';
                        ?>
                        <input type="text" class="form-control" name="jumlah_bayar" value="<?= $jumlah_bayar_value; ?>" id="jumlah_bayar" required autocomplete="off">
                    </div>

                    <div class="mb-3">
                        <label for="pembayaran_via" class="form-label">Metode Pembayaran</label>
                        <select class="form-select" name="pembayaran_via" id="pembayaran_via" onchange="cekMetodeBayar()" required>
                            <option value="">Pilih cara membayar...</option>
                            <option value="koordinator" <?= (isset($data_update['pembayaran_via']) && $data_update['pembayaran_via'] == 'koordinator') ? 'selected' : '' ?>>Ke Koordinator Blok</option>
                            <option value="transfer" <?= (isset($data_update['pembayaran_via']) && $data_update['pembayaran_via'] == 'transfer') ? 'selected' : '' ?>>Transfer ke Bendahara (BCA 8221586107 / Dhani Kispananto) </option>
                            <option value="transfer_2" <?= (isset($data_update['pembayaran_via']) && $data_update['pembayaran_via'] == 'transfer_2') ? 'selected' : '' ?>>Transfer ke Aditya</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="buktiTransfer">
                        <label for="bukti" class="form-label">Upload Bukti Pembayaran</label>
                        <?php if (empty($id_pembayaran_decrypt)): ?>
                            <input type="file" class="form-control" name="bukti" id="bukti" accept="image/*,application/pdf" required>
                        <?php else: ?>
                            <input type="file" class="form-control" name="bukti" id="bukti" accept="image/*,application/pdf">
                        <?php endif; ?>
                        <div class="info-small">Format: JPG, PNG, atau PDF. Maksimal 5 MB.</div>
                        <?php if (isset($data_update['bukti']) && !empty($data_update['bukti'])): ?>
                            <label class="form-label mt-2">Bukti Sebelumnya:</label><br>
                            <?php
                            $ext = pathinfo($data_update['bukti'], PATHINFO_EXTENSION);
                            $file_url = base_url('uploads/bukti/' . $data_update['bukti']);
                            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): ?>
                                <img src="<?= $file_url ?>" alt="Bukti" class="img-fluid rounded" style="max-height: 200px;">
                            <?php elseif (strtolower($ext) === 'pdf'): ?>
                                <a href="<?= $file_url ?>" target="_blank" class="btn btn-outline-secondary btn-sm">Lihat Bukti PDF</a>
                            <?php endif; ?>
                            <input type="hidden" name="file_kk_existing" value="<?= $data_update['bukti'] ?>">
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan (opsional)</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Pembayaran bulan Januari hingga Maret"><?= isset($data_update['keterangan']) ? $data_update['keterangan'] : ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-warning w-100 mb-2" id="btnPreview">Preview</button>
                        <?php if (!empty($id)) :  ?>
                        <button type="submit" class="btn btn-primary w-100">Revisi Isian</button>
                        <?php else : ?>
                        <button type="submit" class="btn btn-primary w-100">Kirim</button>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="modal fade" id="modalPreview" tabindex="-1" aria-labelledby="modalPreviewLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalPreviewLabel">Preview Data Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body" id="previewContent">
                                </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select2 initialization
            $('.select2').select2({
                width: '100%',
                placeholder: 'Pilih Nomor Rumah',
                allowClear: true
            });

            // Flatpickr initialization
            flatpickr("#tanggal_bayar", {
                enableTime: true,
                time_24hr: true,
                dateFormat: "Y-m-d H:i",
                disableMobile: true,
                locale: "id",
                plugins: [
                    new confirmDatePlugin({
                        confirmText: "OK",
                        showAlways: false,
                        theme: "light"
                    })
                ]
            });

            const bulanMulai = document.querySelector("#bulan_mulai");
            if (bulanMulai) {
                flatpickr(bulanMulai, {
                    dateFormat: "Y-m",
                    disableMobile: true,
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: true,
                            dateFormat: "Y-m",
                            altFormat: "F Y"
                        })
                    ],
                    onChange: function(selectedDates, dateStr, instance) {
                        instance.close();
                    }
                });
            } else {
                console.warn('Element #bulan_mulai not found.');
            }

            // Other event listeners
            const metodeSelect = document.getElementById("pembayaran_via");
            const buktiTransfer = document.getElementById("buktiTransfer");
            if (metodeSelect && buktiTransfer) {
                metodeSelect.addEventListener("change", cekMetodeBayar);
                cekMetodeBayar(); // Initial check
            }

            function cekMetodeBayar() {
                if (metodeSelect.value === "transfer") {
                    buktiTransfer.classList.remove("d-none");
                } else if (metodeSelect.value === "transfer_2") {
                    buktiTransfer.classList.remove("d-none");
                }else {
                    buktiTransfer.classList.add("d-none");
                }
            }

            const jumlahBayar = document.getElementById('jumlah_bayar');
            const metode = document.getElementById('metode');
            const bulanRapelCheckboxes = document.getElementById('bulanRapelCheckboxes');
            const hargaPerBulan = 125000;

            function updateJumlahBayar() {
                let metodeVal = metode.value;
                let rapelMatch = metodeVal.match(/^(\d+)_bulan$/);
                if (rapelMatch) {
                    let bulanCount = parseInt(rapelMatch[1]);
                    if (bulanCount > 1 && bulanRapelCheckboxes) {
                        let checked = bulanRapelCheckboxes.querySelectorAll('input[type="checkbox"]:checked').length;
                        if (checked > 0) {
                            jumlahBayar.value = (checked * hargaPerBulan).toLocaleString('id-ID');
                        } else {
                            jumlahBayar.value = '';
                        }
                    } else if (bulanCount === 1) {
                        jumlahBayar.value = hargaPerBulan.toLocaleString('id-ID');
                    }
                } else {
                    jumlahBayar.value = '';
                }
            }

            if (bulanRapelCheckboxes) {
                bulanRapelCheckboxes.addEventListener('change', updateJumlahBayar);
            }
            if (metode) {
                metode.addEventListener('change', function() {
                    setTimeout(updateJumlahBayar, 150);
                });
            }

            setTimeout(updateJumlahBayar, 350);

            if (jumlahBayar) {
                jumlahBayar.addEventListener('input', function(e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value) {
                        this.value = parseInt(value, 10).toLocaleString('id-ID');
                    } else {
                        this.value = '';
                    }
                });
                jumlahBayar.form.addEventListener('submit', function() {
                    jumlahBayar.value = jumlahBayar.value.replace(/\./g, '').replace(/,/g, '');
                });
            }

            const btnPreview = document.getElementById('btnPreview');
            const form = document.getElementById('formBayar');
            const previewContent = document.getElementById('previewContent');
            const modalPreview = new bootstrap.Modal(document.getElementById('modalPreview'));

            btnPreview.addEventListener('click', function(e) {
                e.preventDefault();

                const nomorRumah = form.querySelector('[name="user_id"]');
                const metode = form.querySelector('[name="metode"]');
                const bulanMulai = form.querySelector('[name="bulan_mulai"]');
                const tanggalBayar = form.querySelector('[name="tanggal_bayar"]');
                const lamaCicilan = form.querySelector('[name="lama_cicilan"]');
                const totalCicilan = form.querySelector('[name="total_cicilan"]');
                const jumlahBayar = form.querySelector('[name="jumlah_bayar"]');
                const pembayaranVia = form.querySelector('[name="pembayaran_via"]');
                const keterangan = form.querySelector('[name="keterangan"]');
                const bukti = form.querySelector('[name="bukti"]');
                const bulanSingleSelect = document.getElementById('bulan_single');
                const bulanRapelCheckboxes = document.getElementById('bulanRapelCheckboxes');

                let bulanDetail = '';
                if (metode.value === '1_bulan' && bulanSingleSelect) {
                    bulanDetail = bulanSingleSelect.options[bulanSingleSelect.selectedIndex]?.text || '';
                } else if (metode.value.includes('_bulan') && metode.value !== '1_bulan') {
                    const checkedMonths = Array.from(bulanRapelCheckboxes.querySelectorAll('input[type="checkbox"]:checked'))
                        .map(cb => cb.closest('.bulan-rapel-item').querySelector('.bulan-rapel-label').textContent)
                        .join(', ');
                    bulanDetail = checkedMonths;
                }

                let nomorRumahText = nomorRumah.options[nomorRumah.selectedIndex]?.text || '';
                let metodeText = metode.options[metode.selectedIndex]?.text || '';
                let pembayaranViaText = pembayaranVia.options[pembayaranVia.selectedIndex]?.text || '';

                let previewHtml = `
                    <table class="table table-bordered">
                        <tr><th>Nomor Rumah</th><td>${nomorRumahText}</td></tr>
                        <tr><th>Jenis Pembayaran</th><td>${metodeText}</td></tr>
                `;

                if (bulanDetail) {
                    previewHtml += `<tr><th>Untuk Bulan</th><td>${bulanDetail}</td></tr>`;
                }
                previewHtml += `
                    <tr><th>Tanggal Pembayaran</th><td>${tanggalBayar.value}</td></tr>
                `;

                if (lamaCicilan && lamaCicilan.value) {
                    previewHtml += `<tr><th>Lama Cicilan</th><td>${lamaCicilan.value} bulan</td></tr>`;
                }
                if (totalCicilan && totalCicilan.value) {
                    previewHtml += `<tr><th>Total Cicilan</th><td>${totalCicilan.value}</td></tr>`;
                }

                previewHtml += `
                    <tr><th>Jumlah Bayar</th><td>${jumlahBayar.value}</td></tr>
                    <tr><th>Metode Pembayaran</th><td>${pembayaranViaText}</td></tr>
                    <tr><th>Keterangan</th><td>${keterangan.value}</td></tr>
                `;

                if (bukti && bukti.files && bukti.files.length > 0) {
                    const file = bukti.files[0];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageRow = `<tr><th>Bukti</th><td><img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;" /></td></tr>`;
                            previewContent.innerHTML = `
                                <table class="table table-bordered">
                                    ${previewHtml}
                                    ${imageRow}
                                </table>`;
                            modalPreview.show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        const fileRow = `<tr><th>Bukti</th><td>${file.name}</td></tr>`;
                        previewContent.innerHTML = `<table class="table table-bordered">${previewHtml}${fileRow}</table>`;
                        modalPreview.show();
                    }
                } else {
                    const existingBukti = form.querySelector('input[name="file_kk_existing"]');
                    if (existingBukti && existingBukti.value) {
                        const buktiPath = `<?php echo base_url('uploads/bukti/'); ?>${existingBukti.value}`;
                        const ext = existingBukti.value.split('.').pop().toLowerCase();
                        let buktiHtml;
                        if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            buktiHtml = `<tr><th>Bukti</th><td><img src="${buktiPath}" class="img-fluid rounded" style="max-height: 200px;" /></td></tr>`;
                        } else if (ext === 'pdf') {
                            buktiHtml = `<tr><th>Bukti</th><td><a href="${buktiPath}" target="_blank" class="btn btn-outline-secondary btn-sm">Lihat Bukti PDF</a></td></tr>`;
                        }
                        previewContent.innerHTML = `<table class="table table-bordered">${previewHtml}${buktiHtml}</table>`;
                    } else {
                        previewContent.innerHTML = `<table class="table table-bordered">${previewHtml}</table>`;
                    }
                    modalPreview.show();
                }
            });

            // AJAX check on month change
            const bulanMulaiInput = document.getElementById('bulan_mulai');
            if (bulanMulaiInput) {
                bulanMulaiInput.addEventListener('change', function() {
                    const nomorRumah = $('#nomorRumah').val();
                    const idRumahDariPHP = "<?php echo $id ? decrypt_url($id) : ''; ?>";
                    const idRumah = idRumahDariPHP !== '' ? idRumahDariPHP : nomorRumah;
                    const tanggal = this.value;

                    if (!idRumah || !tanggal) return;

                    const [tahun, bulan] = tanggal.split('-');
                    const namaBulan = [
                        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];

                    fetch('<?php echo base_url(); ?>dashboard/cek_pembayaran', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id_rumah=' + encodeURIComponent(idRumah) + '&tanggal=' + encodeURIComponent(tanggal)
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
                                        window.location.href = '<?php echo base_url(); ?>pembayaran/' + data.id_rumah + '/' + data.id_pembayaran;
                                    }
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            }
        });
    </script>
</body>

</html>