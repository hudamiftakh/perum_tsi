<?php $Auth = $this->session->userdata['username']; ?>
<style>
    table.dataTable thead>tr>th.sorting,
    table.dataTable thead>tr>th.sorting_asc,
    table.dataTable thead>tr>th.sorting_desc,
    table.dataTable thead>tr>th.sorting_asc_disabled,
    table.dataTable thead>tr>th.sorting_desc_disabled,
    table.dataTable thead>tr>td.sorting,
    table.dataTable thead>tr>td.sorting_asc,
    table.dataTable thead>tr>td.sorting_desc,
    table.dataTable thead>tr>td.sorting_asc_disabled,
    table.dataTable thead>tr>td.sorting_desc_disabled {
        cursor: pointer;
        position: relative;
        /* padding-right: 26px; */
        padding: 30px;
    }
</style>

<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Pengeluaran Managements</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="/perum_tsi/dashboard">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Keuangan</li>
                        <li class="breadcrumb-item" aria-current="page">Pengeluaran</li>
                    </ol>
                </nav>
            </div>
            <div class="col-3">
                <div class="text-center mb-n5">
                    <img src="<?php echo base_url(); ?>dist/images/backgrounds/welcome-bg.svg" alt=""
                        class="img-fluid mb-n4" />
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.form-container {
      max-width: 100%;
      margin: 60px auto;
      background: #fff;
      padding: 45px 40px;
      border-radius: 16px;
      border: 1px solid #dee2e6;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.07);
      animation: fadeInUp 0.5s ease-out;
    }

    @keyframes fadeInUp {
      0% {
        opacity: 0;
        transform: translateY(30px);
      }
      100% {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .form-title {
      font-weight: 600;
      font-size: 1.75rem;
      margin-bottom: 30px;
      text-align: center;
      color: #2c3e50;
    }

    .form-label {
      font-weight: 500;
      color: #2f3640;
    }

    .form-control {
      border-radius: 8px;
      border: 1px solid #ced4da;
    }

    textarea.form-control {
      resize: vertical;
    }

    .btn-submit {
      background: #1e88e5;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      padding: 10px 0;
      transition: background 0.3s ease;
    }

    .btn-submit:hover {
      background: #1565c0;
    }

    .info-text {
      font-size: 0.9rem;
      color: #888;
      text-align: center;
      margin-top: 20px;
    }
  </style>
<div class="container">

  <div class="form-container">
    <h3 class="form-title">🗓️ Form Input Pengeluaran</h3>
    <form id="formPengeluaran"method="POST">
        
        <div class="mb-3">
            <input type="hidden" name="id" id="id" value="<?php echo isset($result['id']) ? $result['id'] : ''; ?>" class="form-control" placeholder="Contoh: Rapat Koordinasi Kegiatan RW" required>
            <label class="form-label">Jenis Pengeluaran</label>
            <select class="form-select select2" id="jenis_pengeluaran" name="jenis_pengeluaran"
                required>
                <option value="">Pilih Jenis Pengeluaran</option>
                <?php
                foreach ($option_pengeluaran as $key => $value) :
                ?>
                    <option value="<?php echo $value['id'];?>" <?php if(isset($result['jenis_pengeluaran']) && $result['jenis_pengeluaran']==$value['id']) echo 'selected';?>><?php echo $value['text']; ?> </option>
                <?php endforeach; ?>
            </select>

        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi Pengeluaran</label>
            <input type="text" name="deskripsi" value="<?php echo isset($result['deskripsi']) ? $result['deskripsi'] : ''; ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Nominal Pengeluaran</label>
            <input type="text" name="nominal" id="nominal" value="<?php echo isset($result['nominal']) ? $result['nominal'] : ''; ?> " onkeyup="formatThousand(this)" inputmode="numeric"  class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Pengeluaran</label>
            <input type="date" name="tanggal" value="<?php echo isset($result['tanggal']) ? $result['tanggal'] : ''; ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control" rows="3" placeholder="Deskripsi lebih lengkap terkait pengeluaran tersebut(jika ada)"><?php echo isset($result['keterangan']) ? htmlspecialchars($result['keterangan']) : ''; ?></textarea>
        </div>
        <button type="submit" class="btn btn-submit w-100" name="simpan" style="color : white" >💾 Simpan Pengeluaran</button>
    </form>
    <div class="info-text">Pastikan data yang Anda isi sudah benar sebelum disimpan.</div>
  </div>
</div>
<script>
    $(function(){
        const input = document.getElementById("nominal");
        const event = new KeyboardEvent("keyup", {
        bubbles: true,
        cancelable: true,
        key: input // bisa pakai angka berapa pun, hanya agar event terlihat alami
        });
        input.dispatchEvent(event);
    });
  function formatThousand(input) {
      // Simpan posisi kursor
      let cursorPos = input.selectionStart;
      let oldLength = input.value.length;

      // Hapus semua karakter selain angka
      let raw = input.value.replace(/\D/g, '');

      // Tambahkan titik sebagai pemisah ribuan
      let formatted = raw.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

      // Tampilkan hasil format di field
      input.value = formatted;

      // Sesuaikan posisi kursor
      let newLength = formatted.length;
      cursorPos += newLength - oldLength;
      input.setSelectionRange(cursorPos, cursorPos);
    }
</script>
<script>
    $(document).ready(function() {
        var id = $('#id').val();
        // NEW
        if (id=='') {
            $('#formPengeluaran').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: '<?php echo base_url('kas/save_pengeluaran'); ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Mengirim data...',
                            text: 'Mohon tunggu sebentar...',
                            icon: 'info',
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Sukses!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href="<?=base_url();?>/kas/pengeluaran";
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan: ' + error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        }else{
        // EDIT PENGELUARAN
            $('#formPengeluaran').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);

                $.ajax({
                    url: '<?php echo base_url('kas/edit_pengeluaran'); ?>',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Mengirim data...',
                            text: 'Mohon tunggu sebentar...',
                            icon: 'info',
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Swal.fire({
                                title: 'Sukses!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href="<?=base_url();?>/kas/pengeluaran";
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan: ' + error,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        }
        
            
        });
</script>
