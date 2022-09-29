<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_Principal extends CI_Model{

			protected $table;
			protected $table_gallery;

			function __construct(){
					parent::__construct();
					$this->table = 'principal';
					$this->table_gallery = 'principal_gallery';
			}

			function insert($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table, $data);
					return $data["id"];
			}

			function update($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table, $data, $cond);
			}

			function find($id, $select="") {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table, array("id"=>$id));
					return $usdata->row();
			}

			function find_cond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table, $where);
					return $usdata->row();
			}

			function lists($cond=null, $select='') {
					if(!empty($select)) $this->db->select($select);
					if(!empty($cond)) $this->db->where($cond);
					$usdata = $this->db->get($this->table);
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}

			function galleries($cond=null, $select='') {
					if(!empty($select)) $this->db->select($select);
					if(!empty($cond)) $this->db->where($cond);
					$usdata = $this->db->get($this->table_gallery);
					$gal = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $gal;
			}
}
