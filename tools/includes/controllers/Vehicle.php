<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vehicle extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->load->model("M_Jobs", "job");
		        $this->load->model("M_Principal", "principal");
		        $this->load->model("M_Vehicle", "vehicle");
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
						$skill = $this->vehicle->lists($carsql, "id, name");
						$data = array();
						if(!empty($skill)){
						    $data = $skill;
						}else $message = "vehicle not found";

					  $this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

			public function user_get(){
					$this->auth();
					$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;

	        $message = "";
	        $data = array();
	        $resp = 0;

					$sksql = $this->db->order_by("name", "asc")->select("vehicle.id as id, name")->join("sales_force_vehicle", "sales_force_vehicle.vehicle_id = vehicle.id")->get_where("vehicle", ["sales_force_id"=>$uid]);
					$skills = ($sksql && $sksql->num_rows() > 0) ? $sksql->result() : null;
			    if(!empty($skills)){
	            foreach($skills as $dss){
											$data[] = $dss;
	            }
	        }
	        set_response($message, $resp, $data);
	    }

			public function userset_post(){
					$this->auth();
					$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;

	        $skill = $this->post("vehicle");

	        $message = "";
	        $data = array();
	        $resp = 0;

	        $err_empty2 = $err_notdb2 = $errdate = 0;
	        $not_indb = $wrongdate = [];

	        if(!empty($skill)){
	            foreach($skill as $eps){
	                    if(!empty($eps)){
	                        $desql = $this->db->select("id")->get_where("vehicle", ["id"=>$eps]);
	                        if($desql && $desql->num_rows() > 0){
	                              //continue
	                        }else{
	                            $err_notdb2++;
	                            $not_indb2[] = $eps;
	                        }
	                    }else $err_empty2++;
	            }
	        }else $err_empty2++;

	        if(empty($err_empty2) && empty($err_notdb2)){
	                $this->db->delete("sales_force_vehicle", ['sales_force_id'=>$uid]);
	                if(!empty($skill)){
	                    foreach($skill as $eps){
													$data_ins['id'] = get_uuid();
													$data_ins['vehicle_id'] = $eps;
													$data_ins['sales_force_id'] = $uid;
	                    		$this->db->insert("sales_force_vehicle", $data_ins);
	                		}
	                }
	                $message = "successfully save user vehicle";
	        }else{
	                $resp = REST_Controller::HTTP_BAD_REQUEST;
	                $message = "";
	                if(!empty($err_empty2)) $message .= "vehicle must not be empty";
	                else if(!empty($err_notdb2)){
	                    $message = (!empty($message)) ? $message." and " : "";
	                    $message .= "some of vehicle data not in db";
	                }
	        }

	        set_response($message, $resp);
	    }
}

/* End of file Tracking.php */
