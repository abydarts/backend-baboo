<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Certificate extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Sales", "sales");
		        $this->auth();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		  }

			public function list_get(){
	        $message = "";
	        $data = array();
	        $resp = 0;

	        $data = $this->sales->find_cert_bysales($this->uid, "id, title, organizer, certificate_file, '' as file_size, valid_from_date, valid_until_date, certificate");

	        set_response($message, $resp, resource_cert($data));
	    }

	    public function detail_get($id=''){
	        $message = "";
	        $data = array();
	        $resp = 0;
	        if(!empty($id)) $data = resource_cert_single($this->sales->find_cert(['id'=>$id], "id, title, organizer, certificate_file, '' as file_size, valid_from_date, valid_until_date, certificate"));
	        else{
	            $resp = REST_Controller::HTTP_NOT_FOUND;
	            $message = "data certificate not found";
	        }
	        set_response($message, $resp, $data);
	    }

	    public function manage_post($id='')
	    {
						$title = (isset($_FILES['file'])) ? $_POST["title"] : $this->post("title");
						$valid_from = (isset($_FILES['file'])) ? $_POST['valid_from'] : $this->post("valid_from");
						$valid_until = (isset($_FILES['file'])) ? $_POST['valid_until'] : $this->post("valid_until");
						$certificate = (isset($_FILES['file'])) ? $_POST['description'] : $this->post("description");
						$organizer = (isset($_FILES['file'])) ? $_POST['organizer'] : $this->post("organizer");

						if (!empty($title) && !empty($valid_from) && !empty($organizer)) {
								if (isset($_FILES['file'])) {
										if(!empty($_FILES['file']['name'])){
													$certfile = s3_upload($_FILES['file'], "sales/certificate");
													if(!$certfile){
																		$resps = array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'error when uploading file, check file type or destination path');
																		$this->response($resps, REST_Controller::HTTP_OK);
													}else $data_prd["certificate_file"] = $certfile;
										}else{
												$resps = array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'upload file failed, nothing to upload');
												$this->response($resps, REST_Controller::HTTP_OK);
										}
								}
	              $data_prd["title"] = $title;
	              $data_prd["organizer"] = $organizer;
	              $data_prd["sales_force_id"] = $this->uid;
	              $data_prd["valid_from_date"] = $valid_from;
	              $data_prd["valid_until_date"] = (!empty($valid_until)) ? $valid_until : null;
	              $data_prd["certificate"] = $certificate;
	              if(!empty($id)){
	                  $msg="update";
	                  $upd = $this->sales->update_certificate($data_prd, ["id"=>$id]);
	              }else{
	                  $msg="insert";
	                  $data_prd["id"] = get_uuid();
	                  $upd = $this->sales->insert_certificate($data_prd);
	              }
	              if($upd) $resps = array('code'=>0, 'message'=>$msg.' cerficate success');
	              else $resps = array('code'=>REST_Controller::HTTP_INTERNAL_SERVER_ERROR, 'message'=>'something went wrong while '.$msg.' certificate');
	          }else{
	              $resps =  array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'subject, company and cerficate date is required');
	          }
	          $this->response($resps, REST_Controller::HTTP_OK);
	    }

	    public function hapus_delete($id){
	          $resp = REST_Controller::HTTP_NOT_FOUND;
	          $message = "";
	          $data = [];
	          $cek_us = $this->sales->find_cert(["id"=>$id]);
	          if(isset($cek_us->id)){
								    if($this->sales->delete_cert($id)){
			                  $resp = 0;
			                  $message = "delete certificate success";
			              }else{
			                  $resp = REST_Controller::HTTP_BAD_REQUEST;
			                  $message = "something went wrong while delete certificate";
			              }
	          }else $message = "certificate not found";
	          set_response($message, $resp);
	    }

	    public function image_delete($id){
	          $resp = REST_Controller::HTTP_NOT_FOUND;
	          $message = "";
	          $data = [];
	          $cek_us = $this->sales->find_cert(["id"=>$id]);
	          if(isset($cek_us->id)){
									if(!empty($cek_us->certificate_file)){
								    if($this->sales->update_certificate(["certificate_file"=>""], ["id"=>$id])){
			                  $resp = 0;
			                  $message = "delete certificate image success";
			              }else{
			                  $resp = REST_Controller::HTTP_BAD_REQUEST;
			                  $message = "something went wrong while delete certificate image";
			              }
			            }else{
			                  $resp = REST_Controller::HTTP_NOT_FOUND;
			                  $message = "certificate image still empty";
			            }
	          }else $message = "certificate not found";
	          set_response($message, $resp);
	    }
}

/* End of file Tracking.php */
