<?php
/**
 * Created by PhpStorm.
 * User: Win_10
 * Date: 28/02/2018
 * Time: 9:27
 */

class Amazon extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("S3", NULL, "s3");
        $this->bucket = getenv('S3_BUCKET');
        $this->load->helper("form");
    }
    public function upload_file(){
         if (!empty($_FILES) && $_FILES["file"]["error"] != 4) {
            $files = $_FILES["file"]["name"];
            $ext = $ext = pathinfo($files, PATHINFO_EXTENSION);
            $fileName = "coupon/coupon_".time().".$ext";
            $tmpName = $_FILES["file"]["tmp_name"];
            if ($this->s3->upload($fileName, $tmpName, $this->bucket)) {
                $this->session->set_flashdata("success", "Berhasil upload file $fileName");

                // redirect(site_url("amazon/lihat_file"));
            } else {
                $this->session->set_flashdata("error", "Gagal upload file");
                redirect("amazon/upload_file");
            }
        }
        $this->load->view("upload_file");
    }

    public function lihat_file() {
        $data["list"] = $this->s3->listFile($this->bucket);
        $data['files'] = $this->s3->geturl("coupon/coupon_1641879133.png");
        // $data['files'] = AWS_S3_PresignDownload("/coupon/coupon_1641790199.jpg");
        $this->load->view('lihat_file', $data);
    }
}
