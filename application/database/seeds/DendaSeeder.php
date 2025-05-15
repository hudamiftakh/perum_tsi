<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DendaSeeder extends Seeder {

    private $table = 'tb_denda';

    public function run()
    {

        $this->db->truncate($this->table);

        $data = array(
            array(
                'kode_transaksi' => '11X121212',
                'total_denda' => '100'
            ),
            array(
                'kode_transaksi' => '11X121213',
                'total_denda' => '100'
            ),
        );

        $this->db->insert_batch('tb_denda', $data);

        echo PHP_EOL;
    }
}