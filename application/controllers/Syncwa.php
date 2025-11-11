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

            // Ambil data keluarga berdasarkan id_rumah
            // Ambil data keluarga.
            // Jika ada id_rumah (tidak nol), cari berdasarkan id_rumah.
            // Jika id_rumah nol atau kosong, cari berdasarkan kecocokan alamat di kolom nomor_rumah
            // (mengatasi format "Ruha 6| Ruha 7| TSI Blok I-1| TSI Blok II-8").
            $keluarga = [];
            if (isset($rumah['id']) && is_numeric($rumah['id']) && (int) $rumah['id'] > 0) {
                // Gunakan id_rumah hanya jika bukan 0
                $keluarga = $this->db->get_where('master_keluarga', ['id_rumah' => (int) $rumah['id']])->row_array();
            } else {
                $alamat = trim($rumah['alamat'] ?? '');
                if ($alamat !== '') {
                    $al = $this->db->escape_like_str($alamat);
                    $this->db->group_start();
                    // cari apakah alamat muncul di dalam string nomor_rumah (bagian manapun)
                    $this->db->like('nomor_rumah', $al);
                    // juga cek variasi dengan pipe di sisi kiri/kanan untuk memastikan pencarian bagian
                    $this->db->or_like('nomor_rumah', '|' . $al);
                    $this->db->or_like('nomor_rumah', $al . '|');
                    $this->db->group_end();
                    $keluarga = $this->db->get('master_keluarga')->row_array();
                }
            }

            $nama = $user['nama'] ?? '';
            $alamat = $rumah['alamat'] ?? '';
            $no_hp = $keluarga['no_hp'] ?? '';

            // Validasi nomor HP, jika kosong ambil dari master_keluarga lain yang cocok
            if (empty($no_hp)) {
                $keluarga_alt = $this->db->query("SELECT no_hp FROM master_keluarga WHERE nomor_rumah LIKE '%" . $this->db->escape_like_str($rumah['alamat']) . "%' AND no_hp IS NOT NULL AND no_hp != '' LIMIT 1")->row_array();
                $no_hp = $keluarga_alt['no_hp'] ?? '';
            }
            $bulan = date('F Y', strtotime($pembayaran['bulan_mulai']));

            // Buat link pembayaran terenkripsi
            $link = base_url('download_invoice/' . encrypt_url($pembayaran['id']));

            $text = "📥 Konfirmasi Pembayaran IPL

Assalamu’alaikum/Salam sejahtera Bapak/Ibu *$nama*,

Terima kasih kami ucapkan atas pembayaran IPL bulan *$bulan* sebesar **Rp" . number_format($pembayaran['jumlah_bayar'], 0, ',', '.') . "** yang telah kami terima. 🙏
💳 Tanggal Bayar: " . date('d-m-Y', strtotime($pembayaran['tanggal_bayar'])) . "
📄 Bukti: Sudah diterima
🔄 Metode Pembayaran: " . ($pembayaran['pembayaran_via'] === 'koordinator' ? 'Koordinator' : 'Transfer') . "
📑 Kitir Pembayaran: $link

Pembayaran Bapak/Ibu sangat membantu dalam operasional dan pemeliharaan lingkungan kita bersama.

Jika ada pertanyaan atau masukan, silakan hubungi kami kapan saja.

Hormat kami,
Pengurus Paguyuban TSI
Perumahan Taman Sukodono Indah
_⚠️ Pesan ini dikirim otomatis melalui sistem aplikasi paguyuban. Mohon tidak membalas pesan ini._";
            // Kirim notifikasi via POST ke WA Gateway jika nomor HP valid
            $wa_url = 'https://wa2.digitalminsajo.sch.id/send-message';
            $post_data = [
                'session' => 'wa2',
                'to' => hp($no_hp),
                'text' => $text
            ];

            // Kirim POST (gunakan CURL) dengan error handling
            try {
                $ch = curl_init($wa_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
                curl_setopt($ch, CURLOPT_POST, true);
                $response = curl_exec($ch);
                $curl_error = curl_error($ch);
                curl_close($ch);

                if ($response === false || !empty($curl_error)) {
                    // Jika gagal kirim WA, rollback update status
                    $this->db->where('id', $id);
                    $this->db->update('master_pembayaran', ['wa_send' => 'success']); // kembalikan status semula
                    echo json_encode(['status' => 'error', 'message' => 'Gagal kirim notifikasi WA, status tidak diupdate']);
                    return;
                }

                echo json_encode(['status' => 'success', 'message' => 'Data berhasil diverifikasi']);
            } catch (Exception $e) {
                // Rollback update status jika error
                $this->db->where('id', $id);
                $this->db->update('master_pembayaran', ['wa_send' => 'success']);
                echo json_encode(['status' => 'error', 'message' => 'Gagal kirim notifikasi WA: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data']);
        }
    }
}
