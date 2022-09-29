<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Socmed extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->auth();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;

		  }

			public function info_get()
	    {
					$message = "";
					$data = array();
					$resp = 0;
					$detail = $this->sales->find_cond(['id'=>$this->uid], "COALESCE(facebook, '') as facebook, COALESCE(instagram, '') as instagram, COALESCE(tiktok, '') as tiktok, COALESCE(twitter, '') as twitter");
					if(!empty($detail)){
									$data = $detail;
					}else{
									$resp = REST_Controller::HTTP_NOT_FOUND;
									$message = "user not found";
					}
					set_response($message, $resp, $data);
	    }

			public function manage_post()
	    {
	          $id = $this->uid;
						$posts = $this->post();
						if(!isset($posts["facebook"]) || !isset($posts["instagram"]) || !isset($posts["tiktok"]) || !isset($posts["twitter"])){
								$this->response(["code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"you need at least fb, instagram or tiktok and twitter to fill socmed customer"], REST_Controller::HTTP_OK);
						}
						$fb = $this->post("facebook");
						$ig = $this->post("instagram");
						$tk = $this->post("tiktok");
						$tw = $this->post("twitter");
						$data_ins["facebook"] = (!empty($fb)) ? $fb : null;
	         	$data_ins["instagram"] = (!empty($ig)) ? $ig : null;
	          $data_ins["tiktok"] = (!empty($tk)) ? $tk : null;
	          $data_ins["twitter"] = (!empty($tw)) ? $tw : null;
						$this->sales->update($data_ins, array("id"=>$id));
						$resps = array('code'=>0, 'message'=>'save social media success');
						$this->response($resps, REST_Controller::HTTP_OK);
	    }
}

/* End of file Tracking.php */
