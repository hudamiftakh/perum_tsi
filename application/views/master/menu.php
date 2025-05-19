<nav class="sidebar-nav scroll-sidebar" data-simplebar>
    <ul id="sidebarnav">
        <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">Menu Aplikasi</span>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link sidebar-link" href="<?php echo base_url('dashboard') ?>" aria-expanded="false">
                <span class="rounded-3">
                    <i class="ti ti-layout-grid"></i>
                </span>
                <span class="hide-menu"> Dashboard</span>
            </a>
        </li>
        <?php 
			$menu = $this->uri->segment(1);
			$submenu = $this->uri->segment(2);
		?>
        <?php if($_SESSION['username']['role']=='admin') : ?>
        <li class="sidebar-item">
            <a class="sidebar-link has-arrow <?php echo (in_array($menu, array('agenda'))) ? 'active' : ''; ?>"
                href="javascript:void(0)" aria-expanded="false">
                <span class="d-flex">
                    <i class="ti ti-calendar-event"></i>
                </span>
                <span class="hide-menu">Rapat</span>
            </a>
            <ul aria-expanded="false"
                class="collapse first-level <?php echo (in_array($menu, array('agenda'))) ? 'in' : ''; ?>">
                <li class="sidebar-item">
                    <a href="<?php echo base_url('agenda/agenda') ?>"
                        class="sidebar-link <?php echo (in_array($submenu, array('agenda', 'contact-group'))) ? 'active' : ''; ?>">
                        <div class="round-16 d-flex align-items-center justify-content-center">
                            <i class="ti ti-circle"></i>
                        </div>
                        <span class="hide-menu">Agenda</span>
                    </a>
                </li>
                <!-- <li class="sidebar-item">
                    <a href="<?php echo base_url('agenda/report_agenda') ?>"
                        class="sidebar-link <?php echo (in_array($submenu, array('report_agenda'))) ? 'active' : ''; ?>">
                        <div class="round-16 d-flex align-items-center justify-content-center">
                            <i class="ti ti-circle"></i>
                        </div>
                        <span class="hide-menu">Report Agenda</span>
                    </a>
                </li> -->
            </ul>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link has-arrow <?php echo (in_array($menu, array('warga'))) ? 'active' : ''; ?>"
                href="javascript:void(0)" aria-expanded="false">
                <span class="d-flex">
                    <i class="ti ti-users"></i>
                </span>
                <span class="hide-menu">Warga</span>
            </a>
            <ul aria-expanded="false"
                class="collapse first-level <?php echo (in_array($menu, array('warga'))) ? 'in' : ''; ?>">
                <li class="sidebar-item">
                    <a href="<?php echo base_url('warga/warga') ?>"
                        class="sidebar-link <?php echo (in_array($submenu, array('warga', 'contact-group'))) ? 'active' : ''; ?>">
                        <div class="round-16 d-flex align-items-center justify-content-center">
                            <i class="ti ti-circle"></i>
                        </div>
                        <span class="hide-menu">Data Keluarga</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('warga/laporan-pembayaran') ?>"
                        class="sidebar-link <?php echo (in_array($submenu, array('warga', 'contact-group'))) ? 'active' : ''; ?>">
                        <div class="round-16 d-flex align-items-center justify-content-center">
                            <i class="ti ti-circle"></i>
                        </div>
                        <span class="hide-menu">Laporan Pembayaran</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="<?php echo base_url('warga/verifikasi-pembayaran') ?>"
                        class="sidebar-link <?php echo (in_array($submenu, array('warga', 'contact-group'))) ? 'active' : ''; ?>">
                        <div class="round-16 d-flex align-items-center justify-content-center">
                            <i class="ti ti-circle"></i>
                        </div>
                        <span class="hide-menu">Verifikasi Pembayaran</span>
                    </a>
                </li>
            </ul>
        </li>
        <?php endif; ?>
        <li class="sidebar-item">
            <a class="sidebar-link sidebar-link" href="<?php echo base_url() ?>logout" aria-expanded="false">
                <span class="rounded-3">
                    <i class="ti ti-logout"></i>
                </span>
                <span class="hide-menu"> Logout</span>
            </a>
        </li>
    </ul>
    <div class="unlimited-access hide-menu bg-light-primary position-relative my-7 rounded">
        <div class="d-flex">
            <div class="unlimited-access-title">
                <h6 class="fw-semibold fs-4 mb-6 text-dark w-85" style="color: orange">Ekinerja KSH @
                    <?= date('Y'); ?>
                </h6>
                <!-- <button class="btn btn-primary fs-2 fw-semibold lh-sm">Homepage</button> -->
            </div>
            <div class="unlimited-access-img">
                <img src="<?php echo base_url(); ?>dist/images/backgrounds/rocket.png" style="width: 100%" alt=""
                    class="img-fluid">
            </div>
        </div>
    </div>
</nav>