<?php
/**
 * Created by PhpStorm.
 * User: Abyan
 * Date: 22/11/2017
 * Time: 17:16
 */
class Pdfm
{
    public $param;
    public $pdf;

    public function __construct()
    {
      $CI = & get_instance();
    }

    function load($param=NULL)
    {
        if ($param == NULL || !is_array($param))
        {
            // $mpdf = new \Mpdf\Mpdf();
            return new \Mpdf\Mpdf(array("mode"=>"c","format"=>"A5", "default_font"=>"Roboto-Regular", "default_font_size"=>14));
        }
        else{
            $mode = (isset($param[0]) && !empty($param[0])) ? $param[0] : "en-GB-x";
            $formatPage = (isset($param[1]) && !empty($param[1])) ? $param[1] : "A5";
            $defaultFontSize = (isset($param[2]) && !empty($param[2])) ? $param[2] : 12;
            $defaultFont = (isset($param[3]) && !empty($param[3])) ? $param[3] : "Roboto-Regular";
            $marginLeft = (isset($param[4]) && !empty($param[4])) ? $param[4] : 1;
            $marginRight = (isset($param[5]) && !empty($param[5])) ? $param[5] : 1;
            $marginTop = (isset($param[6]) && !empty($param[6])) ? $param[6] : 1;
            $marginBottom = (isset($param[7]) && !empty($param[7])) ? $param[7] : 1;
            $marginHeader = (isset($param[8]) && !empty($param[8])) ? $param[8] : 0;
            $marginFooter = (isset($param[9]) && !empty($param[9])) ? $param[9] : 0;
            return new \Mpdf\Mpdf(array("mode"=>$mode, "format"=>$formatPage, "default_font_size"=>$defaultFontSize, "default_font"=>$defaultFont, $marginLeft, $marginRight, $marginTop, $marginBottom, $marginHeader, $marginFooter));
        }
    }
}
