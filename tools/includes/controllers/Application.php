<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Application extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model("M_Sales", "sales");
        $this->load->model("M_Jobs", "job");
        $this->load->model("M_Principal", "principal");
    }

    public function notif_post(){
        $resp = REST_Controller::HTTP_NOT_FOUND;
        $message = "";
        $uid = "";
        $to = (!empty($this->post("to"))) ? $this->post("to") : "";
        if(!empty($to)){
            $sales = $this->db->select("id")->get_where("sales_force", ["id"=>$to]);
            if($sales && $sales->num_rows() > 0){
                $uid = $sales->row()->id;
            }else{
                $login = $this->db->select("sales_force_id as id")->get_where("sales_force_login", ["phone"=>$to]);
                if($login && $login->num_rows() > 0){
                    $uid = $login->row()->id;
                }
            }
            $data = [];
            if(!empty($uid)){
                  $title = (!empty($this->post("title"))) ? $this->post("title") : "";
                  $body = (!empty($this->post("message"))) ? $this->post("message") : "";
                  $activities = (!empty($this->post("activity"))) ? $this->post("activity") : "";

                  switch($activities){
                      case "job" :      if(empty($title)) $title = "New Recommendation";
                                        if(empty($body)) $body = "We recommend you this job";
                                        $activity = "job";
                                        $activity_id = (!empty($this->post('activity_id'))) ? $this->post("activity_id") : '';
                                        $notif_data = [];
                                        $owners = [
                                            'id'=>'',
                                            'type'=>'system',
                                            'name'=>'Pintap Sales Force',
                                            'logo_url'=>'https://cdn-ptf.pintap.id/static/pintap-force-icon.png'
                                        ];
                                        if(!empty($activity_id)){
                                            $this->db->where("id", $activity_id);
                                        }else{
                                            $this->db->limit(1, 0);
                                            $this->db->order_by("created_at", "desc");
                                        }
                                        $jobsql = $this->db->select("id, principal_id, job_title, job_position_id, job_type, quota, lower_salary_range, upper_salary_range, date_of_closing, COALESCE(short_description, '') as short_description")->get_where("job", ["status"=>'2']);
                                        if($jobsql && $jobsql->num_rows() > 0){
                                            $arkjo = $jobsql->row();
                                            if(empty($activity_id)) $activity_id = $arkjo->id;
                                            $jp = $this->db->select('id, name')->get_where('job_position', ['id'=>$arkjo->job_position_id]);
                                            $princ = $this->db->select('id, name, company_name')->get_where('principal', ['id'=>$arkjo->principal_id]);
                                            $arkjo->job_position = ($jp && $jp->num_rows() > 0) ? $jp->row() : null;
                                            if(isset($arkjo->job_position->name)) $body .= ", ".$arkjo->job_position->name;
                                            $arkjo->principal = ($princ && $princ->num_rows() > 0) ? $princ->row() : null;
                                            if(isset($arkjo->principal->id)){
                                                $owners = [
                                                    'id'=>$arkjo->principal->id,
                                                    'type'=>'principal',
                                                    'name'=>$arkjo->principal->company_name,
                                                    'logo_url'=> (isset($arkjo->principal->logo_url) && !empty($akrjo->principal->logo_url)) ? $arkjo->principal->logo_url : 'https://cdn-ptf.pintap.id/static/company.png'
                                                ];
                                            }
                                            unset($arkjo->job_position_id);
                                            unset($arkjo->principal_id);
                                            $notif_data = $arkjo;
                                        }else{
                                            $message = "data job not found";
                                            $this->response(['code'=>REST_Controller::HTTP_NOT_FOUND, 'message'=>$message], REST_Controller::HTTP_OK);
                                        }
                                        break;
                      case "application" :
                      case "job-application" : if(empty($title)) $title = "New Schedule Interview";
                                        if(empty($body)) $body = "You get new schedule for interview";
                                        $activity = "job-application";
                                        $activity_id = (!empty($this->post('activity_id'))) ? $this->post("activity_id") : '0';
                                        $notif_data = [];
                                        $owners = [
                                            'id'=>'',
                                            'type'=>'system',
                                            'name'=>'Pintap Sales Force',
                                            'logo_url'=>'https://cdn-ptf.pintap.id/static/pintap-force-icon.png'
                                        ];

                                        $this->db->where("id IN(SELECT job_id FROM job_application WHERE id = '$activity_id')");
                                        $jobsql = $this->db->select("id, principal_id, job_title, job_position_id, job_type, quota, lower_salary_range, upper_salary_range, date_of_closing, COALESCE(short_description, '') as short_description")->get("job");
                                        if($jobsql && $jobsql->num_rows() > 0){
                                            $arkjo = $jobsql->row();
                                            if(empty($activity_id)) $activity_id = $arkjo->id;
                                            $jp = $this->db->select('id, name')->get_where('job_position', ['id'=>$arkjo->job_position_id]);
                                            $princ = $this->db->select('id, name, company_name, logo_url')->get_where('principal', ['id'=>$arkjo->principal_id]);
                                            $arkjo->job_position = ($jp && $jp->num_rows() > 0) ? $jp->row() : null;
                                            if(isset($arkjo->job_position->name)) $body .= ", ".$arkjo->job_position->name;
                                            $arkjo->principal = ($princ && $princ->num_rows() > 0) ? $princ->row() : null;
                                            if(isset($arkjo->principal->id)){
                                                $owners = [
                                                    'id'=>$arkjo->principal->id,
                                                    'type'=>'principal',
                                                    'name'=>$arkjo->principal->company_name,
                                                    'logo_url'=> (isset($arkjo->principal->logo_url) && !empty($akrjo->principal->logo_url)) ? $arkjo->principal->logo_url : 'https://cdn-ptf.pintap.id/static/company.png'
                                                ];
                                            }
                                            $appdata = $this->job->sales_applied(["id"=>$activity_id], "id, status, '' as resume, '' as message, '' as remarks, interview_expired, created_at, updated_at");
                                            if(isset($appdata->status)){
                                                switch($appdata->status){
                                                    case "1" : $dstat = ['id'=>1, 'label'=>'Submit'];break;
                                                    case "2" : $dstat = ['id'=>2, 'label'=>'In Progress'];break;
                                                    case "3" : $dstat = ['id'=>3, 'label'=>'Request for Interview'];break;
                                                    case "4" : $dstat = ['id'=>4, 'label'=>'Interview Approved'];break;
                                                    case "5" : $dstat = ['id'=>5, 'label'=>'Interview Reject'];break;
                                                    case "6" : $dstat = ['id'=>6, 'label'=>'Accepted'];break;
                                                    case "7" : $dstat = ['id'=>7, 'label'=>'Rejected'];break;
                                                    default : $dstat = ['id'=>1, 'label'=>'Submit'];break;
                                                }
                                                $appdata->status = $dstat;
                                                $appdata->interview_expired = (string)$appdata->interview_expired;
                                                $appdata->updated_at = (string)$appdata->updated_at;
                                                if(isset($appdata->created_at)) $appdata->created_at = date_id($appdata->created_at, "j F Y");

                                                //check updated at
                                                if(isset($appdata->updated_at) && !empty($appdata->updated_at)){
                                                    $appdata->updated_at = date_id($appdata->updated_at, "j F Y");
                                                }
                                            }

                                            $arkjo->application_data = $appdata;

                                            unset($arkjo->job_position_id);
                                            unset($arkjo->principal_id);
                                            $notif_data = $arkjo;
                                        }else{
                                            $message = "data application not found";
                                            $this->response(['code'=>REST_Controller::HTTP_NOT_FOUND, 'message'=>$message], REST_Controller::HTTP_OK);
                                        }
                                        break;
                      case "news" :     if(empty($title)) $title = "New Insight";
                                        if(empty($body)) $body = "New Insight";
                                        $notif_data = [];
                                        $activity = "news";
                                        $activity_id = (!empty($this->post('activity_id'))) ? $this->post("activity_id") : '';
                                        $owners = [
                                            'id'=>'',
                                            'type'=>'system',
                                            'name'=>'Pintap Sales Force',
                                            'logo_url'=>'https://cdn-ptf.pintap.id/static/news-icon.png'
                                        ];
                                        if(!empty($activity_id)){
                                            $this->db->where("id", $activity_id);
                                        }else{
                                            $this->db->limit(1, 0);
                                            $this->db->order_by("published_at", "desc");
                                            $this->db->where("status", '2');
                                        }
                                        $newsql = $this->db->select("id, title, category_id, COALESCE(excerpt, '') as excerpt,  COALESCE(featured_image_url, '') as featured_image_url, published_at")->get("news");
                                        if($newsql && $newsql->num_rows() > 0){
                                            $arknews = $newsql->row();
                                            if(empty($activity_id)) $activity_id = $arknews->id;
                                            if(!empty($arknews->title)) $body.= ", ".$arknews->title;
                                            $cat = $this->db->select("id, title, COALESCE(image_url, '') as image_url")->get_where('news_category', ['id'=>$arknews->category_id]);
                                            $arknews->category = ($cat && $cat->num_rows() > 0) ? $cat->row() : null;
                                            unset($arknews->category_id);
                                            $notif_data = $arknews;
                                        }else{
                                            $message = "data news not found";
                                            $this->response(['code'=>REST_Controller::HTTP_NOT_FOUND, 'message'=>$message], REST_Controller::HTTP_OK);
                                        }
                                        break;
                      case "profile" :  if(empty($title)) $title = "Complete your profile";
                                        if(empty($body)) $body = "Complete your profile to get more recommendation";
                                        $activity = "profile";
                                        $activity_id = (!empty($this->post('activity_id'))) ? $this->post("activity_id") : '';
                                        if(empty($activity_id)) $activity_id = "desire";
                                        $notif_data = [];
                                        $owners = [
                                            'id'=>'',
                                            'type'=>'system',
                                            'name'=>'Pintap Sales Force',
                                            'logo_url'=> 'https://cdn-ptf.pintap.id/static/pintap-force-icon.png'
                                        ];
                                        break;
                      case "job-list" : if(empty($title)) $title = "Recommended Job";
                                        if(empty($body)) $body = "Here is a new recommendation job for you";
                                        $activity = "job-list";
                                        $activity_id = (!empty($this->post('activity_id'))) ? $this->post("activity_id") : '';
                                        $notif_data = [];
                                        $jobsql = $this->db->order_by("created_at", "desc")->select("id, principal_id, job_title, job_position_id, job_type, quota, lower_salary_range, upper_salary_range, date_of_closing, COALESCE(short_description, '') as short_description")->get_where("job", ["status"=>'1']);
                                        if($jobsql && $jobsql->num_rows() > 0){
                                            $arkjo = $jobsql->row();
                                            $jp = $this->db->select('id, name')->get_where('job_position', ['id'=>$arkjo->job_position_id]);
                                            $princ = $this->db->select('id, name, company_name, logo_url')->get_where('principal', ['id'=>$arkjo->principal_id]);
                                            $arkjo->job_position = ($jp && $jp->num_rows() > 0) ? $jp->row() : null;
                                            if(isset($arkjo->job_position->name)) $body .= ", ".$arkjo->job_position->name;
                                            $arkjo->principal = ($princ && $princ->num_rows() > 0) ? $princ->row() : null;
                                            if(isset($arkjo->principal->id)){
                                                $owners = [
                                                    'id'=>$arkjo->principal->id,
                                                    'type'=>'principal',
                                                    'name'=>$arkjo->principal->company_name,
                                                    'logo_url'=> (isset($arkjo->principal->logo_url) && !empty($akrjo->principal->logo_url)) ? $arkjo->principal->logo_url : 'https://cdn-ptf.pintap.id/static/company.png'
                                                ];
                                            }
                                            unset($arkjo->job_position_id);
                                            unset($arkjo->principal_id);
                                            $notif_data = $arkjo;
                                        }
                                        $owners = [
                                            'id'=>'',
                                            'type'=>'system',
                                            'name'=>'Pintap Sales Force',
                                            'logo_url'=>'https://cdn-ptf.pintap.id/static/pintap-force-icon.png'
                                        ];
                                        break;
                      default :         if(empty($title)) $title = "Complete your profile";
                                        if(empty($body)) $body = "Complete your profile to get more recommendation";
                                        $activiy = (empty($this->post("activity"))) ? $this->post("activity") : "profile";
                                        $activity_id = $this->post("activity_id");
                                        if(empty($activity_id)) $activity_id = "desire";
                                        $notif_data = [];
                                        $owners = [
                                            'id'=>'',
                                            'type'=>'system',
                                            'name'=>'Pintap Sales Force',
                                            'logo_url'=>'https://cdn-ptf.pintap.id/static/pintap-force-icon.png'
                                        ];
                                        break;
                  }
                  $output = testpush_notif($uid, $title, $body, $activity, $activity_id, $notif_data, $owners);

            }else{
                $output = ["code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"sales not found"];
            }
        }else{
            $output = ["code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"sales identical data is required"];
        }
        $this->response($output, REST_Controller::HTTP_OK);
    }

    public function list_get($type=''){
        $message = "";
        $data = array();
        $resp = 0;

        // $stat = ($type == 'approval') ? '1' : '';

        switch($type){
            case 'approval' : $stat = '1';break;
            case 'interview-progress' : $stat = '3';break;
            case 'interview-approved' : $stat = '4';break;
            case 'interview-reject' : $stat = '5';break;
            case 'accepted' : $stat = '6';break;
            case 'rejected' : $stat = '7';break;
            default : $stat='';break;
        }

        $rpp = intval($this->get("rpp"));
        $rpp = (!empty($rpp)) ? $rpp : 12;
        $page = intval($this->get("page"));
        $page = (!empty($page)) ? $page : 1;

        if(!empty($stat)) $dawhere['status'] = $stat;
        else $dawhere = null;

        $total_data = $this->job->total_applied($dawhere);
        $total_page = ceil($total_data/$rpp);
        $next_page = ($page < $total_page) ? ($page + 1) : $total_page;
        $prev_page = ($page > 1) ? ($page - 1) : 1;
        $apply = $this->job->list_applied($dawhere, 'id, job_id, sales_force_id, status, job_resume as resume, job_letter as message, created_at', $page, $rpp);
        if(!empty($apply)){
            foreach($apply as $ap){
                switch($ap->status){
                    case "1" : $dstat = ['id'=>1, 'label'=>'Submit'];break;
                    case "2" : $dstat = ['id'=>2, 'label'=>'In Progress'];break;
                    case "3" : $dstat = ['id'=>3, 'label'=>'Request for Interview'];break;
                    case "4" : $dstat = ['id'=>4, 'label'=>'Interview Approved'];break;
                    case "5" : $dstat = ['id'=>5, 'label'=>'Interview Reject'];break;
                    case "6" : $dstat = ['id'=>6, 'label'=>'Accepted'];break;
                    case "7" : $dstat = ['id'=>7, 'label'=>'Rejected'];break;
                    default : $dstat = ['id'=>1, 'label'=>'Submit'];break;
                }
                $ap->status = $dstat;
                $ap->sales = $this->sales->find_cond(["id"=>$ap->sales_force_id], 'id, sales_name as name');
                unset($ap->sales_force_id);
                $data[] = $ap;
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

    public function approval_put($id='', $type='approve'){
        $message = "";
        $data = array();
        $zxc = false;
        $resp = REST_Controller::HTTP_NOT_FOUND;
        if(!empty($id)){
              $jobs = $this->job->sales_applied(['id'=>$id, 'status'=>'1']);
              if(isset($jobs->id)){
                  $stat = ($type == 'approve') ? '3' : '7';
                  $tistat = ($type == 'approve') ? "approve" : "reject";
                  $daupd = ['status'=>$stat];
                  if($type == 'approve'){
                    $daupd['interview_expired'] = date("Y-m-d", strtotime("+7days"));
                    $daupd['remarks'] = '<strong> Tempat : </strong> Gedung ABCD<br /><strong>Waktu : </strong>11:00 WIB';
                  }else $daupd['remarks'] = 'rejected';
                  $upd = $this->job->update_application(['id'=>$jobs->id], $daupd);
                  if($upd){
                      $resp = 0;
                      $message = "successfully $tistat this application";
                  }else $message = "something went wrong while $tistat this application";
              }else $message = "status application must be on requested";
        }else $message = "id application can't be empty";
        $resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($resps, REST_Controller::HTTP_OK);
    }

    public function nextapproval_put($id='', $type='approve'){
        $message = "";
        $data = array();
        $zxc = false;
        $resp = REST_Controller::HTTP_NOT_FOUND;
        if(!empty($id)){
              $jobs = $this->job->sales_applied(['id'=>$id, 'status'=>'4']);
              if(isset($jobs->id)){
                  $stat = ($type == 'approve') ? '6' : '7';
                  $tistat = ($type == 'approve') ? "approve" : "reject";
                  $daupd = ['status'=>$stat];
                  // if($type == 'approve'){
                  //   $daupd['interview_expired'] = date("Y-m-d", strtotime("+7days"));
                  //   $daupd['remarks'] = '<strong> Tempat : </strong> Gedung ABCD<br /><strong>Waktu : </strong>11:00 WIB';
                  // }else $daupd['remarks'] = 'rejected';
                  $upd = $this->job->update_application(['id'=>$jobs->id], $daupd);
                  if($upd){
                      $resp = 0;
                      $message = "successfully $tistat this interview";
                  }else $message = "something went wrong while $tistat this interview";
              }else $message = "status interview must be on approved";
        }else $message = "id application can't be empty";
        $resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($resps, REST_Controller::HTTP_OK);
    }
}
