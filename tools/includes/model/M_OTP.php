<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_OTP extends CI_Model{

			protected $table;

			function __construct(){
					parent::__construct();
					$this->table = 'auth_otp';
			}

			function insert($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table, $data);
					return $data["id"];
			}

			function delete($cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->delete($this->table, $cond);
			}

			function find($id) {
					$usdata = $this->db->get_where($this->table, array("id"=>$id));
					return $usdata->row();
			}

			function findCond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table, $where);
					return $usdata->row();
			}
}
