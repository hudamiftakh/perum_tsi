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
      <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div
          class="card-header text-center d-flex flex-column align-items-center justify-content-center p-4"
          style="height: 160px; background: linear-gradient(135deg,rgb(209, 241, 212), #ffffff);">
          <img
            src="<?php echo base_url('logo-tsi-removebg-preview.png'); ?>"
            alt="Logo TSI"
            style="width: 150px; margin-bottom: 15px;">
          <h4 class="fw-bold mb-1 text-dark">PENDATAAN WARGA</h4>
          <p class="mb-0 text-muted" style="font-size: 1.1rem;">
            Perumahan Taman Sukodono Indah
          </p>
        </div>
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
              <label for="statusRumah" class="form-label">Status Tempat Tinggal (Rumah TSI)</label>
              <select class="form-select" id="statusRumah" name="status_rumah" required>
                <option value="">Pilih Status</option>
                <option value="Rumah Sendiri">Rumah Sendiri</option>
                <option value="Sewa/Kontrak">Sewa/Kontrak</option>
                <option value="Musiman">Musiman</option>
                <option value="Usaha">Usaha</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="alamat" class="form-label">Alamat Lengkap (KK)</label>
              <input type="text" class="form-control" id="alamat" name="alamat" required />
            </div>

            <div class="col-sm-6 col-md-3">
              <label for="provinsi" class="form-label">Provinsi (KK)</label>
              <select class="form-select select2" id="provinsi" name="provinsi" required></select>
            </div>
            <div class="col-sm-6 col-md-3">
              <label for="kota" class="form-label">Kota/Kabupaten (KK)</label>
              <select class="form-select select2" id="kota" name="kota" required></select>
            </div>
            <div class="col-sm-6 col-md-3">
              <label for="kecamatan" class="form-label">Kecamatan (KK)</label>
              <select class="form-select select2" id="kecamatan" name="kecamatan" required></select>
            </div>
            <div class="col-sm-6 col-md-3">
              <label for="kelurahan" class="form-label">Kelurahan (KK)</label>
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
        <label class="form-label">Jenis Kelamin</label>
        <select class="form-select" name="jenis_kelamin[]" required>
          <option value="">Pilih Jenis Kelamin...</option>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>
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
         <div class="col-md-6">
            <label class="form-label">Pekerjaan</label>
            <select class="form-select" name="pekerjaan[]" required>
              <option value="">Pilih Pekerjaan...</option>
              <option value="Pelajar/Mahasiswa">Pelajar/Mahasiswa</option>
              <option value="ASN">ASN</option>
              <option value="TNI/Polri">TNI/Polri</option>
              <option value="Pegawai Swasta">Pegawai Swasta</option>
              <option value="Wiraswasta">Wiraswasta</option>
              <option value="Petani">Petani</option>
              <option value="Nelayan">Nelayan</option>
              <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
              <option value="Pensiunan">Pensiunan</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
         <div class="col-md-6">
          <label class="form-label">Golongan Darah</label>
          <select class="form-select" name="golongan_darah[]">
            <option value="">Pilih Golongan Darah...</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="AB">AB</option>
            <option value="O">O</option>
            <option value="Tidak Tahu">Tidak Tahu</option>
          </select>
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
      provinsi.empty().append('<option value="">Pilih Provinsi</option>');
      data.forEach(p => {
        provinsi.append(`<option value="${p.name}" data-id="${p.id}">${p.name}</option>`);
      });
    });

  // On Provinsi Change → Load Kota
  provinsi.on('change', function () {
    kota.empty().append('<option>Loading...</option>');
    kecamatan.empty().append('<option value="">Pilih Kecamatan</option>');
    kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');

    const provinsiId = $('#provinsi option:selected').data('id');

    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provinsiId}.json`)
      .then(res => res.json())
      .then(data => {
        kota.empty().append('<option value="">Pilih Kota/Kabupaten</option>');
        data.forEach(k => {
          kota.append(`<option value="${k.name}" data-id="${k.id}">${k.name}</option>`);
        });
      });
  });

  // On Kota Change → Load Kecamatan
  kota.on('change', function () {
    kecamatan.empty().append('<option>Loading...</option>');
    kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');

    const kotaId = $('#kota option:selected').data('id');

    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${kotaId}.json`)
      .then(res => res.json())
      .then(data => {
        kecamatan.empty().append('<option value="">Pilih Kecamatan</option>');
        data.forEach(kec => {
          kecamatan.append(`<option value="${kec.name}" data-id="${kec.id}">${kec.name}</option>`);
        });
      });
  });

  // On Kecamatan Change → Load Kelurahan
  kecamatan.on('change', function () {
    kelurahan.empty().append('<option>Loading...</option>');

    const kecamatanId = $('#kecamatan option:selected').data('id');

    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${kecamatanId}.json`)
      .then(res => res.json())
      .then(data => {
        kelurahan.empty().append('<option value="">Pilih Kelurahan</option>');
        data.forEach(kel => {
          kelurahan.append(`<option value="${kel.name}" data-id="${kel.id}">${kel.name}</option>`);
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
