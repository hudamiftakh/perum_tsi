<?php
defined('BASEPATH') or exit('No direct script access allowed');

class kas extends CI_Controller
{

	public function __construct()
	{
		error_reporting(1);
		parent::__construct();
		$this->load->database();
		$this->load->library('session');
		$this->load->library('pagination');
		$this->load->model('Kas_model');
		if (function_exists('ensure_log_tables_exist')) {
			ensure_log_tables_exist();
		}
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

	/**
	 * Modul Buku Kas Detail (Debit - Kredit)
	 * Menyajikan arus kas masuk (IPL) & keluar (operasional) dengan running balance
	 */
	public function buku_kas()
	{
		$this->checkSession();
		if (function_exists('ensure_database_schema_exist')) {
			ensure_database_schema_exist();
		}

		$tahun_sekarang = (int)date('Y');
		$bulan_sekarang = (int)date('m');

		$tahun = $this->input->get('tahun');
		$tahun = ($tahun !== null && $tahun !== '') ? (int)$tahun : $tahun_sekarang;

		$bulan = $this->input->get('bulan');
		if ($bulan === null) {
			$bulan = $bulan_sekarang;
		} elseif ($bulan !== 'semua' && $bulan !== '') {
			$bulan = (int)$bulan;
		}

		$kategori_filter = $this->input->get('kategori') ?: 'semua';

		if ($bulan === 'semua' || empty($bulan)) {
			$tgl_awal  = sprintf('%04d-01-01', $tahun);
			$tgl_akhir = sprintf('%04d-12-31', $tahun);
			$periode_label = 'Tahun ' . $tahun . ' (Januari - Desember)';
		} else {
			$bln_int   = (int)$bulan;
			$jml_hari  = cal_days_in_month(CAL_GREGORIAN, $bln_int, $tahun);
			$tgl_awal  = sprintf('%04d-%02d-01', $tahun, $bln_int);
			$tgl_akhir = sprintf('%04d-%02d-%02d', $tahun, $bln_int, $jml_hari);
			$nama_bulan = [
				1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
				5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
				9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
			];
			$periode_label = ($nama_bulan[$bln_int] ?? '') . ' ' . $tahun;
		}

		// 1. Saldo Awal sebelum periode
		$q_pemasukan_lalu = $this->db->query("
			SELECT COALESCE(SUM(jumlah_bayar), 0) as total
			FROM master_pembayaran
			WHERE status = 'verified'
			AND (CASE WHEN tanggal_bayar IS NOT NULL AND tanggal_bayar > '2000-01-01' THEN tanggal_bayar ELSE DATE(created_at) END) < ?
		", [$tgl_awal])->row_array()['total'] ?? 0;

		$q_pengeluaran_lalu = $this->db->query("
			SELECT COALESCE(SUM(nominal), 0) as total
			FROM t_pengeluaran
			WHERE active_status = 1
			AND tanggal < ?
		", [$tgl_awal])->row_array()['total'] ?? 0;

		$saldo_awal = (float)$q_pemasukan_lalu - (float)$q_pengeluaran_lalu;

		// 2. Transaksi Pemasukan (Debit)
		$pemasukan_raw = $this->db->query("
			SELECT p.id,
			       (CASE WHEN p.tanggal_bayar IS NOT NULL AND p.tanggal_bayar > '2000-01-01' THEN p.tanggal_bayar ELSE DATE(p.created_at) END) as tanggal,
			       p.jumlah_bayar, p.metode, p.bulan_mulai, p.untuk_bulan, p.bulan_rapel,
			       p.keterangan, p.pembayaran_via, p.bukti,
			       u.nama as warga_nama, r.alamat as rumah_alamat, k.nama as koordinator_nama
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			LEFT JOIN master_koordinator_blok k ON r.id_koordinator = k.id
			WHERE p.status = 'verified'
			AND (CASE WHEN p.tanggal_bayar IS NOT NULL AND p.tanggal_bayar > '2000-01-01' THEN p.tanggal_bayar ELSE DATE(p.created_at) END) BETWEEN ? AND ?
			ORDER BY tanggal ASC, p.id ASC
		", [$tgl_awal, $tgl_akhir])->result_array();

		// 3. Transaksi Pengeluaran (Kredit)
		$pengeluaran_raw = $this->db->query("
			SELECT t.id, t.tanggal, t.nominal, t.jenis_pengeluaran, t.deskripsi, t.keterangan,
			       COALESCE(mc.deskripsi, t.jenis_pengeluaran) as jenis_label
			FROM t_pengeluaran t
			LEFT JOIN master_code mc ON mc.app_code = 'pengeluaran' AND mc.code = t.jenis_pengeluaran
			WHERE t.active_status = 1
			AND t.tanggal BETWEEN ? AND ?
			ORDER BY t.tanggal ASC, t.id ASC
		", [$tgl_awal, $tgl_akhir])->result_array();

		// 4. Penggabungan data kronologis
		$transaksi = [];
		foreach ($pemasukan_raw as $p) {
			$ket = [];
			if (!empty($p['untuk_bulan']) && $p['untuk_bulan'] != '0000-00-00') {
				$ket[] = 'Bulan: ' . date('m/Y', strtotime($p['untuk_bulan']));
			}
			if (!empty($p['bulan_rapel'])) {
				$ket[] = 'Rapel: ' . $p['bulan_rapel'];
			}
			if (!empty($p['keterangan'])) {
				$ket[] = $p['keterangan'];
			}

			$uraian = 'IPL ' . ($p['rumah_alamat'] ?: 'Warga');
			if (!empty($p['warga_nama'])) {
				$uraian .= ' (' . $p['warga_nama'] . ')';
			}
			if (!empty($ket)) {
				$uraian .= ' - ' . implode(' | ', $ket);
			}

			$via_label = ucfirst($p['pembayaran_via'] ?? 'Tunai');
			if ($p['pembayaran_via'] === 'transfer_2') {
				$via_label = 'Transfer Rek 2';
			}

			$transaksi[] = [
				'id'          => (int)$p['id'],
				'tipe'        => 'debit',
				'tanggal'     => $p['tanggal'],
				'kategori'    => 'Iuran IPL Warga',
				'uraian'      => $uraian,
				'subjek'      => $p['warga_nama'] ?: ($p['rumah_alamat'] ?: 'Warga'),
				'alamat'      => $p['rumah_alamat'] ?? '',
				'via'         => $via_label,
				'bukti'       => $p['bukti'] ?? '',
				'debit'       => (float)$p['jumlah_bayar'],
				'kredit'      => 0,
			];
		}

		foreach ($pengeluaran_raw as $t) {
			$uraian = ($t['jenis_label'] ? '[' . $t['jenis_label'] . '] ' : '') . $t['deskripsi'];
			if (!empty($t['keterangan'])) {
				$uraian .= ' (' . $t['keterangan'] . ')';
			}

			$transaksi[] = [
				'id'          => (int)$t['id'],
				'tipe'        => 'kredit',
				'tanggal'     => $t['tanggal'],
				'kategori'    => $t['jenis_label'] ?: 'Pengeluaran Operasional',
				'uraian'      => $uraian,
				'subjek'      => $t['jenis_label'] ?: 'Operasional',
				'alamat'      => '-',
				'via'         => 'Kas Paguyuban',
				'bukti'       => '',
				'debit'       => 0,
				'kredit'      => (float)$t['nominal'],
			];
		}

		// Urutkan berdasarkan tanggal ASC
		usort($transaksi, function ($a, $b) {
			if ($a['tanggal'] === $b['tanggal']) {
				if ($a['tipe'] !== $b['tipe']) {
					return ($a['tipe'] === 'debit') ? -1 : 1;
				}
				return $a['id'] - $b['id'];
			}
			return strcmp($a['tanggal'], $b['tanggal']);
		});

		$running_balance = $saldo_awal;
		$total_debit = 0;
		$total_kredit = 0;
		foreach ($transaksi as &$tr) {
			$running_balance += ($tr['debit'] - $tr['kredit']);
			$tr['saldo'] = $running_balance;
			$total_debit += $tr['debit'];
			$total_kredit += $tr['kredit'];
		}
		unset($tr);

		$saldo_akhir = $running_balance;

		$transaksi_tampil = $transaksi;
		if ($kategori_filter === 'debit') {
			$transaksi_tampil = array_values(array_filter($transaksi, function ($item) {
				return $item['tipe'] === 'debit';
			}));
		} elseif ($kategori_filter === 'kredit') {
			$transaksi_tampil = array_values(array_filter($transaksi, function ($item) {
				return $item['tipe'] === 'kredit';
			}));
		}

		$data['tahun']            = $tahun;
		$data['bulan']            = $bulan;
		$data['kategori_filter']  = $kategori_filter;
		$data['periode_label']    = $periode_label;
		$data['tgl_awal']         = $tgl_awal;
		$data['tgl_akhir']        = $tgl_akhir;
		$data['saldo_awal']       = $saldo_awal;
		$data['saldo_akhir']      = $saldo_akhir;
		$data['total_debit']      = $total_debit;
		$data['total_kredit']     = $total_kredit;
		$data['surplus_defisit']  = $total_debit - $total_kredit;
		$data['transaksi']        = $transaksi_tampil;
		$data['total_transaksi']  = count($transaksi);
		$data['userData']         = $this->session->userdata('username');
		$data['halaman']          = 'keuangan/buku_kas';

		$this->load->view('modul', $data);
	}

	/**
	 * Export PDF Resmi Buku Kas Umum (Landscape)
	 */
	public function buku_kas_pdf()
	{
		$this->checkSession();
		require_once(APPPATH . 'libraries/tcpdf/tcpdf.php');

		$tahun_sekarang = (int)date('Y');
		$bulan_sekarang = (int)date('m');

		$tahun = (int)($this->input->get('tahun') ?: $tahun_sekarang);
		$bulan = $this->input->get('bulan');
		if ($bulan === null) {
			$bulan = $bulan_sekarang;
		} elseif ($bulan !== 'semua' && $bulan !== '') {
			$bulan = (int)$bulan;
		}

		if ($bulan === 'semua' || empty($bulan)) {
			$tgl_awal  = sprintf('%04d-01-01', $tahun);
			$tgl_akhir = sprintf('%04d-12-31', $tahun);
			$periode_label = 'Tahun ' . $tahun . ' (Januari - Desember)';
		} else {
			$bln_int   = (int)$bulan;
			$jml_hari  = cal_days_in_month(CAL_GREGORIAN, $bln_int, $tahun);
			$tgl_awal  = sprintf('%04d-%02d-01', $tahun, $bln_int);
			$tgl_akhir = sprintf('%04d-%02d-%02d', $tahun, $bln_int, $jml_hari);
			$nama_bulan = [
				1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
				5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
				9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
			];
			$periode_label = ($nama_bulan[$bln_int] ?? '') . ' ' . $tahun;
		}

		// Saldo Awal
		$q_pemasukan_lalu = $this->db->query("
			SELECT COALESCE(SUM(jumlah_bayar), 0) as total
			FROM master_pembayaran
			WHERE status = 'verified'
			AND (CASE WHEN tanggal_bayar IS NOT NULL AND tanggal_bayar > '2000-01-01' THEN tanggal_bayar ELSE DATE(created_at) END) < ?
		", [$tgl_awal])->row_array()['total'] ?? 0;

		$q_pengeluaran_lalu = $this->db->query("
			SELECT COALESCE(SUM(nominal), 0) as total
			FROM t_pengeluaran
			WHERE active_status = 1
			AND tanggal < ?
		", [$tgl_awal])->row_array()['total'] ?? 0;

		$saldo_awal = (float)$q_pemasukan_lalu - (float)$q_pengeluaran_lalu;

		// Pemasukan
		$pemasukan_raw = $this->db->query("
			SELECT p.id,
			       (CASE WHEN p.tanggal_bayar IS NOT NULL AND p.tanggal_bayar > '2000-01-01' THEN p.tanggal_bayar ELSE DATE(p.created_at) END) as tanggal,
			       p.jumlah_bayar, p.keterangan, p.pembayaran_via,
			       u.nama as warga_nama, r.alamat as rumah_alamat
			FROM master_pembayaran p
			LEFT JOIN master_users u ON p.user_id = u.id
			LEFT JOIN master_rumah r ON u.id_rumah = r.id
			WHERE p.status = 'verified'
			AND (CASE WHEN p.tanggal_bayar IS NOT NULL AND p.tanggal_bayar > '2000-01-01' THEN p.tanggal_bayar ELSE DATE(p.created_at) END) BETWEEN ? AND ?
			ORDER BY tanggal ASC, p.id ASC
		", [$tgl_awal, $tgl_akhir])->result_array();

		// Pengeluaran
		$pengeluaran_raw = $this->db->query("
			SELECT t.id, t.tanggal, t.nominal, t.jenis_pengeluaran, t.deskripsi, t.keterangan,
			       COALESCE(mc.deskripsi, t.jenis_pengeluaran) as jenis_label
			FROM t_pengeluaran t
			LEFT JOIN master_code mc ON mc.app_code = 'pengeluaran' AND mc.code = t.jenis_pengeluaran
			WHERE t.active_status = 1
			AND t.tanggal BETWEEN ? AND ?
			ORDER BY t.tanggal ASC, t.id ASC
		", [$tgl_awal, $tgl_akhir])->result_array();

		$transaksi = [];
		foreach ($pemasukan_raw as $p) {
			$uraian = 'IPL ' . ($p['rumah_alamat'] ?: 'Warga') . (!empty($p['warga_nama']) ? ' (' . $p['warga_nama'] . ')' : '');
			$transaksi[] = [
				'id'      => (int)$p['id'],
				'tipe'    => 'debit',
				'tanggal' => $p['tanggal'],
				'uraian'  => $uraian,
				'via'     => ucfirst($p['pembayaran_via'] ?? 'Tunai'),
				'debit'   => (float)$p['jumlah_bayar'],
				'kredit'  => 0,
			];
		}
		foreach ($pengeluaran_raw as $t) {
			$uraian = ($t['jenis_label'] ? '[' . $t['jenis_label'] . '] ' : '') . $t['deskripsi'];
			$transaksi[] = [
				'id'      => (int)$t['id'],
				'tipe'    => 'kredit',
				'tanggal' => $t['tanggal'],
				'uraian'  => $uraian,
				'via'     => 'Kas Paguyuban',
				'debit'   => 0,
				'kredit'  => (float)$t['nominal'],
			];
		}

		usort($transaksi, function ($a, $b) {
			if ($a['tanggal'] === $b['tanggal']) {
				return ($a['tipe'] === 'debit') ? -1 : 1;
			}
			return strcmp($a['tanggal'], $b['tanggal']);
		});

		$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->SetCreator('Perum TSI');
		$pdf->SetAuthor('Paguyuban TSI');
		$pdf->SetTitle('Buku Kas Umum - ' . $periode_label);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(true);
		$pdf->setFooterFont(['dejavusans', 'I', 8]);
		$pdf->SetMargins(10, 10, 10, true);
		$pdf->SetAutoPageBreak(true, 12);
		$pdf->AddPage();

		$pdf->SetFont('dejavusans', 'B', 14);
		$pdf->Cell(0, 7, 'PAGUYUBAN WARGA TAMAN SEDAYU INDAH (TSI)', 0, 1, 'C');
		$pdf->SetFont('dejavusans', 'B', 12);
		$pdf->Cell(0, 6, 'BUKU KAS UMUM (ARUS KAS DEBIT - KREDIT)', 0, 1, 'C');
		$pdf->SetFont('dejavusans', '', 10);
		$pdf->Cell(0, 5, 'Periode: ' . $periode_label . ' | Dicetak: ' . date('d/m/Y H:i') . ' WIB', 0, 1, 'C');
		$pdf->Ln(3);

		$html = '
		<table border="1" cellpadding="5" cellspacing="0" style="font-size: 8.5pt; border-collapse: collapse;">
			<thead>
				<tr style="background-color: #008d4c; color: #ffffff; font-weight: bold; text-align: center;">
					<th width="5%">No</th>
					<th width="10%">Tanggal</th>
					<th width="41%">Uraian Transaksi</th>
					<th width="10%">Via</th>
					<th width="11%">Debit (Masuk)</th>
					<th width="11%">Kredit (Keluar)</th>
					<th width="12%">Saldo (Rp)</th>
				</tr>
				<tr style="background-color: #f1f5f9; font-weight: bold;">
					<td colspan="6" align="right">SALDO AWAL PERIODE INI:</td>
					<td align="right">Rp ' . number_format($saldo_awal, 0, ',', '.') . '</td>
				</tr>
			</thead>
			<tbody>';

		$no = 1;
		$running = $saldo_awal;
		$tot_d = 0;
		$tot_k = 0;

		foreach ($transaksi as $tr) {
			$running += ($tr['debit'] - $tr['kredit']);
			$tot_d += $tr['debit'];
			$tot_k += $tr['kredit'];

			$bg = ($no % 2 === 0) ? '#f8fafc' : '#ffffff';
			$html .= '
				<tr style="background-color: ' . $bg . ';">
					<td align="center">' . $no++ . '</td>
					<td align="center">' . date('d/m/Y', strtotime($tr['tanggal'])) . '</td>
					<td>' . htmlspecialchars($tr['uraian']) . '</td>
					<td align="center">' . htmlspecialchars($tr['via']) . '</td>
					<td align="right" style="color: #15803d;">' . ($tr['debit'] > 0 ? 'Rp ' . number_format($tr['debit'], 0, ',', '.') : '-') . '</td>
					<td align="right" style="color: #b91c1c;">' . ($tr['kredit'] > 0 ? 'Rp ' . number_format($tr['kredit'], 0, ',', '.') : '-') . '</td>
					<td align="right" style="font-weight: bold;">Rp ' . number_format($running, 0, ',', '.') . '</td>
				</tr>';
		}

		$html .= '
				<tr style="background-color: #e2e8f0; font-weight: bold;">
					<td colspan="4" align="right">TOTAL TRANSAKSI PERIODE INI:</td>
					<td align="right" style="color: #15803d;">Rp ' . number_format($tot_d, 0, ',', '.') . '</td>
					<td align="right" style="color: #b91c1c;">Rp ' . number_format($tot_k, 0, ',', '.') . '</td>
					<td align="right">Surplus: Rp ' . number_format($tot_d - $tot_k, 0, ',', '.') . '</td>
				</tr>
				<tr style="background-color: #008d4c; color: #ffffff; font-weight: bold; font-size: 9pt;">
					<td colspan="6" align="right">SALDO AKHIR PERIODE:</td>
					<td align="right">Rp ' . number_format($running, 0, ',', '.') . '</td>
				</tr>
			</tbody>
		</table>';

		$pdf->writeHTML($html, true, false, true, false, '');

		// Tanda tangan
		$pdf->Ln(6);
		$sig_html = '
		<table border="0" cellpadding="2" style="font-size: 9pt;">
			<tr>
				<td width="35%" align="center">
					Mengetahui,<br><strong>Ketua Paguyuban TSI</strong><br><br><br><br>
					( ___________________________ )
				</td>
				<td width="30%"></td>
				<td width="35%" align="center">
					Surabaya, ' . date('d M Y') . '<br><strong>Koordinator Lingkungan / Bendahara</strong><br><br><br><br>
					( ___________________________ )
				</td>
			</tr>
		</table>';
		$pdf->writeHTML($sig_html, true, false, true, false, '');

		$filename = 'buku_kas_' . $tahun . '_' . ($bulan ?: 'semua') . '_' . date('Ymd_His') . '.pdf';
		$pdf->Output($filename, 'I');
	}
}
?>