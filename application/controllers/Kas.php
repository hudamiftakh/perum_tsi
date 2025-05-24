<?php
defined('BASEPATH') or exit('No direct script access allowed');

class kas extends CI_Controller
{

	public function __construct()
	{
		error_reporting(1);
		parent::__construct();
		$this->load->library('session');
		$this->load->library('pagination');
		$this->load->library('session');
		$this->load->model('Kas_model');
	}

	public function checkSession()
	{
		if (empty($this->session->userdata['username'])) {
			redirect('./');
		}
	}

	public function pengeluaran()
	{

        $this->load->model('kas_model');
		$this->checkSession();
        $id = $this->input->post('id');
		if (isset($id)) {
			$id = $this->input->post('id', true);  // XSS clean

			if (is_numeric($id)) {
				$id = (int) $id;

				// Start transaction for data consistency
				$this->db->trans_start();
				// delete pengeluaran
				$this->db->where('id', $id)->delete('t_pengeluaran');

				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE) {
					$this->session->set_flashdata('error', 'Gagal menghapus data pengeluaran.');
				} else {
					$this->session->set_flashdata('success', 'Data pengeluaran berhasil dihapus.');
				}
			} else {
				$this->session->set_flashdata('error', 'ID tidak valid.');
			}

			redirect(current_url()); // Refresh to prevent form resubmission
		}
		$data['data'] = $this->kas_model->get_all();
        $data['halaman'] = 'pengeluaran/index';
		$this->load->view('modul', $data);
	}

    public function form($id='')
	{
        $this->load->model('kas_model');

		$this->checkSession();
        $data['option_pengeluaran'] = $this->kas_model->get_option();
        if ($id!='') {
            $idx = decrypt_url($this->uri->segment(2));
           $data['result'] = $this->kas_model->get_data_by_id($idx);
        }
        
        $data['halaman'] = 'pengeluaran/form_pengeluaran';
		$this->load->view('modul', $data);
	}
    function save_pengeluaran(){
       $pengeluaran_data = [
            'jenis_pengeluaran' => $this->input->post('jenis_pengeluaran'),
            'deskripsi' => $this->input->post('deskripsi'),
            'nominal' => str_replace('.', '', $this->input->post('nominal')),
            'tanggal' => $this->input->post('tanggal'),
            'keterangan' => $this->input->post('keterangan'),
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s"),
        ];

        $this->db->insert('t_pengeluaran', $pengeluaran_data);
        $pengeluaran = $this->db->insert_id();

		if (!$pengeluaran) {
			echo json_encode(['status' => 'failed', 'message' => 'Tidak Berhasil Menyimpan Data']);
		}else{
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
        }
       
    } 
    function edit_pengeluaran(){
       $pengeluaran_edit = [
            'jenis_pengeluaran' => $this->input->post('jenis_pengeluaran'),
            'deskripsi' => $this->input->post('deskripsi'),
            'nominal' => str_replace('.', '', $this->input->post('nominal')),
            'tanggal' => $this->input->post('tanggal'),
            'keterangan' => $this->input->post('keterangan'),
            'updated_at' => date("Y-m-d H:i:s"),
        ];
        $this->db->where('id', $this->input->post('id'));
        $this->db->update('t_pengeluaran', $pengeluaran_edit);
        if ($this->db->affected_rows() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'Tidak Berhasil Menyimpan Data']);
        }
    }
}

?>