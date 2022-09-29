<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_News extends CI_Model{

			var $table = "news";
			var $table_meta = "news_meta";
			var $table_cat = "news_category";
			var $table_file = "news_file";

			var $column_order = array('id', 'category_id', 'title', 'slug', 'tags', 'created_at'); //set column field database for datatable orderable
			var $column_search = array('id', 'category_id', 'title', 'slug', 'tags', 'created_at'); //set column field database for datatable orderable
			var $order = array('id' => 'asc'); // default order

			function __construct(){
					parent::__construct();
			}

			function list_view($cond=null){
					$this->db->from($this->table);
					$i = 0;
					$rapp = (isset($_GET["rpp"])) ? intval($_GET["rpp"]) : 12;
					$pager = (isset($_GET["page"])) ? intval($_GET["page"]) : 1;
					$page = (!empty($pager)) ? $pager : 1;
					$rpp = (!empty($rapp)) ? $rapp : 12;

					if($page > 1){
							 $spage = (($page - 1) * $rpp);
					}else $spage = 0;
					$this->db->limit($rpp, $spage);

					$this->db->order_by("published_at", "DESC");

					foreach ($this->column_search as $item) // loop column
					{
							if(isset($_GET["search"])){ // if datatable send POST for search
									$itemsy = ($item == "created_at") ? "DATE_FORMAT(created_at,'%M %d, %Y')": $item;
									if($i===0){ // first loop
											$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND.
											$this->db->like($itemsy, $_GET['search']);
									}else $this->db->or_like($itemsy, $_GET['search']);

									if(count($this->column_search) - 1 == $i) //last loop
										$this->db->group_end(); //close bracket
							}
							$i++;
					}
					if(!empty($cond)) $this->db->where($cond);
					$ms = $this->db->select("id, category_id as category, title, excerpt, status, COALESCE(slug,'') as slug, tags, COALESCE(featured_image_url, '') as featured_image_url, COALESCE(published_at, created_at) as published_at, created_at")->get();
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->result() : array();
					return $mdata;
			}

			function lists($cond=null){
					if(!empty($cond)) $this->db->where($cond);
					$ms = $this->db->get($this->table);
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->result() : array();
					return $mdata;
			}

			function list_cat($cond=null){
					if(!empty($cond)) $this->db->where($cond);
					$ms = $this->db->get($this->table_cat);
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->result() : array();
					return $mdata;
			}

			function list_file($cond=null, $select=''){
			    if(!empty($cond)) $this->db->where($cond);
			    if(!empty($select)) $this->db->select($select);
			    $ms = $this->db->get($this->table_file);
			    $mdata = ($ms && $ms->num_rows() > 0) ? $ms->result() : array();
			    return $mdata;
			}

			function detail($cond){

					if(!is_array($cond)) $conds = array("id"=>''.$cond.'');
					else $conds = $cond;
					$ms = $this->db->select("id, category_id as category, title, excerpt, COALESCE(content, '') as content, status, COALESCE(slug,'') as slug, tags, COALESCE(featured_image_url, '') as featured_image_url, COALESCE(published_at, created_at) as published_at, created_at, updated_at, comment_status")->get_where($this->table, $conds);
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->row() : array();
					return $mdata;
			}

			function detail_cat($id){
					$ms = $this->db->get_where($this->table_cat, array("id"=>$id));
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->row() : array();
					return $mdata;
			}

			function detail_vcat($id){
					$ms = $this->db->select("id, title, slug, image_url as image")->get_where($this->table_cat, array("id"=>$id));
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->row() : array();
					return $mdata;
			}

			function total(){
					$ms = $this->db->select("COUNT(id) as total")->get($this->table);
					$total = ($ms && $ms->num_rows() > 0) ? (int)$ms->row()->total : 0;
					return $total;
			}

			function total_published(){
					$ms = $this->db->select("COUNT(id) as total")->get_where($this->table, ["status"=>2]);
					$total = ($ms && $ms->num_rows() > 0) ? (int)$ms->row()->total : 0;
					return $total;
			}

			function list_category(){
					$ms = $this->db->get($this->table_cat);
					$mdata = ($ms && $ms->num_rows() > 0) ? $ms->result() : array();
					return $mdata;
			}

			function insert($data){
					$this->db->insert($this->table, $data);
					$insid = $this->db->insert_id();
					return $insid;
			}

			function update($data, $cond){
					return $this->db->update($this->table, $data, $cond);
			}

			function insert_cat($data){
					$this->db->insert($this->table_cat, $data);
					$insid = $this->db->insert_id();
					return $insid;
			}

			function update_cat($data, $cond){
					return $this->db->update($this->table_cat, $data, $cond);
			}

			function delete($cond){
					return $this->db->delete($this->table, $cond);
			}
			function delete_category($cond){
					return $this->db->delete($this->table_cat, $cond);
			}
}
