<?php
function useraAuthData()
{
	$ci = &get_instance();
	if (!empty($ci->session->userdata['username'])) {
		return  $ci->session->userdata['username'];
	}
}
function format_tanggal($waktu)
{
	if (empty($waktu)) {
		return '-';
	}
	$hari_array = array(
		'Minggu',
		'Senin',
		'Selasa',
		'Rabu',
		'Kamis',
		'Jumat',
		'Sabtu'
	);
	$hr = date('w', strtotime($waktu));
	$hari = $hari_array[$hr];
	$tanggal = date('j', strtotime($waktu));
	$bulan_array = array(
		1 => 'Januari',
		2 => 'Februari',
		3 => 'Maret',
		4 => 'April',
		5 => 'Mei',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'Agustus',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Desember',
	);
	$bl = date('n', strtotime($waktu));
	$bulan = $bulan_array[$bl];
	$tahun = date('Y', strtotime($waktu));
	$jam = date('H:i:s', strtotime($waktu));

	//untuk menampilkan hari, tanggal bulan tahun jam
	//return "$hari, $tanggal $bulan $tahun $jam";

	//untuk menampilkan hari, tanggal bulan tahun
	return "$hari, $tanggal $bulan $tahun";
}

function format_tanggal_v2($waktu)
{
	if (empty($waktu)) {
		return '-';
	}
	$hari_array = array(
		'Minggu',
		'Senin',
		'Selasa',
		'Rabu',
		'Kamis',
		'Jumat',
		'Sabtu'
	);
	$hr = date('w', strtotime($waktu));
	$hari = $hari_array[$hr];
	$tanggal = date('j', strtotime($waktu));
	$bulan_array = array(
		1 => 'Januari',
		2 => 'Februari',
		3 => 'Maret',
		4 => 'April',
		5 => 'Mei',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'Agustus',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Desember',
	);
	$bl = date('n', strtotime($waktu));
	$bulan = $bulan_array[$bl];
	$tahun = date('Y', strtotime($waktu));
	$jam = date('H:i:s', strtotime($waktu));

	//untuk menampilkan hari, tanggal bulan tahun jam
	//return "$hari, $tanggal $bulan $tahun $jam";

	//untuk menampilkan hari, tanggal bulan tahun
	return "$tanggal $bulan $tahun";
}

function format_tanggal_jam($waktu)
{
	if (empty($waktu)) {
		return '-';
	}
	$hari_array = array(
		'Minggu',
		'Senin',
		'Selasa',
		'Rabu',
		'Kamis',
		'Jumat',
		'Sabtu'
	);
	$hr = date('w', strtotime($waktu));
	$hari = $hari_array[$hr];
	$tanggal = date('j', strtotime($waktu));
	$bulan_array = array(
		1 => 'Januari',
		2 => 'Februari',
		3 => 'Maret',
		4 => 'April',
		5 => 'Mei',
		6 => 'Juni',
		7 => 'Juli',
		8 => 'Agustus',
		9 => 'September',
		10 => 'Oktober',
		11 => 'November',
		12 => 'Desember',
	);
	$bl = date('n', strtotime($waktu));
	$bulan = $bulan_array[$bl];
	$tahun = date('Y', strtotime($waktu));
	$jam = date('H:i:s', strtotime($waktu));

	//untuk menampilkan hari, tanggal bulan tahun jam
	//return "$hari, $tanggal $bulan $tahun $jam";

	//untuk menampilkan hari, tanggal bulan tahun
	return "$hari, $tanggal $bulan $tahun $jam";
}

function hp($nohp)
{
	// kadang ada penulisan no hp 0811 239 345
	$nohp = str_replace(" ", "", $nohp);
	// kadang ada penulisan no hp (0274) 778787
	$nohp = str_replace("(", "", $nohp);
	// kadang ada penulisan no hp (0274) 778787
	$nohp = str_replace(")", "", $nohp);
	// kadang ada penulisan no hp 0811.239.345
	$nohp = str_replace(".", "", $nohp);

	// cek apakah no hp mengandung karakter + dan 0-9
	if (!preg_match('/[^+0-9]/', trim($nohp))) {
		// cek apakah no hp karakter 1-3 adalah +62
		if (substr(trim($nohp), 0, 3) == '+62') {
			$hp = trim($nohp);
		}
		// cek apakah no hp karakter 1 adalah 0
		elseif (substr(trim($nohp), 0, 1) == '0') {
			$hp = '62' . substr(trim($nohp), 1);
		} else {
			$hp = $nohp;
		}
	}
	return $hp;
}


function generateUniqueTransactionCode($prefix = 'PJM')
{
	$uniqueId = uniqid(); // Mendapatkan ID unik berdasarkan waktu saat ini
	$transactionCode = $prefix . strtoupper(substr(md5($uniqueId), 0, 8)); // Menggabungkan prefix dengan substring unik dari ID

	return $transactionCode;
}

function sendWa1($hp, $text)
{
	// $data = [
	// 	'api_key' => 'FMJ5rNIdm8tn3smAHsjgND3YDFD8T8',
	// 	'sender' => '6281330743343',
	// 	'number' => $hp,
	// 	'message' => $text
	// ];
	// $curl = curl_init();
	// curl_setopt_array($curl, array(
	// 	CURLOPT_URL => 'https://wa.digitalminsajo.sch.id/send-message',
	// 	CURLOPT_RETURNTRANSFER => true,
	// 	CURLOPT_ENCODING => '',
	// 	CURLOPT_MAXREDIRS => 10,
	// 	CURLOPT_TIMEOUT => 0,
	// 	CURLOPT_FOLLOWLOCATION => true,
	// 	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	// 	CURLOPT_CUSTOMREQUEST => 'POST',
	// 	CURLOPT_POSTFIELDS => json_encode($data),
	// 	CURLOPT_HTTPHEADER => array(
	// 		'Content-Type: application/json'
	// 	),
	// ));
	// $response = curl_exec($curl);
	// curl_close($curl);
	// $djson = json_decode($response,true);
	// if($djson["status"]){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://wa-api-v1.wabot.web.id/send-message");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "session=wa1&to=" . $hp . "&text=" . $text . "");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$response = curl_exec($ch);
	curl_close($ch);
	$djson = json_decode($response, true);
	if ($djson['data']['status']) {
		// echo $response;
	} else {
		$data = [
			'api_key' => 'efacb2a793deade57af9fb2fd3f79b91911c5324',
			'sender' => '6281330743343',
			'number' => $hp,
			'message' => $text
		];
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL => 'https://wa.srv2.wapanels.com/send-message',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			)
		);
		$response = curl_exec($curl);
		curl_close($curl);
		$djson2 = json_decode($response, true);
		if ($djson2["status"]) {
			// echo $response;
		} else {
			$data = [
				'api_key' => 'nWlmCfhaK9SoajlsuEzT02riifcAfMeg',
				'sender' => '6281330743343',
				'number' => $hp,
				'message' => $text
			];
			$curl = curl_init();
			curl_setopt_array(
				$curl,
				array(
					CURLOPT_URL => 'https://wa.minsajo.waserv.my.id/send-message',
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => '',
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => json_encode($data),
					CURLOPT_HTTPHEADER => array(
						'Content-Type: application/json'
					),
				)
			);
			$response = curl_exec($curl);
			curl_close($curl);
			$djson3 = json_decode($response, true);
			if ($djson3["status"]) {
				// echo $response;
			} else {
			}
		}
	}
}


function sendWa2($hp, $text)
{
	$data = array(
		'chatId' => $hp . '@c.us',
		'message' => $text,
	);
	$options = array(
		'http' => array(
			'method' => 'POST',
			'content' => json_encode($data),
			'header' => "Content-Type: application/json\r\n" .
				"Accept: application/json\r\n"
		)
	);
	$url = "https://dhsend.com/waInstance1101001027/sendMessage/17f5c57d96922c2e6cdd7190e4aa7918682919ab5024a7c6 ";
	$context = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
}

function kode_transaksi_tabungan()
{
	$prefix = 'TRX'; // Awalan kode transaksi
	$suffix = 'TB'; // Akhiran kode transaksi
	$randomNumber = mt_rand(10000, 99999); // Angka acak antara 10000 dan 99999
	$transactionCode = $prefix . $randomNumber; // Gabungkan awalan, angka acak, dan akhiran
	return $transactionCode;
}

function url_wa()
{
	return 'http://localhost:5001/';
}


function generateApiKey($length = 32)
{
	// Karakter yang diizinkan dalam API key
	$chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charsLength = strlen($chars);
	$apiKey = '';
	// Generate API key secara acak
	for ($i = 0; $i < $length; $i++) {
		$apiKey .= $chars[rand(0, $charsLength - 1)];
	}
	return $apiKey;
}

function generateSecretKey($length = 32)
{
	// Karakter yang diizinkan dalam secret key
	$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
	$charsLength = strlen($chars);
	$secretKey = '';
	// Generate secret key secara acak
	for ($i = 0; $i < $length; $i++) {
		$secretKey .= $chars[rand(0, $charsLength - 1)];
	}
	return $secretKey;
}

if (!function_exists('encrypt_url')) {
	function encrypt_url($data)
	{
		$key = 'rahasia2025'; // ganti dengan kunci rahasia kamu
		$method = 'AES-128-CTR'; // algoritma enkripsi ringan

		$iv_length = openssl_cipher_iv_length($method);
		$iv = random_bytes($iv_length);

		$encrypted = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
		$result = base64_encode($iv . $encrypted);

		// URL-safe base64
		return rtrim(strtr($result, '+/', '-_'), '=');
	}
}

if (!function_exists('decrypt_url')) {
	function decrypt_url($data)
	{
		$key = 'rahasia2025';
		$method = 'AES-128-CTR';

		// Balik ke base64
		$b64 = strtr($data, '-_', '+/');
		$b64 .= str_repeat('=', 3 - (strlen($b64) + 3) % 4); // padding

		$decoded = base64_decode($b64);
		$iv_length = openssl_cipher_iv_length($method);
		$iv = substr($decoded, 0, $iv_length);
		$ciphertext = substr($decoded, $iv_length);

		return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
	}

	function formatBulanTahun($tanggal)
	{
		$bulanIndo = [
			'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		];

		$timestamp = strtotime($tanggal);
		$bulan = $bulanIndo[date('n', $timestamp) - 1];
		$tahun = date('Y', $timestamp);

		return $bulan . ' ' . $tahun;
	}
}

/**
 * =========================================================================
 * HELPER PENGATURAN & TARIF IPL PERUMAHAN
 * =========================================================================
 */
if (!function_exists('ensure_setting_table_exists')) {
	function ensure_setting_table_exists() {
		$ci =& get_instance();
		if (!$ci->db->table_exists('master_setting')) {
			$ci->db->query("CREATE TABLE IF NOT EXISTS `master_setting` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`setting_key` VARCHAR(100) NOT NULL UNIQUE,
				`setting_value` TEXT DEFAULT NULL,
				`keterangan` VARCHAR(255) DEFAULT NULL,
				`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

			$defaults = [
				['setting_key' => 'nominal_ipl', 'setting_value' => '140000', 'keterangan' => 'Nominal Tarif IPL Baru'],
				['setting_key' => 'bulan_berlaku_nominal', 'setting_value' => '2026-11', 'keterangan' => 'Bulan Mulai Berlaku Tarif Baru (YYYY-MM)'],
				['setting_key' => 'nominal_ipl_lama', 'setting_value' => '125000', 'keterangan' => 'Nominal Tarif IPL Lama/Sebelumnya'],
				['setting_key' => 'catatan_tarif', 'setting_value' => 'Penyesuaian nominal iuran IPL mulai November 2026', 'keterangan' => 'Catatan Tambahan Tarif'],
			];
			foreach ($defaults as $d) {
				$ci->db->query("INSERT IGNORE INTO master_setting (setting_key, setting_value, keterangan) VALUES (?, ?, ?)", [$d['setting_key'], $d['setting_value'], $d['keterangan']]);
			}
		}
	}
}

if (!function_exists('get_setting')) {
	function get_setting($key, $default = null) {
		$ci =& get_instance();
		ensure_setting_table_exists();
		$row = $ci->db->get_where('master_setting', ['setting_key' => $key])->row_array();
		if ($row !== null && isset($row['setting_value']) && $row['setting_value'] !== '') {
			return $row['setting_value'];
		}
		return $default;
	}
}

if (!function_exists('set_setting')) {
	function set_setting($key, $value, $keterangan = null) {
		$ci =& get_instance();
		ensure_setting_table_exists();
		$cek = $ci->db->get_where('master_setting', ['setting_key' => $key])->row_array();
		if ($cek) {
			$update_data = ['setting_value' => (string)$value, 'updated_at' => date('Y-m-d H:i:s')];
			if ($keterangan !== null) $update_data['keterangan'] = $keterangan;
			return $ci->db->where('setting_key', $key)->update('master_setting', $update_data);
		} else {
			return $ci->db->insert('master_setting', [
				'setting_key' => $key,
				'setting_value' => (string)$value,
				'keterangan' => $keterangan,
				'updated_at' => date('Y-m-d H:i:s')
			]);
		}
	}
}

if (!function_exists('get_tarif_ipl')) {
	function get_tarif_ipl($tahun_bulan = null) {
		$nominal_baru = (float)get_setting('nominal_ipl', 140000);
		$bulan_berlaku = get_setting('bulan_berlaku_nominal', '2026-11');
		$nominal_lama = (float)get_setting('nominal_ipl_lama', 125000);

		if (empty($tahun_bulan)) {
			return $nominal_baru;
		}

		$ym = substr(trim($tahun_bulan), 0, 7);
		if ($ym < $bulan_berlaku) {
			return $nominal_lama;
		}
		return $nominal_baru;
	}
}

if (!function_exists('terbilang')) {
	function terbilang($angka) {
		$angka = abs((float)$angka);
		$baca = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
		$terbilang = '';

		if ($angka < 12) {
			$terbilang = ' ' . $baca[(int)$angka];
		} elseif ($angka < 20) {
			$terbilang = terbilang($angka - 10) . ' belas';
		} elseif ($angka < 100) {
			$terbilang = terbilang($angka / 10) . ' puluh' . terbilang($angka % 10);
		} elseif ($angka < 200) {
			$terbilang = ' seratus' . terbilang($angka - 100);
		} elseif ($angka < 1000) {
			$terbilang = terbilang($angka / 100) . ' ratus' . terbilang($angka % 100);
		} elseif ($angka < 2000) {
			$terbilang = ' seribu' . terbilang($angka - 1000);
		} elseif ($angka < 1000000) {
			$terbilang = terbilang($angka / 1000) . ' ribu' . terbilang($angka % 1000);
		} elseif ($angka < 1000000000) {
			$terbilang = terbilang($angka / 1000000) . ' juta' . terbilang($angka % 1000000);
		} elseif ($angka < 1000000000000) {
			$terbilang = terbilang($angka / 1000000000) . ' milyar' . terbilang(fmod($angka, 1000000000));
		} elseif ($angka < 1000000000000000) {
			$terbilang = terbilang($angka / 1000000000000) . ' triliun' . terbilang(fmod($angka, 1000000000000));
		}

		return trim($terbilang);
	}
}
