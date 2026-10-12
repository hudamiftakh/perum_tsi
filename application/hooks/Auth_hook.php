<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Hook - Sistem Keamanan & Auto Redirect Halaman Terakhir
 * 
 * Melindungi seluruh controller dari akses tanpa izin (unauthorized access)
 * dan menyimpan URL halaman terakhir yang diakses user sebelum diarahkan ke login.
 * Setelah login berhasil, user otomatis dikembalikan ke halaman terakhir tersebut.
 */
class Auth_hook
{
    /**
     * Daftar route/controller yang boleh diakses publik tanpa login (Whitelist)
     */
    private $public_routes = [
        // Controller Auth
        'auth' => [
            'index',
            'login',
            'dologin',
            'logout'
        ],
        // Controller Home (hanya route index yang me-redirect ke login/dashboard)
        'home' => [
            'index'
        ],
        // Webhook Telegram / Hermes Bot
        'agent' => [
            'webhook',
            'test_notify'
        ],
        // Cron antrean kirim WhatsApp
        'syncwa' => [
            'public'
        ],
        // Rute publik pembayaran warga & download invoice e-kitir
        'dashboard' => [
            'show_form_pembayaran',
            'save_pembayaran',
            'pembayaran_sukses',
            'download_invoice',
            'invoice',
            'show_form_participant'
        ]
    ];

    /**
     * Hook function yang dijalankan pada post_controller_constructor
     */
    public function check_auth()
    {
        $CI =& get_instance();

        // 1. Tambahkan security header untuk mencegah clickjacking & sniffing
        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
        }

        // Ambil nama class controller dan method yang sedang diakses
        $class = strtolower($CI->router->fetch_class());
        $method = strtolower($CI->router->fetch_method());

        // Cek status login dari session CodeIgniter
        $session_user = $CI->session->userdata('username');
        $is_logged_in = !empty($session_user);

        // 2. Cek apakah route yang diakses termasuk whitelist publik
        $is_public = false;
        if (isset($this->public_routes[$class])) {
            $allowed_methods = $this->public_routes[$class];
            if (in_array('*', $allowed_methods) || in_array($method, $allowed_methods)) {
                $is_public = true;
            }
        }

        // 3. Jika user SUDAH login dan mencoba membuka halaman login, lempar langsung ke dashboard
        if ($is_logged_in && $class === 'auth' && in_array($method, ['index', 'login'])) {
            redirect('dashboard');
            exit;
        }

        // 4. Jika route publik, izinkan akses langsung
        if ($is_public) {
            return;
        }

        // 5. Jika user BELUM login dan mengakses halaman yang butuh proteksi:
        if (!$is_logged_in) {
            // Jika request via AJAX, kirim response JSON 401 Unauthorized
            if ($CI->input->is_ajax_request()) {
                header('HTTP/1.1 401 Unauthorized');
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode([
                    'status'   => 'unauthorized',
                    'message'  => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'redirect' => base_url('login')
                ]);
                exit;
            }

            // Tangkap URL lengkap yang sedang diakses user
            $current_url = current_url();
            $query_string = isset($_SERVER['QUERY_STRING']) ? trim($_SERVER['QUERY_STRING']) : '';
            if (!empty($query_string)) {
                $current_url .= '?' . $query_string;
            }

            // Validasi Keamanan Anti-Hack (Cegah Open Redirect & Infinite Loop)
            $base_host = parse_url(base_url(), PHP_URL_HOST);
            $target_host = parse_url($current_url, PHP_URL_HOST);

            // Hanya simpan URL jika internal host dan bukan halaman auth/login/logout
            $is_internal_host = empty($target_host) || strtolower($target_host) === strtolower($base_host);
            $is_not_auth_url = (
                stripos($current_url, 'login') === false &&
                stripos($current_url, 'logout') === false &&
                stripos($current_url, 'doLogin') === false
            );

            if ($is_internal_host && $is_not_auth_url) {
                $CI->session->set_userdata('redirect_url', $current_url);
            }

            // Redirect ke halaman login dengan pesan flashdata jika perlu
            $CI->session->set_flashdata('error', 'Silakan login terlebih dahulu untuk mengakses halaman tersebut.');
            redirect('login');
            exit;
        }
    }
}
