<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_siswa extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'nama' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'rfid' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'foto' => array(
                'type' => 'TEXT',
                'constraint' => '100'
            ),
            'jk' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'alamat' => array(
                'type' => 'TEXT'
            ),
            'telepon' => array(
                'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'kelas' => array(
                'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'nisn' => array(
                'type' => 'INT',
                'constraint' => '255'
            ),
            'nis' => array(
                'type' => 'INT',
                'constraint' => '255'
            ),
            'tempat_lahir' => array(
                'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'tanggal_lahir' => array(
                'type' => 'DATE'
            ),
            'siswa_tahun' => array(
               'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'nama_ayah' => array(
               'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'nama_ibu' => array(
               'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'alamat_ayah' => array(
               'type' => 'TEXT'
            ),
            'alamat_ibu' => array(
               'type' => 'TEXT'
            ),
            'agama' => array(
               'type' => 'VARCHAR',
                'constraint' => '255'
            ),
            'status' => array(
               'type' => 'VARCHAR',
                'constraint' => '255'
            )

        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_siswa');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_siswa');
    }

}
