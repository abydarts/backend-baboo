<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Banner extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
    }

    public function list_get(){

        $message = "";
        $resp = 0;
        $bannerz = $this->db->limit(5,0)->order_by("order", "asc")->select("title, image_url, activity, COALESCE(description, '') as description, COALESCE(destination, '') as target")->get_where("banner", ["status"=>"posted"]);
        $data = ($bannerz && $bannerz->num_rows() > 0) ? $bannerz->result() : [];

        resp_data($message, $resp, $data);

    }

    public function detail_get($id=0){

        $resp = 0;
        $data = array();
        $message = "";
        if(!empty($id)){
            $bannerz = $this->db->select("title, image_url, activity, COALESCE(description, '') as description, COALESCE(destination, '') as target")->get_where("banner", ["id"=>$id]);
            if($bannerz && $bannerz->num_rows() > 0){
                    $data = $bannerz->row();
            }else{
                $resp = REST_Controller::HTTP_NOT_FOUND;
                $message = "banner not found";
            }
        }else{
            $resp = REST_Controller::HTTP_NOT_FOUND;
            $message = "banner id is required";
        }

        set_response($message, $resp, $data);
    }

}
