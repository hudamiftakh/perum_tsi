<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Log Helper
 * Berfungsi untuk memastikan seluruh tabel log otomatis dibuat jika belum ada di database,
 * serta menyediakan fungsi utilitas pencatatan log sistem.
 */

if (!function_exists('ensure_log_tables_exist')) {
    function ensure_log_tables_exist()
    {
        static $checked = false;
        if ($checked) {
            return;
        }

        $CI =& get_instance();
        if (!isset($CI->db)) {
            $CI->load->database();
        }

        // 1. Log Login
        if (!$CI->db->table_exists('log_login')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_login` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `username` VARCHAR(100) DEFAULT NULL COMMENT 'Username yang diinput',
                    `nama` VARCHAR(255) DEFAULT NULL COMMENT 'Nama user jika login berhasil',
                    `role` VARCHAR(50) DEFAULT NULL COMMENT 'Role: admin/koordinator/bendahara',
                    `user_id` INT(11) DEFAULT NULL COMMENT 'ID user di tabel master',
                    `status` ENUM('success','failed') NOT NULL DEFAULT 'failed' COMMENT 'Hasil login',
                    `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address client',
                    `user_agent` TEXT DEFAULT NULL COMMENT 'Browser/device info',
                    `keterangan` VARCHAR(500) DEFAULT NULL COMMENT 'Catatan tambahan',
                    `login_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu login',
                    PRIMARY KEY (`id`),
                    KEY `idx_login_at` (`login_at`),
                    KEY `idx_username` (`username`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log aktivitas login';
            ");
        }

        // 2. Log Verifikasi
        if (!$CI->db->table_exists('log_verifikasi')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_verifikasi` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `pembayaran_id` INT(11) DEFAULT NULL COMMENT 'ID di master_pembayaran',
                    `admin_id` INT(11) DEFAULT NULL COMMENT 'ID admin/verifikator',
                    `admin_username` VARCHAR(100) DEFAULT NULL,
                    `admin_nama` VARCHAR(255) DEFAULT NULL,
                    `admin_role` VARCHAR(50) DEFAULT NULL,
                    `aksi` VARCHAR(50) DEFAULT NULL COMMENT 'verified/rejected',
                    `status_sebelum` VARCHAR(50) DEFAULT NULL COMMENT 'Status sebelum aksi',
                    `status_sesudah` VARCHAR(50) DEFAULT NULL COMMENT 'Status sesudah aksi',
                    `warga_nama` VARCHAR(255) DEFAULT NULL,
                    `warga_alamat` VARCHAR(255) DEFAULT NULL,
                    `bulan_bayar` DATE DEFAULT NULL,
                    `jumlah_bayar` DECIMAL(15,2) DEFAULT 0,
                    `pembayaran_via` VARCHAR(50) DEFAULT NULL COMMENT 'koordinator/transfer',
                    `tanggal_bayar` DATE DEFAULT NULL,
                    `wa_status` VARCHAR(20) DEFAULT NULL COMMENT 'success/failed/skipped/queue',
                    `wa_no_tujuan` VARCHAR(20) DEFAULT NULL COMMENT 'Nomor HP tujuan WA',
                    `wa_response` TEXT DEFAULT NULL COMMENT 'Response dari WA gateway',
                    `wa_error` TEXT DEFAULT NULL COMMENT 'Error message jika WA gagal',
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `user_agent` TEXT DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_created_at` (`created_at`),
                    KEY `idx_pembayaran_id` (`pembayaran_id`),
                    KEY `idx_aksi` (`aksi`),
                    KEY `idx_wa_status` (`wa_status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log aktivitas verifikasi pembayaran';
            ");
        }

        // 3. Log Koordinator
        if (!$CI->db->table_exists('log_koordinator')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_koordinator` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `id_koordinator` INT(11) DEFAULT NULL,
                    `nama_koordinator` VARCHAR(255) DEFAULT NULL,
                    `blok` VARCHAR(50) DEFAULT NULL,
                    `aksi` VARCHAR(100) DEFAULT NULL COMMENT 'entry_pembayaran/reminder/kirim_rekap',
                    `jumlah_rumah` INT(11) DEFAULT 0,
                    `nominal_total` DECIMAL(15,2) DEFAULT 0,
                    `keterangan` TEXT DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_koordinator` (`id_koordinator`),
                    KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log aktivitas koordinator blok';
            ");
        }

        // 4. Log Aktivitas Umum
        if (!$CI->db->table_exists('log_aktivitas')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_aktivitas` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` INT(11) DEFAULT NULL,
                    `username` VARCHAR(100) DEFAULT NULL,
                    `role` VARCHAR(50) DEFAULT NULL,
                    `modul` VARCHAR(100) DEFAULT NULL,
                    `aksi` VARCHAR(100) DEFAULT NULL,
                    `deskripsi` TEXT DEFAULT NULL,
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `user_agent` TEXT DEFAULT NULL,
                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_user` (`user_id`),
                    KEY `idx_modul` (`modul`),
                    KEY `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log aktivitas umum sistem';
            ");
        }

        // 5. Log Teguran
        if (!$CI->db->table_exists('log_teguran')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_teguran` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY, 
                    `id_rumah` INT, 
                    `tgl_kirim` DATETIME, 
                    `dikirim_ke` VARCHAR(20), 
                    `status` VARCHAR(20), 
                    `nominal` INT,
                    `bulan_tunggak` TEXT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // 6. Log Konfirmasi
        if (!$CI->db->table_exists('log_konfirmasi')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_konfirmasi` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `id_rumah` INT,
                    `tipe` VARCHAR(20),
                    `tgl_kirim` DATETIME,
                    `dikirim_ke` VARCHAR(20),
                    `status` VARCHAR(20),
                    `tahun` INT,
                    `bulan` INT,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // 7. Log Akses Siteplan (Pemantauan Kunjungan Koordinator)
        if (!$CI->db->table_exists('log_akses_siteplan')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `log_akses_siteplan` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id` INT(11) DEFAULT NULL,
                    `username` VARCHAR(100) DEFAULT NULL,
                    `nama` VARCHAR(255) DEFAULT NULL,
                    `role` VARCHAR(50) DEFAULT NULL,
                    `id_koordinator` INT(11) DEFAULT NULL,
                    `blok` VARCHAR(150) DEFAULT NULL,
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `user_agent` TEXT DEFAULT NULL,
                    `device` VARCHAR(100) DEFAULT NULL,
                    `filter_tahun` INT(4) DEFAULT NULL,
                    `filter_bulan` INT(2) DEFAULT NULL,
                    `accessed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_accessed_at` (`accessed_at`),
                    KEY `idx_user_id` (`user_id`),
                    KEY `idx_role` (`role`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log akses halaman siteplan';
            ");
        }

        $checked = true;
    }
}

/**
 * Catat log akses halaman siteplan
 */
if (!function_exists('catat_log_akses_siteplan')) {
    function catat_log_akses_siteplan($filter_tahun = null, $filter_bulan = null)
    {
        ensure_log_tables_exist();
        $CI =& get_instance();
        $sess = $CI->session->userdata('username');
        if (!$sess || !is_array($sess)) return;

        $userId = $sess['id'] ?? null;
        $role = $sess['role'] ?? 'guest';
        $nama = $sess['nama'] ?? ($sess['username'] ?? 'User');
        $username = $sess['username'] ?? '';

        // Ambil info blok jika role adalah koordinator
        $id_koor = ($role === 'koordinator') ? $userId : null;
        $blok = null;
        if ($id_koor) {
            $koor = $CI->db->select('nama')->where('id', $id_koor)->get('master_koordinator_blok')->row_array();
            $blok = $koor['nama'] ?? ('Koordinator ID ' . $id_koor);
        }

        // Deteksi jenis perangkat pengguna
        $ua = $CI->input->user_agent();
        $device = 'Laptop / PC';
        if (preg_match('/iPhone/i', $ua)) {
            $device = 'iPhone';
        } elseif (preg_match('/iPad/i', $ua)) {
            $device = 'iPad';
        } elseif (preg_match('/Android/i', $ua)) {
            $device = 'HP Android';
        } elseif (preg_match('/Mobile/i', $ua)) {
            $device = 'Smartphone';
        } elseif (preg_match('/Macintosh|Mac OS/i', $ua)) {
            $device = 'Mac Desktop';
        } elseif (preg_match('/Windows/i', $ua)) {
            $device = 'Windows PC';
        } elseif (preg_match('/Linux/i', $ua)) {
            $device = 'Linux PC';
        }

        $CI->db->insert('log_akses_siteplan', [
            'user_id'        => $userId,
            'username'       => $username,
            'nama'           => $nama,
            'role'           => $role,
            'id_koordinator' => $id_koor,
            'blok'           => $blok,
            'ip_address'     => $CI->input->ip_address(),
            'user_agent'     => $ua,
            'device'         => $device,
            'filter_tahun'   => $filter_tahun,
            'filter_bulan'   => $filter_bulan,
            'accessed_at'    => date('Y-m-d H:i:s')
        ]);
    }
}

/**
 * Catat log aktivitas umum
 */
if (!function_exists('catat_log_aktivitas')) {
    function catat_log_aktivitas($modul, $aksi, $deskripsi = '')
    {
        ensure_log_tables_exist();
        $CI =& get_instance();
        $sess = $CI->session->userdata('username');
        
        $data = [
            'user_id'    => is_array($sess) ? ($sess['id'] ?? null) : null,
            'username'   => is_array($sess) ? ($sess['username'] ?? null) : null,
            'role'       => is_array($sess) ? ($sess['role'] ?? null) : null,
            'modul'      => $modul,
            'aksi'       => $aksi,
            'deskripsi'  => $deskripsi,
            'ip_address' => $CI->input->ip_address(),
            'user_agent' => $CI->input->user_agent(),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        $CI->db->insert('log_aktivitas', $data);
    }
}

/**
 * Memastikan skema database dan tabel-tabel penting otomatis terbuat jika belum ada di database.
 * Jika tabel inti belum ada (misal di server baru / SSH baru), otomatis mengimpor tsi.sql.
 */
if (!function_exists('ensure_database_schema_exist')) {
    function ensure_database_schema_exist($force = false)
    {
        static $schema_checked = false;
        if ($schema_checked && !$force) {
            return;
        }

        $CI =& get_instance();
        if (!isset($CI->db)) {
            $CI->load->database();
        }

        // 1. Pastikan tabel-tabel log terbuat
        ensure_log_tables_exist();

        // 2. Cek apakah tabel master_rumah atau master_admin belum ada sama sekali
        if (!$CI->db->table_exists('master_rumah') || !$CI->db->table_exists('master_admin')) {
            $sql_file = FCPATH . 'tsi.sql';
            if (file_exists($sql_file)) {
                $sql_content = file_get_contents($sql_file);
                // Bersihkan comment dan delimiter
                $queries = explode(";\n", $sql_content);
                foreach ($queries as $q) {
                    $q = trim($q);
                    if (!empty($q)) {
                        @$CI->db->query($q);
                    }
                }
            }
        }

        // 3. Pastikan kolom polygon_coords ada pada tabel master_rumah
        if ($CI->db->table_exists('master_rumah')) {
            if (!$CI->db->field_exists('polygon_coords', 'master_rumah')) {
                @$CI->db->query("ALTER TABLE `master_rumah` ADD COLUMN `polygon_coords` LONGTEXT NULL DEFAULT NULL");
            }
            // Kolom polygon_coords siap digunakan untuk plotting presisi oleh admin
            // (Auto-seed kasar dinonaktifkan agar denah tidak kotor oleh koordinat melenceng)
        }

        // 4. Pastikan tabel master_siteplan_meta ada (untuk setting/posisi default map)
        if (!$CI->db->table_exists('master_siteplan_meta')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `master_siteplan_meta` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `key_name` VARCHAR(100) NOT NULL UNIQUE,
                    `value_data` LONGTEXT NULL,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // 5. Penyelarasan collation master_keluarga & master_rumah
        try {
            $cols = $CI->db->query("
                SELECT TABLE_NAME, COLUMN_NAME, COLLATION_NAME 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME IN ('master_keluarga', 'master_rumah') 
                AND COLUMN_NAME IN ('nomor_rumah', 'alamat')
            ")->result_array();

            $needs_alter = false;
            foreach ($cols as $c) {
                if (!empty($c['COLLATION_NAME']) && $c['COLLATION_NAME'] !== 'utf8mb4_general_ci') {
                    $needs_alter = true;
                    break;
                }
            }

            if ($needs_alter) {
                @$CI->db->query("ALTER TABLE master_keluarga MODIFY nomor_rumah VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
                @$CI->db->query("ALTER TABLE master_rumah MODIFY alamat VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
            }
        } catch (\Throwable $e) {
        }

        // 6. Pastikan tabel status_sampah_rumah ada (kustomisasi pengambilan sampah per rumah & bulan)
        if (!$CI->db->table_exists('status_sampah_rumah')) {
            $CI->db->query("
                CREATE TABLE IF NOT EXISTS `status_sampah_rumah` (
                    `id` INT(11) NOT NULL AUTO_INCREMENT,
                    `id_rumah` INT(11) NOT NULL,
                    `periode_bulan` VARCHAR(7) NOT NULL,
                    `status_sampah` ENUM('ambil', 'tahan') NOT NULL DEFAULT 'ambil',
                    `keterangan` VARCHAR(255) DEFAULT NULL,
                    `updated_by` VARCHAR(100) DEFAULT NULL,
                    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `idx_rumah_periode` (`id_rumah`, `periode_bulan`),
                    KEY `idx_periode` (`periode_bulan`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        // 7. Pastikan akun Koordinator Lingkungan tersedia di master_admin
        if ($CI->db->table_exists('master_admin')) {
            $checkLingkungan = $CI->db->get_where('master_admin', ['username' => 'lingkungan'])->num_rows();
            if ($checkLingkungan == 0) {
                $max_id_row = $CI->db->select_max('id')->get('master_admin')->row();
                $new_id = ($max_id_row && isset($max_id_row->id)) ? ((int)$max_id_row->id + 1) : 10;
                @$CI->db->insert('master_admin', [
                    'id'       => $new_id,
                    'nama'     => 'Koordinator Lingkungan TSI',
                    'username' => 'lingkungan',
                    'password' => md5('lingkungan123'),
                    'role'     => 'koordinator_lingkungan',
                    'login_at' => null
                ]);
            }
        }

        $schema_checked = true;
    }
}

if (!function_exists('_seed_initial_siteplan_polygons')) {
    function _seed_initial_siteplan_polygons($CI)
    {
        $houses = $CI->db->select('id, alamat')->order_by('id', 'ASC')->get('master_rumah')->result_array();
        if (empty($houses)) return;

        $block_configs = [
            'Ruha' => ['lat_top' => 74.0, 'lat_bot' => 70.0, 'lng_left' => -20.0, 'lng_right' => 20.0],
            'Blok I' => ['lat_top' => 69.0, 'lat_bot' => 66.0, 'lng_left' => -10.0, 'lng_right' => 25.0],
            'Blok II' => ['lat_top' => 65.5, 'lat_bot' => 58.5, 'lng_left' => -12.0, 'lng_right' => 38.0],
            'Blok III' => ['lat_top' => 57.0, 'lat_bot' => 49.0, 'lng_left' => -10.0, 'lng_right' => 38.0],
            'Blok IIIB' => ['lat_top' => 48.0, 'lat_bot' => 43.0, 'lng_left' => -8.0, 'lng_right' => 35.0],
            'Blok IV' => ['lat_top' => 42.0, 'lat_bot' => 33.0, 'lng_left' => -10.0, 'lng_right' => 40.0],
            'Blok V' => ['lat_top' => 31.5, 'lat_bot' => 19.0, 'lng_left' => -10.0, 'lng_right' => 42.0],
            'Blok VI' => ['lat_top' => 17.5, 'lat_bot' => 4.0, 'lng_left' => -10.0, 'lng_right' => 42.0],
            'Blok VII' => ['lat_top' => 2.5, 'lat_bot' => -13.0, 'lng_left' => -10.0, 'lng_right' => 42.0],
            'Blok VIII' => ['lat_top' => -14.5, 'lat_bot' => -30.0, 'lng_left' => -10.0, 'lng_right' => 42.0],
            'Blok IX' => ['lat_top' => -31.5, 'lat_bot' => -46.0, 'lng_left' => -10.0, 'lng_right' => 40.0],
            'Blok X' => ['lat_top' => -47.5, 'lat_bot' => -58.0, 'lng_left' => -10.0, 'lng_right' => 38.0],
            'Blok XB' => ['lat_top' => -58.5, 'lat_bot' => -64.0, 'lng_left' => -8.0, 'lng_right' => 35.0],
            'Blok XI' => ['lat_top' => -64.5, 'lat_bot' => -72.0, 'lng_left' => -10.0, 'lng_right' => 38.0],
        ];

        $grouped = [];
        foreach ($houses as $h) {
            $alamat = $h['alamat'];
            $assigned = null;
            if (stripos($alamat, 'Ruha') !== false) {
                $assigned = 'Ruha';
            } elseif (stripos($alamat, 'Raya') !== false) {
                $assigned = 'Raya';
            } else {
                foreach ($block_configs as $bname => $cfg) {
                    if (stripos($alamat, $bname) !== false) {
                        $assigned = $bname;
                        break;
                    }
                }
            }
            if (!$assigned) $assigned = 'Blok II';
            $grouped[$assigned][] = $h;
        }

        if (isset($grouped['Raya'])) {
            $rh = $grouped['Raya'];
            $cnt = count($rh);
            $lat_s = 72.0; $lat_e = -70.0;
            $d_lat = ($lat_s - $lat_e) / max(1, $cnt);
            for ($i = 0; $i < $cnt; $i++) {
                $top = $lat_s - ($i * $d_lat);
                $bot = $top - ($d_lat * 0.90);
                $poly = [
                    [round($top, 4), -41.0], [round($top, 4), -23.0],
                    [round($bot, 4), -23.0], [round($bot, 4), -41.0]
                ];
                $CI->db->where('id', $rh[$i]['id'])->update('master_rumah', ['polygon_coords' => json_encode($poly)]);
            }
        }

        foreach ($block_configs as $bname => $cfg) {
            if (!isset($grouped[$bname])) continue;
            $bh = $grouped[$bname];
            $cnt = count($bh);
            if ($cnt == 0) continue;

            $half = ceil($cnt / 2);
            $lat_mid = ($cfg['lat_top'] + $cfg['lat_bot']) / 2;

            $d_lng_top = ($cfg['lng_right'] - $cfg['lng_left']) / max(1, $half);
            for ($i = 0; $i < $half; $i++) {
                $l = $cfg['lng_left'] + ($i * $d_lng_top);
                $r = $l + ($d_lng_top * 0.92);
                $poly = [
                    [round($cfg['lat_top'], 4), round($l, 4)], [round($cfg['lat_top'], 4), round($r, 4)],
                    [round($lat_mid + 0.4, 4), round($r, 4)], [round($lat_mid + 0.4, 4), round($l, 4)]
                ];
                $CI->db->where('id', $bh[$i]['id'])->update('master_rumah', ['polygon_coords' => json_encode($poly)]);
            }

            $bot_cnt = $cnt - $half;
            if ($bot_cnt > 0) {
                $d_lng_bot = ($cfg['lng_right'] - $cfg['lng_left']) / max(1, $bot_cnt);
                for ($i = 0; $i < $bot_cnt; $i++) {
                    $l = $cfg['lng_left'] + ($i * $d_lng_bot);
                    $r = $l + ($d_lng_bot * 0.92);
                    $poly = [
                        [round($lat_mid - 0.4, 4), round($l, 4)], [round($lat_mid - 0.4, 4), round($r, 4)],
                        [round($cfg['lat_bot'], 4), round($r, 4)], [round($cfg['lat_bot'], 4), round($l, 4)]
                    ];
                    $CI->db->where('id', $bh[$half + $i]['id'])->update('master_rumah', ['polygon_coords' => json_encode($poly)]);
                }
            }
        }
    }
}
