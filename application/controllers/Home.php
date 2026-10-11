<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->helper(['url', 'log']);

        // Auto create / generate database & tabel saat controller di-load
        if (function_exists('ensure_database_schema_exist')) {
            ensure_database_schema_exist();
        }
    }

    public function index()
    {
        // Jika sudah login, arahkan ke dashboard
        if (!empty($this->session->userdata('username'))) {
            redirect('dashboard');
        } else {
            redirect('auth');
        }
    }

    /**
     * Endpoint khusus jika ingin memicu migrasi/generate tabel secara eksplisit
     */
    public function migrate()
    {
        if (function_exists('ensure_database_schema_exist')) {
            ensure_database_schema_exist(true);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Skema database dan seluruh tabel berhasil diverifikasi/digenerate.'
        ]);
    }

    /**
     * Endpoint untuk mereset polygon coords yang salah/berantakan
     */
    public function reset_siteplan_coords()
    {
        $this->db->update('master_rumah', ['polygon_coords' => null]);
        $affected = $this->db->affected_rows();
        echo json_encode([
            'status' => 'success',
            'message' => "Berhasil mereset koordinat polygon ($affected baris direset)."
        ]);
    }
}
