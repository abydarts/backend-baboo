<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_Sales extends CI_Model{

			protected $table;
			protected $table_login;
			protected $table_experience;
			protected $table_cert;

			function __construct(){
					parent::__construct();
					$this->table = 'sales_force';
					$this->table_login = 'sales_force_login';
					$this->table_experience = 'sales_work_experience';
					$this->table_cert = 'sales_force_certificate';
			}

			function insert($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table, $data);
					return $data["id"];
			}

			function insert_exp($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table_experience, $data);
					return $data["id"];
			}

			function insert_certificate($data){
					if(!isset($data["id"])) $data["id"] = get_uuid();
					$this->db->insert($this->table_cert, $data);
					return $data["id"];
			}

			function insert_login($data){
					if(!isset($data["user_id"])) $data["user_id"] = get_uuid();
					$this->db->insert($this->table_login, $data);
					return $data["user_id"];
			}

			function update($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table, $data, $cond);
			}

			function update_exp($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table_experience, $data, $cond);
			}

			function update_certificate($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table_cert, $data, $cond);
			}

			function update_login($data, $cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->update($this->table_login, $data, $cond);
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

			function find_exp($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_experience, $where);
					return $usdata->row();
			}

			function find_cert($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_cert, $where);
					return $usdata->row();
			}

			function find_login($where, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_login, $where);
					$login = ($usdata && $usdata->num_rows() > 0) ? $usdata->row() : null;
					return $login;
			}

			function find_login_bysales($id, $select='') {
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_login, array("sales_force_id"=>$id));
					$login = ($usdata && $usdata->num_rows() > 0) ? $usdata->row() : null;
					return $login;
			}

			function find_exp_bysales($id, $select='') {
					$this->db->order_by("to_date", "DESC");
					$this->db->order_by("is_verified", "DESC");
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_experience, array("sales_force_id"=>$id));
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}

			function find_cert_bysales($id, $select='') {
					$this->db->order_by("valid_from_date", "DESC");
					if(!empty($select)) $this->db->select($select);
					$usdata = $this->db->get_where($this->table_cert, array("sales_force_id"=>$id));
					$expr = ($usdata && $usdata->num_rows() > 0) ? $usdata->result() : [];
					return $expr;
			}

			function delete_exp($cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->delete($this->table_experience, $cond);
			}

			function delete_cert($cond){
					if(!is_array($cond)){
							$cond = array('id'=>$cond);
					}
					return $this->db->delete($this->table_cert, $cond);
			}

			function null_column($col, $cond, $tableup=''){
					if(empty($tableup)) $tableup = $this->table;
					//check is array cond
					if(!is_array($cond)){
								$cond = array('id'=>$cond);
					}
					//check if is array col
					if(!is_array($col)){
								if (strpos($col, ',') !== false){
										$cols = explode(",", $col);
										foreach($cols as $cos){
												$daupd[$cos] = null;
										}
								}else $daupd[$col] = null;
					}else{
							foreach($col as $cos){
									$daupd[$cos] = null;
							}
					}
					return $this->db->update($tableup, $daupd, $cond);
			}

			function insight($id){
					$ndata = $this->db->select("COUNT(id) as total")->get_where("notifications", "sales_force_id = '$id' AND read_at IS NULL");
					$total_notif = ($ndata && $ndata->num_rows() > 0) ? $ndata->row()->total : 0;
					$data_insight = [
						'notif_unread' => $total_notif
					];
					return $data_insight;
			}
}
