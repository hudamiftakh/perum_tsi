<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>OCR Kartu Keluarga</title>
  <script src="https://cdn.jsdelivr.net/npm/tesseract.js@2.1.5/dist/tesseract.min.js"></script>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    canvas { border: 1px solid #ccc; margin-top: 10px; max-width: 100%; }
    input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
    label { font-weight: bold; display: block; margin-top: 15px; }
    button { padding: 10px 15px; font-size: 16px; }
  </style>
</head>
<body>

<h2>Upload Gambar KK</h2>
<input type="file" accept="image/*" id="uploadKK">
<canvas id="canvas"></canvas>

<h3>Hasil OCR:</h3>
<textarea id="ocrText" rows="10" placeholder="Tunggu hasil OCR..."></textarea>

<h3>Form Otomatis dari OCR:</h3>
<form id="kkForm">
  <label>No KK:</label>
  <input type="text" name="no_kk" id="no_kk">

  <label>Alamat:</label>
  <input type="text" name="alamat" id="alamat">

  <label>RT:</label>
  <input type="text" name="rt" id="rt">

  <label>RW:</label>
  <input type="text" name="rw" id="rw">

  <button type="submit">Kirim Data</button>
</form>

<script>
    const canvas = document.getElementById('canvas');
const ctx = canvas.getContext('2d');
const ocrText = document.getElementById('ocrText');
const uploadKK = document.getElementById('uploadKK');

uploadKK.addEventListener('change', (e) => {
  const file = e.target.files[0];
  const reader = new FileReader();

  reader.onload = function(ev) {
    const img = new Image();
    img.onload = function() {
      const isPortrait = img.height > img.width;

      // Tentukan ukuran canvas dan scaling
      const scaleFactor = 2;
      let canvasWidth, canvasHeight;

      if (isPortrait) {
        canvasWidth = img.height * scaleFactor;
        canvasHeight = img.width * scaleFactor;
      } else {
        canvasWidth = img.width * scaleFactor;
        canvasHeight = img.height * scaleFactor;
      }

      canvas.width = canvasWidth;
      canvas.height = canvasHeight;

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.imageSmoothingEnabled = false;

      ctx.save();
      ctx.scale(scaleFactor, scaleFactor);

      if (isPortrait) {
        // Rotate canvas -90 derajat dan sesuaikan posisi
        ctx.translate(img.height / 2, img.width / 2);
        ctx.rotate(-90 * Math.PI / 180);
        ctx.drawImage(img, -img.width / 2, -img.height / 2);
      } else {
        ctx.drawImage(img, 0, 0);
      }

      ctx.restore();

      ocrText.value = 'Mendeteksi teks...';

      Tesseract.recognize(canvas, 'ind', {
        logger: m => console.log(m),
        // gunakan opsi PSM mode 6 (single block of text)
        // oem di default sudah 3, bisa dicoba
        // sesuaikan opsi yang benar
        // psm = page segmentation mode
        // lihat dokumentasi tesseract.js
        // bisa menggunakan config langsung di recognize kedua argumen
      }).then(({ data: { text } }) => {
        // Bersihkan text dari karakter yang tidak perlu (misal: ganti O dengan 0 jika di No KK)
        let cleanedText = text.replace(/[^0-9A-Za-z\s\.\:\-\/]/g, '');
        cleanedText = cleanedText.replace(/O/g, '0'); // O sering tertukar dengan 0
        cleanedText = cleanedText.replace(/I/g, '1'); // I sering tertukar dengan 1

        ocrText.value = cleanedText;

        autoFillForm(cleanedText);
      }).catch(err => {
        ocrText.value = 'Gagal membaca OCR: ' + err.message;
      });
    };
    img.src = ev.target.result;
  };
  reader.readAsDataURL(file);
});

function autoFillForm(text) {
  // Regex lebih fleksibel, tanpa harus persis 'No KK' tapi cari digit 16
  const noKK = text.match(/(?:No\.?\s*KK\s*[:\-]?\s*|No\s*KK\s*[:\-]?\s*)?(\d{16})/i);
  // Cari alamat setelah 'Jl' sampai akhir baris
  const alamat = text.match(/Jl\.?\s*([^\n\r]+)/i);
  // RT/RW bisa dengan berbagai format, cari RT 2 digit dan RW 2 digit
  const rtRw = text.match(/RT\.?\s*(\d{1,3})\s*\/\s*RW\.?\s*(\d{1,3})/i);

  if (noKK) document.getElementById('no_kk').value = noKK[1];
  if (alamat) document.getElementById('alamat').value = 'Jl ' + alamat[1].trim();
  if (rtRw) {
    document.getElementById('rt').value = rtRw[1];
    document.getElementById('rw').value = rtRw[2];
  }
}

document.getElementById('kkForm').addEventListener('submit', e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target));
  alert('Data siap dikirim:\n' + JSON.stringify(data, null, 2));
});

</script>


</body>
</html>
