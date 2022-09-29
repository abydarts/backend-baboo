<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Education extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->load->model("M_Jobs", "job");
		        $this->load->model("M_Principal", "principal");
		        $this->load->model("M_Education", "education");
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
						$skill = $this->education->lists($carsql, "id, name");
						$data = array();
						if(!empty($skill)){
						    $data = $skill;
						}else $message = "education not found";

					  $this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

			public function user_get(){
					$this->auth();
					$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;

	        $message = "";
	        $data = array();
	        $resp = 0;

					$sksql = $this->db->order_by("end_date", "desc")->select("id, education_id as education, institution, COALESCE(subject, '') as subject, start_date as from, end_date as to")->get_where("sales_force_education", ["sales_force_id"=>$uid]);
					$skills = ($sksql && $sksql->num_rows() > 0) ? $sksql->result() : null;
			    if(!empty($skills)){
	            foreach($skills as $dss){
											$edu = $this->education->find_cond(['id'=>$dss->education], 'id, name');
											$dss->education = (isset($edu->id)) ? $edu : null;
											$data[] = $dss;
	            }
	        }
	        set_response($message, $resp, $data);
	    }

			public function detail_get($id=0, $admin=0){
					$this->auth();
					$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
	        $message = "";
	        $data = array();
	        $resp = 0;
					$edwhere = ["id"=>$id];
					if(empty($admin)) $edwhere["sales_force_id"] = $uid;
					$sksql = $this->db->select("id, education_id as education, institution, COALESCE(sales_force_education.subject, '') as subject, start_date as from, end_date as to")->get_where("sales_force_education", $edwhere);
					$skills = ($sksql && $sksql->num_rows() > 0) ? $sksql->row() : null;
			    if(!empty($skills)){
											$dss = $skills;
	            				$edu = $this->education->find_cond(['id'=>$dss->education], 'id, name');
											$dss->education = (isset($edu->id)) ? $edu : null;
											$data = $dss;
	        }else $message = "data education not found";

	        set_response($message, $resp, $data);
	    }

			public function manage_post($id=''){
							$this->auth();
							$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
					    $message = "";
					    $data = array();
					    $resp = 0;
							$epp = (object)$this->post();
							$institute = (isset($epp->institution) && !empty($epp->institution)) ? $epp->institution : "";
							$subject = (isset($epp->subject) && !empty($epp->subject)) ? $epp->subject : "";
							$from_date = (isset($epp->from) && !empty($epp->from)) ? $epp->from : "";
							$to_date = (isset($epp->to)) ? $epp->to : "";
							$education = (isset($epp->education) && !empty($epp->education)) ? $epp->education : "";
							$err_empty = $err_notdb = $errdate = 0;
           		if(!empty($education) && !empty($institute) && !empty($from_date)){
	                    $desql = $this->db->select("id")->get_where("education", ["id"=>$education]);
	                    if($desql && $desql->num_rows() > 0){
	                          //continue
	                          if(!empty($from_date)){
	                              if(!empty($to_date) && $from_date > $to_date){
	                                  $errdate=1;
	                                  $wrongdate = ['from_date'=>$from_date, 'to_date'=>$to_date];
	                              }
	                          }else{
	                              $errdate=1;
	                              $wrongdate = ['from_date'=>$from_date, 'to_date'=>$to_date];
	                          }
	                    }else{
	                        $err_notdb=1;
	                        $not_indb = $education;
	                    }
	            }else $err_empty=1;


	            if(empty($err_empty) && empty($err_notdb) && empty($errdate)){
	                // if(!empty($is_verified)) $to_date = "";
	                $data_ins = [
	                        'education_id'=>$education,
	                        'institution'=>(!empty($institute)) ? $institute : 0,
	                        'start_date'=>(!empty($from_date)) ? $from_date : date("Y-m-d"),
	                        'end_date'=>(!empty($to_date)) ? $to_date : null,
	                        'subject'=>(!empty($subject)) ? $subject : ''
	                ];

									if(empty($id)){
											$data_ins['sales_force_id'] = $uid;
	                		$this->education->insert_sid($data_ins);
									}else{
											$updix = $this->education->findsid_cond(['id'=>$id]);
											if(isset($updix->id)) $this->education->update_sid($data_ins, ["id"=>$id]);
											else{
													set_response("user education not found", REST_Controller::HTTP_NOT_FOUND);
											}
									}
	                $message = "successfully save education";
	            }else{
	                $resp = REST_Controller::HTTP_BAD_REQUEST;
	                $message = "";
									if(!empty($err_empty)) $message .= "name must not be empty";
	                else if(!empty($err_notdb)){
	                    $message = (!empty($message)) ? $message." and " : "";
	                    $message .= "education id not in db";
	                }else if(!empty($errdate)){
	                    $message = (!empty($message)) ? $message." and " : "";
	                    $message .= "data date is not right";
	                }
	            }

	        		set_response($message, $resp);
	    }

			public function hapus_delete($id=''){
	          $resp = REST_Controller::HTTP_NOT_FOUND;
	          $message = "";
	          $data = [];
	          $cek_us = $this->education->findsid_cond(["id"=>$id], "id");
	          if(isset($cek_us->id)){
								    $upd = $this->education->delete_sid(["id"=>$id]);
			              if($upd){
			                  $resp = 0;
			                  $message = "delete education success";
			              }else{
			                  $resp = REST_Controller::HTTP_BAD_REQUEST;
			                  $message = "something went wrong while delete education";
			              }
	          }else $message = "education sales not found";
	          set_response($message, $resp);
	    }
}

/* End of file Tracking.php */
