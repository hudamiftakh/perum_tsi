<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">WABOT API KEY</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">API KEY Setting</li>
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
.alert-danger {
    --bs-alert-color: var(--bs-danger-text-emphasis);
    --bs-alert-bg: var(--bs-danger-bg-subtle);
    --bs-alert-border-color: var(--bs-danger-border-subtle);
    --bs-alert-link-color: var(--bs-danger-text-emphasis);
}
</style>
<?php if($_SESSION['username']['email']=='blowebdev17@gmail.com') :?>
<form action="<?php echo base_url('api/generate_key'); ?>" method="post">
    <select name="email" id="">
        <option value="">Email</option>
        <?php 
		$result = $this->db->get("users")->result_array();
		foreach ($result as $key => $value) :
		?>
        <option value="<?php echo $value['email'] ?>"><?php echo $value['email'] ?></option>
        <?php endforeach;?>
    </select>
    <!-- <input type="hidden" value="<?= useraAuthData()['email']; ?>" name="email"> -->
    <select name="tipe_langganan" id="">
        <option value="">Pilih tanggal</option>
        <option value="31">1 Bulan</option>
        <option value="93">3 Bulan</option>
        <option value="183">6 Bulan</option>
        <option value="365">1 Tahun</option>
    </select>
    <button type="submit" class="btn btn-danger">Set Langganan</button>
</form>
<?php
endif;
$daKey = $this->db->get_where('users', array('email' => useraAuthData()['email']))->row_array();
?>
<br>
<?php echo $this->session->flashdata('notify'); ?>
<div class="input-group mb-3">
    <span class="input-group-text">API Key &nbsp &nbsp &nbsp &nbsp &nbsp</span>
    <input type="text" class="form-control input-lg" disabled value="<?= $daKey['api_key']; ?>">
</div>
<div class="input-group mb-3">
    <span class="input-group-text">Secret Key</span>
    <input type="text" class="form-control input-lg" disabled value="<?= $daKey['secret_key']; ?>">
</div>
<?php
// Tanggal dari database
$tanggalDatabase = $daKey['expired'];
// var_dump($tanggalDatabase);
// Konversi ke objek DateTime
$tanggalTarget = new DateTime($tanggalDatabase);
$tanggalHariIni = new DateTime(); // Tanggal hari ini

// Hitung selisih hari
$selisih = $tanggalHariIni->diff($tanggalTarget);

// Tampilkan hasil
if ($tanggalHariIni > $tanggalTarget) {
   $status = "Expired / Belum langganan";
} else {
   $status = $selisih->days;
}
?>

<div class="input-group mb-3">
    <span class="input-group-text">Expired &nbsp &nbsp &nbsp &nbsp &nbsp</span>
    <input type="text" class="form-control input-lg" disabled
        value="<?php echo $status ?> Hari Lagi / <?php echo farmat_tanggal($daKey['expired']); ?>">
</div>

<div class="alert alert-danger" role="alert">
    <strong>Fitur Berbayar - </strong> Lakukan pembayaran senilai <b>Rp 50.000/Bulan</b> untuk melakukan aktivasi
    <b>Apikey</b> dan <b>Secret Key</b> ke QRIS Dibawah ini.-
    Lakukan konfirmasi ke nomor <a href="https://wa.me/628130088028">628130088028</a>

    <center>
        <img src="<?php echo base_url() ?>assets/qris.jpeg" alt="" style="width: 300px; height: 300px;"><br>
        <img src="<?php echo base_url() ?>assets/bank.png" alt="" style="width: 20%;"><br>
        <!-- Scan QRIS dengan menggunakan ewallet  -->
    </center>
</div>
