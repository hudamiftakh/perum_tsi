<?php

class Migration_tb_pengembalian extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'kode_transaksi' => array(
                'type' => 'TEXT'
            ),
            'nis' => array(
                'type' => 'INT'
            ),
            'nama' => array(
                'type' => 'TEXT'
            ),
            'denda' => array(
                'type' => 'INT'
            ),
            'tgl_pengembalian' => array(
                'type' => 'DATETIME'
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_pengembalian');
    }

    public function down() {
        $this->dbforge->drop_table('tb_pengembalian');
    }

}