<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_denda extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'kode_transaksi' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
            ),
            'total_denda' => array(
                'type' => 'INTEGER',
                'constraint' => '255',
            ),
            'id_siswa' => array(
                'type' => 'INTEGER',
                'constraint' => '255',
            ),
            'tanggal' => array(
                'type' => 'TIMESTAMP'
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_denda');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_denda');
    }

}
