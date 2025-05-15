<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Daftar Hadir - Jotform Style</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f4f6f8;
      font-family: 'Segoe UI', sans-serif;
    }

    .form-container {
      max-width: 600px;
      margin: 40px auto;
      background: white;
      border: 2px solid #dee2e6;
      border-radius: 12px;
      padding: 30px 40px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
    }

    .form-title {
      font-weight: 600;
      margin-bottom: 25px;
      color: #333;
    }

    .form-label {
      font-weight: 500;
    }

    .signature-box {
      border: 2px dashed #adb5bd;
      border-radius: 8px;
      background: #fff;
      padding: 10px;
      display: inline-block;
    }

    canvas {
      display: block;
      background: #fff;
      border-radius: 4px;
    }

    .btn-clear {
      font-size: 0.875rem;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="form-container">
    <h2 class="form-title text-center">Form Daftar Hadir</h2>
    <form method="POST" action="simpan.php" onsubmit="return prepareSignature()">
      <div class="mb-4">
        <label for="nama" class="form-label">Nama Lengkap</label>
        <input type="text" class="form-control" id="nama" name="nama" placeholder="Tuliskan nama lengkap Anda" required>
      </div>

      <div class="mb-4">
        <label class="form-label">Tanda Tangan Digital</label><br>
        <div class="signature-box">
          <canvas id="signature" width="500" height="150"></canvas>
        </div>
        <div class="mt-2">
          <button type="button" class="btn btn-outline-secondary btn-sm btn-clear" onclick="clearSignature()">Bersihkan Tanda Tangan</button>
        </div>
      </div>

      <input type="hidden" name="ttd" id="ttd">

      <button type="submit" class="btn btn-primary w-100">Kirim Kehadiran</button>
    </form>
  </div>
</div>

<script>
  const canvas = document.getElementById('signature');
  const ctx = canvas.getContext('2d');
  let drawing = false;

  canvas.addEventListener('mousedown', () => drawing = true);
  canvas.addEventListener('mouseup', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('mouseout', () => {
    drawing = false;
    ctx.beginPath();
  });
  canvas.addEventListener('mousemove', draw);

  function draw(e) {
    if (!drawing) return;
    const rect = canvas.getBoundingClientRect();
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#000';
    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
  }

  function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.beginPath();
  }

  function prepareSignature() {
    const ttdInput = document.getElementById('ttd');
    ttdInput.value = canvas.toDataURL();
    return true;
  }
</script>

</body>
</html>
