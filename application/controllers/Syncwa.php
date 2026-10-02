<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Syncwa extends CI_Controller
{
    public function __construct()
    {
        error_reporting(0);
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->library('pagination');
        $this->load->model('M_Datatables');
        if (function_exists('ensure_log_tables_exist')) {
            ensure_log_tables_exist();
        }
    }

    public function public()
    {
        // Cek apakah data dengan ID tersebut ada
        $cek = $this->db->get_where('master_pembayaran', ['wa_send' => 'queue'])->row();
        if (!$cek) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan']);
            return;
        }
        $id = $cek->id;
        // Lakukan update status
        $statusBaru = $aksi === 'success' ? 'success' : 'queue';
        $this->db->where('id', $id);
        $update = $this->db->update('master_pembayaran', ['wa_send' => $statusBaru]);

        if ($update) {
            // Ambil data pembayaran & user
            $pembayaran = $this->db->get_where('master_pembayaran', ['id' => $id])->row_array();
            $user = $this->db->get_where('master_users', ['id' => $pembayaran['user_id']])->row_array();
            $rumah = $this->db->get_where('master_rumah', ['id' => $user['id_rumah']])->row_array();

            $this->load->helper('wa');
            $no_hp = get_no_hp_warga($rumah['id'] ?? 0, $rumah['alamat'] ?? '');

            $nama = $user['nama'] ?? '';
            $alamat = $rumah['alamat'] ?? '';
            $bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));

            // Buat link pembayaran terenkripsi
            $link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));

            $text = "✅ Pembayaran IPL Telah Divalidasi\n\nAssalamu'alaikum/Salam sejahtera Bapak/Ibu *$nama*,\n\nPembayaran IPL bulan *$bulan* sebesar *Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "* telah *divalidasi* oleh pengurus. ✅\n💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "\n📄 Bukti: Sudah divalidasi\n🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "\n📑 Kitir Pembayaran: $link\n\nSilakan unduh e-kitir di atas sebagai bukti pembayaran resmi Bapak/Ibu.\n\nTerima kasih atas kontribusi Bapak/Ibu dalam operasional dan pemeliharaan lingkungan kita bersama.\n\nHormat kami,\nPengurus Paguyuban TSI\nPerumahan Taman Sukodono Indah\n_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";
            $this->load->helper('wa');
            $res = send_wa($no_hp, $text);

            if (isset($res['status']) && ($res['status'] === true || $res['status'] == '1')) {
                // Update status wa_send jadi success
                $this->db->where('id', $id);
                $this->db->update('master_pembayaran', ['wa_send' => 'success']);

                // Sinkronkan ke log_verifikasi jika tabel ada
                if ($this->db->table_exists('log_verifikasi')) {
                    $this->db->where('pembayaran_id', $id)->update('log_verifikasi', [
                        'wa_status' => 'success',
                        'wa_response' => json_encode($res),
                    ]);
                }
                echo json_encode(['status' => 'success', 'message' => 'Notifikasi WA berhasil dikirim']);
            } else {
                // Jika gagal, catat error ke log_verifikasi
                if ($this->db->table_exists('log_verifikasi')) {
                    $this->db->where('pembayaran_id', $id)->update('log_verifikasi', [
                        'wa_status' => 'failed',
                        'wa_error' => $res['message'] ?? 'Error API',
                        'wa_response' => json_encode($res),
                    ]);
                }
                echo json_encode(['status' => 'error', 'message' => 'Gagal kirim WA: ' . ($res['message'] ?? 'Error API')]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memproses data pembayaran']);
        }
    }
}
