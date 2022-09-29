<?php defined('BASEPATH') OR exit('No direct script access allowed');
date_default_timezone_set('Asia/Jakarta');
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require_once APPPATH . '/libraries/REST_Controller.php';
require_once APPPATH . '/libraries/JWT.php';
require_once APPPATH . '/libraries/BeforeValidException.php';
require_once APPPATH . '/libraries/ExpiredException.php';
require_once APPPATH . '/libraries/SignatureInvalidException.php';
use \Firebase\JWT\JWT;

class BS_Controller extends REST_Controller
{
	  private $user_credential;

		public function api_access($url, $data=array(), $auth=false, $type="POST", $token="", $devices=array())
		{
					$ci =& get_instance();
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
					if($type == "POST"){
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					}else curl_setopt($ch, CURLOPT_POST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					if(!$auth){
						$token = "";
						$headers = $ci->input->get_request_header('Authorization');
						if (!empty($headers)) {
							if (preg_match('/Bearer\s(\S+)/', $headers , $matches)) $token = $matches[1];
						}
						$arr_header = array(
								'Authorization: Bearer '.$token,
								'Content-Type: application/json'
						);
					}else{
							curl_setopt($ch, CURLOPT_ENCODING, '');
							$device_id = 'tapp-note';
							$device_name = 'tapp-note';
							if(!empty($devices) && is_array($devices)){
									$device_id = (isset($devices["id"]) && !empty($devices["id"])) ? $devices["id"] : 'nusapay-connect';
									$device_name = (isset($devices["name"]) && !empty($devices["name"])) ? $devices["name"] : 'nusapay-connect';
							}
							$arr_header = array(
									'X-Device-ID: '.$device_id,
									'X-Device-Name: '.$device_name
							);
					}
					curl_setopt($ch, CURLOPT_HEADER, TRUE);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
					// $result["info"] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
					$result = curl_exec($ch);
					return $result;
		}

		public function auth()
		{
				$ci =& get_instance();
				ini_set('date.timezone', 'Asia/Jakarta');
				$this->methods['list_get']['limit'] = 500; // 500 requests per hour per user/key
				$this->methods['add_post']['limit'] = 50; // 100 requests per hour per user/key
				$kunci = $ci->config->item('pkey');
				$token="token";
				$headers = $ci->input->get_request_header('Authorization');
				if (!empty($headers)) {
					if (preg_match('/Bearer\s(\S+)/', $headers , $matches)) {
							$token = $matches[1];
					}
				}

				try {
					 $decoded = JWT::decode($token, $kunci, array('RS256'));
					 $this->user_data = $decoded;
					 if(isset($this->user_data->exp) && $this->user_data->exp >= strtotime(date("Y-m-d H:i:s"))){
							 if(isset($this->user_data->jti)){
								 	 $authc = $ci->db->select("user_id")->get_where("auth_token", array("hash"=>$this->user_data->jti));
									 if($authc && $authc->num_rows() > 0){
										 		 $this->sess = array(
														 "user_id" => $this->user_data->sub,
														 "login_at" => date("Y-m-d H:i:s", $this->user_data->iat),
														 "expired_at" => date("Y-m-d H:i:s", $this->user_data->exp)
												 );
									 }else{
											 $invalid = ['code'=>REST_Controller::HTTP_UNAUTHORIZED, 'message'=>'unauthorized']; //Respon if credential invalid
											 $this->response($invalid, REST_Controller::HTTP_UNAUTHORIZED);//401
									 }

							 }else{
									 $invalid = ['code'=>REST_Controller::HTTP_UNAUTHORIZED, 'message'=>'unauthorized']; //Respon if credential invalid
									 $this->response($invalid, REST_Controller::HTTP_UNAUTHORIZED);//401
							 }
						}else{
										$invalid = ['code'=>REST_Controller::HTTP_UNAUTHORIZED, 'message'=>'unauthorized : expired token']; //Respon if credential invalid
										$this->response($invalid, REST_Controller::HTTP_UNAUTHORIZED);//401
						}
				} catch (Exception $e) {
						$invalid = ['code'=>REST_Controller::HTTP_UNAUTHORIZED, 'message' => $e->getMessage()]; //Respon if credential invalid
						$this->response($invalid,  REST_Controller::HTTP_UNAUTHORIZED);//401
				}
		}

		public function free_auth()
		{
				$ci =& get_instance();
				ini_set('date.timezone', 'Asia/Jakarta');
				$this->methods['list_get']['limit'] = 500; // 500 requests per hour per user/key
				$this->methods['add_post']['limit'] = 50; // 100 requests per hour per user/key
				$kunci = $ci->config->item('pkey');
				$token="token";
				$headers = $ci->input->get_request_header('Authorization');
				if (!empty($headers)) {
						if (preg_match('/Bearer\s(\S+)/', $headers , $matches)) {
								$token = $matches[1];
						}
				}
				try {
					 $decoded = JWT::decode($token, $kunci, array('RS256'));
					 $this->user_data = $decoded;
					 if(isset($this->user_data->exp) && $this->user_data->exp >= strtotime(date("Y-m-d"))){
						 	$this->sess = array(
									"user_id" => $this->user_data->sub,
									"created" => date("Y-m-d H:i:s", $this->user_data->iat),
									"expired" => date("Y-m-d H:i:s", $this->user_data->exp)
							);
					 }
				} catch (Exception $e) {

				}
		}
}
