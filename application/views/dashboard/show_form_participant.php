<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Daftar Hadir Responsif</title>
    <link href="<?php echo base_url(); ?>dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    body {
        background-color: #d4edda;
        font-family: 'Segoe UI', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        height: 100vh;
        margin: 0;
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

    .signature-box {
        border: 2px dashed #adb5bd;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
    }

    canvas {
        background: #fff;
        border-radius: 6px;
        width: 100% !important;
        height: auto;
        touch-action: none;
        display: block;
    }

    .btn-clear {
        font-size: 0.875rem;
    }

    @media (max-width: 576px) {
        .form-container {
            padding: 20px 15px;
        }
    }

    .img-logo {
        display: block;
        max-width: 100px;
        height: auto;
        margin: 20px auto 10px auto;
        object-fit: contain;
    }

    @media (min-width: 768px) {
        .img-logo {
            max-width: 120px;
            /* Logo lebih besar di layar laptop */
            margin-top: 30px;
        }
    }
    </style>
</head>

<body>
    <?php 
        $CI =& get_instance();
        $CI->load->library('encryption');
        $id = $this->uri->segment(2);
        $encrypted_id = strtr($id, ['-' => '+', '_' => '/', '~' => '=']);
        $agenda_id = $CI->encryption->decrypt($encrypted_id);
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

    <div class="form-container">
        <div class="text-center mb-3">
            <img src="<?php echo base_url(); ?>logo_2-removebg-preview.png" alt="Logo" class="img-logo" />
        </div>
        <div class="text-center mb-4">
            <h5 class="fw-semibold mb-1">Daftar Hadir <?php echo $result_agenda['judul']; ?></h5>
            <small class="text-muted"><?php echo format_tanggal($result_agenda['tanggal']); ?></small>
        </div>

        <form method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>" onsubmit="return prepareSignature()">
            <div class="mb-4">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama" name="nama" placeholder="Tuliskan nama lengkap Anda"
                    required />
            </div>

            <div class="mb-4">
                <label class="form-label">Tanda Tangan Digital</label>
                <div class="signature-box">
                    <canvas id="signature"></canvas>
                </div>
                <div class="mt-2 text-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-clear"
                        onclick="clearSignature()">Bersihkan Tanda Tangan</button>
                </div>
            </div>

            <input type="hidden" name="ttd" id="ttd" />
            <button type="submit" name="simpan" class="btn btn-primary w-100">Kirim Kehadiran</button>
        </form>
    </div>

    <script>
    const canvas = document.getElementById('signature');
    const ctx = canvas.getContext('2d');
    let drawing = false;
    let scaled = false;

    function resizeCanvas() {
        const containerWidth = canvas.parentElement.offsetWidth;
        const ratio = 0.4;
        const displayWidth = containerWidth;
        const displayHeight = containerWidth * ratio;

        const dpr = window.devicePixelRatio || 1;
        canvas.width = displayWidth * dpr;
        canvas.height = displayHeight * dpr;
        canvas.style.width = displayWidth + 'px';
        canvas.style.height = displayHeight + 'px';

        ctx.setTransform(1, 0, 0, 1, 0, 0); // reset scale
        ctx.scale(dpr, dpr); // scale sekali saja
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

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', endDraw);
    canvas.addEventListener('mouseout', endDraw);

    canvas.addEventListener('touchstart', startDraw, {
        passive: false
    });
    canvas.addEventListener('touchmove', draw, {
        passive: false
    });
    canvas.addEventListener('touchend', endDraw, {
        passive: false
    });
    canvas.addEventListener('touchcancel', endDraw, {
        passive: false
    });

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