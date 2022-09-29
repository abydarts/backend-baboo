<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Apply extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
        $this->load->model("M_Jobs", "job");
        $this->load->model("M_Principal", "principal");
    }

    public function list_get(){
        $message = "";
        $data = array();
        $resp = 0;

        $stat = $this->get("status");
        $rpp = intval($this->get("rpp"));
        $rpp = (!empty($rpp)) ? $rpp : 12;
        $page = intval($this->get("page"));
        $page = (!empty($page)) ? $page : 1;
        $wheresql = ['sales_force_id'=>$this->uid];
        if(!empty($stat) && $stat != "all"){
            if(intval($stat) > 0) $wheresql['status'] = (string)$stat;
            else{
                switch($stat){
                    case "applied" :
                    case "submit" : $wheresql['status'] = "1";break;
                    case "approved" : $wheresql['status'] = "4";break;
                    case "accepted" : $wheresql['status'] = "6";break;
                    case "rejected" : $wheresql['status'] = "7";break;
                    case "done" : $wheresql = "sales_force_id = '".$this->uid."' AND (status = '6' OR status = '7')";break;
                    default : $wheresql['status'] = "1";break;
                }
            }
        }else{
            $wheresql = "sales_force_id = '".$this->uid."' AND (status = '1' OR status = '6' OR status = '7')";
        }
        $total_applied = $this->job->total_applied("sales_force_id = '".$this->uid."' AND (status = '1' OR status = '6' OR status = '7')");
        $total_done = $this->job->total_applied("sales_force_id = '".$this->uid."' AND (status = '6' OR status = '7')");
        $total_data = $this->job->total_applied($wheresql);
        $total_page = ceil($total_data/$rpp);
        $next_page = ($page < $total_page) ? ($page + 1) : $total_page;
        $prev_page = ($page > 1) ? ($page - 1) : 1;

        // $message = $wheresql;
        $apply = $this->job->list_applied($wheresql, 'id, job_id, status, job_resume as resume, job_letter as message, remarks, interview_expired, created_at, updated_at', $page, $rpp);
        if(!empty($apply)){
            foreach($apply as $ap){
                $jobz = $this->job->find(['id'=>$ap->job_id]);
                unset($ap->job_id);
                $jobz = resource_job_single($jobz, $this->uid, $ap);
                $data[] = $jobz;
            }

            $meta = [
              'current_page'=>$page,
              'next_page'=>$next_page,
              'prev_page'=>$prev_page,
              'total_page'=>$total_page,
              'total_result'=>$total_data,
              'rpp'=>$rpp,
              'total_applied'=>$total_applied,
              'total_done'=>$total_done
            ];
        }
        $response = array("code"=>$resp, "message"=>$message, "data"=>$data);
        if(!empty($meta)) $response["meta"] = $meta;
        $this->response($response, REST_Controller::HTTP_OK);
    }

    public function detail_get($id=''){
        $message = "";
        $data = array();
        $zxc = false;
        $resp = REST_Controller::HTTP_NOT_FOUND;
        if(!empty($id)){
              $apply = $this->job->sales_applied(['id'=>$id], 'id, job_id, status, job_resume as resume, job_letter as message, remarks, interview_expired, created_at, updated_at');
              if(isset($apply->id)){
                      $jobzy = $this->job->find(['id'=>$apply->job_id]);
                      if(!empty($jobzy)){
                          unset($apply->job_id);
                          $resp = 0;
                          $jobz = resource_job_single($jobzy, $this->uid, $apply);
                          $data = $jobz;
                      }else {
                          $message = "data job not found";
                      }
              }else $message = "job application not found";
        }else $message = "id application cannot be empty";
        $resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($resps, REST_Controller::HTTP_OK);
    }

    public function job_post($job_id=''){
            $message = "";
            $data = array();
            $resp = $err_empty = $err_notdb = 0;
            $epp = (object)$this->post();
            $desql = null;
            $remark = (isset($epp->application) && !empty($epp->application)) ? $epp->application : "";
            $resume = (isset($epp->resume) && !empty($epp->resume)) ? $epp->resume : "";
            if(!empty($job_id)){
                    $desql = $this->job->find(["id"=>$job_id]);
                    if(isset($desql->id)){
                          //continue
                    }else{
                        $err_notdb=1;
                        $not_indb = $job_id;
                    }
            }else $err_empty=1;

            if(empty($err_empty) && empty($err_notdb)){
                    if (isset($_FILES['file'])) {
                        if(!empty($_FILES['file']['name'])){
                              $resume = s3_upload($_FILES['file'], "sales/resume");
                              if(!$resume){
                                  $this->response(array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'error when uploading file, check file type or destination path'));
                              }
                        }else{
                            $this->response(array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'upload file failed, nothing to upload'));
                        }
                    }

                    if(!empty($remark)){
                        $data_ins = [
                                'job_id'=>$job_id,
                                'job_resume'=>(!empty($resume)) ? $resume : "",
                                'job_letter'=>(!empty($remark)) ? $remark : "",
                                'status'=>'1',
                                'remarks'=>''
                        ];
                        if(!empty($job_id)){
                              $is_applied = $this->job->sales_applied(['sales_force_id'=>$this->uid, 'job_id'=>$job_id, 'status !='=>'5']);
                              if(!isset($is_applied->id)){
                                  $jobzy = resource_job_single($desql);
                                  $object_name = (isset($jobzy->job_position->name)) ? $jobzy->job_position->name : "Test";
                                  $principalId = (isset($jobzy->principal->id)) ? $jobzy->principal->id : $desql->principal_id;
                                  $data_ins['sales_force_id'] = $this->uid;
                                  $ins = $this->job->insert_application($data_ins);
                                  if($ins){
                                      $message = "successfully apply this job";
                                      $danot = notif_backoffice($this->uid, 'job_application', 'salesman_apply_job', '', '', ['id'=>$ins, 'jobId'=>$job_id, 'salesForceId'=>$this->uid, 'clientId'=>$principalId], $object_name);
                                      $data = [
                                          'id'=>$ins,
                                          'total_result'=>$this->job->total_applied(['sales_force_id'=>$this->uid])
                                      ];
                                  }else{
                                      $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                                      $message = "something went wrong while apply for a job";
                                  }
                              }else{
                                  $resp = REST_Controller::HTTP_BAD_REQUEST;
                                  $message = "you've already applied for this job";
                              }
                        }else{
                            $resp = REST_Controller::HTTP_BAD_REQUEST;
                            $message = "job is required to apply a job";
                        }
                    }else{
                        $resp = REST_Controller::HTTP_BAD_REQUEST;
                        $message = "cover letter are required to apply a job";
                    }
            }else{
                $resp = REST_Controller::HTTP_BAD_REQUEST;
                $message = "";
                if(!empty($err_empty)) $message .= "job and cover letter must not be empty";
                else if(!empty($err_notdb)){
                    $message = (!empty($message)) ? $message." and " : "";
                    $message .= "some of designation id not in db";
                }else if(!empty($errdate)){
                    $message = (!empty($message)) ? $message." and " : "";
                    $message .= "some of date data not right";
                }
            }

            set_response($message, $resp, $data);
    }

    public function approval_put($id='', $type='approve'){
        $message = "";
        $data = array();
        $zxc = false;
        $resp = REST_Controller::HTTP_NOT_FOUND;
        if(!empty($id)){
              $jobs = $this->job->sales_applied(['job_id'=>$id, 'sales_force_id'=>$this->uid, 'status'=>'3']);
              if(isset($jobs->id)){
                  $stat = ($type == 'approve') ? '4' : '5';
                  $tistat = ($type == 'approve') ? "approve" : "reject";
                  $upd = $this->job->update_application(['id'=>$jobs->id], ['status'=>$stat]);
                  if($upd){
                      $resp = 0;
                      $message = "successfully $tistat this interview job";
                  }else $message = "something went wrong while $tistat this interview";
              }else $message = "status interview must be accepted";
        }else $message = "id job can't be empty";
        $resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($resps, REST_Controller::HTTP_OK);
    }

    public function approval_patch($id='', $type='approve'){
        $message = "";
        $data = array();
        $zxc = false;
        $resp = REST_Controller::HTTP_NOT_FOUND;
        if(!empty($id)){
              $jobs = $this->job->sales_applied(['job_id'=>$id, 'sales_force_id'=>$this->uid, 'status'=>'3']);
              if(isset($jobs->id)){
                  $stat = ($type == 'approve') ? '4' : '5';
                  $tistat = ($type == 'approve') ? "approve" : "reject";
                  $upd = $this->job->update_application(['id'=>$jobs->id], ['status'=>$stat]);
                  if($upd){
                      $resp = 0;
                      $message = "successfully $tistat this interview job";
                  }else $message = "something went wrong while $tistat this interview";
              }else $message = "status interview must be accepted";
        }else $message = "id job can't be empty";
        $resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($resps, REST_Controller::HTTP_OK);
    }

}
