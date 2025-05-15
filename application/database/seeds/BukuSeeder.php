<?php

class BukuSeeder extends Seeder {

    private $table = 'tb_buku';

    public function run() {
        $this->db->truncate($this->table);

        $data = array(
            array(
                'id_buku' => 1,
                'id_kategori' => 1,
                'kode_buku' => 'B12212KB',
                'judul_buku' => 'Dasar Pemrograman Java',
                'pengarang' => 'Muhammad Ali M.',
                'thn_terbit' => '2020',
                'penerbit' => 'CV KARYA RASA',
                'isbn' => '1233223223',
                'jumlah_buku' => '100',
                'lokasi' => 'RAK-1',
                'gambar' => '-',
                'tgl_input' => date('Y-m-d'),
                'status_buku' => 'active'
            ),
            array(
                'id_buku' => 4,
                'id_kategori' => 3,
                'kode_buku' => 'B12214KB',
                'judul_buku' => 'Dasar Pemrograman PHP MYSQL',
                'pengarang' => 'Muhammad Ali M.',
                'thn_terbit' => '2020',
                'penerbit' => 'CV UJUNG PENA',
                'isbn' => '1233223223',
                'jumlah_buku' => '100',
                'lokasi' => 'RAK-1',
                'gambar' => '-',
                'tgl_input' => date('Y-m-d'),
                'status_buku' => 'active'
            ),
            array(
                'id_buku' => 2,
                'id_kategori' => 3,
                'kode_buku' => 'B12214KB',
                'judul_buku' => 'TRIK SQL',
                'pengarang' => 'Muhammad Ali M.',
                'thn_terbit' => '2020',
                'penerbit' => 'CV UJUNG PENA',
                'isbn' => '1233223223',
                'jumlah_buku' => '100',
                'lokasi' => 'RAK-1',
                'gambar' => '-',
                'tgl_input' => date('Y-m-d'),
                'status_buku' => 'active'
            ),
            array(
                'id_buku' => 3,
                'id_kategori' => 4,
                'kode_buku' => 'B12215KB',
                'judul_buku' => 'Matematika Dasar',
                'pengarang' => 'Muhammad Ali M.',
                'thn_terbit' => '2020',
                'penerbit' => 'CV UJUNG PENA',
                'isbn' => '1233223223',
                'jumlah_buku' => '100',
                'lokasi' => 'RAK-1',
                'gambar' => '-',
                'tgl_input' => date('Y-m-d'),
                'status_buku' => 'active'
            )
        );

        $this->db->insert_batch('tb_buku', $data);

        echo PHP_EOL;
    }
}
