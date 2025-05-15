<div class="card w-100 bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
    <div class="card-body px-4 py-3">
        <div class="row align-items-center">
            <div class="col-9">
                <h4 class="fw-semibold mb-8">Whatsapp API Docs</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a class="text-muted text-decoration-none" href="./">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">API Docs</li>
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

<div class="row">
    <!-- ---------------------------------------------- -->
    <!-- 1. Accordian -->
    <!-- ---------------------------------------------- -->
    <div class="col-lg-12">
        <div class="accordion" id="accordionExample">
            <div class="accordion-item col-lg-12">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        <b>Kirim Pesan Endpoint API WABOT</b>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="table" style="width: 30%;">
                            <tr>
                                <td>Method</td>
                                <td width="1px">: </td>
                                <td><code class="text-success">POST</code></td>
                            </tr>
                            <tr>
                                <td>Endpoint</td>
                                <td width="1px">: </td>
                                <td nowrap=""><code class="text-danger"><?php echo base_url(); ?>api/send-message</code>
                                </td>
                            </tr>
                            <tr>
                                <td>Endpoint</td>
                                <td width="1px">: </td>
                                <td>(FORM DATA)</td>
                            </tr>
                        </table>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>api_key</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>API Key</td>
                                </tr>
                                <tr>
                                    <td>secret_key</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Secret Key</td>
                                </tr>
                                <tr>
                                    <td>from</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Number of your device</td>
                                </tr>
                                <tr>
                                    <td>to</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>recipient number ex 72888xxxx|62888xxxx</td>
                                </tr>
                                <tr>
                                    <td>message</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Messsage to be sent</td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <p>Example Form Data Request</p>
                        <div class="bg-dark text-white">
                            <div style="overflow:auto;">
                                <img src="<?php echo base_url() ?>assets/send_message.png" width="100%" alt=""
                                    class="border-image">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <b>Kirim Pesan Gambar Endpoint API WABOT</b>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        <table class="table" style="width: 30%;">
                            <tr>
                                <td>Method</td>
                                <td width="1px">: </td>
                                <td><code class="text-success">POST</code></td>
                            </tr>
                            <tr>
                                <td>Endpoint</td>
                                <td width="1px">: </td>
                                <td nowrap=""><code class="text-danger"><?php echo base_url(); ?>api/send-image</code>
                                </td>
                            </tr>
                            <tr>
                                <td>Endpoint</td>
                                <td width="1px">: </td>
                                <td>(FORM DATA)</td>
                            </tr>
                        </table>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>api_key</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>API Key</td>
                                </tr>
                                <tr>
                                    <td>secret_key</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Secret Key</td>
                                </tr>
                                <tr>
                                    <td>from</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Number of your device</td>
                                </tr>
                                <tr>
                                    <td>to</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>recipient number ex 72888xxxx|62888xxxx</td>
                                </tr>
                                <tr>
                                    <td>message</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Messsage to be sent</td>
                                </tr>
                                <tr>
                                    <td>file</td>
                                    <td>string</td>
                                    <td>Yes</td>
                                    <td>Link image URL</td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                        <p>Example Form Data Request</p>
                        <div class="bg-dark text-white">
                            <div style="overflow:auto;">
                                <img src="<?php echo base_url() ?>assets/send_image.png" width="100%" alt=""
                                    class="border-image">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
