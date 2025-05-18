<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Form Pendataan Keluarga</title>
   <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>dist/logo_2.png" />
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Select2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>


  <style>
    body {
      background-color: #d4edda;
    }
    .card {
      border-radius: 1rem;
    }
    .remove-member-btn {
      position: absolute;
      right: 10px;
      top: 10px;
    }
    .form-label {
      font-weight: 500;
    }
    .anggota-header {
      font-size: 1.1rem;
      font-weight: bold;
      color: #0d6efd;
      margin-bottom: 1rem;
    }
    @media (max-width: 576px) {
      .form-label {
        font-size: 0.9rem;
      }
      .form-control, .form-select {
        font-size: 0.9rem;
      }
      .remove-member-btn {
        right: 5px;
        top: 5px;
        font-size: 0.75rem;
        padding: 2px 6px;
      }
      .anggota-header {
        font-size: 1rem;
      }
    }
    /* Select2 full width fix */
    .select2-container {
      width: 100% !important;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="card shadow-lg">
     <div style="height: 160px; background-color:rgb(243, 247, 245)" class="card-header text-white text-center d-flex align-items-center justify-content-center">
     <center> <img style="width: 200px;" src="<?php echo base_url('logo-tsi-removebg-preview.png'); ?>" alt=""> 
     <h5 class="mb-0" style="color: black;">Form Pendataan Keluarga <br> Perumahan Taman Sukodono Indah</h5>
    </center>  <br>
    
    </div>
      <div class="card-body">
        <form id="formKeluarga" enctype="multipart/form-data">
          <h6 class="mb-3">Data Kartu Keluarga</h6>
          <div class="row g-3 mb-3">

            <!-- Nomor Rumah pakai select2 -->
            <div class="col-md-6">
              <label for="nomorRumah" class="form-label">Nomor Rumah</label>
              <select class="form-select select2" id="nomorRumah" name="nomor_rumah[]"  multiple="multiple"  required>
                <option value="">Pilih Nomor Rumah</option>
                <?php 
                  $result_rumah = $this->db->get("master_rumah")->result_array();
                  foreach ($result_rumah as $key => $value) :

                ?>
                  <option value="<?php echo $value['alamat']; ?>"><?php echo $value['alamat']; ?> - <?php echo $value['nama']; ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-6">
              <label for="noKK" class="form-label">Nomor KK</label>
              <input type="text" class="form-control" id="noKK" name="no_kk" required />
            </div>

            <div class="col-md-6">
              <label for="statusRumah" class="form-label">Status Tempat Tinggal</label>
              <select class="form-select" id="statusRumah" name="status_rumah" required>
                <option value="">Pilih Status</option>
                <option value="Rumah Sendiri">Rumah Sendiri</option>
                <option value="Sewa/Kontrak">Sewa/Kontrak</option>
                <option value="Musiman">Musiman</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="alamat" class="form-label">Alamat Lengkap</label>
              <input type="text" class="form-control" id="alamat" name="alamat" required />
            </div>

            <div class="col-sm-6 col-md-3">
              <label for="provinsi" class="form-label">Provinsi</label>
              <select class="form-select select2" id="provinsi" name="provinsi" required></select>
            </div>
            <div class="col-sm-6 col-md-3">
              <label for="kota" class="form-label">Kota/Kabupaten</label>
              <select class="form-select select2" id="kota" name="kota" required></select>
            </div>
            <div class="col-sm-6 col-md-3">
              <label for="kecamatan" class="form-label">Kecamatan</label>
              <select class="form-select select2" id="kecamatan" name="kecamatan" required></select>
            </div>
            <div class="col-sm-6 col-md-3">
              <label for="kelurahan" class="form-label">Kelurahan</label>
              <select class="form-select select2" id="kelurahan" name="kelurahan" required></select>
            </div>

            <!-- Upload KK di paling belakang -->
            <div class="col-md-6 mt-3">
              <label for="fileKK" class="form-label">Upload Kartu Keluarga</label>
              <input type="file" class="form-control" id="fileKK" name="file_kk" accept=".pdf,.jpg,.jpeg,.png" required />
            </div>

             <div class="col-md-6 mt-3">
              <label for="fileKK" class="form-label">Nomor Whatsapp</label>
              <input type="text" class="form-control" id="noHp" name="no_hp" required />
            </div>

          </div>

          <h6 class="mt-4 mb-3">Anggota Keluarga</h6>
          <div id="anggotaKeluargaWrapper"></div>
          <button type="button" class="btn btn-outline-primary mt-3 w-100" onclick="tambahAnggota()">+ Tambah Anggota Keluarga</button>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-success w-100">Simpan Data</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Template anggota keluarga -->
  <template id="templateAnggota">
    <div class="card border rounded p-3 mt-3 position-relative anggota-keluarga">
      <button type="button" class="btn btn-sm btn-danger remove-member-btn" onclick="hapusAnggota(this)">X</button>
      <div class="anggota-header">Anggota Ke-<span class="nomor-anggota"></span></div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" class="form-control" name="nama[]" required />
        </div>
        <div class="col-md-6">
          <label class="form-label">NIK</label>
          <input type="text" class="form-control" name="nik[]" required />
        </div>
       <div class="col-md-6">
          <label class="form-label">Agama</label>
          <select class="form-select" name="agama[]" required>
            <option value="">Pilih Agama...</option>
            <option value="Islam">Islam</option>
            <option value="Kristen Protestan">Kristen Protestan</option>
            <option value="Katolik">Katolik</option>
            <option value="Hindu">Hindu</option>
            <option value="Buddha">Buddha</option>
            <option value="Konghucu">Konghucu</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status Perkawinan</label>
          <select class="form-select" name="status_perkawinan[]" required>
            <option value="">Pilih Status Perkawinan...</option>
            <option value="Belum Kawin">Belum Kawin</option>
            <option value="Kawin">Kawin</option>
            <option value="Cerai Hidup">Cerai Hidup</option>
            <option value="Cerai Mati">Cerai Mati</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Hubungan</label>
          <select class="form-select" name="hubungan[]" required>
            <option value="">Pilih...</option>
            <option value="Kepala Keluarga">Kepala Keluarga</option>
            <option value="Istri">Istri</option>
            <option value="Anak">Anak</option>
            <option value="Orang Tua">Orang Tua</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Tanggal Lahir</label>
          <input type="date" class="form-control" name="tgl_lahir[]" required />
        </div>
      </div>
    </div>
  </template>

  <!-- jQuery (required by Select2) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Select2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script>
    // Init Select2 for nomor rumah
    $(document).ready(function() {
      $('.select2').select2({
        placeholder: 'Pilih Nomor Rumah',
        allowClear: true
      });

      $('#provinsi').select2({
        placeholder: 'Pilih Provinsi',
        allowClear: true
      });

      $('#kota').select2({
        placeholder: 'Pilih Kota/Kabupaten',
        allowClear: true
      });

      $('#kecamatan').select2({
        placeholder: 'Pilih Kecamatan',
        allowClear: true
      });

      $('#kelurahan').select2({
        placeholder: 'Pilih Kelurahan',
        allowClear: true
      });
    });

    // Tambah anggota keluarga
    function tambahAnggota() {
      const wrapper = document.getElementById('anggotaKeluargaWrapper');
      const template = document.getElementById('templateAnggota').content.cloneNode(true);
      const count = wrapper.children.length + 1;
      template.querySelector('.nomor-anggota').textContent = count;
      wrapper.appendChild(template);
      updateNomorAnggota();
    }

    // Hapus anggota keluarga
    function hapusAnggota(btn) {
      btn.closest('.anggota-keluarga').remove();
      updateNomorAnggota();
    }

    // Update nomor anggota
    function updateNomorAnggota() {
      const cards = document.querySelectorAll('.anggota-keluarga');
      cards.forEach((card, index) => {
        card.querySelector('.nomor-anggota').textContent = index + 1;
      });
    }

    // API wilayah Indonesia
    const provinsi = $('#provinsi');
    const kota = $('#kota');
    const kecamatan = $('#kecamatan');
    const kelurahan = $('#kelurahan');

    fetch("https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json")
      .then(res => res.json())
      .then(data => {
        provinsi.empty().append('<option></option>');
        data.forEach(p => {
          provinsi.append(`<option value="${p.id}">${p.name}</option>`);
        });
      });

    provinsi.on('change', function () {
      kota.empty().append('<option>Loading...</option>');
      kecamatan.empty().append('<option value="">Pilih Kecamatan</option>');
      kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');

      fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${this.value}.json`)
        .then(res => res.json())
        .then(data => {
          kota.empty().append('<option></option>');
          data.forEach(k => {
            kota.append(`<option value="${k.id}">${k.name}</option>`);
          });
        });
    });

    kota.on('change', function () {
      kecamatan.empty().append('<option>Loading...</option>');
      kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');

      fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${this.value}.json`)
        .then(res => res.json())
        .then(data => {
          kecamatan.empty().append('<option></option>');
          data.forEach(kec => {
            kecamatan.append(`<option value="${kec.id}">${kec.name}</option>`);
          });
        });
    });

    kecamatan.on('change', function () {
      kelurahan.empty().append('<option>Loading...</option>');

      fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${this.value}.json`)
        .then(res => res.json())
        .then(data => {
          kelurahan.empty().append('<option></option>');
          data.forEach(kel => {
            kelurahan.append(`<option value="${kel.id}">${kel.name}</option>`);
          });
        });
    });

    // Load 1 anggota keluarga default
    window.onload = tambahAnggota;
  </script>
  
  <script>
    $(document).ready(function() {
    $('#formKeluarga').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        $.ajax({
            url: '<?php echo base_url('dashboard/save_pendataan_keluarga'); ?>',
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
                        location.reload();
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
});
  </script>

</body>
</html>
