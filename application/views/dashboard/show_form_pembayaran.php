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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/monthSelect/index.js"></script>

    <style>
        body {
            background: #f1f5f9;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .card {
            border-radius: 18px;
        }

        .info-small {
            font-size: 0.84rem;
            color: #64748b;
        }

        .form-label {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.92rem;
            margin-bottom: 0.35rem;
        }

        .highlight {
            background-color: #e0f2fe;
            padding: 12px 14px;
            border-left: 4px solid #0284c7;
            margin-bottom: 1rem;
            border-radius: 8px;
            color: #0369a1;
            font-size: 0.9rem;
        }

        .select2-container .select2-selection--single {
            height: 44px !important;
            display: flex;
            align-items: center;
            border-radius: 10px !important;
            border: 1.5px solid #cbd5e1 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-left: 10px;
            line-height: normal !important;
            flex: 1;
            display: flex;
            align-items: center;
            font-size: 0.95rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important;
        }

        .select2-dropdown {
            border-radius: 12px !important;
            border: 1.5px solid #cbd5e1 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        }

        /* =========================================
           BULAN RAPEL GRID & TOUCH-FRIENDLY CARDS
           ========================================= */
        .bulan-rapel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 10px;
        }

        @media (max-width: 576px) {
            .bulan-rapel-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
        }

        .bulan-rapel-item {
            position: relative;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 10px 12px;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.18s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 52px;
            user-select: none;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        }

        .bulan-rapel-item:hover {
            background: #f0f7ff;
            border-color: #0284c7;
            transform: translateY(-1px);
        }

        .bulan-rapel-item.is-checked {
            background: #eff6ff !important;
            border-color: #0284c7 !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.25), 0 4px 10px rgba(2, 132, 199, 0.12) !important;
        }

        .bulan-rapel-item input[type="checkbox"] {
            width: 1.25em;
            height: 1.25em;
            accent-color: #0284c7;
            cursor: pointer;
            flex-shrink: 0;
            margin: 0;
        }

        .bulan-rapel-text-box {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            overflow: hidden;
        }

        .bulan-rapel-label {
            font-size: 0.88rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .bulan-rapel-item.is-checked .bulan-rapel-label {
            color: #0284c7;
        }

        .bulan-rapel-badge-tarif {
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            margin-top: 2px;
        }

        .bulan-rapel-item.is-checked .bulan-rapel-badge-tarif {
            color: #0369a1;
        }

        /* =========================================
           LIVE IMAGE PREVIEW STYLING
           ========================================= */
        .live-preview-box {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 14px;
            padding: 14px;
            transition: all 0.2s ease;
        }

        .live-preview-box:hover {
            border-color: #0284c7;
            background: #f0f7ff;
        }

        .preview-img-frame {
            max-height: 240px;
            border-radius: 10px;
            overflow: hidden;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
        }

        .preview-img-frame img {
            max-height: 240px;
            max-width: 100%;
            object-fit: contain;
            display: block;
        }

        /* Flatpickr Mobile Fix */
        .flatpickr-calendar {
            border-radius: 16px !important;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15) !important;
            border: 1.5px solid #cbd5e1 !important;
            font-family: inherit !important;
            z-index: 99999 !important;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
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
                            <div id="bulanRapelHeaderInfo" class="mb-2 p-2 rounded-3 bg-light border d-flex align-items-center justify-content-between">
                                <span class="small fw-bold text-dark" id="rapelInstruction"><i class="bi bi-calendar-check text-primary me-1"></i>Pilih Bulan Pembayaran:</span>
                                <span class="badge bg-primary rounded-pill px-2.5 py-1" id="rapelCountBadge" style="font-size: 0.75rem;">0 Bulan</span>
                            </div>
                            <div id="bulanRapelCheckboxes" class="bulan-rapel-grid"></div>
                            <div id="bulanRapelSelect" style="display:none;">
                                <label for="bulan_single" class="form-label small fw-bold text-muted mb-1"><i class="bi bi-calendar-event me-1"></i>Pilih Bulan Tagihan</label>
                                <select id="bulan_single" name="untuk_bulan" class="form-select form-select-lg rounded-3 border-primary-subtle shadow-sm" style="font-size: 0.98rem; padding: 10px 14px;"></select>
                                <div class="small text-muted mt-1" id="singleMonthTarifInfo"></div>
                            </div>
                        </div>

                        <script>
                            // PHP data passed to JavaScript
                            const bulanRapelChecked = <?php echo json_encode($bulan_rapel_checked); ?>;
                            const bulanRapelSelected = "<?php echo $bulan_rapel_selected; ?>";
                            const cfgNominalBaruVal = <?= (float)get_setting('nominal_ipl', 140000) ?>;
                            const cfgBulanBerlakuVal = '<?= get_setting('bulan_berlaku_nominal', '2026-11') ?>';
                            const cfgNominalLamaVal = <?= (float)get_setting('nominal_ipl_lama', 125000) ?>;

                            function getTarifInfo(ym) {
                                if (!ym) return { nominal: cfgNominalBaruVal, label: 'Rp ' + (cfgNominalBaruVal/1000) + 'rb', isBaru: true };
                                const clean = ym.substring(0, 7);
                                const isBaru = (clean >= cfgBulanBerlakuVal);
                                const nominal = isBaru ? cfgNominalBaruVal : cfgNominalLamaVal;
                                const label = 'Rp ' + (nominal/1000) + 'rb';
                                return { nominal: nominal, label: label, isBaru: isBaru };
                            }

                            function getBulanPilihan2025() {
                                const bulan = [];
                                const namaBulan = [
                                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                                ];

                                // 🔹 2025: Juni - Desember
                                for (let m = 6; m <= 12; m++) {
                                    const ym = `2025-${String(m).padStart(2, '0')}`;
                                    bulan.push({
                                        value: ym,
                                        label: `${namaBulan[m - 1]} 2025`,
                                        tarif: getTarifInfo(ym)
                                    });
                                }

                                // 🔹 2026: Januari - Desember
                                for (let m = 1; m <= 12; m++) {
                                    const ym = `2026-${String(m).padStart(2, '0')}`;
                                    bulan.push({
                                        value: ym,
                                        label: `${namaBulan[m - 1]} 2026`,
                                        tarif: getTarifInfo(ym)
                                    });
                                }

                                // 🔹 2027: Januari - Desember (Untuk Bayar di Muka)
                                for (let m = 1; m <= 12; m++) {
                                    const ym = `2027-${String(m).padStart(2, '0')}`;
                                    bulan.push({
                                        value: ym,
                                        label: `${namaBulan[m - 1]} 2027`,
                                        tarif: getTarifInfo(ym)
                                    });
                                }

                                return bulan;
                            }

                            function updateRapelCount(jumlahRapel) {
                                const checkboxes = document.querySelectorAll('#bulanRapelCheckboxes input[type="checkbox"]');
                                const checkedCount = document.querySelectorAll('#bulanRapelCheckboxes input[type="checkbox"]:checked').length;
                                const badge = document.getElementById('rapelCountBadge');
                                const instr = document.getElementById('rapelInstruction');
                                if (badge) {
                                    badge.textContent = `${checkedCount} / ${jumlahRapel} Bulan Dipilih`;
                                    if (checkedCount === jumlahRapel) {
                                        badge.className = 'badge bg-success rounded-pill px-2.5 py-1';
                                    } else {
                                        badge.className = 'badge bg-primary rounded-pill px-2.5 py-1';
                                    }
                                }
                                if (instr) {
                                    instr.innerHTML = `<i class="bi bi-calendar-check text-primary me-1"></i>Pilih ${jumlahRapel} Bulan Tagihan:`;
                                }
                            }

                            function syncBulanMulaiHidden() {
                                const metode = document.getElementById('metode').value;
                                const bulanMulaiHidden = document.getElementById('bulan_mulai');
                                if (!bulanMulaiHidden) return;

                                if (metode === '1_bulan') {
                                    const single = document.getElementById('bulan_single');
                                    if (single && single.value) {
                                        bulanMulaiHidden.value = single.value;
                                    }
                                } else if (metode.includes('_bulan')) {
                                    const firstChecked = document.querySelector('#bulanRapelCheckboxes input[type="checkbox"]:checked');
                                    if (firstChecked) {
                                        bulanMulaiHidden.value = firstChecked.value;
                                    }
                                }
                            }

                            function handleMetodeChange() {
                                const metode = document.getElementById('metode').value;
                                const bulanRapelContainer = document.getElementById('bulanRapelContainer');
                                const bulanRapelCheckboxes = document.getElementById('bulanRapelCheckboxes');
                                const bulanRapelSelect = document.getElementById('bulanRapelSelect');
                                const bulanSingle = document.getElementById('bulan_single');
                                const singleInfo = document.getElementById('singleMonthTarifInfo');
                                let rapelMatch = metode.match(/^(\d+)_bulan$/);

                                if (rapelMatch) {
                                    let jumlahRapel = parseInt(rapelMatch[1]);
                                    bulanRapelContainer.style.display = '';

                                    let options = getBulanPilihan2025();

                                    bulanRapelCheckboxes.innerHTML = '';
                                    bulanSingle.innerHTML = '<option value="">-- Pilih Bulan --</option>';

                                    if (jumlahRapel > 1) {
                                        bulanRapelCheckboxes.style.display = 'grid';
                                        bulanRapelSelect.style.display = 'none';
                                        document.getElementById('bulanRapelHeaderInfo').style.display = 'flex';

                                        options.forEach((opt, idx) => {
                                            let id = 'bulan_rapel_' + idx;
                                            let isChecked = bulanRapelChecked.includes(opt.value);

                                            let wrapper = document.createElement('label');
                                            wrapper.className = 'bulan-rapel-item' + (isChecked ? ' is-checked' : '');
                                            wrapper.htmlFor = id;

                                            let checkbox = document.createElement('input');
                                            checkbox.type = 'checkbox';
                                            checkbox.name = 'bulan_rapel[]';
                                            checkbox.value = opt.value;
                                            checkbox.id = id;
                                            checkbox.checked = isChecked;

                                            let textBox = document.createElement('div');
                                            textBox.className = 'bulan-rapel-text-box';

                                            let span = document.createElement('span');
                                            span.className = 'bulan-rapel-label';
                                            span.textContent = opt.label;

                                            let badgeTarif = document.createElement('span');
                                            badgeTarif.className = 'bulan-rapel-badge-tarif' + (opt.tarif.isBaru ? ' tarif-baru' : '');
                                            badgeTarif.textContent = opt.tarif.label;

                                            textBox.appendChild(span);
                                            textBox.appendChild(badgeTarif);

                                            wrapper.appendChild(checkbox);
                                            wrapper.appendChild(textBox);
                                            bulanRapelCheckboxes.appendChild(wrapper);

                                            checkbox.addEventListener('change', function() {
                                                let checked = bulanRapelCheckboxes.querySelectorAll('input[type="checkbox"]:checked');
                                                if (checked.length > jumlahRapel) {
                                                    this.checked = false;
                                                    Swal.fire({
                                                        icon: 'warning',
                                                        title: 'Batas Pilihan Tercapai',
                                                        text: `Anda memilih jenis rapel ${jumlahRapel} bulan. Hanya dapat memilih ${jumlahRapel} bulan.`,
                                                        timer: 2000,
                                                        showConfirmButton: false
                                                    });
                                                }
                                                if (this.checked) {
                                                    wrapper.classList.add('is-checked');
                                                } else {
                                                    wrapper.classList.remove('is-checked');
                                                }
                                                updateRapelCount(jumlahRapel);
                                                syncBulanMulaiHidden();
                                                updateJumlahBayar();
                                            });
                                        });

                                        updateRapelCount(jumlahRapel);
                                        bulanSingle.required = false;
                                        bulanSingle.disabled = true;

                                    } else {
                                        bulanRapelCheckboxes.style.display = 'none';
                                        bulanRapelSelect.style.display = 'block';
                                        document.getElementById('bulanRapelHeaderInfo').style.display = 'none';

                                        options.forEach(opt => {
                                            let option = document.createElement('option');
                                            option.value = opt.value;
                                            option.textContent = `${opt.label} (${opt.tarif.label})`;
                                            if (bulanRapelSelected === opt.value) {
                                                option.selected = true;
                                            }
                                            bulanSingle.appendChild(option);
                                        });

                                        bulanSingle.required = true;
                                        bulanSingle.disabled = false;

                                        bulanSingle.onchange = function() {
                                            const tarifObj = getTarifInfo(this.value);
                                            if (singleInfo && this.value) {
                                                singleInfo.innerHTML = `<span class="badge ${tarifObj.isBaru ? 'bg-primary' : 'bg-secondary'} me-1">Tarif: ${tarifObj.label}</span> Periode tagihan terpilih`;
                                            } else if (singleInfo) {
                                                singleInfo.innerHTML = '';
                                            }
                                            syncBulanMulaiHidden();
                                            updateJumlahBayar();
                                        };
                                        bulanSingle.dispatchEvent(new Event('change'));
                                    }

                                } else {
                                    bulanRapelContainer.style.display = 'none';
                                    bulanRapelCheckboxes.innerHTML = '';
                                    bulanSingle.innerHTML = '';
                                    bulanSingle.required = false;
                                    bulanSingle.disabled = true;
                                }
                                syncBulanMulaiHidden();
                                updateJumlahBayar();
                            }

                            document.addEventListener('DOMContentLoaded', handleMetodeChange);
                        </script>
                    </div>

                    <?php
                    $bulan_mulai_value = isset($data_update['bulan_mulai']) ? date('Y-m', strtotime($data_update['bulan_mulai'])) : date('Y-m');
                    ?>
                    <!-- Hidden Bulan Mulai (Disinkronkan otomatis oleh field Untuk Bulan) -->
                    <input type="hidden" value="<?= $bulan_mulai_value ?>" id="bulan_mulai" name="bulan_mulai">

                    <!-- Field Tanggal Pembayaran (Clean & Mobile-Friendly) -->
                    <div class="mb-3">
                        <label for="tanggal_bayar" class="form-label d-flex align-items-center gap-1">
                            <i class="bi bi-calendar-event text-primary"></i> Tanggal Pembayaran <span class="text-danger">*</span>
                        </label>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-calendar3"></i></span>
                            <input type="text" class="form-control border-start-0" value="<?php echo isset($data_update['tanggal_bayar']) ? $data_update['tanggal_bayar'] : date('Y-m-d'); ?>" id="tanggal_bayar" name="tanggal_bayar" placeholder="Pilih tanggal pembayaran" required readonly style="background-color:#ffffff; cursor:pointer; font-weight:500;">
                        </div>
                        <div class="info-small mt-1">Tap untuk memilih tanggal transaksi pembayaran warga</div>
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
                            <div class="info-small">Kalkulasi mengikuti tarif IPL aktif (Rp <?= number_format(get_tarif_ipl(), 0, ',', '.') ?>/bulan)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_bayar" class="form-label d-flex align-items-center gap-1">
                            <i class="bi bi-cash-stack text-success"></i> Jumlah yang Dibayar Hari Ini <span class="text-danger">*</span>
                        </label>
                        <?php
                        $jumlah_bayar_value = isset($data_update['jumlah_bayar']) ? number_format($data_update['jumlah_bayar'], 0, ',', '.') : number_format(get_tarif_ipl($bulan_mulai_value), 0, ',', '.');
                        ?>
                        <div class="input-group shadow-sm rounded-3">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="text" class="form-control fw-bold text-dark fs-5" name="jumlah_bayar" value="<?= $jumlah_bayar_value; ?>" id="jumlah_bayar" required autocomplete="off">
                        </div>
                        <div class="info-small mt-1">Otomatis terhitung sesuai nominal tarif iuran bulan yang dipilih</div>
                    </div>

                    <div class="mb-3">
                        <label for="pembayaran_via" class="form-label d-flex align-items-center gap-1">
                            <i class="bi bi-credit-card text-primary"></i> Metode Pembayaran <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg rounded-3 shadow-sm" name="pembayaran_via" id="pembayaran_via" onchange="cekMetodeBayar()" required style="font-size: 0.98rem; padding: 10px 14px;">
                            <option value="">Pilih cara membayar...</option>
                            <option value="koordinator" <?= (isset($data_update['pembayaran_via']) && $data_update['pembayaran_via'] == 'koordinator') ? 'selected' : '' ?>>Ke Koordinator Blok (Tunai)</option>
                            <option value="transfer" <?= (isset($data_update['pembayaran_via']) && $data_update['pembayaran_via'] == 'transfer') ? 'selected' : '' ?>>Transfer ke Bendahara (BCA 8221586107 / Dhani Kispananto) </option>
                            <option value="transfer_2" <?= (isset($data_update['pembayaran_via']) && $data_update['pembayaran_via'] == 'transfer_2') ? 'selected' : '' ?>>Transfer ke Aditya</option>
                        </select>
                    </div>

                    <!-- UPLOAD BUKTI DENGAN LIVE IMAGE PREVIEW -->
                    <div class="mb-3 d-none" id="buktiTransfer">
                        <label for="bukti" class="form-label d-flex align-items-center gap-1">
                            <i class="bi bi-image text-primary"></i> Upload Bukti Pembayaran Transfer <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control rounded-3 shadow-sm" name="bukti" id="bukti" accept="image/*,application/pdf">
                        <div class="info-small mt-1">Format: JPG, JPEG, PNG, atau PDF. Maksimal 5 MB.</div>
                        
                        <!-- Live Image Preview Box Saat Dipilih -->
                        <div id="liveBuktiContainer" class="live-preview-box mt-3 d-none">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-success d-flex align-items-center gap-1">
                                    <i class="bi bi-check-circle-fill"></i> Foto Bukti Siap Diupload
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill shadow-sm" id="btnHapusBukti" title="Batal pilih foto">
                                    <i class="bi bi-trash"></i> Hapus Foto
                                </button>
                            </div>
                            <div class="preview-img-frame shadow-sm mb-2" id="liveImgBox">
                                <img id="liveBuktiImg" src="" alt="Preview Bukti Transfer" class="img-fluid rounded">
                            </div>
                            <div id="livePdfInfo" class="alert alert-secondary py-2 px-3 small mb-2 d-none">
                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-5 me-1"></i> <span id="livePdfName">-</span>
                            </div>
                            <div class="small text-muted font-monospace" id="liveBuktiMeta">-</div>
                        </div>

                        <!-- Bukti Sebelumnya (Jika Mode Edit) -->
                        <?php if (isset($data_update['bukti']) && !empty($data_update['bukti'])): ?>
                            <div class="live-preview-box mt-3" id="existingBuktiBox">
                                <label class="form-label small fw-bold text-muted mb-2 d-flex align-items-center gap-1">
                                    <i class="bi bi-archive"></i> Bukti Pembayaran Tersimpan:
                                </label>
                                <?php
                                $ext = pathinfo($data_update['bukti'], PATHINFO_EXTENSION);
                                $file_url = base_url('uploads/bukti/' . $data_update['bukti']);
                                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png'])): ?>
                                    <div class="preview-img-frame shadow-sm mb-2">
                                        <img src="<?= $file_url ?>" alt="Bukti Sebelumnya" class="img-fluid rounded">
                                    </div>
                                <?php elseif (strtolower($ext) === 'pdf'): ?>
                                    <div class="mb-2">
                                        <a href="<?= $file_url ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> Buka Dokumen PDF Bukti
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <input type="hidden" name="file_kk_existing" value="<?= $data_update['bukti'] ?>">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label d-flex align-items-center gap-1">
                            <i class="bi bi-chat-left-text text-secondary"></i> Keterangan (opsional)
                        </label>
                        <textarea name="keterangan" class="form-control rounded-3" rows="2" placeholder="Contoh: Pembayaran iuran warga"><?= isset($data_update['keterangan']) ? $data_update['keterangan'] : ''; ?></textarea>
                    </div>
                    <div class="mb-3 pt-2">
                        <button type="button" class="btn btn-warning w-100 py-2.5 mb-2 rounded-pill fw-semibold shadow-sm text-dark d-flex align-items-center justify-content-center gap-2" id="btnPreview">
                            <i class="bi bi-eye-fill"></i> Preview Isian & Foto Bukti
                        </button>
                        <?php if (!empty($id)) :  ?>
                            <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-lg"></i> Revisi Isian
                            </button>
                        <?php else : ?>
                            <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-pill fw-semibold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-send-fill"></i> Simpan & Kirim Notifikasi WA
                            </button>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="modal fade" id="modalPreview" tabindex="-1" aria-labelledby="modalPreviewLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                            <div class="modal-header bg-light border-bottom">
                                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalPreviewLabel">
                                    <i class="bi bi-eye text-primary"></i> Preview Data & Bukti Pembayaran
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4" id="previewContent">
                            </div>
                            <div class="modal-footer bg-light border-top">
                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
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

            // Flatpickr Tanggal Bayar (Clean Date Picker murni tanpa jam dan tanpa tombol OK popup)
            flatpickr("#tanggal_bayar", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "j F Y",
                locale: "id",
                allowInput: false,
                disableMobile: false,
                defaultDate: "<?= !empty($data_update['tanggal_bayar']) ? $data_update['tanggal_bayar'] : date('Y-m-d') ?>",
                onChange: function(selectedDates, dateStr, instance) {
                    instance.close();
                }
            });

            // Event listener metode pembayaran
            const metodeSelect = document.getElementById("pembayaran_via");
            const buktiTransfer = document.getElementById("buktiTransfer");
            if (metodeSelect && buktiTransfer) {
                metodeSelect.addEventListener("change", cekMetodeBayar);
                cekMetodeBayar(); // Initial check
            }

            function cekMetodeBayar() {
                const buktiInput = document.getElementById("bukti");
                if (metodeSelect.value === "transfer" || metodeSelect.value === "transfer_2") {
                    buktiTransfer.classList.remove("d-none");
                    if (buktiInput) buktiInput.required = true;
                } else {
                    buktiTransfer.classList.add("d-none");
                    if (buktiInput) buktiInput.required = false;
                }
            }

            // Live preview foto bukti transfer saat dipilih
            const buktiInput = document.getElementById("bukti");
            const liveBuktiContainer = document.getElementById("liveBuktiContainer");
            const liveBuktiImg = document.getElementById("liveBuktiImg");
            const liveImgBox = document.getElementById("liveImgBox");
            const livePdfInfo = document.getElementById("livePdfInfo");
            const livePdfName = document.getElementById("livePdfName");
            const liveBuktiMeta = document.getElementById("liveBuktiMeta");
            const btnHapusBukti = document.getElementById("btnHapusBukti");

            if (buktiInput) {
                buktiInput.addEventListener("change", function() {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        const fileSizeKb = (file.size / 1024).toFixed(1);
                        if (liveBuktiMeta) {
                            liveBuktiMeta.textContent = `${file.name} (${fileSizeKb} KB)`;
                        }

                        if (file.type.startsWith('image/')) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                liveBuktiImg.src = e.target.result;
                                liveImgBox.classList.remove('d-none');
                                livePdfInfo.classList.add('d-none');
                                liveBuktiContainer.classList.remove('d-none');
                            };
                            reader.readAsDataURL(file);
                        } else {
                            liveImgBox.classList.add('d-none');
                            livePdfName.textContent = file.name;
                            livePdfInfo.classList.remove('d-none');
                            liveBuktiContainer.classList.remove('d-none');
                        }
                    } else {
                        if (liveBuktiContainer) liveBuktiContainer.classList.add('d-none');
                    }
                });
            }

            if (btnHapusBukti) {
                btnHapusBukti.addEventListener("click", function() {
                    if (buktiInput) buktiInput.value = '';
                    if (liveBuktiContainer) liveBuktiContainer.classList.add('d-none');
                });
            }

            const jumlahBayar = document.getElementById('jumlah_bayar');
            const metode = document.getElementById('metode');
            const bulanRapelCheckboxes = document.getElementById('bulanRapelCheckboxes');
            const cfgNominalBaru = <?= (float)get_setting('nominal_ipl', 140000) ?>;
            const cfgBulanBerlaku = '<?= get_setting('bulan_berlaku_nominal', '2026-11') ?>';
            const cfgNominalLama = <?= (float)get_setting('nominal_ipl_lama', 125000) ?>;

            function getTarifBulan(ym) {
                if (!ym) return cfgNominalBaru;
                const cleanYm = ym.substring(0, 7);
                return (cleanYm < cfgBulanBerlaku) ? cfgNominalLama : cfgNominalBaru;
            }

            function updateJumlahBayar() {
                let metodeVal = metode.value;
                let rapelMatch = metodeVal.match(/^(\d+)_bulan$/);
                if (rapelMatch) {
                    let bulanCount = parseInt(rapelMatch[1]);
                    if (bulanCount > 1 && bulanRapelCheckboxes) {
                        let checkedBoxes = bulanRapelCheckboxes.querySelectorAll('input[type="checkbox"]:checked');
                        if (checkedBoxes.length > 0) {
                            let total = 0;
                            checkedBoxes.forEach(cb => {
                                total += getTarifBulan(cb.value);
                            });
                            jumlahBayar.value = total.toLocaleString('id-ID');
                        } else {
                            jumlahBayar.value = '';
                        }
                    } else if (bulanCount === 1) {
                        let bulanSingleSelect = document.getElementById('bulan_single');
                        let selectedYm = (bulanSingleSelect && bulanSingleSelect.value) ? bulanSingleSelect.value : (document.getElementById('bulan_mulai')?.value || '');
                        let nominal = getTarifBulan(selectedYm);
                        jumlahBayar.value = nominal.toLocaleString('id-ID');
                    }
                } else {
                    jumlahBayar.value = '';
                }
            }

            if (bulanRapelCheckboxes) {
                bulanRapelCheckboxes.addEventListener('change', updateJumlahBayar);
            }
            const bulanSingleSelectEl = document.getElementById('bulan_single');
            if (bulanSingleSelectEl) {
                bulanSingleSelectEl.addEventListener('change', updateJumlahBayar);
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

            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        setTimeout(() => {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Memproses data & notifikasi...';
                        }, 50);
                    }
                });
            }

            btnPreview.addEventListener('click', function(e) {
                e.preventDefault();

                const nomorRumah = form.querySelector('[name="user_id"]');
                const metode = form.querySelector('[name="metode"]');
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
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <tr><th style="width:35%; background:#f8fafc;">Nomor Rumah</th><td><strong>${nomorRumahText}</strong></td></tr>
                            <tr><th style="background:#f8fafc;">Jenis Pembayaran</th><td>${metodeText}</td></tr>
                `;

                if (bulanDetail) {
                    previewHtml += `<tr><th style="background:#f8fafc;">Untuk Bulan</th><td><span class="badge bg-primary-subtle text-primary border px-2.5 py-1.5 fs-7">${bulanDetail}</span></td></tr>`;
                }
                previewHtml += `
                    <tr><th style="background:#f8fafc;">Tanggal Pembayaran</th><td>${tanggalBayar.value}</td></tr>
                `;

                if (lamaCicilan && lamaCicilan.value) {
                    previewHtml += `<tr><th style="background:#f8fafc;">Lama Cicilan</th><td>${lamaCicilan.value} bulan</td></tr>`;
                }
                if (totalCicilan && totalCicilan.value) {
                    previewHtml += `<tr><th style="background:#f8fafc;">Total Cicilan</th><td>${totalCicilan.value}</td></tr>`;
                }

                previewHtml += `
                    <tr><th style="background:#f8fafc;">Jumlah Bayar</th><td><strong class="text-success fs-6">Rp ${jumlahBayar.value}</strong></td></tr>
                    <tr><th style="background:#f8fafc;">Metode Pembayaran</th><td>${pembayaranViaText}</td></tr>
                    <tr><th style="background:#f8fafc;">Keterangan</th><td>${keterangan.value || '-'}</td></tr>
                `;

                function showModalWithContent(extraRows) {
                    previewContent.innerHTML = `${previewHtml}${extraRows}</table></div>`;
                    modalPreview.show();
                }

                if (bukti && bukti.files && bukti.files.length > 0) {
                    const file = bukti.files[0];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageRow = `
                                <tr>
                                    <th style="background:#f8fafc;">Foto Bukti Pembayaran</th>
                                    <td>
                                        <div class="preview-img-frame shadow-sm" style="max-height:260px;">
                                            <img src="${e.target.result}" class="img-fluid rounded" alt="Foto Bukti" />
                                        </div>
                                        <div class="small text-muted font-monospace mt-1"><i class="bi bi-file-earmark-image me-1"></i>${file.name} (${(file.size/1024).toFixed(1)} KB)</div>
                                    </td>
                                </tr>`;
                            showModalWithContent(imageRow);
                        };
                        reader.readAsDataURL(file);
                    } else {
                        const fileRow = `
                            <tr>
                                <th style="background:#f8fafc;">Dokumen Bukti</th>
                                <td>
                                    <span class="badge bg-secondary p-2"><i class="bi bi-file-earmark-pdf me-1"></i>${file.name}</span>
                                </td>
                            </tr>`;
                        showModalWithContent(fileRow);
                    }
                } else {
                    const existingBukti = form.querySelector('input[name="file_kk_existing"]');
                    if (existingBukti && existingBukti.value) {
                        const buktiPath = `<?php echo base_url('uploads/bukti/'); ?>${existingBukti.value}`;
                        const ext = existingBukti.value.split('.').pop().toLowerCase();
                        let buktiHtml = '';
                        if (['jpg', 'jpeg', 'png'].includes(ext)) {
                            buktiHtml = `
                                <tr>
                                    <th style="background:#f8fafc;">Foto Bukti Tersimpan</th>
                                    <td>
                                        <div class="preview-img-frame shadow-sm" style="max-height:260px;">
                                            <img src="${buktiPath}" class="img-fluid rounded" alt="Foto Bukti" />
                                        </div>
                                        <div class="small text-muted font-monospace mt-1">${existingBukti.value}</div>
                                    </td>
                                </tr>`;
                        } else if (ext === 'pdf') {
                            buktiHtml = `
                                <tr>
                                    <th style="background:#f8fafc;">Dokumen Bukti</th>
                                    <td>
                                        <a href="${buktiPath}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill"><i class="bi bi-file-earmark-pdf me-1"></i>Lihat Bukti PDF</a>
                                    </td>
                                </tr>`;
                        }
                        showModalWithContent(buktiHtml);
                    } else {
                        showModalWithContent(`<tr><th style="background:#f8fafc;">Foto Bukti</th><td><span class="text-muted fst-italic">Belum ada foto bukti yang dipilih</span></td></tr>`);
                    }
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