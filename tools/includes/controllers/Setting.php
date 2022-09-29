<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setting extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model("M_Sales", "motor");
    }

    public function list_get(){
        $message = "";
        $resp = 0;
        $z0p = $this->db->select("name, site_value")->get("site_options");
        $data = null;
        if($z0p && $z0p->num_rows() > 0){
            foreach($z0p->result() as $zp){
                $data[$zp->name] = $zp->site_value;
            }
        }
        set_response($message, $resp, $data);
    }

    public function info_get(){
        $message = "";
        $resp = 0;

        $this->auth();
        $uid = (isset($this->sess["user_id"])) ? $this->sess["user_id"] : 0;
        $motor = $this->motor->find($uid);
        $data['complete_profile'] = $complete_profile = (isset($motor->profile_picture_url) && !empty($motor->profile_picture_url)) ? true : false;

        $n0p = $this->db->select("COUNT(id) as total")->get_where("notification", "read_at IS NULL AND sales_id = '$uid'");
        $data["notif_count"] = ($n0p && $n0p->num_rows() > 0) ? (int)$n0p->row()->total : 0;

        set_response($message, $resp, $data);
    }

}
