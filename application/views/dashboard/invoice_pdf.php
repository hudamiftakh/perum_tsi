<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Kitir Pembayaran - Paguyuban Taman Sukodono Indah</title>
     <style>
        body { font-family: helvetica, arial, sans-serif; font-size: 10pt; color: #000; }
        .company-name { background-color: #0070c0; color: #fff; font-weight: bold; font-size: 14pt; padding: 5pt; }
        .title { text-align: center; font-weight: bold; font-size: 12pt; background-color: #f2f2f2; padding: 4pt; border-bottom: 1pt solid #000; }
        .section-title { font-weight: bold; text-align: center; background-color: #f2f2f2; padding: 11pt; border: 0.5pt solid #ccc; }
        table { border-collapse: collapse; }
        td { padding: 3pt; vertical-align: top; }
        .header-table { width: 100%; }
        .label { width: 35%; }
        .separator { width: 5%; }
        .paid-status { font-weight: bold; }
        .total-line { font-weight: bold; font-size: 11pt; margin-bottom: 4pt; }
        .terbilang, .terbayar  { background-color: #e7e6e6; padding: 10pt; font-weight: bold; border: 0.5pt solid #a5a5a5; margin-bottom: 10pt; }
        .notes { font-size: 9pt; }
        .notes ol { padding-left: 14pt; margin: 0; }
         .watermark {
            position: fixed;
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 30pt;
            color: rgba(0, 128, 0, 0.15);
            font-weight: bold;
            z-index: 999;
            pointer-events: none;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <table class="header-table"  cellpadding="4" width="100%">
            <tr>
                <td style="width: 80mm; text-align: left; vertical-align: middle;">
                    <table style="width:100%;">
                        <tr>
                            <td style="width:50%; text-align:right;">
                                <img src="<?= base_url('logo_2-removebg-preview.png') ?>" style="width: 30mm; height: 20mm; object-fit: contain; vertical-align: middle;">
                            </td>
                            <td style="width:50%; text-align:left;">
                                <img src="<?= base_url('logo-tsi-removebg-preview.png') ?>" style="width: 30mm; height: 20mm; object-fit: contain; vertical-align: middle;">
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="padding-top: 1px;">
                   <label for="" style="font-size: large;"><b>PAGUYUBAN TAMAN SUKODONO INDAH</b></label>
                    <div>
                        <strong>Alamat:</strong> Jl. Taman Sukodono Indah, Kedung, Jumputrejo, Sukodono, Sidoarjo<br>
                        <strong>Telepon:</strong> 0852-1234-5678<br>
                        <strong>Email:</strong> tsipaguyuban@gmail.com
                    </div>
                </td>
            </tr>
        </table>
        <table width="100%">
            <tr>
                <td style="width: 100%; vertical-align: top;">
                    <div class="section-title"><br> E-KITIR PEMBAYARAN IPL <br></div> <br>
                    <?php
                    // Ambil id_rumah dan bulan_mulai dari GET
                    $id = decrypt_url($this->uri->segment(2));
                    if(!empty($id)) {
                        $this->db->where('id', $id);
                        $pembayaran = $this->db->get('master_pembayaran')->row_array();
                        if ($pembayaran) {
                            $id_rumah = $pembayaran['id_rumah'];
                            $bulan_mulai = $pembayaran['bulan_mulai'];
                        } else {
                            // Jika tidak ada pembayaran, ambil dari GET
                            $id_rumah = $this->input->get('id_rumah');
                            $bulan_mulai = $this->input->get('bulan');
                        }
                    } else {
                        // Jika id tidak valid, ambil dari GET
                        $id_rumah = $this->input->get('id_rumah');
                        $bulan_mulai = $this->input->get('bulan');

                    }
                    // Query pembayaran
                    $this->db->where('id_rumah', $id_rumah);
                    $this->db->where('bulan_mulai', $bulan_mulai);
                    $pembayaran = $this->db->get('master_pembayaran')->row_array();

                    // Query rumah
                    $this->db->where('id', $id_rumah);
                    $rumah = $this->db->get('master_rumah')->row_array();
                    ?>

                    <?php
                        // Ambil data bulan tagihan dari GET jika pembayaran belum ada
                        if ($pembayaran) {
                            $bulanAngka = (int)substr($pembayaran['bulan_mulai'], 5, 2);
                            $tahun = substr($pembayaran['bulan_mulai'], 0, 4);
                        } else {
                            $bulanAngka = (int)substr($bulan_mulai, 5, 2);
                            $tahun = substr($bulan_mulai, 0, 4);
                        }
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        $bulanTagihan = $namaBulan[$bulanAngka] . ' ' . $tahun;
                    ?>
                    <table class="details-table" style="width: 100%;">
                         <tr><td class="label">No. Pembayaran</td><td class="separator">:</td><td>INV/<?= htmlspecialchars($pembayaran['id'])."/".date('Y') ?>/</td> <td rowspan="8"><?php if ($pembayaran && strtolower($pembayaran['status']) === 'verified'): ?><div class="watermark">LUNAS</div><?php endif; ?></td></tr>
                        <tr>
                            <td class="label" style="padding-top: 10px;">Nama</td>
                            <td class="separator">:</td>
                            <td><?= htmlspecialchars($rumah['nama']) ?></td>
                        </tr>
                        <tr>
                            <td class="label">Nomor Rumah</td>
                            <td class="separator">:</td>
                            <td><?= htmlspecialchars($rumah['alamat'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td class="label">Bulan Tagihan</td>
                            <td class="separator">:</td>
                            <td><?= $bulanTagihan ?></td>
                        </tr>
                        <?php if ($pembayaran): ?>
                            <tr><td class="label">Jumlah Bayar</td><td class="separator">:</td><td>Rp <?= number_format($pembayaran['jumlah_bayar'], 2, ',', '.') ?></td></tr>
                            <tr><td class="label">Keterangan</td><td class="separator">:</td><td><?= htmlspecialchars($pembayaran['keterangan']) ?></td></tr>
                            <tr><td class="label">Tanggal Bayar</td><td class="separator">:</td><td><?= htmlspecialchars($pembayaran['tanggal_bayar']) ?></td></tr>
                            <tr><td class="label">Pembayaran Via</td><td class="separator">:</td><td><?= htmlspecialchars($pembayaran['pembayaran_via']) ?></td></tr>
                            <tr><td class="label">Alamat</td><td class="separator">:</td><td><?= htmlspecialchars($rumah['alamat']) ?></td></tr>
                             <tr>
                                <td class="label">Status</td>
                                <td class="separator">:</td>
                                <td><?php if (strtolower($pembayaran['status']) === 'verified'): ?><span style="color: green; font-weight: bold;">Sudah Bayar</span>
                                    <?php else: ?>
                                        <?= htmlspecialchars($pembayaran['status']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td class="label">Status</td>
                                <td class="separator">:</td>
                                <td style="color: red;"><strong>Belum Bayar</strong></td>
                            </tr>
                            <tr>
                                <td class="label">Keterangan</td>
                                <td class="separator">:</td>
                                <td>Data pembayaran belum tersedia untuk rumah dan bulan yang dipilih.</td>
                            </tr>
                            <tr>
                                <td class="label">Alamat</td>
                                <td class="separator">:</td>
                                <td><?= htmlspecialchars($rumah['alamat']) ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>
        </table>
        <div class="total-section">
            <div class="total-line"  style="margin: 10px; background-color: #e7e6e6; padding: 10pt; font-weight: bold; border: 0.5pt solid #a5a5a5; margin-bottom: 10pt;">
                Total Tagihan : Rp 125.000,00
            <br>
            Terbilang : Seratus Dua Puluh Lima Ribu Rupiah
            </div>
               <ol>
                <li>Iuran diatas sudah termasuk biaya administrasi</li>
                <li>Pembayaran dianggap sah apabila pembayaran di transfer lewat Bendahara Paguyuban (BCA 8221586107 / Dhani Kispananto) atau tunai pada koordinator Blok</li>
                <li>Pembayaran dianggap telah diterima dengan baik apabila dana telah di rekening paguyuban</li>
                <li>Pembayaran selambat-lambatnya pada tanggal 10 setiap bulan</li>
                <li>Simpanlah e-kitir ini sebagai bukti pembayaran</li>
                <li>Untuk pengajuan keluhan bisa menghubungi pengurus Paguyuban Taman Sukodono Indah</li>
            </ol>
        </div>
         
    </div>
</body>
</html>
