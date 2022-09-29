<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Newsfeed extends BS_Controller
{
			protected $admin_uri;

			function __construct()
			{
						parent::__construct();
						$this->load->model("M_News", "news");
						$this->load->model("M_Sales", "sales");
						$this->load->model("M_Jobs", "job");
						if(!empty(getenv('BASE_URL'))){
								if(getenv('BASE_URL') == 'https://dev-xapi-salesforce.pintap.id' || getenv('BASE_URL') == 'http://sforce.test'){
									$this->admin_uri = "https://dev-ptf-landing-page.pintap.id";
								}else{
									$this->admin_uri = "https://force.pintap.id";
								}
						}else{
								$this->admin_uri = "https://qa-ptf-landing-page.pintap.id";
						}
			}

			public function list_get(){
						$this->free_auth();
						$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : "";
						$desired_position = "";
						if(!empty($uid)){
								$desql = $this->sales->find_cond(['id'=>$uid], 'desired_position');
								if(!empty($desql) && isset($desql->desired_position)){
										if(strpos($desql->desired_position, "'") !== false){
											$desired_position = $desql->desired_position;
										}else{
											$despo = explode(",", $desql->desired_position);
											$dosp = [];
											foreach($despo as $dsp){
													$dosp[] = "'".$dsp."'";
											}
											$desired_position = implode(",", $dosp);
										}
								}
						}
						$resp = 0;
						$message = "";
						$data = [];
						$meta = [];
						$rpp=  (!empty($this->get("rpp"))) ? $this->get("rpp") : 12;
						$page=$this->get("page");
						$list = $this->news->list_view("status = 1 AND published_at IS NOT NULL", $rpp, $page);
						if(!empty($list)){
								$page = (!empty(intval($page))) ? (int)$page : 1;
								$total_published = $this->news->total_published();
								$total_page = ceil($total_published/$rpp);
								$npage = ($page < $total_page) ? ($page + 1) : $page;
								$ppage = ($page > 1) ? $page - 1 : 1;
								$meta = [
										'current_page' => $page,
										'next_page' => $npage,
										'prev_page' => $ppage,
										'total_page' => $total_page,
										'rpp' => $rpp
								];
								$rnj = $this->db->select("site_value")->get_where("site_options", ["name"=>"row_news_job"]);
								$sdj = ($rnj && $rnj->num_rows() > 0) ? $rnj->row()->site_value : 0;
								if(!empty($sdj)){
										$sdj = $sdj - 1;
								}
								foreach($list as $lkv=>$lv){
									 	if(!empty($lv->category)){
											 $dcat = $this->news->detail_vcat($lv->category);
											 if(!empty($dcat)) $lv->category = $dcat;
											 else $lv->category = null;
										}else $lv->category = null;
										$publish_date = date("Y-m-d H:i:s", strtotime($lv->published_at."+7hours"));
										$lv->published_at = waktu_lalu($publish_date);
										$lv->tags = (!empty($lv->tags)) ? explode(",", $lv->tags) : [];
										$image = (isset($lv->featured_image_url) && !empty($lv->featured_image_url)) ? $lv->featured_image_url : "https://placekitten.com/600/300";
										$lv->featured_image_url = $image;
										if(!empty($lv->slug)){
											$lv->slug = (strpos($lv->slug, 'http') !== false) ? $lv->slug : $this->admin_uri.'/news/'.$lv->slug;
										}
										$tcmnt = $this->db->select("COALESCE(COUNT(id), 0) as total_comment")->get_where("news_comment", array("news_id"=>$lv->id));
										$lv->total_comment = ($tcmnt && $tcmnt->num_rows() > 0) ? (int)$tcmnt->row()->total_comment : 0;
										$tlike = $this->db->select("COALESCE(COUNT(id), 0) as total_like")->get_where("news_react_user", array("news_id"=>$lv->id));
										$lv->total_like = ($tlike && $tlike->num_rows() > 0) ? (int)$tlike->row()->total_like : 0;
										$is_like = false;
										if(!empty($uid)){
											$sqlike = $this->db->select("id")->get_where("news_react_user", array("news_id"=>$lv->id, "created_by"=>$uid));
											$is_like = ($sqlike && $sqlike->num_rows() > 0) ? true : false;
										}
										$lv->is_like = $is_like;
										$lv->list_type = "news";
										$lv->owner=pintap_system_data();
										unset($lv->status);
										$data[] = $lv;
										$clist = count($list) - 1;
										if(!empty($sdj) && !empty($desired_position)){
											if($lkv != 0 && $lkv % $sdj == 0 ){
													$jobs = $this->job->publish_cond("status = 2 AND job_position_id IN($desired_position) AND date_of_closing >= '".date("Y-m-d")."'",  "id, job_title, job_type, principal_id, minimum_experience, date_of_closing, city_code, lower_salary_range, upper_salary_range, date_of_closing, short_description, job_position_id, created_at", 1, 5);
													if(!empty($jobs)) $data[] = ["id"=>date("ymdhis")."#".$lkv, "list_type"=>"job", "data"=>resource_job($jobs)];
											}
										}
 								}
						}else $message = "data news not found";
						$ressp = array("code"=>$resp, "message"=>$message, "data"=>$data);
						if(!empty($meta)) $ressp["meta"] = $meta;
						$this->response($ressp);
	    }

			public function admin_get(){
						$this->auth();
						$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : '';
						$resp = 0;
						$message = "";
						$data = [];
						$where_arr = null;
						$status = $this->get("status");
						if(!empty($status)) $where_arr["status"] = $status;
						$list = $this->news->list_view($where_arr);
						if(!empty($list)){
								foreach($list as $lv){
										if(!empty($lv->category)){
											 $dcat = $this->news->detail_vcat($lv->category);
											 if(!empty($dcat)) $lv->category = $dcat;
											 else $lv->category = null;
										}else $lv->category = null;

										$lv->status = (isset($lv->status) && $lv->status == 2) ? "published" : "draft";
										$lv->tags = (!empty($lv->tags)) ? explode(",", $lv->tags) : [];
										$lv->owner=pintap_system_data();

										$data[] = $lv;
								}
						}else $message = "data news not found";
						$this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

			public function category_get(){
						$resp = 0;
						$message = "";
						$data = [];
						$cat = $this->news->list_cat();
						if(!empty($cat)){
								$data = $cat;
						}else $message = "data category not found";
						$this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
	    }

			public function detail_get($id=0){
					$this->free_auth();
					$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : "";
					$resp = REST_Controller::HTTP_NOT_FOUND;
					$message = "";
					$data = [];
					$slug=$this->get("slug");
					if(!empty($id) || !empty($slug)){
							$where_arr = (!empty($id)) ? ['id'=>$id] : array("slug"=>urldecode($slug));
							$news = $this->news->detail($where_arr);
							if(!empty($news)){
									$resp = 0;
									if(isset($news->category) && !empty($news->category)){
											$dcat = $this->news->detail_vcat($news->category);
											$news->category = (!empty($dcat)) ? $dcat : null;
									}else $news->category = null;

									$news->owner=pintap_system_data();

									if(!empty($news->slug)){
										$news->slug = (strpos($news->slug, 'http') !== false) ? $news->slug : $this->admin_uri.'/news/'.$news->slug;
									}

									$news->status = (isset($news->status) && $news->status == 2) ? "published" : "draft";
									$publish_date = date("Y-m-d H:i:s", strtotime($news->published_at."+7hours"));
									$news->published_at = waktu_lalu($publish_date);

									$news->comment_enabled = (isset($news->comment_status) && !empty($news->comment_status)) ? true : false;
									$news->tags = (isset($news->tags) && !empty($news->tags)) ? explode(",", $news->tags) : [];
									$tcmnt = $this->db->select("COALESCE(COUNT(id), 0) as total_comment")->get_where("news_comment", array("news_id"=>$news->id));
									$news->total_comment = ($tcmnt && $tcmnt->num_rows() > 0) ? (int)$tcmnt->row()->total_comment : 0;
									$tlike = $this->db->select("COALESCE(COUNT(id), 0) as total_like")->get_where("news_react_user", array("news_id"=>$news->id));
									$news->total_like = ($tlike && $tlike->num_rows() > 0) ? (int)$tlike->row()->total_like : 0;
									$imageurl = (isset($news->featured_image_url) && !empty($news->featured_image_url)) ? $news->featured_image_url : "https://placekitten.com/600/300";
									$news->featured_image_url = $imageurl;
									$news_files = $this->news->list_file(['news_id'=>$news->id]);
									$news->attachments = $news_files;
									$is_like = false;
									if(!empty($uid)){
										$sqlike = $this->db->select("id")->get_where("news_react_user", array("news_id"=>$news->id, "created_by"=>$uid));
										$is_like = ($sqlike && $sqlike->num_rows() > 0) ? true : false;
									}
									$news->is_like = $is_like;

									$comments = [];
									if($news->comment_enabled){
												$this->db->order_by("created_at", "DESC");
												$this->db->limit(10, 0);
												$tcmnt = $this->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("news_comment", "news_id = '".$news->id."' AND parent IS NULL");
												if($tcmnt && $tcmnt->num_rows() > 0){
														foreach($tcmnt->result() as $tcv){
																$users = [];
																$avatar = base_url("contents/assets/img/user.png");
																if(!empty($tcv->is_moderator)) $users = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("contents/assets/img/admin.png"));
																else{
																		$vusr = $this->db->select("id, sales_name as name")->get_where("sales_force", array("id"=>$tcv->user));
																		if($vusr && $vusr->num_rows() > 0) $users = array("id"=>$vusr->row()->id, "name"=>$vusr->row()->name, "avatar"=>$avatar);
																}

																if(!empty($users)){
																		$editable = false;
																		if(isset($this->sess["admin"])) $editable = true;
																		else if(empty($tcv->is_moderator) && isset($this->sess["vendor"]) && $this->sess["user_id"] = $tcv->user) $editable = true;
																		$tcv->user = $users;
																		$tcv->editable = $editable;
																		$this->db->order_by("id", "DESC");
																		$trpt = $this->db->select("id, user_id as user, COALESCE(content, '') as content, is_moderator, created_at")->get_where("news_comment", array("news_id"=>$news->id, "parent"=>$tcv->id));
																		$tcv->total_reply = ($trpt && $trpt->num_rows() > 0) ? (double)$trpt->num_rows() : 0;
																		$latest_reply = null;
																		if($trpt && $trpt->num_rows() > 0){
																				$rply = $trpt->row();
																				$user_rep = [];
																				if(!empty($rply->is_moderator)) $user_rep = array("id"=>0, "name"=>"MODERATOR", "avatar"=>base_url("public/assets/img/admin.png"));
																				else{
																						$vusrep = $this->db->select("id, sales_name as name")->get_where("sales_force", array("id"=>$rply->user));
																						if($vusrep && $vusrep->num_rows() > 0) $user_rep = array("id"=>$vusrep->row()->id, "name"=>$vusrep->row()->name, "avatar"=>$avatar);
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
																		$comments[] = $tcv;
																}
														}
												}
									}
									$news->comments = $comments;
									unset($news->comment_status);
									$data = $news;
							}else $message = "data not found";
					}else $message = "id or slugs required for news";
					$this->response(array("code"=>$resp, "message"=>$message, "data"=>$data));
			}

			public function save_post($id=0){
						$this->auth();
						$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : '';

						$title = $this->post("title");
						$content = $this->post("content");
						$status = $this->post("is_publish");
						$cstatus = $this->post("comment_enabled");
						$excerpt = $this->post("excerpt");
						$category = $this->post("category");
						$featured_image = $this->post("image");
						$meta_description = $this->post("meta_description");
						$tags = $this->post("tags");
						$resps = $data = array();
						if(!empty($title) && !empty($content) && !empty($category)){
								$data_upd["title"] = $title;
								$zlugs = str_replace(' ', '-', strtolower($title)); // Replaces all spaces with hyphens.
								$zlugs = preg_replace('/[^A-Za-z0-9\-]/', '', $zlugs); // Removes special chars.
								$data_upd["slug"] = $zlugs;
								if(!empty($id)) $this->db->where(array("id !="=>$id));
								$cslug = $this->db->get_where("news", array("slug"=>$slugs));
								if($cslug && $cslug->num_rows() > 0){
										$resps = array("code"=>REST_Controller::HTTP_CREATED, "message"=>"title already exist");
								}else{
										$data_upd["tags"] = (!empty($tags)) ? implode(",", $tags) : '';
										$data_upd["excerpt"] = $excerpt;
										$data_upd["content"] = $content;
										$data_upd["category_id"] = $category;
										// $data_upd["featured_image"] = $featured_image;
										$data_upd["status"] = (!$status) ? 1 : 2;
										$data_upd["comment_status"] = (!$cstatus) ? 0 : 1;
										if($status)$data_upd["published_at"] = date("Y-m-d H:i:s");
										if(!empty($id)){
												$partner_data = $this->news->detail($id);
												if(!empty($partner_data)){
														$upd = $this->news->update($data_upd, array("id"=>$id));
														if($upd){
																$data = array("id"=>$id);
																$resps = array("code"=>0, "message"=>"successfully update article");
														}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : fail to update partner");
												}else $resps = array("code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"error : data id not found");
										}else{
												$upd = $this->news->insert($data_upd);
												if($upd){
														$data = array("id"=>$upd);
														$resps = array("code"=>0, "message"=>"successfully create article");
												}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : fail to insert article");
										}
								}
						}else{
								$errmess = [];
								if(empty($content)) $errmess[] = "content";
								if(empty($title)) $errmess[] = "title";
								if(empty($category)) $errmess[] = "category";
								$mess = "";
								if(count($errmess) > 2) $mess = implode(", ", $errmess);
								else $mess = implode(" & ", $errmess);
								$mess = "$mess must not be empty";
								$resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : $mess");
						}
						$resps["data"]=$data;
						$this->response($resps, REST_Controller::HTTP_OK);
			}

			public function category_save_post($id=0){
						$this->auth();
						$uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : '';
						$title = $this->post("title");
						$description = $this->post("description");
						$image = $this->post("image");
						$resps = $data = array();
						if(!empty($title) && !empty($description)){
								$data_upd["title"] = $title;
								$slugs = str_replace(" ", "-", strtolower($title));
								$data_upd["slug"] = $slugs;
								if(!empty($id)) $this->db->where(array("id !="=>$id));
								$cslug = $this->db->get_where("news_category", array("slug"=>$slugs));
								if($cslug && $cslug->num_rows() > 0){
										$resps = array("code"=>REST_Controller::HTTP_CREATED, "message"=>"title already exist");
								}else{
										$data_upd["description"] = $description;
										$data_upd["image"] = $image;
										if(!empty($id)){
												$partner_data = $this->news->detail_cat($id);
												if(!empty($partner_data)){
														$upd = $this->news->update_cat($data_upd, array("id"=>$id));
														if($upd){
															$data = array("id"=>$id);
															$resps = array("code"=>0, "message"=>"successfully update category");
														}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : fail to update category");
												}else $resps = array("code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"error : data id not found");
										}else{
												$upd = $this->news->insert_cat($data_upd);
												if($upd){
														$data = array("id"=>$upd);
														$resps = array("code"=>0, "message"=>"successfully create category");
												}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : fail to insert category");
										}
								}
						}else{
								$mess = (empty($description)) ? "description must not be empty" : "title must not be empty";
								$resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : $mess");
						}
						$resps["data"]=$data;
						$this->response($resps, REST_Controller::HTTP_OK);
			}

			public function delnews_delete($id=0){
					if(!empty($id)){
							$usvend = $this->news->detail($id);
							if(!empty($usvend)){
									$del = $this->news->delete(array("id"=>$id));
									if($del){
											$resps = array("code"=>0, "message"=>"successfully delete news");
									}else $resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : something wrong happen when delete news");
							} else $resps = array("code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"error : data news not found");
					}else{
							$resps = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : request can't be empty");
					}
					$resps["data"]=[];
					$this->response($resps, REST_Controller::HTTP_OK);
			}

			public function delcat_delete($id=0){
					if(!empty($id)){
							$usvend = $this->news->detail_cat($id);
							if(!empty($usvend)){
									$del = $this->news->delete_category(array("id"=>$id));
									if($del){
											$resp = array("code"=>0, "message"=>"successfully delete category");
									}else $resp = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : something wrong happen when delete category");
							} else $resp = array("code"=>REST_Controller::HTTP_NOT_FOUND, "message"=>"error : data category not found");
					}else{
							$resp = array("code"=>REST_Controller::HTTP_BAD_REQUEST, "message"=>"error : request can't be empty");
					}
					$resp["data"]=[];
					$this->response($resp, REST_Controller::HTTP_OK);
			}

			private function upload_files($path, $title, $files)
		  {
		        $config = array(
		            'upload_path'   => $path,
		            'allowed_types' => 'jpg|jpeg|png|gif|bmp|webp'
		        );

		        $this->load->library('upload', $config);

		        $images = array();

		        $_FILES['file']['name']= $files['name'];
		        $_FILES['file']['type']= $files['type'];
		        $_FILES['file']['tmp_name']= $files['tmp_name'];
		        $_FILES['file']['error']= $files['error'];
		        $_FILES['file']['size']= $files['size'];
		        $fileName = $title;
		        // $fileName = $_FILES['file']['name'];
		        $images = $fileName;
		        $config['file_name'] = $fileName;

		        $this->upload->initialize($config);

		        if ($this->upload->do_upload('file')) {
		            $upload_data = $this->upload->data();
								$images = $upload_data['file_name'];
		        } else return false;

		        return $images;
		  }

			public function react_put($slug=''){
					$this->auth();

					$user = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : '';

	        $message = "";
	        $data = array();
	        $zxc = false;
	        $resp = REST_Controller::HTTP_NOT_FOUND;
	        if(!empty($user)){
	            if(!empty($slug)){
								$ceus = $this->db->select("id")->get_where("news_react_user", array("news_id"=>$slug, "created_by"=>$user));
	              if($ceus && $ceus->num_rows() > 0){
	                    $ins = $this->db->delete("news_react_user", array("id"=>$ceus->row()->id));
	                    $message = "you unlike this post";
											$like = false;
	              }else{
	                  $ins_data = array("id"=>get_uuid(), "news_id"=>$slug, "created_by"=>$user);
	                  $ins = $this->db->insert("news_react_user", $ins_data);
	                  $message = "you like this post";
										$like = true;
	              }


	              $message = ($ins) ? $message : "something went wrong when you like this post";
	              $resp = ($ins) ? 0 : REST_Controller::HTTP_NOT_FOUND;
	              $zxc= ($ins) ? true : false;
								if($ins){
									$sqlr = $this->db->select("COUNT(id) as total")->get_where("news_react_user", ["news_id"=>$slug]);
									$totreact = ($sqlr && $sqlr->num_rows() > 0) ? (int)$sqlr->row()->total : 0;
									$data = [
											"liked"=>$like,
											"likes"=>$totreact
									];
								}
	            }else $message = "news can't be empty";
	        }else{
	            $resp = REST_Controller::HTTP_UNAUTHORIZED;
	            $message = "unauthorized";
	        }
					$resps = array("code"=>$resp, "message"=>$message, "data"=>$data);
					$this->response($resps, REST_Controller::HTTP_OK);
	    }
}

/* End of file News.php */
