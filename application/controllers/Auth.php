<?php
defined('BASEPATH') or exit('No direct script access allowed');

class auth extends CI_Controller
{

    public function __construct()
    {
        error_reporting(0);
        parent::__construct();
        $this->load->library('session');
    }
    public function index()
    {
        if (!empty($this->session->userdata['username'])) {
            redirect('./dashboard');
        } else {
            $this->login();
        }

    }
    public function login()
    {
        $this->load->view('login');
    }
    public function logout()
    {
        session_destroy();
        redirect('./login');
    }

    public function doLogin()
    {
        try {
            if (isset($_POST['username'])) {
                $checkUser = $this->db->get_where('master_admin',array('username'=>$_POST['username'], 'password'=>md5($_REQUEST['password'])));
                if($checkUser->num_rows()>=1){
					$data = $checkUser->row_array();
					// var_dump($data);
                    $this->db->update('master_admin',array('login_at'=>date('Y-m-d H:i:s')),array('username'=>$_POST['username']));
                    $this->session->set_userdata('username', $data);
                    redirect('./dashboard');
                }else{
                    redirect('./login');
                }
                
            } 
        } catch (Exception $e) {
            echo "Error " . $e;
            redirect('./login');
        }
    }
    public function checkSession()
    {
        if (empty($this->session->userdata['username'])) {
            redirect('./');
        }
    }
}
?>
