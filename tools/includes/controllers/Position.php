<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Position extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->load->model("M_Jobs", "job");
		  }

			public function list_get()
	    {
						$resp = 0;
						$message = "";
						$data = [];
						$cari = $this->get("search");
						$carsql = "";
						if(!empty($cari)){
								$cari = strtolower($cari);
								$carsql = "LOWER(name) LIKE '%$cari%'";
						}
						$skill = $this->job->list_position($carsql, "id, name");
						$data = array();
						if(!empty($skill)){
						    $data = $skill;
						}else $message = "job position not found";

					  $this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

			public function category_get()
	    {
						$resp = 0;
						$message = "";
						$data = [];
						$cari = $this->get("search");
						$carsql = "";
						if(!empty($cari)){
								$cari = strtolower($cari);
								$carsql = "LOWER(name) LIKE '%$cari%' AND ";
						}
						$carsql .= "status != 0";
						$cat = $this->job->job_category($carsql, "id, name, COALESCE(icon, 'https://prod-ptf-force-api.s3-ap-southeast-1.amazonaws.com/sales/all_1656403690.svg') as icon");
						$data = array();
						if(!empty($cat)){
						    $data = $cat;
						}else $message = "job category not found";

					  $this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

			public function type_get()
	    {
						$resp = 0;
						$message = "";
						$data = [
							['id'=>'1', 'name'=>'Penuh Waktu'],
							['id'=>'2', 'name'=>'Paruh Waktu'],
							['id'=>'3', 'name'=>'Kontrak'],
							['id'=>'4', 'name'=>'Remote']
						];
						$this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }
}

/* End of file Tracking.php */
