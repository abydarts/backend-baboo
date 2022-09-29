<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Resume extends BS_Controller
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
					$detail = $this->sales->find_cond(['id'=>$this->uid], "'' as resume_size, COALESCE(resume, '') as resume, COALESCE(application, '') as application");
					if(!empty($detail)){
									if(!empty($detail->resume)){
											$headers = get_headers($detail->resume, true);
											$detail->resume_size = (int)$headers['Content-Length'];
									}else $detail->resume_size = 0;
									$data = $detail;
					}else{
									$resp = REST_Controller::HTTP_NOT_FOUND;
									$message = "user not found";
					}
					set_response($message, $resp, $data);
	    }

			public function sales_post()
	    {
	          $id = $this->uid;
						if (isset($_FILES['file'])) {
								if(!empty($_FILES['file']['name'])){
	                    $resume = s3_upload($_FILES['file'], "sales/resume");
	                    if(!$resume){
	                              $resps = array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'error when uploading file, check file type or destination path');
	                    }else{
	                              $data_prd["resume"] = $resume;
																$msg = '';
	                              if(isset($_POST['application'])){
																		$data_prd["application"] = (!empty($this->post("application"))) ? $this->post("application") : null;
																		$msg = 'and application ';
																}
	                              $this->sales->update($data_prd, array("id"=>$id));
	                              $resps = array('code'=>0, 'message'=>'save resume '.$msg.'success', 'data'=>['resume'=>$resume]);
	                    }
								}else{
			              $resps =  array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'upload file failed, nothing to upload');
			          }
	          }else if(isset($_POST['application']) && !empty($_POST['application'])){
	              		$data_prd["application"] = (!empty($this->post("application"))) ? $this->post("application") : null;
										$this->sales->update($data_prd, array("id"=>$id));
										$resps = array('code'=>0, 'message'=>'save application success');
						}else{
								$resps =  array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'update resume or application failed, nothing to update');
						}
	          $this->response($resps, REST_Controller::HTTP_OK);
	    }

			public function hapus_delete(){
	          $resp = REST_Controller::HTTP_NOT_FOUND;
	          $message = "";
	          $data = [];
	          $cek_us = $this->sales->find_cond(["id"=>$this->uid], "id, resume");
	          if(isset($cek_us->id)){
								if(!empty($cek_us->resume)){
			              $upd = $this->sales->null_column('resume', ["id"=>$this->uid]);
			              if($upd){
			                  $resp = 0;
			                  $message = "delete resume success";
			              }else{
			                  $resp = REST_Controller::HTTP_BAD_REQUEST;
			                  $message = "something went wrong while update avatar";
			              }
	              }else{
	                  $resp = REST_Controller::HTTP_NOT_FOUND;
	                  $message = "resume not found";
	              }
	          }else $message = "sales not found";
	          set_response($message, $resp);
	    }
}

/* End of file Tracking.php */
