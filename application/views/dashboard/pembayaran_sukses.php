<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Konfirmasi Pembayaran IPL</title>
  <link href="<?php echo base_url(); ?>dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #e0f7fa, #ffffff);
      padding: 40px 0;
    }

    .card {
      border-radius: 20px;
      border: none;
    }

    .card-header-custom {
      background-color: #e6fff1;
      padding: 30px;
      border-radius: 20px 20px 0 0;
    }

    .check-icon {
      font-size: 80px;
      color: #28a745;
    }

    .text-muted {
      font-size: 1.05rem;
    }

    .bukti-info {
      background-color: #f8f9fa;
      border-left: 5px solid #0d6efd;
      border-radius: 12px;
      padding: 15px 20px;
    }

    .btn-primary {
      background-color: #198754;
      border: none;
    }

    .btn-primary:hover {
      background-color: #157347;
    }

    .btn-outline-success {
      border-radius: 8px;
    }
  </style>
</head>
<?php
// URL-encoded JSON string
$data = $_REQUEST['data'];
// Langkah 1: Ambil hanya bagian setelah `data=`
parse_str($data, $parsed);
// Langkah 2: Decode JSON-nya
$jsonString = $data;
$jsonData = json_decode($jsonString, true); // true = associative array
?>

<style>
  .info-grid {
    display: grid;
    grid-template-columns: max-content 1fr;
    row-gap: 0.5rem;
    column-gap: 0.5rem;
  }
</style>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg">
          <div class="card-header-custom text-center">
            <div class="check-icon mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="currentColor"
                class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                <path
                  d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 11.03a.75.75 0 0 0 1.08 0l4.992-4.992a.75.75 0 1 0-1.08-1.04L7.5 9.477 5.523 7.5a.75.75 0 0 0-1.06 1.06l2.507 2.47z" />
              </svg>
            </div>
            <h3 class="fw-bold text-success">Konfirmasi Pembayaran Berhasil!</h3>
            <p class="text-muted">Terima kasih, Konfirmasi pembayaran IPL Anda telah diterima dengan baik.</p>
          </div>
          <div class="card-body">
          <div class="bukti-info mb-4 info-grid">
            <div><strong>Nama</strong></div><div>: Budi</div>
            <div><strong>Periode</strong></div><div>: Januari - Maret 2025</div>
            <div><strong>Jumlah Dibayar</strong></div><div>: Rp375.000</div>
            <div><strong>Metode</strong></div><div>: Transfer ke Bendahara</div>
            <div><strong>Tanggal</strong></div><div>: 19 Mei 2025</div>
          </div>
            <div class="d-flex justify-content-between">
              <a href="<?php echo base_url('pembayaran'); ?>" class="btn btn-outline-success">Kembali ke Beranda</a>
              <button class="btn btn-primary" onclick="window.print()">Cetak Bukti</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>