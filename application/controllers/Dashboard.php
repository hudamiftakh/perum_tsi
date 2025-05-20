<?php
defined('BASEPATH') or exit('No direct script access allowed');

class dashboard extends CI_Controller
{

	public function __construct()
	{
		error_reporting(0);
		parent::__construct();
		$this->load->library('session');
		$this->load->library('pagination');
		$this->load->library('session');
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
		$this->load->view('dashboard/pendataan_keluarga');
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
		$this->db->select('*')->from('master_keluarga');

		if (!empty($keyword)) {
			$this->db->group_start()
				->like('no_kk', $keyword)
				->or_like('alamat', $keyword)
				->or_where("id IN (SELECT keluarga_id FROM master_anggota_keluarga WHERE nik LIKE '%" . $this->db->escape_like_str($keyword) . "%' OR nama LIKE '%" . $this->db->escape_like_str($keyword) . "%')", NULL, FALSE)
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
			'start'=> $start,
			'total_rows'=> $total_rows,
			'per_page'=> $config['per_page'],
			'current_page'=> floor($start / $config['per_page']) + 1,
			'total_pages'=> ceil($total_rows / $config['per_page']),
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
        $config['max_size'] = 5120;

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file_kk')) {
            echo json_encode(['status' => 'error', 'message' => $this->upload->display_errors()]);
            return;
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
                    'tgl_lahir' => $this->input->post('tgl_lahir')[$key]
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
		$config['max_size'] = 5120;
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
			$new_file_name = $no_kk."-".date('ymd'). $ext;
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

	public function laporan_pembayaran($tahun = null)
	{
		if ($tahun === null) {
			$tahun = date('Y');
		}
		$keyword      = $this->input->get('keyword');
		$tahun          = $this->input->get('tahun');
		$id_koordinator = $this->input->get('id_koordinator');
		// Ambil semua rumah
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
										SELECT DISTINCT b.alamat, b.nama, c.nama as koordinator,b.id_koordinator FROM master_users as a
										LEFT JOIN master_rumah as b ON a.id = id_rumah
										LEFT JOIN master_koordinator_blok as c ON b.id_koordinator = c.id
										ORDER BY b.alamat ASC
									) as vb ".$whereClause)->result_array();
		$koordinator = $this->db->query("SELECT DISTINCT id, nama FROM master_koordinator_blok")->result_array();

		$data['tahun'] = $tahun;
		$data['rumah'] = $rumah;
		$data['koordinator'] = $koordinator;
		$data['halaman'] = 'dashboard/laporan_pembayaran';
		$this->load->view('modul', $data);
	}

	public function verifikasi_pembayaran()
	{
		$sql = "
            SELECT p.*, u.nama, u.rumah 
            FROM master_pembayaran p
            JOIN master_users u ON u.id = p.user_id
            WHERE p.status = 'pending'
            ORDER BY p.created_at DESC
        ";
        $data['pembayaran'] = $this->db->query($sql)->result();
		$data['halaman'] = 'dashboard/verifikasi_pembayaran';
		$this->load->view('modul', $data);
	}

	public function save_pembayaran()
	{
		// Ambil data dari POST
		$user_id = $this->input->post('user_id');
		$metode = $this->input->post('metode');
		$bulan_mulai = $this->input->post('bulan_mulai'); // format yyyy-mm
		$jumlah_bayar = $this->input->post('jumlah_bayar');
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
		$nama_file_bukti = null;

		// Jika via transfer, lakukan upload
		if ($pembayaran_via === 'transfer') {
			$config['upload_path']   = './uploads/bukti/';
			$config['allowed_types'] = 'jpg|jpeg|png|pdf';
			$config['max_size']      = 2048; // 2MB
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
			}
		}

		// Siapkan data pembayaran
		$data_pembayaran = [
			'user_id' => $user_id,
			'metode' => $metode,
			'bulan_mulai' => $bulan_mulai ? $bulan_mulai . '-01' : null,
			'jumlah_bayar' => $jumlah_bayar,
			'bukti' => $nama_file_bukti, // <-- ini penting
			'keterangan' => $keterangan,
			'created_at' => date('Y-m-d H:i:s'),
		];

		// Insert ke tabel pembayaran
		$this->db->insert('master_pembayaran', $data_pembayaran);
		$pembayaran_id = $this->db->insert_id();

		if (!$pembayaran_id) {
			$this->session->set_flashdata('error', 'Gagal menyimpan pembayaran');
			redirect('pembayaran');
			return;
		}

		if ($metode === 'cicilan') {
			if (!$lama_cicilan || !$total_cicilan) {
				$this->session->set_flashdata('error', 'Data cicilan wajib diisi');
				redirect('pembayaran');
				return;
			}

			$data_cicilan = [
				'pembayaran_id' => $pembayaran_id,
				'lama_cicilan' => intval($lama_cicilan),
				'total_cicilan' => intval($total_cicilan)
			];
			$this->db->insert('master_detail_cicilan', $data_cicilan);
		} else {
			// Hitung jumlah bulan bayar dari metode
			$bulanCount = 1;
			if (strpos($metode, '_bulan') !== false) {
				$bulanCount = intval(explode('_', $metode)[0]);
			} elseif ($metode === '7_tahun') {
				$bulanCount = 12;
			}

			if ($bulan_mulai) {
				$nominal_per_bulan = 125000;
				$startDate = new DateTime($bulan_mulai . '-01');
				for ($i = 0; $i < $bulanCount; $i++) {
					$bulan = $startDate->format('Y-m-01');

					$this->db->insert('master_detail_pembayaran_bulanan', [
						'pembayaran_id' => $pembayaran_id,
						'bulan'         => $bulan,
						'nominal'       => $nominal_per_bulan
					]);
					$startDate->modify('+1 month');
				}
			}
		}

		$this->session->set_flashdata('success', 'Pembayaran berhasil disimpan');
		redirect('pembayaran-sukses?data=' . urlencode(json_encode($data_pembayaran)));
	}


	public function pembayaran_sukses()
	{
		$this->load->view('dashboard/pembayaran_sukses');
	}

	public function logout()
	{
		$this->session->sess_destroy();
		redirect('./login');
	}

	public function berhasil()
	{
		echo "<script>alert('Berhasil disimpan')</script>";
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

?>