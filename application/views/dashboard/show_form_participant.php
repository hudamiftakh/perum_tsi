<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Daftar Hadir - Jotform Style</title>
    <link href="<?php echo base_url(); ?>dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #d4edda;
            /* Hijau pastel */
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            max-width: 600px;
            width: 100%;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 30px 20px;
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
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        canvas {
            background: #fff;
            border-radius: 6px;
            width: 100% !important;
            height: auto;
            max-height: 200px; /* batasi tinggi maksimum supaya tidak terlalu tinggi */
            touch-action: none;
        }

        .btn-clear {
            font-size: 0.875rem;
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 20px 15px;
            }
        }
    </style>
</head>

<body>

    <?php 
        // Dekripsi
        $id = $this->uri->segment(2);
        $agenda_id = decrypt_url($id);
        $result_agenda = $this->db->where(array('id'=>$agenda_id))->get("master_agenda")->row_array();
        if(isset($_POST['simpan'])){
            $nama = $_REQUEST['nama'];
            $ttd = htmlspecialchars($_REQUEST['ttd']);

            $data = array(
                'agenda_id' => $agenda_id,
                'nama' => $nama,
                'ttd_base64' => $ttd,
                'created_at' => date('Y-m-d H:i:s')
            );
            $result = $this->db->insert('master_partisipant', $data);
            $current_url = $_SERVER['REQUEST_URI'];
            if ($result) {
                echo "
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Data kehadiran berhasil disimpan.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '" . htmlspecialchars($current_url) . "';
                    });
                </script>";
            } else {
                echo "
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Data gagal disimpan. Silakan coba lagi.',
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '" . htmlspecialchars($current_url) . "';
                    });
                </script>";
            }
        }
    ?>

    <div
        style="min-height: 10vh; display: flex; align-items: center; justify-content: center; background-color: #d4edda;">
        <div class="wrapper">
            <div class="form-container">
                <div class="text-center mb-3 mt-10">
                    <img src="<?php echo base_url(); ?>logo_2-removebg-preview.png" alt="Logo" style="width: 90px; height: auto;" />
                </div>

                <div class="text-center mb-4">
                    <h5 class="fw-semibold mb-1">Daftar Hadir <?php echo $result_agenda['judul']; ?></h5>
                    <small class="text-muted"><?php echo format_tanggal($result_agenda['tanggal']); ?></small>
                </div>

                <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>" onsubmit="return prepareSignature()">
                    <div class="mb-4">
                        <label for="nama" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Tuliskan nama lengkap Anda" required />
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Tanda Tangan Digital</label>
                        <div class="signature-box">
                            <canvas id="signature"></canvas>
                        </div>
                        <div class="mt-2 text-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-clear" onclick="clearSignature()">Bersihkan Tanda Tangan</button>
                        </div>
                    </div>

                    <input type="hidden" name="ttd" id="ttd" />
                    <button type="submit" name="simpan" class="btn btn-primary w-100">Kirim Kehadiran</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const canvas = document.getElementById('signature');
        const ctx = canvas.getContext('2d');
        let drawing = false;

        function resizeCanvas() {
            const containerWidth = canvas.parentElement.offsetWidth;

            const tempImage = new Image();
            tempImage.src = canvas.toDataURL();

            canvas.width = containerWidth;
            canvas.height = containerWidth * 0.4; // rasio tinggi:lebar = 0.4 agar tidak terlalu tinggi

            tempImage.onload = function () {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(tempImage, 0, 0, canvas.width, canvas.height);
            };
        }

        window.addEventListener('load', resizeCanvas);
        window.addEventListener('resize', resizeCanvas);

        function getPosition(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches.length > 0) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top,
                };
            } else {
                return {
                    x: e.clientX - rect.left,
                    y: e.clientY - rect.top,
                };
            }
        }

        function startDraw(e) {
            drawing = true;
            const pos = getPosition(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!drawing) return;
            const pos = getPosition(e);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function endDraw(e) {
            if (!drawing) return;
            drawing = false;
            ctx.closePath();
            e.preventDefault();
        }

        // Event mouse
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', endDraw);
        canvas.addEventListener('mouseout', endDraw);

        // Event touch (HP/tablet)
        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', endDraw, { passive: false });
        canvas.addEventListener('touchcancel', endDraw, { passive: false });

        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.beginPath();
        }

        function prepareSignature() {
            const ttdInput = document.getElementById('ttd');
            ttdInput.value = canvas.toDataURL('image/png');
            return true;
        }
    </script>

</body>

</html>
