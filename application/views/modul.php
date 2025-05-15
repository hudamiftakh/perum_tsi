<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Title -->

    <title>PERUM TSI #GODIGITAL</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="handheldfriendly" content="true" />
    <meta name="MobileOptimized" content="width" />
    <meta name="description" content="Mordenize" />
    <meta name="author" content="" />
    <meta name="keywords" content="Mordenize" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>dist/logo_2.png" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>dist/libs/owl.carousel/dist/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/style.min.css" />
    <link rel="stylesheet" href="<?php echo base_url('dist/whatsapp/css/whatsapp-editor.css'); ?>" />
    <link rel="stylesheet"
        href="<?php echo base_url('dist/material-design-iconic-font/css/material-design-iconic-font.css'); ?>" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>dist/libs/dropzone/dropzone.min.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet"
        href="<?php echo base_url(); ?>dist/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css">
    <link href="<?= base_url('dist/font/css/font-awesome.css') ?>" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <link rel="stylesheet" href="<?php echo base_url('dist/css/whatsapp.css'); ?>" />
    <script src="<?php echo base_url(); ?>dist/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="<?php echo base_url(); ?>dist/js/datatable/datatable-basic.init.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>
    <style type="text/css">
    .nav-icon-hover {
        display: none;
    }

    #main-wrapper[data-layout=vertical] .app-header.fixed-header .navbar {
        background: #008d4c !important;
        padding: 0 0px !important;
        border-radius: 0px !important;
        box-shadow: none !important;
        margin-top: 0px !important;
    }

    input[type="checkbox"] {
        width: 23px;
        height: 23px;
    }

    /* .custom-control-input:focus~.custom-control-indicator {
      -webkit-box-shadow: 0 0 0 1px #fff, 0 0 0 3px #0275d8;
      box-shadow: 0 0 0 1px #fff, 0 0 0 3px #0275d8;
    } */

    /* Styling checkbox border */
    .custom-checkbox .form-check-input {
        width: 20px;
        height: 20px;
        border: 2px solid #adb5bd;
        border-radius: 4px;
    }

    /* Styling checked state */
    .custom-checkbox .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }

    table.fixedHeader-floating {
        position: fixed !important;
        background-color: white;
    }

    table.fixedHeader-floating.no-footer {
        border-bottom-width: 0;
    }

    table.fixedHeader-locked {
        position: absolute !important;
        background-color: white;
    }

    @media print {
        table.fixedHeader-floating {
            display: none;
        }
    }

    .nowrap {
        white-space: nowrap;
    }

    .card2 {
        position: relative !important;
        display: flex !important;
        flex-direction: column !important;
        min-width: 0 !important;
        word-wrap: break-word !important;
        background-color: #fff !important;
        background-clip: border-box !important;
        border: 1px solid rgba(0, 0, 0, .2) !important;
        border-radius: 0.25rem;
        color: black;
    }

    .card-body2 {
        flex: 1 1 auto;
        padding: 1rem 1rem;
    }

    .card-header2 {
        padding: 0.5rem 1rem;
        margin-bottom: 0;
        background-color: rgba(0, 0, 0, .03);
        border-bottom: 1px solid rgba(0, 0, 0, .125);
    }

    .img-fluid {
        max-width: 1000px !important;
        height: auto;
    }
    </style>
</head>

<body>
    <!--<div class="preloader">-->
    <!--  <img src="<?php echo base_url() ?>logo.svg" alt="loader" class="lds-ripple img-fluid"-->
    <!--    style="width: 16vh; height: 300" /> <BR>-->
    <!--  <center style="font-weight: bold; padding-top: 30%; color: orange;"><label-->
    <!--      style=" padding: 8px;border-radius: 10px">#Ekinerja KSH <label style="color: green">GODIGITAL</label></label>-->
    <!--  </center>-->
    <!--</div>-->
    <div class="page-wrapper mini-sidebar show-sidebar" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6"
        data-boxed-layout="full" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <aside class="left-sidebar">
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between"
                    style="background-color: #008d4c">
                    <a href="#" class="text-nowrap logo-img">
                        <!-- <table>
            <tr>
              <td width="50%" style="text-align: right;"><img src="<?php echo base_url(); ?>assets/logo_min.png" class="dark-logo" width="39" alt="" /></td>
              <td width="50%" style="text-align: left; line-height: 15px; padding-left: 2px">
                <label style="font-weight: bold; color: white; font-size: 19px; padding-top: 10px">E-Absensi</label><br>
                <label style="color: white; font-size: 11px">Min 1 Jombang</label>
              </td>
            </tr>
          </table> -->
                        <table style="padding-left: 0px" width="30%">
                            <tr>
                                <!--  <td width="1px">
                    <a href="./" style="color: white"><i style="font-size: 25px" class="ti ti-arrow-left"></i></a>
                  </td> -->
                                <td width="5%" style="text-align: right;">
                                    <!-- <img src="<?php echo base_url() ?>assets/logo_min.png"
                    class="dark-logo" width="39" alt="" /> -->
                                </td>
                                <td width="50%" style="text-align: left; line-height: 15px; padding-left: 2px">
                                    <label
                                        style="font-weight: bold; color: white; font-size: 19px; padding-top: 10px">Paguyuban
                                        Perum
                                        <label style="color: #f9ca24; font-weight: bold;">TSI</label></label>
                                </td>
                            </tr>
                        </table>

                    </a>
                    <div class="close-btn d-lg-none d-block sidebartoggler cursor-pointer text-white"
                        id="sidebarCollapse">
                        <i class="ti ti-x fs-8 text-white"></i>
                    </div>
                </div>
                <?php include 'master/menu.php'; ?>
            </div>
        </aside>
        <div class="body-wrapper">
            <header class="app-header" style="background-color: #008d4c">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link sidebartoggler ms-n3" id="headerCollapse" href="javascript:void(0)">
                                <i class="ti ti-menu-2 text-white" style="color: white"></i>
                            </a>
                        </li>
                    </ul>
                    <div class="navbar-collapse justify-content-end collapse" id="navbarNav" style="">
                        <div class="d-flex align-items-center justify-content-between">
                            <a href="javascript:void(0)"
                                class="nav-link d-flex d-lg-none align-items-center justify-content-center"
                                type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar"
                                aria-controls="offcanvasWithBothOptions">
                                <i class="ti ti-align-justified fs-7"></i>
                            </a>
                            <?php $userData = $this->session->userdata('username'); ?>
                            <ul class="navbar-nav flex-row ms-auto align-items-right justify-content-center">
                                <li class="nav-item dropdown">
                                    <a class="nav-link pe-0" href="javascript:void(0)" id="drop1"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <div class="d-flex align-items-center">
                                            <div class="user-profile-img">
                                                <img src="<?php echo base_url() ?>assets/user.png"
                                                    class="rounded-circle" width="35" height="35" alt="">
                                            </div>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up"
                                        aria-labelledby="drop1">
                                        <div class="message-body">
                                            <div class="py-8 px-7 mt-8 d-flex align-items-center">
                                                <span
                                                    class="d-flex align-items-center justify-content-center bg-light rounded-10">
                                                    <img src="<?php echo base_url() ?>assets/user.png" alt="" width="35"
                                                        height="35">
                                                </span>
                                                <div class="w-75 d-inline-block v-middle ps-3">
                                                    <div class="ms-3">
                                                        <h5 class="mb-1 fs-3">Halo !!,
                                                            <?php echo $userData['nama']; ?>
                                                        </h5>
                                                        <span class="mb-1 d-block"><i class="ti ti-user fs-4"></i>
                                                            <?php echo $userData['username']; ?>
                                                        </span>
                                                        <p class="mb-0 d-flex align-items-center gap-2">
                                                            <i class="ti ti-calendar fs-4"></i>
                                                            <?php echo $userData['login_at']; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="message-body">

                                        </div>
                                        <div class="d-grid py-4 px-7 pt-8">
                                            <a href="<?php echo base_url(); ?>logout"
                                                class="btn btn-outline-primary">Log Out</a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>
            </header>
            <div class="container-fluid mw-87">
                <?php $this->load->view($halaman, array('result' => $result, 'start' => $start)); ?>
            </div>
            <script src="<?php echo base_url(); ?>dist/libs/simplebar/dist/simplebar.min.js"></script>
            <script src="<?php echo base_url(); ?>dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
            <script src="<?php echo base_url(); ?>dist/js/app.min.js"></script>
            <script src="<?php echo base_url(); ?>dist/js/app.minisidebar.init.js"></script>
            <script src="<?php echo base_url(); ?>dist/js/sidebarmenu.js"></script>
            <script src="<?php echo base_url(); ?>dist/js/custom.js"></script>
            <script src="<?php echo base_url(); ?>dist/js/ckeditor.js"></script>
            <script src="<?php echo base_url(); ?>dist/libs/dropzone/dropzone.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script src="<?php echo base_url('dist/whatsapp/js/whatsapp-editor.js'); ?>"></script>

</body>

</html>