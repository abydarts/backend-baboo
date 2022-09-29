<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_Notif extends CI_Model{

			var $table = 'notification';

			function __construct(){
					parent::__construct();
			}

			public function total_unread($uid){
					$unread = $this->db->select("COUNT(id) as total")->get_where($this->table, "read_at IS NULL AND sales_id = '$uid'");
					$total_unread = ($unread && $unread->num_rows() > 0) ? (int)$unread->row()->total : 0;
					return $total_unread;
			}

			public function read_unread($uid, $id=""){
					if(empty($id)){
							$unread = $this->db->select("COUNT(id) as total")->get_where($this->table, "read_at IS NULL AND sales_id = '$uid'");
							if($unread && $unread->num_rows() > 0 && $unread->row()->total > 0){
									return $this->db->query("UPDATE `".$this->table."` SET `read_at` = NOW() WHERE `read_at` IS NULL AND sales_id = '$uid'");
							}
					}else{
							return $this->db->update($this->table, ["read_at"=>date("Y-m-d H:i:s")], ["id"=>$id]);
					}
			}

			public function total_notif($uid){
					$unread = $this->db->select("COUNT(id) as total")->get_where($this->table, array("sales_id"=>$uid));
					$total_unread = ($unread && $unread->num_rows() > 0) ? (int)$unread->row()->total : 0;
					return $total_unread;
			}

			function latest_notif($uid, $page = 1, $rpp=10){
				  if($rpp != "all"){
							if($page > 1){
									$page = (($page - 1) * $rpp);
							}else $page = 0;
							$this->db->limit($rpp, $page);
					}
					$this->db->order_by("created_at", "DESC");
					$ms = $this->db->get_where($this->table, array("sales_id"=>$uid));
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->result() : array();
					return $mdata;
			}

			function detail($id, $select=""){
					if(!empty($select)) $this->db->select($select);
					$ms = $this->db->get_where($this->table, array("id"=>$id));
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->row() : array();
					return $mdata;
			}

			function insert_notif($data){
					$this->db->insert($this->table, $data);
					$insid = $this->db->insert_id();
					return $insid;
			}

			function update_notif($data, $cond){
					if(!empty($cond) && !is_array($cond)) $cond = array("id"=>$cond);
					return $this->db->update($this->table, $data, $cond);
			}
}
