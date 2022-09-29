<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Location extends BS_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        $this->load->model('M_Sales', 'motor');
    }

    public function latlong_get(){
            $message = "";
            $latitude = $this->get("lat");
            $longitude = $this->get("long");
            $data = array();
            $resp = 0;
            if(!empty($latitude) && !empty($longitude)){
                $data_loc = get_location($latitude, $longitude);
                if(!empty($data_loc)){
                      $resp = 0;
                      $cities = $provinces = $districts = [];
                      if(isset($data_loc["district"])){
                          $disdis = $data_loc["district"];
                          $dista = $this->db->select("county_code as id, county_name as name")->get_where("districts", "county_name LIKE '%$disdis%'");
                          $districts = ($dista && $dista->num_rows() > 0) ? $dista->row_array() : ['id'=>'', 'name'=>$data_loc["district"]];
                      }
                      if(isset($data_loc["city"])){
                          $citcit = $data_loc["city"];
                          $cits = $this->db->select("city_code as id, city_name as name")->get_where("cities", "city_name LIKE '%$citcit%'");
                          $cities = ($cits && $cits->num_rows() > 0) ? $cits->row_array() : ['id'=>'', "name"=>$citcit];
                      }
                      if(isset($data_loc["state"])){
                          $provprov = $data_loc["state"];
                          $prov = $this->db->select("province_code as id, name")->get_where("provinces", "name LIKE '%$provprov%'");
                          $provinces = ($prov && $prov->num_rows() > 0) ? $prov->row_array() : ["id"=>"", "name"=>$provprov];

                      }
                      $data = ["province"=>$provinces, "city"=>$cities, "district"=>$districts, "address"=>(isset($data_loc["address"])) ? $data_loc["address"] : ""];
                }else $message = "data city not found";
            }else $message = "latitude and longitude are required";

            resp_data($message, $resp, $data);
    }

    public function cari_get(){
            $message = "";
            $q = $this->get("q");
            $data = [];
            $resp = REST_Controller::HTTP_BAD_REQUEST;
            if(!empty($q)){
                $data_loc = query_street(urlencode($q));
                if(!empty($data_loc)){
                      $resp = 0;
                      $data = $data_loc;
                }else{
                    $resp = REST_Controller::HTTP_NOT_FOUND;
                    $message = "list address not available";
                }
            }else $message = "query autocomplete are required";
            resp_data($message, $resp, $data);
    }

    public function province_get(){
            $message = "";
            $data = array();
            $resp = 0;
            $subcatlist = $this->db->get("province");
                if($subcatlist && $subcatlist->num_rows() > 0){
                  $resp = 0;
                  foreach ($subcatlist->result() AS $eachSubcat){
                      $data[] = array("id"=>$eachSubcat->province_code, "name"=>$eachSubcat->province_name);
                  }
            }else $message = "data city not found";

            resp_data($message, $resp, $data);
    }

    public function city_get($code=''){
            $message = "";
            $data = array();
            $resp = REST_Controller::HTTP_NOT_FOUND;
            if(!empty($code)){
                $subcatlist = $this->db->get_where("city", array("province_code"=>$code));
                if($subcatlist && $subcatlist->num_rows() > 0){
                  $resp = 0;
                  foreach ($subcatlist->result() AS $eachSubcat){
                      $data[] = array("id"=>$eachSubcat->city_code, "name"=>$eachSubcat->city_name);
                  }
                }else $message = "data city not found";
            }else $message = "province is required to see city list";

            resp_data($message, $resp, $data);
    }

    public function onlycity_get(){
            $message = "";
            $cari = $this->get("search");
            $resp = 0;
            $data = array();
            $this->db->limit(20, 0);
            // $this->db->order_by("city_code", "ASC");
            $this->db->order_by("province_code", "ASC");
            if(!empty($cari)){
                $cari = strtolower($cari);
                $this->db->where("LOWER(city_name) LIKE '%$cari%'");
            }
            $subcatlist = $this->db->get("city");
            if($subcatlist && $subcatlist->num_rows() > 0){
                foreach ($subcatlist->result() AS $eachSubcat){
                      $type = (strpos($eachSubcat->city_name, "Kota ") !== false) ? "Kota" : "Kabupaten";
                      $citname = str_replace("Kota ", "", $eachSubcat->city_name);
                      $citname = str_replace("Kabupaten ", "", $citname);
                      $data[] = array("id"=>$eachSubcat->city_code, "name"=>$citname, "type"=>$type, "country"=>"Indonesia");
                }
            }else{
                $message = "data city not found";
            }

            resp_data($message, $resp, $data);
    }

    public function district_get($code=''){
            $message = "";
            $data = array();
            $resp = REST_Controller::HTTP_NOT_FOUND;
            if(!empty($code)){
                $subcatlist = $this->db->get_where("district", array("city_code"=>$code));
                if($subcatlist && $subcatlist->num_rows() > 0){
                  $resp = 0;
                  foreach ($subcatlist->result() AS $eachSubcat){
                      $data[] = array("id"=>$eachSubcat->district_code, "name"=>$eachSubcat->district_name);
                  }
                }else $message = "data district not found";
            }else $message = "city is required to see district list";

            resp_data($message, $resp, $data);
    }

    public function subdistrict_get($code=''){
            $message = "";
            $data = array();
            $resp = REST_Controller::HTTP_NOT_FOUND;
            if(!empty($code)){
                $subcatlist = $this->db->get_where("subdistrict", array("district_code"=>$code));
                if($subcatlist && $subcatlist->num_rows() > 0){
                  $resp = 0;
                  foreach ($subcatlist->result() AS $eachSubcat){
                      $data[] = array("id"=>$eachSubcat->subdistrict_code, "name"=>$eachSubcat->subdistrict_name, "zip_code"=>$eachSubcat->zip_code);
                  }
                }else $message = "data subdistrict not found";
            }else $message = "district code is required to see subdistrict list";

            resp_data($message, $resp, $data);
    }

}
