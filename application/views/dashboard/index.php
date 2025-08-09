
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="card border-bottom border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h2 class="fs-7">
                                <?php
                                // echo $this->db->get("master_pegawai")->num_rows();
                                ?>
                            </h2>
                            <h6 class="fw-medium text-info mb-0">TOTAL RUMAH</h6>
                        </div>
                        <div class="ms-auto">
                            <span class="text-info display-6"><i class="ti ti-file-text"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-bottom border-primary">
                <div class="card-body">
                    <div class="d-flex no-block align-items-center">
                        <div>
                            <h2 class="fs-7">
                                <?php
                                // echo $this->db->query("
                                // SELECT 
                                // 	b.*, 
                                // 	a.*, 
                                // 	b.nama as pegawai,
                                // 	b.nik as nik_pegawai,
                                // 	ROW_NUMBER() OVER (ORDER BY a.total DESC) AS ranking
                                // FROM 
                                // 	master_pegawai AS b
                                // LEFT JOIN 
                                // 	report AS a ON a.nik = b.nik
                                //  WHERE 
                                // a.total > 1
                                // ORDER BY 
                                // 	ranking
                                // ")->num_rows();
                                // Hitung jumlah rumah yang sudah membayar IPL (status pembayaran sudah diverifikasi) per bulan
                                // Cek bulan ini, jika kosong cek bulan sebelumnya, jika kosong cari bulan yang ada
                                $bulan = date('m');
                                $tahun = date('Y');

                                // Fungsi untuk mendapatkan bulan dan tahun terakhir yang ada data pembayaran
                                function get_last_payment_month($db) {
                                    $db->select('MONTH(tanggal_bayar) as bulan, YEAR(tanggal_bayar) as tahun');
                                    $db->where('status', 'verified');
                                    $db->order_by('tahun', 'DESC');
                                    $db->order_by('bulan', 'DESC');
                                    $db->limit(1);
                                    $result = $db->get('master_pembayaran')->row();
                                    if ($result) {
                                        return [$result->bulan, $result->tahun];
                                    }
                                    return [date('m'), date('Y')];
                                }

                                // Cek data bulan ini
                                $this->db->where('status', 'verified');
                                $this->db->where('MONTH(tanggal_bayar)', $bulan);
                                $this->db->where('YEAR(tanggal_bayar)', $tahun);
                                $this->db->select('id_rumah');
                                $this->db->group_by('id_rumah');
                                $count = $this->db->get('master_pembayaran')->num_rows();

                                if ($count == 0) {
                                    // Cek bulan sebelumnya
                                    $bulan_sebelumnya = $bulan - 1;
                                    $tahun_sebelumnya = $tahun;
                                    if ($bulan_sebelumnya == 0) {
                                        $bulan_sebelumnya = 12;
                                        $tahun_sebelumnya = $tahun - 1;
                                    }
                                    $this->db->where('status', 'verified');
                                    $this->db->where('MONTH(tanggal_bayar)', $bulan_sebelumnya);
                                    $this->db->where('YEAR(tanggal_bayar)', $tahun_sebelumnya);
                                    $this->db->select('id_rumah');
                                    $this->db->group_by('id_rumah');
                                    $count = $this->db->get('master_pembayaran')->num_rows();

                                    if ($count == 0) {
                                        // Cari bulan terakhir yang ada data
                                        list($bulan_ada, $tahun_ada) = get_last_payment_month($this->db);
                                        $this->db->where('status', 'verified');
                                        $this->db->where('MONTH(tanggal_bayar)', $bulan_ada);
                                        $this->db->where('YEAR(tanggal_bayar)', $tahun_ada);
                                        $this->db->select('id_rumah');
                                        $this->db->group_by('id_rumah');
                                        $count = $this->db->get('master_pembayaran')->num_rows();
                                    }
                                }
                                echo $count;
                                ?>
                            </h2>
                            <h6 class="fw-medium text-primary mb-0">TOTAL RUMAH BAYAR IPL</h6>
                        </div>
                        <div class="ms-auto">
                            <span class="text-primary display-6"><i class="ti ti-users"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-bottom border-success">
                <div class="card-body">
                    <div class="d-flex no-block align-items-center">
                        <div>
                            <h2 class="fs-7">
                                <?php
                                // Hitung total IPL terkumpul
                                $this->db->select_sum('jumlah_bayar');
                                $this->db->where('status', 'verified');
                                $total_ipl = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
                                echo number_format($total_ipl, 0, ',', '.');
                                ?>
                            </h2>
                            <h6 class="fw-medium text-success mb-0">IPL TERKUMPUL</h6>
                        </div>
                        <div class="ms-auto">
                            <span class="text-success display-6"><i class="ti ti-phone"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-bottom border-danger">
                <div class="card-body">
                    <div class="d-flex no-block align-items-center">
                        <div>
                            <h2 class="fs-7">
                                
                            </h2>
                            <h6 class="fw-medium text-danger mb-0">TOTAL SALDO</h6>
                        </div>
                        <div class="ms-auto">
                            <span class="text-danger display-6"><i class="ti ti-send"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
    <style>
        body {
            background-color: #f0f2f5;
        }

        .profile-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            padding: 30px 20px;
            text-align: center;
        }

        .profile-img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #198754;
            margin-bottom: 15px;
        }

        .role-badge {
            font-size: 0.9rem;
            background-color: #e6ffed;
            color: #198754;
            border-radius: 12px;
            padding: 6px 12px;
            display: inline-block;
            margin-top: 10px;
        }

        .welcome-msg {
            font-weight: 500;
            font-size: 1.1rem;
            margin-top: 15px;
            color: #333;
        }
    </style>
    <div class="container d-flex justify-content-center align-items-center">
        <div class="profile-card w-100" style="max-width: 400px;">
            <img src="<?php echo base_url(); ?>logo-tsi-removebg-preview.png" alt="Foto Karikatur" class="profile-img">
            <h4 class="mb-0"><?php echo $_SESSION['username']['nama']; ?></h4>
            <div class="role-badge">
                <i class="bi bi-person-badge"></i> Koordinator Blok
            </div>
            <p class="welcome-msg">
                Selamat datang di <br><strong>APLIKASI MANAJEMEN PERUMAHAN TSI</strong><br>
                Terima kasih telah menjadi bagian dari paguyuban kami.
            </p>
            <br>
            <a class="btn btn-success w-100 d-flex flex-column justify-content-center align-items-center gap-2 p-3 rounded-2 shadow-sm" 
                href="<?= base_url('pendataan-keluarga'); ?>" style="transition: 0.2s;">
                    
                    <i class="fa fa-users fs-10 text-white"></i>

                    <div class="text-center">
                        <div class="fw-bold fs-5 text-white">Pendataan Warga</div>
                        <small class="text-white-50">Kelola data keluarga & anggota</small>
                    </div>
            </a>
        </div>
    </div>
<?php endif; ?>
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
    <style>
        .card-header.bg-primary,
        .btn-success,
        .table thead th,
        .role-badge {
            background-color: #008d4c !important;
            border-color: #008d4c !important;
        }
        .btn-success {
            color: #fff !important;
        }
        .table thead th {
            color: #fff;
        }
        .filter-bar {
            background: #e6f7ee;
            border-radius: 14px;
            padding: 18px 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,141,76,0.08);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .export-btns .btn {
            margin-right: 10px;
        }
        .dashboard-header {
            background: linear-gradient(90deg, #008d4c 60%, #00b86b 100%);
            color: #fff;
            border-radius: 18px;
            padding: 32px 18px 24px 18px;
            margin-bottom: 28px;
            box-shadow: 0 4px 18px rgba(0,141,76,0.13);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .dashboard-header h2 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }
        .dashboard-header p {
            font-size: 1.1rem;
            margin-bottom: 0;
            opacity: 0.93;
        }
        .dashboard-header .header-icon {
            position: absolute;
            right: 24px;
            top: 24px;
            font-size: 3.5rem;
            opacity: 0.13;
        }
        @media (max-width: 767.98px) {
            .dashboard-header {
                padding: 22px 8px 18px 8px;
                font-size: 1rem;
            }
            .dashboard-header h2 {
                font-size: 1.2rem;
            }
            .dashboard-header .header-icon {
                font-size: 2.2rem;
                right: 10px;
                top: 10px;
            }
            .filter-bar {
                flex-direction: column;
                align-items: stretch;
                gap: 0.7rem;
                padding: 12px 6px;
            }
            .export-btns {
                justify-content: flex-start !important;
            }
            .table-responsive {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 575.98px) {
            .dashboard-header {
                padding: 12px 4px 10px 4px;
            }
            .filter-bar {
                padding: 8px 2px;
            }
            .table-responsive {
                font-size: 0.89rem;
            }
        }
    </style>
    <div class="dashboard-header mb-4">
        <h2 style="color: #fff; font-weight: 600;">
            <i class="fa fa-home me-2"></i>
            Statistik IPL Perumahan TSI
        </h2>
        <p>
            Pantau pembayaran IPL, filter data sesuai periode, dan ekspor laporan dengan mudah.
        </p>
        <span class="header-icon">
            <i class="fa fa-bar-chart"></i>
        </span>
    </div>
    <div class="filter-bar flex-column flex-md-row d-flex align-items-stretch align-items-md-center justify-content-between gap-3 gap-md-0" style="background: linear-gradient(90deg, #e6f7ee 70%, #d2f4e3 100%); box-shadow: 0 2px 10px rgba(0,141,76,0.09); border-radius: 16px; padding: 22px 18px; margin-bottom: 28px;">
        <form class="d-flex flex-wrap gap-2 align-items-center" method="get" id="filterForm" style="margin-bottom:0;">
            <label class="mb-0 fw-bold text-success" for="start_date" style="font-size:1.05rem;">
                <i class="fa fa-calendar-alt me-1"></i> Periode:
            </label>
            <input type="date" class="form-control border-success" name="start_date" id="start_date" value="<?= isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); ?>" style="min-width:140px;">
            <span class="mx-2 fw-bold text-secondary">s/d</span>
            <input type="date" class="form-control border-success" name="end_date" id="end_date" value="<?= isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>" style="min-width:140px;">
            <button type="submit" class="btn btn-success d-flex align-items-center px-3 ms-2" style="font-weight:500;">
                <i class="fa fa-filter me-1"></i> Filter
            </button>
        </form>
        <div class="export-btns d-flex align-items-center gap-2 mt-3 mt-md-0">
            <button class="btn btn-outline-success d-flex align-items-center px-3" id="exportExcel" style="font-weight:500;">
                <i class="fa fa-file-excel me-1"></i> Excel
            </button>
            <button class="btn btn-outline-success d-flex align-items-center px-3" id="exportImage" style="font-weight:500;">
                <i class="fa fa-image me-1"></i> Image
            </button>
        </div>
    </div>
    <style>
        .filter-bar input[type="date"]::-webkit-input-placeholder { color: #198754; }
        .filter-bar input[type="date"]::-moz-placeholder { color: #198754; }
        .filter-bar input[type="date"]:-ms-input-placeholder { color: #198754; }
        .filter-bar input[type="date"]::placeholder { color: #198754; }
        .filter-bar input[type="date"]:focus {
            border-color: #00b86b;
            box-shadow: 0 0 0 0.15rem rgba(0,184,107,.15);
        }
        .filter-bar label {
            letter-spacing: 0.5px;
        }
        .export-btns .btn {
            transition: background 0.18s, color 0.18s;
        }
        .export-btns .btn:hover {
            background: #00b86b !important;
            color: #fff !important;
            border-color: #00b86b !important;
        }
        @media (max-width: 767.98px) {
            .filter-bar {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 1rem !important;
                padding: 14px 8px !important;
            }
            .export-btns {
                justify-content: flex-start !important;
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        // Export Excel
        document.getElementById('exportExcel').onclick = function() {
            var table = document.querySelector('.table');
            var wb = XLSX.utils.table_to_book(table, {sheet:"Statistik IPL"});
            XLSX.writeFile(wb, 'statistik_ipl.xlsx');
        };
        // Export Image
        document.getElementById('exportImage').onclick = function() {
            html2canvas(document.querySelector('.card-body')).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'statistik_ipl.png';
                link.href = canvas.toDataURL();
                link.click();
            });
        };
        // Filter tanggal reload page
        document.getElementById('filterForm').onsubmit = function() {
            // Let form submit normally (GET)
        };
    </script>
    <?php
    // Filter tanggal PHP
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $statistik = [];
    $period = new DatePeriod(
        new DateTime($start_date),
        new DateInterval('P1M'),
        (new DateTime($end_date))->modify('+1 month')
    );
    foreach ($period as $dt) {
        $bulan = $dt->format('n');
        $tahun = $dt->format('Y');
        $this->db->where('status', 'verified');
        $this->db->where('MONTH(tanggal_bayar)', $bulan);
        $this->db->where('YEAR(tanggal_bayar)', $tahun);
        $this->db->where('tanggal_bayar >=', $start_date);
        $this->db->where('tanggal_bayar <=', $end_date);
        $this->db->select_sum('jumlah_bayar');
        $total = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
        $statistik[] = [
            'bulan' => $dt->format('M Y'),
            'total' => $total ? $total : 0
        ];
    }
    ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex flex-wrap align-items-center justify-content-between">
                    <span class="fw-bold fs-5"><i class="fa fa-calendar-check me-2"></i>Statistik Pembayaran IPL Per Bulan</span>
                    <span class="d-none d-md-inline text-white-50 fs-6">Periode: <?= date('d M Y', strtotime($start_date)); ?> - <?= date('d M Y', strtotime($end_date)); ?></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-striped align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <th><?= $s['bulan']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <td>
                                            <span class="fw-bold text-success">
                                                Rp <?= number_format($s['total'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <canvas id="statistikChartFilter" height="80"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx2 = document.getElementById('statistikChartFilter').getContext('2d');
                        new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($statistik, 'bulan')); ?>,
                                datasets: [{
                                    label: 'Total IPL (Rp)',
                                    data: <?= json_encode(array_column($statistik, 'total')); ?>,
                                    backgroundColor: 'rgba(0,141,76,0.7)',
                                    borderColor: 'rgba(0,141,76,1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if ($_SESSION['username']['role'] == 'admin') : ?>
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Statistik Pembayaran IPL Per Bulan</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Ambil data 12 bulan terakhir
                    $bulan_ini = date('n');
                    $tahun_ini = date('Y');
                    $statistik = [];
                    for ($i = 11; $i >= 0; $i--) {
                        $bulan = $bulan_ini - $i;
                        $tahun = $tahun_ini;
                        if ($bulan <= 0) {
                            $bulan += 12;
                            $tahun -= 1;
                        }
                        $this->db->where('status', 'verified');
                        $this->db->where('MONTH(tanggal_bayar)', $bulan);
                        $this->db->where('YEAR(tanggal_bayar)', $tahun);
                        $this->db->select_sum('jumlah_bayar');
                        $total = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
                        $statistik[] = [
                            'bulan' => date('M Y', strtotime("$tahun-$bulan-01")),
                            'total' => $total ? $total : 0
                        ];
                    }
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle text-center">
                            <thead class="table-light">
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <th><?= $s['bulan']; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php foreach ($statistik as $s) : ?>
                                        <td>
                                            <span class="fw-bold text-success">
                                                Rp <?= number_format($s['total'], 0, ',', '.'); ?>
                                            </span>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Optional: Chart.js for better visualization -->
                    <canvas id="statistikChart" height="80"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        const ctx = document.getElementById('statistikChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?= json_encode(array_column($statistik, 'bulan')); ?>,
                                datasets: [{
                                    label: 'Total IPL (Rp)',
                                    data: <?= json_encode(array_column($statistik, 'total')); ?>,
                                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                    borderColor: 'rgba(13, 110, 253, 1)',
                                    borderWidth: 1,
                                    borderRadius: 6,
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return 'Rp ' + value.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($_SESSION['username']['role'] == 'bendahara') : ?>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }
        .bendahara-dashboard {
            max-width: 1100px;
            margin: 0 auto;
            padding: 25px 20px;
        }
        .bendahara-header {
            background: linear-gradient(135deg, #009f60, #00c77c);
            color: #fff;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: 0 4px 15px rgba(0, 159, 96, 0.25);
            text-align: center;
            position: relative;
        }
        .bendahara-header h2 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 8px;
        }
        .bendahara-header p {
            font-size: 1.05rem;
            margin-bottom: 0;
            opacity: 0.95;
        }
        .header-icon {
            position: absolute;
            right: 28px;
            top: 28px;
            font-size: 4rem;
            opacity: 0.12;
        }
        .bendahara-card {
            border-radius: 14px;
            background: #fff;
            border: none;
            transition: all 0.25s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .bendahara-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .card-body {
            padding: 22px 18px;
        }
        .card-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 8px;
            color: #555;
        }
        .card-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #009f60;
        }
        .card-icon {
            font-size: 2.4rem;
            margin-right: 14px;
            border-radius: 50%;
            padding: 10px;
            color: #fff;
        }
        .icon-green { background-color: #00c77c; }
        .icon-blue { background-color: #007bff; }
        .icon-yellow { background-color: #ffc107; }
        .icon-purple { background-color: #6f42c1; }
        table th {
            font-weight: 600;
        }
        .btn-custom {
            border-radius: 8px;
            font-weight: 500;
        }
        @media (max-width: 768px) {
            .bendahara-header {
                padding: 20px;
            }
            .bendahara-header h2 {
                font-size: 1.4rem;
            }
        }
    </style>

    <div class="bendahara-dashboard mt-4">
        <!-- Header -->
        <div class="bendahara-header mb-4">
            <h2>
                <i class="fa fa-money-bill-wave me-2"></i>
                Dashboard Bendahara IPL TSI
            </h2>
            <p>
                Selamat datang, <b><?= $_SESSION['username']['nama']; ?></b>.<br>
                Kelola pembayaran IPL, pantau saldo, dan ekspor laporan dengan mudah.
            </p>
            <span class="header-icon">
                <i class="fa fa-wallet"></i>
            </span>
        </div>

        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card bendahara-card">
                    <div class="card-body d-flex align-items-center">
                        <span class="card-icon icon-green"><i class="fa fa-home"></i></span>
                        <div>
                            <div class="card-title">Total Rumah</div>
                            <div class="card-value">
                                <?php
                                $total_rumah = $this->db->get('master_rumah')->num_rows();
                                echo $total_rumah;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bendahara-card">
                    <div class="card-body d-flex align-items-center">
                        <span class="card-icon icon-blue"><i class="fa fa-check-circle"></i></span>
                        <div>
                            <div class="card-title">Bayar Bulan Ini</div>
                            <div class="card-value">
                                <?php
                                $bulan = date('m');
                                $tahun = date('Y');
                                $this->db->where('status', 'verified');
                                $this->db->where('MONTH(tanggal_bayar)', $bulan);
                                $this->db->where('YEAR(tanggal_bayar)', $tahun);
                                $this->db->select('id_rumah');
                                $this->db->group_by('id_rumah');
                                $rumah_bayar = $this->db->get('master_pembayaran')->num_rows();
                                echo $rumah_bayar;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card bendahara-card">
                    <div class="card-body d-flex align-items-center">
                        <span class="card-icon icon-yellow"><i class="fa fa-dollar"></i></span>
                        <div>
                            <div class="card-title">Total Saldo IPL</div>
                            <div class="card-value">
                                <?php
                                $this->db->select_sum('jumlah_bayar');
                                $this->db->where('status', 'verified');
                                $total_saldo = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
                                echo 'Rp ' . number_format($total_saldo, 0, ',', '.');
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik Chart -->
        <div class="card bendahara-card mb-4">
            <div class="card-body">
                <div class="card-title mb-2"><i class="fa fa-chart-bar me-2"></i>Statistik 6 Bulan Terakhir</div>
                <?php
                $bulan_ini = date('n');
                $tahun_ini = date('Y');
                $statistik = [];
                for ($i = 5; $i >= 0; $i--) {
                    $bulan = $bulan_ini - $i;
                    $tahun = $tahun_ini;
                    if ($bulan <= 0) {
                        $bulan += 12;
                        $tahun -= 1;
                    }
                    $this->db->where('status', 'verified');
                    $this->db->where('MONTH(tanggal_bayar)', $bulan);
                    $this->db->where('YEAR(tanggal_bayar)', $tahun);
                    $this->db->select_sum('jumlah_bayar');
                    $total = $this->db->get('master_pembayaran')->row()->jumlah_bayar;
                    $statistik[] = [
                        'bulan' => date('M Y', strtotime("$tahun-$bulan-01")),
                        'total' => $total ? $total : 0
                    ];
                }
                ?>
                <canvas id="bendaharaChart" height="80"></canvas>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctxB = document.getElementById('bendaharaChart').getContext('2d');
                    new Chart(ctxB, {
                        type: 'bar',
                        data: {
                            labels: <?= json_encode(array_column($statistik, 'bulan')); ?>,
                            datasets: [{
                                label: 'Total IPL (Rp)',
                                data: <?= json_encode(array_column($statistik, 'total')); ?>,
                                backgroundColor: 'rgba(0, 159, 96, 0.7)',
                                borderColor: 'rgba(0, 159, 96, 1)',
                                borderWidth: 1,
                                borderRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                </script>
            </div>
        </div>

        <!-- Ekspor -->
        <div class="card bendahara-card">
            <div class="card-body">
                <div class="card-title mb-2"><i class="fa fa-file-export me-2"></i>Ekspor Laporan IPL</div>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= base_url('laporan-ipl/excel'); ?>" class="btn btn-outline-success btn-custom d-flex align-items-center px-3">
                        <i class="fa fa-file-excel me-1"></i> Excel
                    </a>
                    <a href="<?= base_url('laporan-ipl/pdf'); ?>" class="btn btn-outline-danger btn-custom d-flex align-items-center px-3">
                        <i class="fa fa-file-pdf me-1"></i> PDF
                    </a>
                </div>
                <small class="text-muted d-block mt-2">Ekspor data pembayaran IPL sesuai kebutuhan bendahara.</small>
            </div>
        </div>
    </div>
<?php endif; ?>
