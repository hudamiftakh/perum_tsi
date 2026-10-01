<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Warga Menunggak IPL</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.4;
        }

        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 4px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 2px 0;
        }

        .header-logo {
            text-align: left;
            width: 35%;
        }

        .header-info {
            text-align: right;
            width: 65%;
            font-size: 7.5pt;
            line-height: 1.35;
        }

        .header-info strong {
            font-size: 10pt;
            color: #0070c0;
        }

        .logo {
            height: 34px;
            margin-right: 4px;
            border: none;
        }

        .report-title-box {
            text-align: center;
            margin: 6px 0 10px 0;
        }

        .report-title {
            font-size: 11pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            letter-spacing: 0.3px;
        }

        .report-subtitle {
            font-size: 8pt;
            color: #333;
            margin-top: 3px;
        }

        .report-stats {
            font-size: 7.5pt;
            color: #475569;
            margin-top: 2px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            line-height: 1.45;
        }

        table.data-table th {
            background-color: #0070c0;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 8pt;
            border: 1px solid #005691;
            height: 26px;
            vertical-align: middle;
        }

        table.data-table td {
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            font-size: 7.5pt;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        .row-even td { background-color: #ffffff; }
        .row-odd td { background-color: #f8fafc; }

        .group-header td {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: bold;
            font-size: 8pt;
            text-align: left;
            border: 1px solid #7dd3fc;
            height: 24px;
        }

        .total-row td {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: bold;
            font-size: 8pt;
            border: 1px solid #94a3b8;
            height: 24px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI IDENTIK DENGAN LAPORAN LAIN -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="<?= base_url('logo_2-removebg-preview.png') ?>" alt="Logo 1" class="logo">
                <img src="<?= base_url('logo-tsi-removebg-preview.png') ?>" alt="Logo 2" class="logo">
            </td>
            <td class="header-info">
                <strong>PAGUYUBAN WARGA TAMAN SUKODONO INDAH</strong><br>
                Sekretariat: Perumahan Taman Sukodono Indah, Kedung, Sukodono, Sidoarjo 61258<br>
                Kontak Layanan: 0852-1234-5678 &nbsp;|&nbsp; Email: tsipaguyuban@gmail.com
            </td>
        </tr>
    </table>
    <hr style="border: 0.5px solid #0070c0; margin: 3px 0 8px 0;">

    <!-- JUDUL LAPORAN DAN RINGKASAN METADATA (TERINTEGRASI TANPA TABEL BERTUMPUK) -->
    <div class="report-title-box">
        <div class="report-title">LAPORAN REKAPITULASI WARGA MENUNGGAK IPL</div>
        <div class="report-subtitle">
            Tahun: <b><?= $tahun ?></b> &nbsp;|&nbsp; 
            Periode: <b><?= $periode_str ?> (<?= $total_bulan_wajib ?> Bulan Wajib)</b>
            <?php if (!empty($koordinator_label) && $koordinator_label !== 'Semua Koordinator (Seluruh Blok)'): ?>
                <br>Koordinator: <b><?= htmlspecialchars($koordinator_label) ?></b>
            <?php else: ?>
                &nbsp;|&nbsp; Koordinator: <b>Semua Koordinator</b>
            <?php endif; ?>
        </div>
        <div class="report-stats">
            Total Terdata: <b><?= number_format($total_rumah, 0, ',', '.') ?> Rumah</b> &nbsp;|&nbsp; 
            Warga Menunggak: <b style="color: #b91c1c;"><?= number_format($total_menunggak, 0, ',', '.') ?> Rumah</b> &nbsp;|&nbsp; 
            Total Tunggakan: <b style="color: #b91c1c;"><?= number_format($total_akumulasi_bulan, 0, ',', '.') ?> Bulan</b> &nbsp;|&nbsp; 
            Dicetak: <i><?= date('d/m/Y H:i') ?> WIB</i>
        </div>
    </div>
    <br>

    <!-- TABEL DATA WARGA MENUNGGAK (DENGAN PADDING LEGA & TINGGI YANG NYAMAN DILIHAT) -->
    <table class="data-table" cellpadding="6">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%; text-align: left;">Alamat / Blok</th>
                <th style="width: 22%; text-align: left;">Nama Warga</th>
                <th style="width: 12%;">No. WhatsApp</th>
                <?php if (empty($group_by_koor)): ?>
                    <th style="width: 16%; text-align: left;">Koordinator</th>
                    <th style="width: 9%;">Tunggakan</th>
                    <th style="width: 13%; text-align: left;">Bulan Belum Bayar</th>
                    <th style="width: 8%;">Status</th>
                <?php else: ?>
                    <th style="width: 11%;">Tunggakan</th>
                    <th style="width: 25%; text-align: left;">Rincian Bulan Belum Bayar</th>
                    <th style="width: 10%;">Status</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($menunggak)): ?>
                <tr>
                    <td colspan="<?= empty($group_by_koor) ? 8 : 7 ?>" class="text-center" style="padding: 18px; font-weight: bold; color: #16a34a;">
                        Alhamdulillah, seluruh warga sudah tertib melakukan pembayaran pada periode ini. Tidak ada tunggakan.
                    </td>
                </tr>
            <?php elseif (!empty($group_by_koor) && !empty($grouped_menunggak)): ?>
                <?php 
                $global_index = 0;
                foreach ($grouped_menunggak as $k_name => $grp): 
                ?>
                    <!-- HEADER GRUP PER KOORDINATOR -->
                    <tr class="group-header">
                        <td colspan="7">
                            &nbsp; KOORDINATOR: <?= htmlspecialchars($grp['nama']) ?>
                            &nbsp;&nbsp;-&nbsp;&nbsp;
                            <span style="font-weight: normal; color: #0369a1; font-size: 7.2pt;">
                                <?= $grp['total_warga'] ?> Warga Menunggak &nbsp;|&nbsp; Total: <?= number_format($grp['total_bulan'], 0, ',', '.') ?> Bulan Tunggakan
                            </span>
                        </td>
                    </tr>
                    <?php foreach ($grp['items'] as $w): 
                        $global_index++;
                        $row_class = ($global_index % 2 === 0) ? 'row-even' : 'row-odd';
                        $status_badge = ($w['tunggakan'] >= 3) 
                            ? '<span style="color: #b91c1c; font-weight: bold;">Surat Teguran</span>' 
                            : '<span style="color: #d97706; font-weight: bold;">Peringatan</span>';
                    ?>
                        <tr class="<?= $row_class ?>">
                            <td style="width: 5%;" class="text-center"><?= $global_index ?></td>
                            <td style="width: 15%; font-weight: bold;" class="text-left">
                                <?= htmlspecialchars($w['alamat'] ?: '-') ?>
                            </td>
                            <td style="width: 22%;" class="text-left">
                                <?= !empty($w['nama']) ? htmlspecialchars($w['nama']) : '<span style="color:#94a3b8; font-style:italic;">-</span>' ?>
                            </td>
                            <td style="width: 12%;" class="text-center">
                                <?= !empty($w['no_hp']) ? htmlspecialchars($w['no_hp']) : '-' ?>
                            </td>
                            <td style="width: 11%; font-weight: bold; color: #b91c1c;" class="text-center">
                                <?= $w['tunggakan'] ?> Bulan
                            </td>
                            <td style="width: 25%; font-size: 7.2pt;" class="text-left">
                                <?php if ($w['is_all_period']): ?>
                                    <b><?= htmlspecialchars($w['bulan_tunggak_short']) ?></b>
                                    <span style="color:#b91c1c; font-size:6.5pt;">(Semua)</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($w['bulan_tunggak_short']) ?>
                                <?php endif; ?>
                            </td>
                            <td style="width: 10%;" class="text-center">
                                <?= $status_badge ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                <!-- BARIS TOTAL -->
                <tr class="total-row">
                    <td colspan="4" class="text-right" style="padding-right: 10px;">
                        TOTAL WARGA MENUNGGAK:
                    </td>
                    <td class="text-center" style="color: #b91c1c; font-size: 8pt;">
                        <?= number_format($total_akumulasi_bulan, 0, ',', '.') ?> Bln
                    </td>
                    <td colspan="2" class="text-left" style="padding-left: 10px;">
                        <?= number_format($total_menunggak, 0, ',', '.') ?> Warga
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($menunggak as $index => $w): 
                    $row_class = ($index % 2 === 0) ? 'row-even' : 'row-odd';
                    $status_badge = ($w['tunggakan'] >= 3) 
                        ? '<span style="color: #b91c1c; font-weight: bold;">Surat Teguran</span>' 
                        : '<span style="color: #d97706; font-weight: bold;">Peringatan</span>';
                ?>
                    <tr class="<?= $row_class ?>">
                        <td style="width: 5%;" class="text-center"><?= $index + 1 ?></td>
                        <td style="width: 13%; font-weight: bold;" class="text-left">
                            <?= htmlspecialchars($w['alamat'] ?: '-') ?>
                        </td>
                        <td style="width: 18%;" class="text-left">
                            <?= !empty($w['nama']) ? htmlspecialchars($w['nama']) : '<span style="color:#94a3b8; font-style:italic;">-</span>' ?>
                        </td>
                        <td style="width: 11%;" class="text-center">
                            <?= !empty($w['no_hp']) ? htmlspecialchars($w['no_hp']) : '-' ?>
                        </td>
                        <td style="width: 16%;" class="text-left">
                            <?= !empty($w['koordinator']) ? htmlspecialchars($w['koordinator']) : '-' ?>
                        </td>
                        <td style="width: 9%; font-weight: bold; color: #b91c1c;" class="text-center">
                            <?= $w['tunggakan'] ?> Bulan
                        </td>
                        <td style="width: 18%; font-size: 7.2pt;" class="text-left">
                            <?php if ($w['is_all_period']): ?>
                                <b><?= htmlspecialchars($w['bulan_tunggak_short']) ?></b>
                            <?php else: ?>
                                <?= htmlspecialchars($w['bulan_tunggak_short']) ?>
                            <?php endif; ?>
                        </td>
                        <td style="width: 10%;" class="text-center">
                            <?= $status_badge ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <!-- BARIS TOTAL -->
                <tr class="total-row">
                    <td colspan="5" class="text-right" style="padding-right: 10px;">
                        TOTAL WARGA MENUNGGAK:
                    </td>
                    <td class="text-center" style="color: #b91c1c; font-size: 8pt;">
                        <?= number_format($total_akumulasi_bulan, 0, ',', '.') ?> Bln
                    </td>
                    <td colspan="2" class="text-left" style="padding-left: 10px;">
                        <?= number_format($total_menunggak, 0, ',', '.') ?> Warga
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
