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

        $checked = true;
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
