<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 *	CodeIgniter Amazon S3 library in PHP by zairwolf
 *
 *	Source: https://github.com/zairwolf/CodeIgniter-AmazonS3/blob/master/S3.php
 *
 *	Author: Hai Zheng @ https://www.linkedin.com/in/zairwolf/
 *
 */
require_once APPPATH.'libraries/vendor/autoload.php';
require_once 'REST_Controller.php';
use Aws\S3\S3Client;
class S3{
    public $s3hd	= false;
    protected $CI;
    public function __construct(){
        $this->CI =& get_instance();
        //initialize s3 connection
        $region = REST_Controller::region_singapore;

        if(!$this->s3hd) $this->s3hd = S3Client::factory(array(
            'credentials' => [
                'key'	=> (!empty(getenv('S3_KEY'))) ? getenv('S3_KEY') : getenv('/qa/ptf_force_api/config/S3_KEY'),
                'secret'	=> (!empty(getenv('S3_SECRET'))) ? getenv('S3_SECRET') : getenv('/qa/ptf_force_api/config/S3_SECRET')
            ],
            'version' => '2006-03-01',
            'region' => $region
        ));
    }

    public function listFile($Bucket=false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        $result = $this->s3hd->listObjects([
            'Bucket' => $Bucket
        ]);
        return $result;
    }

    public function getsBucket($Bucket=false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        $result = $this->s3hd->getBucket((string)$Bucket);
        return $result;
    }

    public function geturl($name, $Bucket=false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        return $this->s3hd->getObjectUrl((string)$Bucket, $name);
    }

    public function getPrivateUrl($name, $expires=604800, $Bucket=false){
        $args = array();
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        return $this->s3hd->getObjectUrl((string)$Bucket, $name, '+10 minutes');
    }

    public function read($name, $Bucket = false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        if(!$this->exist($name, $Bucket)) exit("File not exist: $name");
        $info = $this->s3hd->getObject(array(
            'Bucket'       => (string)$Bucket,
            'Key'          => $name,
        ));
        return $info['Body'];
    }

    public function setImage($name, $Bucket= false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        if(!$this->exist($name, $bucket)) exit("File not exist: $name");
        $info = $this->s3hd->create_object((string)$Bucket, $name, array(
            'contentType' => 'image/png'
        ));
        return $info;
    }

    public function del($name, $Bucket = false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        $info = $this->s3hd->deleteObject(array(
            'Bucket'       => (string)$Bucket,
            'Key'          => $name,
        ));
        return $info;
    }

    public function exist($name, $Bucket = false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        return $this->s3hd->doesObjectExist((string)$Bucket, $name);
    }
    public function upload($name, $file, $Bucket = false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        $result = $this->s3hd->putObject(array(
            'Bucket'       => (string)$Bucket,
            'Key'          => $name,
            'SourceFile'   => $file,
            'ACL'          => 'public-read',
            //'StorageClass' => 'REDUCED_REDUNDANCY',
        ));
        $this->s3hd->waitUntil('ObjectExists', array(
            'Bucket' => (string)$Bucket,
            'Key'    => $name,
        ));
        return $result;
    }

    public function write($name, $info, $Bucket = false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        $result = $this->s3hd->upload((string)$Bucket, $name, $info, 'public-read');
        $this->s3hd->waitUntil('ObjectExists', array(
            'Bucket' => (string)$Bucket,
            'Key'    => $name,
        ));
        return $result;
    }
    public function copyFile($src, $target, $Bucket = false){
        if(!$Bucket) $Bucket = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
        $info = $this->s3hd->copyObject(array(
            'Bucket'       => (string)$Bucket,
            'CopySource'   => $Bucket.'/'.$src,
            'Key'          => $target,
        ));
        return $info;
    }
}
