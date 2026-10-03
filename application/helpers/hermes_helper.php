<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Hermes Helper
 * Menangani komunikasi Telegram Bot dan Gemini AI
 */

if (!function_exists('hermes_get_config')) {
    function hermes_get_config($key = null, $default = null) {
        $CI =& get_instance();
        if (!$CI->config->item('hermes_telegram_token')) {
            $CI->config->load('hermes', TRUE);
        }
        $val = $CI->config->item($key, 'hermes');
        if ($val === NULL) {
            $val = $CI->config->item($key);
        }
        return ($val !== NULL) ? $val : $default;
    }
}

/**
 * Dapatkan daftar chat_id pelanggan/admin alert
 */
if (!function_exists('hermes_get_subscribers')) {
    function hermes_get_subscribers() {
        $file = hermes_get_config('hermes_subscribers_file', APPPATH . 'cache/hermes_subscribers.json');
        if (!file_exists($file)) {
            return [];
        }
        $data = @json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : [];
    }
}

/**
 * Tambah / Daftarkan Chat ID admin penerima notifikasi
 */
if (!function_exists('hermes_add_subscriber')) {
    function hermes_add_subscriber($chat_id, $extra = []) {
        $file = hermes_get_config('hermes_subscribers_file', APPPATH . 'cache/hermes_subscribers.json');
        $subs = hermes_get_subscribers();
        
        $subs[(string)$chat_id] = array_merge([
            'chat_id' => $chat_id,
            'registered_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $extra);
        
        @file_put_contents($file, json_encode($subs, JSON_PRETTY_PRINT));
        return true;
    }
}

/**
 * Kirim pesan ke Telegram
 */
if (!function_exists('hermes_send_telegram')) {
    function hermes_send_telegram($message, $chat_id = null, $parse_mode = 'HTML') {
        $token = hermes_get_config('hermes_telegram_token');
        if (empty($token)) {
            return ['status' => false, 'message' => 'Token bot Telegram belum diset'];
        }

        $targets = [];
        if ($chat_id) {
            $targets[] = $chat_id;
        } else {
            $subs = hermes_get_subscribers();
            $targets = array_keys($subs);
        }

        if (empty($targets)) {
            return ['status' => false, 'message' => 'Belum ada Chat ID admin yang terdaftar (Kirim /start ke bot di Telegram terlebih dahulu)'];
        }

        $results = [];
        foreach ($targets as $tid) {
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $payload = [
                'chat_id'    => $tid,
                'text'       => $message,
                'parse_mode' => $parse_mode,
                'disable_web_page_preview' => true
            ];

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 8,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            $results[$tid] = $err ? ['status' => false, 'error' => $err] : json_decode($response, true);
        }

        return ['status' => true, 'results' => $results];
    }
}

/**
 * Panggil Gemini AI (Model Free: gemini-1.5-flash)
 */
if (!function_exists('hermes_gemini_ask')) {
    function hermes_gemini_ask($prompt, $system_instruction = '') {
        $api_key = hermes_get_config('hermes_gemini_api_key');
        $model   = hermes_get_config('hermes_gemini_model', 'gemini-1.5-flash');

        if (empty($api_key)) {
            return "Error: Gemini API Key belum diset.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

        $system_prompt = "Anda adalah 'Hermes Agent', asisten DevOps dan AI Code Auditor ahli PHP CodeIgniter 3, MySQL, dan Linux server untuk aplikasi Manajemen Perumahan Perum TSI. Berikan jawaban yang ringkas, teknis, to-the-point, dan berikan solusi kode yang jelas jika ada error.";
        if (!empty($system_instruction)) {
            $system_prompt .= "\n" . $system_instruction;
        }

        $full_prompt = $system_prompt . "\n\nPertanyaan/Data:\n" . $prompt;

        $body = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $full_prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 800
            ]
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return "Koneksi ke Gemini gagal: " . $err;
        }

        $res = json_decode($response, true);
        if (isset($res['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($res['candidates'][0]['content']['parts'][0]['text']);
        }

        if (isset($res['error']['message'])) {
            return "Gemini API Error: " . $res['error']['message'];
        }

        return "Tidak dapat menghasilkan respon dari Gemini AI.";
    }
}

/**
 * Laporkan Bug ke Telegram dengan Analisis Otomatis dari Gemini AI
 */
if (!function_exists('hermes_report_bug')) {
    function hermes_report_bug($error_title, $error_detail, $file = '', $line = '') {
        // Cek throttle agar tidak spam untuk error yang sama
        $hash = md5($error_title . $file . $line);
        $cache_file = APPPATH . 'cache/hermes_alert_' . $hash . '.tmp';
        $throttle = (int) hermes_get_config('hermes_alert_throttle', 600);

        if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $throttle) {
            return false; // Lewati karena baru saja dilaporkan
        }
        @touch($cache_file);

        // Minta Gemini menganalisa error secara singkat
        $prompt = "Terjadi bug pada aplikasi:\nJudul: {$error_title}\nFile: {$file} (Baris {$line})\nDetail Error:\n{$error_detail}\n\nTolong jelaskan penyebabnya dalam 2 kalimat dan langkah perbaikan tepatnya.";
        $ai_analysis = hermes_gemini_ask($prompt);

        // Format pesan Telegram
        $time_now = date('d-m-Y H:i:s');
        $server_host = $_SERVER['HTTP_HOST'] ?? 'manajemenperumtsi.web.id';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '-';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '-';

        $msg  = "🚨 <b>[HERMES BUG ALERT]</b> 🚨\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "⏰ <b>Waktu:</b> <code>{$time_now}</code>\n";
        $msg .= "🌐 <b>Host:</b> <code>{$server_host}</code>\n";
        $msg .= "🔗 <b>URI:</b> <code>" . htmlspecialchars($request_uri) . "</code>\n";
        $msg .= "👤 <b>Client IP:</b> <code>{$ip}</code>\n";
        if (!empty($file)) {
            $msg .= "📁 <b>Lokasi:</b> <code>" . htmlspecialchars(basename($file)) . ":{$line}</code>\n";
        }
        $msg .= "\n💥 <b>Detail Masalah:</b>\n<pre>" . htmlspecialchars(substr($error_detail, 0, 500)) . "</pre>\n";
        $msg .= "\n🤖 <b>Analisa Hermes AI (Gemini Flash):</b>\n";
        $msg .= htmlspecialchars($ai_analysis) . "\n";
        $msg .= "━━━━━━━━━━━━━━━━━━━━\n";
        $msg .= "<i>Kirim /status atau /cek_bug di Telegram untuk info lebih lanjut.</i>";

        return hermes_send_telegram($msg);
    }
}
