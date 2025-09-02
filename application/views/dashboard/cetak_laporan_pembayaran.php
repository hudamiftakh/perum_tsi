<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran IPL - Paguyuban Taman Sukodono Indah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            /* lebih kecil agar muat di halaman PDF */
            color: #000;
        }

        @page {
            margin: 12mm;
            /* sedikit diperkecil untuk menambah area cetak */
        }

        /* Using a table for the header layout as TCPDF doesn't support flexbox */
        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 10px;
            /* dikurangi */
        }

        .header-table td {
            vertical-align: top;
            padding-bottom: 6px;
            /* dikurangi */
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
            /* dikurangi */
        }

        .logo {
            height: 35px;
            /* diperkecil */
            margin-right: 4px;
            border: none;
        }

        .report-title {
            text-align: center;
            font-size: 11pt;
            /* diperkecil */
            font-weight: bold;
            margin: 12px 0 8px 0;
        }

        .info-section {
            font-size: 7pt;
            margin-bottom: 8px;
        }

        .info-section p {
            margin: 2px 0;
        }

        .table-container {
            margin-top: 8px;
            width: 100%;
        }

        /* Pastikan tabel menggunakan full width dan kolom terdistribusi merata */
        table {
            width: 100% !important;
            border-collapse: collapse;
            font-size: 8pt;
            table-layout: fixed;
            /* paksa tabel mengisi lebar penuh, kolom terdistribusi */
        }

        /* Override inline width supaya tabel tetap responsif dan mengisi 100% */
        table th,
        table td {
            border: 1px solid #000;
            padding: 3px;
            /* dikurangi agar lebih padat */
            vertical-align: middle;
            text-align: center;
            white-space: normal !important;
            /* biarkan wrap jika teks panjang */
            word-wrap: break-word;
            width: auto !important;
        }

        table thead th {
            background-color: #0070c0;
            color: #fff;
            text-align: center;
            white-space: nowrap;
            font-size: 8pt;
        }

        .nama-rumah {
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }

        /* Simplified status styles */
        .status-cell {
            padding: 2px 3px;
            font-weight: bold;
            font-size: 7.5pt;
        }

        .status-verified {
            background-color: #d4edda;
            color: #155724;
        }

        .status-dimuka {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-rapel {
            background-color: #e2e3ff;
            color: #383d7c;
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
            display: block;
            margin-top: 2px;
        }

        .total-row td {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 8pt;
        }
    </style>
</head>

<body>
    <br>
    <table class="header-table" style="border: none;">
        <tr>
            <td class="header-logo" style="border: none;">
                <img src="<?= base_url('logo_2-removebg-preview.png') ?>" alt="Logo 1" class="logo">
                <img src="<?= base_url('logo-tsi-removebg-preview.png') ?>" alt="Logo 2" class="logo">
            </td>
            <td class="header-info" style="border: none;">
                <strong>PAGUYUBAN TAMAN SUKODONO INDAH</strong><br>
                Alamat: Jl. Taman Sukodono Indah, Kedung, Jumputrejo, Sukodono, Sidoarjo<br>
                Telepon: 0852-1234-5678<br>
                Email: tsipaguyuban@gmail.com
            </td>
        </tr>
    </table>
    <br>
    <hr>

    <h1 class="report-title">Laporan Pembayaran Iuran Pengelolaan Lingkungan
        <?php echo @$koordinator['nama']; ?>
    </h1>
    <div class="table-container">
        <table style="width:900px !important;">
            <thead>
                <tr>
                    <th style="width: 5%; height: 18px; font-weight: bold;">No</th>
                    <th style="width: 15%; font-weight: bold;">Nama</th>
                    <th style="width: 12%; font-weight: bold;">Rumah</th>
                    <?php
                    $tahun_terpilih = $this->input->get('tahun') ?: date('Y');
                    // $bulan_terakhir = ($tahun_terpilih == date('Y')) ? date('n') : 12;
                    $bulan_terakhir = 12;
                    $bulan_mulai = 6;
                    $kolom_bulan = $bulan_terakhir - $bulan_mulai + 1;

                    $bulan_indonesia = [
                        1  => 'Januari',
                        2  => 'Februari',
                        3  => 'Maret',
                        4  => 'April',
                        5  => 'Mei',
                        6  => 'Juni',
                        7  => 'Juli',
                        8  => 'Agustus',
                        9  => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember'
                    ];

                    for ($i = $bulan_mulai; $i <= $bulan_terakhir; $i++): ?>
                        <th style="width: <?= 50 / $kolom_bulan ?>%; font-weight: bold;">
                            <?= $bulan_indonesia[$i] ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;

                // accumulator total
                $total_per_bulan = [];
                $total_transfer_per_bulan = [];
                $total_koordinator_per_bulan = [];

                foreach ($rumah as $data_bulanan): ?>
                    <tr>
                        <td style="width: 5%;"><?= $no++; ?></td>
                        <td class="nama-rumah" nowrap style="width: 15%;"><?= strtoupper(htmlspecialchars($data_bulanan['nama'])); ?></td>
                        <td style="width: 12%;"><?= htmlspecialchars($data_bulanan['alamat']); ?></td>

                        <?php for ($i = $bulan_mulai; $i <= $bulan_terakhir; $i++):
                            $currentMonthStr = sprintf('%04d-%02d', $tahun_terpilih, $i);

                            $data_pembayaran = $this->db->query("
                    SELECT a.*, b.*, a.id as id_pembayaran
                    FROM master_pembayaran as a
                    LEFT JOIN master_users as b ON a.user_id = b.id
                    WHERE b.id_rumah = '" . $data_bulanan['id'] . "'
                    AND (
                        a.untuk_bulan = '$currentMonthStr'
                        OR FIND_IN_SET('$currentMonthStr', a.bulan_rapel)
                        OR DATE_FORMAT(a.bulan_mulai, '%Y-%m') = '$currentMonthStr'
                    )
                    ORDER BY b.rumah ASC
                ")->row_array();
                        ?>
                            <td style="width: <?= 50 / $kolom_bulan ?>%;">
                                <?php if ($data_pembayaran): ?>
                                    <?php if ($data_pembayaran['status'] == 'verified'): ?>
                                        <?php
                                        $bulan_mulai_pembayaran = date('Y-m', strtotime($data_pembayaran['bulan_mulai']));
                                        $bulan_untuk = date('Y-m', strtotime($data_pembayaran['untuk_bulan']));
                                        $is_bayar_dimuka = ($bulan_mulai_pembayaran < $bulan_untuk);
                                        $is_rapel = ($data_pembayaran['metode'] !== '1_bulan');

                                        // ✅ hanya hitung di bulan mulai
                                        if ($currentMonthStr == $bulan_mulai_pembayaran) {
                                            $jumlah = (float)$data_pembayaran['jumlah_bayar'];

                                            if (!isset($total_per_bulan[$i])) $total_per_bulan[$i] = 0;
                                            $total_per_bulan[$i] += $jumlah;

                                            if (stripos($data_pembayaran['pembayaran_via'], 'transfer') !== false) {
                                                if (!isset($total_transfer_per_bulan[$i])) $total_transfer_per_bulan[$i] = 0;
                                                $total_transfer_per_bulan[$i] += $jumlah;
                                            }

                                            if (
                                                stripos($data_pembayaran['pembayaran_via'], 'koordinator') !== false
                                                || stripos($data_pembayaran['pembayaran_via'], 'koor') !== false
                                            ) {
                                                if (!isset($total_koordinator_per_bulan[$i])) $total_koordinator_per_bulan[$i] = 0;
                                                $total_koordinator_per_bulan[$i] += $jumlah;
                                            }
                                        }
                                        ?>

                                        <?php if ($currentMonthStr == $bulan_mulai_pembayaran): ?>
                                            <div class="status-cell status-verified">
                                                <strong><?= number_format($data_pembayaran['jumlah_bayar']); ?></strong>
                                                <span class="status-date"><?= date('d/m/y', strtotime($data_pembayaran['tanggal_bayar'])); ?></span>
                                            </div>
                                        <?php elseif ($is_bayar_dimuka): ?>
                                            <div class="status-cell status-dimuka"><span>Dimuka</span></div>
                                        <?php elseif ($is_rapel): ?>
                                            <div class="status-cell status-rapel"><span>Rapel</span></div>
                                        <?php endif; ?>

                                    <?php elseif ($data_pembayaran['status'] == 'pending'): ?>
                                        <div class="status-cell status-pending"><span>Pending</span></div>
                                    <?php else: ?>
                                        <div class="status-cell status-unpaid"><span>Belum</span></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="status-cell status-unpaid"><span>Belum</span></div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>

                <!-- Total Pemasukan -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; width: 32%;">Total Pemasukan</td>
                    <?php
                    $grand_total = 0;
                    for ($i = $bulan_mulai; $i <= $bulan_terakhir; $i++):
                        $total = $total_per_bulan[$i] ?? 0;
                        $grand_total += $total;
                    ?>
                        <td style="width: <?= 50 / $kolom_bulan ?>%; text-align: right;">
                            <?= $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : '-' ?>
                        </td>
                    <?php endfor; ?>
                </tr>

                <!-- Total Transfer -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; width: 32%;">Total Transfer</td>
                    <?php
                    $grand_total_transfer = 0;
                    for ($i = $bulan_mulai; $i <= $bulan_terakhir; $i++):
                        $total = $total_transfer_per_bulan[$i] ?? 0;
                        $grand_total_transfer += $total;
                    ?>
                        <td style="width: <?= 50 / $kolom_bulan ?>%; text-align: right;">
                            <?= $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : '-' ?>
                        </td>
                    <?php endfor; ?>
                </tr>

                <!-- Total Koordinator -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; width: 32%;">Total Bayar di Koordinator</td>
                    <?php
                    $grand_total_koor = 0;
                    for ($i = $bulan_mulai; $i <= $bulan_terakhir; $i++):
                        $total = $total_koordinator_per_bulan[$i] ?? 0;
                        $grand_total_koor += $total;
                    ?>
                        <td style="width: <?= 50 / $kolom_bulan ?>%; text-align: right;">
                            <?= $total > 0 ? 'Rp ' . number_format($total, 0, ',', '.') : '-' ?>
                        </td>
                    <?php endfor; ?>
                </tr>

                <!-- Grand Total -->

            </tbody>

        </table>
    </div>
</body>

</html>