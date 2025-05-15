<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tb_peminjaman_buku extends CI_Migration {

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
            'id_siswa' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'nis' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'nama' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'kelas' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'jml_buku' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'tgl_pinjam' => array(
                'type' => 'DATE'
            ),
            'tgl_kembali' => array(
                'type' => 'DATE'
            ),
            'durasi' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'denda' => array(
                'type' => 'INT',
                'constraint' => '100'
            ),
            'status' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('tb_peminjaman_buku');
    }

    public function down()
    {
        $this->dbforge->drop_table('tb_peminjaman_buku');
    }

}
