<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Keywords extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->load->model("M_Keyword", "keyword");
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
						$keyword = $this->keyword->lists($carsql, "id, name");
						$data = array();
						if(!empty($keyword)){
						    $data = $keyword;
						}else $message = "keyword not found";

					  $this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

	    public function profile_post(){

					$this->auth();
					$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;

	        $description = $this->post("about_me");
	        $keywords = $this->post("keywords");

	        $message = "";
	        $data = array();
	        $resp = 0;

	        $err_empty = $err_notdb = $errmore = 0;
	        $not_indb = $wrongdate = [];

	        if(!empty($keywords)){
							if(count($keywords) > 3) $errmore = 1;
	            foreach($keywords as $epr){
	                    if(!empty($epr)){
	                        $desql = $this->db->select("id")->get_where("keyword", ["id"=>(string)$epr]);
	                        if($desql && $desql->num_rows() > 0){
	                              //continue
	                        }else{
	                            $err_notdb++;
	                            $not_indb[] = $epr;
	                        }
	                    }
	            }
	        }

	        if(!empty($description) && empty($errmore) && empty($err_notdb)){
	                $keyws = (is_array($keywords) && !empty($keywords)) ? implode(",", $keywords) : "";
	                $data_upd["info_description"] = $description;
	                $data_upd['keywords'] = $keyws;
	                $this->sales->update($data_upd, ["id"=>$uid]);
	                $message = "successfully save descripton and keyword";
	        }else{
	                $resp = REST_Controller::HTTP_BAD_REQUEST;
	                $message = "";
									if(empty($description)) $message = "description can't be empty";
									if(!empty($errmore)){
											$message = (!empty($message)) ? $message." and " : "";
											$message = "only allowed 3 keywords";
									}
	                if(!empty($err_notdb)){
	                    $message = (!empty($message)) ? $message." and " : "";
	                    $message .= "some of keyword data not in db";
	                }
	        }

	        set_response($message, $resp);
	    }
}

/* End of file Tracking.php */
