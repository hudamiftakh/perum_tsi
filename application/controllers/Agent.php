<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Hermes Agent Controller
 * Webhook Telegram, Diagnostic Bug & Asisten AI
 */
class Agent extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['hermes', 'url']);
    }

    /**
     * Webhook Receiver dari Telegram
     * URL: https://manajemenperumtsi.web.id/agent/webhook
     */
    public function webhook() {
        $raw_input = file_get_contents('php://input');
        if (empty($raw_input)) {
            echo json_encode(['status' => false, 'message' => 'Empty payload']);
            return;
        }

        $update = json_decode($raw_input, true);
        if (!$update) {
            echo json_encode(['status' => false, 'message' => 'Invalid JSON']);
            return;
        }

        // Tangkap pesan masuk
        $message = $update['message'] ?? $update['edited_message'] ?? null;
        if (!$message) {
            echo "OK";
            return;
        }

        $chat_id = $message['chat']['id'] ?? null;
        $text    = trim($message['text'] ?? '');
        $sender  = $message['from']['first_name'] ?? 'User';
        $username = $message['from']['username'] ?? '';

        if (!$chat_id) {
            echo "OK";
            return;
        }

        // Auto-register Chat ID pengirim
        hermes_add_subscriber($chat_id, [
            'name' => $sender,
            'username' => $username,
            'type' => $message['chat']['type'] ?? 'private'
        ]);

        // Routing perintah bot
        if (strpos($text, '/start') === 0) {
            $reply  = "👋 <b>Halo, {$sender}!</b>\n\n";
            $reply .= "Saya adalah 🤖 <b>Hermes Agent</b>, asisten DevOps & Bug Monitor untuk sistem <b>Perum TSI</b>.\n\n";
            $reply .= "✅ <b>Chat ID Anda:</b> <code>{$chat_id}</code> telah berhasil terdaftar sebagai penerima notifikasi otomatis.\n\n";
            $reply .= "📋 <b>Daftar Perintah yang Tersedia:</b>\n";
            $reply .= "🔹 /status - Cek kesehatan server & database\n";
            $reply .= "🔹 /cek_bug - Pindai error log & analisa otomatis AI\n";
            $reply .= "🔹 /tanya <i>[pertanyaan]</i> - Konsultasi teknis ke Hermes AI\n";
            $reply .= "🔹 /test - Uji coba pengiriman alert sistem\n";
            $reply .= "🔹 /info - Informasi konfigurasi agent\n\n";
            $reply .= "Setiap kali ada bug / error fatal di website, saya akan langsung mengirimkan laporan dan solusi perbaikannya ke sini!";
            
            hermes_send_telegram($reply, $chat_id);
            echo "OK";
            return;
        }

        if (strpos($text, '/status') === 0) {
            $php_ver = phpversion();
            $mem_usage = round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
            $disk_free = round(@disk_free_space(".") / (1024*1024*1024), 2) . ' GB';
            
            // Cek DB
            $db_status = "🟢 Terhubung";
            $pending_count = 0;
            try {
                if ($this->db->initialize()) {
                    $pending_count = $this->db->where('status_verifikasi', 'pending')->count_all_results('master_pembayaran');
                } else {
                    $db_status = "🔴 Gagal Konek";
                }
            } catch (Exception $e) {
                $db_status = "🔴 Error: " . $e->getMessage();
            }

            $reply  = "🖥️ <b>[STATUS SERVER & SISTEM]</b>\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━\n";
            $reply .= "🌐 <b>Host:</b> <code>" . ($_SERVER['HTTP_HOST'] ?? 'manajemenperumtsi.web.id') . "</code>\n";
            $reply .= "🐘 <b>PHP Version:</b> <code>{$php_ver}</code>\n";
            $reply .= "💽 <b>Free Disk:</b> <code>{$disk_free}</code>\n";
            $reply .= "📊 <b>Memory Usage:</b> <code>{$mem_usage}</code>\n";
            $reply .= "🗄️ <b>Database:</b> {$db_status}\n";
            $reply .= "⏳ <b>Pembayaran Pending:</b> <b>{$pending_count}</b> transaksi\n";
            $reply .= "🤖 <b>AI Model:</b> <code>" . hermes_get_config('hermes_gemini_model') . "</code>\n";
            $reply .= "⏰ <b>Server Time:</b> <code>" . date('Y-m-d H:i:s') . "</code>\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━";

            hermes_send_telegram($reply, $chat_id);
            echo "OK";
            return;
        }

        if (strpos($text, '/cek_bug') === 0) {
            hermes_send_telegram("🔍 <i>Sedang memindai error_log dan menganalisa dengan Gemini Flash... Mohon tunggu sebentar.</i>", $chat_id);
            
            $errors_found = $this->_scan_recent_errors();
            if (empty($errors_found)) {
                $reply = "✅ <b>Alhamdulillah, Tidak Ditemukan Bug Baru!</b>\nSemua log bersih dan tidak ada fatal error tercatat hari ini.";
                hermes_send_telegram($reply, $chat_id);
                echo "OK";
                return;
            }

            $prompt = "Berikut adalah potongan log error terbaru dari server Perum TSI:\n\n" . substr($errors_found, 0, 1500) . "\n\nTolong simpulkan:\n1. Bug apa saja yang terdeteksi?\n2. Seberapa kritis masalah tersebut?\n3. Solusi perbaikan konkritnya?";
            $analysis = hermes_gemini_ask($prompt);

            $reply  = "⚠️ <b>[HASIL ANALISA LOG BUG - HERMES AI]</b>\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━\n";
            $reply .= htmlspecialchars($analysis) . "\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━\n";
            $reply .= "<i>Rincian log diambil dari error_log server dan CI log hari ini.</i>";

            hermes_send_telegram($reply, $chat_id);
            echo "OK";
            return;
        }

        if (strpos($text, '/test') === 0) {
            hermes_report_bug(
                "Uji Coba Notifikasi Hermes Bug Monitor",
                "Ini adalah simulasi bug untuk memastikan integrasi Bot Telegram dan AI Gemini Flash berjalan sempurna.",
                __FILE__,
                __LINE__
            );
            echo "OK";
            return;
        }

        if (strpos($text, '/info') === 0) {
            $subs = hermes_get_subscribers();
            $reply  = "ℹ️ <b>[INFORMASI HERMES AGENT]</b>\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━\n";
            $reply .= "🤖 <b>Bot:</b> @" . hermes_get_config('hermes_bot_username') . "\n";
            $reply .= "🧠 <b>Engine AI:</b> Google Gemini (<code>" . hermes_get_config('hermes_gemini_model') . "</code>)\n";
            $reply .= "👥 <b>Admin Terdaftar:</b> " . count($subs) . " akun\n";
            $reply .= "🔗 <b>Webhook Status:</b> Aktif\n";
            $reply .= "━━━━━━━━━━━━━━━━━━━━";
            hermes_send_telegram($reply, $chat_id);
            echo "OK";
            return;
        }

        // Jika diawali /tanya atau pertanyaan bebas
        $query = $text;
        if (strpos($text, '/tanya') === 0) {
            $query = trim(substr($text, 6));
        }

        if (!empty($query)) {
            hermes_send_telegram("💭 <i>Hermes sedang memproses jawaban...</i>", $chat_id);
            $ai_res = hermes_gemini_ask($query);
            hermes_send_telegram("🤖 <b>Hermes AI:</b>\n\n" . htmlspecialchars($ai_res), $chat_id);
        }

        echo "OK";
    }

    /**
     * Set Webhook ke Telegram API secara otomatis
     * Akses via browser/cURL: https://manajemenperumtsi.web.id/agent/setup
     */
    public function setup() {
        $token = hermes_get_config('hermes_telegram_token');
        $webhook_url = site_url('agent/webhook');

        // Pastikan protokol https
        if (strpos($webhook_url, 'http://') === 0 && !is_local()) {
            $webhook_url = str_replace('http://', 'https://', $webhook_url);
        }

        $api_url = "https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhook_url);
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $err ? false : true,
                'webhook_url' => $webhook_url,
                'telegram_response' => json_decode($res, true) ?: $res,
                'error' => $err
            ], JSON_PRETTY_PRINT));
    }

    /**
     * Endpoint Cron Watcher (Bisa dijalankan via Cron Job cPanel)
     * Contoh command di cPanel: curl -s https://manajemenperumtsi.web.id/agent/cron_watch >/dev/null 2>&1
     */
    public function cron_watch() {
        $errors = $this->_scan_recent_errors();
        if (!empty($errors)) {
            // Minta Hermes kirim alert jika ada error baru
            hermes_report_bug("Cron Watcher: Terdeteksi Error pada Server", $errors, "Server error_log", "-");
            echo "Alert sent: " . substr($errors, 0, 100);
        } else {
            echo "Clean: No errors detected.";
        }
    }

    /**
     * Helper privat membaca baris error terbaru
     */
    private function _scan_recent_errors() {
        $error_text = "";
        
        // 1. Cek error_log di root web
        $root_error_log = FCPATH . 'error_log';
        if (file_exists($root_error_log)) {
            $lines = file($root_error_log);
            if (!empty($lines)) {
                $last_lines = array_slice($lines, -25);
                $error_text .= "=== ROOT error_log ===\n" . implode("", $last_lines) . "\n";
            }
        }

        // 2. Cek application/logs/log-YYYY-MM-DD.php
        $today_log = APPPATH . 'logs/log-' . date('Y-m-d') . '.php';
        if (file_exists($today_log)) {
            $lines = file($today_log);
            if (!empty($lines)) {
                $last_lines = array_slice($lines, -25);
                $filtered = array_filter($last_lines, function($l) {
                    return stripos($l, 'ERROR') !== false || stripos($l, 'Severity') !== false;
                });
                if (!empty($filtered)) {
                    $error_text .= "=== CI LOG TODAY ===\n" . implode("", $filtered) . "\n";
                }
            }
        }

        return trim($error_text);
    }
}
