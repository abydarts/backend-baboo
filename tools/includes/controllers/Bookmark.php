<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bookmark extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->auth();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->load->model("M_Jobs", "job");
		        $this->load->model("M_Principal", "principal");
		  }

			public function list_get(){
					$message = "";
	        $data = array();
	        $resp = 0;

					$rpp = intval($this->get("rpp"));
					$rpp = (!empty($rpp)) ? $rpp : 12;
					$page = intval($this->get("page"));
					$page = (!empty($page)) ? $page : 1;

					$total_data = $this->job->total_bookmark(['sales_id'=>$this->uid]);
					$total_page = ceil($total_data/$rpp);
					$next_page = ($page < $total_page) ? ($page + 1) : $total_page;
					$prev_page = ($page > 1) ? ($page - 1) : 1;
					$book = $this->job->list_bookmark(['sales_id'=>$this->uid], 'job_id', $page, $rpp);
					if(!empty($book)){
							foreach($book as $bk){
									$jobz = $this->job->find(['id'=>$bk->job_id]);
									$jobz = resource_job_single($jobz, $this->uid);
									$data[] = $jobz;
	        		}

							$meta = [
								'current_page'=>$page,
								'next_page'=>$next_page,
								'prev_page'=>$prev_page,
								'total_page'=>$total_page,
								'total_result'=>$total_data,
								'rpp'=>$rpp
							];
	        }
					$response = array("code"=>$resp, "message"=>$message, "data"=>$data);
					if(!empty($meta)) $response["meta"] = $meta;
					$this->response($response, REST_Controller::HTTP_OK);
	    }

			public function job_put($id=''){
					$message = "";
	        $data = array();
	        $zxc = false;
	        $resp = REST_Controller::HTTP_NOT_FOUND;
	        if(!empty($id)){
								$resp = 0;
								$data = $this->job->change_bookmark($id, $this->uid);
	        }else $message = "id job can't be empty";
					$resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
					$this->response($resps, REST_Controller::HTTP_OK);
	    }
}

/* End of file Tracking.php */
