<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper untuk pengiriman WhatsApp menggunakan API WABOT
 */
if (!function_exists('send_wa')) {
    function send_wa($to, $message) {
        $clean_to = hp($to);
        if (empty($clean_to) || strlen($clean_to) < 9) {
            return ['status' => false, 'message' => 'Nomor WhatsApp tujuan tidak valid atau kosong'];
        }

        $api_key = 'Iql9xs0J2p7UzgdFG4fXfHIrFxBDuiTB';
        $secret_key = 'k48486hpmfpmr2hiybg9zes8bakncg5l';
        $from = '89677658056';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://app.wabot.web.id/api/send-message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'api_key' => $api_key,
                'secret_key' => $secret_key,
                'from' => $from,
                'to' => $clean_to,
                'message' => $message
            ),
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return ['status' => false, 'message' => 'CURL Error: ' . $curl_error];
        }
        
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['status' => false, 'message' => 'Invalid API Response: ' . substr($response, 0, 100)];
    }
}

/**
 * Fungsi untuk mengirim dokumen via WABOT (menggunakan URL file)
 */
if (!function_exists('send_wa_doc')) {
    function send_wa_doc($to, $media_url, $filename = 'Dokumen.pdf', $text = '') {
        $clean_to = hp($to);
        if (empty($clean_to) || strlen($clean_to) < 9) {
            return ['status' => false, 'message' => 'Nomor WhatsApp tujuan tidak valid atau kosong'];
        }

        $api_key = 'Iql9xs0J2p7UzgdFG4fXfHIrFxBDuiTB';
        $secret_key = 'k48486hpmfpmr2hiybg9zes8bakncg5l';
        $from = '89677658056';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://app.wabot.web.id/api/send-document',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_POSTFIELDS => array(
                'api_key' => $api_key,
                'secret_key' => $secret_key,
                'from' => $from,
                'to' => $clean_to,
                'media' => $media_url,
                'filename' => $filename,
                'text' => $text
            ),
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        curl_close($curl);

        if ($curl_error) {
            return ['status' => false, 'message' => 'CURL Error: ' . $curl_error];
        }
        
        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : ['status' => false, 'message' => 'Invalid API Response: ' . substr($response, 0, 100)];
    }
}

/**
 * Format nomor HP agar standar 62
 */
if (!function_exists('hp')) {
    function hp($nomor) {
        $nomor = trim((string)$nomor);
        if ($nomor === '') {
            return '';
        }
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

/**
 * Dapatkan nomor HP warga secara akurat
 */
if (!function_exists('get_no_hp_warga')) {
    function get_no_hp_warga($id_rumah, $alamat_rumah = '') {
        $CI =& get_instance();
        
        // 1. Prioritaskan pencarian dengan id_rumah
        if (!empty($id_rumah) && is_numeric($id_rumah) && (int)$id_rumah > 0) {
            $keluarga = $CI->db->get_where('master_keluarga', ['id_rumah' => (int)$id_rumah])->row_array();
            if (!empty($keluarga['no_hp'])) {
                return $keluarga['no_hp'];
            }
        }
        
        // 2. Jika tidak ditemukan atau id_rumah kosong/0, gunakan pencocokan alamat persis (dengan normalisasi dash dan spasi)
        if (!empty($alamat_rumah)) {
            $normalize = function($addr) {
                $addr = preg_replace('/[‐-‒–—−]/u', '-', $addr);
                $addr = strtolower(trim($addr));
                $addr = preg_replace('/\s+/', ' ', $addr);
                return $addr;
            };
            
            $target = $normalize($alamat_rumah);
            
            // Ambil semua data keluarga yang memiliki nomor HP
            $all_keluarga = $CI->db->query("SELECT no_hp, nomor_rumah FROM master_keluarga WHERE no_hp IS NOT NULL AND no_hp != ''")->result_array();
            foreach ($all_keluarga as $k) {
                $parts = preg_split('/[|,]/', $k['nomor_rumah']);
                foreach ($parts as $part) {
                    if ($normalize($part) === $target) {
                        return $k['no_hp'];
                    }
                }
            }
        }
        
        return '';
    }
}

