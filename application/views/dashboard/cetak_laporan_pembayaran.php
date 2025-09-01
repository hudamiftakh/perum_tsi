<?php header('Content-Type: text/html; charset=UTF-8'); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pembayaran IPL - Paguyuban Taman Sukodono Indah</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }

        @page {
            margin: 20mm;
        }

        /* Using a table for the header layout as TCPDF doesn't support flexbox */
        .header-table {
            width: 100%;
            border: none;
            /* Changed here to remove all borders */
            margin-bottom: 20px;
        }

        .header-table td {
            vertical-align: top;
            padding-bottom: 10px;
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
            font-size: 12pt;
        }

        .logo {
            height: 50px;
            margin-right: 5px;
            border: none;
        }

        .report-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 20px 0 10px 0;
        }

        .info-section {
            font-size: 9pt;
            margin-bottom: 15px;
        }

        .info-section p {
            margin: 2px 0;
        }

        .table-container {
            margin-top: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: middle;
            text-align: center;
        }

        table thead th {
            background-color: #0070c0;
            color: #fff;
            text-align: center;
            white-space: nowrap;
        }

        .nama-rumah {
            text-align: left;
            font-weight: bold;
        }

        /* Simplified status styles */
        .status-cell {
            padding: 4px;
            font-weight: bold;
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
            font-size: 7pt;
            display: block;
            margin-top: 2px;
        }

        .total-row td {
            background-color: #f2f2f2;
            font-weight: bold;
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
        <?php echo @$koordinator['nama'];?>
    </h1>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 5%; height: 18px; font-weight: bold;">No</th>
                    <th style="width: 25%; font-weight: bold;">Nama</th>
                    <th style="width: 20%; font-weight: bold;">Rumah</th>
                    <?php
                    $tahun_terpilih = $this->input->get('tahun') ?: date('Y');
                    $bulan_terakhir = ($tahun_terpilih == date('Y')) ? date('n') : 12;
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
                <?php $no = 1;
                foreach ($rumah as $data_bulanan): ?>
                    <tr>
                        <td style="width: 5%;"><?= $no++; ?></td>
                        <td class="nama-rumah" nowrap="" style="width: 25%;"><?= strtoupper(htmlspecialchars($data_bulanan['nama'])); ?></td>
                        <td style="width: 20%;"><?= htmlspecialchars($data_bulanan['alamat']); ?></td>
                        <?php for ($i = 6; $i <= $bulan_terakhir; $i++):
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
                                ORDER BY a.status DESC
                            ")->row_array();
                        ?>
                            <td>
                                <?php if ($data_pembayaran): ?>
                                    <?php if ($data_pembayaran['status'] == 'verified'): ?>
                                        <?php
                                        $bulan_mulai_pembayaran = date('Y-m', strtotime($data_pembayaran['bulan_mulai']));
                                        $bulan_untuk = date('Y-m', strtotime($data_pembayaran['untuk_bulan']));
                                        $is_bayar_dimuka = ($bulan_mulai_pembayaran < $bulan_untuk);
                                        $is_rapel = ($data_pembayaran['metode'] !== '1_bulan');
                                        ?>
                                        <?php if ($currentMonthStr == $bulan_mulai_pembayaran): ?>
                                            <div class="status-cell status-verified">
                                                <strong><?= number_format($data_pembayaran['jumlah_bayar']); ?></strong>
                                                <span class="status-date"><?= date('d/m/y', strtotime($data_pembayaran['tanggal_bayar'])); ?></span>
                                            </div>
                                        <?php elseif ($is_bayar_dimuka): ?>
                                            <div class="status-cell status-dimuka">
                                                <span>Dimuka</span>
                                            </div>
                                        <?php elseif ($is_rapel): ?>
                                            <div class="status-cell status-rapel">
                                                <span>Rapel</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($data_pembayaran['status'] == 'pending'): ?>
                                        <div class="status-cell status-pending">
                                            <span>Pending</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-cell status-unpaid">
                                            <span>Belum Bayar</span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="status-cell status-unpaid">
                                        <span>Belum Bayar</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endforeach; ?>

                <tr class="total-row">
                    <td colspan="3" style="text-align: right;">Total Pemasukan</td>
                    <?php
                    // Hitung total pembayaran per bulan sesuai filter
                    $total_per_bulan = [];
                    for ($i = 1; $i <= $bulan_terakhir; $i++) {
                        $total_per_bulan[$i] = 0;
                    }
                    foreach ($rumah as $data_bulanan) {
                        for ($i = 1; $i <= $bulan_terakhir; $i++) {
                            $data_pembayaran = $this->db->query("
                        SELECT * FROM master_pembayaran as a 
                        LEFT JOIN master_users as b ON a.user_id = b.id
                        WHERE MONTH(a.bulan_mulai)='$i'
                        AND YEAR(a.bulan_mulai)='$tahun_terpilih'
                        AND b.id_rumah='" . $data_bulanan['id'] . "'
                        AND a.status = 'verified'
                    ")->row_array();
                            if ($data_pembayaran) {
                                $total_per_bulan[$i] += $data_pembayaran['jumlah_bayar'];
                            }
                        }
                    }
                    ?>
                    <?php for ($i = $bulan_mulai; $i <= $bulan_terakhir; $i++): ?>
                        <td><?= $total_per_bulan[$i] > 0 ? number_format($total_per_bulan[$i]) : '-' ?></td>
                    <?php endfor; ?>

                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>