<!DOCTYPE html>
<html lang="en">

<head>
  <title>Perum TSI</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="handheldfriendly" content="true" />
  <meta name="MobileOptimized" content="width" />
  <meta name="description" content="Mordenize" />
  <meta name="author" content="" />
  <meta name="keywords" content="Mordenize" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Favicon -->
  <link rel="shortcut icon" type="image/png" href="<?php echo base_url() ?>dist/logo_2.png" />
  <!-- Owl Carousel -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/libs/owl.carousel/dist/assets/owl.carousel.min.css">
  <!-- Core Css -->
  <link rel="stylesheet" href="<?php echo base_url(); ?>dist/css/style.min.css" />
</head>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="<?php echo base_url() ?>dist/logo_2.png" alt="loader" class="lds-ripple img-fluid"
      style="width: 16vh; height: 500" /> <BR>
    <center style="font-weight: bold; padding-top: 30%; color: orange;"><label
        style=" padding: 8px;border-radius: 10px">Paguyuban <label style="color: green">Taman Sukodono Indah</label></label></center>
  </div>
  <!-- Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div class="position-relative overflow-hidden radial-gradient min-vh-100">
      <div class="position-relative z-index-5">
        <div class="row">
          <div class="col-xl-7 col-xxl-8">
            <a href="./" class="text-nowrap logo-img d-block px-4 py-9 w-100">
              <!-- lOGO -->
            </a>
            <div class="d-none d-xl-flex align-items-center justify-content-center" style="height: calc(100vh - 80px);">
              <img src="<?php echo $base_url; ?>dist/images/backgrounds/login-security.svg" alt="" class="img-fluid"
                width="500">
            </div>
          </div>
          <div class="col-xl-5 col-xxl-4">
            <div class="authentication-login min-vh-100 bg-body row justify-content-center align-items-center p-4">
              <div class="col-sm-8 col-md-6 col-xl-9">
                <center>
                  <img src="<?php echo base_url() ?>dist/logo_2.png" width="100px">
                  <br>
                  <br>
                  <h2 class="mb-3 fs-7 fw-bolder" style="font-weight: bold;">Paguyuban <br>Taman Sukodono Indah
                  <br>
                  <br>
                </center>
								<?php if ($this->session->flashdata('error')): ?>
										<div class="alert alert-danger">
												<?php echo $this->session->flashdata('error'); ?>
										</div>
								<?php endif; ?>
								<form action="<?php echo base_url('doLogin'); ?>" method="POST">
                  <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <input type="text" class="form-control" name="username" aria-describedby="emailHelp" placeholder="Username">
                  </div>
                    <div class="mb-4">
                    <label for="exampleInputPassword1" class="form-label">Password</label>
                    <div class="input-group">
                      <input type="password" class="form-control" name="password" id="passwordInput" placeholder="Password">
                      <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                      <span id="toggleIcon">Show</span>
                      </button>
                    </div>
                    </div>
                    <script>
                    document.getElementById('togglePassword').addEventListener('click', function () {
                      var passwordInput = document.getElementById('passwordInput');
                      var toggleIcon = document.getElementById('toggleIcon');
                      if (passwordInput.type === 'password') {
                      passwordInput.type = 'text';
                      toggleIcon.textContent = 'Hide';
                      } else {
                      passwordInput.type = 'password';
                      toggleIcon.textContent = 'Show';
                      }
                    });
                    </script>
                  <div class="d-flex align-items-center justify-content-between mb-4" >
                    <div class="form-check">
                      <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
                      <label class="form-check-label text-dark" for="flexCheckChecked">
                        Remeber this Device
                      </label>
                    </div>
                  </div>
                  <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2">Sign In</button>

                </form>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Import Js Files -->
    <script src="<?php echo base_url(); ?>dist/libs/jquery/dist/jquery.min.js"></script>
    <script src="<?php echo base_url(); ?>dist/libs/simplebar/dist/simplebar.min.js"></script>
    <script src="<?php echo base_url(); ?>dist/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- core files -->
    <script src="<?php echo base_url(); ?>dist/js/app.min.js"></script>
    <script src="<?php echo base_url(); ?>dist/js/app.minisidebar.init.js"></script>
    <script src="<?php echo base_url(); ?>dist/js/app-style-switcher.js"></script>
    <script src="<?php echo base_url(); ?>dist/js/sidebarmenu.js"></script>

    <script src="<?php echo base_url(); ?>dist/js/custom.js"></script>
    <!-- current page js files -->
    <script src="<?php echo base_url(); ?>dist/libs/owl.carousel/dist/owl.carousel.min.js"></script>
</body>

<!-- Mirrored from demos.adminmart.com/premium/bootstrap/modernize-bootstrap/package/html/minisidebar/authentication-login.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 29 May 2023 07:27:07 GMT -->

</html>
