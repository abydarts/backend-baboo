<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';
require_once APPPATH . '/libraries/REST_Controller.php';

use \Firebase\JWT\JWT;

class Sales extends BS_Controller {

    protected $uid;

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model("M_Sales", "sales");
        $this->load->model("M_Region", "region");
        $this->load->model("M_Education", "education");
        $this->auth();
        $this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
    }

    public function profile_post(){

        $message = "";
        $data = array();
        $resp = 0;

        $uid = $this->uid;

        $all_post = $this->post();

        if(isset($all_post["name"])){
            if(empty($all_post["name"])) set_response("name cannot be empty", REST_Controller::HTTP_BAD_REQUEST);
        }
        if(isset($all_post["birthdate"])){
            if(empty($all_post["birthdate"])) set_response("birthdate cannot be empty", REST_Controller::HTTP_BAD_REQUEST);
            else if(DateTime::createFromFormat('Y-m-d', $all_post["birthdate"]) == false) set_response("invalid format birthdate", REST_Controller::HTTP_BAD_REQUEST);
        }
        if(isset($all_post["ktp"])){
            if(empty($all_post["ktp"])) set_response("ktp cannot be empty", REST_Controller::HTTP_BAD_REQUEST);
            else if(!empty($all_post["ktp"])){
                $cektp = $this->sales->find_cond(["ktp"=>$all_post["ktp"], "id!="=>$this->uid], "id");
                if(isset($cektp->id)){
                    $ceklogin = $this->sales->find_login(["sales_force_id"=>$cektp->id], "sales_force_id as id");
                    if(isset($ceklogin->id)){
                        set_response("invalid identity, already registered", REST_Controller::HTTP_BAD_REQUEST);
                    }else{
                        $uid = $cektp->id;
                        $clogin = $this->sales->find_login(["sales_force_id"=>$this->uid], "phone, password");
                        if(isset($clogin->phone)){
                            $data_inslog = ['sales_force_id'=>$uid, 'phone'=>$clogin->phone, 'email'=>''];
                            if(!empty($clogin->password)) $data_inslog['password'] = $clogin->password;
                            if($this->sales->insert_login($data_inslog)){
                                  $this->db->delete("auth_token", ["user_id"=>$this->uid]);
                                  $this->db->delete("sales_force_login", ["sales_force_id"=>$this->uid]);
                                  $this->db->delete("sales_force", ["id"=>$this->uid]);
                            }
                        }
                    }
                }
            }
        }
        if(isset($all_post["current_location"])){
            if(empty($all_post["current_location"])) set_response("current location can't be empty", REST_Controller::HTTP_BAD_REQUEST);
        }
        if(isset($all_post["bio_description"])){
            if(empty($all_post["bio_description"])) set_response("bio description is required", REST_Controller::HTTP_BAD_REQUEST);
        }
        if(isset($all_post["description"])){
            if(empty($all_post["description"])) set_response("description is required", REST_Controller::HTTP_BAD_REQUEST);
        }

        $name = $this->post("name");
        $nik = $this->post("ktp");
        $birthdate = $this->post("birthdate");
        $gender = $this->post("gender");
        $identity_image = $this->post("identity_image");
        $image = $this->post("image");
        $address = $this->post("address");
        $province_id = $this->post("province");
        $city_id = $this->post("city");
        $district_id = $this->post("district");
        $subdistrict_id = $this->post("subdistrict");
        $zipcode = $this->post("zipcode");
        $current_location = $this->post("current_location");
        $bio_description = $this->post("bio_description");
        $description = $this->post("description");

        if(isset($all_post["name"]) && !empty($name)) $data_ins["sales_name"] = $name;
        if(isset($all_post["ktp"]) &&!empty($nik)) $data_ins["ktp"] = $nik;
        if(isset($all_post["birthdate"]) && !empty($birthdate)) $data_ins["birth_date"] = $birthdate;
        if(isset($all_post["address"])) $data_ins["address"] = $address;
        if(!empty($zipcode)) $data_ins["zip_code"] = $zipcode;
        if(!empty($province_id)) $data_ins["province_code"] = $province_id;
        if(!empty($city_id)) $data_ins["city_code"] = $city_id;
        if(!empty($district_id)) $data_ins["district_code"] = $district_id;
        if(!empty($subdistrict_id)) $data_ins["subdistrict_code"] = $subdistrict_id;
        if(!empty($current_location)) $data_ins["current_city_code"] = $current_location;
        if(!empty($bio_description)) $data_ins["bio_description"] = $bio_description;
        if(!empty($gender)){
            $gender = strtolower($gender);
            $gender_male = ['laki laki', 'laki-laki', 'male', 'pria'];
            $gender_female = ['perempuan', 'wanita', 'female'];

            if(in_array($gender, $gender_male)) $gender = 'pria';
            else if(in_array($gender, $gender_female)) $gender = 'wanita';

            $data_ins["gender"] = $gender;
        }

        if(!empty($image)) $data_ins["profile_picture_url"] = $image;
        if(!empty($identity_image)) $data_ins["ktp_url"] = $identity_image;

        $cek_us = $this->sales->find_cond(["id"=>$uid], "id");
        if(isset($cek_us->id)){
                  $mid = $cek_us->id;
                  if(!empty($data_ins)){
                      if($this->sales->update($data_ins, $mid)){
                          $message = "update profile success";
                          $scopes = $token = "";
                          if(isset($all_post["ktp"]) && $uid != $this->uid){
                              $date = new DateTime();
                              $jti = generateRandomString(80);
                              $tokenz['aud'] = $uid;
                              $tokenz['jti'] = $jti;
                              $tokenz['iat'] = $date->getTimestamp();
                              $tokenz['nbf'] = $date->getTimestamp();
                              $expr = $date->getTimestamp() + 60*60*24*30; //To here is to generate token for 3 days
                              $tokenz['exp'] = $expr; //To here is to generate token for 3 days
                              $tokenz['sub'] = $uid;
                              $tokenz['scopes'] = (!empty($scopes)) ? explode(" ", $scopes) : [];
                              $ins = $this->db->insert("auth_token", array("id"=>get_uuid(), "user_id"=>$uid, "hash"=>$jti, "expired_at"=>date("Y-m-d H:i:s", $expr)));
                              $token = JWT::encode($tokenz, $this->config->item('thekey'), 'RS256'); //This is the output token
                          }
                          $data["token"] = $token;
                      }else{
                          $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                          $message = "something went wrong";
                      }
                  }else{
                      $resp = REST_Controller::HTTP_BAD_REQUEST;
                      $message = "request is empty";
                  }
        }else{
                $resp = REST_Controller::HTTP_UNAUTHORIZED;
                $message = "user not registered";
        }

        set_response($message, $resp, $data);
    }

    public function expr_get(){
        $message = "";
        $data = array();
        $resp = 0;
        $data = $this->sales->find_exp_bysales($this->uid);

        set_response($message, $resp, resource_experience($data));
    }

    public function experience_post(){

        $exp = $this->post("experience");

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($exp)){
            $err_empty = $err_notdb = $errdate = 0;
            $not_indb = $wrongdate = [];
            foreach($exp as $epr){
                $epp = (object)$epr;
                if(isset($epp->designation_id) && !empty($epp->designation_id) && isset($epp->from) && isset($epp->to)){
                    $desql = $this->db->select("id")->get_where("job_position", ["id"=>$epp->designation_id]);
                    if($desql && $desql->num_rows() > 0){
                          //continue
                          if(!empty($epp->from) && !empty($epp->to)){
                              if($epp->from > $epp->to){
                                  $errdate++;
                                  $wrongdate[] = ['from_date'=>$epp->from, 'to_date'=>$epp->to];
                              }
                          }else{
                              $errdate++;
                              $wrongdate[] = ['from_date'=>$epp->from, 'to_date'=>$epp->to];
                          }
                    }else{
                        $err_notdb++;
                        $not_indb[] = $epp->designation_id;
                    }
                }else $err_empty++;
            }

            if(empty($err_empty) && empty($err_notdb) && empty($errdata)){
                $this->sales->delete_exp(['sales_force_id'=>$this->uid]);
                foreach($exp as $epr){
                    $epp = (object)$epr;
                    $designation = (isset($epp->designation_id)) ? $epp->designation_id : "";
                    $company_name = (isset($epp->company_name)) ? $epp->company_name : "";
                    $from_date = (isset($epp->from)) ? $epp->from : "";
                    $to_date = (isset($epp->to)) ? $epp->to : "";
                    $description = (isset($epp->description)) ? $epp->description : "";
                    $is_verified = (isset($epp->current_experience) && !$epp->current_experience) ? 0 : 1;
                    if(!empty($is_verified)) $to_date = "";
                    $data_ins = [
                        'sales_force_id'=>$this->uid,
                        'job_position_id'=>$designation,
                        'company_name'=>(!empty($company_name)) ? $company_name : 0,
                        'from_date'=>(!empty($from_date)) ? $from_date : "",
                        'to_date'=>(!empty($to_date)) ? $to_date : null,
                        'is_verified'=>0,
                        'description'=>(!empty($description)) ? $description : ''
                    ];

                    $this->sales->insert_exp($data_ins);
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
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "name must not be empty";
        }
        set_response($message, $resp);
    }

    public function desire_post(){

        $skill = $this->post("skill");
        $position = $this->post("position");

        $message = "";
        $data = array();
        $resp = 0;

        $err_empty = $err_empty2 = $err_notdb = $err_notdb2 = $errdate = 0;
        $not_indb = $wrongdate = [];

        if(!empty($position)){
            foreach($position as $epr){
                    if(!empty($epr)){
                        $desql = $this->db->select("id")->get_where("job_position", ["id"=>(string)$epr]);
                        if($desql && $desql->num_rows() > 0){
                              //continue
                        }else{
                            $err_notdb++;
                            $not_indb[] = $epr;
                        }
                    }else $err_empty++;
            }
        }

        if(!empty($skill)){
            foreach($skill as $eps){
                    if(!empty($eps)){
                        $desql = $this->db->select("id")->get_where("skill", ["id"=>$eps]);
                        if($desql && $desql->num_rows() > 0){
                              //continue
                        }else{
                            $err_notdb2++;
                            $not_indb2[] = $eps;
                        }
                    }else $err_empty2++;
            }
        }

        if(empty($err_empty) && empty($err_notdb) && empty($err_empty2) && empty($err_notdb2)){
                $this->sales->update(['desired_skill'=>null, 'desired_position'=>null], ['id'=>$this->uid]);
                if(!empty($skill)){
                    $skillset = implode(",", $skill);
                    $this->sales->update(['desired_skill'=>$skillset], ["id"=>$this->uid]);
                }
                if(!empty($position)){
                    $positionset = implode(",", $position);
                    $this->sales->update(['desired_position'=>$positionset], ["id"=>$this->uid]);
                }
                $message = "successfully save desire";
        }else{
                $resp = REST_Controller::HTTP_BAD_REQUEST;
                $message = "";
                if(!empty($err_empty)) $message .= "position must not be empty";
                else if(!empty($err_empty2)){
                    $message = (!empty($message)) ? $message." and " : "";
                    $message .= "skill must not be empty";
                }
                else if(!empty($err_notdb)){
                    $message = (!empty($message)) ? $message." and " : "";
                    $message .= "some of position id not in db";
                }else if(!empty($err_notdb2)){
                    $message = (!empty($message)) ? $message." and " : "";
                    $message .= "some of skill data not in db";
                }
        }

        set_response($message, $resp);
    }

    public function desires_get(){

        $message = "";
        $data = array();
        $resp = 0;

        $sales = $this->sales->find_cond(['id'=>$this->uid], 'desired_skill, desired_position');
        if(!empty($sales)){
            $skills = [];
            if(!empty($sales->desired_skill)){
                $deskill = [];
                $deskal = explode(",", $sales->desired_skill);
                foreach($deskal as $dss){
                    $deskill[] = "'".$dss."'";
                }
                $dskil = implode(",", $deskill);
                $sksql = $this->db->select("id, name")->get_where("skill", "id IN($dskil)");
                $skills = ($sksql && $sksql->num_rows() > 0) ? $sksql->result() : $skills;
            }
            $posts=[];
            if(!empty($sales->desired_position)){
                $despos = [];
                $despal = explode(",", $sales->desired_position);
                foreach($despal as $dsp){
                    $despos[] = "'".$dsp."'";
                }
                $dsps = implode(",", $despos);
                $posql = $this->db->select("id, name")->get_where("job_position", "id IN($dsps)");
                $posts = ($posql && $posql->num_rows() > 0) ? $posql->result() : $posts;
            }
            $data["skill"] = $skills;
            $data["position"] = $posts;
        }

        set_response($message, $resp, $data);
    }

    public function phone_post(){

        $phone = $this->post("phone");
        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone)){
            $firstphone = substr($phone, 0, 1);
            if($firstphone == "0"){
              $phone = ltrim($phone, "0");
              $phone = "+62".$phone;
            }
            $cek_phone = $this->sales->find_login(['phone'=>$phone, 'sales_force_id !='=>$this->uid], 'id, phone');
            if(isset($cek_phone->id)){
                      $resp = REST_Controller::HTTP_BAD_REQUEST;
                      $message = "this phone number already registered";
            }else{
                try{
                    if($this->sales->update(['phone'=>$phone], ['sales_force_id'=>$this->uid])){
                        $resp = 0;
                        $message = "update phone successful";
                    }else{
                        $resp = REST_Controller::HTTP_BAD_REQUEST;
                        $message = "something went wrong";
                    }
                }catch(Exception $e){
                  $resp = REST_Controller::HTTP_BAD_REQUEST;
                  $message = $e->getMessage();
                }
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone number is required";
        }
        set_response($message, $resp, $data);
    }

    public function password_post(){
        $posts = $this->post();
        $pass = $this->post("password");
        $cpass = $this->post("confirm_password");
        $message = "";
        $data = array();
        $resp = 0;

        if(isset($posts["old_password"])){
            if(empty($posts["old_password"])) set_response("old password is required", REST_Controller::HTTP_BAD_REQUEST);
            else{
                $cek_pass = $this->sales->find_login(["sales_force_id"=>$this->uid], 'password');
                if(isset($cek_pass->password) && !empty($cek_pass->password)){
                    if(password_verify($posts["old_password"], $cek_pass->password)){
                        //continue
                    }else set_response("wrong old password, check the old password again", REST_Controller::HTTP_BAD_REQUEST);
                }else set_response("you need to set password first before change password", REST_Controller::HTTP_BAD_REQUEST);
            }
        }

        if(!empty($pass) && !empty($cpass)){
          if($pass == $cpass){
              $hash_pass = password_hash($pass, PASSWORD_DEFAULT);
              $data_ins = ['password'=>$hash_pass];
              $cek_us = $this->sales->find_login(["sales_force_id"=>$this->uid], 'sales_force_id as id, password');
              if(isset($cek_us->id)){
                    $mid = $cek_us->id;
                    /* mulai comment disini */
                    $number = preg_match('@[0-9]@', $pass);
                    if(empty($cek_us->password) || !isset($posts["old_password"])){
                        $this->sales->update(['active'=>true], ['id'=>$this->uid]);
                        $danot = notif_backoffice($this->uid);
                        // if(isset($danot["message"])) $data = $danot;
                    }
                    // $uppercase = preg_match('@[A-Z]@', $pass);
                    // $lowercase = preg_match('@[a-z]@', $pass);
                    // $specialChars = preg_match('@[^\w]@', $pass);

                    if(strlen($pass) < 8 || !$number) {
                        $resp = REST_Controller::HTTP_BAD_REQUEST;
                        $message = "Password must be at least 8 characters in length and must contain at least one number, one upper case letter, one lower case letter and one special character.";
                        if(strlen($pass) < 8) $errdata["length"] = "Password must be at least 8 characters in length";
                        if(!$number) $errdata[] = "Password must contain at least one number";
                        // if(!$uppercase) $errdata[] = "Password must contain at least one upper case letter";
                        // if(!$lowercase) $errdata[] = "Password must contain at least one lower case letter";
                        // if(!$specialChars) $errdata[] = "Password must contain at least one special character";
                        if(isset($errdata)) $data=['error'=>$errdata];
                    } else {
                      /* tutup comment */

                        if($this->sales->update_login($data_ins, ["sales_force_id"=>$mid])){
                            $message = "update password success";
                        }else{
                            $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                            $message = "something went wrong";
                        }
                    } //ini juga bakalan di koment nantinya
              }else{
                  $resp = REST_Controller::HTTP_UNAUTHORIZED;
                  $message = "sales not registered";
              }
          }else{
              $resp = REST_Controller::HTTP_BAD_REQUEST;
              $message = "miss-match password and confirm password";
          }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "password and confirm password are required";
        }
        set_response($message, $resp, $data);
    }

    public function info_get($sid=''){
        $sid = (!empty($sid)) ? $sid : $this->uid;
        $selectinfo = "id, sales_name, COALESCE(profile_picture_url, '') as profile_picture_url, COALESCE(info_description, '') as about_me, COALESCE(keywords, '') as keywords, COALESCE(ktp, '') as ktp, COALESCE(birth_date, '1970-01-01') as birth_date, gender, join_date, COALESCE(address, '') as address, province_code, city_code, district_code, subdistrict_code, COALESCE(zip_code, '') as zip_code, COALESCE(desired_skill, '') as desired_skill, COALESCE(desired_position, '') as desired_position, COALESCE(bio_description, '') as bio_description, COALESCE(current_city_code, '') as current_location, COALESCE(resume, '') as resume, COALESCE(application, '') as application, '' as resume_size, COALESCE(facebook, '') as facebook, COALESCE(instagram, '') as instagram, COALESCE(tiktok, '') as tiktok, COALESCE(twitter, '') as twitter";
        $motor = $this->sales->find_cond(['id'=>$sid], $selectinfo);

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($motor)){
            $ar = $motor;
            $user_rate = 0;
            $rated = $this->db->select("avg(rate) as user_rate")->get_where("job_application_rate", ["user_id"=>$sid]);
            if($rated && $rated->num_rows() > 0){
                $user_rate = (double)$rated->row()->user_rate;
            }
            $ar->rate = round($user_rate, 1);
            $ar->fullrate = 5;
            $allrate = [];
            $total_rate = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", ["user_id"=>$sid]);
            $trate5 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND rate = 5");
            $total_rate5 = ($trate5 && $trate5->num_rows() > 0) ? (int)$trate5->row()->total : 0;
            $trate4 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 4 AND rate < 5)");
            $total_rate4 = ($trate4 && $trate4->num_rows() > 0) ? (int)$trate4->row()->total : 0;
            $trate3 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 3 AND rate < 4)");
            $total_rate3 = ($trate3 && $trate3->num_rows() > 0) ? (int)$trate3->row()->total : 0;
            $trate2 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 2 AND rate < 3)");
            $total_rate2 = ($trate2 && $trate2->num_rows() > 0) ? (int)$trate2->row()->total : 0;
            $trate1 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 1 AND rate < 2)");
            $total_rate1 = ($trate1 && $trate1->num_rows() > 0) ? (int)$trate1->row()->total : 0;
            $total_rating = ($total_rate && $total_rate->num_rows() > 0) ? $total_rate->row()->total : 0;
            $pcrate5 = $pcrate4 = $pcrate3 = $pcrate2 = $pcrate1 = 0;
            if($total_rating > 0){
                $pcrate5 = round(($total_rate5 / $total_rating) * 100, 2);
                $pcrate4 = round(($total_rate4 / $total_rating) * 100, 2);
                $pcrate3 = round(($total_rate3 / $total_rating) * 100, 2);
                $pcrate2 = round(($total_rate2 / $total_rating) * 100, 2);
                $pcrate1 = round(($total_rate2 / $total_rating) * 100, 2);
            }
            $grates = [
                'rate-5' => ['label'=>'Luar Biasa', 'total_rate'=>$total_rate5, 'percent_rate'=>$pcrate5],
                'rate-4' => ['label'=>'Sangat Baik', 'total_rate'=>$total_rate4, 'percent_rate'=>$pcrate4],
                'rate-3' => ['label'=>'Baik', 'total_rate'=>$total_rate3, 'percent_rate'=>$pcrate3],
                'rate-2' => ['label'=>'Kurang', 'total_rate'=>$total_rate2, 'percent_rate'=>$pcrate2],
                'rate-1' => ['label'=>'Buruk', 'total_rate'=>$total_rate1, 'percent_rate'=>$pcrate1]
            ];
            $ar->total_rating = ($total_rate && $total_rate->num_rows() > 0) ? (int)$total_rate->row()->total : 0;
            $allrated = $this->db->limit(2, 0)->order_by("created_at", "desc")->select("created_by, rate as user_rate, rate_comment, created_at")->get_where("job_application_rate", ["user_id"=>$sid]);
            if($allrated && $allrated->num_rows() > 0){
                foreach($allrated->result() as $alrat){
                    $princip = $this->db->select("id, name, COALESCE(logo_url, 'https://cdn-ptf.pintap.id/static/company.png') as image_url")->get_where("principal", ["id"=>$alrat->created_by]);
                    $princ = ($princip && $princip->num_rows() > 0) ? $princip->row() : null;
                    $gruprate = "rate-".floor($alrat->user_rate);
                    $rcomm = [];
                    if(!empty($alrat->rate_comment)){
                      $racom = json_decode($alrat->rate_comment, true);
                      foreach($racom as $rac){
                          $grc = $this->db->select("id, name")->get_where("rate_comment", ["id"=>$rac]);
                          if($grc && $grc->num_rows() > 0) $rcomm[] = $grc->row();
                      }
                    }
                    $allrate[] = [
                        'principal' => $princ,
                        'rate'=>(double)$alrat->user_rate,
                        'group' => $gruprate,
                        'label' => $grates[$gruprate]['label'],
                        'created_at' => $alrat->created_at,
                        'comment' => $rcomm
                    ];
                }
            }
            $ar->all_rate = $allrate;
            $ar->group_rate = $grates;
            if(!empty($ar->profile_picture_url)){
                if(strpos($ar->profile_picture_url, "placekitten.com") !== false){
                    $ar->profile_picture_url = "https://cdn-ptf.pintap.id/static/pintap-force-icon.png";
                }
            }else{
                $ar->profile_picture_url = "https://cdn-ptf.pintap.id/static/pintap-force-icon.png";
            }

            $logins = $this->sales->find_login(['sales_force_id'=>$sid], 'phone');
            $ar->phone = (isset($logins->phone)) ? $logins->phone : '';

            $sdists = $this->region->findSubdistCond(['subdistrict_code'=>$ar->subdistrict_code], "subdistrict_code as id, subdistrict_name as name, COALESCE(zip_code, '') as zip_code");
            $ar->subdistrict = $sdists;
            unset($ar->subdistrict_code);

            $dists = $this->region->findDistCond(['district_code'=>$ar->district_code], 'district_code as id, district_name as name');
            $ar->district = $dists;
            unset($ar->district_code);

            $cits = $this->region->findCityCond(['city_code'=>$ar->city_code], 'city_code as id, city_name as name');
            $ar->city = $cits;
            unset($ar->city_code);

            if(!empty($ar->resume)){
                $headers = get_headers($ar->resume, true);
                $ar->resume_size = (int)$headers['Content-Length'];
            }else $ar->resume_size = 0;

            $provs = $this->region->findProvCond(['province_code'=>$ar->province_code], 'province_code as id, province_name as name');
            $ar->province = $provs;
            unset($ar->province_code);

            $citloc = $this->region->findCityCond(['city_code'=>$ar->current_location], 'city_code as id, city_name as name');
            if(isset($citloc->name)){
              $type = (strpos($citloc->name, "Kota ") !== false) ? "Kota" : "Kabupaten";
              $citname = str_replace("Kota ", "", $citloc->name);
              $citname = str_replace("Kabupaten ", "", $citname);
              $citloc->name = $citname;
              $citloc->type = $type;
              $citloc->country = "Indonesia";
            }
            $ar->current_location = $citloc;
            // unset($ar->current_);

            $expr = resource_experience($this->sales->find_exp_bysales($sid), true);
            $ar->experience = $expr['experience'];
            $ar->summary_experience = $expr['post_duration'];
            $ar->total_experience = (isset($expr["total_experience"])) ? $expr["total_experience"] : "";
            $skills = [];
            if(!empty($motor->desired_skill)){
                $deskill = [];
                $deskal = explode(",", $motor->desired_skill);
                foreach($deskal as $dss){
                    $deskill[] = "'".$dss."'";
                }
                $dskil = implode(",", $deskill);
                $sksql = $this->db->select("id, name")->get_where("skill", "id IN($dskil)");
                $skills = ($sksql && $sksql->num_rows() > 0) ? $sksql->result() : $skills;
            }
            $posts=[];
            if(!empty($motor->desired_position)){
                $despos = [];
                $despal = explode(",", $motor->desired_position);
                foreach($despal as $dsp){
                    $despos[] = "'".$dsp."'";
                }
                $dsps = implode(",", $despos);
                $posql = $this->db->select("id, name")->get_where("job_position", "id IN($dsps)");
                $posts = ($posql && $posql->num_rows() > 0) ? $posql->result() : $posts;
            }
            $ar->desired_skill = $skills;
            $ar->desired_position = $posts;
            $keywords=[];
            if(!empty($motor->keywords)){
                $deskey = [];
                $deskeys = explode(",", $motor->keywords);
                foreach($deskeys as $dsk){
                    $deskey[] = "'".$dsk."'";
                }
                $dsks = implode(",", $deskey);
                $keysql = $this->db->select("id, name")->get_where("keyword", "id IN($dsks)");
                $keywords = ($keysql && $keysql->num_rows() > 0) ? $keysql->result() : $keysql;
            }
            $ar->keywords = $keywords;
            $skilluser = [];
            $sksqlu = $this->db->select("skill.id as id, name")->join("sales_force_skill", "sales_force_skill.skill_id = skill.id")->get_where("skill", ["sales_force_id"=>$sid]);
  					$skillus = ($sksqlu && $sksqlu->num_rows() > 0) ? $sksqlu->result() : [];
  			    $ar->user_skill = $skillus;
            $vehicle = [];
            $sksqlv = $this->db->select("vehicle.id as id, name")->join("sales_force_vehicle", "sales_force_vehicle.vehicle_id = vehicle.id")->get_where("vehicle", ["sales_force_id"=>$sid]);
  					$vhius = ($sksqlv && $sksqlv->num_rows() > 0) ? $sksqlv->result() : [];
  			    $ar->user_vehicle = $vhius;
            $identity = [];
            $sksqli = $this->db->select("identity.id as id, name")->join("sales_force_identity", "sales_force_identity.identity_id = identity.id")->get_where("identity", ["sales_force_id"=>$sid]);
  					$idius = ($sksqli && $sksqli->num_rows() > 0) ? $sksqli->result() : [];
  			    $ar->user_identity = $idius;
            $sksqll = $this->db->select("language.id as id, name")->join("sales_force_language", "sales_force_language.lang_id = language.id")->get_where("language", ["sales_force_id"=>$sid]);
  					$langius = ($sksqll && $sksqll->num_rows() > 0) ? $sksqll->result() : [];
  			    $ar->user_language = $langius;
            $education = [];
            $eksql = $this->db->order_by("end_date", "desc")->select("id, education_id as education, institution, COALESCE(sales_force_education.subject, '') as subject, start_date as from, end_date as to")->get_where("sales_force_education", ["sales_force_id"=>$sid]);
  					$educs = ($eksql && $eksql->num_rows() > 0) ? $eksql->result() : null;
  			    if(!empty($educs)){
  	            foreach($educs as $ess){
  											$edu = $this->education->find_cond(['id'=>$ess->education], 'id, name');
  											$ess->education = (isset($edu->id)) ? $edu : null;
  											$education[] = $ess;
  	            }
  	        }
            $ar->user_education = $education;
            $data = $ar;
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "user not found or already blocked";
        }

        set_response($message, $resp, $data);
    }

    public function rate_get($sid=''){
        $sid = (!empty($sid)) ? $sid : $this->uid;
        $motor = $this->sales->find_cond(['id'=>$sid], "id");
        $rpp = (!empty($this->get("rpp"))) ? (int)$this->get("rpp") : 20;
        $page = (!empty($this->get("page"))) ? (int)$this->get("page") : 1;
        $group = (!empty($this->get("group"))) ? (int)$this->get("group") : '';
        $message = "";
        $limit = (!empty($rpp)) ? $rpp : 20;
        $page = (!empty($page)) ? $page : 1;
        $data = $meta = array();
        $resp = 0;

        if(!empty($motor)){
            $ar = $motor;
            $stot = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", ["user_id"=>$sid]);
            $rated = $this->db->select("avg(rate) as user_rate")->get_where("job_application_rate", ["user_id"=>$sid]);
            $user_rate = ($rated && $rated->num_rows() > 0) ? (double)$rated->row()->user_rate : 0;
            $ar->rate = round($user_rate, 1);
            $ar->fullrate = 5;
            $allrate = [];
            $total_data = ($stot && $stot->num_rows() > 0) ? (int)$stot->row()->total : 0;
            $total_page = ceil($total_data / $limit);
            $next_page = ($page >= $total_page) ? $total_page : ($page + 1);
            $prev_page = ($page <= 1) ? 1 : ($page - 1);
            $total_rate = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", ["user_id"=>$sid]);
            $trate5 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND rate = 5");
            $total_rate5 = ($trate5 && $trate5->num_rows() > 0) ? (int)$trate5->row()->total : 0;
            $trate4 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 4 AND rate < 5)");
            $total_rate4 = ($trate4 && $trate4->num_rows() > 0) ? (int)$trate4->row()->total : 0;
            $trate3 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 3 AND rate < 4)");
            $total_rate3 = ($trate3 && $trate3->num_rows() > 0) ? (int)$trate3->row()->total : 0;
            $trate2 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 2 AND rate < 3)");
            $total_rate2 = ($trate2 && $trate2->num_rows() > 0) ? (int)$trate2->row()->total : 0;
            $trate1 = $this->db->select("COUNT(id) as total")->get_where("job_application_rate", "user_id = '$sid' AND (rate >= 1 AND rate < 2)");
            $total_rate1 = ($trate1 && $trate1->num_rows() > 0) ? (int)$trate1->row()->total : 0;
            $total_rating = ($total_rate && $total_rate->num_rows() > 0) ? $total_rate->row()->total : 0;
            $pcrate5 = $pcrate4 = $pcrate3 = $pcrate2 = $pcrate1 = 0;
            if($total_rating > 0){
                $pcrate5 = round(($total_rate5 / $total_rating) * 100, 2);
                $pcrate4 = round(($total_rate4 / $total_rating) * 100, 2);
                $pcrate3 = round(($total_rate3 / $total_rating) * 100, 2);
                $pcrate2 = round(($total_rate2 / $total_rating) * 100, 2);
                $pcrate1 = round(($total_rate2 / $total_rating) * 100, 2);
            }
            $grates = [
                'rate-5' => ['label'=>'Luar Biasa', 'total_rate'=>$total_rate5, 'percent_rate'=>$pcrate5],
                'rate-4' => ['label'=>'Sangat Baik', 'total_rate'=>$total_rate4, 'percent_rate'=>$pcrate4],
                'rate-3' => ['label'=>'Baik', 'total_rate'=>$total_rate3, 'percent_rate'=>$pcrate3],
                'rate-2' => ['label'=>'Kurang', 'total_rate'=>$total_rate2, 'percent_rate'=>$pcrate2],
                'rate-1' => ['label'=>'Buruk', 'total_rate'=>$total_rate1, 'percent_rate'=>$pcrate1]
            ];
            $meta = [
                'current_page'=>$page,
                'next_page'=>$next_page,
                'prev_page'=>$prev_page,
                'total_page'=>$total_page,
                'rpp'=>$limit,
                'total_data'=>$total_data,
            ];
            $spage = ($page > 1) ? (($page - 1) * $limit) : 0;
            if(!empty($group)){
                if($group < 5){
                    $group2 = $group + 1;
                    $query = "(rate >= '$group' AND rate < '$group2')";
                }else $query = "rate = '5'";
                $this->db->where($query);
            }
            $allrated = $this->db->limit($limit, $spage)->order_by("created_at", "desc")->select("created_by, rate as user_rate, rate_comment, created_at")->get_where("job_application_rate", ["user_id"=>$sid]);
            // $message .= $this->db->last_query()." - $group";
            if($allrated && $allrated->num_rows() > 0){
                foreach($allrated->result() as $alrat){
                    $princip = $this->db->select("id, name, COALESCE(logo_url, 'https://cdn-ptf.pintap.id/static/company.png') as image_url")->get_where("principal", ["id"=>$alrat->created_by]);
                    $princ = ($princip && $princip->num_rows() > 0) ? $princip->row() : null;
                    $gruprate = floor($alrat->user_rate);
                    $rcomm = [];
                    if(!empty($alrat->rate_comment)){
                      $racom = json_decode($alrat->rate_comment, true);
                      foreach($racom as $rac){
                          $grc = $this->db->select("id, name")->get_where("rate_comment", ["id"=>$rac]);
                          if($grc && $grc->num_rows() > 0) $rcomm[] = $grc->row();
                      }
                    }
                    $allrate[] = [
                        'principal' => $princ,
                        'rate'=>(double)$alrat->user_rate,
                        'group' => "rate-".$gruprate,
                        'label_group' => $grates["rate-".$gruprate]['label'],
                        'created_at' => $alrat->created_at,
                        'comment' => $rcomm
                    ];
                    // $label = $grates[$gruprate];
                }
            }
            $ar->allrate = $allrate;
            $ar->total_rating = ($total_rate && $total_rate->num_rows() > 0) ? $total_rate->row()->total : 0;
            $ar->group_rate = $grates;
            unset($ar->id);
            $data = $ar;
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "user not found";
        }
        $ressp = array("code"=>$resp, "message"=>$message, "data"=>$data);
        if(!empty($meta)) $ressp["meta"] = $meta;
        $this->response($ressp, REST_Controller::HTTP_OK);
    }

    public function ktp_post(){

        $ktp = $this->post("ktp");

        if(empty($ktp)) set_response("ktp cannot be empty", REST_Controller::HTTP_BAD_REQUEST);
        else{
                $cektp = $this->sales->find_cond(["ktp"=>$ktp, "id!="=>$this->uid], "id");
                if(isset($cektp->id)){
                    $ceklogin = $this->sales->find_login(["sales_force_id"=>$cektp->id], "sales_force_id as id");
                    if(isset($ceklogin->id)){
                        set_response("invalid identity, already registered by other sales", REST_Controller::HTTP_BAD_REQUEST);
                    }
                }
        }


        $selectinfo = "id, sales_name, COALESCE(profile_picture_url, '') as profile_picture_url, COALESCE(info_description, '') as about_me, COALESCE(keywords, '') as keywords, COALESCE(ktp, '') as ktp, COALESCE(birth_date, '1970-01-01') as birth_date, gender, join_date, COALESCE(address, '') as address, province_code, city_code, district_code, subdistrict_code, COALESCE(zip_code, '') as zip_code, COALESCE(desired_skill, '') as desired_skill, COALESCE(desired_position, '') as desired_position, COALESCE(bio_description, '') as bio_description, COALESCE(current_city_code, '') as current_location, COALESCE(resume, '') as resume, COALESCE(application, '') as application, '' as resume_size";
        $motor = $this->sales->find_cond(['ktp'=>$ktp], $selectinfo);

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($motor)){
            $sid = $motor->id;
            $ar = $motor;
            $logins = $this->sales->find_login(['sales_force_id'=>$motor->id], 'phone');
            $ar->phone = (isset($logins->phone)) ? $logins->phone : '';

            $sdists = $this->region->findSubdistCond(['subdistrict_code'=>$ar->subdistrict_code], "subdistrict_code as id, subdistrict_name as name, COALESCE(zip_code, '') as zip_code");
            $ar->subdistrict = $sdists;
            unset($ar->subdistrict_code);

            $dists = $this->region->findDistCond(['district_code'=>$ar->district_code], 'district_code as id, district_name as name');
            $ar->district = $dists;
            unset($ar->district_code);

            $cits = $this->region->findCityCond(['city_code'=>$ar->city_code], 'city_code as id, city_name as name');
            $ar->city = $cits;
            unset($ar->city_code);

            if(!empty($ar->resume)){
                $headers = get_headers($ar->resume, true);
                $ar->resume_size = (int)$headers['Content-Length'];
            }else $ar->resume_size = 0;

            $provs = $this->region->findProvCond(['province_code'=>$ar->province_code], 'province_code as id, province_name as name');
            $ar->province = $provs;
            unset($ar->province_code);

            $citloc = $this->region->findCityCond(['city_code'=>$ar->current_location], 'city_code as id, city_name as name');
            if(isset($citloc->name)){
              $type = (strpos($citloc->name, "Kota ") !== false) ? "Kota" : "Kabupaten";
              $citname = str_replace("Kota ", "", $citloc->name);
              $citname = str_replace("Kabupaten ", "", $citname);
              $citloc->name = $citname;
              $citloc->type = $type;
              $citloc->country = "Indonesia";
            }
            $ar->current_location = $citloc;
            // unset($ar->current_);

            $expr = resource_experience($this->sales->find_exp_bysales($motor->id), true);
            $ar->experience = $expr['experience'];
            $ar->summary_experience = $expr['post_duration'];
            $ar->total_experience = (isset($expr["total_experience"])) ? $expr["total_experience"] : "";
            $skills = [];
            if(!empty($motor->desired_skill)){
                $deskill = [];
                $deskal = explode(",", $motor->desired_skill);
                foreach($deskal as $dss){
                    $deskill[] = "'".$dss."'";
                }
                $dskil = implode(",", $deskill);
                $sksql = $this->db->select("id, name")->get_where("skill", "id IN($dskil)");
                $skills = ($sksql && $sksql->num_rows() > 0) ? $sksql->result() : $skills;
            }
            $posts=[];
            if(!empty($motor->desired_position)){
                $despos = [];
                $despal = explode(",", $motor->desired_position);
                foreach($despal as $dsp){
                    $despos[] = "'".$dsp."'";
                }
                $dsps = implode(",", $despos);
                $posql = $this->db->select("id, name")->get_where("job_position", "id IN($dsps)");
                $posts = ($posql && $posql->num_rows() > 0) ? $posql->result() : $posts;
            }
            $ar->desired_skill = $skills;
            $ar->desired_position = $posts;
            $keywords=[];
            if(!empty($motor->keywords)){
                $deskey = [];
                $deskeys = explode(",", $motor->keywords);
                foreach($deskeys as $dsk){
                    $deskey[] = "'".$dsk."'";
                }
                $dsks = implode(",", $deskey);
                $keysql = $this->db->select("id, name")->get_where("keyword", "id IN($dsks)");
                $keywords = ($keysql && $keysql->num_rows() > 0) ? $keysql->result() : $keysql;
            }
            $ar->keywords = $keywords;
            $skilluser = [];
            $sksqlu = $this->db->select("skill.id as id, name")->join("sales_force_skill", "sales_force_skill.skill_id = skill.id")->get_where("skill", ["sales_force_id"=>$motor->id]);
  					$skillus = ($sksqlu && $sksqlu->num_rows() > 0) ? $sksqlu->result() : [];
  			    $ar->user_skill = $skillus;
            $data = $ar;
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "user not found or already blocked";
        }

        set_response($message, $resp, $data);
    }

    public function mini_get($sid=''){
        $sid = (!empty($sid)) ? $sid : $this->uid;
        $selectinfo = "id, sales_name, COALESCE(profile_picture_url, '') as profile_picture_url, COALESCE(bio_description, '') as bio_description, COALESCE(current_city_code, '') as current_location";
        $motor = $this->sales->find_cond(['id'=>$sid], $selectinfo);

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($motor)){
            $ar = $motor;

            $citloc = $this->region->findCityCond(['city_code'=>$ar->current_location], 'city_code as id, city_name as name');
            if(isset($citloc->name)){
              $type = (strpos($citloc->name, "Kota ") !== false) ? "Kota" : "Kabupaten";
              $citname = str_replace("Kota ", "", $citloc->name);
              $citname = str_replace("Kabupaten ", "", $citname);
              $citloc->name = $citname;
              $citloc->type = $type;
              $citloc->country = "Indonesia";
            }

            $ar->current_location = $citloc;
            // unset($ar->current_);

            $expr = resource_experience($this->sales->find_exp_bysales($sid), true);

            $ar->total_experience = (isset($expr["total_experience"])) ? $expr["total_experience"] : "";
            $data = $ar;
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "user not found or already blocked";
        }

        set_response($message, $resp, $data);
    }

    public function insight_get(){

        $motor = $this->sales->insight($this->uid);

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($motor)){
            $data = $motor;
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "sales not found or already blocked";
        }

        set_response($message, $resp, $data);
    }

    public function upload_post($type='avatar')
    {
          $id = $this->uid;
          if (!empty($_FILES['file']['name'])) {
                    $upload_path = 'contents/img/'.$type;
                    if(!file_exists($upload_path.'/')) mkdir($upload_path.'/', 0777);
                    $images = s3_upload($_FILES['file'], "sales/$type");
                    // $images = false;
                    if(!$images){
                              $resps = array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'error when uploading file, check file type or destination path');
                    }else{
                              if($type == 'identity'){
                                  $data_prd["ktp_url"] = $images;
                              }else{
                                  $data_prd["profile_picture_url"] = $images;
                              }
                              $this->sales->update($data_prd, array("id"=>$id));
                              $resps = array('code'=>0, 'message'=>'upload '.$type.' success', 'data'=>['image'=>$images]);
                    }
          }else{
              $resps =  array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'upload file failed, nothing to upload');
          }
          // $resps['env'] = $_ENV;
          $this->response($resps, REST_Controller::HTTP_OK);
    }

    public function certify_get(){
        $message = "";
        $data = array();
        $resp = 0;

        $data = $this->sales->find_cert_bysales($this->uid);

        set_response($message, $resp, resource_cert($data));
    }

    public function detailcert_get($id=''){
        $message = "";
        $data = array();
        $resp = 0;
        if(!empty($id)) $data = resource_cert_single($this->sales->find_cert(['id'=>$id]));
        else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "data certificate not found";
        }
        set_response($message, $resp, $data);
    }

    public function certificate_post($id='')
    {
          $title = $this->post('title');
          $valid_from = $this->post('valid_from');
          $valid_until = $this->post('valid_until');
          $certificate = $this->post('description');
          $organizer = $this->post('organizer');
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
              else array('code'=>REST_Controller::HTTP_INTERNAL_SERVER_ERROR, 'message'=>'something went wrong while '.$msg.' certificate');
          }else{
              $resps =  array('code'=>REST_Controller::HTTP_BAD_REQUEST, 'message'=>'subject, company and cerficate date is required');
          }
          $this->response($resps, REST_Controller::HTTP_OK);
    }

    function avatar_delete(){
          $resp = REST_Controller::HTTP_NOT_FOUND;
          $message = "";
          $data = [];
          $cek_us = $this->sales->find_cond(["id"=>$this->uid], "id, profile_picture_url");
          if(isset($cek_us->id)){
              $default_img = 'https://cdn-ptf.pintap.id/static/pintap-force-icon.png';
              $upd = $this->sales->update(['profile_picture_url'=>$default_img], ["id"=>$this->uid]);
              if($upd){
                  $resp = 0;
                  $data['profile_picture_url'] = $default_img;
              }else{
                  $resp = REST_Controller::HTTP_BAD_REQUEST;
                  $message = "something went wrong while update avatar";
              }
          }else $message = "sales not found";
          set_response($message, $resp, $data);
    }

}
