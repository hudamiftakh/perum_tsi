<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper untuk pengiriman WhatsApp menggunakan API WABOT
 */
if (!function_exists('send_wa')) {
    function send_wa($to, $message) {
        $api_key = 'Iql9xs0J2p7UzgdFG4fXfHIrFxBDuiTB';
        $secret_key = 'k48486hpmfpmr2hiybg9zes8bakncg5l';
        $from = '89677658056';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://app.wabot.web.id/api/send-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'api_key' => $api_key,
                'secret_key' => $secret_key,
                'from' => $from,
                'to' => hp($to), // Memastikan format nomor benar
                'message' => $message
            ),
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
}

/**
 * Fungsi untuk mengirim dokumen via WABOT (menggunakan URL file)
 */
if (!function_exists('send_wa_doc')) {
    function send_wa_doc($to, $media_url, $filename = 'Dokumen.pdf', $text = '') {
        $api_key = 'Iql9xs0J2p7UzgdFG4fXfHIrFxBDuiTB';
        $secret_key = 'k48486hpmfpmr2hiybg9zes8bakncg5l';
        $from = '89677658056';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://app.wabot.web.id/api/send-document',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'api_key' => $api_key,
                'secret_key' => $secret_key,
                'from' => $from,
                'to' => hp($to),
                'media' => $media_url,
                'filename' => $filename,
                'text' => $text
            ),
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
}

/**
 * Format nomor HP agar standar 62
 */
if (!function_exists('hp')) {
    function hp($nomor) {
        $nomor = str_replace([' ', '-', '+', '.'], '', $nomor);
        if (substr($nomor, 0, 1) == '0') {
            $nomor = '62' . substr($nomor, 1);
        } elseif (substr($nomor, 0, 2) == '62') {
            $nomor = $nomor;
        } else {
            $nomor = '62' . $nomor;
        }
        return $nomor;
    }
}
