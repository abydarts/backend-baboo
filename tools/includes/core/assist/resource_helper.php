<?php
/**
 * @author   Abyan Ahmad fathin <abyan.site@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/* put function resource here */

if(!function_exists('resource_experience')){
    function resource_experience($arr, $check_dur=false){
        $ci =& get_instance();
        $resps = $darv = [];

        if(!empty($arr)){
            foreach($arr as $ar){
                $ar->from = $ar->from_date;
                $ar->to = (!empty($ar->to_date)) ? $ar->to_date : "";
                $postid = $ar->job_position_id;
                $ar->letter_reference = (string)$ar->letter_reference;
                $file_size = 0;
                $ar->file_size = $file_size;
                if(isset($ar->job_position_id)){
                    if(!empty($ar->job_position_id)){
                        $posts = $ci->db->select('id, name')->get_where('job_position', ['id'=>$ar->job_position_id]);
                        $ar->posts = ($posts && $posts->num_rows() > 0) ? $posts->row() : null;
                    }
                    unset($ar->job_position_id);
                }
                $todate = (!empty($ar->to_date)) ? $ar->to_date : date("Y-m-d");
                $ar->current_experience = (empty($ar->to_date)) ? true : false;
                $duration_work = strtotime($todate) - strtotime($ar->from_date);
                $ar->duration = calculate_timestamp($duration_work);
                $resps[] = $ar;
                if($check_dur){
                    if(!empty($darv) && isset($darv[$postid])){
                        $durbef = $darv[$postid]['duration'];
                        $durbef += strtotime($todate) - strtotime($ar->from_date);
                        $darv[$postid]['duration'] = $durbef;
                    }
                    else{
                        $durbef = strtotime($todate) - strtotime($ar->from_date);
                        $darv[$postid] = ['posts'=>$ar->posts, 'duration'=>$durbef];
                    }
                }
                if(isset($ar->sales_force_id)) unset($ar->sales_force_id);
                unset($ar->from_date);
                unset($ar->to_date);
                if(isset($ar->created_at)) unset($ar->created_at);
                if(isset($ar->created_at)) unset($ar->is_verified);
            }
        }
        if(!$check_dur) return $resps;
        else{
            $durr = [];
            array_multisort(array_column($darv, "duration"), SORT_DESC, $darv);
            $total_experience = 0;
            if(!empty($darv)){
                foreach($darv as $dav){
                    $total_experience += $dav['duration'];
                    $dav['duration'] = calculate_timestamp($dav['duration']);
                    $durr[] = $dav;
                }
            }
            $response = [
                'experience'=>$resps,
                'post_duration'=>$durr,
                'total_experience'=>calculate_timestamp($total_experience)
            ];
            return $response;
        }
    }
}

if(!function_exists('resource_expr_single')){
    function resource_expr_single($ar){
        $ci =& get_instance();
        $resps = null;
        if(!empty($ar)){
                $ar->from = $ar->from_date;
                $ar->to = (!empty($ar->to_date)) ? $ar->to_date : "";
                if(isset($ar->job_position_id)){
                  $postid = $ar->job_position_id;
                  if(!empty($ar->job_position_id)){
                      $posts = $ci->db->select('id, name')->get_where('job_position', ['id'=>$ar->job_position_id]);
                      $ar->posts = ($posts && $posts->num_rows() > 0) ? $posts->row() : null;
                  }
                  unset($ar->job_position_id);
                }

                $ar->letter_reference = (string)$ar->letter_reference;
                $file_size = 0;
                if(!empty($ar->letter_reference)){
                    $headers = get_headers($ar->letter_reference, true);
                    $file_size = (int)$headers['Content-Length'];
                }
                $ar->file_size = $file_size;

                $todate = (!empty($ar->to_date)) ? $ar->to_date : date("Y-m-d");
                $ar->current_experience = (!empty($ar->is_verified)) ? true : false;
                $duration_work = strtotime($todate) - strtotime($ar->from_date);
                $ar->duration = calculate_timestamp($duration_work);
                if(isset($ar->sales_force_id)) unset($ar->sales_force_id);
                unset($ar->from_date);
                unset($ar->to_date);
                if(isset($ar->created_at)) unset($ar->created_at);
                if(isset($ar->is_verified)) unset($ar->is_verified);
                $resps = $ar;
        }
        return $resps;
    }
}

if(!function_exists('resource_job')){
    function resource_job($arr, $sforce_id=''){
        $ci =& get_instance();
        if(!isset($ci->job)) $ci->load->model("M_Jobs", "job");
        if(!isset($ci->principal)) $ci->load->model("M_Principal", "principal");
        if(!isset($ci->region)) $ci->load->model("M_Region", "region");
        $resps = [];

        if(!empty($arr)){
            foreach($arr as $r){

                $designation = $ci->job->info_position($r->job_position_id);
                $r->job_position = $designation;
                if(isset($r->is_featured)) $r->is_featured = (!empty($r->is_featured)) ? true : false;
                unset($r->job_position_id);
                // get job type
                // $job_type = $ci->job->info($r->job_type);
                // $r->job_type = $;
                // get date
                if(isset($r->date_of_closing)) $r->date_of_closing = date_id($r->date_of_closing, "j F Y");
                if(isset($r->created_at)) $r->created_at = date_id($r->created_at, "j F Y");
                $cits = $ci->region->findCityCond(['city_code'=>$r->city_code], 'city_code as id, city_name as name');
                $r->city = $cits;
                unset($r->city_code);
                /* get job status*/
                $r->image = (isset($r->image) && !empty($r->image)) ? $r->image : "https://placekitten.com/600/400";
                // if(isset($r->status)){
                //     if($r->status==1): $r->status = 'published'; else: $r->status = 'draft'; endif;
                // }
                $jcat = $ci->job->single_category("id IN(SELECT job_category_id FROM job_category_mapping WHERE job_id = '".$r->id."')", "id, name");
                $r->category = $jcat;

                $p_company = $ci->principal->find($r->principal_id, "id, name, company_name, '' as description, logo_url as image_url");
                if(!empty($p_company)){
                    if(!isset($p_company->image_url) || (isset($p_company->image_url) && empty($p_company->image_url))){
                        $p_company->image_url = "https://cdn-ptf.pintap.id/static/company.png";
                    }
                    if(isset($p_company->company_name)){
                        $p_company->name = $p_company->company_name;
                    }
                    $galleries = [];
                    $gall = $ci->principal->galleries(['principal_id'=>$p_company->id], 'image_url');
                    if(!empty($gall)){
                        foreach($gall as $gal){
                            $galleries[] = $gal->image_url;
                        }
                    }
                    $p_company->gallery = $galleries;
                    if(isset($p_company->updated_at)) unset($p_company->updated_at);
                    if(isset($p_company->updated_at)) unset($p_company->updated_at);
                }
                $r->principal = $p_company;
                $isbook = $isapply = false;
                $appdata = null;
                if(!empty($sforce_id)){
                    $book = $ci->job->sales_bookmark(["job_id"=>$r->id, "sales_id"=>$sforce_id]);
                    $isbook = (isset($book->id)) ? true : $isbook;
                    $apply = $ci->job->check_apply_job($r->id, $sforce_id);
                    $isapply = ($apply && $apply->num_rows() > 0) ? true : $isapply;
                    $appdata = $ci->job->sales_applied(["sales_force_id"=>$sforce_id, "job_id"=>$r->id, 'status !='=>'5'], "status, '' as resume, '' as message, '' as remarks, interview_expired, created_at, updated_at");
                    if(isset($appdata->status)){
                        switch($appdata->status){
                            case "1" : $dstat = ['id'=>1, 'label'=>'Submit'];break;
                            case "2" : $dstat = ['id'=>2, 'label'=>'In Progress'];break;
                            case "3" : $dstat = ['id'=>3, 'label'=>'Request for Interview'];break;
                            case "4" : $dstat = ['id'=>4, 'label'=>'Approved'];break;
                            case "5" : $dstat = ['id'=>5, 'label'=>'Interview Reject'];break;
                            case "6" : $dstat = ['id'=>6, 'label'=>'Accepted'];break;
                            case "7" : $dstat = ['id'=>7, 'label'=>'Rejected'];break;
                            default : $dstat = ['id'=>1, 'label'=>'Submit'];break;
                        }
                        $appdata->status = $dstat;
                        $appdata->interview_expired = (string)$appdata->interview_expired;
                        $appdata->updated_at = (string)$appdata->updated_at;
                    }

                    if(isset($appdata->created_at)) $appdata->created_at = date_id($appdata->created_at, "j F Y");

                    //check updated at
                    if(isset($appdata->updated_at) && !empty($appdata->updated_at)){
                        $appdata->updated_at = date_id($appdata->updated_at, "j F Y");
                    }
                }
                $r->application_data = $appdata;
                $r->is_bookmark = $isbook;
                $r->is_applied = $isapply;
                $comments = [];
                if(isset($r->id)){
                    $tlike = $ci->db->select("COALESCE(COUNT(id), 0) as total_like")->get_where("job_react_user", array("job_id"=>$r->id));
                    $r->total_like = ($tlike && $tlike->num_rows() > 0) ? (int)$tlike->row()->total_like : 0;
                    $is_like = false;
                    if(!empty($sforce_id)){
                        $sqlike = $ci->db->select("id")->get_where("job_react_user", array("job_id"=>$r->id, "created_by"=>$sforce_id));
                        $is_like = ($sqlike && $sqlike->num_rows() > 0) ? true : false;
                    }
                    $r->is_like = $is_like;
                    $tcomm = $ci->db->select("COALESCE(COUNT(id), 0) as total")->get_where("job_comment", array("job_id"=>$r->id));
                    $r->total_comment = ($tcomm && $tcomm->num_rows() > 0) ? (int)$tcomm->row()->total : 0;
                    $ci->db->order_by("created_at", "DESC");
                    $ci->db->limit(10, 0);
                    $tcmnt = $ci->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("job_comment", "job_id = '".$r->id."' AND parent IS NULL");
                    if($tcmnt && $tcmnt->num_rows() > 0){
                        foreach($tcmnt->result() as $tcv){
                            $users = [];
                            $avatar = base_url("contents/assets/img/user.png");
                            if(!empty($tcv->is_moderator)) $users = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("contents/assets/img/admin.png"));
                            else{
                                $vusr = $ci->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$tcv->user));
                                if($vusr && $vusr->num_rows() > 0) $users = array("id"=>$vusr->row()->id, "name"=>$vusr->row()->name, "avatar"=>(string)$vusr->row()->profile_picture_url);
                            }

                            if(!empty($users)){
                                $editable = false;
                                if($tcv->user == $sforce_id) $editable = true;
                                $tcv->user = $users;
                                $tcv->created_at = waktu_lalu($tcv->created_at);
                                $tcv->editable = $editable;
                                $ci->db->order_by("id", "DESC");
                                $trpt = $ci->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("job_comment", array("job_id"=>$r->id, "parent"=>$tcv->id));
                                $tcv->total_reply = ($trpt && $trpt->num_rows() > 0) ? (double)$trpt->num_rows() : 0;
                                $latest_reply = null;
                                if($trpt && $trpt->num_rows() > 0){
                                    $rply = $trpt->row();
                                    $user_rep = [];
                                    if(!empty($rply->is_moderator)) $user_rep = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
                                    else{
                                        $vusrep = $ci->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$rply->user));
                                        if($vusrep && $vusrep->num_rows() > 0) $user_rep = array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "avatar"=>(string)$vusrep->row()->profile_picture_url);
                                    }

                                    if(!empty($user_rep)){
                                        $rep_ed = false;
                                        $rply->user = $user_rep;
                                        $rply->editable = $rep_ed;
                                        unset($rply->is_moderator);
                                        $latest_reply = $rply;
                                    }
                                }
                                $tcv->latest_reply = $latest_reply;
                                unset($tcv->is_moderator);
                                $comments[] = $tcv;
                            }
                        }
                    }
                }
                $r->comments = $comments;
                unset($r->status);
                unset($r->job_title);
                unset($r->principal_id);
                $resps[] = $r;
            }
        }
        return $resps;
    }
}

if(!function_exists('resource_job_single')){
    function resource_job_single($r, $sforce_id='', $data_apply=null){
        $ci =& get_instance();
        if(!isset($ci->job)) $ci->load->model("M_Jobs", "job");
        if(!isset($ci->principal)) $ci->load->model("M_Principal", "principal");
        if(!isset($ci->region)) $ci->load->model("M_Region", "region");
        $resps = [];

        if(!empty($r)){
                $designation = $ci->job->info_position($r->job_position_id);
                $r->job_position = $designation;
                if(isset($r->is_featured)) $r->is_featured = (!empty($r->is_featured)) ? true : false;
                unset($r->job_position_id);
                // get job type
                // $job_type = $ci->job->info($r->job_type);
                // $r->job_type = $;
                // get date
                if(isset($r->date_of_closing)) $r->date_of_closing = date_id($r->date_of_closing, "j F Y");
                if(isset($r->created_at)) $r->created_at = date_id($r->created_at, "j F Y");
                $cits = $ci->region->findCityCond(['city_code'=>$r->city_code], 'city_code as id, city_name as name');
                $r->city = $cits;
                unset($r->city_code);
                /* get job status*/
                // if(isset($r->status)){
                //     if($r->status==1): $r->status = 'published'; else: $r->status = 'draft'; endif;
                // }
                $jcat = $ci->job->single_category("id IN(SELECT job_category_id FROM job_category_mapping WHERE job_id = '".$r->id."')", "id, name");
                $r->category = $jcat;
                $r->image = (isset($r->image) && !empty($r->image)) ? $r->image : "https://placekitten.com/600/400";

                $p_company = $ci->principal->find($r->principal_id, "id, name, company_name,  COALESCE(company_info, '') as descripton, logo_url as image_url");
                if(!empty($p_company)){
                    if(!isset($p_company->image_url) || (isset($p_company->image_url) && empty($p_company->image_url))){
                        $p_company->image_url = "https://cdn-ptf.pintap.id/static/company.png";
                    }
                    if(isset($p_company->company_name)){
                        $p_company->name = $p_company->company_name;
                    }
                    $galleries = [];
                    $gall = $ci->principal->galleries(['principal_id'=>$p_company->id], 'image_url');
                    if(!empty($gall)){
                        foreach($gall as $gal){
                            $galleries[] = $gal->image_url;
                        }
                    }
                    $p_company->gallery = $galleries;
                    if(isset($p_company->created_at)) unset($p_company->created_at);
                    if(isset($p_company->updated_at)) unset($p_company->updated_at);
                }
                $r->principal = $p_company;
                $isbook = $isapply = false;
                $appdata = null;
                $tlike = $ci->db->select("COALESCE(COUNT(id), 0) as total_like")->get_where("job_react_user", array("job_id"=>$r->id));
                $is_like = false;
                $total_comment = $total_like = 0;
                if(!empty($sforce_id)){
                    $sqlike = $ci->db->select("id")->get_where("job_react_user", array("job_id"=>$r->id, "created_by"=>$sforce_id));
                    $is_like = ($sqlike && $sqlike->num_rows() > 0) ? true : false;
                    $book = $ci->job->sales_bookmark(["job_id"=>$r->id, "sales_id"=>$sforce_id]);
                    $isbook = (isset($book->id)) ? true : $isbook;
                    $apply = $ci->job->check_apply_job($r->id, $sforce_id);
                    $isapply = ($apply && $apply->num_rows() > 0) ? true : $isapply;
                    if(empty($data_apply)) $appdata = $ci->job->sales_applied(["sales_force_id"=>$sforce_id, "job_id"=>$r->id, 'status !='=>'5'], "id, status, job_resume as resume, job_letter as message, remarks, interview_expired, created_at, updated_at");
                    else $appdata = $data_apply;
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
                        $appdata->updated_at = (string)$appdata->updated_at;
                        $appdata->status = $dstat;
                        $appdata->interview_expired = (string)$appdata->interview_expired;
                    }
                    if(isset($appdata->created_at)) $appdata->created_at = date_id($appdata->created_at, "j F Y");
                    //check updated at
                    if(isset($appdata->updated_at) && !empty($appdata->updated_at)){
                        $appdata->updated_at = date_id($appdata->updated_at, "j F Y");
                    }
                }
                $r->application_data = $appdata;
                $r->is_bookmark = $isbook;
                $r->is_applied = $isapply;
                $comments = [];
                if(isset($r->id)){
                    $tlike = $ci->db->select("COALESCE(COUNT(id), 0) as total_like")->get_where("job_react_user", array("job_id"=>$r->id));
                    $r->total_like = ($tlike && $tlike->num_rows() > 0) ? (int)$tlike->row()->total_like : 0;
                    $is_like = false;
                    if(!empty($sforce_id)){
                        $sqlike = $ci->db->select("id")->get_where("job_react_user", array("job_id"=>$r->id, "created_by"=>$sforce_id));
                        $is_like = ($sqlike && $sqlike->num_rows() > 0) ? true : false;
                    }
                    $ci->db->order_by("created_at", "DESC");
                    $ci->db->limit(10, 0);
                    $tcmnt = $ci->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("job_comment", "job_id = '".$r->id."' AND parent IS NULL");
                    if($tcmnt && $tcmnt->num_rows() > 0){
                        foreach($tcmnt->result() as $tcv){
                            $users = [];
                            $avatar = base_url("contents/assets/img/user.png");
                            if(!empty($tcv->is_moderator)) $users = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("contents/assets/img/admin.png"));
                            else{
                                $vusr = $ci->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$tcv->user));
                                if($vusr && $vusr->num_rows() > 0) $users = array("id"=>$vusr->row()->id, "name"=>$vusr->row()->name, "avatar"=>(string)$vusr->row()->profile_picture_url);
                            }

                            if(!empty($users)){
                                $editable = false;
                                if($tcv->user == $sforce_id) $editable = true;
                                $tcv->user = $users;
                                $tcv->created_at = waktu_lalu($tcv->created_at);
                                $tcv->editable = $editable;
                                $ci->db->order_by("id", "DESC");
                                $trpt = $ci->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("job_comment", array("job_id"=>$r->id, "parent"=>$tcv->id));
                                $tcv->total_reply = ($trpt && $trpt->num_rows() > 0) ? (double)$trpt->num_rows() : 0;
                                $latest_reply = null;
                                if($trpt && $trpt->num_rows() > 0){
                                    $rply = $trpt->row();
                                    $user_rep = [];
                                    if(!empty($rply->is_moderator)) $user_rep = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
                                    else{
                                        $vusrep = $ci->db->select("id, sales_name as name, profile_picture_url")->get_where("sales_force", array("id"=>$rply->user));
                                        if($vusrep && $vusrep->num_rows() > 0) $user_rep = array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "avatar"=>(string)$vusrep->row()->profile_picture_url);
                                    }

                                    if(!empty($user_rep)){
                                        $rep_ed = false;
                                        $rply->user = $user_rep;
                                        $rply->editable = $rep_ed;
                                        unset($rply->is_moderator);
                                        $latest_reply = $rply;
                                    }
                                }
                                $tcv->latest_reply = $latest_reply;
                                unset($tcv->is_moderator);
                                $comments[] = $tcv;
                            }
                        }
                    }
                    $total_like = ($tlike && $tlike->num_rows() > 0) ? (int)$tlike->row()->total_like : 0;
                    $tcomm = $ci->db->select("COALESCE(COUNT(id), 0) as total")->get_where("job_comment", array("job_id"=>$r->id));
                    $total_comment = ($tcomm && $tcomm->num_rows() > 0) ? (int)$tcomm->row()->total : 0;
                }
                $r->total_like = $total_like;
                $r->is_like = $is_like;
                $r->total_comment = $total_comment;
                $r->comments = $comments;
                unset($r->status);
                unset($r->job_title);
                unset($r->principal_id);
                $resps = $r;
        }
        return $resps;
    }
}

if(!function_exists('resource_cert')){
    function resource_cert($arr){
        $ci =& get_instance();
        $resps = $darv = [];

        if(!empty($arr)){
            foreach($arr as $ar){
                $ar->valid_from = $ar->valid_from_date;
                $ar->valid_until = (!empty($ar->valid_until_date)) ? $ar->valid_until_date : "";
                $ar->description = (isset($ar->certificate)) ? $ar->certificate : "";
                $ar->certificate_file = (isset($ar->certificate_file)) ? (string)$ar->certificate_file : "";
                $ar->file_size = 0;
                $todate = (!empty($ar->valid_until_date)) ? $ar->valid_until_date : date("Y-m-d");
                unset($ar->sales_force_id);
                unset($ar->valid_from_date);
                unset($ar->valid_until_date);
                unset($ar->created_at);
                unset($ar->certificate);
                $resps[] = $ar;

            }
        }
        return $resps;
    }
}

if(!function_exists('resource_cert_single')){
    function resource_cert_single($ar){
        $ci =& get_instance();
        $resps = $darv = [];

        if(!empty($ar)){
                $ar->valid_from = $ar->valid_from_date;
                $ar->valid_until = (!empty($ar->valid_until_date)) ? $ar->valid_until_date : "";
                $ar->description = (isset($ar->certificate)) ? $ar->certificate : "";
                $ar->certificate_file = (isset($ar->certificate_file)) ? (string)$ar->certificate_file : "";
                $file_size = 0;
                if(isset($ar->certificate_file) && !empty($ar->certificate_file)){
                  $headers = get_headers($ar->certificate_file, true);
                  $file_size = (int)$headers['Content-Length'];
                }
                $ar->file_size = $file_size;
                $todate = (!empty($ar->valid_until_date)) ? $ar->valid_until_date : date("Y-m-d");
                unset($ar->sales_force_id);
                unset($ar->valid_from_date);
                unset($ar->valid_until_date);
                unset($ar->created_at);
                unset($ar->certificate);
                $resps = $ar;

        }
        return $resps;
    }
}
