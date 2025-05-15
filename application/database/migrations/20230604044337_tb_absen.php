<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_absen extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'nis' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'kelas' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'keterangan' => array(
                'type' => 'TEXT'
            ),
            'keterangan_pulang' => array(
                'type' => 'TEXT'
            ),
            'tanggal' => array(
                'type' => 'DATE'
            ),
            'jam_masuk' => array(
                'type' => 'TIME'
            ),
            'jam_pulang' => array(
                'type' => 'TIME'
            ),
            'nama' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'catatan' => array(
                'type' => 'TEXT'
            ),
            'semester' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'tahun_ajaran' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_absen');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_absen');
    }

}
