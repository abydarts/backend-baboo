<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jobs extends BS_Controller
{

			function __construct()
			{
						parent::__construct();
						$this->auth();
						$this->uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
		        $this->load->model("M_Jobs", "job");
		        $this->load->model("M_Principal", "principal");
		  }

			public function list_get()
	    {
						$resp = 0;
						$message = "";
						$data = $meta = [];
						$rpp = intval($this->get("rpp"));
						$rpp = (!empty($rpp)) ? $rpp : 12;
						$page = intval($this->get("page"));
						$page = (!empty($page)) ? $page : 1;
						$cari = $this->get("search");
						$min_gaji = $this->get("start");
						$max_gaji = $this->get("to");
						$city = $this->get("city");
						$type = $this->get("type");
						$carsql = "";
						if(!empty($cari)){
								$cari = strtolower($cari);
								$carsql = "job_position_id IN(SELECT id FROM job_position WHERE LOWER(name) LIKE '%$cari%')";
								$message = "search result by $cari";
						}
						if(!empty($type)){
								$carsql .= (!empty($carsql)) ? " AND " : "";
								switch($type){
										case '1' : $statype="Full Time";break;
										case '2' : $statype="Part Time";break;
										case '3' : $statype="Contract";break;
										case '4' : $statype="Remote";break;
										default : $statype="Full Time";break;
								}
								$carsql .= "job_type = '$statype'";
						}

						if(!empty($city)){
								$carsql .= (!empty($carsql)) ? " AND " : "";
								$carsql .= "city_code = '$city'";
						}

						if(!empty($min_gaji)){
										if($min_gaji > $max_gaji){
												$message = "$max_gaji != $min_gaji";
												set_response("to amount is bigger than start amount", REST_Controller::HTTP_BAD_REQUEST);
										}
										$carsql .= (!empty($carsql)) ? " AND " : "";
										$carsql .= "lower_salary_range >= $min_gaji";

						}

						if(!empty($max_gaji)){

										if($min_gaji > $max_gaji){
											$message = "$max_gaji != $min_gaji";
											set_response("to amount is bigger than start amount", REST_Controller::HTTP_BAD_REQUEST);
										}
										$carsql .= (!empty($carsql)) ? " AND " : "";
										$carsql .= "upper_salary_range <= $max_gaji";

						}

						$total_data = $this->job->total_published($carsql);
						$total_page = ceil($total_data/$rpp);
						$next_page = ($page < $total_page) ? ($page + 1) : $total_page;
						$prev_page = ($page > 1) ? ($page - 1) : 1;
						$jobs = $this->job->published($carsql, $page, $rpp);
						$data = array();
						if(!empty($jobs)){
						    $data = resource_job($jobs, $this->uid);
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

			public function filter_post()
	    {
						$resp = 0;
						$message = "";
						$data = $meta = [];
						$rpp = intval($this->post("rpp"));
						$rpp = (!empty($rpp)) ? $rpp : 6;
						$page = intval($this->post("page"));
						$page = (!empty($page)) ? $page : 1;
						$cari = $this->post("search");
						$position = $this->post("position");
						$category = $this->post("category");
						$min_gaji = $this->post("start");
						$max_gaji = $this->post("to");
						$city = $this->post("city");
						$type = $this->post("type");
						$carsql = "";
						if(!empty($cari)){
								$cari = strtolower($cari);
								$carsql = "job_position_id IN(SELECT id FROM job_position WHERE LOWER(name) LIKE '%$cari%')";
						}

						if(!empty($position)){
								$carsql .= (!empty($carsql)) ? " AND " : "";
								if(is_array($position)){
										$dapos = [];
										foreach($position as $post){
													$dapos[] = "'$post'";
										}
										if(!empty($dapos)){
													$dapos = implode(",", $dapos);
													$carsql .= "job_position_id IN($dapos)";
										}
								}else{
										$carsql .= "job_position_id = '$position'";
								}
						}

						if(!empty($category)){
								$carsql .= (!empty($carsql)) ? " AND " : "";
								if(is_array($category)){
										$dacat = [];
										foreach($category as $catt){
													$dacat[] = "'$catt'";
										}
										if(!empty($dacat)){
													$dacats = implode(",", $dacat);
													$carsql .= "id in(select job_id from job_category_mapping where job_category_id in($dacats))";
										}
								}else{
										$carsql .= "id in(select job_id from job_category_mapping where job_category_id = '$category')";
								}
						}

						if(!empty($type)){
								$carsql .= (!empty($carsql)) ? " AND " : "";
								if(is_array($type)){
										$datype = [];
										foreach($type as $typ){
													switch($typ){
															case '1' : $statype="Full Time";break;
															case '2' : $statype="Part Time";break;
															case '3' : $statype="Contract";break;
															case '4' : $statype="Remote";break;
															default : $statype="Full Time";break;
													}
													$datype[] = "'$statype'";
										}
										if(!empty($datype)){
													$datip = implode(",", $datype);
													$carsql .= "job_type IN($datip)";
										}
								}else{
											switch($type){
													case '1' : $statype="Full Time";break;
													case '2' : $statype="Part Time";break;
													case '3' : $statype="Contract";break;
													case '4' : $statype="Remote";break;
													default : $statype="Full Time";break;
											}
											$carsql .= "job_type = '$statype'";
								}
						}

						if(!empty($city)){
								$carsql .= (!empty($carsql)) ? " AND " : "";
								if(is_array($city)){
										$dacit = [];
										foreach($city as $cit){
													$dacit[] = "'$cit'";
										}
										if(!empty($dacit)){
													$daci = implode(",", $dacit);
													$carsql .= "city_code IN($daci)";
										}
								}else $carsql .= "city_code = '$city'";
						}

						if(!empty($min_gaji)){
										if(!empty($max_gaji) && $min_gaji > $max_gaji){
												$message = "$max_gaji != $min_gaji";
												set_response("to amount is bigger than start amount", REST_Controller::HTTP_BAD_REQUEST);
										}
										$carsql .= (!empty($carsql)) ? " AND " : "";
										$carsql .= "lower_salary_range >= $min_gaji";

						}

						if(!empty($max_gaji)){
										if(!empty($min_gaji) && $min_gaji > $max_gaji){
											$message = "$max_gaji != $min_gaji";
											set_response("to amount is bigger than start amount $min_gaji != $max_gaji", REST_Controller::HTTP_BAD_REQUEST);
										}
										$carsql .= (!empty($carsql)) ? " AND " : "";
										$carsql .= "upper_salary_range <= $max_gaji";

						}

						$total_data = $this->job->total_published($carsql);
						$total_page = ceil($total_data/$rpp);
						$next_page = ($page < $total_page) ? ($page + 1) : $total_page;
						$prev_page = ($page > 1) ? ($page - 1) : 1;
						$jobs = $this->job->published($carsql, $page, $rpp);
						$data = array();
						if(!empty($jobs)){
						    $data = resource_job($jobs, $this->uid);
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

			public function detail_get($id='')
	    {
					$message = "";
					$data = array();
					$resp = 0;
					$detail = $this->job->find(['id'=>$id]);
					if(!empty($id)){
							if(!empty($detail)){
									$data = resource_job_single($detail, $this->uid);
							}else{
									$resp = REST_Controller::HTTP_NOT_FOUND;
									$message = "job not found";
							}
					}else{
							$resp = REST_Controller::HTTP_BAD_REQUEST;
							$message = "id is required";
					}

					set_response($message, $resp, $data);
	    }

			public function react_put($slug=''){
					$user = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : '';
	        $message = "";
	        $data = array();
	        $zxc = false;
	        $resp = REST_Controller::HTTP_NOT_FOUND;
	        if(!empty($user)){
	            if(!empty($slug)){
								$ceus = $this->db->select("id")->get_where("job_react_user", array("job_id"=>$slug, "created_by"=>$user));
	              if($ceus && $ceus->num_rows() > 0){
	                    $ins = $this->db->delete("job_react_user", array("id"=>$ceus->row()->id));
	                    $message = "you unlike this job";
											$like = false;
	              }else{
		                  $ins_data = array("id"=>get_uuid(), "job_id"=>$slug, "created_by"=>$user);
		                  $ins = $this->db->insert("job_react_user", $ins_data);
		                  $message = "you like this job";
											$like = true;
	              }

	              $message = ($ins) ? $message : "something went wrong when you like this job";
	              $resp = ($ins) ? 0 : REST_Controller::HTTP_NOT_FOUND;
	              $zxc= ($ins) ? true : false;
								if($ins){
									$sqlr = $this->db->select("COUNT(id) as total")->get_where("job_react_user", ["job_id"=>$slug]);
									$totreact = ($sqlr && $sqlr->num_rows() > 0) ? (int)$sqlr->row()->total : 0;
									$data = [
											"job_id"=>$slug,
											"liked"=>$like,
											"likes"=>$totreact
									];
								}
	            }else $message = "job can't be empty";
	        }else{
	            $resp = REST_Controller::HTTP_UNAUTHORIZED;
	            $message = "unauthorized";
	        }
					$resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
					$this->response($resps, REST_Controller::HTTP_OK);
	    }
}

/* End of file Tracking.php */
