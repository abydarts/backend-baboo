<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_Education extends CI_Model{

			protected $table;
			protected $table_sid;

			function __construct(){
					parent::__construct();
					$this->table = 'education';
					$this->table_sid = 'sales_force_education';
			}

			function insert($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table, $data);
					return $data["id"];
			}

			function insert_sid($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table_sid, $data);
					return $data["id"];
			}

			function update($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table, $data, $cond);
			}

			function update_sid($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table_sid, $data, $cond);
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

			function findsid_cond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_sid, $where);
					return $usdata->row();
			}

			function delete($cond){
					return $this->db->delete($this->table, $cond);
			}

			function delete_sid($cond){
					return $this->db->delete($this->table_sid, $cond);
			}

			function lists($cond=null, $select='') {
					if(!empty($select)) $this->db->select($select);
					if(!empty($cond)) $this->db->where($cond);
					$this->db->limit(10, 0);
					$usdata = $this->db->get($this->table);
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}

			function list_sid($cond=null, $select='') {
					if(!empty($select)) $this->db->select($select);
					if(!empty($cond)) $this->db->where($cond);
					$this->db->limit(10, 0);
					$this->db->join($this->table, $this->table_sid.".education_id = ".$this->table.".id");
					$usdata = $this->db->get($this->table_sid);
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}
}
