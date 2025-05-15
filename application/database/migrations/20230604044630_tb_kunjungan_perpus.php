<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_kunjungan_perpus extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'tanggal_kunjungan' => array(
                'type' => 'DATE'
            ),
            'jam_kunjungan' => array(
                'type' => 'TIME'
            ),
            'kelas' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'nama' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'nis' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'keterangan' => array(
                'type' => 'text'
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_kunjungan_perpus');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_kunjungan_perpus');
    }

}
