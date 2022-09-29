<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_Jobs extends CI_Model {

    var $table = "";
    var $table_type = "";
    var $table_cat= "";
    var $table_book= "";
    var $table_application = "";
    var $table_position = "";

    public function __construct()
    {
        parent::__construct();
        $this->table = "job";
        $this->table_type = "job_type";
        $this->table_book = "job_bookmark";
        $this->table_cat = "job_category";
        $this->table_application = "job_application";
        $this->table_position = "job_position";
    }

  	// get jobs
  	public function get_jobs() {
  	  return $this->db->get($this->table);
  	}
  	// get jobs
  	public function find($cond, $select='') {
      if(!empty($select)) $this->db->select($select);
      $inf = $this->db->get_where($this->table, $cond);
      $info = ($inf && $inf->num_rows() > 0) ? $inf->row() : null;
      return $info;
  	}
  	// get published jobs
  	public function published($cond=null, $page=1, $rpp=6) {
        $this->db->order_by('date_of_closing', 'ASC');
        if($rpp != "all"){
            $spage = ($page > 1) ? (($page - 1) * $rpp) : 0;
            $this->db->limit($rpp, $spage);
        }
        if(!empty($cond)){
            $this->db->where($cond);
        }
    	  $ssql = $this->db->get_where($this->table, "status = 2 AND date_of_closing >= '".date("Y-m-d")."'");
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->result() : [];
        return $data;
  	}
  	// get published jobs
  	public function publish_cond($cond=null, $select='', $page=1, $rpp=12) {
        $this->db->order_by('date_of_closing', 'ASC');
        if($rpp != "all"){
            $spage = ($page > 1) ? (($page - 1) * $rpp) : 0;
            $this->db->limit($rpp, $spage);
        }
        if(!empty($cond)){
            if(!is_array($cond)){
              if(strpos($cond, '=') !== false){
                  //cond = cond
              }else $cond = ['id'=>$cond];
            }
        }
        if(!empty($select)) $this->db->select($select);
    	  $ssql = $this->db->get_where($this->table, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->result() : [];
        return $data;
  	}

  	public function total_published($cond=null) {
        if(!empty($cond)) $this->db->where($cond);
        $ssql = $this->db->select('COUNT(id) as total')->get_where($this->table, ['status'=>2, "date_of_closing >="=>date("Y-m-d")]);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row()->total : 0;
        return $data;
  	}
  	// get info job position
  	public function info_position($cond) {
        if(!is_array($cond)) $cond = ['id'=>$cond];
    	  $ssql = $this->db->get_where($this->table_position, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row() : null;
        return $data;
  	}
  	// bookmark
    public function total_bookmark($cond) {
        $ssql = $this->db->select('COUNT(id) as total')->get_where($this->table_book, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row()->total : 0;
        return $data;
  	}
  	public function sales_bookmark($cond, $select='') {
        if(!empty($select)) $this->db->select($select);
        $ssql = $this->db->get_where($this->table_book, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row() : null;
        return $data;
  	}
  	public function list_bookmark($cond, $select='', $page=1, $rpp=12) {
        if($rpp != "all"){
            $spage = ($page > 1) ? (($page - 1) * $rpp) : 0;
            $this->db->limit($rpp, $spage);
        }
        $this->db->order_by('created_at', 'desc');
        if(!empty($select)) $this->db->select($select);
        $ssql = $this->db->get_where($this->table_book, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->result() : [];
        return $data;
  	}
  	public function change_bookmark($job_id, $sales_id) {
        $ssql = $this->db->get_where($this->table_book, ['job_id'=>$job_id, 'sales_id'=>$sales_id]);
        if($ssql && $ssql->num_rows() > 0){
            $isbook=false;
            $this->db->delete($this->table_book, ['job_id'=>$job_id, 'sales_id'=>$sales_id]);
        }else{
            $isbook = true;
            $dains = [
              'id'=>get_uuid(),
              'job_id'=>$job_id,
              'sales_id'=>$sales_id
            ];
            $this->db->insert($this->table_book, $dains);
        }

        $data = [
            'id'=>$job_id,
            'is_bookmark'=>$isbook,
            'total_result'=> $this->total_bookmark(['sales_id'=>$sales_id])
        ];
        return $data;
  	}

    // bookmark
    public function total_applied($cond) {
        $ssql = $this->db->select('COUNT(id) as total')->get_where($this->table_application, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row()->total : 0;
        return $data;
  	}

  	public function sales_applied($cond, $select='') {
        if(!empty($select)) $this->db->select($select);
        $ssql = $this->db->get_where($this->table_application, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row() : null;
        return $data;
  	}

  	public function list_applied($cond, $select='', $page=1, $rpp=12) {
        if($rpp != "all"){
            $spage = ($page > 1) ? (($page - 1) * $rpp) : 0;
            $this->db->limit($rpp, $spage);
        }
        $this->db->order_by('created_at', 'desc');
        if(!empty($select)) $this->db->select($select);
        $ssql = $this->db->get_where($this->table_application, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->result() : [];
        return $data;
  	}

    public function insert_application($data){
        // if(!isset($data["id"]) || (isset($data["id"]) && empty($data["id"]))) $data["id"] = get_uuid();
        $this->db->insert($this->table_application, $data);
        return $this->db->insert_id();
    }

    public function update_application($cond, $data){
        return $this->db->update($this->table_application, $data, $cond);
    }

  	// get category job
  	public function single_category($cond, $select='') {
        if(!empty($select)) $this->db->select($select);
        $ssql = $this->db->get_where($this->table_cat, $cond);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->row() : null;
        return $data;
  	}

  	public function job_category($cond=null, $select='') {
        if(!empty($select)) $this->db->select($select);
        if(!empty($cond)) $this->db->where($cond);
        $ssql = $this->db->get($this->table_cat);
        $data = ($ssql && $ssql->num_rows() > 0) ? $ssql->result() : [];
        return $data;
  	}

  	// get all job candidates
  	public function get_jobs_candidates() {
  	  return $this->db->get($this->table_application);
  	}

  	// get all employee applied jobs
  	public function get_employee_jobs_applied($id) {
  	  $sql = 'SELECT * from '.$this->table_application.' where user_id = ?';
  	  $binds = array($id);
  	  $query = $this->db->query($sql, $binds);
  	  return $query;
  	}

    // read job info
  	public function read_job_information($id) {
    		$sql = 'SELECT * from '.$this->table.' where id = ?';
    		$binds = array($id);
    		$query = $this->db->query($sql, $binds);

    		if ($query->num_rows() > 0) {
    			return $query->result();
    		} else {
    			return null;
    		}
  	}

  	// get all jobtype jobs
  	public function read_all_jobs_by_type() {

  		$sql = 'SELECT * from '.$this->table.' where job_type != ? group by job_type';
  		$binds = array('');
  		$query = $this->db->query($sql, $binds);

  		if ($query->num_rows() > 0) {
  			return $query->result();
  		} else {
  			return null;
  		}
  	}

  	// get all job types
  	public function all_job_types() {
  	  $query = $this->db->query("SELECT * from ".$this->table_type);
    	  return $query->result();
  	}

  	// get all jobs by designation
  	 public function read_all_jobs_by_designation() {

  		$sql = 'SELECT * from '.$this->table.' where designation_id != ?';
  		$binds = array('');
  		$query = $this->db->query($sql, $binds);

  		if ($query->num_rows() > 0) {
  			return $query->result();
  		} else {
  			return null;
  		}
  	}

  	// check apply jobs > remove duplicate
  	public function check_apply_job($job_id,$user_id) {

  		$sql = "SELECT * from ".$this->table_application." where job_id = ? and sales_force_id = ? and status != '5'";
  		$binds = array($job_id,$user_id);
  		$query = $this->db->query($sql, $binds);

  		return $query;
  	}

  	// read job type info
  	public function read_job_type_information($id) {
    		$sql = 'SELECT * from '.$this->table_type.' where job_type_id = ?';
    		$binds = array($id);
    		$query = $this->db->query($sql, $binds);

    		if ($query->num_rows() > 0) {
    			return $query->result();
    		} else {
    			return null;
    		}
  	}


  	// Function to add record in table
  	public function add($data){
    		$this->db->insert($this->table, $data);
    		if ($this->db->affected_rows() > 0) {
    			return true;
    		} else {
    			return false;
    		}
  	}

  	// Function to add record in table
  	public function add_resume($data){
    		$this->db->insert($this->table_application, $data);
    		if ($this->db->affected_rows() > 0) {
    			return true;
    		} else {
    			return false;
    		}
  	}

  	// get all job > frontend
  	public function all_jobs() {
  	     $query = $this->db->query("SELECT * from '.$this->table.'");
    	   return $query->result();
  	}
  	// get all job > frontend
  	public function list_position($cond=null, $select='') {
         if(!empty($cond)) $this->db->where($cond);
         if(!empty($select)) $this->db->select($select);
  	     $query = $this->db->get($this->table_position);
    	   $result = ($query && $query->num_rows() > 0) ? $query->result() : [];
         return $result;
  	}
  	// 4 jobs - dashboard >>
  	public function five_latest_jobs() {
  	  $query = $this->db->query("SELECT * from '.$this->table.' limit 5");
    	  return $query->result();
  	}

  	// Function to Delete selected record from table
  	public function delete_record($id){
  		$this->db->where('job_id', $id);
  		$this->db->delete($this->table);

  	}

  	// Function to Delete selected record from table
  	public function delete_application_record($id){
  		$this->db->where('application_id', $id);
  		$this->db->delete($this->table_application);

  	}


  	// get department > designations
  	public function ajax_job_user_information($id) {

  		$sql = 'SELECT * from '.$this->table_application.' where job_id = ?';
  		$binds = array($id);
  		$query = $this->db->query($sql, $binds);

  		if ($query->num_rows() > 0) {
  			return $query->result();
  		} else {
  			return false;
  		}
  	}

  	// Function to update record in table
  	public function update_record($data, $id){
  		$this->db->where('job_id', $id);
  		if( $this->db->update($this->table,$data)) {
  			return true;
  		} else {
  			return false;
  		}
  	}
}
