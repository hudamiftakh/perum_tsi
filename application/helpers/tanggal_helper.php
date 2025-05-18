<?php
function useraAuthData() {
	$ci=& get_instance();
	if(!empty($ci->session->userdata['username'])){
		return  $ci->session->userdata['username'];
	}
}
function format_tanggal($waktu)
{
	if(empty($waktu)){
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
	if(empty($waktu)){
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
	if(empty($waktu)){
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
	curl_setopt($ch, CURLOPT_URL, "https://wa2.digitalminsajo.sch.id/send-message");
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
		curl_setopt_array($curl, array(
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
			curl_setopt_array($curl, array(
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
?>
