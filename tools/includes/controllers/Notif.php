<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Notif extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->auth();
        $this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
        $this->load->model('M_Sales', 'user');
    }

    public function test_get(){
        $message = "";
        $resp = 0;
        $data = [];
        setpush_notif($this->uid, 'testing notification', 'notification', ['uid'=>$this->uid, 'description'=>'test notif']);
        resp_data($message, $resp, $data);
    }

    public function list_get()
    {
        if(!function_exists("waktu_lalu")) $this->load->helper("bisa");
        $resp = 0;
        $message = "";
        $data = $meta = array();
        $this->load->model("M_Notif");
        $gpage = $this->get("page");
        $rpp = $this->get("rpp");
        $rpp = (!empty($rpp)) ? $rpp : 10;
        $page = ($gpage && !empty($gpage)) ? $gpage : 0;
        if(isset($this->sess)){
              $sess = $this->sess;
              $uid = (!empty($sess) && isset($sess["user_id"])) ? $sess["user_id"] : 0;
              $notif_unread = $this->M_Notif->total_unread($uid);
              $latest_notif = $this->M_Notif->latest_notif($uid, $page, $rpp);
              $next_page = (!empty($page) && $page > 1) ? ($page + 1) : 2;
              $next_notif = $this->M_Notif->latest_notif($uid, $next_page, $rpp);
              $total_next = count($next_notif);
              $total_data = $this->M_Notif->total_notif($uid);
              $total_page = ($rpp != "all") ? ceil($total_data/$rpp) : 1;
  						$curr_page = ($gpage && !empty($gpage)) ? $gpage : 1;
              $lanjut_page = (!empty($total_next)) ? $next_page : 1;
              $prev_page = (!empty($curr_page) && $curr_page > 1) ? $curr_page - 1 : 1;
              $data_notif = [];
              if(!empty($latest_notif)){
                  foreach($latest_notif as $nov){
                      $nov->owner = $nov->owner;
                      $status_notif = (!empty($nov->read_at)) ? "read" : "unread";
                      $nov->status = $status_notif;
                      $nov->link = (!empty($nov->link)) ? $nov->link : "";
                      $nov->notif_data = (!empty($nov->data)) ? json_decode($nov->data, true) : null;
                      unset($nov->data);
                      $nov->owner = (!empty($nov->owner)) ? json_decode($nov->owner) : null;
                      if(isset($nov->owner->logo_url) && !empty($nov->owner->logo_url)){
                          if(strpos($nov->owner->logo_url, "placekitten.com") !== false){
                              if($nov->activity == "news"){
                                  $nov->owner->logo_url = 'https://cdn-ptf.pintap.id/static/news-icon.png';
                              }else if($nov->activity == "job" || $nov->activity == "job-application" || $nov->activity == "job-list"){
                                  $nov->owner->logo_url = 'https://cdn-ptf.pintap.id/static/company.png';
                              }else{
                                  $nov->owner->logo_url = 'https://cdn-ptf.pintap.id/static/pintap-force-icon.png';
                              }
                          }
                      }
                      $created = $nov->created_at;
                      $created_at = date("Y-m-d H:i:s", strtotime($created . "+7hours"));
                      $nov->notif_time = waktu_lalu($created_at);
                      $nov->time = $created_at;
                      $nov->read_at = (!empty($nov->read_at)) ? waktu_lalu($nov->read_at) : "";
                      // $nov->current_time = date("Y-m-d H:i:s", $curtime);
                      unset($nov->sales_id);
                      unset($nov->created_at);
                      unset($nov->updated_at);
                      $data_notif[] = $nov;
                  }
                  $data = $data_notif;
                  $meta = array(
                    'current_page'=>$curr_page,
  									'next_page'=>$lanjut_page,
  									'prev_page'=>$prev_page,
  									'total_page'=>$total_page,
  									'total_result'=>$total_data,
  									'rpp'=>($rpp != "all") ? (int)$rpp : (int)$total_data,
                    'total_unread' => $notif_unread,
                    'total_next' => $total_next
                  );

              }else{
                  $message = "notification is empty";
              }
        }else{
              $resp = REST_Controller::HTTP_UNAUTHORIZED;
              $message = "unauthorized";
        }
        $output = array("code"=>$resp, "message"=>$message, "data"=>$data);
        if(!empty($meta)) $output["meta"] = $meta;
        $this->response($output, REST_Controller::HTTP_OK);
    }

    public function read_patch($id="")
    {
        $resp = 0;
        $message = "";
        $data = array();
        $this->load->model("M_Notif");
        $gpage = $this->get("page");
        $page = ($gpage && !empty($gpage)) ? $gpage : 0;
        if(!empty($id)){
              $detail = $this->M_Notif->detail($id, "id");
              if(isset($detail->id)){
                  $message = "";
                  $readun = $this->M_Notif->read_unread($this->uid, $id);
              }else{
                  $resp = REST_Controller::HTTP_NOT_FOUND;
                  $message = "notification not found";
              }
        }else{
              $resp = REST_Controller::HTTP_BAD_REQUEST;
              $message = "unauthorized";
        }
        $output = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($output, REST_Controller::HTTP_OK);
    }

    public function read_put($id="")
    {
        $resp = 0;
        $message = "";
        $data = array();
        $this->load->model("M_Notif");
        $gpage = $this->get("page");
        $page = ($gpage && !empty($gpage)) ? $gpage : 0;
        if(!empty($id)){
              $detail = $this->M_Notif->detail($id, "id");
              if(isset($detail->id)){
                  $message = "read notif success";
                  $readun = $this->M_Notif->read_unread($this->uid, $id);
              }else{
                  $resp = REST_Controller::HTTP_NOT_FOUND;
                  $message = "notification not found";
              }
        }else{
              $resp = REST_Controller::HTTP_BAD_REQUEST;
              $message = "unauthorized";
        }
        $output = array("code"=>$resp, "message"=>$message, "data"=>$data);
        $this->response($output, REST_Controller::HTTP_OK);
    }

}
