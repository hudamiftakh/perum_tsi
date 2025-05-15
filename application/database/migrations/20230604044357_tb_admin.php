<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_admin extends CI_Migration {

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
            'kelas' => array(
                'type' => 'TEXT'
            ),
            'username' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'password' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'level' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_admin');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_admin');
    }

}
