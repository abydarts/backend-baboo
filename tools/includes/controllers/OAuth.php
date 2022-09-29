<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';
require_once APPPATH . '/libraries/REST_Controller.php';

use \Firebase\JWT\JWT;


class OAuth extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model("M_Sales", "motor");
    }

    public function token_post(){
        $message = "";
        $stat = REST_Controller::HTTP_NOT_FOUND;
        $data = array();
        $user = $this->post("phone");
        $email = $this->post("email");
        $pass = $this->post("password");
        $scopes = $this->post("scopes");
        try{
            if(!empty($user) && !empty($pass)){
                  $sqs = $this->motor->find_login(["phone"=>$user], "sales_force_id as id, password");
                  if(isset($sqs->id)){
                      if(isset($sqs->password) && !empty($sqs->password)){
                          $usrow = $sqs;
                          $hash_pass = $usrow->password;
                          if(password_verify($pass, $hash_pass)){
                             //set token
                             $stat = 0;
                             $message = "";
                             $date = new DateTime();
                             $jti = generateRandomString(80);
                             $token['aud'] = $usrow->id;
                             $token['jti'] = $jti;
                             $token['iat'] = $date->getTimestamp();
                             $token['nbf'] = $date->getTimestamp();
                             $expr = $date->getTimestamp() + 60*60*24*30; //To here is to generate token for 3 days
                             $token['exp'] = $expr; //To here is to generate token for 3 days
                             $token['sub'] = $usrow->id;
                             $token['scopes'] = (!empty($scopes)) ? explode(" ", $scopes) : [];
                             $ins = $this->db->insert("auth_token", array("id"=>get_uuid(), "user_id"=>$usrow->id, "hash"=>$jti, "expired_at"=>date("Y-m-d H:i:s", $expr)));
                             $data['token'] = JWT::encode($token, $this->config->item('thekey'), 'RS256'); //This is the output token
                             $data['expires'] = 60*60*24*30;

                             // $devid = $this->input->get_request_header('Vid-TapOn');
                             // $fcmid = $this->input->get_request_header('Gid-TapOn');
                             // if (!empty($devid) && !empty($fcmid)) {
                             //     $data_insert = array(
                             //         "device_id" => base64_decode($devid),
                             //         "fcm_id" => base64_decode($fcmid)
                             //     );
                             //     $cekDuid = $this->db->select("id, device_id, login_count")->get_where("motorist_devices", array("motorist_id"=>$usrow->id, "device_id"=>base64_decode($devid)));
                             //     if($cekDuid && $cekDuid->num_rows() > 0){
                             //         $did = $cekDuid->row()->id;
                             //         $lcount = $cekDuid->row()->login_count + 1;
                             //         $data_insert["login_count"] = $lcount;
                             //         $insd = $this->db->update("motorist_devices", $data_insert, array("id"=>$did));
                             //     }else{
                             //         $data_insert["motorist_id"] = $usrow->id;
                             //         $data_insert["login_count"] = 1;
                             //         $data_insert["id"] = get_uuid();
                             //         $insd = $this->db->insert("motorist_devices", $data_insert);
                             //     }
                             //     // checking
                             // }
                          }else{
                              $stat = REST_Controller::HTTP_BAD_REQUEST;
                              $message = "password doesn't match with username";
                          }
                    }else{
                        $stat = REST_Controller::HTTP_PARTIAL_CONTENT;
                        $message = 'password not set, set password first';
                    }
                  }else{
                      $message = 'phone not registered';
                  }
            }else{
                $stat = REST_Controller::HTTP_BAD_REQUEST;
                $message = "username or password can't be empty";
            }
        }
        catch (Exception $catchMessage) {
            $message = "error : ".$catchMessage->getMessage()." ".FCPATH;
        }
        set_response($message, $stat, $data);
    }

    public function otp_post(){

        $phone = $this->post("phone");
        $channel = $this->post("channel");

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone)){

            $cek_us = $this->motor->find_login(["phone"=>$phone], "id");
            if(isset($cek_us->id)){
                $ins = $cek_us->id;
            }else{
                $ins = $this->motor->insert(['phone'=>$phone, 'status'=>0, 'area_operational'=>'eb1d3160-0b88-4577-9644-fe8f48d12d92']);
            }

            if($ins && !empty($ins)){
                $otp = mt_rand(100000, 999999);
                $data_otp = array("id"=>get_uuid(), "activity"=>"auth-otp", "user_id"=>$ins, "otp"=>$otp, "channel"=>$channel, "expired"=>date("Y-m-d H:i:s", strtotime("+10 minutes")));
                if($this->db->insert("auth_otp", $data_otp)){
                    //put_send_message_otp
                    $devid = $this->input->get_request_header('Vid-TapOn');
                    $fcmid = $this->input->get_request_header('Gid-TapOn');
                    if (!empty($devid) && !empty($fcmid)) {
                        $data_insert = array(
                            "device_id" => base64_decode($devid),
                            "fcm_id" => base64_decode($fcmid)
                        );
                        $this->db->select("id, device_id, login_count");
                        $cekDuid = $this->db->get_where("motorist_devices", array("motorist_id"=>$ins, "device_id"=>base64_decode($devid)));
                        if($cekDuid && $cekDuid->num_rows() > 0){
                            $did = $cekDuid->row()->id;
                            $insd = $this->db->update("motorist_devices", $data_insert, array("id"=>$did));
                        }else{
                            $data_insert["motorist_id"] = $ins;
                            $data_insert["login_count"] = 0;
                            $data_insert["id"] = get_uuid();
                            $insd = $this->db->insert("motorist_devices", $data_insert);
                        }
                        // checking
                        setpush_notif($ins, 'your OTP is '.$otp, 'send-otp', ['uid'=>$ins, 'otp'=>$otp]);
                    }
                    $message = "otp has been sent";
                }else{
                    $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    $message = "something went wrong while request otp";
                }
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to send otp";
        }
        set_response($message, $resp);
    }

    public function look_otp_get(){

        $phone = $this->get("phone");
        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone)){
            $firstphone = substr($phone, 0, 1);
            if($firstphone == "0"){
              $phone = ltrim($phone, "0");
              $phone = "+62".trim($phone);
            }else if($firstphone != "+"){
                $phone = "+".trim($phone);
            }
            $oztp = $this->db->order_by("expired_at", "desc")->select("otp, expired_at")->get_where("auth_otp", ['user_id'=>$phone]);
            if($oztp && $oztp->num_rows() > 0){
                $now = date("Y-m-d H:i:s");
                if($oztp->row()->expired_at < $now){
                    $resp = REST_Controller::HTTP_BAD_REQUEST;
                    $message = "otp has been expired please request again";
                    $data = [
                        "expired"=>$oztp->row()->expired_at,
                        "now"=>$now
                    ];
                    set_response($message, $resp, $data);
                }else{
                    $resp = 0;
                    $message = "";
                    $data["otp"] = $oztp->row()->otp;
                }
            }else{
                $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id");
                if(isset($cek_us->id)){
                    $id = $cek_us->id;
                    $oztp = $this->db->order_by("expired_at", "desc")->select("otp, expired_at")->get_where("auth_otp", ['user_id'=>$id]);
                    if($oztp && $oztp->num_rows() > 0){
                              $now = date("Y-m-d H:i:s");
                              if($oztp->row()->expired_at < $now){
                                  $resp = REST_Controller::HTTP_BAD_REQUEST;
                                  $message = "otp has been expired please request again";
                                  $data = [
                                      "expired"=>$oztp->row()->expired_at,
                                      "now"=>$now
                                  ];
                                  set_response($message, $resp, $data);
                              }else{
                                  $resp = 0;
                                  $message = "";
                                  $data["otp"] = $oztp->row()->otp;
                              }
                    }else{
                        $resp = REST_Controller::HTTP_UNAUTHORIZED;
                        $message = "otp not found please request otp first";
                        set_response($message, $resp, $data);
                    }
                }else{
                    $resp = REST_Controller::HTTP_NOT_FOUND;
                    $message = "phone not found $phone";
                    set_response($message, $resp, $data);
                }
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to check otp";
            set_response($message, $resp, $data);
        }
        $this->response($data, 200);
    }

    public function check_otp_post(){

        $phone = $this->post("phone");
        $otp = $this->post("otp");

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone) && !empty($otp)){
            $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id");
            if(isset($cek_us->id)){
                $id = $cek_us->id;
                $oztp = $this->db->order_by("expired", "desc")->select("otp, expired_at")->get_where("auth_otp", ['user'=>$id]);
                if($oztp && $oztp->num_rows() > 0){
                      // if($otp == $oztp->row()->otp){
                          if($oztp->row()->expired < date("Y-m-d H:i:s")){
                              $resp = REST_Controller::HTTP_BAD_REQUEST;
                              $message = "otp has been expired please request again ".$oztp->row()->expired_at;
                          }else{
                              $resp = 0;
                              $message = "";
                              $date = new DateTime();
                              $jti = generateRandomString(80);
                              $token['aud'] = $id;
                              $token['jti'] = $jti;
                              $token['iat'] = $date->getTimestamp();
                              $token['nbf'] = $date->getTimestamp();
                              $expr = $date->getTimestamp() + 60*60*24*30; //To here is to generate token for 3 days
                              $token['exp'] = $expr; //To here is to generate token for 3 days
                              $token['sub'] = $id;
                              $token['scopes'] = (!empty($scopes)) ? explode(" ", $scopes) : [];
                              $ins = $this->db->insert("auth_token", array("id"=>get_uuid(), "user"=>$id, "hash"=>$jti, "expired_at"=>date("Y-m-d H:i:s", $expr)));
                              $data['token'] = JWT::encode($token, $this->config->item('thekey'), 'RS256'); //This is the output token
                              $data['expires'] = 60*60*24*30;
                              $this->db->delete("auth_otp", ["user"=>$id]);
                          }
                      // }else{
                      //     $resp = REST_Controller::HTTP_UNAUTHORIZED;
                      //     $message = "missmatch otp";
                      // }
                }else{
                    $resp = REST_Controller::HTTP_UNAUTHORIZED;
                    $message = "please request otp first";
                }
            }else{
              $resp = REST_Controller::HTTP_NOT_FOUND;
              $message = "user not found";
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to send otp";
        }
        set_response($message, $resp, $data);
    }

    public function confirm_otp_post(){

        $phone = $this->post("phone");
        $otp = $this->post("otp");

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone) && !empty($otp)){
            $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id");
            if(isset($cek_us->id)){
                $id = $cek_us->id;
                $oztp = $this->db->order_by("created_at", "desc")->select("otp, expired_at")->get_where("auth_otp", ['user_id'=>$id]);
                if($oztp && $oztp->num_rows() > 0){
                      if($otp == $oztp->row()->otp){
                          if($oztp->row()->expired_at < date("Y-m-d H:i:s")){
                              $resp = REST_Controller::HTTP_BAD_REQUEST;
                              $message = "otp has been expired please request again, expired ".$oztp->row()->expired.", now ".date("Y-m-d H:i:s");
                          }else{
                              $resp = 0;
                              $message = "";
                              $date = new DateTime();
                              $jti = generateRandomString(80);
                              $token['aud'] = $id;
                              $token['jti'] = $jti;
                              $token['iat'] = $date->getTimestamp();
                              $token['nbf'] = $date->getTimestamp();
                              $expr = $date->getTimestamp() + 60*60*24*30; //To here is to generate token for 3 days
                              $token['exp'] = $expr; //To here is to generate token for 3 days
                              $token['sub'] = $id;
                              $token['scopes'] = (!empty($scopes)) ? explode(" ", $scopes) : [];
                              $ins = $this->db->insert("auth_token", array("id"=>get_uuid(), "user_id"=>$id, "hash"=>$jti, "expired_at"=>date("Y-m-d H:i:s", $expr)));
                              $data['token'] = JWT::encode($token, $this->config->item('thekey'), 'RS256'); //This is the output token
                              $data['expires'] = 60*60*24*30;
                              $this->db->delete("auth_otp", ["user_id"=>$id]);
                          }
                      }else{
                          $resp = REST_Controller::HTTP_UNAUTHORIZED;
                          $message = "missmatch otp";
                      }
                }else{
                    $resp = REST_Controller::HTTP_UNAUTHORIZED;
                    $message = "please request otp first";
                }
            }else{
              $resp = REST_Controller::HTTP_NOT_FOUND;
              $message = "user not found";
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to send otp";
        }
        set_response($message, $resp, $data);
    }

    public function check_user_post(){

        $phone = $this->post("phone");

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone)){
            $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id, password");
            if(isset($cek_us->id)){
                $cek_sale = $this->motor->find_cond(['id'=>$cek_us->id], 'id, sales_name');
                $id = $cek_us->id;
                $stat = 0;
                $pass = $cek_us->password;
                $name = (isset($cek_sale->sales_name)) ? (string)$cek_sale->sales_name : '';
                if(empty($stat)){
                    if(empty($name) && empty($pass)) $stat = 2;
                    else if(empty($name) && !empty($pass)) $stat = 2;
                    else if(!empty($name) && empty($pass)) $stat = 3;
                    else if(!empty($name) && !empty($pass)) $stat = 1;
                }
                $message = "";
                $data['status'] = (int)$stat;
            }else{
              $resp = REST_Controller::HTTP_NOT_FOUND;
              $message = "phone not registered";
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required";
        }
        set_response($message, $resp, $data);
    }

    public function logout_put(){
          $this->auth();

          $err = 0;
          $message = "";

          if(isset($this->sess["user_id"]) && !empty($this->sess["user_id"])){
              $uid = $this->sess["user_id"];
              if($this->db->delete("auth_token", ["user_id"=>$uid])) $message = "successfully logout";
              else{
                  $err = 422;
                  $message = "something went wrong while logout";
              }
          }else{
              $err = REST_Controller::HTTP_UNAUTHORIZED;
              $message = "unauthorized";
          }

          set_response($message, $err);
    }

}
