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

        // Cek di master_admin
        $checkUser = $this->db->get_where('master_admin', [
            'username' => $username,
            'password' => $password_md5
        ]);

        if ($checkUser->num_rows() >= 1) {
            $data = $checkUser->row_array();
            $this->db->update('master_admin', ['login_at' => date('Y-m-d H:i:s')], ['username' => $username]);
            $this->session->set_userdata('username', $data);
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
            redirect('./dashboard');
            return;
        }

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