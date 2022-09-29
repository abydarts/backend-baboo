<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Experience extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->load->model("M_Sales", "sales");
		        $this->load->model("M_Jobs", "job");
		        $this->load->model("M_Principal", "principal");
		        $this->auth();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;

		  }

			public function list_get()
	    {
					$message = "";
					$data = array();
					$resp = 0;
					$data = $this->sales->find_exp_bysales($this->uid);

					set_response($message, $resp, resource_experience($data));
	    }

			public function detail_get($id='')
	    {
					$message = "";
					$data = array();
					$resp = 0;
					if(!empty($id)){
							$detail = $this->sales->find_exp(['id'=>$id], "id, company_name, COALESCE(description, '') as description, from_date, to_date, job_position_id, COALESCE(letter_reference, '') as letter_reference, '0' as file_size");
							if(!empty($detail)){
									$data = resource_expr_single($detail);
							}else{
									$resp = REST_Controller::HTTP_NOT_FOUND;
									$message = "experience not found";
							}
					}else{
							$resp = REST_Controller::HTTP_BAD_REQUEST;
							$message = "id is required";
					}

					set_response($message, $resp, $data);
	    }

			public function manage_post($id=''){
					    $message = "";
					    $data = array();
					    $resp = 0;
							$epp = (object)$this->post();
							$designation = (isset($epp->designation_id) && !empty($epp->designation_id)) ? $epp->designation_id : "";
							$company_name = (isset($epp->company_name) && !empty($epp->company_name)) ? $epp->company_name : "";
							$from_date = (isset($epp->from) && !empty($epp->from)) ? $epp->from : "";
							$to_date = (isset($epp->to)) ? $epp->to : "";
							$description = (isset($epp->description) && !empty($epp->description)) ? $epp->description : "";
							$is_verified = (isset($epp->current_experience) && !$epp->current_experience) ? 0 : 1;
							$err_empty = $err_notdb = $errdate = 0;
           		if(!empty($designation) && !empty($from_date)){
	                    $desql = $this->db->select("id")->get_where("job_position", ["id"=>$designation]);
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
	                        $not_indb = $designation;
	                    }
	            }else $err_empty=1;


	            if(empty($err_empty) && empty($err_notdb) && empty($errdate)){
	                // if(!empty($is_verified)) $to_date = "";
	                $data_ins = [
	                        'job_position_id'=>$designation,
	                        'company_name'=>(!empty($company_name)) ? $company_name : 0,
	                        'from_date'=>(!empty($from_date)) ? $from_date : date("Y-m-d"),
	                        'to_date'=>(!empty($to_date)) ? $to_date : null,
	                        'description'=>(!empty($description)) ? $description : ''
	                ];

									if (isset($_FILES['file'])) {
											if(!empty($_FILES['file']['name'])){
														$certfile = s3_upload($_FILES['file'], "sales/reference");
														if(!$certfile){
																			$resps = array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'error when uploading file, check file type or destination path');
																			$this->response($resps, REST_Controller::HTTP_OK);
														}else $data_ins["letter_reference"] = $certfile;
											}else{
													$resps = array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'upload file failed, nothing to upload');
													$this->response($resps, REST_Controller::HTTP_OK);
											}
									}

									if(empty($id)){
											$data_ins['is_verified'] = 0;
											$data_ins['sales_force_id'] = $this->uid;
	                		$this->sales->insert_exp($data_ins);
									}else{
											$updix = $this->sales->find_exp(['id'=>$id]);
											if(isset($updix->id)) $this->sales->update_exp($data_ins, ["id"=>$id]);
											else{
													set_response("experience not found", REST_Controller::HTTP_NOT_FOUND);
											}
									}
	                $message = "successfully save experience";
	            }else{
	                $resp = REST_Controller::HTTP_BAD_REQUEST;
	                $message = "";
									if(!empty($err_empty)) $message .= "name must not be empty";
	                else if(!empty($err_notdb)){
	                    $message = (!empty($message)) ? $message." and " : "";
	                    $message .= "some of designation id not in db";
	                }else if(!empty($errdate)){
	                    $message = (!empty($message)) ? $message." and " : "";
	                    $message .= "some of date data not right";
	                }
	            }

	        		set_response($message, $resp);
	    }

			public function ref_delete($id){
	          $resp = REST_Controller::HTTP_NOT_FOUND;
	          $message = "";
	          $data = [];
	          $cek_us = $this->sales->find_exp(["id"=>$id]);
	          if(isset($cek_us->id)){
									if(!empty($cek_us->letter_reference)){
								    if($this->sales->update_exp(["letter_reference"=>""], ["id"=>$id])){
			                  $resp = 0;
			                  $message = "delete letter reference success";
			              }else{
			                  $resp = REST_Controller::HTTP_BAD_REQUEST;
			                  $message = "something went wrong while delete letter reference";
			              }
			            }else{
			                  $resp = REST_Controller::HTTP_NOT_FOUND;
			                  $message = "letter reference still empty";
			            }
	          }else $message = "experience not found";
	          set_response($message, $resp);
	    }

			public function hapus_delete($id){
	          $resp = REST_Controller::HTTP_NOT_FOUND;
	          $message = "";
	          $data = [];
	          $cek_us = $this->sales->find_exp(["id"=>$id]);
	          if(isset($cek_us->id)){
								    if($this->sales->delete_exp($id)){
			                  $resp = 0;
			                  $message = "delete work experience success";
			              }else{
			                  $resp = REST_Controller::HTTP_BAD_REQUEST;
			                  $message = "something went wrong while delete experience";
			              }
	          }else $message = "experience not found";
	          set_response($message, $resp);
	    }
}

/* End of file Tracking.php */
