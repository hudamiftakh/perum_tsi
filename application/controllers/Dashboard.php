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
		$id_rumah = $this->input->post('id_rumah'); // bisa juga dari session

		$bulan = date('m', strtotime($tanggal));
		$tahun = date('Y', strtotime($tanggal));

		$data_pembayaran = $this->db->query("
			SELECT a.id as id_pembayaran, a.id_rumah FROM master_pembayaran AS a
			LEFT JOIN master_users AS b ON a.user_id = b.id
			WHERE MONTH(a.bulan_mulai) = '$bulan'
			AND YEAR(a.bulan_mulai) = '$tahun'
			AND b.id_rumah = '$id_rumah'
		")->row_array();

		if ($data_pembayaran) {
			echo json_encode([
				'sudah_dibayar' => true,
				'id_rumah' => encrypt_url($id_rumah),
				'id_pembayaran' => encrypt_url($data_pembayaran['id_pembayaran']) // ID dari `master_pembayaran`
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
		}else{
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
		$get_total = function($via, $bulan = null, $tahun = null) use ($where_koor) {
			$this_ci = $this;
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
		if (in_array($pembayaran_via,array('transfer','transfer_2'))) {
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
	// 	$bulan_mulai_db = $bulan_mulai ? $bulan_mulai . '-01' : null;
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
			// $pembayaran_id = $this->db->insert_id();
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
		// Ambil ID dari POST
		$id = $this->input->post('id');
		$aksi = $this->input->post('aksi');

		// Validasi ID
		if (empty($id) || !is_numeric($id)) {
			echo json_encode(['status' => 'error', 'message' => 'ID tidak valid']);
			return;
		}

		// Cek apakah data dengan ID tersebut ada
		$cek = $this->db->get_where('master_pembayaran', ['id' => $id])->row();
		if (!$cek) {
			echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
			return;
		}

		// Lakukan update status
		$statusBaru = $aksi === 'verified' ? 'verified' : 'rejected';
		$this->db->where('id', $id);
		$update = $this->db->update('master_pembayaran', ['status' => $statusBaru]);

		if ($update) {
			// Ambil data pembayaran & user
			$pembayaran = $this->db->get_where('master_pembayaran', ['id' => $id])->row_array();
			$user = $this->db->get_where('master_users', ['id' => $pembayaran['user_id']])->row_array();
			$rumah = $this->db->get_where('master_rumah', ['id' => $user['id_rumah']])->row_array();

			// Ambil data keluarga berdasarkan id_rumah
			$keluarga = $this->db->query("SELECT * FROM master_keluarga WHERE nomor_rumah LIKE '%" . $this->db->escape_like_str($rumah['alamat']) . "%'")->row_array();

			$nama = $user['nama'] ?? '';
			$alamat = $rumah['alamat'] ?? '';
			$no_hp = $keluarga['no_hp'] ?? '';

			// Validasi nomor HP, jika kosong ambil dari master_keluarga lain yang cocok
			if (empty($no_hp)) {
				$keluarga_alt = $this->db->query("SELECT no_hp FROM master_keluarga WHERE nomor_rumah LIKE '%" . $this->db->escape_like_str($rumah['alamat']) . "%' AND no_hp IS NOT NULL AND no_hp != '' LIMIT 1")->row_array();
				$no_hp = $keluarga_alt['no_hp'] ?? '';
			}

			$bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));

			// Buat link pembayaran terenkripsi
			$link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));

			$text = "📥 Konfirmasi Pembayaran IPL

Assalamu’alaikum/Salam sejahtera Bapak/Ibu *$nama*,

Terima kasih kami ucapkan atas pembayaran IPL bulan *$bulan* sebesar **Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "** yang telah kami terima. 🙏
💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "
📄 Bukti: Sudah diterima
🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "
📑 Kitir Pembayaran: $link

Pembayaran Bapak/Ibu sangat membantu dalam operasional dan pemeliharaan lingkungan kita bersama.

Jika ada pertanyaan atau masukan, silakan hubungi kami kapan saja.

Hormat kami,
Pengurus Paguyuban TSI
Perumahan Taman Sukodono Indah
_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";
			// Kirim notifikasi via POST ke WA Gateway jika nomor HP valid
			$wa_url = 'https://wa2.digitalminsajo.sch.id/send-message';
			$post_data = [
				'session' => 'wa2',
				'to' => hp($no_hp),
				'text' => $text
			];

			// Kirim POST (gunakan CURL) dengan error handling
			try {
				$ch = curl_init($wa_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
				curl_setopt($ch, CURLOPT_POST, true);
				$response = curl_exec($ch);
				$curl_error = curl_error($ch);
				curl_close($ch);

				if ($response === false || !empty($curl_error)) {
					// Jika gagal kirim WA, rollback update status
					$this->db->where('id', $id);
					$this->db->update('master_pembayaran', ['status' => $cek->status]); // kembalikan status semula
					echo json_encode(['status' => 'error', 'message' => 'Gagal kirim notifikasi WA, status tidak diupdate']);
					return;
				}

				echo json_encode(['status' => 'success', 'message' => 'Data berhasil diverifikasi']);
			} catch (Exception $e) {
				// Rollback update status jika error
				$this->db->where('id', $id);
				$this->db->update('master_pembayaran', ['status' => $cek->status]);
				echo json_encode(['status' => 'error', 'message' => 'Gagal kirim notifikasi WA: ' . $e->getMessage()]);
			}
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data']);
		}
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
				$post_data = [
					'session' => 'wa2',
					'to' => $no_hp,
					'text' => $text
				];

				$ch = curl_init($wa_url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
				curl_setopt($ch, CURLOPT_POST, true);
				$response = curl_exec($ch);
				$error = curl_error($ch);
				curl_close($ch);

				if ($response !== false && empty($error)) {
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
	public function invoice(){
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
	public function update_password()  {
		$data['halaman'] = 'dashboard/update_password';
		$this->load->view('modul', $data);
	}
	public function berhasil_dikirim()
	{
		echo "<script>alert('Berhasil Dikirim')</script>";
	}

	public function gagal()
	{
		echo "<script>alert('Gagal disimpan')</script>";
	}

	public function checkSession()
	{
		if (empty($this->session->userdata['username'])) {
			redirect('./');
		}
	}
}