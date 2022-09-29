<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pages extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    public function list_get(){

        $message = "";
        $resp = 0;
        $pages = $this->db->select("id, title, slug")->get("static_page");
        $data = ($pages && $pages->num_rows() > 0) ? $pages->result() : [];

        resp_data($message, $resp, $data);

    }

    public function detail_get($slug=''){

        $resp = 0;
        $data = array();
        $message = "";
        if(!empty($slug)){
            $pages = $this->db->select("id, title, slug, COALESCE(content, '') as content, created_at")->get_where("static_page", ["slug"=>$slug]);
            if($pages && $pages->num_rows() > 0){
                    $data = $pages->row();
            }else{
                $resp = REST_Controller::HTTP_NOT_FOUND;
                $message = "page not found";
            }
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "page slug is required";
        }

        set_response($message, $resp, $data);
    }

}
