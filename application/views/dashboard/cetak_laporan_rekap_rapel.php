<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Rekap Pembayaran IPL - Paguyuban Taman Sukodono Indah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #000;
        }

        @page {
            margin: 12mm;
        }

        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
            padding-bottom: 6px;
            border: none;
        }

        .header-logo {
            text-align: left;
            width: 50%;
        }

        .header-info {
            text-align: right;
            width: 50%;
        }

        .header-info strong {
            font-size: 10pt;
        }

        .logo {
            height: 35px;
            margin-right: 4px;
            border: none;
        }

        .report-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 12px 0 8px 0;
        }

        .table-container {
            margin-top: 8px;
            width: 100%;
        }

        table {
            width: 100% !important;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
            text-align: center;
            white-space: normal !important;
            word-wrap: break-word;
        }

        table thead th {
            background-color: #0070c0;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            padding: 4px 2px;
            font-weight: bold;
        }

        .nama-rumah {
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }

        /* Status cells — identik dengan cetak_laporan_pembayaran.php */
        .status-cell {
            padding: 2px 3px;
            font-weight: bold;
            font-size: 7.5pt;
        }

        .status-verified {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rapel {
            background-color: #e2e3ff;
            color: #383d7c;
        }

        .status-dimuka {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-date {
            font-size: 6pt;
            margin-top: 2px;
        }
    </style>
</head>

<body>
    <br>
    <table class="header-table" style="border:none;">
        <tr>
            <td class="header-logo" style="border:none;">
                <img src="<?= base_url('logo_2-removebg-preview.png') ?>" alt="Logo 1" class="logo">
                <img src="<?= base_url('logo-tsi-removebg-preview.png') ?>" alt="Logo 2" class="logo">
            </td>
            <td class="header-info" style="border:none;">
                <strong>PAGUYUBAN TAMAN SUKODONO INDAH</strong><br>
                Alamat: Jl. Taman Sukodono Indah, Kedung, Jumputrejo, Sukodono, Sidoarjo<br>
                Telepon: 0852-1234-5678<br>
                Email: tsipaguyuban@gmail.com
            </td>
        </tr>
    </table>
    <br>
    <hr>

    <h1 class="report-title">Laporan Rekap Pembayaran IPL
        <?php echo @$koordinator_filter['nama'] ? ' — ' . $koordinator_filter['nama'] : ''; ?><br>
        <?php
        function format_tgl_rekap($dt)
        {
            $bln = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $d = new DateTime($dt);
            return $d->format('d') . ' ' . $bln[(int)$d->format('n')] . ' ' . $d->format('Y H:i');
        }
        echo '<i>Periode: ' . $nama_bulan_periode . ' ' . $tahun . ' &nbsp;|&nbsp; Tanggal Cetak: ' . format_tgl_rekap('now') . '</i>';
        ?>
    </h1>

    <?php
    $bulan_indo_short = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des'
    ];

    // Rentang bulan — selalu tampilkan 12 bulan penuh
    $bulan_awal_pdf  = 1;
    $bulan_akhir_pdf = 12;
    $kolom_bulan     = $bulan_akhir_pdf - $bulan_awal_pdf + 1;

    // DEBUG — hapus setelah fix
    // Pastikan $tahun adalah integer
    $tahun = (int)$tahun;

    // Lebar kolom dinamis
    if ($kolom_bulan >= 10) {
        $lebar_no = 3;
        $lebar_nama = 13;
        $lebar_rumah = 10;
    } elseif ($kolom_bulan >= 7) {
        $lebar_no = 3;
        $lebar_nama = 15;
        $lebar_rumah = 11;
    } else {
        $lebar_no = 4;
        $lebar_nama = 20;
        $lebar_rumah = 15;
    }
    $lebar_bulan_pct = round((100 - $lebar_no - $lebar_nama - $lebar_rumah) / $kolom_bulan, 1);
    $font_cell       = ($kolom_bulan >= 10) ? '6pt' : (($kolom_bulan >= 7) ? '6.5pt' : '8pt');

    // Accumulator totals — nama variabel sama dengan laporan_rekap_rapel.php
    $total_ipl_bulan = [];
    $total_tgl_bayar = [];
    $counted_ids_pdf = [];
    for ($i = $bulan_awal_pdf; $i <= $bulan_akhir_pdf; $i++) {
        $total_ipl_bulan[$i] = 0;
        $total_tgl_bayar[$i] = 0;
    }
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:<?= $lebar_no ?>%; font-size:<?= $font_cell ?>; padding:4px 2px;">No</th>
                    <th style="width:<?= $lebar_nama ?>%; font-size:<?= $font_cell ?>; padding:4px 2px;">Nama</th>
                    <th style="width:<?= $lebar_rumah ?>%; font-size:<?= $font_cell ?>; padding:4px 2px;">Rumah</th>
                    <?php for ($i = $bulan_awal_pdf; $i <= $bulan_akhir_pdf; $i++): ?>
                        <th style="width:<?= $lebar_bulan_pct ?>%; font-size:<?= $font_cell ?>; padding:4px 2px;" nowrap>
                            <?= $bulan_indo_short[$i] ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                foreach ($rumah as $data_rumah):
                    $id_rumah = $data_rumah['id'];
                ?>
                    <tr>
                        <td style="width:<?= $lebar_no ?>%; font-size:7pt;"><?= $no++ ?></td>
                        <td class="nama-rumah" style="width:<?= $lebar_nama ?>%; font-size:<?= $font_cell ?>;"><?= strtoupper(htmlspecialchars($data_rumah['nama'])) ?></td>
                        <td style="width:<?= $lebar_rumah ?>%; font-size:<?= $font_cell ?>;"><?= htmlspecialchars($data_rumah['alamat']) ?></td>

                        <?php for ($i = $bulan_awal_pdf; $i <= $bulan_akhir_pdf; $i++):
                            $currentMonthStr = sprintf('%04d-%02d', $tahun, $i);

                            $dp = $this->db->query("
                            SELECT a.*, b.id_rumah, a.id as id_pembayaran
                            FROM master_pembayaran as a
                            LEFT JOIN master_users as b ON a.user_id = b.id
                            WHERE b.id_rumah = '$id_rumah'
                            AND (
                                a.untuk_bulan = '$currentMonthStr'
                                OR FIND_IN_SET('$currentMonthStr', a.bulan_rapel)
                                OR (
                                    DATE_FORMAT(a.bulan_mulai, '%Y-%m') = '$currentMonthStr'
                                    AND (a.bulan_rapel IS NULL OR a.bulan_rapel = '')
                                )
                            )
                            ORDER BY a.status DESC
                            LIMIT 1
                        ")->row_array();

                            // Tentukan status & hitung akumulator
                            $is_rapel  = false;
                            $is_dimuka = false;
                            $tgl_label = '';

                            if ($dp && $dp['status'] == 'verified') {
                                $is_rapel  = !empty($dp['bulan_rapel']);
                                $mulai_ym  = !empty($dp['bulan_mulai']) ? date('Y-m', strtotime($dp['bulan_mulai'])) : '';
                                $untuk_ym  = !empty($dp['untuk_bulan']) ? date('Y-m', strtotime($dp['untuk_bulan'])) : '';
                                $is_dimuka = ($mulai_ym && $untuk_ym && $mulai_ym < $untuk_ym);
                                $tgl_label = !empty($dp['tanggal_bayar']) ? date('d/m/y', strtotime($dp['tanggal_bayar'])) : '';

                                // Total IPL: hanya verified, per bulan kewajiban
                                // Untuk rapel, bagi rata jumlah_bayar sesuai jumlah bulan
                                $ipl_amount = (float)$dp['jumlah_bayar'];
                                if (!empty($dp['bulan_rapel'])) {
                                    $rapel_count = count(explode(',', $dp['bulan_rapel']));
                                    if ($rapel_count > 1) {
                                        $ipl_amount = $ipl_amount / $rapel_count;
                                    }
                                }
                                $total_ipl_bulan[$i] += $ipl_amount;

                                // Total Kas Masuk: berdasar tanggal_bayar — anti double-count via pay_key
                                if (!empty($dp['tanggal_bayar'])) {
                                    $pay_key = $id_rumah . '_' . $dp['id_pembayaran'];
                                    if (!in_array($pay_key, $counted_ids_pdf)) {
                                        $counted_ids_pdf[] = $pay_key;
                                        $ts_bayar    = strtotime($dp['tanggal_bayar']);
                                        $bln_bayar_i = (int)date('n', $ts_bayar);
                                        $thn_bayar   = (int)date('Y', $ts_bayar);
                                        if ($thn_bayar == $tahun && $bln_bayar_i >= $bulan_awal_pdf && $bln_bayar_i <= $bulan_akhir_pdf) {
                                            $total_tgl_bayar[$bln_bayar_i] += (float)$dp['jumlah_bayar'];
                                        }
                                    }
                                }
                            } elseif ($dp && $dp['status'] == 'pending') {
                                $tgl_label = !empty($dp['tanggal_bayar']) ? date('d/m/y', strtotime($dp['tanggal_bayar'])) : '';
                            }
                        ?>
                            <td style="width:<?= $lebar_bulan_pct ?>%;">
                                <?php if (!$dp): ?>
                                    <div class="status-cell status-unpaid"><span>Belum</span></div>
                                <?php elseif ($dp['status'] == 'verified'): ?>
                                    <?php if ($is_rapel): ?>
                                        <div class="status-cell status-rapel">
                                            <span>Rapel</span>
                                            <br><span class="status-date"><?= $tgl_label ?></span>
                                        </div>
                                    <?php elseif ($is_dimuka): ?>
                                        <div class="status-cell status-dimuka"><span>Dimuka</span></div>
                                    <?php else: ?>
                                        <div class="status-cell status-verified">
                                            <strong><?= number_format($dp['jumlah_bayar']) ?></strong>
                                            <br><span class="status-date"><?= $tgl_label ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($dp['status'] == 'pending'): ?>
                                    <div class="status-cell status-pending"><span>Pending</span></div>
                                <?php else: ?>
                                    <div class="status-cell status-unpaid"><span>Belum</span></div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>

                <!-- Row 1: Total IPL per bulan kewajiban -->
                <tr>
                    <td style="background-color:#f2f2f2; color:#000; font-size:6pt; font-weight:bold; width:<?= $lebar_no ?>%;"></td>
                    <td style="background-color:#f2f2f2; color:#000; text-align:right; font-size:7pt; font-weight:bold; width:<?= $lebar_nama ?>%;">TOTAL IPL<br><span style="font-weight:normal; font-size:5.5pt;">per bulan kewajiban</span></td>
                    <td style="background-color:#f2f2f2; color:#000; font-size:6pt; font-weight:bold; width:<?= $lebar_rumah ?>%;"></td>
                    <?php for ($i = $bulan_awal_pdf; $i <= $bulan_akhir_pdf; $i++): ?>
                        <td style="background-color:#f2f2f2; color:#000; text-align:center; font-size:7pt; font-weight:bold; width:<?= $lebar_bulan_pct ?>%;">
                            <?= $total_ipl_bulan[$i] > 0 ? number_format($total_ipl_bulan[$i]) : '-' ?>
                        </td>
                    <?php endfor; ?>
                </tr>

                <!-- Row 2: Total Kas Masuk berdasarkan tanggal_bayar aktual -->
                <tr>
                    <td style="background-color:#fff3e0; color:#bf360c; font-size:6pt; font-weight:bold; width:<?= $lebar_no ?>%;"></td>
                    <td style="background-color:#fff3e0; color:#bf360c; text-align:right; font-size:7pt; font-weight:bold; width:<?= $lebar_nama ?>%;">Total Kas Masuk<br><span style="font-weight:normal; font-size:5.5pt;">per tanggal bayar</span></td>
                    <td style="background-color:#fff3e0; color:#bf360c; font-size:6pt; font-weight:bold; width:<?= $lebar_rumah ?>%;"></td>
                    <?php for ($i = $bulan_awal_pdf; $i <= $bulan_akhir_pdf; $i++): ?>
                        <td style="background-color:#fff3e0; color:#bf360c; text-align:center; font-size:7pt; font-weight:bold; width:<?= $lebar_bulan_pct ?>%;">
                            <?php if ($total_tgl_bayar[$i] > 0): ?>
                                <?= number_format($total_tgl_bayar[$i]) ?>
                                <br><span style="font-size:5.5pt; font-weight:normal; color:#bf360c;"><?= $bulan_indo_short[$i] ?></span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>

            </tbody>
        </table>
    </div>

</body>

</html>