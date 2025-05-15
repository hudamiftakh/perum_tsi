<?php

class KetegoriSeeder extends Seeder {

    private $table = 'tb_kategori';

    public function run() {
        $this->db->truncate($this->table);

        $data = array(
            array(
                'id_kategori' => 1,
                'nama_kategori' => 'Sains'
            ),
            array(
                'id_kategori' => 2,
                'nama_kategori' => 'Hobby'
            ),
            array(
                'id_kategori' => 3,
                'nama_kategori' => 'Komputer'
            ),
            array(
                'id_kategori' => 4,
                'nama_kategori' => 'Komunikasi'
            ),
            array(
                'id_kategori' => 5,
                'nama_kategori' => 'Hukum'
            ),
            array(
                'id_kategori' => 6,
                'nama_kategori' => 'Agama'
            ),  
            array(
                'id_kategori' => 7,
                'nama_kategori' => 'Populer'
            ),
            array(
                'id_kategori' => 8,
                'nama_kategori' => 'Bahasa'
            ),
        );

        $this->db->insert_batch($this->table, $data);

        echo PHP_EOL;
    }
}
