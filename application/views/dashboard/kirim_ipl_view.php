<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kirim Notifikasi IPL - Perumahan</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
        }
        .header-logo {
            height: 60px;
            margin-right: 15px;
        }
        .card-custom {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .log-box {
            max-height: 400px;
            overflow-y: auto;
            background: #ffffff;
            border: 1px solid #dee2e6;
            padding: 10px;
            font-size: 14px;
        }
        .table-sm th, .table-sm td {
            font-size: 13px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <img src="<?= base_url('logo-tsi-removebg-preview.png') ?>" alt="Logo" class="header-logo">
        <div>
            <h4 class="mb-0">Sistem Notifikasi IPL</h4>
            <small class="text-muted">Perumahan Taman Sukodono Indah</small>
        </div>
    </div>

    <!-- Card Main -->
    <div class="card card-custom mb-4">
        <div class="card-body">
            <h5 class="card-title">🔔 Kirim Notifikasi WA IPL</h5>
            <p class="card-text">Tekan tombol di bawah ini untuk mengirim pesan pengingat IPL bulan ini ke seluruh warga.</p>
            <button id="kirimBatch" class="btn btn-success mb-3">Kirim Notifikasi</button>

            <!-- Progress -->
            <div class="progress mb-3" style="height: 25px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" style="width: 0%">0%</div>
            </div>

            <!-- Hasil Kirim -->
            <div id="hasilKirim" class="log-box"></div>
        </div>
    </div>

    <!-- Log -->
    <div class="card card-custom">
        <div class="card-header">
            <h6 class="mb-0">📄 Log Pengiriman Terbaru</h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>No HP</th>
                        <th>Status</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= $log->nama ?></td>
                                <td><?= $log->alamat ?></td>
                                <td><?= $log->no_hp ?></td>
                                <td>
                                    <span class="badge badge-<?= $log->status == 'Berhasil' ? 'success' : 'danger' ?>">
                                        <?= $log->status ?>
                                    </span>
                                </td>
                                <td><?= date('d-m-Y H:i', strtotime($log->created_at)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Belum ada log pengiriman.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
                    <!-- Pagination Links -->
            <div class="mt-3">
                <?= $pagination ?>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#kirimBatch').click(function () {
    if (!confirm("Yakin ingin mengirim notifikasi WA ke semua warga?")) return;

    $('#kirimBatch').prop('disabled', true).text('Mengirim...');
    $('#hasilKirim').html('');
    $('#progressBar').css('width', '0%').text('0%');

    $.ajax({
        url: '<?= base_url('dashboard/ajaxSendWaBatch') ?>',
        type: 'POST',
        dataType: 'json',
        success: function (res) {
            if (res.status === 'done') {
                let total = res.total;
                let success = 0;

                res.data.forEach(function (item, i) {
                    let percent = Math.round(((i + 1) / total) * 100);
                    let statusClass = item.status === 'Berhasil' ? 'text-success' : 'text-danger';
                    if (item.status === 'Berhasil') success++;

                    $('#hasilKirim').append(`<div class="${statusClass}">[${item.index}/${total}] ${item.nama} - ${item.alamat} : ${item.status}</div>`);
                    $('#progressBar').css('width', percent + '%').text(percent + '%');
                });

                $('#hasilKirim').prepend(`<div class="alert alert-info">✅ Selesai: ${success} berhasil dari ${total}.</div>`);
                setTimeout(() => location.reload(), 2000);
            }
        },
        error: function () {
            $('#hasilKirim').html('<div class="alert alert-danger">❌ Terjadi kesalahan saat mengirim. Coba lagi nanti.</div>');
        },
        complete: function () {
            $('#kirimBatch').prop('disabled', false).text('Kirim Notifikasi');
        }
    });
});
</script>

</body>
</html>
