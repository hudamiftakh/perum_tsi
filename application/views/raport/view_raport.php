<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<div class="card border-top border-success">
    <div class="card-header" style="font-size: 20px;">
        <i class="ti ti-filter"></i> Filter
    </div>
    <div class="card-body">
        <form action="" method="post">
            <div class="row">
                <div class="col">
                    <label for="">Bulan</label>
                    <select class="form-control js-example-basic-single" name="bulan" style="width: 97%;">
                        <option value="">Pilih Bulan</option>
                        <?php 
						$bulan_nama = [
							1 => 'Januari', 
							2 => 'Februari', 
							3 => 'Maret', 
							4 => 'April', 
							5 => 'Mei', 
							6 => 'Juni', 
							7 => 'Juli', 
							8 => 'Agustus', 
							9 => 'September', 
							10 => 'Oktober', 
							11 => 'November', 
							12 => 'Desember'
						];

						for ($i = 1; $i <= 12; $i++) : ?>
                        <option value="<?php echo $i; ?>" <?php echo ($_REQUEST['bulan'] == $i) ? "selected" : ""; ?>>
                            <?php echo $bulan_nama[$i]; ?>
                        </option>
                        <?php endfor; ?>

                    </select>
                </div>
                <div class="col">
                    <label for="">Pegawai - KSH</label>
                    <select class="js-example-basic-single" name="pegawai" style="width: 97%;">
                        <option value="">Pilih KSH</option>
                        <?php 
							if($_SESSION['username']['role']=='admin') { 
								$ksh = $this->db->get("master_pegawai")->result_array();
							}else{
								$id = $_SESSION['username']['id'];
								$ksh = $this->db->where(array('id'=>$id))->get("master_pegawai")->result_array();
							};
							foreach ($ksh as $key => $value) :
						?>
                        <option value="<?php echo $value['id']; ?>"
                            <?php echo ($_REQUEST['pegawai']==$value['id']) ? "selected": ""; ?>>
                            <?php echo $value['nik']." -".$value['nama']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div style="padding-top: 12px;">
                <button type="submit" class="btn btn-success" name="filter"><i class="ti ti-filter"></i> Filter</button>
                <a href="<?php echo base_url('raport'); ?>" class="btn btn-warning" name=""><i
                        class="ti ti-refresh"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

<?php if(isset($_REQUEST['filter'])) : ?>  
<?php 
$bulan_nama = [
    1 => 'Januari', 
    2 => 'Februari', 
    3 => 'Maret', 
    4 => 'April', 
    5 => 'Mei', 
    6 => 'Juni', 
    7 => 'Juli', 
    8 => 'Agustus', 
    9 => 'September', 
    10 => 'Oktober', 
    11 => 'November', 
    12 => 'Desember'
];
?>
<div class="card border-top border-success">
    <div class="card-body">
        <i class="ti ti-calendar"></i> <u>Rekap Poin Kinerja KSH pada Bulan <?php echo $bulan_nama[$_REQUEST['bulan']]; ?></u>
        <div class="table-responsive">
            <table class="table border table-striped display" style="width: 100%; table-layout:fixed">
                <thead class="bg-danger text-white">
                    <tr>
                        <th width="45px" nowrap>RANKING</th>
                        <th width="90px" nowrap="">NIK</th>
                        <th width="80px" nowrap="">NAMA</th>
                        <th width="80px" nowrap="">ALAMAT</th>
                        <th width="1px" nowrap="">RW</th>
                        <th width="1px" nowrap="">RT</th>
                        <th width="80px" nowrap="">KEL</th>
                        <th width="80px" nowrap="">KEC</th>
                        <th width="60px" nowrap="">TOTAL POIN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
						if(isset($_REQUEST['filter'])){
                            $pegawai = (!empty($_REQUEST['pegawai'])) ? "AND b.id='".$_REQUEST['pegawai']."'": "";
							$filter = "WHERE a.bulan ='".$_REQUEST['bulan']."'".$pegawai;
						}
						$result = $this->db->query("
                        SELECT 
                            sub.* 
                        FROM (
                            SELECT 
                                a.*, 
                                b.id,
                                b.nama AS pegawai,
                                b.nik AS nik_pegawai,
                                ROW_NUMBER() OVER (ORDER BY a.total DESC) AS ranking
                            FROM 
                                master_pegawai AS b
                            LEFT JOIN 
                                report AS a ON a.nik = b.nik 
                        ) AS sub
                        WHERE 
                            sub.bulan = '".$_REQUEST['bulan']."' OR  sub.bulan IS NULL 
                            ".(!empty($_REQUEST['pegawai']) ? "AND sub.id = '".$_REQUEST['pegawai']."'" : "")."
                        ORDER BY 
                            sub.ranking
                        ")->result_array();

					foreach ($result as $key => $value) :
					?>
                    <tr>
                        <td>
                        <?php if($value['total']<>0) :  ?>
                        <?php if ($value['ranking'] == '1') : ?>
                            <center>
                                (<?php echo $value['ranking']; ?>) <br>
                                <img height="20" width="20" style="width: 22px !important; height: 22px !important;"
                                    src="<?php echo base_url('assets/trophy.png'); ?>" alt="">
                                <br>
                            </center>
                        <?php elseif ($value['ranking'] == '2') : ?>
                            <center>
                                <?php if($value['total']<>0) :  ?>
                                (<?php echo $value['ranking']; ?>)<br>
                                <img height="20" width="20" style="width: 22px !important; height: 22px !important;"
                                    src="<?php echo base_url('assets/trophy.png'); ?>" alt="">
                                <?php endif; ?>
                                <br>
                            </center>
                            <?php elseif ($value['ranking'] == '3') : ?>
                            <center>
                            <?php if($value['total']<>0) :  ?>
                                (<?php echo $value['ranking']; ?>)<br>
                                <img height="20" width="20" style="width: 22px !important; height: 22px !important;"
                                    src="<?php echo base_url('assets/trophy.png'); ?>" alt="">
                                <br>
                            <?php endif; ?>
                            </center>
                        <?php else : ?>
                            <center>
                                <?php echo $value['ranking']; ?>
                            </center>
                        <?php endif; ?>
                        <?php endif; ?>

                        </td>
                        <td><?php echo $value['nik_pegawai'] ?></td>
                        <td><?php echo $value['pegawai'] ?></td>
                        <td><?php echo $value['alamat'] ?></td>
                        <td><?php echo $value['rw'] ?></td>
                        <td><?php echo $value['rt'] ?></td>
                        <td>Pradah Kalikendal</td>
                        <td>Dukuh Pakis</td>
                        <td><?php echo $value['total'] ?></td>
                    </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else : ?>

<?php endif; ?>

<style>
.select2-selection {
    height: 40px !important;
}
</style>
<script>
$(document).ready(function() {
    $('.js-example-basic-single').select2();
});
var table = $('#DataAktivitas').DataTable({});
</script>