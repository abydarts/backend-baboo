<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cust404 extends BS_Controller {

  public function __construct() {
      parent::__construct();
      // load base_url
      $this->load->helper('url');
  }

  public function index(){
      set_response('unauthorized', REST_Controller::HTTP_UNAUTHORIZED);
  }

}
