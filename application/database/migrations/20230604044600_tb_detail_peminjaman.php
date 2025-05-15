<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_detail_peminjaman extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'kode_transaksi' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'id_buku' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'qty' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'status' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_detail_peminjaman');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_detail_peminjaman');
    }

}
