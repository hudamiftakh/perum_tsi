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
                        <li class="breadcrumb-item" aria-current="page">Connect Whatsapp</li>
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
<div class="modal" id="tambah_nomor">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Whatsapp Number Input Form</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="#" id="saveSession" onsubmit="saveSession(event);" method="POST">
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Country Code :</label>
                        <select name="country_code" class="form-control" id="country_code" required>
                            <option value="93">Afghanistan (93)</option>
                            <option value="355">Albania (355)</option>
                            <option value="213">Algeria (213)</option>
                            <option value="1684">American Samoa (1684)</option>
                            <option value="376">Andorra (376)</option>
                            <option value="244">Angola (244)</option>
                            <option value="1264">Anguilla (1264)</option>
                            <option value="0">Antarctica (0)</option>
                            <option value="1268">Antigua and Barbuda (1268)</option>
                            <option value="54">Argentina (54)</option>
                            <option value="374">Armenia (374)</option>
                            <option value="297">Aruba (297)</option>
                            <option value="61">Australia (61)</option>
                            <option value="43">Austria (43)</option>
                            <option value="994">Azerbaijan (994)</option>
                            <option value="1242">Bahamas (1242)</option>
                            <option value="973">Bahrain (973)</option>
                            <option value="880">Bangladesh (880)</option>
                            <option value="1246">Barbados (1246)</option>
                            <option value="375">Belarus (375)</option>
                            <option value="32">Belgium (32)</option>
                            <option value="501">Belize (501)</option>
                            <option value="229">Benin (229)</option>
                            <option value="1441">Bermuda (1441)</option>
                            <option value="975">Bhutan (975)</option>
                            <option value="591">Bolivia (591)</option>
                            <option value="387">Bosnia and Herzegovina (387)</option>
                            <option value="267">Botswana (267)</option>
                            <option value="0">Bouvet Island (0)</option>
                            <option value="55">Brazil (55)</option>
                            <option value="246">British Indian Ocean Territory (246)</option>
                            <option value="673">Brunei Darussalam (673)</option>
                            <option value="359">Bulgaria (359)</option>
                            <option value="226">Burkina Faso (226)</option>
                            <option value="257">Burundi (257)</option>
                            <option value="855">Cambodia (855)</option>
                            <option value="237">Cameroon (237)</option>
                            <option value="1">Canada (1)</option>
                            <option value="238">Cape Verde (238)</option>
                            <option value="1345">Cayman Islands (1345)</option>
                            <option value="236">Central African Republic (236)</option>
                            <option value="235">Chad (235)</option>
                            <option value="56">Chile (56)</option>
                            <option value="86">China (86)</option>
                            <option value="61">Christmas Island (61)</option>
                            <option value="672">Cocos (Keeling) Islands (672)</option>
                            <option value="57">Colombia (57)</option>
                            <option value="269">Comoros (269)</option>
                            <option value="242">Congo (242)</option>
                            <option value="242">Congo, the Democratic Republic of the (242)</option>
                            <option value="682">Cook Islands (682)</option>
                            <option value="506">Costa Rica (506)</option>
                            <option value="225">Cote D'Ivoire (225)</option>
                            <option value="385">Croatia (385)</option>
                            <option value="53">Cuba (53)</option>
                            <option value="357">Cyprus (357)</option>
                            <option value="420">Czech Republic (420)</option>
                            <option value="45">Denmark (45)</option>
                            <option value="253">Djibouti (253)</option>
                            <option value="1767">Dominica (1767)</option>
                            <option value="1809">Dominican Republic (1809)</option>
                            <option value="593">Ecuador (593)</option>
                            <option value="20">Egypt (20)</option>
                            <option value="503">El Salvador (503)</option>
                            <option value="240">Equatorial Guinea (240)</option>
                            <option value="291">Eritrea (291)</option>
                            <option value="372">Estonia (372)</option>
                            <option value="251">Ethiopia (251)</option>
                            <option value="500">Falkland Islands (Malvinas) (500)</option>
                            <option value="298">Faroe Islands (298)</option>
                            <option value="679">Fiji (679)</option>
                            <option value="358">Finland (358)</option>
                            <option value="33">France (33)</option>
                            <option value="594">French Guiana (594)</option>
                            <option value="689">French Polynesia (689)</option>
                            <option value="0">French Southern Territories (0)</option>
                            <option value="241">Gabon (241)</option>
                            <option value="220">Gambia (220)</option>
                            <option value="995">Georgia (995)</option>
                            <option value="49">Germany (49)</option>
                            <option value="233">Ghana (233)</option>
                            <option value="350">Gibraltar (350)</option>
                            <option value="30">Greece (30)</option>
                            <option value="299">Greenland (299)</option>
                            <option value="1473">Grenada (1473)</option>
                            <option value="590">Guadeloupe (590)</option>
                            <option value="1671">Guam (1671)</option>
                            <option value="502">Guatemala (502)</option>
                            <option value="224">Guinea (224)</option>
                            <option value="245">Guinea-Bissau (245)</option>
                            <option value="592">Guyana (592)</option>
                            <option value="509">Haiti (509)</option>
                            <option value="0">Heard Island and Mcdonald Islands (0)</option>
                            <option value="39">Holy See (Vatican City State) (39)</option>
                            <option value="504">Honduras (504)</option>
                            <option value="852">Hong Kong (852)</option>
                            <option value="36">Hungary (36)</option>
                            <option value="354">Iceland (354)</option>
                            <option value="91">India (91)</option>
                            <option value="62" selected="">Indonesia (62)</option>
                            <option value="98">Iran, Islamic Republic of (98)</option>
                            <option value="964">Iraq (964)</option>
                            <option value="353">Ireland (353)</option>
                            <option value="972">Israel (972)</option>
                            <option value="39">Italy (39)</option>
                            <option value="1876">Jamaica (1876)</option>
                            <option value="81">Japan (81)</option>
                            <option value="962">Jordan (962)</option>
                            <option value="7">Kazakhstan (7)</option>
                            <option value="254">Kenya (254)</option>
                            <option value="686">Kiribati (686)</option>
                            <option value="850">Korea, Democratic People's Republic of (850)</option>
                            <option value="82">Korea, Republic of (82)</option>
                            <option value="965">Kuwait (965)</option>
                            <option value="996">Kyrgyzstan (996)</option>
                            <option value="856">Lao People's Democratic Republic (856)</option>
                            <option value="371">Latvia (371)</option>
                            <option value="961">Lebanon (961)</option>
                            <option value="266">Lesotho (266)</option>
                            <option value="231">Liberia (231)</option>
                            <option value="218">Libyan Arab Jamahiriya (218)</option>
                            <option value="423">Liechtenstein (423)</option>
                            <option value="370">Lithuania (370)</option>
                            <option value="352">Luxembourg (352)</option>
                            <option value="853">Macao (853)</option>
                            <option value="389">Macedonia, the Former Yugoslav Republic of (389)</option>
                            <option value="261">Madagascar (261)</option>
                            <option value="265">Malawi (265)</option>
                            <option value="60">Malaysia (60)</option>
                            <option value="960">Maldives (960)</option>
                            <option value="223">Mali (223)</option>
                            <option value="356">Malta (356)</option>
                            <option value="692">Marshall Islands (692)</option>
                            <option value="596">Martinique (596)</option>
                            <option value="222">Mauritania (222)</option>
                            <option value="230">Mauritius (230)</option>
                            <option value="269">Mayotte (269)</option>
                            <option value="52">Mexico (52)</option>
                            <option value="691">Micronesia, Federated States of (691)</option>
                            <option value="373">Moldova, Republic of (373)</option>
                            <option value="377">Monaco (377)</option>
                            <option value="976">Mongolia (976)</option>
                            <option value="1664">Montserrat (1664)</option>
                            <option value="212">Morocco (212)</option>
                            <option value="258">Mozambique (258)</option>
                            <option value="95">Myanmar (95)</option>
                            <option value="264">Namibia (264)</option>
                            <option value="674">Nauru (674)</option>
                            <option value="977">Nepal (977)</option>
                            <option value="31">Netherlands (31)</option>
                            <option value="599">Netherlands Antilles (599)</option>
                            <option value="687">New Caledonia (687)</option>
                            <option value="64">New Zealand (64)</option>
                            <option value="505">Nicaragua (505)</option>
                            <option value="227">Niger (227)</option>
                            <option value="234">Nigeria (234)</option>
                            <option value="683">Niue (683)</option>
                            <option value="672">Norfolk Island (672)</option>
                            <option value="1670">Northern Mariana Islands (1670)</option>
                            <option value="47">Norway (47)</option>
                            <option value="968">Oman (968)</option>
                            <option value="92">Pakistan (92)</option>
                            <option value="680">Palau (680)</option>
                            <option value="970">Palestinian Territory, Occupied (970)</option>
                            <option value="507">Panama (507)</option>
                            <option value="675">Papua New Guinea (675)</option>
                            <option value="595">Paraguay (595)</option>
                            <option value="51">Peru (51)</option>
                            <option value="63">Philippines (63)</option>
                            <option value="0">Pitcairn (0)</option>
                            <option value="48">Poland (48)</option>
                            <option value="351">Portugal (351)</option>
                            <option value="1787">Puerto Rico (1787)</option>
                            <option value="974">Qatar (974)</option>
                            <option value="262">Reunion (262)</option>
                            <option value="40">Romania (40)</option>
                            <option value="70">Russian Federation (70)</option>
                            <option value="250">Rwanda (250)</option>
                            <option value="290">Saint Helena (290)</option>
                            <option value="1869">Saint Kitts and Nevis (1869)</option>
                            <option value="1758">Saint Lucia (1758)</option>
                            <option value="508">Saint Pierre and Miquelon (508)</option>
                            <option value="1784">Saint Vincent and the Grenadines (1784)</option>
                            <option value="684">Samoa (684)</option>
                            <option value="378">San Marino (378)</option>
                            <option value="239">Sao Tome and Principe (239)</option>
                            <option value="966">Saudi Arabia (966)</option>
                            <option value="221">Senegal (221)</option>
                            <option value="381">Serbia and Montenegro (381)</option>
                            <option value="248">Seychelles (248)</option>
                            <option value="232">Sierra Leone (232)</option>
                            <option value="65">Singapore (65)</option>
                            <option value="421">Slovakia (421)</option>
                            <option value="386">Slovenia (386)</option>
                            <option value="677">Solomon Islands (677)</option>
                            <option value="252">Somalia (252)</option>
                            <option value="27">South Africa (27)</option>
                            <option value="0">South Georgia and the South Sandwich Islands (0)</option>
                            <option value="34">Spain (34)</option>
                            <option value="94">Sri Lanka (94)</option>
                            <option value="249">Sudan (249)</option>
                            <option value="597">Suriname (597)</option>
                            <option value="47">Svalbard and Jan Mayen (47)</option>
                            <option value="268">Swaziland (268)</option>
                            <option value="46">Sweden (46)</option>
                            <option value="41">Switzerland (41)</option>
                            <option value="963">Syrian Arab Republic (963)</option>
                            <option value="886">Taiwan, Province of China (886)</option>
                            <option value="992">Tajikistan (992)</option>
                            <option value="255">Tanzania, United Republic of (255)</option>
                            <option value="66">Thailand (66)</option>
                            <option value="670">Timor-Leste (670)</option>
                            <option value="228">Togo (228)</option>
                            <option value="690">Tokelau (690)</option>
                            <option value="676">Tonga (676)</option>
                            <option value="1868">Trinidad and Tobago (1868)</option>
                            <option value="216">Tunisia (216)</option>
                            <option value="90">Turkey (90)</option>
                            <option value="7370">Turkmenistan (7370)</option>
                            <option value="1649">Turks and Caicos Islands (1649)</option>
                            <option value="688">Tuvalu (688)</option>
                            <option value="256">Uganda (256)</option>
                            <option value="380">Ukraine (380)</option>
                            <option value="971">United Arab Emirates (971)</option>
                            <option value="44">United Kingdom (44)</option>
                            <option value="1">United States (1)</option>
                            <option value="1">United States Minor Outlying Islands (1)</option>
                            <option value="598">Uruguay (598)</option>
                            <option value="998">Uzbekistan (998)</option>
                            <option value="678">Vanuatu (678)</option>
                            <option value="58">Venezuela (58)</option>
                            <option value="84">Viet Nam (84)</option>
                            <option value="1284">Virgin Islands, British (1284)</option>
                            <option value="1340">Virgin Islands, U.s. (1340)</option>
                            <option value="681">Wallis and Futuna (681)</option>
                            <option value="212">Western Sahara (212)</option>
                            <option value="967">Yemen (967)</option>
                            <option value="260">Zambia (260)</option>
                            <option value="263">Zimbabwe (263)</option>
                        </select>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Whatsapp Number </label>
                        <input type="number" id="number" class="form-control" placeholder="Whatsapp Number"
                            name="number" required>
                    </div>
                    <div class="mb-3 mt-3">
                        <label for="email" class="form-label">Full Name </label>
                        <input type="text" id="fullname" class="form-control" placeholder="Full Name" name="fullname"
                            required>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="simpan" id="simpanButton" class="btn btn-primary">Simpan</button>
                </form>
                <a href="#" class="btn btn-danger" data-bs-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
<div class="card w-100 position-relative overflow-hidden">
    <div class="card-header" style="padding : 0px !important">
        <div class="px-4 pt-4" nowrap="">
            <div style="float:right;">
                <form action="<?php echo base_url('sessions') ?>" type="get">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control border border-success" max="12" name="q"
                            placeholder="Cari Nomor Enter">
                        <button class="btn btn-outline-success btn-lg rounded-end" type="submit">
                            Cari
                        </button> &nbsp &nbsp
                        <button type="button" class="btn waves-effect waves-light btn-lg  btn-outline-success"
                            style="border-radius: 6px !important;" data-bs-toggle="modal"
                            data-bs-target="#tambah_nomor">
                            <i class="ti ti-brand-whatsapp"></i> &nbsp Add Number
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table border table-striped display" style="width: 100%">
                <thead class="bg-success text-white">
                    <tr>
                        <th width="100px">#</th>
                        <th>Number</th>
                        <th>Full Name</th>
                        <th>Status</th>
                        <th nowrap="">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    echo (empty($result)) ? '<tr><td colspan="5"><center><p class="text-muted"><center>Data tidak ditemukan</center></td></tr>' : '';
                    foreach ($result as $key => $data): ?>
                        <tr>
                            <td>
                                <div class="dropdown dropstart">
                                    <a href="#" class="text-muted" id="dropdownMenuButton" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="ti ti-dots-vertical fs-6"></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3"
                                                onclick="showQR('<?= $data->number ?>');" href="#">
                                                <i class="ti ti-qrcode"></i>Scan</a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item d-flex align-items-center gap-3"
                                                onclick="del_session('<?= $data->id ?>', '<?= $data->number ?>')" href="#">
                                                <i class="ti ti-trash-x"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td style="font-weight: bold;">
                            <i class="ti ti-brand-whatsapp text-success" style="font-size: 23px;"></i> <?= $data->country_code . $data->number; ?>
                            </td>
                            <td>
                                <?= $data->fullname; ?>
                            </td>
                            <td>
                                <?php if ($data->is_connected == 'connect'): ?>
                                    <a class="badge text-bg-success rounded-3 fw-semibold fs-2"><i
                                            class="ti ti-plug-connected"></i> Status: <b>Connect</b></a>
                                <?php else: ?>
                                    <a class="badge text-bg-danger rounded-3 fw-semibold fs-2"><i
                                            class="ti ti-plug-off"></i>Status: <b>Not Connect</b></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-info btn-block" onclick="showQR('<?= $data->number ?>');">
                                    <i class="ti ti-qrcode"></i> Scan WhatsApp</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php echo $this->pagination_bootstrap->render(); ?>
        </div>
    </div>
</div>

<style type="text/css">
    .dataTables_filter,
    .dataTables_length {
        margin-bottom: 0px !important;
    }

    .wrap {
        white-space: nowrap;
    }
</style>
<div class="modal" id="qrshow">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Connect Whatsapp Account</h4>
            </div>
            <div class="modal-body">
                <ul class="list-icons">
                    <li style="line-height: 30px;"><a href="javascript:void(0)"><span class="align-middle mr-2">1.
                            </span>Open <b>WhatsApp</b> in your smartphone</a></li>
                    <li style="line-height: 30px;"><a href="javascript:void(0)"><span class="align-middle mr-2">2.
                            </span>Click on the <b>3-dots icon</b> on the top right corner</a></li>
                    <li style="line-height: 30px;"><a href="javascript:void(0)"><span class="align-middle mr-2">3.
                            </span>Select <b>"Whatsapp Web"</b></a></li>
                    <li style="line-height: 30px;"><a href="javascript:void(0)"><span class="align-middle mr-2">4.
                            </span>After that <b>Scan the QR Code</b> Below</a></li>
                </ul>
                <div class="d-flex flex-wrap justify-content-center align-items-center mt-2"
                    style="height: 350px; border: 1px solid #CCC;">
                    <div class="dashboard_bar mr-2">
                        <button type="button" class="btn btn-primary" id="button-generate" onclick="generateQRCode()"
                            style="display: none;">
                            <span class="btn-icon-left text-primary"><i
                                    class="fa fa-qrcode color-primary"></i></span>GENERATE QR CODE
                        </button>
                        <div id="qrcode" style="margin:auto; text-align:center;"></div>
                        <div id="text-loading" class="mt-2" style="text-align: center;"><span id="qrcode_type">scan this
                                qrcode to enter the beta version </span></div>
                    </div>
                </div>

                <b>Catatan:</b>

                <span>Jika setelah scan halaman tidak otomatis refresh, silahkan refresh halaman secara manual
                    atau tekan tombol refresh dibawah</span>
            </div>
            <div class="modal-footer">
                <a href="<?= base_url('sessions') ?>" class="btn btn-danger">Tutup</a>
            </div>
        </div>
    </div>
</div>

<script>
    function showQR(number) {
        $('#qrshow').modal('toggle');
        createQr(number);
    }
    function createQr(number) {
        $("#qrcode").html('<img src="<?= base_url(); ?>assets/preloader.gif" />');
        $.ajax({
            url: "<?php echo base_url() ?>sessions/create_qr/" + number,
            type: "post",
            dataType: "json",
            success: function (response) {
                if (response.status == true) {
                    if (response.qr !== '' || response.qr !== 'undefined') {
                        $("#qrcode").html('<img src="' + response.qr + '" />');
                    } else if (response.qr == 'belum connect') {
                        $("#qrcode").html('<img src="' + response.qr + '" />');
                    } else if (response.qr == 'sudah connect') {
                        $("#qrcode").html('<img src="<?= base_url('assets/Eo_circle_green_white_checkmark.svg.png'); ?>"  width="100px" height="100px"/>');
                    } else {
                        $("#qrcode").html('<img src="<?= base_url('assets/warning.png'); ?>"  width="100px" height="100px"/>');
                    }

                    setTimeout(() => {
                        createQr(number);
                    }, 10000);
                } else {
                    if (response.qr !== 'error') {
                        $("#qrcode").html('<img src="<?= base_url('assets/Eo_circle_green_white_checkmark.svg.png'); ?>"  width="100px" height="100px"/>');
                    } else {
                        $("#qrcode").html('<img src="<?= base_url('assets/warning.png'); ?>"  width="100px" height="100px"/>');
                    }
                    // window.location = "<?= base_url(); ?>sessions";
                }
            }
        });
    }

    function del_session(id, number) {
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "post",
                    url: "<?= base_url('sessions/delete-session') ?>",
                    data: { id: id, number: number },
                    dataType: "json",
                    success: function (response) {
                        console.log(response);
                        if (response.status == true) {
                            setTimeout(() => {
                                window.location = "<?= base_url(); ?>sessions";
                            }, 2000);
                            Swal.fire({
                                title: "Deleted!",
                                text: "Your session has been deleted.",
                                icon: "success"
                            });
                        } else {
                            setTimeout(() => {
                                window.location = "<?= base_url(); ?>sessions";
                            }, 2000);

                            Swal.fire({
                                title: "Erro!",
                                text: "Your session error.",
                                icon: "error"
                            });
                        }
                    }
                });
            }
        });
    }

    function saveSession(event) {
        event.preventDefault();
        var button = document.getElementById('simpanButton');
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        button.disabled = true;
        var formData = new FormData(document.getElementById("saveSession"));
        $.ajax({
            type: "POST",
            url: "<?php echo base_url('sessions/create_sessions'); ?>",
            data: formData,
            dataType: "json",
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status == 'success') {
                    Swal.fire({
                        title: "Saved!",
                        text: "Your session has been saved. Please Scan QRcode Whatsapp ",
                        icon: "success",
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        Swal.close();
                        $('#tambah_nomor').modal('toggle');
                        showQR(response.number);
                    }, 2000);
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: response.msg,
                        icon: "error"
                    });
                    button.disabled = false;
                    button.innerHTML = 'Simpan';
                }
            }
        });
    }
</script>