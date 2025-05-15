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

	public function show_form_participant()
	{
		$this->load->view('dashboard/show_form_participant');
	}

	public function setting_jam()
	{
		$this->checkSession();
		if (isset($_REQUEST['setting_all'])) {
			$jam_masuk = $_REQUEST['jam_masuk'];
			$jam_pulang = $_REQUEST['jam_pulang'];
			$data = array(
				'jam_masuk' => $jam_masuk,
				'jam_pulang' => $jam_pulang
			);
			$result = $this->db->update('tb_kelas', $data);
			if ($result) {
				$this->berhasil();
				redirect('setting_jam');
			} else {
				$this->gagal();
				redirect('setting_jam');
			}
		}


		if (isset($_REQUEST['update'])) {
			$jam_masuk = $_REQUEST['jam_masuk'];
			$jam_pulang = $_REQUEST['jam_pulang'];
			$id = $_REQUEST['id'];
			$data = array(
				'jam_masuk' => $jam_masuk,
				'jam_pulang' => $jam_pulang
			);
			$this->db->where(array('id' => $id));
			$result = $this->db->update('tb_kelas', $data);
			// echo $this->db->last_query();
			if ($result) {
				$this->berhasil();
				redirect('setting_jam');
			} else {
				$this->gagal();
				redirect('setting_jam');
			}
		}

		if (isset($_REQUEST['update_check'])) {
			$jam_masuk = $_REQUEST['jam_masuk'];
			$jam_pulang = $_REQUEST['jam_pulang'];
			foreach ($jam_masuk as $key => $jm) {
				$data = array('jam_masuk' => $jm);
				$this->db->where(array('id' => $key));
				$result = $this->db->update('tb_kelas', $data);
			}
			foreach ($jam_pulang as $key => $jp) {
				$data = array('jam_pulang' => $jp);
				$this->db->where(array('id' => $key));
				$result = $this->db->update('tb_kelas', $data);
			}

			redirect('setting_jam');
		}
		$data['halaman'] = 'absensi/setting_jam';
		$this->load->view('modul', $data);
	}
	public function absensi_masuk()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/absensi_masuk';
		$this->load->view('modul_absen', $data);
	}
	public function absensi_pulang()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/absensi_pulang';
		$this->load->view('modul_absen', $data);
	}
	public function siswa()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/siswa';
		$this->load->view('modul', $data);
	}
	public function act_siswa()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/act_siswa';
		$this->load->view('modul', $data);
	}
	public function kelas()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/kelas';
		$this->load->view('modul', $data);
	}
	public function save_kelas()
	{
		$this->checkSession();
		$kelas = $_REQUEST['kelas'];
		$abjad = $_REQUEST['abjad'];
		$nm_kelas = $kelas . "-" . $abjad;
		$nm_walikelas = $_REQUEST['nm_walikelas'];
		$hp_walikelas = $_REQUEST['hp_walikelas'];
		$data = array(
			'kelas' => $kelas,
			'abjad' => $abjad,
			'nama' => $nm_kelas,
			'nm_walikelas' => $nm_walikelas,
			'hp_walikelas' => str_replace('+', '', hp($hp_walikelas))
		);
		if (!empty($_REQUEST['id'])) {
			$this->db->where('id', $_REQUEST['id']);
			$exc = $this->db->update('tb_kelas', $data);
		} else {
			$exc = $this->db->insert('tb_kelas', $data);
		}

		if ($exc) {
			$alert['alert'] = '
			<div class="alert alert-success alert-dismissible" role="alert">
			<div class="alert-message">
			<strong>Perhatian !! Data berhasil disimpan</strong>
			</div>
			</div>
			';
			$this->session->set_flashdata('alert', $alert);
			if (!empty($_REQUEST['id'])) {
				redirect('act_kelas?id=' . $_REQUEST['id']);
			} else {
				redirect('act_kelas');
			}
		} else {
			$alert['alert'] = '
			<div class="alert alert-danger alert-dismissible" role="alert">
			<div class="alert-message">
			<strong>Perhatian !! Data gagal disimpan</strong>
			</div>
			</div>
			';
			$this->session->set_flashdata('alert', $alert);
			if (!empty($_REQUEST['id'])) {
				redirect('act_kelas?id=' . $_REQUEST['id']);
			} else {
				redirect('act_kelas');
			}

		}
	}
	public function laporan()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/laporan';
		$this->load->view('modul', $data);
	}
	public function isi_alasan()
	{
		// $this->checkSession();
		if (isset($_REQUEST['simpan'])) {
			$id_siswa = $_REQUEST['id_siswa'];
			$tgl_awal = $_REQUEST['tgl_awal'];
			$tgl_akhir = $_REQUEST['tgl_akhir'];
			$nama = $_REQUEST['nama'];
			$kelas = $_REQUEST['kelas'];
			$jenis = $_REQUEST['jenis'];
			$alasan = $_REQUEST['alasan'];
			$deskripsi = $_REQUEST['deskripsi'];
			$cek_apakah_ada = $this->db->get_where('tb_alasan', array('id_siswa' => $id_siswa, 'tanggal' => $tgl_awal))->num_rows();
			$datane = array(
				'id_siswa' => $id_siswa,
				'nama' => $nama,
				'kelas' => $kelas,
				'tanggal' => $tgl_awal,
				'jenis' => $jenis,
				'deskripsi' => $deskripsi
			);
			if ($cek_apakah_ada >= 1) {
				$update_data = array(
					'nama' => $nama,
					'kelas' => $kelas,
					'tanggal' => $tgl_awal,
					'jenis' => $jenis,
					'deskripsi' => $deskripsi
				);
				$this->db->where(array('id_siswa' => $id_siswa, 'tanggal' => $tgl_awal));
				$save = $this->db->update('tb_alasan', $update_data);
			} else {
				$save = $this->db->insert('tb_alasan', $datane);
			}

			if ($save) {
				$this->berhasil();
			} else {
				$this->gagal();
			}
		}
		$data['halaman'] = 'absensi/isi_alasan';
		$this->load->view('modul_popup', $data);
	}
	public function statistik_absen()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/statistik_absen';
		$this->load->view('modul', $data);
	}
	public function notifikasi_walikelas()
	{
		$this->checkSession();

		if (isset($_REQUEST['kirim_wa'])) {
			// echo "sasas";
			$hp = $_REQUEST['hp'];
			$text = $_REQUEST['text'];
			sendWa1($hp, $text);
			$this->berhasil_dikirim();
		}
		$data['halaman'] = 'absensi/notifikasi_walikelas';
		$this->load->view('modul', $data);
	}
	public function show_siswa_laporan()
	{
		$this->checkSession();
		$tables = "tb_siswa";
		$search = array('nis', 'nama', 'kelas', 'tanggal_lahir');
		// jika memakai IS NULL pada where sql
		$where = array('kelas' => $_REQUEST['kelas']);

		$isWhere = NULL;
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables_where($tables, $search, $where, $isWhere);

	}

	public function laporan_total_siswa_hadir()
	{
		$this->checkSession();
		$tables = "tb_absen";
		$search = array('nis', 'nama', 'kelas', 'tanggal', 'jam_masuk', 'keterangan');
		// jika memakai IS NULL pada where sql
		$where = array('kelas' => $_REQUEST['kelas'], 'tanggal >' => $_REQUEST['tgl_awal'], 'tanggal<' => $_REQUEST['tgl_akhir']);

		$isWhere = NULL;
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables_where($tables, $search, $where, $isWhere);
	}

	public function laporan_total_siswa_hadir_tepat_waktu()
	{
		$this->checkSession();
		$tables = "tb_absen";
		$search = array('nis', 'nama', 'kelas', 'tanggal', 'jam_masuk', 'keterangan');
		// jika memakai IS NULL pada where sql
		$where = array('keterangan' => 'Hadir', 'kelas' => $_REQUEST['kelas'], 'tanggal >' => $_REQUEST['tgl_awal'], 'tanggal<' => $_REQUEST['tgl_akhir']);

		$isWhere = NULL;
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables_where($tables, $search, $where, $isWhere);
	}

	public function laporan_total_siswa_hadir_tidak_tepat_waktu()
	{
		$this->checkSession();
		$tables = "tb_absen";
		$search = array('nis', 'nama', 'kelas', 'tanggal', 'jam_masuk', 'keterangan');
		// jika memakai IS NULL pada where sql
		$where = array('keterangan' => 'Terlambat', 'kelas' => $_REQUEST['kelas'], 'tanggal >' => $_REQUEST['tgl_awal'], 'tanggal<' => $_REQUEST['tgl_akhir']);

		$isWhere = NULL;
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables_where($tables, $search, $where, $isWhere);
	}

	public function table_siswa_absen_pulang()
	{
		$this->checkSession();
		$tables = "tb_absen";
		$search = array('nis', 'nama', 'kelas', 'tanggal', 'jam_masuk', 'keterangan_pulang');
		// jika memakai IS NULL pada where sql
		$where = array('kelas' => $_REQUEST['kelas'], 'tanggal >' => $_REQUEST['tgl_awal'], 'tanggal<' => $_REQUEST['tgl_akhir']);

		$isWhere = "jam_pulang != '00:00:00'";
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables_where($tables, $search, $where, $isWhere);
	}

	public function laporan_total_siswa_tidak_hadir($tgl_awal, $tgl_akhir, $kelas)
	{
		$_REQUEST['kelas'] = (empty($_REQUEST['kelas'])) ? $kelas : $_REQUEST['kelas'];
		$_REQUEST['tgl_awal'] = (empty($_REQUEST['tgl_awal'])) ? $tgl_awal : $_REQUEST['tgl_awal'];
		$_REQUEST['tgl_akhir'] = (empty($_REQUEST['tgl_akhir'])) ? $tgl_akhir : $_REQUEST['tgl_akhir'];
		$_REQUEST['kelas'] = (empty($_REQUEST['kelas'])) ? $kelas : $_REQUEST['kelas'];
		$filter_kelas = ($_REQUEST['kelas'] == 'semua') ? "" : "kelas='" . $_REQUEST['kelas'] . "' AND";

		$query = "SELECT a.id,a.nis, a.nama,a.kelas,a.alamat,a.jk, b.deskripsi, b.jenis, b.tanggal FROM `tb_siswa` as a
		LEFT JOIN tb_alasan as b ON a.id = b.id_siswa AND b.tanggal='" . $_REQUEST['tgl_awal'] . "'
		";
		$search = array('a.nis', 'a.nama', 'a.kelas', 'a.alamat', 'a.jk', 'b.deskripsi', 'b.jenis');
		// $where  = null; 
		if ($_REQUEST['kelas'] == 'semua'):
			$where = array();
		else:
			$where = array('a.kelas' => $_REQUEST['kelas']);
		endif;
		// jika memakai IS NULL pada where sql
		$isWhere = "a.id NOT IN (SELECT id_siswa FROM tb_absen WHERE " . $filter_kelas . " tanggal>='" . $_REQUEST['tgl_awal'] . "' AND tanggal<='" . $_REQUEST['tgl_akhir'] . "')";
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables_query($query, $search, $where, $isWhere);
	}

	public function laporan_total()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/laporan_total';
		$this->load->view('modul', $data);
	}
	public function upload_csv()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/upload_csv';
		$this->load->view('modul', $data);
	}
	public function act_kelas()
	{
		$this->checkSession();
		$data['halaman'] = 'absensi/act_kelas';
		$this->load->view('modul', $data);
	}

	public function laporan_view()
	{
		$this->checkSession();
		$tgl_awal = $_REQUEST['tgl_awal'];
		$tgl_akhir = $_REQUEST['tgl_akhir'];
		$kelas = $_REQUEST['kelas'];
		if ($_REQUEST['status'] == 'Tidak Hadir') {
			$this->laporan_total_siswa_tidak_hadir($tgl_awal, $tgl_akhir, $kelas);
		} else {
			$tables = "tb_absen";
			$search = array('tanggal', 'nis', 'kelas', 'jam_masuk', 'keterangan', 'jam_pulang', 'keterangan_pulang', 'id');
			if ($_REQUEST['status'] !== 'semua') {
				if (stripos($_REQUEST['status'], 'Pulang') !== false) {
					$where = array('keterangan_pulang' => $_REQUEST['status'], 'kelas' => $_REQUEST['kelas'], 'tanggal >' => $_REQUEST['tgl_awal'], 'tanggal<' => $_REQUEST['tgl_akhir']);
				} else {
					$where = array('keterangan' => $_REQUEST['status'], 'kelas' => $_REQUEST['kelas'], 'tanggal >' => $_REQUEST['tgl_awal'], 'tanggal<' => $_REQUEST['tgl_akhir']);
				}
			} else {
				$where = array('tanggal >' => $_REQUEST['tgl_awal'], 'tanggal <' => $_REQUEST['tgl_akhir']);
			}

			// var_dump($where);
			$isWhere = NULL;
			header('Content-Type: application/json');
			echo $this->M_Datatables->get_tables_where($tables, $search, $where, $isWhere);
		}
	}

	public function data_siswa_show()
	{
		$this->checkSession();
		$tables = "tb_siswa";
		$search = array('rfid', 'foto', 'nisn', 'nama', 'kelas', 'jk', 'nama_ayah', 'telepon', 'alamat', 'id');
		// jika memakai IS NULL pada where sql
		$isWhere = NULL;
		// $isWhere = 'artikel.deleted_at IS NULL';
		header('Content-Type: application/json');
		echo $this->M_Datatables->get_tables($tables, $search, $isWhere);
	}


	public function save_siswa()
	{
		$this->checkSession();
		if (isset($_REQUEST['simpan'])) {
			$config['upload_path'] = './assets/siswa/';
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['max_size'] = 8000;
			$config['max_width'] = 1000;
			$config['max_height'] = 1000;
			$this->load->library('upload', $config);

			if ($_FILES['foto']['name'] != "") {
				if (!$this->upload->do_upload('foto')) {
					echo $this->upload->display_errors();
				} else {
					$upload_data = $this->upload->data();
					$foto = (empty($upload_data['file_name'])) ? "-" : $upload_data['file_name'];
				}
			} else {
				$foto = $_REQUEST['foto_lama'];
			}

			$set_foto = (!empty($foto)) ? $foto : 'NULL';
			$data = array(
				'nis' => $_REQUEST['nis'],
				'nisn' => $_REQUEST['nisn'],
				'rfid' => $_REQUEST['rfid'],
				'nama' => $_REQUEST['nama'],
				'tempat_lahir' => $_REQUEST['tempat_lahir'],
				'tanggal_lahir' => $_REQUEST['tanggal_lahir'],
				'jk' => $_REQUEST['jk'],
				'alamat' => $_REQUEST['alamat'],
				'kelas' => $_REQUEST['kelas'],
				'telepon' => str_replace('+', '', hp($_REQUEST['telepone'])),
				'nama_ayah' => $_REQUEST['nama_ayah'],
				'nama_Ibu' => $_REQUEST['nama_Ibu'],
				'foto' => $set_foto
			);


			if (!empty($_REQUEST['id'])) {
				$this->db->where('id', $_REQUEST['id']);
				$exc = $this->db->update('tb_siswa', $data);
			} else {
				$exc = $this->db->insert('tb_siswa', $data);
			}

			if ($exc) {
				$alert['alert'] = '
				<div class="alert alert-success alert-dismissible" role="alert">
				<div class="alert-message">
				<strong>Perhatian !! Data berhasil disimpan</strong>
				</div>
				</div>
				';
				$this->session->set_flashdata('alert', $alert);
				if (!empty($_REQUEST['id'])) {
					redirect('act_siswa?id=' . $_REQUEST['id']);
				} else {
					redirect('act_siswa');
				}
			} else {
				$alert['alert'] = '
				<div class="alert alert-danger alert-dismissible" role="alert">
				<div class="alert-message">
				<strong>Perhatian !! Data gagal disimpan</strong>
				</div>
				</div>
				';
				$this->session->set_flashdata('alert', $alert);
				if (!empty($_REQUEST['id'])) {
					redirect('act_siswa?id=' . $_REQUEST['id']);
				} else {
					redirect('act_siswa');
				}
			}
		}
	}
	public function cron_send_wa_masuk()
	{
		$res_absensi = $this->db->get_where('tb_absen', array('send_wa_status' => 'queue'), 1);
		$total_absensi = $res_absensi->num_rows();
		if ($total_absensi >= 1) {
			$dAbsensi = $res_absensi->row_array();
			$siswa = $this->db->get_where('tb_siswa', array('id' => $dAbsensi['id_siswa'], 'nisn' => $dAbsensi['nis']))->row_array();
			$menit = (substr($dAbsensi['jam_masuk'], 0, 5) > '07:00') ? (substr($dAbsensi['jam_masuk'], 0, 2) - 7) * 60 + (substr($dAbsensi['jam_masuk'], 3, 2) - 0) . " Menit" : '';
			// echo substr($dAbsensi['jam_masuk'], 3,2);
			$keterangan = ($dAbsensi['jam_masuk'] > '07:00') ? 'Terlambat' : 'Hadir';
			$text = "*NOTIFIKASI ABSEN MASUK MIN 1 JOMBANG* \n \n Siswa dengan NIS " . $dAbsensi['nis'] . " Atas nama *" . $dAbsensi['nama'] . "* Sudah melakukan absensi pada tanggal *" . farmat_tanggal($dAbsensi['tanggal']) . "* jam *" . $dAbsensi['jam_masuk'] . "* dengan status *" . $keterangan . "* " . $menit . "";
			// echo $text;
			$hp = str_replace('+', '', hp($siswa['telepon']));
			$data = array('send_wa_status' => 'done', 'date_send_wa' => date('Y-m-d H:i:s'));
			$this->db->where('id', $dAbsensi['id']);
			$update = $this->db->update('tb_absen', $data);
			if ($update) {
				sendWa1($hp, $text);
			}
			// sendWa1($hp,$text);
		} else {
			echo json_encode(array('status' => 'data sudah dikirim semua'));
		}
	}

	public function cron_send_wa_pulang()
	{
		$res_absensi = $this->db->get_where('tb_absen', array('send_wa_status_pulang' => 'queue'), 1);
		$total_absensi = $res_absensi->num_rows();
		if ($total_absensi >= 1) {
			$dAbsensi = $res_absensi->row_array();
			$siswa = $this->db->get_where('tb_siswa', array('id' => $dAbsensi['id_siswa'], 'nisn' => $dAbsensi['nis']))->row_array();
			$menit = (substr($dAbsensi['jam_masuk'], 0, 5) < '12:00') ? (substr($dAbsensi['jam_masuk'], 0, 2) - 7) * 60 + (substr($dAbsensi['jam_masuk'], 3, 2) - 0) . " Menit" : '';
			$keterangan = ($dAbsensi['jam_masuk'] > '12:00') ? 'Pulang' : 'Pulang Cepat';

			$text = "*NOTIFIKASI ABSEN PULANG MIN 1 JOMBANG* \n \n Siswa dengan NIS " . $dAbsensi['nis'] . " Atas nama *" . $dAbsensi['nama'] . "* Sudah melakukan absensi pada tanggal *" . farmat_tanggal($dAbsensi['tanggal']) . "* jam *" . $dAbsensi['jam_masuk'] . "* dengan status *" . $keterangan . "* " . $menit . "";
			$hp = str_replace('+', '', hp($siswa['telepon']));
			$data = array('send_wa_status_pulang' => 'done', 'date_send_wa_pulang' => date('Y-m-d H:i:s'));
			$this->db->where('id', $dAbsensi['id']);
			$update = $this->db->update('tb_absen', $data);
			if ($update) {
				sendWa1($hp, $text);
			}
		} else {
			echo json_encode(array('status' => 'data sudah dikirim semua'));
		}
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