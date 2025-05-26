<?php
error_reporting(E_ALL & ~E_DEPRECATED); // suppress warning PHP 8+

require_once 'vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig('credential.json');
$client->addScope(Google_Service_Drive::DRIVE_FILE);
$client->setRedirectUri('http://localhost/perum_tsi/get_token.php');
$client->setAccessType('offline');

if (!isset($_GET['code'])) {
    // Redirect to Google for auth
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit;
} else {
    // Setelah user mengizinkan, Google redirect ke sini
    $accessToken = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (!is_array($accessToken) || isset($accessToken['error'])) {
        echo "❌ Gagal mengambil token:<br>";
        print_r($accessToken);
        exit;
    }

    // Simpan token ke file
    file_put_contents('token.json', json_encode($accessToken));
    echo "✅ Token berhasil disimpan ke token.json";
}
