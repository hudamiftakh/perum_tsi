<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Title -->

  <title>Absensi Digital #MIN1JBG</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <!-- <meta name="viewport" content="width=device-width, initial-scale=1" /> -->
  <meta name="handheldfriendly" content="true" />
  <meta name="MobileOptimized" content="width" />
  <meta name="description" content="Mordenize" />
  <meta name="author" content="" />
  <meta name="keywords" content="Mordenize" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <link rel="shortcut icon" type="image/png" href="<?php base_url() ?>assets/logo_min.png" />
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/style.min.css" />
  <script src="<?php echo base_url(); ?>dist/libs/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo base_url(); ?>dist/alert/sweetalert2@9.js"></script>
   <script>
      function behasil_absen() {
          Swal.fire({
            title: 'Berhasil',
            text: 'Terimakasi sudah melakukan absensi',
            icon : 'success',
            timer: 1200,
            showCancelButton: false,
            showConfirmButton: false
          }).then(function() {

          });
      }

      function sudah_pernah_absen() {
          Swal.fire({
            title: 'Maaf !!',
            text: 'Sudah melakukan absen sebelumnya',
            icon : 'error',
            timer: 1400,
            showCancelButton: false,
            showConfirmButton: false
          }).then(function() {
            
          });
      }

      function data_kosong() {
          Swal.fire({
            title: 'Maaf !!',
            text: 'Data siswa tidak ditemukan',
            icon : 'error',
            timer: 1400,
            showCancelButton: false,
            showConfirmButton: false
          }).then(function() {
            
          });
      }
      // data_kosong();
    </script>
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
    .card2 {
      position: relative !important;
      display: flex !important;
      flex-direction: column !important;
      min-width: 0 !important;
      word-wrap: break-word !important;
      background-color: #fff !important;
      background-clip: border-box !important;
      border: 1px solid rgba(0,0,0,.2) !important;
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
      background-color: rgba(0,0,0,.03);
      border-bottom: 1px solid rgba(0,0,0,.125);
    }
  </style>
</head>

<body>
  <div class="page-wrapper">
    <div class="body-wrapper">
      <header class="app-header" style="background-color: #008d4c"> 
        <nav class="navbar navbar-expand-lg navbar-light">
          <ul class="navbar-nav">
            <li class="nav-item" style="width: 1000px; padding-left: 0px !important" >
              <table style="padding-left: 0px" width="30%" >
                <tr>
                 <!--  <td width="1px">
                    <a href="./" style="color: white"><i style="font-size: 25px" class="ti ti-arrow-left"></i></a>
                  </td> -->
                  <td width="5%" style="text-align: right;"><img src="<?php base_url() ?>assets/logo_min.png" class="dark-logo" width="39" alt="" /></td>
                  <td width="50%" style="text-align: left; line-height: 15px; padding-left: 2px">
                    <label style="font-weight: bold; color: white; font-size: 19px; padding-top: 10px">Absensi <label style="color: #f9ca24; font-weight: bold;">Digital</label></label><br>
                    <label style="color: white; font-size: 11px;">MIN I Jombang</label>
                  </td>
                </tr>
              </table>
            </li>
          </ul>
          <div class="navbar-collapse justify-content-end collapse" id="navbarNav" style="">
            <div class="d-flex align-items-center justify-content-between">
              <a href="javascript:void(0)" class="nav-link d-flex d-lg-none align-items-center justify-content-center" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar" aria-controls="offcanvasWithBothOptions">
                <i class="ti ti-align-justified fs-7"></i>
              </a>
              <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-center">
                <li class="nav-item dropdown">
                  <a class="nav-link pe-0" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center">
                      <div class="user-profile-img">
                        <img src="<?php echo base_url(); ?>dist/images/profile/user-1.jpg" class="rounded-circle" width="35" height="35" alt="">
                      </div>
                    </div>
                  </a>
                  <div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">

                    <div class="message-body">
                      <a href="page-user-profile.html" class="py-8 px-7 mt-8 d-flex align-items-center">
                        <span class="d-flex align-items-center justify-content-center bg-light rounded-1 p-6">
                          <img src="<?php echo base_url(); ?>dist/images/profile/user-1.jpg" alt="" width="24" height="24">
                        </span>
                        <div class="w-75 d-inline-block v-middle ps-3">
                          <h6 class="mb-1 bg-hover-primary fw-semibold"> My Profile </h6>
                          <span class="d-block text-dark">Account Settings</span>
                        </div>
                      </a>
                    </div>
                    <div class="d-grid py-4 px-7 pt-8">
                      <a href="<?php echo base_url(); ?>logout" class="btn btn-outline-primary">Log Out</a>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </nav>
      </header>

      <!-- Header End -->

      <div id="halaman" class="container-fluid">
        <?php include $halaman.".php"; ?>
      </div>

      <script type="text/javascript">
        function show_siswa(e) {
          var id = "";
          id =  $("#id").val();

        }
      </script>
     
      <script src="<?php echo base_url(); ?>dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
      <script src="<?php echo base_url(); ?>dist/js/app.min.js"></script>
      <script src="<?php echo base_url(); ?>dist/js/custom.js"></script>
    </body>
    </html>
