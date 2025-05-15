<?php

class AdminSeeder extends Seeder {

    private $table = 'tb_admin';

    public function run() {
        $this->db->truncate($this->table);

        $data = array(
            array(
                'nama' => 'Administrator',
                'username' => 'admin',
                'password' => md5('admin'),
                'level' =>'1'
            ),
            array(
                'nama' => 'Admin MIN 1 JBG',
                'username' => 'admin_absen',
                'password' => md5('admin'),
                'level' =>'2'
            ),
        );

        $this->db->insert_batch('tb_admin', $data);

        echo PHP_EOL;
    }
}
