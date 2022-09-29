<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Comments extends BS_Controller{

      function __construct(){
            parent::__construct();
            $this->load->model("M_News", "news");
            $this->load->model("M_Jobs", "jobs");
            $this->auth();
  	  }

      public function add_post($news=''){
          $parents = $this->post("parent");
          $content = $this->post("comments");
					$user_id = $this->sess["user_id"];
          $is_moderator = (isset($this->sess["admin"])) ? 1 : 0;
	        $resps = [];
	        if(!empty($news)){
    	        if(!empty($content)){
    							$esql = $this->db->select("id")->get_where("news", array("id"=>$news));
    							if($esql && $esql->num_rows() > 0){
    										$usrow = $esql->row();
                        $insid = get_uuid();
    										$data_upd["id"] = $insid;
    										$data_upd["content"] = $content;
    										$data_upd["user_id"] = $user_id;
    										$data_upd["news_id"] = $news;
    										$data_upd["is_moderator"] = $is_moderator;
                        if(!empty($parents)){
                            $psql = $this->db->select("id, parent")->get_where("news_comment", array("id"=>$parents));
                            if($psql && $psql->num_rows() > 0){
                                if(!empty($psql->row()->parent)) $data_upd["parent"] = $psql->row()->parent;
                                else $data_upd["parent"] = $parents;
                            }else $this->response(array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"chosen comment not found", "data"=>[]), REST_Controller::HTTP_OK);
                        }
    										// $insid = $this->db->insert_id();
    										if($this->db->insert("news_comment", $data_upd)){
                              $total_comm = $this->db->select("COUNT(id) as total")->get_where("news_comment", ["news_id"=>$news]);
                              $total_comment = ($total_comm && $total_comm->num_rows() > 0) ? (int)$total_comm->row()->total : 0;
                              $vusrep = $this->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$user_id));
                              $user_rep = ($vusrep && $vusrep->num_rows() > 0) ? array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "profile_picture_url"=>$vusrep->row()->profile_picture_url) : [];
                              $data = [
                                  "id"=>$insid,
                                  "user"=>$user_rep,
                                  "content"=>$content,
                                  "created_at"=>"baru saja",
                                  "editable"=>false,
                                  "total_reply"=>0,
                                  "latest_reply"=>null,
                                  "parent"=>(!empty($parents)) ? $parents : "",
                                  "total_comment"=>$total_comment
                              ];
    													$resps = array("code"=>0, "message"=>"successfully add comment", "data"=>$data);
    										}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"something wrong when add comment, contact admin if you see this error");
    							}else{
    									$resps = array("code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"news not found");
    							}
    					}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST,  "message"=>"bad request : comment text can't be empty");
          }else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST,  "message"=>"bad request : news are required to add comment");
					//set flashdata
					if(!isset($resps["data"])) $resps["data"] = [];
					$this->response($resps, REST_Controller::HTTP_OK);
	    }

      public function addjob_post($news=''){
          $parents = $this->post("parent");
          $content = $this->post("comments");
					$user_id = $this->sess["user_id"];
          $is_moderator = (isset($this->sess["admin"])) ? 1 : 0;
	        $resps = [];
	        if(!empty($news)){
    	        if(!empty($content)){
    							$esql = $this->db->select("id")->get_where("job", array("id"=>$news));
    							if($esql && $esql->num_rows() > 0){
    										$usrow = $esql->row();
                        $insid = get_uuid();
    										$data_upd["id"] = $insid;
    										$data_upd["content"] = $content;
    										$data_upd["user_id"] = $user_id;
    										$data_upd["job_id"] = $news;
    										$data_upd["is_moderator"] = $is_moderator;
                        if(!empty($parents)){
                            $psql = $this->db->select("id, parent")->get_where("job_comment", array("id"=>$parents));
                            if($psql && $psql->num_rows() > 0){
                                if(!empty($psql->row()->parent)) $data_upd["parent"] = $psql->row()->parent;
                                else $data_upd["parent"] = $parents;
                            }else $this->response(array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"chosen comment not found", "data"=>[]), REST_Controller::HTTP_OK);
                        }
    										// $insid = $this->db->insert_id();
    										if($this->db->insert("job_comment", $data_upd)){
                              $total_comm = $this->db->select("COUNT(id) as total")->get_where("job_comment", ["job_id"=>$news]);
                              $total_comment = ($total_comm && $total_comm->num_rows() > 0) ? (int)$total_comm->row()->total : 0;
                              $vusrep = $this->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$user_id));
                              $user_rep = ($vusrep && $vusrep->num_rows() > 0) ? array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "profile_picture_url"=>$vusrep->row()->profile_picture_url) : [];
                              $data = [
                                  "id"=>$insid,
                                  "user"=>$user_rep,
                                  "content"=>$content,
                                  "created_at"=>"baru saja",
                                  "editable"=>false,
                                  "total_reply"=>0,
                                  "latest_reply"=>null,
                                  "parent"=>(!empty($parents)) ? $parents : "",
                                  "total_comment"=>$total_comment
                              ];
    													$resps = array("code"=>0, "message"=>"successfully add comment", "data"=>$data);
    										}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"something wrong when add comment, contact admin if you see this error");
    							}else{
    									$resps = array("code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"news not found");
    							}
    					}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST,  "message"=>"bad request : comment text can't be empty");
          }else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST,  "message"=>"bad request : news are required to add comment");
					//set flashdata
					if(!isset($resps["data"])) $resps["data"] = [];
					$this->response($resps, REST_Controller::HTTP_OK);
	    }

      public function more_get($news=''){
          $data = [];
          $resp = REST_Controller::HTTP_NOT_FOUND;
          $parents = $this->get("parent");
          $page = $this->get("page");
          $message = "";
          $fpage = (!empty((int)$page) && $page > 1) ? (int)$page : 1;
          $spage = (!empty($fpage) && $fpage > 1) ? (($fpage - 1) * 10) + 1 : 0;
          $news = $this->news->detail($news);
          if(!empty($news)){
              if(!empty($news->comment_status)){
                    $this->db->order_by("created_at", "DESC");
                    $this->db->limit(10, $spage);
                    $whereData = (!empty($parents)) ? " AND parent = '$parents'" : " AND parent IS NULL";
                    $tcmnt = $this->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("news_comment", "news_id = '".$news->id."'".$whereData);
                    $lsq = $this->db->last_query();
                    if($tcmnt && $tcmnt->num_rows() > 0){
                        $resp = 0;
                        foreach($tcmnt->result() as $tcv){
                            $users = [];
                            $avatar = base_url("public/assets/img/user.png");
                            if(!empty($tcv->is_moderator)) $users = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
                            else{
                                $vusr = $this->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$tcv->user));
                                if($vusr && $vusr->num_rows() > 0) $users = array("id"=>$vusr->row()->id, "name"=>$vusr->row()->name, "profile_picture_url"=>$vusr->row()->profile_picture_url);
                            }

                            if(!empty($users)){
                                $editable = false;
                                if(isset($this->sess["admin"])) $editable = true;
                                else if(empty($tcv->is_moderator) && isset($this->sess["vendor"]) && $this->sess["user_id"] = $tcv->user) $editable = true;
                                $tcv->user = $users;
                                $tcv->created_at = waktu_lalu($tcv->created_at);
                                $tcv->editable = $editable;
                                $this->db->order_by("created_at", "DESC");
                                $trpt = $this->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("news_comment", array("news_id"=>$news->id, "parent"=>$tcv->id));
                                $tcv->total_reply = ($trpt && $trpt->num_rows() > 0) ? (double)$trpt->num_rows() : 0;
                                $latest_reply = null;
                                if($trpt && $trpt->num_rows() > 0 && $tcv){
                                    $rply = $trpt->row();
                                    $user_rep = [];
                                    if(!empty($rply->is_moderator)) $user_rep = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
                                    else{
                                      $vusrep = $this->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$rply->user));
                                      if($vusrep && $vusrep->num_rows() > 0) $user_rep = array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "avatar"=>$vusrep->row()->profile_picture_url);
                                    }

                                    if(!empty($user_rep)){
                                        $rep_ed = false;
                                        if(isset($this->sess["admin"])) $rep_ed = true;
                                        else if(empty($rply->is_moderator) && isset($this->sess["vendor"]) && $this->sess["user_id"] = $rply->user) $rep_ed = true;

                                        $rply->user = $user_rep;
                                        $rply->editable = $rep_ed;
                                        unset($rply->is_moderator);
                                        $latest_reply = $rply;
                                    }
                                }
                                $tcv->latest_reply = $latest_reply;
                                unset($tcv->is_moderator);
                                $data[] = $tcv;
                            }
                        }
                    }else $message = "data comment not found";
              }else $message = "comment not enabled";
          }else $message = "news data not found";
          $output = array(
              "code"=>$resp,
              "message"=>$message,
              "data"=>$data
          );
          $this->response($output, REST_Controller::HTTP_OK);
      }

      public function morejob_get($news=''){
          $data = [];
          $resp = REST_Controller::HTTP_NOT_FOUND;
          $parents = $this->get("parent");
          $page = $this->get("page");
          $message = "";
          $fpage = (!empty((int)$page) && $page > 1) ? (int)$page : 1;
          $spage = (!empty($fpage) && $fpage > 1) ? (($fpage - 1) * 10) + 1 : 0;
          $news = $this->jobs->find(['id'=>$news]);
          if(!empty($news)){
                    $this->db->order_by("created_at", "DESC");
                    $this->db->limit(10, $spage);
                    $whereData = (!empty($parents)) ? " AND parent = '$parents'" : " AND parent IS NULL";
                    $tcmnt = $this->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("job_comment", "job_id = '".$news->id."'".$whereData);
                    $lsq = $this->db->last_query();
                    if($tcmnt && $tcmnt->num_rows() > 0){
                        $resp = 0;
                        foreach($tcmnt->result() as $tcv){
                            $users = [];
                            $avatar = base_url("public/assets/img/user.png");
                            if(!empty($tcv->is_moderator)) $users = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
                            else{
                                $vusr = $this->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$tcv->user));
                                if($vusr && $vusr->num_rows() > 0) $users = array("id"=>$vusr->row()->id, "name"=>$vusr->row()->name, "profile_picture_url"=>$vusr->row()->profile_picture_url);
                            }

                            if(!empty($users)){
                                $editable = false;
                                if(isset($this->sess["admin"])) $editable = true;
                                else if(empty($tcv->is_moderator) && isset($this->sess["vendor"]) && $this->sess["user_id"] = $tcv->user) $editable = true;
                                $tcv->user = $users;
                                $tcv->created_at = waktu_lalu($tcv->created_at);
                                $tcv->editable = $editable;
                                $this->db->order_by("created_at", "DESC");
                                $trpt = $this->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("job_comment", array("job_id"=>$news->id, "parent"=>$tcv->id));
                                $tcv->total_reply = ($trpt && $trpt->num_rows() > 0) ? (double)$trpt->num_rows() : 0;
                                $latest_reply = null;
                                if($trpt && $trpt->num_rows() > 0 && $tcv){
                                    $rply = $trpt->row();
                                    $user_rep = [];
                                    if(!empty($rply->is_moderator)) $user_rep = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
                                    else{
                                      $vusrep = $this->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$rply->user));
                                      if($vusrep && $vusrep->num_rows() > 0) $user_rep = array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "avatar"=>$vusrep->row()->profile_picture_url);
                                    }

                                    if(!empty($user_rep)){
                                        $rep_ed = false;
                                        if(isset($this->sess["admin"])) $rep_ed = true;
                                        else if(empty($rply->is_moderator) && isset($this->sess["vendor"]) && $this->sess["user_id"] = $rply->user) $rep_ed = true;

                                        $rply->user = $user_rep;
                                        $rply->editable = $rep_ed;
                                        unset($rply->is_moderator);
                                        $latest_reply = $rply;
                                    }
                                }
                                $tcv->latest_reply = $latest_reply;
                                unset($tcv->is_moderator);
                                $data[] = $tcv;
                            }
                        }
                    }else $message = "data comment not found";
          }else $message = "news data not found";
          $output = array(
              "code"=>$resp,
              "message"=>$message,
              "data"=>$data
          );
          $this->response($output, REST_Controller::HTTP_OK);
      }

}
