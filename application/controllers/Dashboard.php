<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends CI_Controller
{

	public function __construct()
	{
		error_reporting(0);
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
		$this->load->library('pagination');
		$this->load->model('M_Datatables');

		// Auto Create Table Log Teguran jika belum ada
		$this->db->query("CREATE TABLE IF NOT EXISTS log_teguran (
			id INT AUTO_INCREMENT PRIMARY KEY, 
			id_rumah INT, 
			tgl_kirim DATETIME, 
			dikirim_ke VARCHAR(20), 
			status VARCHAR(20), 
			nominal INT,
			bulan_tunggak TEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		)");

		// Auto-create tabel log_login jika belum ada
		$this->_create_log_tables();
	}

	/**
	 * Auto-create tabel log_login dan log_verifikasi jika belum ada
	 */
	private function _create_log_tables()
	{
		// Tabel log_login
		if (!$this->db->table_exists('log_login')) {
			$this->db->query("
				CREATE TABLE `log_login` (
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log semua aktivitas login (berhasil/gagal)'
			");
		}

		// Tabel log_verifikasi
		if (!$this->db->table_exists('log_verifikasi')) {
			$this->db->query("
				CREATE TABLE `log_verifikasi` (
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
					`wa_status` VARCHAR(20) DEFAULT NULL COMMENT 'success/failed/skipped',
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Log semua aktivitas verifikasi pembayaran + status kirim WA'
			");
		}
	}

	/**
	 * Helper: Insert log verifikasi
	 */
	private function _insert_log_verifikasi($data)
	{
		$session = $this->session->userdata('username');
		$log = array_merge([
			'admin_id' => $session['id'] ?? null,
			'admin_username' => $session['username'] ?? null,
			'admin_nama' => $session['nama'] ?? null,
			'admin_role' => $session['role'] ?? null,
			'ip_address' => $this->input->ip_address(),
			'user_agent' => $this->input->user_agent(),
			'created_at' => date('Y-m-d H:i:s'),
		], $data);
		$this->db->insert('log_verifikasi', $log);
	}

	/**
	 * Halaman Log Login
	 */
	public function log_login()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/log_login';
		$this->load->view('modul', $data);
	}

	/**
	 * Halaman Log Verifikasi
	 */
	public function log_verifikasi()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/log_verifikasi';
		$this->load->view('modul', $data);
	}

	public function hook_web()
	{
		header('Content-Type: application/json');

		try {
			// Ambil JSON
			$raw = file_get_contents("php://input");
			$data = json_decode($raw, true);

			if (!$data) {
				echo json_encode([
					'status' => false,
					'message' => 'Invalid JSON'
				]);
				return;
			}

			$insert = [
				'hook'       => json_encode($data),
				'created_at' => date('Y-m-d H:i:s')
			];

			$this->db->insert('master_hook', $insert);

			echo json_encode([
				'status' => true,
				'message' => 'Webhook received'
			]);
		} catch (Exception $e) {
			echo json_encode([
				'status' => false,
				'error' => $e->getMessage()
			]);
		}

		exit;
	}

	public function index()
	{
		// var_dump($this->session->userdata['username']);
		if (!empty($this->session->userdata['username'])) {
			$this->dashboard();
		} else {
			redirect('./login');
			// $this->login();
			// $this->load->view('onboard');
		}
	}

	public function login()
	{
		if (isset($_POST['username'])) {
			try {
				$username = @$_POST['username'];
				$password = md5(@$_POST['password']);
				if (isset($username) && isset($password)) {
					$data = $this->db->get_where('tb_admin', array('username' => $username, 'password' => $password, 'status' => 'aktif'))->row_array();
					if (!empty($data['username'])) {
						$this->session->set_userdata('username', $data);
						redirect('./');
					} else {
						redirect('./login');
					}
				}
			} catch (Exception $e) {
				echo "Error " . $e;
			}
		} else {
			$this->load->view('login');
		}
	}
	public function dashboard()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/index';
		$this->load->view('modul', $data);
	}

	public function agenda()
	{
		$this->checkSession();
		$id = $this->input->post('id');
		if (isset($id)) {
			$id = $this->input->post('id', true);  // ambil id dari form dengan xss clean
			if (is_numeric($id)) {
				$id = (int) $id;
				$this->db->where('id', $id);
				$this->db->delete('master_agenda');

				if ($this->db->affected_rows() > 0) {
					$this->session->set_flashdata('success', 'Agenda berhasil dihapus.');
				} else {
					$this->session->set_flashdata('error', 'Gagal menghapus agenda.');
				}
			} else {
				$this->session->set_flashdata('error', 'ID tidak valid.');
			}

			redirect(current_url()); // Refresh halaman agar tidak submit ulang
		}
		$data['halaman'] = 'dashboard/agenda';
		$this->load->view('modul', $data);
	}

	public function add_agenda()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/add_agenda';
		$this->load->view('modul', $data);
	}

	public function report_agenda()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/report_agenda';
		$this->load->view('modul', $data);
	}

	public function pendataan_keluarga()
	{
		// Ambil semua nomor_rumah dan id dari master_keluarga
		$pengisian = $this->db->select('id, nomor_rumah')->get('master_keluarga')->result_array();

		$alamat_terisi = []; // alamat = key, id terenkripsi = value

		foreach ($pengisian as $item) {
			$alamat_split = explode('|', $item['nomor_rumah']);
			foreach ($alamat_split as $alamat) {
				$trimmed = trim($alamat);
				$alamat_terisi[$trimmed] = encrypt_url($item['id']); // simpan id terenkripsi
			}
		}
		$data['alamat_terisi'] = $alamat_terisi;
		$this->load->view('dashboard/pendataan_keluarga', $data);
	}

	public function pendataan_keluarga_koordinator()
	{
		// Ambil semua nomor_rumah dan id dari master_keluarga
		$pengisian = $this->db->select('id, nomor_rumah')->get('master_keluarga')->result_array();

		$alamat_terisi = []; // alamat = key, id terenkripsi = value

		foreach ($pengisian as $item) {
			$alamat_split = explode('|', $item['nomor_rumah']);
			foreach ($alamat_split as $alamat) {
				$trimmed = trim($alamat);
				$alamat_terisi[$trimmed] = encrypt_url($item['id']); // simpan id terenkripsi
			}
		}
		$data['alamat_terisi'] = $alamat_terisi;
		$this->load->view('dashboard/pendataan_keluarga_backup', $data);
	}

	public function hapus_anggota_keluarga()
	{
		$id = $this->input->get('id', true);  // ambil id dari form dengan xss clean
		$id_keluarga = $this->input->get('id_keluarga', true);  // ambil id dari form dengan xss clean
		if (is_numeric($id)) {
			$id = (int) $id;
			$this->db->where('id', $id);
			$this->db->delete('master_anggota_keluarga');

			if ($this->db->affected_rows() > 0) {
				$this->session->set_flashdata('success', 'KK berhasil hapus Anggota Keluarga.');
			} else {
				$this->session->set_flashdata('error', 'Gagal menghapus Anggota Keluarga.');
			}
			redirect('edit-pendataan-keluarga/' . $id_keluarga); // Refresh halaman agar tidak submit ulang
		}
	}

	public function edit_pendataan_keluarga()
	{
		$this->load->view('dashboard/edit_pendataan_keluarga');
	}

	public function edit_pendataan_keluarga_koordinator()
	{
		$this->load->view('dashboard/edit_pendataan_keluarga_backup');
	}

	public function warga()
	{
		// Check session
		$this->checkSession();

		// Handle delete action
		$id = $this->input->post('id');
		if (isset($id)) {
			$id = $this->input->post('id', true);  // XSS clean

			if (is_numeric($id)) {
				$id = (int) $id;

				// Start transaction for data consistency
				$this->db->trans_start();

				// First delete anggota keluarga
				$this->db->where('keluarga_id', $id)->delete('master_anggota_keluarga');

				// Then delete kepala keluarga
				$this->db->where('id', $id)->delete('master_keluarga');

				$this->db->trans_complete();

				if ($this->db->trans_status() === FALSE) {
					$this->session->set_flashdata('error', 'Gagal menghapus data keluarga.');
				} else {
					$this->session->set_flashdata('success', 'Data keluarga berhasil dihapus.');
				}
			} else {
				$this->session->set_flashdata('error', 'ID tidak valid.');
			}

			redirect(current_url()); // Refresh to prevent form resubmission
		}

		// Setup pagination and search
		$this->load->library('pagination');

		$keyword = $this->input->get('keyword', true); // XSS clean
		$page = $this->uri->segment(3, 0);

		// Main query with search condition
		// $this->db->select('*')->from('master_keluarga')->order_by('created_at', 'DESC');
		$subquery = $this->db->select('keluarga_id')
			->from('master_anggota_keluarga')
			->group_start()
			->like('nik', $keyword)
			->or_like('nama', $keyword)
			->group_end()
			->get_compiled_select();

		$this->db->select('mk.*, mr.nama as nama_pemilik')
			->from('master_keluarga mk')
			->join('master_rumah mr', "CONCAT('|', mk.nomor_rumah, '|') LIKE CONCAT('%|', mr.alamat, '|%')", 'left')
			->order_by('mk.created_at', 'DESC');

		if (!empty($keyword)) {
			$this->db->group_start()
				->like('mk.no_kk', $keyword)
				->or_like('mk.alamat', $keyword)
				->or_like('mr.alamat', $keyword)
				->or_like('mr.nama', $keyword)
				->or_where("mk.id IN ($subquery)", NULL, FALSE)
				->group_end();
		}

		// Clone the query for counting
		$db_clone = clone $this->db;
		// $total_rows = $db_clone->count_all_results('', FALSE);
		$total_rows = $db_clone->count_all_results('', FALSE);

		// Pagination config with Bootstrap 5 styling
		$config['base_url'] = base_url('warga/data-warga');
		$config['total_rows'] = $total_rows;
		$config['per_page'] = 10;
		$config['uri_segment'] = 3;
		$config['reuse_query_string'] = TRUE;

		$start = $this->uri->segment($config['uri_segment'], 0);

		// Bootstrap 5 Pagination Style
		$config['full_tag_open'] = '<nav><ul class="pagination">';
		$config['full_tag_close'] = '</ul></nav>';
		$config['attributes'] = ['class' => 'page-link'];
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
		$config['cur_tag_close'] = '</a></li>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);

		// Get paginated results
		$this->db->limit($config['per_page'], $page);
		$keluarga_result = $this->db->get()->result_array();

		// Get anggota for each keluarga
		foreach ($keluarga_result as &$row) {
			$row['anggota'] = $this->db
				->select('*')
				->from('master_anggota_keluarga')
				->where('keluarga_id', $row['id'])
				->order_by('hubungan', 'ASC')
				->get()
				->result_array();
		}

		// Prepare data for view
		$data = [
			'start' => $start,
			'total_rows' => $total_rows,
			'per_page' => $config['per_page'],
			'current_page' => floor($start / $config['per_page']) + 1,
			'total_pages' => ceil($total_rows / $config['per_page']),
			'keluarga' => $keluarga_result,
			'pagination' => $this->pagination->create_links(),
			'keyword' => $keyword,
			'halaman' => 'dashboard/warga'
		];

		$this->load->view('modul', $data);
	}

	public function save_pendataan_keluarga()
	{
		$this->load->library('upload');
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'pdf|jpg|jpeg|png';
		$config['max_size'] = 100120;

		$this->upload->initialize($config);

		if ($_FILES['file_kk']['name']) {
			// Jika ada file yang diupload
			if (!$this->upload->do_upload('file_kk')) {
				echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors()]);
				return;
			}

			$file_data = $this->upload->data();
			$ext = $file_data['file_ext'];
			$no_kk = $this->input->post('no_kk');

			// Rename file
			$new_file_name = $no_kk . $ext;
			$new_file_path = $file_data['file_path'] . $new_file_name;
			rename($file_data['full_path'], $new_file_path);

			// Resize jika gambar
			$allowed_image_ext = ['.jpg', '.jpeg', '.png'];
			if (in_array(strtolower($ext), $allowed_image_ext)) {
				$config_resize['image_library'] = 'gd2';
				$config_resize['source_image'] = $new_file_path;
				$config_resize['maintain_ratio'] = TRUE;
				$config_resize['width'] = 800;
				$config_resize['height'] = 800;

				$this->load->library('image_lib', $config_resize);
				$this->image_lib->resize();
			}

			$file_kk = $new_file_name;
		} else {
			// Jika tidak ada file diupload
			$file_kk = null;
		}

		$file_data = $this->upload->data();
		$ext = $file_data['file_ext']; // ekstensi file, contoh ".jpg"
		$no_kk = $this->input->post('no_kk');

		// Rename file dengan nama no_kk + ekstensi
		$new_file_name = $no_kk . $ext;
		$new_file_path = $file_data['file_path'] . $new_file_name;

		rename($file_data['full_path'], $new_file_path);

		// Resize Image
		$config_resize['image_library'] = 'gd2';
		$config_resize['source_image'] = $file_path;
		$config_resize['maintain_ratio'] = TRUE;
		$config_resize['width'] = 800;
		$config_resize['height'] = 800;
		$this->load->library('image_lib', $config_resize);
		$this->image_lib->resize();

		$file_kk = $file_data['file_name'];

		$nomor_rumah = implode('| ', $this->input->post('nomor_rumah'));

		$keluarga_data = [
			'nomor_rumah' => $nomor_rumah,
			'no_kk' => $this->input->post('no_kk'),
			'status_rumah' => $this->input->post('status_rumah'),
			'alamat' => $this->input->post('alamat'),
			'provinsi' => $this->input->post('provinsi'),
			'kota' => $this->input->post('kota'),
			'kecamatan' => $this->input->post('kecamatan'),
			'kelurahan' => $this->input->post('kelurahan'),
			'file_kk' => $new_file_name,
			'no_hp' => $this->input->post('no_hp')
		];

		$this->db->insert('master_keluarga', $keluarga_data);
		$keluarga_id = $this->db->insert_id();

		$anggota = $this->input->post('nama');

		if ($anggota) {
			foreach ($anggota as $key => $nama) {
				$this->db->insert('master_anggota_keluarga', [
					'keluarga_id' => $keluarga_id,
					'nama' => $nama,
					'nik' => $this->input->post('nik')[$key],
					'agama' => $this->input->post('agama')[$key],
					'status_perkawinan' => $this->input->post('status_perkawinan')[$key],
					'hubungan' => $this->input->post('hubungan')[$key],
					'pekerjaan' => $this->input->post('pekerjaan')[$key],
					'jenis_kelamin' => $this->input->post('jenis_kelamin')[$key],
					'golongan_darah' => $this->input->post('golongan_darah')[$key],
					'tgl_lahir' => $this->input->post('tgl_lahir')[$key],
					'tempat_bekerja' => $this->input->post('tempat_bekerja')[$key]
				]);
			}
		}

		echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan.']);
	}

	public function update_pendataan_keluarga()
	{
		$id = $this->input->post('keluarga_id');
		$this->load->library('upload');

		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'pdf|jpg|jpeg|png';
		$config['max_size'] = 100120;
		$this->upload->initialize($config);

		$file_kk = $this->input->post('file_kk_existing');
		$no_kk = $this->input->post('no_kk');

		// Upload file baru jika ada
		if (!empty($_FILES['file_kk']['name'])) {
			if (!$this->upload->do_upload('file_kk')) {
				echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors()]);
				return;
			}

			// Hapus file lama
			$old_file = './uploads/' . $this->input->post('file_kk_existing');
			if (file_exists($old_file)) {
				unlink($old_file);
			}

			$file_data = $this->upload->data();
			$ext = $file_data['file_ext'];
			$new_file_name = $no_kk . "-" . date('ymd') . $ext;
			$new_file_path = $file_data['file_path'] . $new_file_name;
			rename($file_data['full_path'], $new_file_path);

			// Resize jika gambar
			$allowed_images = ['.jpg', '.jpeg', '.png'];
			if (in_array(strtolower($ext), $allowed_images)) {
				$config_resize['image_library'] = 'gd2';
				$config_resize['source_image'] = $new_file_path;
				$config_resize['maintain_ratio'] = TRUE;
				$config_resize['width'] = 800;
				$config_resize['height'] = 800;

				$this->load->library('image_lib', $config_resize);
				$this->image_lib->resize();
			}

			$file_kk = $new_file_name;
		}

		// Update data keluarga
		$nomor_rumah = implode('| ', $this->input->post('nomor_rumah'));

		$keluarga_data = [
			'nomor_rumah' => $nomor_rumah,
			'no_kk' => $no_kk,
			'status_rumah' => $this->input->post('status_rumah'),
			'alamat' => $this->input->post('alamat'),
			'provinsi' => $this->input->post('provinsi'),
			'kota' => $this->input->post('kota'),
			'kecamatan' => $this->input->post('kecamatan'),
			'kelurahan' => $this->input->post('kelurahan'),
			'file_kk' => $file_kk,
			'no_hp' => $this->input->post('no_hp')
		];

		$this->db->where('id', $id);
		$this->db->update('master_keluarga', $keluarga_data);
		// echo $this->db->last_query();
		log_message('error', $this->upload->display_errors());
		// ====================
		// Update anggota keluarga
		// ====================

		$anggota_ids = $this->input->post('anggota_id');
		$nama_list = $this->input->post('nama');
		$nik_list = $this->input->post('nik');
		$agama_list = $this->input->post('agama');
		$status_perkawinan_list = $this->input->post('status_perkawinan');
		$hubungan_list = $this->input->post('hubungan');
		$pekerjaan_list = $this->input->post('pekerjaan');
		$jenis_kelamin_list = $this->input->post('jenis_kelamin');
		$tgl_lahir_list = $this->input->post('tgl_lahir');
		$golongan_darah_list = $this->input->post('golongan_darah');
		$tempat_bekerja_list = $this->input->post('tempat_bekerja');

		// Ambil semua id anggota lama untuk membandingkan
		$anggota_lama = $this->db->get_where('master_anggota_keluarga', ['keluarga_id' => $id])->result_array();
		$anggota_lama_ids = array_column($anggota_lama, 'id');

		$anggota_terpakai = []; // akan menyimpan ID anggota yang masih dipakai

		foreach ($nama_list as $i => $nama) {
			$anggota_id = $anggota_ids[$i];

			$data = [
				'keluarga_id' => $this->input->post('keluarga_id'),
				'nama' => $nama,
				'nik' => $nik_list[$i],
				'agama' => $agama_list[$i],
				'status_perkawinan' => $status_perkawinan_list[$i],
				'hubungan' => $hubungan_list[$i],
				'pekerjaan' => $pekerjaan_list[$i],
				'jenis_kelamin' => $jenis_kelamin_list[$i],
				'golongan_darah' => $golongan_darah_list[$i],
				'tgl_lahir' => $tgl_lahir_list[$i],
				'tempat_bekerja' => $tempat_bekerja_list[$i],
			];

			if ($anggota_id) {
				// Update
				$this->db->where('id', $anggota_id);
				$this->db->update('master_anggota_keluarga', $data);
				$anggota_terpakai[] = $anggota_id;
			} else {
				// Tambah baru
				$this->db->insert('master_anggota_keluarga', $data);
				$anggota_terpakai[] = $this->db->insert_id();
			}
		}

		// Hapus anggota lama yang tidak dikirim lagi
		$anggota_hapus = array_diff($anggota_lama_ids, $anggota_terpakai);
		if (!empty($anggota_hapus)) {
			$this->db->where_in('id', $anggota_hapus);
			$this->db->delete('master_anggota_keluarga');
		}

		// echo $this->db->last_query();
		echo json_encode(['status' => 'success', 'message' => 'Data berhasil diperbarui.']);
	}

	public function show_participant()
	{
		$this->checkSession();
		$id = $this->input->post('id');
		if (isset($id)) {
			$id = $this->input->post('id', true);  // ambil id dari form dengan xss clean
			if (is_numeric($id)) {
				$id = (int) $id;
				$this->db->where('id', $id);
				$this->db->delete('master_partisipant');

				if ($this->db->affected_rows() > 0) {
					$this->session->set_flashdata('success', 'participants berhasil dihapus.');
				} else {
					$this->session->set_flashdata('error', 'Gagal menghapus participants.');
				}
			} else {
				$this->session->set_flashdata('error', 'ID tidak valid.');
			}

			redirect(current_url()); // Refresh halaman agar tidak submit ulang
		}
		$data['halaman'] = 'dashboard/show_participant';
		$this->load->view('modul', $data);
	}

	public function show_form_participant()
	{
		$this->load->view('dashboard/show_form_participant');
	}

	public function show_form_pembayaran()
	{
		$this->load->view('dashboard/show_form_pembayaran');
	}

	public function keterisian_pendataan()
	{
		$this->load->view('dashboard/keterisian_pendataan');
	}

	public function cek_pembayaran()
	{
		$this->checkSession();
		$tanggal = $this->input->post('tanggal');
		$id_rumah = $this->input->post('id_rumah');

		$bulan = date('m', strtotime($tanggal));
		$tahun = date('Y', strtotime($tanggal));
		$bulan_str = $tahun . '-' . $bulan; // e.g. "2026-03"

		// Cek 1: Pembayaran 1 bulan biasa — cek untuk_bulan
		$data_pembayaran = $this->db->query("
			SELECT a.id as id_pembayaran, a.id_rumah FROM master_pembayaran AS a
			LEFT JOIN master_users AS b ON a.user_id = b.id
			WHERE DATE_FORMAT(a.untuk_bulan, '%Y-%m') = ?
			AND b.id_rumah = ?
			LIMIT 1
		", array($bulan_str, $id_rumah))->row_array();

		// Cek 2: Bulan ini mungkin ter-cover oleh rapel — cek bulan_rapel
		if (!$data_pembayaran) {
			$data_pembayaran = $this->db->query("
				SELECT a.id as id_pembayaran, a.id_rumah FROM master_pembayaran AS a
				LEFT JOIN master_users AS b ON a.user_id = b.id
				WHERE FIND_IN_SET(?, a.bulan_rapel) > 0
				AND b.id_rumah = ?
				LIMIT 1
			", array($bulan_str, $id_rumah))->row_array();
		}

		if ($data_pembayaran) {
			echo json_encode([
				'sudah_dibayar' => true,
				'id_rumah' => encrypt_url($id_rumah),
				'id_pembayaran' => encrypt_url($data_pembayaran['id_pembayaran'])
			]);
		} else {
			echo json_encode(['sudah_dibayar' => false]);
		}
	}

	public function laporan_pembayaran($tahun = null)
	{
		$this->checkSession();
		if ($tahun === null) {
			$tahun = date('Y');
		}
		$keyword      = $this->input->get('keyword');
		$tahun          = $this->input->get('tahun');
		// Ambil id_koordinator dari GET atau dari session jika level user adalah koordinator
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$id_koordinator = $this->session->userdata('username')['id'];
		} else {
			$id_koordinator = $this->input->get('id_koordinator', TRUE);
		}
		$filter = [];
		if (!empty($id_koordinator)) {
			$filter[] = "vb.id_koordinator = '" . $this->db->escape_str($id_koordinator) . "'";
		}
		if (!empty($keyword)) {
			$filter[] = "(vb.nama LIKE '%" . $this->db->escape_str($keyword) . "%' OR vb.alamat LIKE '%" . $this->db->escape_str($keyword) . "%')";
		}
		// if (!empty($tahun)) {
		// 	$filter[] = "YEAR(vb.tanggal_pelaporan) = '" . $this->db->escape_str($tahun) . "'";
		// }
		// Gabungkan semua kondisi dengan AND
		$whereClause = '';
		if (!empty($filter)) {
			$whereClause = 'WHERE ' . implode(' AND ', $filter);
		}

		$rumah = $this->db->query("SELECT vb.* FROM (
										SELECT DISTINCT b.id, b.alamat, b.nama, c.nama as koordinator,b.id_koordinator FROM master_users as a
										LEFT JOIN master_rumah as b ON a.id = id_rumah
										LEFT JOIN master_koordinator_blok as c ON b.id_koordinator = c.id
										ORDER BY b.alamat ASC
									) as vb " . $whereClause)->result_array();
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			// Jika login sebagai koordinator, hanya tampilkan koordinator yang sedang login
			$koordinator = $this->db->query("SELECT DISTINCT id, nama FROM master_koordinator_blok WHERE id = '" . $this->db->escape_str($this->session->userdata('username')['id']) . "'")->result_array();
		} else {
			// Jika admin, tampilkan semua koordinator
			$koordinator = $this->db->query("SELECT DISTINCT id, nama FROM master_koordinator_blok")->result_array();
		}

		// Filter pembayaran sesuai login koordinator jika role koordinator
		$where_koor = [];
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$id_koordinator_login = $this->session->userdata('username')['id'];
			$where_koor['id_koordinator'] = $id_koordinator_login;
		} elseif (!empty($id_koordinator)) {
			$where_koor['id_koordinator'] = $id_koordinator;
		}

		// Bulan & tahun sekarang
		$bulan_ini = date('m');
		$tahun_ini = date('Y');

		// Helper untuk ambil total pembayaran dengan filter koordinator
		$get_total = function ($via, $bulan = null, $tahun = null) use ($where_koor) {
			$this_ci = $this;

			$this_ci->db->select("SUM(mp.jumlah_bayar) as jumlah_bayar");
			$this_ci->db->from('master_pembayaran mp');
			$this_ci->db->join('master_users u', 'u.id = mp.user_id', 'left');
			$this_ci->db->join('master_rumah r', 'r.id = u.id_rumah', 'left');

			$this_ci->db->where('mp.status', 'verified');
			$this_ci->db->where('mp.pembayaran_via', $via);

			if ($bulan && $tahun) {
				// Tentukan bulan mulai (aturan 2025 mulai Juni)
				$bulan_mulai = ($tahun == 2025) ? 6 : 1;

				$tanggal_awal  = sprintf('%d-%02d-01', $tahun, $bulan_mulai);
				$tanggal_akhir = date('Y-m-t', strtotime("$tahun-$bulan-01"));

				$this_ci->db->where('mp.bulan_mulai >=', $tanggal_awal);
				$this_ci->db->where('mp.bulan_mulai <=', $tanggal_akhir);
			}

			if (!empty($where_koor['id_koordinator'])) {
				$this_ci->db->where('r.id_koordinator', $where_koor['id_koordinator']);
			}

			return $this_ci->db->get()->row_array();
		};



		$jumlah_transfer_bulan_ini_transfer = $get_total('transfer', $bulan_ini, $tahun_ini);
		$jumlah_transfer_bulan_ini_koordinator = $get_total('koordinator', $bulan_ini, $tahun_ini);

		$jumlah_transfer_sd_transfer = $get_total('transfer');
		$jumlah_transfer_sd_koordinator = $get_total('koordinator');

		$data['tahun'] = $tahun;
		$data['rumah'] = $rumah;
		$data['bulan_ini_koor'] = $jumlah_transfer_bulan_ini_koordinator;
		$data['bulan_ini_transfer'] = $jumlah_transfer_bulan_ini_transfer;
		$data['bulan_sd_koor'] = $jumlah_transfer_sd_koordinator;
		$data['bulan_sd_transfer'] = $jumlah_transfer_sd_transfer;
		$data['koordinator'] = $koordinator;
		$data['halaman'] = 'dashboard/laporan_pembayaran';
		$this->load->view('modul', $data);
	}

	public function verifikasi_pembayaran()
	{
		$this->checkSession();
		// Ambil input dari query string (GET)
		$status          = $this->input->get('status', TRUE) ?: 'pending'; // default 'pending'
		$keyword         = $this->input->get('keyword', TRUE);
		$pembayaran_via  = $this->input->get('pembayaran_via', TRUE);
		$id_koordinator  = $this->input->get('id_koordinator', TRUE);

		// Cek jika ada input user dan bulan, tampilkan error jika sudah ada pembayaran
		$user_id = $this->input->get('user_id', TRUE);
		$bulan_mulai = $this->input->get('bulan_mulai', TRUE);

		if (!empty($user_id) && !empty($bulan_mulai)) {
			$cek = $this->db->get_where('master_pembayaran', [
				'user_id' => $user_id,
				'MONTH(bulan_mulai)' => date('m', strtotime($bulan_mulai)),
				'YEAR(bulan_mulai)' => date('Y', strtotime($bulan_mulai))
			])->row();
			if ($cek) {
				$this->session->set_flashdata('error', 'Bulan ini sudah terbayarkan untuk user tersebut.');
			}
		}

		// ====================
		// Query data pembayaran
		// ====================
		$this->db->select('p.*, u.nama, u.rumah');
		$this->db->from('master_pembayaran p');
		$this->db->join('master_users u', 'u.id = p.user_id');
		$this->db->where('p.status', $status);

		// Filter jika ada input
		if (!empty($keyword)) {
			$this->db->group_start();
			$this->db->like('u.nama', $keyword);
			$this->db->or_like('u.rumah', $keyword);
			$this->db->group_end();
		}
		if (!empty($pembayaran_via)) {
			$this->db->where('p.pembayaran_via', $pembayaran_via);
		}
		if (!empty($id_koordinator)) {
			$this->db->where('u.id_koordinator', $id_koordinator);
		}

		$this->db->order_by('p.created_at', 'DESC');
		$data['pembayaran'] = $this->db->get()->result();

		// ====================
		// Hitung total dinamis berdasarkan filter
		// ====================
		$base_query = function ($via = null, $status_filter = null) use ($keyword, $id_koordinator) {
			$CI = &get_instance(); // CI instance

			$CI->db->select('SUM(p.jumlah_bayar) as total');
			$CI->db->from('master_pembayaran p');
			$CI->db->join('master_users u', 'u.id = p.user_id');
			$CI->db->where('p.status', $status_filter ?? 'pending');

			// Filter keyword (nama atau rumah)
			if (!empty($keyword)) {
				$CI->db->group_start();
				$CI->db->like('u.nama', $keyword);
				$CI->db->or_like('u.rumah', $keyword);
				$CI->db->group_end();
			}

			// Filter id_koordinator (jika ada)
			if (!empty($id_koordinator)) {
				$CI->db->where('u.id_koordinator', $id_koordinator);
			}

			// Filter pembayaran_via (eksklusif dari parameter $via, bukan GET)
			if (!empty($via)) {
				$CI->db->where('p.pembayaran_via', $via);
			}

			// Return hasil total
			return $CI->db->get()->row()->total ?? 0;
		};

		// Total seluruh hasil terfilter
		$data['total_terfilter'] = $base_query(null, $status);

		// Total transfer
		$data['total_transfer'] = $base_query('transfer', $status);

		// Total via koordinator
		$data['total_koordinator'] = $base_query('koordinator', $status);

		// ====================
		// Koordinator dropdown
		// ====================
		$data['koordinator'] = $this->db->query("SELECT DISTINCT id, nama FROM master_koordinator_blok")->result_array();

		// Untuk filter status di view
		$data['status_filter'] = $status;

		$data['halaman'] = 'dashboard/verifikasi_pembayaran';
		$this->load->view('modul', $data);
	}

	public function save_pembayaran()
	{
		$this->checkSession();
		// Ambil data dari POST
		$id = $this->input->post('id_pembayaran');
		$user_id = $this->input->post('user_id');
		$metode = $this->input->post('metode');
		$bulan_mulai = $this->input->post('bulan_mulai'); // format yyyy-mm
		$untuk_bulan = $this->input->post('untuk_bulan'); // format yyyy-mm
		$jumlah_bayar = $this->input->post('jumlah_bayar');
		$tanggal_bayar = $this->input->post('tanggal_bayar');
		$keterangan = $this->input->post('keterangan');

		$lama_cicilan = $this->input->post('lama_cicilan');
		$total_cicilan = $this->input->post('total_cicilan');

		// Validasi sederhana
		if (!$user_id || !$metode || !$jumlah_bayar) {
			$this->session->set_flashdata('error', 'Data wajib diisi lengkap');
			redirect('pembayaran');
			return;
		}

		// Ambil metode bayar
		$pembayaran_via = $this->input->post('pembayaran_via');

		// Inisialisasi variabel untuk bukti
		$bukti_lama = $this->input->post('file_kk_existing');
		$nama_file_bukti = $bukti_lama;

		// Jika via transfer, lakukan upload
		if (in_array($pembayaran_via, array('transfer', 'transfer_2'))) {
			// Cek apakah ada file yang diupload
			if (!empty($_FILES['bukti']['name'])) {
				$config['upload_path']   = './uploads/bukti/';
				$config['allowed_types'] = 'jpg|jpeg|png|pdf';
				$config['max_size']      = 50048; // 50MB (ganti sesuai kebutuhan)
				$config['encrypt_name']  = TRUE;

				$this->load->library('upload', $config);

				if (!$this->upload->do_upload('bukti')) {
					$this->session->set_flashdata('error', 'Upload bukti gagal: ' . $this->upload->display_errors('', ''));
					redirect('pembayaran');
					return;
				} else {
					$upload_data = $this->upload->data();
					$nama_file_bukti = $upload_data['file_name'];

					// Kompres jika gambar
					if (in_array(strtolower($upload_data['file_ext']), ['.jpg', '.jpeg', '.png'])) {
						$config['image_library'] = 'gd2';
						$config['source_image'] = $upload_data['full_path'];
						$config['quality'] = '70%'; // Kompres kualitas 70%
						$config['maintain_ratio'] = TRUE;

						$this->load->library('image_lib', $config);

						if (!$this->image_lib->resize()) {
							$this->session->set_flashdata('error', 'Gagal kompres gambar: ' . $this->image_lib->display_errors('', ''));
							redirect('pembayaran');
							return;
						}
					}

					// Simpan nama file ke dalam data update
					$data['bukti_transfer'] = $nama_file_bukti;
				}
			}
		}

		// Cek apakah pembayaran untuk id_rumah dan bulan_mulai sudah ada
		$bulan_mulai_db = $bulan_mulai ? $bulan_mulai . '-01' : null;
		// 	$cek_pembayaran = $this->db->get_where('master_pembayaran', [
		// 		'id_rumah' => $user_id,
		// 		'bulan_mulai' => $bulan_mulai_db
		//  ])->row_array();

		// 	if ($cek_pembayaran && empty($id)) {
		// 		$this->session->set_flashdata('error', 'Pembayaran untuk rumah dan bulan tersebut sudah ada.');
		// 		redirect('pembayaran');
		// 		return;
		// 	}

		// Ambil data bulan rapel dari POST (array), gabungkan jadi string dipisah koma
		$bulan_rapel = $this->input->post('bulan_rapel');
		$bulan_rapel_str = '';
		if (!empty($bulan_rapel) && is_array($bulan_rapel)) {
			$bulan_rapel_str = implode(',', $bulan_rapel);
		}

		$data_pembayaran = [
			'user_id' => $user_id,
			'id_rumah' => $user_id,
			'metode' => $metode,
			'pembayaran_via' => $pembayaran_via,
			'bulan_mulai' => $bulan_mulai_db,
			'jumlah_bayar' => $jumlah_bayar,
			'bukti' => $nama_file_bukti, // <-- ini penting
			'keterangan' => $keterangan,
			'tanggal_bayar' => $tanggal_bayar,
			'untuk_bulan' => $untuk_bulan,
			'bulan_rapel' => $bulan_rapel_str, // <-- simpan sebagai string
			'created_at' => date('Y-m-d H:i:s'),
		];

		// Insert ke tabel pembayaran
		if (!empty($id)) {
			$this->db->where(array('id' => $id))->update('master_pembayaran', $data_pembayaran);
			$data_pembayaran['pembayaran_id'] = $id;
		} else {
			$this->db->insert('master_pembayaran', $data_pembayaran);
			$data_pembayaran['pembayaran_id'] = $this->db->insert_id();
		}

		// =============================================
		// Kirim notifikasi WA ke warga saat koordinator entry
		// Pesan tanpa link kitir (kitir menunggu proses validasi)
		// =============================================
		try {
			$pembayaran_id = $data_pembayaran['pembayaran_id'];
			$wa_user = $this->db->get_where('master_users', ['id' => $user_id])->row_array();
			$wa_rumah = $this->db->get_where('master_rumah', ['id' => $wa_user['id_rumah'] ?? 0])->row_array();

			// Ambil data keluarga untuk nomor HP
			$wa_keluarga = [];
			if (isset($wa_rumah['id']) && is_numeric($wa_rumah['id']) && (int) $wa_rumah['id'] > 0) {
				$wa_keluarga = $this->db->get_where('master_keluarga', ['id_rumah' => (int) $wa_rumah['id']])->row_array();
			} else {
				$wa_alamat_cari = trim($wa_rumah['alamat'] ?? '');
				if ($wa_alamat_cari !== '') {
					$al = $this->db->escape_like_str($wa_alamat_cari);
					$this->db->group_start();
					$this->db->like('nomor_rumah', $al);
					$this->db->or_like('nomor_rumah', '|' . $al);
					$this->db->or_like('nomor_rumah', $al . '|');
					$this->db->group_end();
					$wa_keluarga = $this->db->get('master_keluarga')->row_array();
				}
			}

			$wa_nama = $wa_user['nama'] ?? '';
			$wa_no_hp = $wa_keluarga['no_hp'] ?? '';

			// Validasi nomor HP, jika kosong ambil dari master_keluarga lain yang cocok
			if (empty($wa_no_hp) && !empty($wa_rumah['alamat'])) {
				$wa_keluarga_alt = $this->db->query("SELECT no_hp FROM master_keluarga WHERE nomor_rumah LIKE '%" . $this->db->escape_like_str($wa_rumah['alamat']) . "%' AND no_hp IS NOT NULL AND no_hp != '' LIMIT 1")->row_array();
				$wa_no_hp = $wa_keluarga_alt['no_hp'] ?? '';
			}

			if (!empty($wa_no_hp)) {
				$wa_bulan = date('F Y', strtotime($bulan_mulai_db));
				$wa_metode_text = ($pembayaran_via === 'koordinator') ? 'Koordinator' : 'Transfer';

				$wa_text = "📥 Konfirmasi Pembayaran IPL

Assalamu'alaikum/Salam sejahtera Bapak/Ibu *$wa_nama*,

Terima kasih kami ucapkan atas pembayaran IPL bulan *$wa_bulan* sebesar *Rp" . number_format($jumlah_bayar, 0, ',', '.') . "* yang telah kami terima. 🙏
💳 Tanggal Bayar: " . date('d-m-Y', strtotime($tanggal_bayar)) . "
🔄 Metode Pembayaran: $wa_metode_text
📋 Status: Menunggu validasi

Pembayaran Bapak/Ibu sedang dalam proses validasi oleh pengurus. Bukti kitir pembayaran akan dikirimkan setelah proses validasi selesai.

Pembayaran Bapak/Ibu sangat membantu dalam operasional dan pemeliharaan lingkungan kita bersama.

Jika ada pertanyaan atau masukan, silakan hubungi kami kapan saja.

Hormat kami,
Pengurus Paguyuban TSI
Perumahan Taman Sukodono Indah
_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";

				$this->load->helper('wa');
				$res = send_wa($wa_no_hp, $wa_text);
				$wa_status = (isset($res['status']) && ($res['status'] === true || $res['status'] == '1')) ? 'success' : 'failed';
			}
		} catch (Exception $e) {
			// Gagal kirim WA tidak menggagalkan proses simpan
			log_message('error', 'Gagal kirim WA saat entry koordinator: ' . $e->getMessage());
		}

		$this->session->set_flashdata('success', 'Pembayaran berhasil disimpan');
		redirect('pembayaran-sukses?data=' . urlencode(json_encode($data_pembayaran)));
	}

	public function pembayaran_sukses()
	{
		$this->load->view('dashboard/pembayaran_sukses');
	}

	public function act_verifikasi_pembayaran()
	{
		$this->checkSession();
		$id = $this->input->post('id');
		$aksi = $this->input->post('aksi');

		$cek = $this->db->get_where('master_pembayaran', ['id' => $id])->row();
		if (!$cek) {
			echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
			return;
		}

		$statusBaru = $aksi === 'verified' ? 'verified' : 'rejected';
		$this->db->where('id', $id);
		$update = $this->db->update('master_pembayaran', ['status' => $statusBaru]);

		if ($update) {
			$pembayaran = $this->db->get_where('master_pembayaran', ['id' => $id])->row_array();
			$user = $this->db->get_where('master_users', ['id' => $pembayaran['user_id']])->row_array();
			$rumah = $this->db->get_where('master_rumah', ['id' => $user['id_rumah']])->row_array();
			$keluarga = $this->db->query("SELECT no_hp FROM master_keluarga WHERE nomor_rumah LIKE '%".$this->db->escape_like_str($rumah['alamat'])."%' AND no_hp IS NOT NULL AND no_hp != '' LIMIT 1")->row_array();
			
			$nama = $user['nama'] ?? '';
			$no_hp = $keluarga['no_hp'] ?? '';
			$bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));
			$link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));

			$text = "✅ Pembayaran IPL Telah Divalidasi\n\nAssalamu'alaikum/Salam sejahtera Bapak/Ibu *$nama*,\n\nPembayaran IPL bulan *$bulan* sebesar *Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "* telah *divalidasi* oleh pengurus. ✅\n💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "\n📄 Bukti: Sudah divalidasi\n🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "\n📑 Kitir Pembayaran: $link\n\nSilakan unduh e-kitir di atas sebagai bukti pembayaran resmi Bapak/Ibu.\n\nTerima kasih atas kontribusi Bapak/Ibu dalam operasional dan pemeliharaan lingkungan kita bersama.\n\nHormat kami,\nPengurus Paguyuban TSI\nPerumahan Taman Sukodono Indah\n_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";

			$log_data = [
				'pembayaran_id' => $id, 'aksi' => $aksi, 'status_sebelum' => $cek->status, 'status_sesudah' => $statusBaru,
				'warga_nama' => $nama, 'warga_alamat' => $rumah['alamat'], 'bulan_bayar' => $pembayaran['bulan_mulai'],
				'jumlah_bayar' => $pembayaran['jumlah_bayar'], 'pembayaran_via' => $pembayaran['pembayaran_via'],
				'tanggal_bayar' => $pembayaran['tanggal_bayar'], 'wa_no_tujuan' => hp($no_hp),
			];

			// Tandai untuk dikirim via antrian (agar simpan jadi cepat/instan)
			$this->db->where('id', $id);
			$this->db->update('master_pembayaran', ['wa_send' => 'queue']);

			echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan dan notifikasi WA masuk antrian']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data']);
		}
	}

	public function act_verifikasi_pembayaran_all()
	{
		$this->checkSession();
		$ids = $this->input->post('ids');
		$aksi = $this->input->post('aksi');
		if (empty($ids) || !is_array($ids)) { echo json_encode(['status' => 'error', 'message' => 'Tidak ada data']); return; }

		$statusBaru = ($aksi === 'verified') ? 'verified' : 'rejected';
		$this->load->helper('wa');

		foreach ($ids as $id) {
			$cek = $this->db->get_where('master_pembayaran', ['id' => $id])->row();
			if (!$cek || $cek->status == $statusBaru) continue;

			$this->db->where('id', $id);
			if ($this->db->update('master_pembayaran', ['status' => $statusBaru])) {
				$pembayaran = $this->db->get_where('master_pembayaran', ['id' => $id])->row_array();
				$user = $this->db->get_where('master_users', ['id' => $pembayaran['user_id']])->row_array();
				$rumah = $this->db->get_where('master_rumah', ['id' => $user['id_rumah']])->row_array();
				$keluarga = $this->db->query("SELECT no_hp FROM master_keluarga WHERE nomor_rumah LIKE '%".$this->db->escape_like_str($rumah['alamat'])."%' AND no_hp IS NOT NULL AND no_hp != '' LIMIT 1")->row_array();
				
				$no_hp = $keluarga['no_hp'] ?? '';
				if (!empty($no_hp)) {
					$bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));
					$link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));
					$text = "✅ Pembayaran IPL Telah Divalidasi\n\nAssalamu'alaikum/Salam sejahtera Bapak/Ibu *".$user['nama']."*,\n\nPembayaran IPL bulan *$bulan* sebesar *Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "* telah *divalidasi* oleh pengurus. ✅\n💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "\n📄 Bukti: Sudah divalidasi\n🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "\n📑 Kitir Pembayaran: $link\n\nSilakan unduh e-kitir di atas sebagai bukti pembayaran resmi Bapak/Ibu.\n\nTerima kasih atas kontribusi Bapak/Ibu dalam operasional dan pemeliharaan lingkungan kita bersama.\n\nHormat kami,\nPengurus Paguyuban TSI\nPerumahan Taman Sukodono Indah\n_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";
					send_wa($no_hp, $text);
				}
			}
		}
		echo json_encode(['status' => 'success', 'message' => 'Data terpilih berhasil diproses']);
	}

	public function ajaxSendWaBatch()
	{
		$this->checkSession();
		if (!$this->input->is_ajax_request()) {
			show_404();
		}

		// Set unlimited execution time
		set_time_limit(0);

		$keluarga_list = $this->db->select('id, no_hp, nomor_rumah')
			->from('master_keluarga')
			->where('no_hp !=', '')
			->get()
			->result_array();

		$bulan = date('F Y');
		$wa_url = 'https://wa2.digitalminsajo.sch.id/send-message';

		$hasil = [];

		foreach ($keluarga_list as $index => $row) {
			$nama = '';
			$alamat = $row['nomor_rumah'];
			// Ambil nama dari master_users berdasarkan alamat rumah
			$user = $this->db->select('nama')
				->from('master_users')
				->like('rumah', $alamat)
				->get()
				->row_array();

			if ($user && !empty($user['nama'])) {
				$nama = $user['nama'];
			} else {
				// fallback ke kepala keluarga jika tidak ada di master_users
				$anggota = $this->db->select('nama')
					->from('master_anggota_keluarga')
					->where('keluarga_id', $row['id'])
					->where('hubungan', 'Kepala Keluarga')
					->get()
					->row_array();

				if ($anggota && !empty($anggota['nama'])) {
					$nama = $anggota['nama'];
				}
			}

			$text = "📢 Pengingat Pembayaran IPL\n\nAssalamu’alaikum/Salam sejahtera Bapak/Ibu *$nama*,\n\nKami mengingatkan untuk melakukan pembayaran IPL bulan *$bulan* untuk rumah di alamat *$alamat* batas pembayaran tanggal 10 setiap bulannya.\n\n💳 Cara Pembayaran:\n- Melalui Koordinator Blok\n- Transfer ke Bendahara Paguyuban (BCA 8221586107 / Dhani Kispananto)\n\nPembayaran IPL sangat penting untuk mendukung operasional dan pemeliharaan lingkungan kita bersama.\n\nTerima kasih atas perhatian dan kerjasama Bapak/Ibu.\n\nHormat kami,\nPengurus Paguyuban TSI\nPerumahan Taman Sukodono Indah\n _⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi. Mohon tidak membalas pesan ini._";

			$no_hp = hp($row['no_hp']);
			$status = 'Gagal';
			if (!empty($no_hp)) {
				$this->load->helper('wa');
				$res = send_wa($no_hp, $text);
				if (isset($res['status']) && ($res['status'] === true || $res['status'] == '1')) {
					$status = 'Berhasil';
				}
			}

			// Simpan ke log DB
			$this->db->insert('log_pengiriman_wa_ipl', [
				'keluarga_id' => $row['id'],
				'nama' => $nama,
				'alamat' => $alamat,
				'no_hp' => $no_hp,
				'status' => $status,
				'pesan' => $text,
				'created_at' => date('Y-m-d H:i:s')
			]);

			$hasil[] = [
				'nama' => $nama,
				'alamat' => $alamat,
				'status' => $status,
				'index' => $index + 1
			];
		}

		$this->output
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => 'done',
				'total' => count($hasil),
				'data' => $hasil
			]));
	}

	public function kirim_ipl()
	{
		$this->checkSession();
		$this->load->library('pagination');

		// Config pagination
		$config['base_url'] = site_url('pembayaran/kirim_ipl');
		$config['total_rows'] = $this->db->count_all('log_pengiriman_wa_ipl');
		$config['per_page'] = 10;
		$config['uri_segment'] = 3;

		// Styling (Bootstrap 4/5)
		$config['full_tag_open'] = '<nav><ul class="pagination justify-content-center">';
		$config['full_tag_close'] = '</ul></nav>';
		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><span class="page-link">';
		$config['cur_tag_close'] = '</span></li>';
		$config['next_tag_open'] = '<li class="page-item">';
		$config['next_tag_close'] = '</li>';
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';
		$config['attributes'] = ['class' => 'page-link'];

		$this->pagination->initialize($config);

		$start = $this->uri->segment(3, 0);

		// Ambil data log
		$this->db->order_by('created_at', 'DESC');
		$logs = $this->db->get('log_pengiriman_wa_ipl', $config['per_page'], $start)->result();

		$data = [
			'logs' => $logs,
			'pagination' => $this->pagination->create_links()
		];

		$this->load->view('dashboard/kirim_ipl_view', $data);
	}
	public function logout()
	{
		$this->session->sess_destroy();
		redirect('./login');
	}
	public function invoice()
	{
		$this->checkSession();
		$this->load->view('dashboard/invoice_pdf');
	}
	public function download_invoice()
	{
		mb_internal_encoding('UTF-8');

		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		// Ambil data
		$id = $this->input->get('id', true);
		$data = [];
		if ($id) {
			$data['pembayaran'] = $this->db->get_where('master_pembayaran', ['id' => $id])->row_array();
		} else {
			$data['pembayaran'] = [];
		}


		// Render view jadi HTML
		$html = $this->load->view('dashboard/invoice_pdf', $data, true);

		// 1. Normalisasi semua dash ke hyphen ASCII
		$html = preg_replace('/[‐-‒–—−]/u', '-', $html);

		// 2. Pastikan encoding HTML UTF-8
		if (stripos($html, '<meta charset') === false) {
			$html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html;
		}

		// Buat objek TCPDF
		$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

		// 3. Gunakan font Unicode penuh
		$pdf->SetFont('dejavusans', '', 10, '', true);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Perum TSI');
		$pdf->SetTitle('Invoice IPL');
		$pdf->SetMargins(10, 10, 10, true);
		$pdf->SetAutoPageBreak(TRUE, 10);

		$pdf->AddPage();

		// Tulis HTML
		$pdf->writeHTML($html, true, false, true, false, '');

		// Output PDF
		$nama_file = 'invoice_ipl_' . date('YmdHis') . '.pdf';
		if (!empty($this->session->userdata['username'])) {
			$pdf->Output($nama_file, 'I'); // Preview
		} else {
			$pdf->Output($nama_file, 'D'); // Download
		}
	}

	public function berhasil()
	{
		echo "<script>alert('Berhasil disimpan')</script>";
	}
	public function act_hapus_pembayaran()
	{
		$this->checkSession();
		$id = $this->input->post('id', true);

		if (empty($id) || !is_numeric($id)) {
			echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
			return;
		}

		// Ambil data pembayaran
		$pembayaran = $this->db->get_where('master_pembayaran', ['id' => $id])->row_array();
		if (!$pembayaran) {
			echo json_encode(['status' => 'error', 'message' => 'Data pembayaran tidak ditemukan']);
			return;
		}

		// Hapus file bukti jika ada
		if (!empty($pembayaran['bukti'])) {
			$file_path = FCPATH . 'uploads/bukti/' . $pembayaran['bukti'];
			if (file_exists($file_path)) {
				@unlink($file_path);
			}
		}

		// Hapus data pembayaran
		$this->db->where('id', $id)->delete('master_pembayaran');

		if ($this->db->affected_rows() > 0) {
			echo json_encode(['status' => 'success', 'message' => 'Pembayaran berhasil dihapus']);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pembayaran']);
		}
	}
	public function act_update_password()
	{
		$this->checkSession();

		$username_data = $this->session->userdata('username');
		$role = $username_data['role'] ?? '';
		$id = $username_data['id'] ?? '';

		$new_password = $this->input->post('new_password', true);
		$old_password = $this->input->post('old_password', true);

		if (empty($new_password) || empty($old_password)) {
			echo json_encode(['status' => 'error', 'message' => 'Password lama dan baru wajib diisi']);
			return;
		}

		// Pilih tabel sesuai role
		if ($role === 'koordinator') {
			$table = 'master_koordinator_blok';
			$where = ['id' => $id];
		} else {
			$table = 'master_admin';
			$where = ['id' => $id];
		}

		// Ambil data user
		$user = $this->db->get_where($table, $where)->row_array();
		if (!$user) {
			echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan']);
			return;
		}

		// Validasi password lama
		if ($user['password'] !== md5($old_password)) {
			echo json_encode(['status' => 'error', 'message' => 'Password lama salah']);
			return;
		}

		// Update password baru
		$this->db->where($where)->update($table, ['password' => md5($new_password)]);
		echo json_encode(['status' => 'success', 'message' => 'Password berhasil diupdate']);
	}
	public function update_password()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/update_password';
		$this->load->view('modul', $data);
	}
	/**
	 * Halaman Laporan Rekap Pembayaran (web, bukan PDF)
	 * Total berdasarkan tanggal_bayar aktual — support rapel & bayar belakang
	 */
	public function laporan_rekap_rapel()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/laporan_rekap_rapel';
		$this->load->view('modul', $data);
	}

	/**
	 * Halaman Analisis Pembayaran
	 * Fitur: Tunggakan, Grafik Tren, Rekap Tahunan
	 */
	public function analisis_pembayaran()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/analisis_pembayaran';
		$this->load->view('modul', $data);
	}

	/**
	 * Halaman Setting
	 * Edit nama user dan nama pemilik rumah
	 */
	public function setting()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/setting';
		$this->load->view('modul', $data);
	}

	/**
	 * Update nama user (admin/koordinator)
	 */
	public function update_user()
	{
		$this->checkSession();

		$id = $this->input->post('user_id', true);
		$table = $this->input->post('user_table', true);
		$nama = trim($this->input->post('nama', true));

		// Validasi tabel
		$allowed_tables = ['master_admin', 'master_koordinator_blok'];
		if (!in_array($table, $allowed_tables) || empty($id) || empty($nama)) {
			$this->session->set_flashdata('error', 'Data tidak valid.');
			redirect('setting');
			return;
		}

		$this->db->where('id', $id);
		$this->db->update($table, ['nama' => $nama]);

		if ($this->db->affected_rows() >= 0) {
			$this->session->set_flashdata('success', 'Nama user berhasil diperbarui.');
		} else {
			$this->session->set_flashdata('error', 'Gagal memperbarui nama user.');
		}

		redirect('setting');
	}

	/**
	 * Update nama pemilik rumah
	 */
	public function update_rumah()
	{
		$this->checkSession();

		$id = $this->input->post('rumah_id', true);
		$nama = trim($this->input->post('nama', true));

		if (empty($id)) {
			$this->session->set_flashdata('error', 'ID rumah tidak valid.');
			redirect('setting');
			return;
		}

		$this->db->where('id', $id);
		$this->db->update('master_rumah', ['nama' => $nama]);

		if ($this->db->affected_rows() >= 0) {
			$this->session->set_flashdata('success', 'Nama pemilik rumah berhasil diperbarui.');
		} else {
			$this->session->set_flashdata('error', 'Gagal memperbarui nama pemilik rumah.');
		}

		redirect('setting');
	}

	/**
	 * Laporan Rekap Pembayaran (Termasuk Rapel & Bayar Belakang)
	 * Total dihitung berdasarkan tanggal_bayar, bukan bulan_mulai
	 * PDF output menggunakan TCPDF
	 */
	public function laporan_rekap_rapel_pdf()
	{
		$this->checkSession();
		mb_internal_encoding('UTF-8');
		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		// Ambil parameter
		$id_koordinator = decrypt_url($this->input->get('id_koordinator', TRUE));
		$bulan          = $this->input->get('bulan', TRUE);  // format: 01-12
		$tahun          = $this->input->get('tahun', TRUE);

		if (empty($bulan)) {
			$bulan = date('m');
		}
		if (empty($tahun)) {
			$tahun = date('Y');
		}

		// Jika role = koordinator, override id_koordinator dari session
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$id_koordinator = $this->session->userdata('username')['id'];
		}

		// Build filter WHERE untuk data rumah
		$filter = [];
		if (!empty($id_koordinator)) {
			$filter[] = "vb.id_koordinator = '" . $this->db->escape_str($id_koordinator) . "'";
		}
		$whereClause = '';
		if (!empty($filter)) {
			$whereClause = 'WHERE ' . implode(' AND ', $filter);
		}

		// Data rumah
		$rumah = $this->db->query("SELECT vb.* FROM (
                                    SELECT DISTINCT b.id, b.alamat, b.nama, c.nama as koordinator, b.id_koordinator
                                    FROM master_users as a
                                    LEFT JOIN master_rumah as b ON a.id_rumah = b.id
                                    LEFT JOIN master_koordinator_blok as c ON b.id_koordinator = c.id
                                    ORDER BY b.alamat ASC
                               ) as vb " . $whereClause)->result_array();

		// Data koordinator
		$koordinator_filter = [];
		if (!empty($id_koordinator)) {
			$koordinator_filter = $this->db->query("SELECT id, nama FROM master_koordinator_blok WHERE id = '" . $this->db->escape_str($id_koordinator) . "'")->row_array();
		}

		$bulan_indonesia_map = [
			'01' => 'Januari',
			'02' => 'Februari',
			'03' => 'Maret',
			'04' => 'April',
			'05' => 'Mei',
			'06' => 'Juni',
			'07' => 'Juli',
			'08' => 'Agustus',
			'09' => 'September',
			'10' => 'Oktober',
			'11' => 'November',
			'12' => 'Desember'
		];
		$nama_bulan_periode = $bulan_indonesia_map[str_pad($bulan, 2, '0', STR_PAD_LEFT)] ?? $bulan;

		$data = [
			'rumah'                  => $rumah,
			'bulan'                  => (int)$bulan,
			'tahun'                  => $tahun,
			'nama_bulan_periode'     => $nama_bulan_periode,
			'koordinator_filter'     => $koordinator_filter,
			'id_koordinator_filter'  => $id_koordinator,
		];

		$html = $this->load->view('dashboard/cetak_laporan_rekap_rapel', $data, true);

		// Normalisasi karakter khusus
		$html = preg_replace('/[‐-‒–—−]/u', '-', $html);
		if (stripos($html, '<meta charset') === false) {
			$html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html;
		}

		// Buat PDF
		$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetFont('dejavusans', '', 9);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Perum TSI');
		$pdf->SetTitle('Laporan Rekap Pembayaran IPL ' . $nama_bulan_periode . ' ' . $tahun);
		$pdf->SetMargins(10, 10, 10, true);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');

		$nama_file = 'laporan_rekap_rapel_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . date('His') . '.pdf';
		$pdf->Output($nama_file, 'I');
	}

	public function laporan_pdf()
	{

		$this->checkSession();
		mb_internal_encoding('UTF-8');
		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		// Ambil parameter
		$id_koordinator = decrypt_url($this->input->get('id_koordinator', TRUE));
		$keyword        = $this->input->get('keyword', TRUE);
		$tahun          = $this->input->get('tahun', TRUE);

		if (empty($tahun)) {
			$tahun = date('Y');
		}

		// Jika role = koordinator, override id_koordinator dari session
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$id_koordinator = $this->session->userdata('username')['id'];
		}

		// Build filter
		$filter = [];
		if (!empty($id_koordinator)) {
			$filter[] = "vb.id_koordinator = '" . $this->db->escape_str($id_koordinator) . "'";
		}
		if (!empty($keyword)) {
			$filter[] = "(vb.nama LIKE '%" . $this->db->escape_str($keyword) . "%' 
                      OR vb.alamat LIKE '%" . $this->db->escape_str($keyword) . "%')";
		}

		$whereClause = '';
		if (!empty($filter)) {
			$whereClause = 'WHERE ' . implode(' AND ', $filter);
		}

		// Data rumah
		$rumah = $this->db->query("SELECT vb.* FROM (
                                    SELECT DISTINCT b.id, b.alamat, b.nama, c.nama as koordinator, b.id_koordinator 
                                    FROM master_users as a
                                    LEFT JOIN master_rumah as b ON a.id = id_rumah
                                    LEFT JOIN master_koordinator_blok as c ON b.id_koordinator = c.id
                                    ORDER BY b.alamat ASC
                               ) as vb " . $whereClause)->result_array();

		// Data koordinator
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$koordinator = $this->db->query("SELECT id, nama 
                                         FROM master_koordinator_blok 
                                         WHERE id = '" . $this->db->escape_str($id_koordinator) . "'")
				->result_array();
		} else {
			$koordinator = $this->db->query("SELECT id, nama FROM master_koordinator_blok")->result_array();
		}

		// Filter pembayaran by koordinator
		$where_koor = [];
		if (!empty($id_koordinator)) {
			$where_koor['id_koordinator'] = $id_koordinator;
		}

		// Bulan & tahun sekarang
		$bulan_ini = date('m');
		$tahun_ini = date('Y');

		// Helper function
		$get_total = function ($via, $bulan = null, $tahun = null) use ($where_koor) {
			$this_ci = &get_instance();
			$this_ci->db->select("SUM(mp.jumlah_bayar) as jumlah_bayar");
			$this_ci->db->from('master_pembayaran mp');
			$this_ci->db->join('master_users u', 'u.id = mp.user_id', 'left');
			$this_ci->db->join('master_rumah r', 'r.id = u.id_rumah', 'left');
			$this_ci->db->where('mp.status', 'verified');
			$this_ci->db->where('mp.pembayaran_via', $via);
			if ($bulan) $this_ci->db->where('MONTH(mp.bulan_mulai)', $bulan);
			if ($tahun) $this_ci->db->where('YEAR(mp.bulan_mulai)', $tahun);
			if (!empty($where_koor['id_koordinator'])) {
				$this_ci->db->where('r.id_koordinator', $where_koor['id_koordinator']);
			}
			return $this_ci->db->get()->row_array();
		};

		// Hitung total
		$jumlah_transfer_bulan_ini_transfer = $get_total('transfer', $bulan_ini, $tahun_ini);
		$jumlah_transfer_bulan_ini_koordinator = $get_total('koordinator', $bulan_ini, $tahun_ini);
		$jumlah_transfer_sd_transfer = $get_total('transfer');
		$jumlah_transfer_sd_koordinator = $get_total('koordinator');

		// Data untuk view
		$data = [
			'tahun'               => $tahun,
			'rumah'               => $rumah,
			'bulan_ini_koor'      => $jumlah_transfer_bulan_ini_koordinator,
			'bulan_ini_transfer'  => $jumlah_transfer_bulan_ini_transfer,
			'bulan_sd_koor'       => $jumlah_transfer_sd_koordinator,
			'bulan_sd_transfer'   => $jumlah_transfer_sd_transfer,
			'koordinator'         => $koordinator,
		];
		// === Buat PDF ===
		// return $this->load->view('dashboard/cetak_laporan_pembayaran', $data);
		// exit;
		$html = $this->load->view('dashboard/cetak_laporan_pembayaran', $data, true);

		// Normalisasi dash
		$html = preg_replace('/[‐-‒–—−]/u', '-', $html);

		// Tambahkan meta charset jika belum ada
		if (stripos($html, '<meta charset') === false) {
			$html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html;
		}

		// Buat objek PDF
		$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Perum TSI');
		$pdf->SetTitle('Laporan Pembayaran IPL - Paguyuban Taman Sukodono Indah');
		$pdf->SetMargins(10, 10, 10, true);
		$pdf->SetAutoPageBreak(TRUE, 10);
		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');

		// Output PDF
		$nama_file = 'laporan_pembayaran_' . date('YmdHis') . '.pdf';
		if ($this->session->userdata('username')) {
			$pdf->Output($nama_file, 'I'); // Preview di browser
		} else {
			$pdf->Output($nama_file, 'D'); // Download
		}
	}

	public function berhasil_dikirim()
	{
		echo "<script>alert('Berhasil Dikirim')</script>";
	}

	public function gagal()
	{
		echo "<script>alert('Gagal disimpan')</script>";
	}

	/**
	 * Halaman Profil Kepatuhan Warga
	 * Tab: Menunggak, Rajin Bayar, Bayar Di Muka
	 */
	public function profil_warga()
	{
		$this->checkSession();
		$data['halaman'] = 'dashboard/profil_warga';
		$this->load->view('modul', $data);
	}

	/**
	 * AJAX Endpoint: Data Profil Kepatuhan Warga
	 */
	public function ajax_profil_warga()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		$tahun = (int)($this->input->get('tahun', true) ?: date('Y'));
		$bulan_filter = $this->input->get('bulan', true);
		$tahun_sekarang = (int)date('Y');
		$bulan_sekarang = (int)date('n');

		$bulan_indo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$selected_koor = $this->session->userdata('username')['id'];
		} else {
			$selected_koor = $this->input->get('id_koordinator', true);
		}
		$where_koor = !empty($selected_koor) ? "AND r.id_koordinator = '".$this->db->escape_str($selected_koor)."'" : '';

		$bulan_mulai_ipl = ($tahun == 2025) ? 6 : 1;
		$bulan_akhir_ipl = !empty($bulan_filter) ? (int)$bulan_filter : (($tahun == $tahun_sekarang) ? $bulan_sekarang : 12);
		$total_bulan_wajib = max(1, $bulan_akhir_ipl - $bulan_mulai_ipl + 1);

		// Semua rumah
		$all_rumah = $this->db->query("
			SELECT r.id, r.alamat, r.nama, MAX(kl.no_hp) as no_hp, MAX(k.nama) as koordinator
			FROM master_users u
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
			LEFT JOIN master_keluarga kl ON kl.nomor_rumah = r.alamat AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
			WHERE r.id IS NOT NULL $where_koor
			GROUP BY r.id, r.alamat, r.nama, r.id_koordinator
			ORDER BY r.alamat ASC
		")->result_array();

		// Pembayaran (Verified & Pending)
		$pembayaran_raw = $this->db->query("
			SELECT u.id_rumah, p.untuk_bulan, p.bulan_rapel, DATE_FORMAT(p.bulan_mulai, '%Y-%m') as bulan_mulai_ym, p.status
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			WHERE p.status IN ('verified','pending')
			AND (YEAR(p.untuk_bulan) = $tahun OR YEAR(p.bulan_mulai) = $tahun OR p.bulan_rapel LIKE '%$tahun%')
			$where_koor
		")->result_array();

		$lunas_map = [];
		foreach ($pembayaran_raw as $p) {
			$idr = $p['id_rumah'];
			if (!isset($lunas_map[$idr])) $lunas_map[$idr] = [];
			if (!empty($p['untuk_bulan']) && $p['untuk_bulan'] != '0000-00-00' && $p['untuk_bulan'] != '') $lunas_map[$idr][date('Y-m', strtotime($p['untuk_bulan']))] = true;
			if (!empty($p['bulan_rapel'])) {
				foreach (explode(',', $p['bulan_rapel']) as $br) if (trim($br)) $lunas_map[$idr][trim($br)] = true;
			}
			if ((empty($p['untuk_bulan']) || $p['untuk_bulan'] == '0000-00-00' || $p['untuk_bulan'] == '') && empty($p['bulan_rapel']) && !empty($p['bulan_mulai_ym'])) $lunas_map[$idr][$p['bulan_mulai_ym']] = true;
		}

		$menunggak = []; $rajin = []; $dimuka = [];

		foreach ($all_rumah as $r) {
			$idr = $r['id'];
			$tunggak_names = [];
			$jumlah_bayar = 0;

			for ($m = $bulan_mulai_ipl; $m <= $bulan_akhir_ipl; $m++) {
				$key = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
				if (isset($lunas_map[$idr][$key])) $jumlah_bayar++;
				else $tunggak_names[] = $bulan_indo[$m];
			}

			$r['jumlah_bulan_bayar'] = $jumlah_bayar;
			$r['tunggakan'] = count($tunggak_names);
			$r['bulan_tunggak_list'] = implode(', ', $tunggak_names);
			$r['status_tunggak'] = $r['tunggakan'] > 0 ? ($r['tunggakan'] >= 3 ? 'Surat Teguran' : 'Peringatan') : 'Lunas';
			$r['level'] = $r['tunggakan'] >= 3 ? 'danger' : ($r['tunggakan'] > 0 ? 'warning' : 'success');

			if ($r['tunggakan'] > 0) {
				$menunggak[] = $r;
			} else {
				$is_dimuka = false; $dm_count = 0;
				if (isset($lunas_map[$idr])) {
					foreach ($lunas_map[$idr] as $ym => $val) {
						$p = explode('-', $ym);
						if ($p[0] > $tahun || ($p[0] == $tahun && (int)$p[1] > $bulan_akhir_ipl)) {
							$is_dimuka = true; $dm_count++;
						}
					}
				}
				if ($is_dimuka) { $r['bulan_dimuka'] = $dm_count; $dimuka[] = $r; }
				else { $rajin[] = $r; }
			}
		}

		usort($menunggak, function($a,$b){ return $b['tunggakan'] - $a['tunggakan']; });
		usort($rajin, function($a,$b){ return $b['jumlah_bulan_bayar'] - $a['jumlah_bulan_bayar']; });
		usort($dimuka, function($a,$b){ return ($b['bulan_dimuka'] ?? 0) - ($a['bulan_dimuka'] ?? 0); });

		echo json_encode(['stats' => ['total'=>count($all_rumah), 'menunggak'=>count($menunggak), 'rajin'=>count($rajin), 'dimuka'=>count($dimuka)], 'total_bulan_wajib'=>$total_bulan_wajib, 'periode'=>$bulan_indo[$bulan_mulai_ipl].' - '.$bulan_indo[$bulan_akhir_ipl], 'menunggak'=>$menunggak, 'rajin'=>$rajin, 'dimuka'=>$dimuka]);
	}

	/**
	 * Generate PDF Surat Teguran Pembayaran IPL
	 */
	public function surat_teguran_pdf($save_path = null)
	{
		$this->checkSession();
		mb_internal_encoding('UTF-8');
		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		$id_rumah = $this->input->get('id_rumah', true);
		$tahun = (int)($this->input->get('tahun', true) ?: date('Y'));
		$bulan_filter = $this->input->get('bulan', true);

		if (empty($id_rumah)) { show_error('ID Rumah tidak valid'); return; }

		$rumah = $this->db->get_where('master_rumah', ['id' => $id_rumah])->row_array();
		if (!$rumah) { show_error('Data rumah tidak ditemukan'); return; }
		$user = $this->db->get_where('master_users', ['id_rumah' => $id_rumah])->row_array();
		$nama = $user['nama'] ?? $rumah['nama'] ?? '-';
		$alamat = $rumah['alamat'] ?? '-';

		// Logika Tunggakan (Sinkron)
		$bulan_mulai_ipl = ($tahun == 2025) ? 6 : 1;
		$bulan_akhir = !empty($bulan_filter) ? (int)$bulan_filter : (($tahun == (int)date('Y')) ? (int)date('n') : 12);
		$bulan_indo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

		$p_raw = $this->db->query("SELECT untuk_bulan, bulan_rapel, DATE_FORMAT(bulan_mulai, '%Y-%m') as bulan_mulai_ym FROM master_pembayaran WHERE user_id IN (SELECT id FROM master_users WHERE id_rumah = ?) AND status IN ('verified', 'pending') AND (YEAR(untuk_bulan) = ? OR YEAR(bulan_mulai) = ? OR bulan_rapel LIKE ?)", [$id_rumah, $tahun, $tahun, '%'.$tahun.'%'])->result_array();
		$paid = [];
		foreach ($p_raw as $p) {
			if (!empty($p['untuk_bulan']) && $p['untuk_bulan'] != '0000-00-00' && $p['untuk_bulan'] != '') $paid[date('Y-m', strtotime($p['untuk_bulan']))] = true;
			if (!empty($p['bulan_rapel'])) { foreach (explode(',', $p['bulan_rapel']) as $br) if (trim($br)) $paid[trim($br)] = true; }
			if ((empty($p['untuk_bulan']) || $p['untuk_bulan'] == '0000-00-00' || $p['untuk_bulan'] == '') && empty($p['bulan_rapel']) && !empty($p['bulan_mulai_ym'])) $paid[$p['bulan_mulai_ym']] = true;
		}

		$tunggak_list = [];
		for ($m = $bulan_mulai_ipl; $m <= $bulan_akhir; $m++) {
			$key = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
			if (!isset($paid[$key])) $tunggak_list[] = $bulan_indo[$m] . ' ' . $tahun;
		}

		$jml_tunggak = count($tunggak_list);
		if ($jml_tunggak == 0) { show_error('Warga ini tidak memiliki tunggakan.'); return; }
		$total_tunggakan = $jml_tunggak * 125000;
		$list_bulan_str = implode(', ', $tunggak_list);

		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator('Perum TSI'); $pdf->SetTitle('Surat Teguran IPL');
		$pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
		$pdf->SetMargins(25, 15, 25); $pdf->AddPage();

		$lm = 25; $rm = 185; $cw = $rm - $lm;
		$logo = FCPATH . 'logo-tsi.png';
		if (file_exists($logo)) $pdf->Image($logo, $lm, 15, 20, 20, 'PNG');

		$pdf->SetXY($lm + 23, 16);
		$pdf->SetFont('dejavusans', 'B', 12); $pdf->SetTextColor(30, 120, 180);
		$pdf->Cell($cw - 23, 6, 'PAGUYUBAN WARGA PERUMAHAN', 0, 1, 'C');
		$pdf->SetX($lm + 23); $pdf->Cell($cw - 23, 6, 'TAMAN SUKODONO INDAH', 0, 1, 'C');
		$pdf->SetX($lm + 23); $pdf->SetFont('dejavusans', '', 9); $pdf->SetTextColor(80, 80, 80);
		$pdf->Cell($cw - 23, 5, 'Ds. Jumputrejo, Kec. Sukodono, Kab. Sidoarjo, 61258.', 0, 1, 'C');

		$pdf->SetY(38); $pdf->SetDrawColor(30, 120, 180); $pdf->SetLineWidth(0.8); $pdf->Line($lm, 38, $rm, 38);

		$pdf->Ln(6); $pdf->SetTextColor(0); $pdf->SetFont('dejavusans', '', 10);
		$no_surat = str_pad($id_rumah, 3, '0', STR_PAD_LEFT) . '/TGR-IPL/TSI/' . ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][(int)date('n')] . '/' . date('Y');
		$pdf->Cell(90, 6, 'Nomor  : ' . $no_surat, 0, 0, 'L');
		$pdf->Cell($cw - 90, 6, 'Sidoarjo, ' . date('d') . ' ' . $bulan_indo[(int)date('n')] . ' ' . date('Y'), 0, 1, 'R');
		$pdf->Cell(0, 6, 'Perihal : Teguran Pembayaran IPL', 0, 1, 'L');

		$pdf->Ln(6); $pdf->Cell(0, 6, 'Assalamu\'alaikum Wr. Wb.', 0, 1);
		$pdf->Ln(3); $pdf->Cell(0, 6, 'Yang terhormat Bapak/Ibu warga Perumahan Taman Sukodono Indah.', 0, 1);

		$pdf->Ln(3); $pdf->Cell(10, 6, '', 0, 0); $pdf->Cell(50, 6, 'Nama', 0, 0); $pdf->Cell(5, 6, ':', 0, 0); $pdf->SetFont('dejavusans', 'B', 10); $pdf->Cell(0, 6, $nama, 0, 1);
		$pdf->SetFont('dejavusans', '', 10); $pdf->Cell(10, 6, '', 0, 0); $pdf->Cell(50, 6, 'Alamat TSI', 0, 0); $pdf->Cell(5, 6, ':', 0, 0); $pdf->SetFont('dejavusans', 'B', 10); $pdf->Cell(0, 6, $alamat, 0, 1);

		// === ISI SURAT ===
		$pdf->Ln(4);
		$isi = 'Dengan hormat, melalui surat ini, kami sebagai pengurus lingkungan perum TSI memberitahukan bahwa menurut pembukuan kami, Bapak/Ibu masih memiliki kewajiban pembayaran IPL yang belum terlunasi sebesar :';
		$pdf->MultiCell(0, 6, $isi, 0, 'J');

		// Nominal besar
		$pdf->Ln(1);
		$pdf->SetFont('dejavusans', 'B', 13);
		$pdf->Cell(0, 8, 'Rp ' . number_format($total_tunggakan, 0, ',', '.') . ',-', 0, 1, 'C');
		$pdf->SetFont('dejavusans', '', 10);

		// === DETAIL TUNGGAKAN ===
		$pdf->Ln(2);
		$lbl_d = 55; // label width for detail

		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_d, 6, 'Perhitungan mulai bulan', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->Cell(0, 6, 'Bulan ' . ($tunggak_list[0] ?? '-'), 0, 1);

		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_d, 6, 'Jumlah bulan tunggak', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->Cell(0, 6, $jml_tunggak . ' bulan', 0, 1);

		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_d, 6, 'Dengan nominal', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->Cell(0, 6, 'Rp 125.000,- / bulan', 0, 1);

		// Daftar bulan yang belum dibayar
		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_d, 6, 'Bulan belum dibayar', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->MultiCell(0, 6, $list_bulan_str, 0, 'L');

		$pdf->Ln(4); $pdf->MultiCell(0, 6, 'Dan apabila Bapak/Ibu telah membayar iuran IPL sebelum surat pemberitahuan ini diterima, kami mohon konfirmasi melalui Koordinator Blok atau Bendahara kami.', 0, 'J');
		$pdf->Ln(4); $pdf->Cell(0, 6, 'Demikian surat ini kami sampaikan. Atas perhatiannya kami ucapkan terima kasih.', 0, 1);
		$pdf->Ln(2); $pdf->Cell(0, 6, 'Wassalamu\'alaikum Wr. Wb.', 0, 1);

		$pdf->Ln(10); $ttd_y = $pdf->GetY(); $col_w = $cw / 2; $stamp_w = 40; $stamp_h = 16;
		$pdf->SetFont('dejavusans', '', 10); $pdf->SetXY($lm, $ttd_y); $pdf->Cell($col_w, 5, 'Bidang Lingkungan TSI', 0, 0, 'C'); $pdf->Cell($col_w, 5, 'Bendahara TSI', 0, 1, 'C');
		
		$pdf->Ln(2); $curr_y = $pdf->GetY(); $pdf->SetDrawColor(0, 128, 0); $pdf->SetTextColor(0, 128, 0); $pdf->SetLineWidth(0.3);
		$xk = $lm + ($col_w - $stamp_w) / 2; $pdf->RoundedRect($xk, $curr_y, $stamp_w, $stamp_h, 2, '1111', 'D');
		$pdf->SetXY($xk, $curr_y + 2); $pdf->SetFont('dejavusans', 'B', 7); $pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C'); $pdf->SetX($xk); $pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');
		$xn = $lm + $col_w + ($col_w - $stamp_w) / 2; $pdf->RoundedRect($xn, $curr_y, $stamp_w, $stamp_h, 2, '1111', 'D');
		$pdf->SetXY($xn, $curr_y + 2); $pdf->SetFont('dejavusans', 'B', 7); $pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C'); $pdf->SetX($xn); $pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');

		// Nama TTD Atas
		$pdf->Ln(17); // Jarak dikurangi agar lebih pas
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetX($lm);
		$pdf->SetFont('dejavusans', 'BU', 10);
		$pdf->Cell($col_w, 5, 'Angger', 0, 0, 'C');
		$pdf->Cell($col_w, 5, 'Dhani Kispananto', 0, 1, 'C');

		// === BAGIAN BAWAH (KETUA DENGAN TTE) ===
		$pdf->Ln(7);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->Cell(0, 5, 'Mengetahui, Ketua Paguyuban TSI', 0, 1, 'C');

		// TTE Stamp di tengah bawah
		$pdf->Ln(2);
		$sy = $pdf->GetY();
		$sx = $lm + ($cw - $stamp_w) / 2;
		$pdf->SetDrawColor(0, 128, 0);
		$pdf->SetTextColor(0, 128, 0);
		$pdf->RoundedRect($sx, $sy, $stamp_w, $stamp_h, 2, '1111', 'D');
		$pdf->SetXY($sx, $sy+2);
		$pdf->SetFont('dejavusans', 'B', 7);
		$pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C');
		$pdf->SetX($sx);
		$pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');

		$pdf->Ln(11); // Jarak dikurangi agar lebih pas
		$pdf->SetTextColor(0);
		$pdf->SetFont('dejavusans', 'BU', 10);
		$pdf->Cell(0, 5, 'Mulyono', 0, 1, 'C');

		if ($save_path) {
			$pdf->Output($save_path, 'F');
		} else {
			$pdf->Output('Surat_Teguran_IPL_' . str_replace(' ', '_', $alamat) . '.pdf', 'I');
		}
	}

	/**
	 * Kirim Surat Teguran via WhatsApp Gateway (API)
	 */
	public function kirim_teguran_wa()
	{
		$this->checkSession();
		$id_rumah = $this->input->get('id_rumah', true);
		$tahun = (int)($this->input->get('tahun', true) ?: date('Y'));
		$bulan_filter = $this->input->get('bulan', true);

		// Ambil data untuk pesan
		$rumah = $this->db->get_where('master_rumah', ['id' => $id_rumah])->row_array();
		$keluarga = $this->db->query("SELECT no_hp FROM master_keluarga WHERE nomor_rumah LIKE '%".$this->db->escape_like_str($rumah['alamat'])."%' AND no_hp IS NOT NULL AND no_hp != '' LIMIT 1")->row_array();
		$no_hp = $keluarga['no_hp'] ?? '';
		$bulan_indo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

		$tunggak_names = [];
		for ($m = $bulan_mulai_ipl; $m <= $bulan_akhir_hitung; $m++) {
			$key = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
			if (!isset($bulan_lunas[$key])) $tunggak_names[] = $bulan_indo[$m];
		}
		
		$jml_tunggak = count($tunggak_names);
		$total_rupiah = $jml_tunggak * 125000;
		$list_bulan_str = implode(', ', $tunggak_names);

		// 1. Generate PDF ke file fisik agar bisa diakses via URL
		$filename = 'Surat_Teguran_' . $id_rumah . '_' . date('YmdHis') . '.pdf';
		$temp_dir = FCPATH . 'assets/temp_pdf/';
		if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);
		$filepath = $temp_dir . $filename;
		
		// Generate file
		$this->surat_teguran_pdf($filepath);

		// 2. Kirim via WA (Gunakan send_wa_doc)
		$this->load->helper('wa');
		$media_url = base_url('assets/temp_pdf/' . $filename);
		$caption = "⚠️ *PEMBERITAHUAN TUNGGAKAN IPL*\n\nAssalamu'alaikum Bapak/Ibu *".$rumah['nama']."*,\n\nKami melampirkan Surat Pemberitahuan Tunggakan IPL untuk rumah *".$rumah['alamat']."* sebesar *Rp ".number_format($total_rupiah,0,',','.')."* ($list_bulan_str).\n\nMohon segera melakukan koordinasi pembayaran. Terima kasih.\n\n*Pengurus Paguyuban TSI*";

		$res = send_wa_doc($no_hp, $media_url, $filename, $caption);

		if (isset($res['status']) && ($res['status'] === true || $res['status'] == '1')) {
			// Catat Log
			$this->db->insert('log_teguran', [
				'id_rumah' => $id_rumah,
				'tgl_kirim' => date('Y-m-d H:i:s'),
				'dikirim_ke' => $no_hp,
				'status' => 'success',
				'nominal' => $total_rupiah,
				'bulan_tunggak' => $list_bulan_str
			]);
			echo json_encode(['status' => 'success', 'message' => 'Surat teguran berhasil dikirim sebagai lampiran dokumen.']);
		} else {
			// Catat Gagal
			$this->db->insert('log_teguran', [
				'id_rumah' => $id_rumah,
				'tgl_kirim' => date('Y-m-d H:i:s'),
				'dikirim_ke' => $no_hp,
				'status' => 'failed',
				'nominal' => $total_rupiah,
				'bulan_tunggak' => $list_bulan_str
			]);
			echo json_encode(['status' => 'error', 'message' => 'Gagal kirim dokumen WA: ' . ($res['message'] ?? 'Error API')]);
		}
	}

	/**
	 * Halaman Log Laporan Koordinator
	 * Menampilkan koordinator yang rajin entry dan yang tidak
	 */
	public function log_koordinator()
	{
		$this->checkSession();

		// Filter bulan & tahun
		$bulan = $this->input->get('bulan') ?: date('m');
		$tahun = $this->input->get('tahun') ?: date('Y');

		// Semua koordinator
		$koordinator_all = $this->db->query("SELECT id, nama, username FROM master_koordinator_blok ORDER BY nama ASC")->result_array();

		// Hitung entry per koordinator bulan ini (termasuk pending)
		$entry_data = $this->db->query("
			SELECT COALESCE(u.id_koordinator, r.id_koordinator) as id_koordinator,
				   COUNT(p.id) as total_entry,
				   SUM(p.jumlah_bayar) as total_nominal,
				   MAX(p.created_at) as last_entry,
				   SUM(CASE WHEN p.status='verified' THEN 1 ELSE 0 END) as verified,
				   SUM(CASE WHEN p.status='pending' THEN 1 ELSE 0 END) as pending,
				   SUM(CASE WHEN p.status='rejected' THEN 1 ELSE 0 END) as rejected
			FROM master_pembayaran p
			LEFT JOIN master_users u ON u.id = p.user_id
			LEFT JOIN master_rumah r ON r.id = u.id_rumah
			WHERE MONTH(p.created_at) = ?
			AND YEAR(p.created_at) = ?
			AND p.pembayaran_via = 'koordinator'
			AND p.status IN ('verified', 'pending')
			GROUP BY COALESCE(u.id_koordinator, r.id_koordinator)
		", [$bulan, $tahun])->result_array();

		// Index by id_koordinator
		$entry_map = [];
		foreach ($entry_data as $e) {
			if ($e['id_koordinator'] !== null) {
				$entry_map[$e['id_koordinator']] = $e;
			}
		}

		// Hitung jumlah rumah per koordinator
		$rumah_data = $this->db->query("SELECT id_koordinator, COUNT(*) as jml_rumah FROM master_rumah WHERE id_koordinator IS NOT NULL GROUP BY id_koordinator")->result_array();
		$rumah_map = [];
		foreach ($rumah_data as $rd) {
			$rumah_map[$rd['id_koordinator']] = $rd['jml_rumah'];
		}

		// Gabungkan data
		$result = [];
		foreach ($koordinator_all as $k) {
			$kid = $k['id'];
			$entry = $entry_map[$kid] ?? null;
			$result[] = [
				'id' => $kid,
				'nama' => $k['nama'],
				'username' => $k['username'],
				'jml_rumah' => $rumah_map[$kid] ?? 0,
				'total_entry' => $entry['total_entry'] ?? 0,
				'total_nominal' => $entry['total_nominal'] ?? 0,
				'last_entry' => $entry['last_entry'] ?? null,
				'verified' => $entry['verified'] ?? 0,
				'pending' => $entry['pending'] ?? 0,
				'rejected' => $entry['rejected'] ?? 0,
			];
		}

		// Hitung total entry semua bulan per koordinator (untuk chart, termasuk pending)
		$trend_data = $this->db->query("
			SELECT COALESCE(u.id_koordinator, r.id_koordinator) as id_koordinator,
				   DATE_FORMAT(p.created_at, '%Y-%m') as bulan,
				   COUNT(p.id) as total
			FROM master_pembayaran p
			LEFT JOIN master_users u ON u.id = p.user_id
			LEFT JOIN master_rumah r ON r.id = u.id_rumah
			WHERE YEAR(p.created_at) = ?
			AND p.pembayaran_via = 'koordinator'
			AND p.status IN ('verified', 'pending')
			GROUP BY COALESCE(u.id_koordinator, r.id_koordinator), DATE_FORMAT(p.created_at, '%Y-%m')
			ORDER BY bulan ASC
		", [$tahun])->result_array();

		$data['koordinator_list'] = $result;
		$data['trend_data'] = $trend_data;
		$data['bulan_filter'] = $bulan;
		$data['tahun_filter'] = $tahun;
		$data['halaman'] = 'dashboard/log_koordinator';
		$this->load->view('modul', $data);
	}

	public function checkSession()
	{
		if (empty($this->session->userdata['username'])) {
			redirect('./');
		}
	}
}
