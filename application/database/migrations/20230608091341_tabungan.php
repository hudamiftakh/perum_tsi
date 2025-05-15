<?php

class Migration_tabungan extends CI_Migration {

    public function up() {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            ,
            'tgl_transaksi' => array(
                'type' => 'DATE'
            ),
            'nis' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'nama' => array(
                'type' => 'VARCHAR',
                'constraint' => 11
            ),
            'kelas' => array(
                'type' => 'VARCHAR',
                'constraint' => 11
            ),
            'debit' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'kredit' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'saldo_terakhir' => array(
                'type' => 'INT',
                 'constraint' => 30
            ),
            'tgl_record' => array(
                'type' => 'TIMESTAMP'
            ),
            'kode_transaksi' => array(
                'type' => 'TEXT'
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_tabungan');
    }

    public function down() {
        $this->dbforge->drop_table('tb_tabungan');
    }

}