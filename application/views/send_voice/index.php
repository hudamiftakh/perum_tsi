<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Whatsapp</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Send Voice</li>
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

<?php 
echo $this->session->flashdata('notify');
?>

<div class="card w-100">
    <div class="card-body">
        <div class="d-flex mb-3 align-items-center">
            <div>
                <h5 class="mb-0 fs-5">Send Whatsapp</h5>
            </div>
        </div>
        <form class="mt-4" action="<?=base_url(); ?>sendwa/act_send_voice" method="Post">
            <div class="form-group">
                <label for="exampleFormControlSelect1">From Number</label>
                <select class="form-control" name="from" required>
                    <option value="">From Number</option>
                    <?php
                    $result = $this->db->get_where("sessions", array('status' => 'aktif', 'is_connected'=>'connect'))->result_array();
                    foreach ($result as $key => $value) {?>
                        <option value="<?= $value['number'] ?>">
                            <?= $value['number'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <br/>
            <div class="form-group">
                <label for="exampleFormControlSelect1">To (Number Whatsapp)</label>
                <input type="number" class="form-control" name="to" value="" required>
            </div>
            <br/>
            <div class="form-group">
                <label for="exampleFormControlSelect1">Message convert to voice</label>
                <textarea name="message" class="form-control" cols="30" rows="10" required></textarea>
            </div>
            <br/>
            <div class="form-group">
                <button class="btn btn-danger" type="reset">Reset</button>
                <button class="btn btn-primary" type="submit" name="simpan">Simpan</button>
            </div>
        </form>
    </div>
</div>