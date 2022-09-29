<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';
require_once APPPATH . '/libraries/REST_Controller.php';

use \Firebase\JWT\JWT;


class Otp extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model("M_Sales", "motor");
        $this->load->model("M_OTP", "otp");
    }

    public function send_post(){

        $phone = $this->post("phone");
        $channel = $this->post("channel");
        if(empty($channel)) $channel = "sms";

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone) && strlen($phone) > 11){

            $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id");
            if(isset($cek_us->id)){
                $ins = $cek_us->id;
            }else{
                $ins = get_uuid();
                $sales = $this->motor->insert(['id'=>$ins, 'sales_name'=>'', 'agency_id'=>'', 'active'=>false, 'profile_picture_url'=>'https://cdn-ptf.pintap.id/static/pintap-force-icon.png', 'join_date'=>date("Y-m-d")]);
                $login = $this->motor->insert_login(['sales_force_id'=>$ins, 'phone'=>$phone, 'email'=>'']);
            }

            if($ins && !empty($ins)){

                $otp = mt_rand(100000, 999999);
                $data_otp = array("id"=>get_uuid(), "activity"=>"auth-otp", "user_id"=>$ins, "otp"=>$otp, "channel"=>$channel, "expired_at"=>date("Y-m-d H:i:s", strtotime("+5 minutes")));
                if($this->db->insert("auth_otp", $data_otp)){
                    //put_send_message_otp
                    $devid = $this->input->get_request_header('Vid-TapOn');
                    $fcmid = $this->input->get_request_header('Gid-TapOn');
                    // if (!empty($devid) && !empty($fcmid)) {
                        // $data_insert = array(
                        //     "device_id" => base64_decode($devid),
                        //     "fcm_id" => base64_decode($fcmid)
                        // );
                        // $this->db->select("id, device_id, login_count");
                        // $cekDuid = $this->db->get_where("motorist_devices", array("motorist_id"=>$ins, "device_id"=>base64_decode($devid)));
                        // if($cekDuid && $cekDuid->num_rows() > 0){
                        //     $did = $cekDuid->row()->id;
                        //     $insd = $this->db->update("motorist_devices", $data_insert, array("id"=>$did));
                        // }else{
                        //     $data_insert["motorist_id"] = $ins;
                        //     $data_insert["login_count"] = 0;
                        //     $data_insert["id"] = get_uuid();
                        //     $insd = $this->db->insert("motorist_devices", $data_insert);
                        // }
                        $data_login = [
                            "username"=>USERNAME_KOKATTO,
                            "password"=>PASS_KOKATTO
                        ];

                        $tock = xapi_post(KOKATTO_URLAUTH."/auth/login", $data_login);
                        if(!empty($tock)){
                              $tock = (object)$tock;
                              if(isset($tock->token)){
                                      $token = $tock->token;
                                      // checking
                                      $timestamp = date("Y-m-d H:i:s");
                                      $phonesend = str_replace("+62", "0", $phone);
                                      $arrayOtp = [
                                          'destination'=>$phonesend,
                                          'clientId'=> KOKATTO_CLIENTID,
                                          'campaignName'=> ($channel == 'call') ? KOKATTO_CAMPAIGN : KOKATTO_CAMPSMS,
                                          'code'=>$otp,
                                          'codeLength'=>6,
                                          'codeValidity'=>120,
                                          'dlrCallbackUrl'=>base_url('v1/api/otp/callback'),
                                          'timestamp'=>date(DATE_ISO8601, strtotime($timestamp))
                                      ];
                                      $secrets = ($channel == 'call') ? KOKATTO_CLIENTSECRET : KOKATTO_SMSSECRET;
                                      $signature = generateKokattoAPISignedRequest($arrayOtp, $secrets);
                                      $arrayOtp["signature"] = $signature;
                                      $url_otp = KOKATTO_URLSERVICE."/otp/request/self-validation?";
                                      $otp_send = xapi_post($url_otp, $arrayOtp, $token);
                                      if(!empty($otp_send)){
                                            $otp_send = (object)$otp_send;
                                            if(isset($otp_send->status) && $otp_send->status == 201){
                                                $resp=0;
                                                // $message = (isset($otp_send->message)) ? strtolower($otp_send->message) : "otp has been sent";
                                                $channmsg = ($channel == 'call') ? "cek 6 angka terakhir di nomor yg menelpon anda" : "cek sms anda";
                                                $message = "OTP telah dikirim, $channmsg";
                                                // $data = (isset($otp_send->data)) ? $otp_send : (object)["message"=>$otp_send->message];
                                            }else{
                                                if(strpos(getenv("BASE_URL"), 'dev-xapi-salesforce.pintap.id') !== false || strpos(getenv("BASE_URL"), "sforce.test") !== false){
                                                    $resp=0;
                                                    // $message = (isset($otp_send->message)) ? strtolower($otp_send->message) : "otp has been sent";
                                                    $channmsg = ($channel == 'call') ? "cek 6 angka terakhir di nomor yg menelpon anda" : "cek sms anda";
                                                    $message = "OTP telah dikirim, $channmsg";
                                                }else{
                                                  $resp = (isset($otp_send->status)) ? $otp_send->status : REST_Controller::HTTP_BAD_REQUEST;
                                                  if(isset($otp_send->message)){
                                                      if(is_array($otp_send->message)) $message = $otp_send->message[0];
                                                      else $message = $otp_send->message;
                                                  }else $message = "something went wrong while access api otp";
                                                }
                                                // $data = (isset($otp_send->data)) ? $otp_send : (object)["message"=>$otp_send->message];
                                                // $data->request->otp = $arrayOtp;
                                                // $data->request->token = $token;
                                            }
                                      }else{
                                          $resp = REST_Controller::HTTP_BAD_REQUEST;
                                          $message = "something went wrong while access api otp";
                                      }
                              }else{
                                      $resp = (isset($tock->status)) ? $tock->status : REST_Controller::HTTP_BAD_REQUEST;
                                      $message = (isset($tock->message)) ? $tock->message : "something went wrong while access api signature";
                                      // $data = $tock;
                                      // $data->request = $data_login;
                              }
                      }else{
                                  $resp = REST_Controller::HTTP_BAD_REQUEST;
                                  $message = "something went wrong while access api signature";
                      }

                        // notif_api($ins, 'your OTP is '.$otp, 'send-otp', ['uid'=>$ins, 'otp'=>$otp]);
                    // }
                    // $message = "otp has been sent";
                }else{
                    $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    $message = "something went wrong while request otp";
                }
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = (!empty($phone)) ? "invalid phone number" : "phone is required to send otp";
        }
        set_response($message, $resp, $data);
    }

    public function forgot_post(){

        $phone = $this->post("phone");
        $channel = $this->post("channel");
        if(empty($channel)) $channel = "call";

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone)){
            $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id");
            if(isset($cek_us->id)){
                $ins = $cek_us->id;
                $otp = mt_rand(100000, 999999);
                $data_otp = array("id"=>get_uuid(), "activity"=>"forgot-otp", "user_id"=>$ins, "otp"=>$otp, "channel"=>$channel, "expired_at"=>date("Y-m-d H:i:s", strtotime("+2 minutes")));
                if($this->db->insert("auth_otp", $data_otp)){
                    //put_send_message_otp
                    // $devid = $this->input->get_request_header('Vid-TapOn');
                    // $fcmid = $this->input->get_request_header('Gid-TapOn');
                    // if (!empty($devid) && !empty($fcmid)) {

                        $data_login = [
                            "username"=>USERNAME_KOKATTO,
                            "password"=>PASS_KOKATTO
                        ];
                        $tock = xapi_post(KOKATTO_URLAUTH."/auth/login", $data_login);
                        if(!empty($tock)){
                              $tock = (object)$tock;
                              if(isset($tock->token)){
                                      $token = $tock->token;
                                      // checking
                                      $timestamp = date("Y-m-d H:i:s");
                                      $phonesend = str_replace("+62", "0", $phone);
                                      $arrayOtp = [
                                          'destination'=>$phonesend,
                                          'clientId'=> KOKATTO_CLIENTID,
                                          'campaignName'=> ($channel == 'call') ? KOKATTO_CAMPAIGN : KOKATTO_CAMPSMS,
                                          'code'=>$otp,
                                          'codeLength'=>6,
                                          'codeValidity'=>120,
                                          'dlrCallbackUrl'=>base_url('v1/api/otp/callback'),
                                          'timestamp'=>date(DATE_ISO8601, strtotime($timestamp))
                                      ];
                                      $secrets = ($channel == 'call') ? KOKATTO_CLIENTSECRET : KOKATTO_SMSSECRET;
                                      $signature = generateKokattoAPISignedRequest($arrayOtp, $secrets);
                                      $arrayOtp["signature"] = $signature;
                                      $url_otp = KOKATTO_URLSERVICE."/otp/request/self-validation?";
                                      $otp_send = xapi_post($url_otp, $arrayOtp, $token);
                                      if(!empty($otp_send)){
                                            $otp_send = (object)$otp_send;
                                            if(isset($otp_send->status) && $otp_send->status == 201){
                                                $resp=0;
                                                // $message = (isset($otp_send->message)) ? strtolower($otp_send->message) : "otp has been sent";
                                                $channmsg = ($channel == 'call') ? "cek 6 angka terakhir di nomor yg menelpon anda" : "cek sms anda";
                                                $message = "OTP telah dikirim, $channmsg";
                                                // $data = (isset($otp_send->data)) ? $otp_send : (object)["message"=>$otp_send->message];
                                            }else{
                                                $resp = (isset($otp_send->status)) ? $otp_send->status : REST_Controller::HTTP_BAD_REQUEST;
                                                if(isset($otp_send->message)){
                                                    if(is_array($otp_send->message)) $message = $otp_send->message[0];
                                                    else $message = $otp_send->message;
                                                }else $message = "something went wrong while access api otp";
                                                // $data = (isset($otp_send->data)) ? $otp_send : (object)["message"=>$otp_send->message];
                                                // $data->request->otp = $arrayOtp;
                                                // $data->request->token = $token;
                                            }
                                      }else{
                                          $resp = REST_Controller::HTTP_BAD_REQUEST;
                                          $message = "something went wrong while access api otp";
                                      }
                              }else{
                                      $resp = (isset($tock->status)) ? $tock->status : REST_Controller::HTTP_BAD_REQUEST;
                                      $message = (isset($tock->message)) ? $tock->message : "something went wrong while access api signature";
                                      // $data = $tock;
                                      // $data->request = $data_login;
                              }
                      }else{
                                  $resp = REST_Controller::HTTP_BAD_REQUEST;
                                  $message = "something went wrong while access api signature";
                      }
                        // notif_api($ins, 'your OTP is '.$otp, 'send-otp', ['uid'=>$ins, 'otp'=>$otp]);
                    // }
                    // $message = "otp has been sent";
                }else{
                    $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    $message = "something went wrong while request otp";
                }
            }else{
                $resp = REST_Controller::HTTP_NOT_FOUND;
                $message = "sales not registered";
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to send otp";
        }
        set_response($message, $resp, $data);
    }

    public function confirm_post(){

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
                              $message = "OTP has been expired please request again";
                          }else{
                              $resp = 0;
                              $message = "";
                              $date = new DateTime();
                              $jti = generateRandomString(80);
                              $token['aud'] = "#".$id;
                              $token['jti'] = $jti;
                              $token['iat'] = $date->getTimestamp();
                              $token['nbf'] = $date->getTimestamp();
                              $expr = $date->getTimestamp() + 60*60*24*30; //To here is to generate token for 3 days
                              $token['exp'] = $expr; //To here is to generate token for 3 days
                              $token['sub'] = "#".$id;
                              $token['scopes'] = (!empty($scopes)) ? explode(" ", $scopes) : [];
                              $ins = $this->db->insert("auth_token", array("id"=>get_uuid(), "user_id"=>$id, "hash"=>$jti, "expired_at"=>date("Y-m-d H:i:s", $expr)));
                              $data['token'] = JWT::encode($token, $this->config->item('thekey'), 'RS256'); //This is the output token
                              $data['expires'] = 60*60*24*30;
                              $this->db->delete("auth_otp", ["user_id"=>$id]);
                          }
                      }else{
                          $resp = REST_Controller::HTTP_UNAUTHORIZED;
                          $message = "miss-match otp";
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
            $message = "phone is required to confirm otp";
        }
        set_response($message, $resp, $data);
    }

    public function intip_get(){

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

            $cek_us = $this->motor->find_login(["phone"=>$phone], "sales_force_id as id");
            if(isset($cek_us->id)){
                $id = $cek_us->id;
                $oztp = $this->db->order_by("expired", "desc")->select("otp, expired")->get_where("auth_otp", ['user_id'=>$id]);
                if($oztp && $oztp->num_rows() > 0){
                          $now = date("Y-m-d H:i:s");
                          if($oztp->row()->expired < $now){
                              $resp = REST_Controller::HTTP_BAD_REQUEST;
                              $message = "otp has been expired please request again";
                              $data = [
                                  "expired"=>$oztp->row()->expired,
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
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to check otp";
            set_response($message, $resp, $data);
        }
        $this->response($data, 200);
    }

    public function req_phone_post(){

        $this->auth();
        $sess = $this->sess;
        $uid = (isset($this->sess["user_id"])) ? $sess["user_id"] : "";

        $phone = $this->post("phone");
        $channel = $this->post("channel");
        if(empty($channel)) $channel = "sms";

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone)){
            $cek_us = $this->motor->find_login(["phone"=>$phone, "sales_force_id !="=>$uid], "sales_force_id as id");
            if(isset($cek_us->id)){
                $resp = REST_Controller::HTTP_BAD_REQUEST;
                $message = "this phone has been registered by other sales";
            }else{
                $ins = $uid;
                $otp = mt_rand(100000, 999999);
                $data_otp = array("id"=>get_uuid(), "activity"=>"change-phone-otp", "user_id"=>$phone, "otp"=>$otp, "channel"=>$channel, "expired_at"=>date("Y-m-d H:i:s", strtotime("+2 minutes")));
                if($this->db->insert("auth_otp", $data_otp)){
                    //put_send_message_otp
                        // if($channel == 'call'){
                              $data_login = [
                                  "username"=>USERNAME_KOKATTO,
                                  "password"=>PASS_KOKATTO
                              ];
                              $tock = xapi_post(KOKATTO_URLAUTH."/auth/login", $data_login);
                              if(!empty($tock)){
                                        $tock = (object)$tock;
                                        if(isset($tock->token)){
                                            $token = $tock->token;
                                            // checking
                                            $timestamp = date("Y-m-d H:i:s");
                                            $phonesend = str_replace("+62", "0", $phone);
                                            $arrayOtp = [
                                                'destination'=>$phonesend,
                                                'clientId'=>KOKATTO_CLIENTID,
                                                'campaignName'=>($channel == 'call') ? KOKATTO_CAMPAIGN : KOKATTO_CAMPSMS,
                                                'code'=>$otp,
                                                'codeLength'=>6,
                                                'codeValidity'=>120,
                                                'dlrCallbackUrl'=>base_url('v1/api/otp/callback'),
                                                'timestamp'=>date(DATE_ISO8601, strtotime($timestamp))
                                            ];
                                            $secrets = ($channel == 'call') ? KOKATTO_CLIENTSECRET : KOKATTO_SMSSECRET;
                                            $signature = generateKokattoAPISignedRequest($arrayOtp, $secrets);
                                            $arrayOtp["signature"] = $signature;
                                            $url_otp = KOKATTO_URLSERVICE."/otp/request/self-validation?";
                                            $otp_send = xapi_post($url_otp, $arrayOtp, $token);
                                            if(!empty($otp_send)){
                                                  $otp_send = (object)$otp_send;
                                                  if(isset($otp_send->status) && $otp_send->status == 201){
                                                      $resp=0;
                                                      // $message = (isset($otp_send->message)) ? strtolower($otp_send->message) : "otp has been sent";
                                                      $msgchan = ($channel == 'call') ? "cek 6 angka terakhir di nomor yg menelpon anda" : "cek sms anda";
                                                      $message = "OTP telah dikirim, $msgchan";
                                                      // $data = (isset($otp_send->data)) ? $otp_send : (object)["message"=>$otp_send->message];
                                                  }else{
                                                      $resp = (isset($otp_send->status)) ? $otp_send->status : REST_Controller::HTTP_BAD_REQUEST;
                                                      if(isset($otp_send->message)){
                                                          if(is_array($otp_send->message)) $message = $otp_send->message[0];
                                                          else $message = $otp_send->message;
                                                      }else $message = "something went wrong while access api otp";
                                                      // $message = (isset($otp_send->message)) ? $otp_send->message : "something went wrong while access api otp";
                                                      // $data = (isset($otp_send->data)) ? $otp_send : (object)["message"=>$otp_send->message];
                                                      // $data->request->otp = $arrayOtp;
                                                      // $data->request->token = $token;
                                                  }
                                            }else{
                                                $resp = REST_Controller::HTTP_BAD_REQUEST;
                                                $message = "something went wrong while access api otp";
                                            }
                                        }else{
                                            $resp = (isset($tock->status)) ? $tock->status : REST_Controller::HTTP_BAD_REQUEST;
                                            $message = (isset($tock->message)) ? $tock->message : "something went wrong while access api signature";
                                            // $data = $tock;
                                            // $data->request = $data_login;
                                        }
                              }else{
                                        $resp = REST_Controller::HTTP_BAD_REQUEST;
                                        $message = "something went wrong while authenticate";
                              }
                        // }else{
                        //     setpush_notif($ins, 'your OTP is '.$otp, 'send-otp', ['uid'=>$ins, 'otp'=>$otp]);
                        //     $message = "otp telah dikirim, akan kadaluarsa dalam waktu 2 menit";
                        // }
                        // notif_api($ins, 'your OTP is '.$otp, 'send-otp', ['uid'=>$ins, 'otp'=>$otp]);
                    // $message = "otp has been sent";
                }else{
                    $resp = REST_Controller::HTTP_INTERNAL_SERVER_ERROR;
                    $message = "something went wrong while request otp";
                }
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to send otp";
        }
        set_response($message, $resp, $data);
    }

    public function confirm_req_post(){
        $this->auth();
        $sess = $this->sess;
        $uid = (isset($this->sess["user_id"])) ? $sess["user_id"] : "";

        $phone = $this->post("phone");
        $otp = $this->post("otp");

        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($phone) && !empty($otp)){
            $id = $uid;
            $oztp = $this->db->order_by("created_at", "desc")->select("otp, expired_at")->get_where("auth_otp", ["user_id"=>$phone]);
            $lq = $this->db->last_query();
            if($oztp && $oztp->num_rows() > 0){
                      if($otp == $oztp->row()->otp){
                            if($oztp->row()->expired_at < date("Y-m-d H:i:s")){
                                $resp = REST_Controller::HTTP_BAD_REQUEST;
                                $message = "OTP has been expired please request again";
                            }else{
                                $resp = 0;
                                $message = "";
                                $date = new DateTime();
                                if($this->motor->update_login(['phone'=>$phone], ['sales_force_id'=>$uid])){
                                    $resp = 0;
                                    $message = "update phone successful";
                                    $this->db->delete("auth_otp", ["user_id"=>$phone]);
                                }else{
                                    $resp = REST_Controller::HTTP_BAD_REQUEST;
                                    $message = "something went wrong";
                                }
                            }
                      }else{
                          $otp1 = $oztp->row()->otp;
                          $otp2 = $otp;
                          $resp = REST_Controller::HTTP_UNAUTHORIZED;
                          $message = "miss-match otp";
                      }
            }else{
              $resp = REST_Controller::HTTP_NOT_FOUND;
              $message = "please request otp first";
            }
        }else{
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            $message = "phone is required to confirm otp";
        }
        set_response($message, $resp, $data);
    }

    public function password_post(){
        $this->auth();
        $sess = $this->sess;
        $uid = (isset($this->sess["user_id"])) ? substr($sess["user_id"], 1) : "";
        $pass = $this->post("password");
        $cpass = $this->post("confirm_password");
        $message = "";
        $data = array();
        $resp = 0;

        if(!empty($uid) && !empty($pass) && !empty($cpass)){
          if($pass == $cpass){
              $hash_pass = password_hash($pass, PASSWORD_DEFAULT);
              $data_ins = ['password'=>$hash_pass];
              $cek_us = $this->motor->find_login(["sales_force_id"=>$uid], 'sales_force_id as id');
              if(isset($cek_us->id)){
                    $mid = $cek_us->id;
                    /* mulai comment disini */
                    $number = preg_match('@[0-9]@', $pass);
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

                        if($this->motor->update_login($data_ins, ['sales_force_id'=>$mid])){
                            $message = "update password success";
                            if(isset($this->user_data->jti)) $this->db->delete("auth_token", ["hash"=>$this->user_data->jti]);
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
            $resp = (!empty($uid)) ? REST_Controller::HTTP_BAD_REQUEST : REST_Controller::HTTP_UNAUTHORIZED;
            $message = (!empty($uid)) ? "password and confirm password are required" : "request otp first to confirm OTP forgot password";
        }
        set_response($message, $resp, $data);
    }

}
