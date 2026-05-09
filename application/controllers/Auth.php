<?php
defined('BASEPATH') or exit('No direct script access allowed');

class auth extends CI_Controller
{

    public function __construct()
    {
        error_reporting(0);
        parent::__construct();
        $this->load->library('session');
    }
    public function index()
    {
        if (!empty($this->session->userdata['username'])) {
            redirect('./dashboard');
        } else {
            $this->login();
        }

    }
    public function login()
    {
        $this->load->view('login');
    }
    public function logout()
    {
        session_destroy();
        redirect('./login');
    }

    public function doLogin()
    {
        // Load form validation library
        $this->load->library('form_validation');
        $this->form_validation->set_rules('username', 'Username', 'required|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', 'Username dan Password wajib diisi.');
            redirect('./login');
            return;
        }

        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);
        $password_md5 = md5($password);
        $ip = $this->input->ip_address();
        $ua = $this->input->user_agent();

        // Auto-create tabel log_login jika belum ada
        if (!$this->db->table_exists('log_login')) {
            $this->db->query("
                CREATE TABLE `log_login` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `username` VARCHAR(100) DEFAULT NULL,
                    `nama` VARCHAR(255) DEFAULT NULL,
                    `role` VARCHAR(50) DEFAULT NULL,
                    `user_id` INT(11) DEFAULT NULL,
                    `status` ENUM('success','failed') NOT NULL DEFAULT 'failed',
                    `ip_address` VARCHAR(45) DEFAULT NULL,
                    `user_agent` TEXT DEFAULT NULL,
                    `keterangan` VARCHAR(500) DEFAULT NULL,
                    `login_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_login_at` (`login_at`),
                    KEY `idx_username` (`username`),
                    KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
        }

        // Cek di master_admin
        $checkUser = $this->db->get_where('master_admin', [
            'username' => $username,
            'password' => $password_md5
        ]);

        if ($checkUser->num_rows() >= 1) {
            $data = $checkUser->row_array();
            $this->db->update('master_admin', ['login_at' => date('Y-m-d H:i:s')], ['username' => $username]);
            $this->session->set_userdata('username', $data);

            // Log login berhasil - Admin
            $this->db->insert('log_login', [
                'username' => $username,
                'nama' => $data['nama'] ?? '',
                'role' => $data['role'] ?? 'admin',
                'user_id' => $data['id'] ?? null,
                'status' => 'success',
                'ip_address' => $ip,
                'user_agent' => $ua,
                'keterangan' => 'Login berhasil sebagai Admin',
                'login_at' => date('Y-m-d H:i:s'),
            ]);

            redirect('./dashboard');
            return;
        }

        // Cek di master_koordinator_blok
        $checkKoordinator = $this->db->get_where('master_koordinator_blok', [
            'username' => $username,
            'password' => $password_md5
        ]);

        if ($checkKoordinator->num_rows() >= 1) {
            $data = $checkKoordinator->row_array();
            $this->db->update('master_koordinator_blok', ['login_at' => date('Y-m-d H:i:s')], ['username' => $username]);
            $data['role'] ='koordinator';
            $this->session->set_userdata('username', $data);

            // Log login berhasil - Koordinator
            $this->db->insert('log_login', [
                'username' => $username,
                'nama' => $data['nama'] ?? '',
                'role' => 'koordinator',
                'user_id' => $data['id'] ?? null,
                'status' => 'success',
                'ip_address' => $ip,
                'user_agent' => $ua,
                'keterangan' => 'Login berhasil sebagai Koordinator',
                'login_at' => date('Y-m-d H:i:s'),
            ]);

            redirect('./dashboard');
            return;
        }

        // Login gagal - Log percobaan gagal
        // Cek apakah username ada tapi password salah
        $cek_admin = $this->db->get_where('master_admin', ['username' => $username])->num_rows();
        $cek_koor = $this->db->get_where('master_koordinator_blok', ['username' => $username])->num_rows();
        
        $ket = 'Login gagal - ';
        if ($cek_admin > 0 || $cek_koor > 0) {
            $ket .= 'Password salah (username ditemukan)';
        } else {
            $ket .= 'Username tidak ditemukan';
        }

        $this->db->insert('log_login', [
            'username' => $username,
            'nama' => null,
            'role' => null,
            'user_id' => null,
            'status' => 'failed',
            'ip_address' => $ip,
            'user_agent' => $ua,
            'keterangan' => $ket,
            'login_at' => date('Y-m-d H:i:s'),
        ]);

        // Jika gagal login
        $this->session->set_flashdata('error', 'Username atau Password salah.');
        redirect('./login');
    }
    public function checkSession()
    {
        if (empty($this->session->userdata['username'])) {
            redirect('./');
        }
    }
}
?>