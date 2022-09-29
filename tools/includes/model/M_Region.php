<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_Region extends CI_Model{

			protected $table;
			protected $table_city;
			protected $table_dist;
			protected $table_sdist;

			function __construct(){
					parent::__construct();
					$this->table = 'province';
					$this->table_city = 'city';
					$this->table_dist = 'district';
					$this->table_sdist = 'subdistrict';
			}

			function findProvCond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table, $where);
					return $usdata->row();
			}

			function findCityCond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_city, $where);
					return $usdata->row();
			}

			function findDistCond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_dist, $where);
					return $usdata->row();
			}
			function findSubdistCond($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_sdist, $where);
					return $usdata->row();
			}
}
