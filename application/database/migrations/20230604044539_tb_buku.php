<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_buku extends CI_Migration {

    public function up()
    {
        $this->dbforge->add_field(array(
            'id_buku' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'id_kategori' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'kode_buku' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'judul_buku' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'pengarang' => array(
                'type' => 'TEXT'
            ),
            'thn_terbit' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'penerbit' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'isbn' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'jumlah_buku' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'lokasi' => array(
                'type' => 'VARCHAR',
                'constraint' => 50
            ),
            'gambar' => array(
                'type' => 'VARCHAR',
                'constraint' => 50
            ),
            'tgl_input' => array(
                'type' => 'DATE'
            ),
            'status_buku' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
        ));
        $this->dbforge->add_key('id_buku', TRUE);
        $this->dbforge->create_table('tb_buku');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_buku');
    }

}
