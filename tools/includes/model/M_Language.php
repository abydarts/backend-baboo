<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_Language extends CI_Model{

			protected $table;
			protected $table_slang;

			function __construct(){
					parent::__construct();
					$this->table = 'language';
					$this->table_slang = 'sales_force_language';
			}

			function insert($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table, $data);
					return $data["id"];
			}

			function insert_slang($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table_slang, $data);
					return $data["id"];
			}

			function update($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table, $data, $cond);
			}

			function find($id) {
					$usdata = $this->db->get_where($this->table, array("id"=>$id));
					return $usdata->row();
			}

			function find_cond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table, $where);
					return $usdata->row();
			}

			function findslang_cond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_slang, $where);
					return $usdata->row();
			}

			function delete($cond){
					return $this->db->delete($this->table, $cond);
			}

			function delete_slang($cond){
					return $this->db->delete($this->table_slang, $cond);
			}

			function lists($cond=null, $select='') {
					if(!empty($select)) $this->db->select($select);
					if(!empty($cond)) $this->db->where($cond);
					$this->db->limit(10, 0);
					$usdata = $this->db->get($this->table);
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}

			function list_slang($cond=null, $select='') {
					if(!empty($select)) $this->db->select($select);
					if(!empty($cond)) $this->db->where($cond);
					$this->db->limit(10, 0);
					$this->db->join($this->table, $this->table_slang.".lang_id = ".$this->table.".id");
					$usdata = $this->db->get($this->table_slang);
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}
}
