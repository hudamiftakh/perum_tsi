<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
                        
class Kas_model extends CI_Model 
{
    function __construct()
    {
        parent::__construct();
    }

    public function get_option()
    {
        return $this->db->query(
            'Select code as id, deskripsi as text from master_code where app_code="pengeluaran" and active_status =1 order by order_no asc'
        )->result_array();
        // print_r('haha');exit;
    }            
    
    function get_all(){
         return $this->db->query(
            "Select tp.*, mc.deskripsi jenis_pengeluaran_desc  from t_pengeluaran tp 
            join master_code mc on mc.code=tp.jenis_pengeluaran and mc.app_code='pengeluaran'
            where tp.active_status=1 order by id asc "
        )->result_array();
    }
    
    function get_data_by_id($id){
         return $this->db->query(
            "Select tp.*, mc.deskripsi jenis_pengeluaran_desc from t_pengeluaran tp 
            join master_code mc on mc.code=tp.jenis_pengeluaran and mc.app_code='pengeluaran'
            where tp.active_status=1 and tp.id='".$id."'  order by id asc "
        )->row_array();
    }
    
                        
}


/* End of file M_kas_model.php and path /application/models/M_kas_model.php */
