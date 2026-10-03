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

		// Auto Create Table Log Konfirmasi (lancar & dimuka)
		$this->db->query("CREATE TABLE IF NOT EXISTS log_konfirmasi (
			id INT AUTO_INCREMENT PRIMARY KEY,
			id_rumah INT,
			tipe VARCHAR(20),
			tgl_kirim DATETIME,
			dikirim_ke VARCHAR(20),
			status VARCHAR(20),
			tahun INT,
			bulan INT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		)");

		// Auto-create tabel log_login jika belum ada
		$this->_create_log_tables();

		// Auto-fix sinkronisasi collation master_keluarga & master_rumah
		$this->_fix_collations();
	}

	/**
	 * Auto-create semua tabel log jika belum ada di database
	 */
	private function _create_log_tables()
	{
		if (function_exists('ensure_log_tables_exist')) {
			ensure_log_tables_exist();
			return;
		}

		// Fallback jika helper belum ter-load
		$this->db->query("CREATE TABLE IF NOT EXISTS `log_login` (
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
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$this->db->query("CREATE TABLE IF NOT EXISTS `log_verifikasi` (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`pembayaran_id` INT(11) DEFAULT NULL,
			`admin_id` INT(11) DEFAULT NULL,
			`admin_username` VARCHAR(100) DEFAULT NULL,
			`admin_nama` VARCHAR(255) DEFAULT NULL,
			`admin_role` VARCHAR(50) DEFAULT NULL,
			`aksi` VARCHAR(50) DEFAULT NULL,
			`status_sebelum` VARCHAR(50) DEFAULT NULL,
			`status_sesudah` VARCHAR(50) DEFAULT NULL,
			`warga_nama` VARCHAR(255) DEFAULT NULL,
			`warga_alamat` VARCHAR(255) DEFAULT NULL,
			`bulan_bayar` DATE DEFAULT NULL,
			`jumlah_bayar` DECIMAL(15,2) DEFAULT 0,
			`pembayaran_via` VARCHAR(50) DEFAULT NULL,
			`tanggal_bayar` DATE DEFAULT NULL,
			`wa_status` VARCHAR(20) DEFAULT NULL,
			`wa_no_tujuan` VARCHAR(20) DEFAULT NULL,
			`wa_response` TEXT DEFAULT NULL,
			`wa_error` TEXT DEFAULT NULL,
			`ip_address` VARCHAR(45) DEFAULT NULL,
			`user_agent` TEXT DEFAULT NULL,
			`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `idx_created_at` (`created_at`),
			KEY `idx_pembayaran_id` (`pembayaran_id`),
			KEY `idx_aksi` (`aksi`),
			KEY `idx_wa_status` (`wa_status`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$this->db->query("CREATE TABLE IF NOT EXISTS `log_koordinator` (
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`id_koordinator` INT(11) DEFAULT NULL,
			`nama_koordinator` VARCHAR(255) DEFAULT NULL,
			`blok` VARCHAR(50) DEFAULT NULL,
			`aksi` VARCHAR(100) DEFAULT NULL,
			`jumlah_rumah` INT(11) DEFAULT 0,
			`nominal_total` DECIMAL(15,2) DEFAULT 0,
			`keterangan` TEXT DEFAULT NULL,
			`created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`),
			KEY `idx_koordinator` (`id_koordinator`),
			KEY `idx_created_at` (`created_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$this->db->query("CREATE TABLE IF NOT EXISTS `log_aktivitas` (
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
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
	}

	/**
	 * Auto-fix sinkronisasi collation tabel master_keluarga & master_rumah
	 * agar tidak terjadi Error 1267 (Illegal mix of collations)
	 */
	private function _fix_collations()
	{
		try {
			$cols = $this->db->query("
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
				$this->db->query("ALTER TABLE master_keluarga MODIFY nomor_rumah VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
				$this->db->query("ALTER TABLE master_rumah MODIFY alamat VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL");
			}
		} catch (\Throwable $e) {
			// Cegah fatal error jika permission terbatas
		}
	}

	/**
	 * Helper: Insert log verifikasi
	 */
	private function _insert_log_verifikasi($data)
	{
		$this->_create_log_tables();
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
	 * Backfill/sinkronisasi historis dari master_pembayaran ke log_verifikasi
	 * jika tabel log_verifikasi masih kosong.
	 */
	private function _sync_historical_log_verifikasi()
	{
		$this->_create_log_tables();
		$sudah_ada = $this->db->count_all('log_verifikasi');
		if ($sudah_ada > 0) {
			return;
		}

		$pembayaran = $this->db->query("
			SELECT p.id, p.status, p.user_id, p.jumlah_bayar, p.pembayaran_via, p.tanggal_bayar,
			       p.bulan_mulai, p.created_at, p.wa_send,
			       u.nama as warga_nama, r.alamat as warga_alamat, kl.no_hp
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
			WHERE p.status IN ('verified', 'rejected')
			ORDER BY p.id DESC
		")->result_array();

		if (empty($pembayaran)) {
			return;
		}

		$batch = [];
		$this->load->helper('wa');
		foreach ($pembayaran as $p) {
			$wa_status = 'skipped';
			if ($p['wa_send'] === 'success') {
				$wa_status = 'success';
			} elseif ($p['wa_send'] === 'queue') {
				$wa_status = 'queue';
			}

			$batch[] = [
				'pembayaran_id'   => $p['id'],
				'admin_id'        => 1,
				'admin_username'  => 'admin',
				'admin_nama'      => 'Admin Sistem',
				'admin_role'      => 'admin',
				'aksi'            => $p['status'],
				'status_sebelum'  => 'pending',
				'status_sesudah'  => $p['status'],
				'warga_nama'      => $p['warga_nama'] ?? 'Warga',
				'warga_alamat'    => $p['warga_alamat'] ?? '-',
				'bulan_bayar'     => $p['bulan_mulai'],
				'jumlah_bayar'    => $p['jumlah_bayar'],
				'pembayaran_via'  => $p['pembayaran_via'],
				'tanggal_bayar'   => $p['tanggal_bayar'],
				'wa_status'       => $wa_status,
				'wa_no_tujuan'    => !empty($p['no_hp']) ? hp($p['no_hp']) : null,
				'wa_response'     => 'Auto-sync historical payment',
				'ip_address'      => '127.0.0.1',
				'user_agent'      => 'System Migration',
				'created_at'      => $p['created_at'] ?: date('Y-m-d H:i:s'),
			];
		}

		if (!empty($batch)) {
			$this->db->insert_batch('log_verifikasi', $batch);
		}
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
		$this->_sync_historical_log_verifikasi();
		$data['halaman'] = 'dashboard/log_verifikasi';
		$this->load->view('modul', $data);
	}

	/**
	 * Server-side DataTables Endpoint untuk Log Verifikasi
	 */
	public function ajax_log_verifikasi()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		$this->_create_log_tables();

		$draw = intval($this->input->post_get('draw'));
		$start = intval($this->input->post_get('start'));
		$length = intval($this->input->post_get('length') ?: 25);
		$search_val = trim($this->input->post_get('search')['value'] ?? '');
		$order_col_idx = intval($this->input->post_get('order')[0]['column'] ?? 1);
		$order_dir = strtolower($this->input->post_get('order')[0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

		$cols = [
			0 => 'id',
			1 => 'created_at',
			2 => 'admin_nama',
			3 => 'aksi',
			4 => 'warga_nama',
			5 => 'bulan_bayar',
			6 => 'jumlah_bayar',
			7 => 'pembayaran_via',
			8 => 'wa_status',
			9 => 'wa_no_tujuan',
			10 => 'id'
		];
		$order_col = $cols[$order_col_idx] ?? 'created_at';

		$total_records = $this->db->count_all('log_verifikasi');

		if (!empty($search_val)) {
			$this->db->group_start();
			$this->db->like('admin_nama', $search_val);
			$this->db->or_like('admin_username', $search_val);
			$this->db->or_like('warga_nama', $search_val);
			$this->db->or_like('warga_alamat', $search_val);
			$this->db->or_like('aksi', $search_val);
			$this->db->or_like('pembayaran_via', $search_val);
			$this->db->or_like('wa_status', $search_val);
			$this->db->or_like('wa_no_tujuan', $search_val);
			$this->db->or_like('created_at', $search_val);
			$this->db->group_end();
		}

		$filtered_records = $this->db->count_all_results('log_verifikasi', FALSE);

		$this->db->order_by($order_col, $order_dir);
		$this->db->limit($length, $start);
		$logs = $this->db->get()->result();

		$data = [];
		$no = $start + 1;
		foreach ($logs as $log) {
			// Aksi badge
			if ($log->aksi === 'verified') {
				$badge_aksi = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Verified</span>';
			} elseif ($log->aksi === 'rejected') {
				$badge_aksi = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rejected</span>';
			} else {
				$badge_aksi = '<span class="badge bg-secondary">' . htmlspecialchars($log->aksi ?? '-') . '</span>';
			}

			// Via badge
			if ($log->pembayaran_via === 'koordinator') {
				$badge_via = '<span class="badge bg-info">Koordinator</span>';
			} elseif (in_array($log->pembayaran_via, ['transfer', 'transfer_2'])) {
				$badge_via = '<span class="badge bg-primary">Transfer</span>';
			} else {
				$badge_via = '<span class="badge bg-secondary">' . htmlspecialchars($log->pembayaran_via ?? '-') . '</span>';
			}

			// WA Status badge
			if ($log->wa_status === 'success') {
				$badge_wa = '<span class="badge bg-success"><i class="bi bi-check-all"></i> Terkirim</span>';
			} elseif ($log->wa_status === 'failed') {
				$badge_wa = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Gagal</span>';
			} elseif ($log->wa_status === 'queue') {
				$badge_wa = '<span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Antrean</span>';
			} else {
				$badge_wa = '<span class="badge bg-secondary"><i class="bi bi-dash"></i> Lewati</span>';
			}

			// No HP format
			if (!empty($log->wa_no_tujuan)) {
				$clean_hp = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $log->wa_no_tujuan));
				$hp_display = '<a href="https://wa.me/' . $clean_hp . '" target="_blank" class="text-success text-decoration-none fw-semibold"><i class="bi bi-whatsapp"></i> ' . htmlspecialchars($log->wa_no_tujuan) . '</a>';
			} else {
				$hp_display = '<span class="text-muted small">-</span>';
			}

			// Json data for modal
			$detail_json = htmlspecialchars(json_encode([
				'id'              => $log->id,
				'created_at'      => $log->created_at ? date('d/m/Y H:i:s', strtotime($log->created_at)) : '-',
				'admin_nama'      => $log->admin_nama ?? '-',
				'admin_username'  => $log->admin_username ?? '-',
				'admin_role'      => $log->admin_role ?? '-',
				'aksi'            => $log->aksi ?? '-',
				'status_sebelum'  => $log->status_sebelum ?? '-',
				'status_sesudah'  => $log->status_sesudah ?? '-',
				'warga_nama'      => $log->warga_nama ?? '-',
				'warga_alamat'    => $log->warga_alamat ?? '-',
				'bulan_bayar'     => $log->bulan_bayar ? date('F Y', strtotime($log->bulan_bayar)) : '-',
				'jumlah_bayar_rp' => 'Rp' . number_format($log->jumlah_bayar ?? 0, 0, ',', '.'),
				'pembayaran_via'  => $log->pembayaran_via ?? '-',
				'tanggal_bayar'   => $log->tanggal_bayar ? date('d/m/Y', strtotime($log->tanggal_bayar)) : '-',
				'wa_status'       => $log->wa_status ?? '-',
				'wa_no_tujuan'    => $log->wa_no_tujuan ?? '-',
				'wa_response'     => $log->wa_response ?? '-',
				'wa_error'        => $log->wa_error ?? '',
				'ip_address'      => $log->ip_address ?? '-',
				'user_agent'      => $log->user_agent ?? '-',
			]), ENT_QUOTES, 'UTF-8');

			$btn_detail = '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 btn-detail-verifikasi" data-log=\'' . $detail_json . '\'><i class="bi bi-eye"></i> Detail</button>';

			$data[] = [
				'no'           => $no++,
				'waktu'        => '<small class="text-nowrap"><i class="bi bi-clock text-muted"></i> ' . date('d/m/Y H:i:s', strtotime($log->created_at)) . '</small>',
				'admin'        => '<strong>' . htmlspecialchars($log->admin_nama ?? '-') . '</strong><br><small class="text-muted">' . htmlspecialchars($log->admin_username ?? '') . '</small>',
				'aksi'         => $badge_aksi,
				'warga'        => '<strong>' . htmlspecialchars($log->warga_nama ?? '-') . '</strong><br><small class="text-muted">' . htmlspecialchars($log->warga_alamat ?? '') . '</small>',
				'bulan'        => '<span class="text-nowrap">' . ($log->bulan_bayar ? date('M Y', strtotime($log->bulan_bayar)) : '-') . '</span>',
				'jumlah'       => '<span class="text-nowrap fw-bold text-dark">Rp' . number_format($log->jumlah_bayar ?? 0, 0, ',', '.') . '</span>',
				'via'          => $badge_via,
				'wa_status'    => $badge_wa,
				'wa_no_tujuan' => $hp_display,
				'aksi_btn'     => $btn_detail
			];
		}

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $total_records,
			'recordsFiltered' => $filtered_records,
			'data'            => $data
		]);
	}

	/**
	 * Server-side DataTables Endpoint untuk Log Login
	 */
	public function ajax_log_login()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		$this->_create_log_tables();

		$draw = intval($this->input->post_get('draw'));
		$start = intval($this->input->post_get('start'));
		$length = intval($this->input->post_get('length') ?: 25);
		$search_val = trim($this->input->post_get('search')['value'] ?? '');
		$order_col_idx = intval($this->input->post_get('order')[0]['column'] ?? 1);
		$order_dir = strtolower($this->input->post_get('order')[0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';

		$cols = [
			0 => 'id',
			1 => 'login_at',
			2 => 'username',
			3 => 'nama',
			4 => 'role',
			5 => 'status',
			6 => 'ip_address',
			7 => 'user_agent',
			8 => 'keterangan'
		];
		$order_col = $cols[$order_col_idx] ?? 'login_at';

		$total_records = $this->db->count_all('log_login');

		if (!empty($search_val)) {
			$this->db->group_start();
			$this->db->like('username', $search_val);
			$this->db->or_like('nama', $search_val);
			$this->db->or_like('role', $search_val);
			$this->db->or_like('status', $search_val);
			$this->db->or_like('ip_address', $search_val);
			$this->db->or_like('user_agent', $search_val);
			$this->db->or_like('keterangan', $search_val);
			$this->db->or_like('login_at', $search_val);
			$this->db->group_end();
		}

		$filtered_records = $this->db->count_all_results('log_login', FALSE);

		$this->db->order_by($order_col, $order_dir);
		$this->db->limit($length, $start);
		$logs = $this->db->get()->result();

		$data = [];
		$no = $start + 1;
		foreach ($logs as $log) {
			// Role badge
			if ($log->role === 'admin') {
				$badge_role = '<span class="badge bg-primary">Admin</span>';
			} elseif ($log->role === 'koordinator') {
				$badge_role = '<span class="badge bg-warning text-dark">Koordinator</span>';
			} elseif ($log->role === 'bendahara') {
				$badge_role = '<span class="badge bg-info">Bendahara</span>';
			} else {
				$badge_role = '<span class="badge bg-secondary">' . htmlspecialchars($log->role ?? '-') . '</span>';
			}

			// Status badge
			if ($log->status === 'success') {
				$badge_status = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Berhasil</span>';
			} else {
				$badge_status = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Gagal</span>';
			}

			$ua_short = htmlspecialchars(mb_strimwidth($log->user_agent ?? '-', 0, 55, '...'));
			$ua_full = htmlspecialchars($log->user_agent ?? '');

			$data[] = [
				'no'          => $no++,
				'waktu'       => '<span class="text-nowrap small"><i class="bi bi-clock text-muted me-1"></i>' . date('d/m/Y H:i:s', strtotime($log->login_at)) . '</span>',
				'username'    => '<code>' . htmlspecialchars($log->username ?? '-') . '</code>',
				'nama'        => htmlspecialchars($log->nama ?? '-'),
				'role'        => $badge_role,
				'status'      => $badge_status,
				'ip_address'  => '<small class="text-muted">' . htmlspecialchars($log->ip_address ?? '-') . '</small>',
				'user_agent'  => '<small class="text-muted" title="' . $ua_full . '">' . $ua_short . '</small>',
				'keterangan'  => '<small>' . htmlspecialchars($log->keterangan ?? '-') . '</small>',
			];
		}

		echo json_encode([
			'draw'            => $draw,
			'recordsTotal'    => $total_records,
			'recordsFiltered' => $filtered_records,
			'data'            => $data
		]);
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

	public function export_warga_excel()
	{
		$this->checkSession();

		// Query semua data rumah sebagai base
		$this->db->select('mr.alamat as nomor_rumah, mr.nama as nama_pemilik, mk.alamat as alamat_lengkap, mk.id as keluarga_id')
			->from('master_rumah mr')
			->join('master_keluarga mk', "CONCAT('| ', mk.nomor_rumah COLLATE utf8mb4_general_ci, '|') LIKE CONCAT('%| ', mr.alamat COLLATE utf8mb4_general_ci, '|%')", 'left')
			->order_by('mr.alamat', 'ASC');

		$rumah_result = $this->db->get()->result_array();

		// Set headers untuk download Excel
		$filename = 'Data_Warga_' . date('Y-m-d_His') . '.xls';
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Cache-Control: max-age=0');

		// Output Excel content sebagai HTML table
		$output = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
		$output .= '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Data Warga</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head>';
		$output .= '<body>';
		$output .= '<table border="1" cellpadding="5" cellspacing="0">';
		$output .= '<thead>';
		$output .= '<tr style="background-color: #28a745; color: #ffffff; font-weight: bold; text-align: center;">';
		$output .= '<th>No</th>';
		$output .= '<th>Nomor Rumah</th>';
		$output .= '<th>Alamat</th>';
		$output .= '<th>Nama</th>';
		$output .= '</tr>';
		$output .= '</thead>';
		$output .= '<tbody>';

		$no = 1;
		foreach ($rumah_result as $value) {
			// Nama diambil dari master_rumah
			$nama_str = !empty($value['nama_pemilik']) ? strtoupper($value['nama_pemilik']) : '-';
			$alamat = !empty($value['alamat_lengkap']) ? $value['alamat_lengkap'] : '-';

			$output .= '<tr>';
			$output .= '<td style="text-align: center;">' . $no++ . '</td>';
			$output .= '<td>' . htmlspecialchars($value['nomor_rumah']) . '</td>';
			$output .= '<td>' . htmlspecialchars($alamat) . '</td>';
			$output .= '<td>' . htmlspecialchars($nama_str) . '</td>';
			$output .= '</tr>';
		}

		$output .= '</tbody>';
		$output .= '</table>';
		$output .= '</body></html>';

		echo $output;
		exit;
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
			// Jika admin, tampilkan koordinator yang punya rumah binaan
			$koordinator = $this->db->query("SELECT DISTINCT k.id, k.nama FROM master_koordinator_blok k WHERE EXISTS (SELECT 1 FROM master_rumah r WHERE r.id_koordinator = k.id) ORDER BY k.nama")->result_array();
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
		$data['koordinator'] = $this->db->query("SELECT DISTINCT k.id, k.nama FROM master_koordinator_blok k WHERE EXISTS (SELECT 1 FROM master_rumah r WHERE r.id_koordinator = k.id) ORDER BY k.nama")->result_array();

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

			// Ambil nomor HP menggunakan helper get_no_hp_warga
			$this->load->helper('wa');
			$wa_no_hp = get_no_hp_warga($wa_rumah['id'] ?? 0, $wa_rumah['alamat'] ?? '');
			$wa_nama = $wa_user['nama'] ?? '';

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
			$this->load->helper('wa');
			$no_hp = get_no_hp_warga($user['id_rumah'] ?? 0, $rumah['alamat'] ?? '');
			
			$nama = $user['nama'] ?? '';
			$bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));
			$link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));

			$text = "✅ Pembayaran IPL Telah Divalidasi\n\nAssalamu'alaikum/Salam sejahtera Bapak/Ibu *$nama*,\n\nPembayaran IPL bulan *$bulan* sebesar *Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "* telah *divalidasi* oleh pengurus. ✅\n💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "\n📄 Bukti: Sudah divalidasi\n🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "\n📑 Kitir Pembayaran: $link\n\nSilakan unduh e-kitir di atas sebagai bukti pembayaran resmi Bapak/Ibu.\n\nTerima kasih atas kontribusi Bapak/Ibu dalam operasional dan pemeliharaan lingkungan kita bersama.\n\nHormat kami,\nPengurus Paguyuban TSI\nPerumahan Taman Sukodono Indah\n_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";

			$log_data = [
				'pembayaran_id' => $id, 'aksi' => $aksi, 'status_sebelum' => $cek->status, 'status_sesudah' => $statusBaru,
				'warga_nama' => $nama, 'warga_alamat' => $rumah['alamat'] ?? '', 'bulan_bayar' => $pembayaran['bulan_mulai'],
				'jumlah_bayar' => $pembayaran['jumlah_bayar'], 'pembayaran_via' => $pembayaran['pembayaran_via'],
				'tanggal_bayar' => $pembayaran['tanggal_bayar'], 'wa_no_tujuan' => hp($no_hp),
				'wa_status' => !empty($no_hp) ? 'queue' : 'skipped',
			];
			$this->_insert_log_verifikasi($log_data);

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
				$no_hp = get_no_hp_warga($user['id_rumah'] ?? 0, $rumah['alamat'] ?? '');
				
				$wa_status = 'skipped';
				$wa_response = null;
				if (!empty($no_hp)) {
					$bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));
					$link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));
					$text = "✅ Pembayaran IPL Telah Divalidasi\n\nAssalamu'alaikum/Salam sejahtera Bapak/Ibu *".$user['nama']."*,\n\nPembayaran IPL bulan *$bulan* sebesar *Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "* telah *divalidasi* oleh pengurus. ✅\n💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "\n📄 Bukti: Sudah divalidasi\n🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "\n📑 Kitir Pembayaran: $link\n\nSilakan unduh e-kitir di atas sebagai bukti pembayaran resmi Bapak/Ibu.\n\nTerima kasih atas kontribusi Bapak/Ibu dalam operasional dan pemeliharaan lingkungan kita bersama.\n\nHormat kami,\nPengurus Paguyuban TSI\nPerumahan Taman Sukodono Indah\n_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";
					$res = send_wa($no_hp, $text);
					$wa_status = (isset($res['status']) && ($res['status'] === true || $res['status'] == '1')) ? 'success' : 'failed';
					$wa_response = json_encode($res);
				}

				// Catat ke log verifikasi
				$log_data = [
					'pembayaran_id' => $id,
					'aksi' => $aksi,
					'status_sebelum' => $cek->status,
					'status_sesudah' => $statusBaru,
					'warga_nama' => $user['nama'] ?? '',
					'warga_alamat' => $rumah['alamat'] ?? '',
					'bulan_bayar' => $pembayaran['bulan_mulai'],
					'jumlah_bayar' => $pembayaran['jumlah_bayar'],
					'pembayaran_via' => $pembayaran['pembayaran_via'],
					'tanggal_bayar' => $pembayaran['tanggal_bayar'],
					'wa_no_tujuan' => !empty($no_hp) ? hp($no_hp) : null,
					'wa_status' => $wa_status,
					'wa_response' => $wa_response,
				];
				$this->_insert_log_verifikasi($log_data);
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
		$this->_fix_collations();
		// Auto-add no_hp column if not exists
		if (!$this->db->field_exists('no_hp', 'master_koordinator_blok')) {
			$this->db->query("ALTER TABLE master_koordinator_blok ADD no_hp VARCHAR(20) DEFAULT NULL");
		}
		if (!$this->db->field_exists('no_hp', 'master_rumah')) {
			$this->db->query("ALTER TABLE master_rumah ADD no_hp VARCHAR(20) DEFAULT NULL");
		}
		if (!$this->db->field_exists('status_rumah', 'master_rumah')) {
			$this->db->query("ALTER TABLE master_rumah ADD status_rumah VARCHAR(50) DEFAULT 'Rumah Sendiri'");
			$this->db->query("UPDATE master_rumah r 
				JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci 
				SET r.status_rumah = kl.status_rumah 
				WHERE kl.status_rumah IS NOT NULL AND kl.status_rumah != ''");
		}
		
		// Auto-sinkronisasi nomor HP warga dari master_keluarga ke master_rumah
		$this->_sync_rumah_no_hp();
		
		$data['halaman'] = 'dashboard/setting';
		$this->load->view('modul', $data);
	}

	/**
	 * Sinkronisasi nomor WhatsApp dari master_keluarga ke master_rumah
	 */
	private function _sync_rumah_no_hp()
	{
		// 1. Sinkronisasi langsung berdasarkan nomor_rumah = alamat
		$this->db->query("UPDATE master_rumah r 
			JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci 
			SET r.no_hp = kl.no_hp 
			WHERE (r.no_hp IS NULL OR r.no_hp = '') AND kl.no_hp IS NOT NULL AND kl.no_hp != ''");

		// 2. Sinkronisasi berdasarkan id_rumah jika kolom tersedia
		if ($this->db->field_exists('id_rumah', 'master_keluarga')) {
			$this->db->query("UPDATE master_rumah r 
				JOIN master_keluarga kl ON kl.id_rumah = r.id 
				SET r.no_hp = kl.no_hp 
				WHERE (r.no_hp IS NULL OR r.no_hp = '') AND kl.no_hp IS NOT NULL AND kl.no_hp != ''");
		}

		// 3. Normalisasi dash dan multi-nomor rumah (A1-05|A1-06)
		$empty_houses = $this->db->query("SELECT id, alamat FROM master_rumah WHERE no_hp IS NULL OR no_hp = ''")->result_array();
		if (!empty($empty_houses)) {
			$all_keluarga = $this->db->query("SELECT no_hp, nomor_rumah FROM master_keluarga WHERE no_hp IS NOT NULL AND no_hp != ''")->result_array();
			if (!empty($all_keluarga)) {
				$normalize = function($addr) {
					$addr = preg_replace('/[‐-‒–—−]/u', '-', $addr);
					$addr = strtolower(trim($addr));
					$addr = preg_replace('/\s+/', ' ', $addr);
					return $addr;
				};

				$map = [];
				foreach ($all_keluarga as $k) {
					$parts = preg_split('/[|,]/', $k['nomor_rumah']);
					foreach ($parts as $part) {
						$norm = $normalize($part);
						if (!empty($norm) && !isset($map[$norm])) {
							$map[$norm] = $k['no_hp'];
						}
					}
				}

				foreach ($empty_houses as $eh) {
					$norm_eh = $normalize($eh['alamat']);
					if (isset($map[$norm_eh])) {
						$this->db->where('id', $eh['id'])->update('master_rumah', ['no_hp' => $map[$norm_eh]]);
					}
				}
			}
		}
	}

	/**
	 * Server-side DataTables Endpoint untuk Setting Data Rumah
	 */
	public function ajax_setting_rumah()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		// Jalankan auto-sync agar nomor HP selalu terupdate
		$this->_sync_rumah_no_hp();

		$Auth = $this->session->userdata['username'];
		$is_admin = ($Auth['role'] === 'admin');

		$draw = intval($this->input->post_get('draw'));
		$start = intval($this->input->post_get('start'));
		$length = intval($this->input->post_get('length') ?: 25);
		$search_val = trim($this->input->post_get('search')['value'] ?? '');
		$order_col_idx = intval($this->input->post_get('order')[0]['column'] ?? 1);
		$order_dir = strtolower($this->input->post_get('order')[0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';

		// Custom filters
		$filter_koor = $this->input->post_get('filter_koordinator');
		$filter_status = $this->input->post_get('filter_status');

		$cols = [
			0 => 'r.id',
			1 => 'r.alamat',
			2 => 'r.nama',
			3 => 'r.status_rumah',
			4 => 'r.no_hp',
			5 => 'k.nama',
			6 => 'r.id'
		];
		$order_col = $cols[$order_col_idx] ?? 'r.alamat';

		// Base query for total count
		$this->db->from('master_rumah r');
		if (!$is_admin) {
			$this->db->where('r.id_koordinator', $Auth['id']);
		}
		$total_records = $this->db->count_all_results();

		// Query for filtered records
		$this->db->select("r.id, r.alamat, r.nama, r.id_koordinator, k.nama as koordinator, 
						  COALESCE(r.status_rumah, 'Rumah Sendiri') as status_rumah, 
						  r.no_hp");
		$this->db->from('master_rumah r');
		$this->db->join('master_koordinator_blok k', 'r.id_koordinator = k.id', 'left');

		if (!$is_admin) {
			$this->db->where('r.id_koordinator', $Auth['id']);
		} elseif (!empty($filter_koor)) {
			$this->db->where('r.id_koordinator', $filter_koor);
		}

		if (!empty($filter_status)) {
			if ($filter_status === 'Rumah Sendiri') {
				$this->db->group_start();
				$this->db->where('r.status_rumah', 'Rumah Sendiri');
				$this->db->or_where('r.status_rumah IS NULL');
				$this->db->or_where('r.status_rumah', '');
				$this->db->group_end();
			} else {
				$this->db->like('r.status_rumah', $filter_status);
			}
		}

		if (!empty($search_val)) {
			$this->db->group_start();
			$this->db->like('r.alamat', $search_val);
			$this->db->or_like('r.nama', $search_val);
			$this->db->or_like('r.no_hp', $search_val);
			$this->db->or_like('k.nama', $search_val);
			$this->db->or_like('r.status_rumah', $search_val);
			$this->db->group_end();
		}

		$filtered_records = $this->db->count_all_results('', FALSE);

		$this->db->order_by($order_col, $order_dir);
		$this->db->limit($length, $start);
		$rows = $this->db->get()->result();

		$this->load->helper('wa');

		$data = [];
		$no = $start + 1;
		foreach ($rows as $row) {
			$curr_status = trim($row->status_rumah ?? '');
			if (empty($curr_status)) {
				$curr_status = 'Rumah Sendiri';
			}
			$is_kontrak = (stripos($curr_status, 'kontrak') !== false || stripos($curr_status, 'sewa') !== false);
			$is_musiman = (stripos($curr_status, 'musiman') !== false);

			if ($is_kontrak) {
				$badge_status = '<span class="badge-status-kontrak"><i class="bi bi-key-fill me-1"></i>Sewa / Kontrak</span>';
			} elseif ($is_musiman) {
				$badge_status = '<span class="badge-status-musiman"><i class="bi bi-clock-history me-1"></i>Musiman</span>';
			} else {
				$badge_status = '<span class="badge-status-sendiri"><i class="bi bi-house-check-fill me-1"></i>Rumah Sendiri</span>';
			}

			// Fallback cek nomor HP jika di master_rumah masih kosong
			$no_hp = trim($row->no_hp ?? '');
			if (empty($no_hp) && function_exists('get_no_hp_warga')) {
				$no_hp = get_no_hp_warga($row->id, $row->alamat);
				if (!empty($no_hp)) {
					$this->db->where('id', $row->id)->update('master_rumah', ['no_hp' => $no_hp]);
				}
			}

			// No HP format
			if (!empty($no_hp)) {
				$wa_num = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $no_hp));
				$no_hp_html = '<a href="https://wa.me/' . $wa_num . '" target="_blank" class="text-decoration-none text-success fw-semibold"><i class="bi bi-whatsapp me-1"></i>' . htmlspecialchars($no_hp) . '</a>';
			} else {
				$no_hp_html = '<span class="text-muted small"><i class="bi bi-dash"></i> Belum ada</span>';
			}

			// Action button
			$btn_edit = '<button type="button" class="btn btn-sm btn-outline-success btn-edit-rumah rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1" ' .
				'data-id="' . $row->id . '" ' .
				'data-alamat="' . htmlspecialchars($row->alamat ?? '', ENT_QUOTES) . '" ' .
				'data-nama="' . htmlspecialchars($row->nama ?? '', ENT_QUOTES) . '" ' .
				'data-status="' . htmlspecialchars($curr_status, ENT_QUOTES) . '" ' .
				'data-nohp="' . htmlspecialchars($no_hp, ENT_QUOTES) . '" ' .
				'data-idkoor="' . htmlspecialchars($row->id_koordinator ?? '', ENT_QUOTES) . '">' .
				'<i class="bi bi-pencil-square"></i> <span>Edit</span></button>';

			$data[] = [
				'<div class="text-center fw-bold text-muted">' . $no++ . '</div>',
				'<span class="fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i>' . htmlspecialchars($row->alamat ?? '-') . '</span>',
				'<strong><i class="bi bi-person-fill text-primary me-1"></i>' . htmlspecialchars($row->nama ?? '-') . '</strong>',
				$badge_status,
				$no_hp_html,
				'<span class="badge bg-light text-secondary border px-2 py-1"><i class="bi bi-person-badge me-1"></i>' . htmlspecialchars($row->koordinator ?? '-') . '</span>',
				'<div class="text-center">' . $btn_edit . '</div>'
			];
		}

		echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $total_records,
			'recordsFiltered' => $filtered_records,
			'data' => $data
		]);
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
		$no_hp = trim($this->input->post('no_hp', true));

		$is_ajax = $this->input->is_ajax_request();

		// Validasi tabel
		$allowed_tables = ['master_admin', 'master_koordinator_blok'];
		if (!in_array($table, $allowed_tables) || empty($id) || empty($nama)) {
			if ($is_ajax) {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'error', 'message' => 'Data tidak valid atau nama tidak boleh kosong.']);
				return;
			}
			$this->session->set_flashdata('error', 'Data tidak valid.');
			redirect('setting?tab=users');
			return;
		}

		$update_data = ['nama' => $nama];
		if ($table === 'master_koordinator_blok') {
			$update_data['no_hp'] = $no_hp;
		}

		$this->db->where('id', $id);
		$this->db->update($table, $update_data);

		if ($is_ajax) {
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 'success',
				'message' => 'Data user ' . $nama . ' berhasil diperbarui!'
			]);
			return;
		}

		if ($this->db->affected_rows() >= 0) {
			$this->session->set_flashdata('success', 'Data user berhasil diperbarui.');
		} else {
			$this->session->set_flashdata('error', 'Gagal memperbarui data user.');
		}

		redirect('setting?tab=users');
	}

	/**
	 * Update nama pemilik rumah & status kepemilikan
	 */
	public function update_rumah()
	{
		$this->checkSession();

		$id = $this->input->post('rumah_id', true);
		$nama = trim($this->input->post('nama', true));
		$no_hp = trim($this->input->post('no_hp', true));
		$status_rumah = trim($this->input->post('status_rumah', true));
		$id_koordinator = $this->input->post('id_koordinator', true);

		if (empty($status_rumah)) {
			$status_rumah = 'Rumah Sendiri';
		}

		$is_ajax = $this->input->is_ajax_request();

		if (empty($id)) {
			if ($is_ajax) {
				header('Content-Type: application/json');
				echo json_encode(['status' => 'error', 'message' => 'ID rumah tidak valid.']);
				return;
			}
			$this->session->set_flashdata('error', 'ID rumah tidak valid.');
			redirect('setting?tab=rumah');
			return;
		}

		$update_payload = [
			'nama' => $nama,
			'no_hp' => $no_hp,
			'status_rumah' => $status_rumah
		];

		// If admin and id_koordinator passed
		if (!empty($id_koordinator) && $this->session->userdata['username']['role'] === 'admin') {
			$update_payload['id_koordinator'] = $id_koordinator;
		}

		$this->db->where('id', $id);
		$this->db->update('master_rumah', $update_payload);

		// Sinkronisasi ke master_keluarga jika diperlukan
		$rumah = $this->db->get_where('master_rumah', ['id' => $id])->row_array();
		if ($rumah) {
			$update_keluarga = ['status_rumah' => $status_rumah];
			if (!empty($no_hp)) {
				$update_keluarga['no_hp'] = $no_hp;
			}
			$this->db->where('nomor_rumah', $rumah['alamat']);
			$this->db->update('master_keluarga', $update_keluarga);
		}

		if ($is_ajax) {
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 'success',
				'message' => 'Data rumah ' . ($rumah['alamat'] ?? '') . ' berhasil disimpan!'
			]);
			return;
		}

		if ($this->db->affected_rows() >= 0) {
			$this->session->set_flashdata('success', 'Data rumah berhasil diperbarui.');
		} else {
			$this->session->set_flashdata('error', 'Gagal memperbarui data rumah.');
		}

		redirect('setting?tab=rumah');
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
			$koordinator = $this->db->query("SELECT k.id, k.nama FROM master_koordinator_blok k WHERE EXISTS (SELECT 1 FROM master_rumah r WHERE r.id_koordinator = k.id) ORDER BY k.nama")->result_array();
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
			LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
			WHERE r.id IS NOT NULL $where_koor
			GROUP BY r.id, r.alamat, r.nama, r.id_koordinator
			ORDER BY r.alamat ASC
		")->result_array();

		// Pembayaran (Verified & Pending)
		$pembayaran_raw = $this->db->query("
			SELECT p.id, u.id_rumah, p.untuk_bulan, p.bulan_rapel, DATE_FORMAT(p.bulan_mulai, '%Y-%m') as bulan_mulai_ym, p.status, p.jumlah_bayar, p.tanggal_bayar
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			WHERE p.status IN ('verified','pending')
			AND (YEAR(p.untuk_bulan) = $tahun OR YEAR(p.bulan_mulai) = $tahun OR p.bulan_rapel LIKE '%$tahun%')
			$where_koor
		")->result_array();

		$lunas_map = [];
		$stats_map = [];
		foreach ($pembayaran_raw as $p) {
			$idr = $p['id_rumah'];
			$pid = $p['id'];
			
			if (!isset($lunas_map[$idr])) $lunas_map[$idr] = [];
			if (!isset($stats_map[$idr])) $stats_map[$idr] = ['total_bayar' => 0, 'terakhir_bayar' => null, 'counted_pids' => []];

			// Hitung total nominal & tgl terakhir bayar (1x per payment_id)
			if (!in_array($pid, $stats_map[$idr]['counted_pids'])) {
				$stats_map[$idr]['counted_pids'][] = $pid;
				$stats_map[$idr]['total_bayar'] += (float)$p['jumlah_bayar'];
				if (!empty($p['tanggal_bayar'])) {
					if (empty($stats_map[$idr]['terakhir_bayar']) || strtotime($p['tanggal_bayar']) > strtotime($stats_map[$idr]['terakhir_bayar'])) {
						$stats_map[$idr]['terakhir_bayar'] = $p['tanggal_bayar'];
					}
				}
			}

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

			$r['total_bayar'] = $stats_map[$idr]['total_bayar'] ?? 0;
			$r['terakhir_bayar'] = $stats_map[$idr]['terakhir_bayar'] ?? null;

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
				$max_ym = '';
				if (isset($lunas_map[$idr])) {
					foreach ($lunas_map[$idr] as $ym => $val) {
						if ($ym > $max_ym) $max_ym = $ym;
						$p_parts = explode('-', $ym);
						if ($p_parts[0] > $tahun || ($p_parts[0] == $tahun && (int)$p_parts[1] > $bulan_akhir_ipl)) {
							$is_dimuka = true; $dm_count++;
						}
					}
				}
				
				if ($is_dimuka) { 
					$r['bulan_dimuka'] = $dm_count; 
					if ($max_ym) {
						$parts = explode('-', $max_ym);
						$r['bayar_sampai'] = ($bulan_indo[(int)$parts[1]] ?? '') . ' ' . $parts[0];
					} else {
						$r['bayar_sampai'] = '-';
					}
					$dimuka[] = $r; 
				} else { 
					$rajin[] = $r; 
				}
			}
		}

		usort($menunggak, function($a,$b){ return $b['tunggakan'] - $a['tunggakan']; });
		usort($rajin, function($a,$b){ return $b['jumlah_bulan_bayar'] - $a['jumlah_bulan_bayar']; });
		usort($dimuka, function($a,$b){ return ($b['bulan_dimuka'] ?? 0) - ($a['bulan_dimuka'] ?? 0); });

		// Hitung jumlah kirim konfirmasi per rumah
		$konfirmasi_counts = [];
		$log_data = $this->db->query("SELECT id_rumah, COUNT(*) as total_kirim FROM log_konfirmasi WHERE status = 'success' GROUP BY id_rumah")->result_array();
		foreach ($log_data as $ld) {
			$konfirmasi_counts[$ld['id_rumah']] = (int)$ld['total_kirim'];
		}

		// Tambahkan count ke data rajin & dimuka
		foreach ($rajin as &$r_item) {
			$r_item['wa_count'] = $konfirmasi_counts[$r_item['id']] ?? 0;
		}
		unset($r_item);
		foreach ($dimuka as &$d_item) {
			$d_item['wa_count'] = $konfirmasi_counts[$d_item['id']] ?? 0;
		}
		unset($d_item);

		echo json_encode(['stats' => ['total'=>count($all_rumah), 'menunggak'=>count($menunggak), 'rajin'=>count($rajin), 'dimuka'=>count($dimuka)], 'total_bulan_wajib'=>$total_bulan_wajib, 'periode'=>$bulan_indo[$bulan_mulai_ipl].' - '.$bulan_indo[$bulan_akhir_ipl], 'menunggak'=>$menunggak, 'rajin'=>$rajin, 'dimuka'=>$dimuka]);
	}

	/**
	 * Generate PDF Rekapitulasi Warga Menunggak IPL
	 */
	public function rekap_menunggak_pdf()
	{
		$this->checkSession();
		mb_internal_encoding('UTF-8');
		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		$tahun = (int)($this->input->get('tahun', true) ?: date('Y'));
		$bulan_filter = $this->input->get('bulan', true);
		$group_by_koor = $this->input->get('group_by_koor') !== null ? (int)$this->input->get('group_by_koor') : 1;
		$tahun_sekarang = (int)date('Y');
		$bulan_sekarang = (int)date('n');

		$bulan_indo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
		$bulan_singkat = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'];

		// Koordinator filter: bisa 1, array, atau dipisahkan koma
		if ($this->session->userdata('username')['role'] === 'koordinator') {
			$koor_ids = [$this->session->userdata('username')['id']];
		} else {
			$raw_koor = $this->input->get('id_koordinator');
			if (is_array($raw_koor)) {
				$koor_ids = array_filter(array_map('trim', $raw_koor));
			} else if (!empty($raw_koor)) {
				$koor_ids = array_filter(explode(',', trim($raw_koor)));
			} else {
				$koor_ids = [];
			}
		}

		$where_koor = '';
		$selected_koor_names = [];
		if (!empty($koor_ids)) {
			$escaped_ids = array_map(function($id) { return "'" . $this->db->escape_str($id) . "'"; }, $koor_ids);
			$where_koor = "AND r.id_koordinator IN (" . implode(',', $escaped_ids) . ")";
			
			$koor_rows = $this->db->query("SELECT id, nama FROM master_koordinator_blok WHERE id IN (" . implode(',', $escaped_ids) . ") ORDER BY nama")->result_array();
			foreach ($koor_rows as $kr) {
				$selected_koor_names[] = $kr['nama'];
			}
		}

		$all_koor_count = $this->db->query("SELECT COUNT(*) as c FROM master_koordinator_blok")->row()->c;
		$is_all_koor = empty($koor_ids) || (count($koor_ids) >= (int)$all_koor_count);

		if ($is_all_koor) {
			$koordinator_label = 'Semua Koordinator (Seluruh Blok)';
		} else if (count($selected_koor_names) === 1) {
			$koordinator_label = $selected_koor_names[0];
		} else if (count($selected_koor_names) <= 3) {
			$koordinator_label = implode(', ', $selected_koor_names);
		} else {
			$koordinator_label = count($selected_koor_names) . ' Koordinator Terpilih (' . implode(', ', array_slice($selected_koor_names, 0, 2)) . ', dll)';
		}

		$bulan_mulai_ipl = ($tahun == 2025) ? 6 : 1;
		$bulan_akhir_ipl = !empty($bulan_filter) ? (int)$bulan_filter : (($tahun == $tahun_sekarang) ? $bulan_sekarang : 12);
		$total_bulan_wajib = max(1, $bulan_akhir_ipl - $bulan_mulai_ipl + 1);

		// Semua rumah
		$all_rumah = $this->db->query("
			SELECT r.id, r.alamat, r.nama, MAX(kl.no_hp) as no_hp, MAX(k.nama) as koordinator
			FROM master_users u
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
			LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
			WHERE r.id IS NOT NULL $where_koor
			GROUP BY r.id, r.alamat, r.nama, r.id_koordinator
			ORDER BY r.alamat ASC
		")->result_array();

		// Pembayaran (Verified & Pending)
		$pembayaran_raw = $this->db->query("
			SELECT p.id, u.id_rumah, p.untuk_bulan, p.bulan_rapel, DATE_FORMAT(p.bulan_mulai, '%Y-%m') as bulan_mulai_ym, p.status, p.jumlah_bayar, p.tanggal_bayar
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
			if (!empty($p['untuk_bulan']) && $p['untuk_bulan'] != '0000-00-00' && $p['untuk_bulan'] != '') {
				$lunas_map[$idr][date('Y-m', strtotime($p['untuk_bulan']))] = true;
			}
			if (!empty($p['bulan_rapel'])) {
				foreach (explode(',', $p['bulan_rapel']) as $br) {
					if (trim($br)) $lunas_map[$idr][trim($br)] = true;
				}
			}
			if ((empty($p['untuk_bulan']) || $p['untuk_bulan'] == '0000-00-00' || $p['untuk_bulan'] == '') && empty($p['bulan_rapel']) && !empty($p['bulan_mulai_ym'])) {
				$lunas_map[$idr][$p['bulan_mulai_ym']] = true;
			}
		}

		$menunggak = [];
		$total_akumulasi_bulan = 0;

		foreach ($all_rumah as $r) {
			$idr = $r['id'];
			$tunggak_names = [];
			$tunggak_short = [];
			$jumlah_bayar = 0;

			for ($m = $bulan_mulai_ipl; $m <= $bulan_akhir_ipl; $m++) {
				$key = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
				if (isset($lunas_map[$idr][$key])) {
					$jumlah_bayar++;
				} else {
					$tunggak_names[] = $bulan_indo[$m];
					$tunggak_short[] = $bulan_singkat[$m];
				}
			}

			$r['jumlah_bulan_bayar'] = $jumlah_bayar;
			$r['tunggakan'] = count($tunggak_names);
			$r['bulan_tunggak_list'] = implode(', ', $tunggak_names);
			$r['bulan_tunggak_short'] = implode(', ', $tunggak_short);
			$r['is_all_period'] = ($r['tunggakan'] === $total_bulan_wajib);
			$r['status_tunggak'] = $r['tunggakan'] > 0 ? ($r['tunggakan'] >= 3 ? 'Surat Teguran' : 'Peringatan') : 'Lunas';
			$r['level'] = $r['tunggakan'] >= 3 ? 'danger' : ($r['tunggakan'] > 0 ? 'warning' : 'success');

			if ($r['tunggakan'] > 0) {
				$menunggak[] = $r;
				$total_akumulasi_bulan += $r['tunggakan'];
			}
		}

		// Urutkan berdasarkan tunggakan terbanyak, lalu alamat
		usort($menunggak, function($a, $b) {
			if ($b['tunggakan'] !== $a['tunggakan']) {
				return $b['tunggakan'] - $a['tunggakan'];
			}
			return strnatcasecmp($a['alamat'], $b['alamat']);
		});

		// Pengelompokan data per Koordinator
		$grouped_menunggak = [];
		if ($group_by_koor) {
			foreach ($menunggak as $w) {
				$k_name = !empty($w['koordinator']) ? $w['koordinator'] : 'Tanpa Koordinator';
				if (!isset($grouped_menunggak[$k_name])) {
					$grouped_menunggak[$k_name] = [
						'nama' => $k_name,
						'items' => [],
						'total_warga' => 0,
						'total_bulan' => 0
					];
				}
				$grouped_menunggak[$k_name]['items'][] = $w;
				$grouped_menunggak[$k_name]['total_warga']++;
				$grouped_menunggak[$k_name]['total_bulan'] += $w['tunggakan'];
			}
			ksort($grouped_menunggak);
		}

		$periode_str = $bulan_indo[$bulan_mulai_ipl] . ' s/d ' . $bulan_indo[$bulan_akhir_ipl] . ' ' . $tahun;

		$data = [
			'tahun'                  => $tahun,
			'bulan_filter'           => $bulan_filter,
			'bulan_mulai_ipl'        => $bulan_mulai_ipl,
			'bulan_akhir_ipl'        => $bulan_akhir_ipl,
			'total_bulan_wajib'      => $total_bulan_wajib,
			'periode_str'            => $periode_str,
			'koordinator_label'      => $koordinator_label,
			'selected_koor_names'    => $selected_koor_names,
			'group_by_koor'          => $group_by_koor,
			'grouped_menunggak'      => $grouped_menunggak,
			'total_rumah'            => count($all_rumah),
			'total_menunggak'        => count($menunggak),
			'total_akumulasi_bulan'  => $total_akumulasi_bulan,
			'menunggak'              => $menunggak,
			'user_session'           => $this->session->userdata('username'),
		];

		$html = $this->load->view('dashboard/cetak_rekap_menunggak', $data, true);

		// Normalisasi karakter khusus/unicode
		$html = preg_replace('/[‐-‒–—−]/u', '-', $html);
		if (stripos($html, '<meta charset') === false) {
			$html = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html;
		}

		$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator('Perum TSI');
		$pdf->SetAuthor('Paguyuban TSI');
		$pdf->SetTitle('Rekapitulasi Warga Menunggak IPL - ' . $periode_str);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->setFooterFont(['helvetica', 'I', 8]);
		$pdf->SetFooterMargin(10);
		$pdf->SetMargins(12, 10, 12, true);
		$pdf->SetAutoPageBreak(TRUE, 12);
		$pdf->AddPage();
		$pdf->writeHTML($html, true, false, true, false, '');

		$nama_file = 'rekap_warga_menunggak_' . $tahun . '_' . date('Ymd_His') . '.pdf';
		$pdf->Output($nama_file, 'I');
	}

	/**
	 * Generate PDF Surat Teguran Pembayaran IPL
	 */
	public function surat_teguran_pdf($save_path = null, $is_kosongan = false)
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
		
		$pdf->Ln(2); $curr_y = $pdf->GetY(); 
		if (!$is_kosongan) {
			$pdf->SetDrawColor(0, 128, 0); $pdf->SetTextColor(0, 128, 0); $pdf->SetLineWidth(0.3);
			$xk = $lm + ($col_w - $stamp_w) / 2; $pdf->RoundedRect($xk, $curr_y, $stamp_w, $stamp_h, 2, '1111', 'D');
			$pdf->SetXY($xk, $curr_y + 2); $pdf->SetFont('dejavusans', 'B', 7); $pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C'); $pdf->SetX($xk); $pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');
			$xn = $lm + $col_w + ($col_w - $stamp_w) / 2; $pdf->RoundedRect($xn, $curr_y, $stamp_w, $stamp_h, 2, '1111', 'D');
			$pdf->SetXY($xn, $curr_y + 2); $pdf->SetFont('dejavusans', 'B', 7); $pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C'); $pdf->SetX($xn); $pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');
		}

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
		if (!$is_kosongan) {
			$pdf->SetDrawColor(0, 128, 0);
			$pdf->SetTextColor(0, 128, 0);
			$pdf->RoundedRect($sx, $sy, $stamp_w, $stamp_h, 2, '1111', 'D');
			$pdf->SetXY($sx, $sy+2);
			$pdf->SetFont('dejavusans', 'B', 7);
			$pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C');
			$pdf->SetX($sx);
			$pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');
		}

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
	 * Batch Generate Surat Teguran PDF → ZIP Download
	 * POST: id_rumah[] (array), tahun, bulan
	 */
	public function batch_surat_teguran_zip()
	{
		$this->checkSession();

		$id_rumah_list = $this->input->post('id_rumah');
		$tahun = $this->input->post('tahun') ?: date('Y');
		$bulan = $this->input->post('bulan');

		if (empty($id_rumah_list) || !is_array($id_rumah_list)) {
			show_error('Tidak ada warga yang dipilih.');
			return;
		}

		// Buat folder temp untuk batch
		$batch_id = 'batch_' . date('YmdHis') . '_' . mt_rand(1000, 9999);
		$temp_dir = FCPATH . 'assets/temp_pdf/' . $batch_id . '/';
		if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);

		$pdf_files = [];

		foreach ($id_rumah_list as $id_rumah) {
			$id_rumah = (int) $id_rumah;

			// Set GET params agar surat_teguran_pdf() bisa membacanya
			$_GET['id_rumah'] = $id_rumah;
			$_GET['tahun'] = $tahun;
			$_GET['bulan'] = $bulan;

			// Ambil data rumah untuk penamaan file
			$rumah = $this->db->get_where('master_rumah', ['id' => $id_rumah])->row_array();
			if (!$rumah) continue;

			$safe_alamat = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rumah['alamat']);
			$pdf_filename = 'Surat_Teguran_' . $safe_alamat . '.pdf';
			$pdf_path_tte = $temp_dir . 'TTE_' . $pdf_filename;
			$pdf_path_kosong = $temp_dir . 'Kosong_' . $pdf_filename;

			// Generate PDF ke file (reuse method yang sudah ada)
			try {
				// 1. Generate dengan TTE
				$this->surat_teguran_pdf($pdf_path_tte, false);
				if (file_exists($pdf_path_tte)) {
					$pdf_files[] = [
						'path' => $pdf_path_tte,
						'name' => 'TTE/' . $pdf_filename
					];
				}

				// 2. Generate Kosongan (tanpa TTE)
				$this->surat_teguran_pdf($pdf_path_kosong, true);
				if (file_exists($pdf_path_kosong)) {
					$pdf_files[] = [
						'path' => $pdf_path_kosong,
						'name' => 'Kosongan/' . $pdf_filename
					];
				}
			} catch (Exception $e) {
				// Skip jika gagal (misalnya tidak ada tunggakan)
				continue;
			}
		}

		if (empty($pdf_files)) {
			// Cleanup
			$this->_cleanup_dir($temp_dir);
			show_error('Tidak ada PDF yang berhasil di-generate. Kemungkinan warga yang dipilih tidak memiliki tunggakan.');
			return;
		}

		// Buat ZIP
		$zip_filename = 'Surat_Teguran_Batch_' . date('Y-m-d_His') . '.zip';
		$zip_path = FCPATH . 'assets/temp_pdf/' . $zip_filename;

		$zip = new ZipArchive();
		if ($zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
			$this->_cleanup_dir($temp_dir);
			show_error('Gagal membuat file ZIP.');
			return;
		}

		foreach ($pdf_files as $file) {
			$zip->addFile($file['path'], $file['name']);
		}
		$zip->close();

		// Stream ZIP ke browser
		header('Content-Type: application/zip');
		header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
		header('Content-Length: ' . filesize($zip_path));
		header('Cache-Control: no-cache, must-revalidate');
		readfile($zip_path);

		// Cleanup
		$this->_cleanup_dir($temp_dir);
		if (file_exists($zip_path)) unlink($zip_path);
		exit;
	}

	/**
	 * Helper: Hapus folder temp beserta isinya
	 */
	private function _cleanup_dir($dir)
	{
		if (!is_dir($dir)) return;
		$files = glob($dir . '*');
		foreach ($files as $file) {
			if (is_file($file)) unlink($file);
		}
		rmdir($dir);
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
		
		$this->load->helper('wa');
		$no_hp = get_no_hp_warga($id_rumah, $rumah['alamat'] ?? '');
		
		$bulan_indo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

		// Hitung tunggakan dengan benar
		$bulan_mulai_ipl = ($tahun == 2025) ? 6 : 1;
		$bulan_akhir_hitung = !empty($bulan_filter) ? (int)$bulan_filter : (($tahun == (int)date('Y')) ? (int)date('n') : 12);
		
		$p_raw = $this->db->query("SELECT untuk_bulan, bulan_rapel, DATE_FORMAT(bulan_mulai, '%Y-%m') as bulan_mulai_ym FROM master_pembayaran WHERE user_id IN (SELECT id FROM master_users WHERE id_rumah = ?) AND status IN ('verified', 'pending') AND (YEAR(untuk_bulan) = ? OR YEAR(bulan_mulai) = ? OR bulan_rapel LIKE ?)", [$id_rumah, $tahun, $tahun, '%'.$tahun.'%'])->result_array();
		$bulan_lunas = [];
		foreach ($p_raw as $p) {
			if (!empty($p['untuk_bulan']) && $p['untuk_bulan'] != '0000-00-00' && $p['untuk_bulan'] != '') $bulan_lunas[date('Y-m', strtotime($p['untuk_bulan']))] = true;
			if (!empty($p['bulan_rapel'])) { foreach (explode(',', $p['bulan_rapel']) as $br) if (trim($br)) $bulan_lunas[trim($br)] = true; }
			if ((empty($p['untuk_bulan']) || $p['untuk_bulan'] == '0000-00-00' || $p['untuk_bulan'] == '') && empty($p['bulan_rapel']) && !empty($p['bulan_mulai_ym'])) $bulan_lunas[$p['bulan_mulai_ym']] = true;
		}

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
	/**
	 * Hitung data log koordinator (binaan, lunas, nunggak, capaian)
	 */
	private function _get_log_koordinator_data($bulan, $tahun)
	{
		$nama_bulan = [
			'01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
			'05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
			'09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
		];
		$bulan = sprintf('%02d', (int)$bulan);
		$tahun = (int)$tahun;
		$bulan_str = sprintf('%04d-%02d', $tahun, $bulan);

		// Pastikan kolom no_hp ada di master_koordinator_blok
		if (!$this->db->field_exists('no_hp', 'master_koordinator_blok')) {
			$this->db->query("ALTER TABLE master_koordinator_blok ADD no_hp VARCHAR(20) DEFAULT NULL");
		}

		// Koordinator yang punya rumah binaan saja
		$koordinator_all = $this->db->query("
			SELECT k.id, k.nama, k.username, k.no_hp 
			FROM master_koordinator_blok k
			WHERE EXISTS (SELECT 1 FROM master_rumah r WHERE r.id_koordinator = k.id)
			ORDER BY k.nama ASC
		")->result_array();

		// Ambil semua rumah binaan beserta no_hp warga
		$semua_rumah = $this->db->query("
			SELECT r.id as id_rumah, r.alamat, r.nama, r.id_koordinator, MAX(kl.no_hp) as no_hp
			FROM master_rumah r
			LEFT JOIN master_keluarga kl ON kl.nomor_rumah COLLATE utf8mb4_general_ci = r.alamat COLLATE utf8mb4_general_ci AND kl.no_hp IS NOT NULL AND kl.no_hp != ''
			WHERE r.id_koordinator IS NOT NULL
			GROUP BY r.id, r.alamat, r.nama, r.id_koordinator
			ORDER BY r.alamat ASC
		")->result_array();

		// Ambil seluruh data pembayaran untuk bulan kewajiban ini
		$pembayaran_bulan = $this->db->query("
			SELECT p.id, u.id_rumah, p.status, p.jumlah_bayar, p.pembayaran_via, p.tanggal_bayar, p.created_at
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			WHERE p.status IN ('verified', 'pending')
			AND (
				DATE_FORMAT(p.untuk_bulan, '%Y-%m') = ?
				OR FIND_IN_SET(?, p.bulan_rapel) > 0
				OR (
					DATE_FORMAT(p.bulan_mulai, '%Y-%m') = ?
					AND (p.bulan_rapel IS NULL OR p.bulan_rapel = '')
					AND (p.untuk_bulan IS NULL OR p.untuk_bulan = '' OR p.untuk_bulan = '0000-00-00')
				)
			)
			ORDER BY p.id DESC
		", [$bulan_str, $bulan_str, $bulan_str])->result_array();

		// Map pembayaran per id_rumah
		$pay_by_rumah = [];
		foreach ($pembayaran_bulan as $p) {
			if (!isset($pay_by_rumah[$p['id_rumah']])) {
				$pay_by_rumah[$p['id_rumah']] = $p;
			}
		}

		// Kelompokkan rumah per koordinator
		$rumah_by_koor = [];
		foreach ($semua_rumah as $r) {
			$rumah_by_koor[$r['id_koordinator']][] = $r;
		}

		// Gabungkan data per koordinator
		$result = [];
		$total_rumah_all = 0;
		$total_lunas_all = 0;
		$total_nominal_all = 0;
		$rajin_cnt = 0;
		$belum_entry_cnt = 0;

		foreach ($koordinator_all as $k) {
			$kid = $k['id'];
			$daftarrumah = $rumah_by_koor[$kid] ?? [];
			$list_lunas = [];
			$list_nunggak = [];
			$total_nominal = 0;
			$last_entry = null;
			$verified_cnt = 0;
			$pending_cnt = 0;
			$via_koor = 0;
			$via_tf = 0;

			foreach ($daftarrumah as $rm) {
				$idr = $rm['id_rumah'];
				if (isset($pay_by_rumah[$idr])) {
					$pay = $pay_by_rumah[$idr];
					$total_nominal += (float)$pay['jumlah_bayar'];
					if ($pay['status'] === 'verified') $verified_cnt++;
					elseif ($pay['status'] === 'pending') $pending_cnt++;

					if ($pay['pembayaran_via'] === 'koordinator') $via_koor++;
					elseif (in_array($pay['pembayaran_via'], ['transfer', 'transfer_2'])) $via_tf++;

					$tgl_entry = $pay['created_at'] ?: $pay['tanggal_bayar'];
					if ($tgl_entry && (!$last_entry || strtotime($tgl_entry) > strtotime($last_entry))) {
						$last_entry = $tgl_entry;
					}

					$list_lunas[] = array_merge($rm, [
						'pembayaran_id' => $pay['id'],
						'jumlah_bayar'  => $pay['jumlah_bayar'],
						'tanggal_bayar' => $pay['tanggal_bayar'],
						'pembayaran_via'=> $pay['pembayaran_via'],
						'status'        => $pay['status'],
					]);
				} else {
					$list_nunggak[] = $rm;
				}
			}

			$jml_rumah = count($daftarrumah);
			$jml_lunas = count($list_lunas);
			$jml_nunggak = count($list_nunggak);
			$persen = ($jml_rumah > 0) ? round(($jml_lunas / $jml_rumah) * 100) : 0;

			$total_rumah_all += $jml_rumah;
			$total_lunas_all += $jml_lunas;
			$total_nominal_all += $total_nominal;

			// Kategori kerajinan
			if ($jml_rumah > 0 && $jml_lunas == $jml_rumah) {
				$status_rajin = 'sangat_rajin';
				$label_rajin = '🌟 Tuntas 100%';
				$badge_class = 'bg-success';
				$rajin_cnt++;
			} elseif ($persen >= 75) {
				$status_rajin = 'rajin';
				$label_rajin = '✅ Rajin (' . $persen . '%)';
				$badge_class = 'bg-success';
				$rajin_cnt++;
			} elseif ($persen >= 50) {
				$status_rajin = 'cukup';
				$label_rajin = '⚡ Cukup (' . $persen . '%)';
				$badge_class = 'bg-warning text-dark';
			} elseif ($jml_lunas > 0) {
				$status_rajin = 'kurang';
				$label_rajin = '⚠️ Kurang (' . $persen . '%)';
				$badge_class = 'bg-danger';
			} else {
				$status_rajin = 'belum_entry';
				$label_rajin = '❌ Belum Entry';
				$badge_class = 'bg-dark';
				$belum_entry_cnt++;
			}

			$result[] = [
				'id'              => $kid,
				'nama'            => $k['nama'],
				'username'        => $k['username'],
				'no_hp'           => $k['no_hp'],
				'jml_rumah'       => $jml_rumah,
				'total_entry'     => $jml_lunas,
				'jml_lunas'       => $jml_lunas,
				'jml_nunggak'     => $jml_nunggak,
				'persen_lunas'    => $persen,
				'status_rajin'    => $status_rajin,
				'label_rajin'     => $label_rajin,
				'badge_class'     => $badge_class,
				'total_nominal'   => $total_nominal,
				'last_entry'      => $last_entry,
				'verified'        => $verified_cnt,
				'pending'         => $pending_cnt,
				'via_koordinator' => $via_koor,
				'via_transfer'    => $via_tf,
				'list_lunas'      => $list_lunas,
				'list_nunggak'    => $list_nunggak,
			];
		}

		// Urutkan default: % tertinggi ke terendah
		usort($result, function($a, $b) {
			if ($b['persen_lunas'] != $a['persen_lunas']) return $b['persen_lunas'] - $a['persen_lunas'];
			return $b['total_entry'] - $a['total_entry'];
		});

		// Top 3 Koordinator Terajin
		$top3 = array_slice($result, 0, 3);

		// Bottom 3 (Perlu Diingatkan)
		$sorted_bottom = $result;
		usort($sorted_bottom, function($a, $b) {
			if ($b['jml_nunggak'] != $a['jml_nunggak']) return $b['jml_nunggak'] - $a['jml_nunggak'];
			return $a['persen_lunas'] - $b['persen_lunas'];
		});
		$bottom3 = array_slice($sorted_bottom, 0, 3);

		$persen_total_perumahan = ($total_rumah_all > 0) ? round(($total_lunas_all / $total_rumah_all) * 100) : 0;

		return [
			'koordinator_list'       => $result,
			'total_koordinator'      => count($result),
			'total_rajin'            => $rajin_cnt,
			'total_belum_entry'      => $belum_entry_cnt,
			'persen_total_perumahan' => $persen_total_perumahan,
			'total_lunas_all'        => $total_lunas_all,
			'total_rumah_all'        => $total_rumah_all,
			'total_nominal_all'      => $total_nominal_all,
			'top3'                   => $top3,
			'bottom3'                => $bottom3,
			'bulan'                  => $bulan,
			'tahun'                  => $tahun,
			'bulan_str'              => $bulan_str,
			'bulan_label'            => $nama_bulan[$bulan] ?? $bulan,
		];
	}

	/**
	 * Halaman Log Laporan Koordinator (Shell View)
	 */
	public function log_koordinator()
	{
		$this->checkSession();

		$data['bulan_filter'] = $this->input->get('bulan') ?: date('m');
		$data['tahun_filter'] = $this->input->get('tahun') ?: date('Y');
		$data['halaman'] = 'dashboard/log_koordinator';
		$this->load->view('modul', $data);
	}

	/**
	 * Endpoint AJAX DataTables untuk Rekap Koordinator
	 */
	public function ajax_log_koordinator()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		$bulan = $this->input->get('bulan') ?: date('m');
		$tahun = $this->input->get('tahun') ?: date('Y');

		$res = $this->_get_log_koordinator_data($bulan, $tahun);

		$data_rows = [];
		$no = 1;
		foreach ($res['koordinator_list'] as $k) {
			$data_rows[] = [
				'no'             => $no++,
				'id'             => $k['id'],
				'nama'           => $k['nama'],
				'username'       => $k['username'],
				'no_hp'          => $k['no_hp'],
				'jml_rumah'      => $k['jml_rumah'],
				'jml_lunas'      => $k['jml_lunas'],
				'jml_nunggak'    => $k['jml_nunggak'],
				'persen_lunas'   => $k['persen_lunas'],
				'total_nominal'  => $k['total_nominal'],
				'total_nominal_rp' => 'Rp' . number_format($k['total_nominal'], 0, ',', '.'),
				'last_entry'     => $k['last_entry'],
				'last_entry_formatted' => $k['last_entry'] ? date('d/m/Y H:i', strtotime($k['last_entry'])) : '-',
				'status_rajin'   => $k['status_rajin'],
				'label_rajin'    => $k['label_rajin'],
				'badge_class'    => $k['badge_class'],
			];
		}

		echo json_encode([
			'status' => 'success',
			'kpi'    => [
				'total_koordinator'       => $res['total_koordinator'],
				'total_rajin'             => $res['total_rajin'],
				'total_belum_entry'       => $res['total_belum_entry'],
				'persen_total_perumahan'  => $res['persen_total_perumahan'],
				'total_lunas_all'         => $res['total_lunas_all'],
				'total_rumah_all'         => $res['total_rumah_all'],
				'total_nominal_all_rp'    => 'Rp' . number_format($res['total_nominal_all'], 0, ',', '.'),
				'bulan_label'             => $res['bulan_label'],
				'tahun'                   => $res['tahun'],
				'top3'                    => $res['top3'],
				'bottom3'                 => $res['bottom3'],
			],
			'data'   => $data_rows
		]);
	}

	/**
	 * Endpoint AJAX Detail Rekap Koordinator (Rumah Lunas & Nunggak)
	 */
	public function ajax_detail_log_koordinator()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		$id_koor = (int)$this->input->get('id');
		$bulan = $this->input->get('bulan') ?: date('m');
		$tahun = $this->input->get('tahun') ?: date('Y');

		if (empty($id_koor)) {
			echo json_encode(['status' => 'error', 'message' => 'ID Koordinator tidak valid']);
			return;
		}

		$res = $this->_get_log_koordinator_data($bulan, $tahun);
		$selected = null;
		foreach ($res['koordinator_list'] as $k) {
			if ($k['id'] == $id_koor) {
				$selected = $k;
				break;
			}
		}

		if (!$selected) {
			echo json_encode(['status' => 'error', 'message' => 'Data koordinator tidak ditemukan']);
			return;
		}

		// WhatsApp reminder link
		$wa_link = '';
		if (!empty($selected['no_hp']) && $selected['jml_nunggak'] > 0) {
			$wa_msg = "Halo Bapak/Ibu Koordinator *" . $selected['nama'] . "*,\n\nSalam dari Pengurus Paguyuban TSI.\nMohon bantuannya untuk menindaklanjuti entri rekapan iuran IPL bulan *" . $res['bulan_label'] . " " . $tahun . "*.\nSaat ini di binaan Anda masih tercatat *" . $selected['jml_nunggak'] . " rumah* yang belum terentri/bayar dari total " . $selected['jml_rumah'] . " rumah binaan.\n\nTerima kasih banyak atas dedikasi dan kerjasamanya!";
			$wa_link = "https://wa.me/" . preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $selected['no_hp'])) . "?text=" . urlencode($wa_msg);
		}

		echo json_encode([
			'status' => 'success',
			'koordinator' => [
				'id'           => $selected['id'],
				'nama'         => $selected['nama'],
				'no_hp'        => $selected['no_hp'],
				'jml_rumah'    => $selected['jml_rumah'],
				'jml_lunas'    => $selected['jml_lunas'],
				'jml_nunggak'  => $selected['jml_nunggak'],
				'persen_lunas' => $selected['persen_lunas'],
				'total_nominal'=> $selected['total_nominal'],
				'total_nominal_rp' => 'Rp' . number_format($selected['total_nominal'], 0, ',', '.'),
				'label_rajin'  => $selected['label_rajin'],
				'badge_class'  => $selected['badge_class'],
				'wa_link'      => $wa_link,
				'bulan_label'  => $res['bulan_label'],
				'tahun'        => $tahun,
			],
			'list_nunggak' => $selected['list_nunggak'],
			'list_lunas'   => $selected['list_lunas'],
		]);
	}

	/**
	 * Kirim Konfirmasi Verifikasi / Kitir via WhatsApp (Lancar & Bayar Di Muka)
	 * Generates PDF kitir and sends as document via WA API
	 */
	public function kirim_konfirmasi_wa()
	{
		$this->checkSession();
		header('Content-Type: application/json');

		$id_rumah = $this->input->get('id_rumah', true);
		$tahun = (int)($this->input->get('tahun', true) ?: date('Y'));
		$bulan_filter = $this->input->get('bulan', true);
		$tipe = $this->input->get('tipe', true); // 'lancar' atau 'dimuka'

		if (empty($id_rumah)) {
			echo json_encode(['status' => 'error', 'message' => 'ID Rumah tidak valid']);
			return;
		}

		// Data rumah
		$rumah = $this->db->get_where('master_rumah', ['id' => $id_rumah])->row_array();
		if (!$rumah) {
			echo json_encode(['status' => 'error', 'message' => 'Data rumah tidak ditemukan']);
			return;
		}

		$this->load->helper('wa');
		$no_hp = get_no_hp_warga($id_rumah, $rumah['alamat'] ?? '');

		if (empty($no_hp)) {
			echo json_encode(['status' => 'error', 'message' => 'Nomor HP warga tidak ditemukan. Mohon lengkapi data terlebih dahulu.']);
			return;
		}

		$bulan_indo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

		// Hitung pembayaran
		$bulan_mulai_ipl = ($tahun == 2025) ? 6 : 1;
		$bulan_akhir = !empty($bulan_filter) ? (int)$bulan_filter : (($tahun == (int)date('Y')) ? (int)date('n') : 12);

		// Ambil data pembayaran verified
		$pembayaran_raw = $this->db->query("
			SELECT p.id, p.untuk_bulan, p.bulan_rapel, DATE_FORMAT(p.bulan_mulai, '%Y-%m') as bulan_mulai_ym,
			       p.jumlah_bayar, p.tanggal_bayar, p.bulan_mulai
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			WHERE u.id_rumah = ? AND p.status = 'verified'
			AND (YEAR(p.untuk_bulan) = ? OR YEAR(p.bulan_mulai) = ? OR p.bulan_rapel LIKE ?)
			ORDER BY p.tanggal_bayar DESC
		", [$id_rumah, $tahun, $tahun, '%'.$tahun.'%'])->result_array();

		if (empty($pembayaran_raw)) {
			echo json_encode(['status' => 'error', 'message' => 'Tidak ditemukan data pembayaran verified untuk warga ini.']);
			return;
		}

		// Hitung bulan yang sudah lunas
		$lunas_map = [];
		$total_bayar = 0;
		$terakhir_bayar = null;
		$last_payment_id = null;

		foreach ($pembayaran_raw as $p) {
			$total_bayar += (float)$p['jumlah_bayar'];
			if (!empty($p['tanggal_bayar']) && (empty($terakhir_bayar) || strtotime($p['tanggal_bayar']) > strtotime($terakhir_bayar))) {
				$terakhir_bayar = $p['tanggal_bayar'];
			}
			if ($last_payment_id === null) $last_payment_id = $p['id'];

			if (!empty($p['untuk_bulan']) && $p['untuk_bulan'] != '0000-00-00') {
				$lunas_map[date('Y-m', strtotime($p['untuk_bulan']))] = true;
			}
			if (!empty($p['bulan_rapel'])) {
				foreach (explode(',', $p['bulan_rapel']) as $br) {
					if (trim($br)) $lunas_map[trim($br)] = true;
				}
			}
			if ((empty($p['untuk_bulan']) || $p['untuk_bulan'] == '0000-00-00') && empty($p['bulan_rapel']) && !empty($p['bulan_mulai_ym'])) {
				$lunas_map[$p['bulan_mulai_ym']] = true;
			}
		}

		$bulan_lunas = [];
		for ($m = $bulan_mulai_ipl; $m <= $bulan_akhir; $m++) {
			$key = $tahun . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
			if (isset($lunas_map[$key])) {
				$bulan_lunas[] = $bulan_indo[$m];
			}
		}

		// Info tambahan untuk dimuka
		$max_ym = '';
		$bulan_dimuka_count = 0;
		if ($tipe === 'dimuka') {
			foreach ($lunas_map as $ym => $val) {
				if ($ym > $max_ym) $max_ym = $ym;
				$p_parts = explode('-', $ym);
				if (count($p_parts) == 2 && ($p_parts[0] > $tahun || ($p_parts[0] == $tahun && (int)$p_parts[1] > $bulan_akhir))) {
					$bulan_dimuka_count++;
				}
			}
		}

		// === Generate PDF Kitir Verifikasi ===
		mb_internal_encoding('UTF-8');
		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator('Perum TSI');
		$pdf->SetTitle('Kitir Verifikasi Pembayaran IPL');
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(20, 15, 20);
		$pdf->AddPage();

		$lm = 20; $rm = 190; $cw = $rm - $lm;

		// Logo
		$logo = FCPATH . 'logo-tsi.png';
		if (file_exists($logo)) $pdf->Image($logo, $lm, 15, 20, 20, 'PNG');

		// Header
		$pdf->SetXY($lm + 23, 16);
		$pdf->SetFont('dejavusans', 'B', 12);
		$pdf->SetTextColor(30, 120, 180);
		$pdf->Cell($cw - 23, 6, 'PAGUYUBAN WARGA PERUMAHAN', 0, 1, 'C');
		$pdf->SetX($lm + 23);
		$pdf->Cell($cw - 23, 6, 'TAMAN SUKODONO INDAH', 0, 1, 'C');
		$pdf->SetX($lm + 23);
		$pdf->SetFont('dejavusans', '', 9);
		$pdf->SetTextColor(80, 80, 80);
		$pdf->Cell($cw - 23, 5, 'Ds. Jumputrejo, Kec. Sukodono, Kab. Sidoarjo, 61258.', 0, 1, 'C');

		$pdf->SetY(38);
		$pdf->SetDrawColor(30, 120, 180);
		$pdf->SetLineWidth(0.8);
		$pdf->Line($lm, 38, $rm, 38);

		// Judul
		$pdf->Ln(6);
		$pdf->SetTextColor(0);
		$pdf->SetFont('dejavusans', 'B', 14);
		if ($tipe === 'lancar') {
			$pdf->SetTextColor(34, 139, 34);
			$pdf->Cell(0, 8, 'SURAT KONFIRMASI LUNAS IPL', 0, 1, 'C');
		} else {
			$pdf->SetTextColor(102, 126, 234);
			$pdf->Cell(0, 8, 'SURAT KONFIRMASI BAYAR DI MUKA IPL', 0, 1, 'C');
		}

		$pdf->SetTextColor(0);
		$pdf->SetFont('dejavusans', '', 10);
		$periode_str = $bulan_indo[$bulan_mulai_ipl] . ' - ' . $bulan_indo[$bulan_akhir] . ' ' . $tahun;
		$pdf->Cell(0, 6, 'Periode: ' . $periode_str, 0, 1, 'C');

		// Garis bawah judul
		$pdf->Ln(4);
		$pdf->SetDrawColor(200, 200, 200);
		$pdf->SetLineWidth(0.3);
		$pdf->Line($lm, $pdf->GetY(), $rm, $pdf->GetY());

		// Isi surat
		$pdf->Ln(6);
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->Cell(0, 6, "Assalamu'alaikum Wr. Wb.", 0, 1);
		$pdf->Ln(3);
		$pdf->MultiCell(0, 6, 'Dengan hormat, melalui surat ini kami mengkonfirmasi bahwa:', 0, 'L');

		$pdf->Ln(3);
		$lbl_w = 45;
		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_w, 6, 'Nama', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->SetFont('dejavusans', 'B', 10);
		$pdf->Cell(0, 6, strtoupper($rumah['nama'] ?? '-'), 0, 1);

		$pdf->SetFont('dejavusans', '', 10);
		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_w, 6, 'Alamat', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->SetFont('dejavusans', 'B', 10);
		$pdf->Cell(0, 6, $rumah['alamat'] ?? '-', 0, 1);

		$pdf->SetFont('dejavusans', '', 10);
		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_w, 6, 'Periode', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->Cell(0, 6, $periode_str, 0, 1);

		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_w, 6, 'Total Pembayaran', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->SetFont('dejavusans', 'B', 11);
		$pdf->Cell(0, 6, 'Rp ' . number_format($total_bayar, 0, ',', '.') . ',-', 0, 1);

		$pdf->SetFont('dejavusans', '', 10);
		$pdf->Cell(10, 6, '', 0, 0);
		$pdf->Cell($lbl_w, 6, 'Bulan Lunas', 0, 0);
		$pdf->Cell(5, 6, ':', 0, 0);
		$pdf->MultiCell(0, 6, implode(', ', $bulan_lunas), 0, 'L');

		if ($tipe === 'dimuka' && $bulan_dimuka_count > 0 && $max_ym) {
			$parts = explode('-', $max_ym);
			$bayar_sampai_str = ($bulan_indo[(int)$parts[1]] ?? '') . ' ' . $parts[0];
			$pdf->Cell(10, 6, '', 0, 0);
			$pdf->Cell($lbl_w, 6, 'Bayar Sampai', 0, 0);
			$pdf->Cell(5, 6, ':', 0, 0);
			$pdf->SetFont('dejavusans', 'B', 10);
			$pdf->SetTextColor(102, 126, 234);
			$pdf->Cell(0, 6, $bayar_sampai_str . ' (+' . $bulan_dimuka_count . ' bulan di muka)', 0, 1);
			$pdf->SetTextColor(0);
		}

		// Status box
		$pdf->Ln(6);
		if ($tipe === 'lancar') {
			$pdf->SetFillColor(34, 139, 34);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFont('dejavusans', 'B', 13);
			$pdf->Cell(0, 12, '  STATUS : LUNAS  ', 0, 1, 'C', true);
		} else {
			$pdf->SetFillColor(102, 126, 234);
			$pdf->SetTextColor(255, 255, 255);
			$pdf->SetFont('dejavusans', 'B', 13);
			$pdf->Cell(0, 12, '  STATUS : BAYAR DI MUKA  ', 0, 1, 'C', true);
		}

		$pdf->SetTextColor(0);
		$pdf->SetFont('dejavusans', '', 10);

		// Keterangan penutup
		$pdf->Ln(6);
		if ($tipe === 'lancar') {
			$pdf->MultiCell(0, 6, 'Telah melunasi seluruh kewajiban Iuran Pengelolaan Lingkungan (IPL) untuk periode tersebut di atas. Terima kasih atas kepatuhan Bapak/Ibu dalam memenuhi kewajiban iuran.', 0, 'J');
		} else {
			$pdf->MultiCell(0, 6, 'Telah melakukan pembayaran IPL melebihi bulan berjalan (bayar di muka). Kami sangat mengapresiasi kontribusi Bapak/Ibu yang telah membayar lebih awal. Terima kasih.', 0, 'J');
		}

		$pdf->Ln(3);
		$pdf->Cell(0, 6, "Wassalamu'alaikum Wr. Wb.", 0, 1);

		// Tanda tangan
		$pdf->Ln(8);
		$pdf->Cell(0, 5, 'Sidoarjo, ' . date('d') . ' ' . $bulan_indo[(int)date('n')] . ' ' . date('Y'), 0, 1, 'R');
		$pdf->Ln(2);
		$pdf->Cell(0, 5, 'Bendahara TSI', 0, 1, 'R');

		// TTE
		$stamp_w = 40; $stamp_h = 16;
		$pdf->Ln(2);
		$sy = $pdf->GetY();
		$sx = $rm - $stamp_w - 15;
		$pdf->SetDrawColor(0, 128, 0);
		$pdf->SetTextColor(0, 128, 0);
		$pdf->RoundedRect($sx, $sy, $stamp_w, $stamp_h, 2, '1111', 'D');
		$pdf->SetXY($sx, $sy + 2);
		$pdf->SetFont('dejavusans', 'B', 7);
		$pdf->Cell($stamp_w, 4, 'DITANDATANGANI SECARA', 0, 1, 'C');
		$pdf->SetX($sx);
		$pdf->Cell($stamp_w, 4, 'ELEKTRONIK (TTE)', 0, 1, 'C');

		$pdf->Ln(11);
		$pdf->SetTextColor(0);
		$pdf->SetFont('dejavusans', 'BU', 10);
		$pdf->Cell(0, 5, 'Dhani Kispananto', 0, 1, 'R');

		// Simpan PDF ke file
		$label_file = ($tipe === 'lancar') ? 'Konfirmasi_Lunas' : 'Konfirmasi_BayarDimuka';
		$filename = $label_file . '_' . $id_rumah . '_' . date('YmdHis') . '.pdf';
		$temp_dir = FCPATH . 'assets/temp_pdf/';
		if (!is_dir($temp_dir)) mkdir($temp_dir, 0777, true);
		$filepath = $temp_dir . $filename;
		$pdf->Output($filepath, 'F');

		// === Kirim via WhatsApp ===
		$this->load->helper('wa');
		$media_url = base_url('assets/temp_pdf/' . $filename);

		if ($tipe === 'lancar') {
			$caption = "✅ *KONFIRMASI LUNAS IPL*\n\nAssalamu'alaikum Bapak/Ibu *" . $rumah['nama'] . "*,\n\nKami mengkonfirmasi bahwa pembayaran IPL untuk rumah *" . $rumah['alamat'] . "* periode *" . $periode_str . "* telah *LUNAS*.\n\nTotal: *Rp " . number_format($total_bayar, 0, ',', '.') . "*\nBulan lunas: " . implode(', ', $bulan_lunas) . "\n\nTerlampir e-kitir konfirmasi pembayaran.\nTerima kasih atas kepatuhan Bapak/Ibu. 🙏\n\n*Pengurus Paguyuban TSI*";
		} else {
			$parts_max = $max_ym ? explode('-', $max_ym) : [];
			$bayar_sampai_label = !empty($parts_max) ? (($bulan_indo[(int)$parts_max[1]] ?? '') . ' ' . $parts_max[0]) : '-';
			$caption = "⭐ *KONFIRMASI BAYAR DI MUKA IPL*\n\nAssalamu'alaikum Bapak/Ibu *" . $rumah['nama'] . "*,\n\nKami mengkonfirmasi bahwa pembayaran IPL untuk rumah *" . $rumah['alamat'] . "* telah dibayar hingga *" . $bayar_sampai_label . "* (+" . $bulan_dimuka_count . " bulan di muka).\n\nTotal: *Rp " . number_format($total_bayar, 0, ',', '.') . "*\n\nTerlampir e-kitir konfirmasi pembayaran.\nTerima kasih atas kontribusi luar biasa Bapak/Ibu. 🌟\n\n*Pengurus Paguyuban TSI*";
		}

		$res = send_wa_doc($no_hp, $media_url, $filename, $caption);

		if (isset($res['status']) && ($res['status'] === true || $res['status'] == '1')) {
			// Catat log konfirmasi
			$this->db->insert('log_konfirmasi', [
				'id_rumah' => $id_rumah,
				'tipe' => $tipe,
				'tgl_kirim' => date('Y-m-d H:i:s'),
				'dikirim_ke' => $no_hp,
				'status' => 'success',
				'tahun' => $tahun,
				'bulan' => $bulan_akhir
			]);
			$total_sent = $this->db->where(['id_rumah' => $id_rumah, 'status' => 'success'])->count_all_results('log_konfirmasi');
			echo json_encode(['status' => 'success', 'message' => 'Konfirmasi berhasil dikirim via WhatsApp beserta file PDF.<br><small class="text-muted">Dikirim ke: ' . $no_hp . ' (Total kirim: ' . $total_sent . 'x)</small>']);
		} else {
			// Catat log gagal
			$this->db->insert('log_konfirmasi', [
				'id_rumah' => $id_rumah,
				'tipe' => $tipe,
				'tgl_kirim' => date('Y-m-d H:i:s'),
				'dikirim_ke' => $no_hp,
				'status' => 'failed',
				'tahun' => $tahun,
				'bulan' => $bulan_akhir
			]);
			echo json_encode(['status' => 'error', 'message' => 'Gagal kirim WA: ' . ($res['message'] ?? 'Error API')]);
		}
	}

	public function checkSession()
	{
		if (empty($this->session->userdata['username'])) {
			redirect('./');
		}
	}
}
