<?php

class SiswaSeeder extends Seeder {

    private $table = 'tb_siswa';

    public function run() {
        $this->db->truncate($this->table);

        $data = array(
            array(
                'id' => 1,
                'nama' => 'Mahardhika Arya Bimnantara',
                'rfid' => '0014037362',
                'foto' => '2100232.png',
                'jk' => 'L',
                'alamat' => 'Jombang Jawa Timur',
                'telepon' => '6285748496135',
                'kelas' => '6-A',
                'nis' => '00237563727',
                'nisn' => '00237563727',
                'tempat_lahir' => 'Jombang',
                'tanggal_lahir' => '2023-05-02',
                'siswa_tahun' => '2022',
                'nama_ayah' => 'SASMITO',
                'nama_ibu' => 'SARMINAH',
                'alamat_ayah' => 'Jombang',
                'alamat_ibu' => 'Jombang',
                'agama' => 'Islam',
                'status' => 'Active',
            ),array(
                'id' => 2,
                'nama' => 'Abdullah Faqih',
                'rfid' => '0014114337',
                'foto' => '1.png',
                'jk' => 'L',
                'alamat' => 'Jombang Jawa Timur',
                'telepon' => '6285748496135',
                'kelas' => '6-A',
                'nis' => '00237563727',
                'nisn' => '00237563727',
                'tempat_lahir' => 'Jombang',
                'tanggal_lahir' => '2023-05-02',
                'siswa_tahun' => '2022',
                'nama_ayah' => 'SASMITO',
                'nama_ibu' => 'SARMINAH',
                'alamat_ayah' => 'Jombang',
                'alamat_ibu' => 'Jombang',
                'agama' => 'Islam',
                'status' => 'Active',
            )
            ,array(
                'id' => 3,
                'nama' => 'Alfieka Maisaroh',
                'rfid' => '0014053635',
                'foto' => '2.png',
                'jk' => 'L',
                'alamat' => 'Jombang Jawa Timur',
                'telepon' => '6285748496135',
                'kelas' => '6-A',
                'nis' => '00237563727',
                'nisn' => '00237563727',
                'tempat_lahir' => 'Jombang',
                'tanggal_lahir' => '2023-05-02',
                'siswa_tahun' => '2022',
                'nama_ayah' => 'SASMITO',
                'nama_ibu' => 'SARMINAH',
                'alamat_ayah' => 'Jombang',
                'alamat_ibu' => 'Jombang',
                'agama' => 'Islam',
                'status' => 'Active',
            )
        );

        $this->db->insert_batch('tb_siswa', $data);

        echo PHP_EOL;
    }
}
