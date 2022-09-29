<?php
/**
 * @author   Abyan Ahmad fathin <abyan.site@gmail.com>
 */
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('waktu_lalu')) {
    function waktu_lalu($timestamp)
    {
        $time_ago = strtotime($timestamp);
        // $current_time = time() + 7 * 60 * 60;
        $current_time = time();
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        $minutes = round($seconds / 60);           // value 60 is seconds
        $hours = round($seconds / 3600);           //value 3600 is 60 minutes * 60 sec
        $days = round($seconds / 86400);          //86400 = 24 * 60 * 60;
        $weeks = round($seconds / 604800);          // 7*24*60*60;
        $months = round($seconds / 2629440);     //((365+365+365+365+366)/5/12)*24*60*60
        $years = round($seconds / 31553280);     //(365+365+365+365+366)/5 * 24 * 60 * 60
        if ($seconds <= 60) {
            return "baru saja";
        } else if ($minutes <= 60) {
            if ($minutes == 1) {
                return "semenit yang lalu";
            } else {
                return "$minutes menit yang lalu";
            }
        } else if ($hours <= 24) {
            if ($hours == 1) {
                return "sejam yang lalu";
            } else {
                return "$hours jam yang lalu";
            }
        } else if ($days <= 7) {
            if ($days == 1) {
                return "kemarin";
            } else {
                return "$days hari yang lalu";
            }
        } else if ($weeks <= 4.3) //4.3 == 52/12
        {
            if ($weeks == 1) {
                return "seminggu yang lalu";
            } else {
                return "$weeks minggu yang lalu";
            }
        } else if ($months <= 12) {
            if ($months == 1) {
                return "sebulan yang lalu";
            } else {
                return "$months bulan yang lalu";
            }
        } else {
            if ($years == 1) {
                return "setahun yang lalu";
            } else {
                return "$years tahun yang lalu";
            }
        }
    }
}

if (!function_exists('calculate_date')) {
    function calculate_date($from, $to)
    {
        $time_ago = strtotime($from);
        // $current_time = time() + 7 * 60 * 60;
        $current_time = strtotime($to);
        $time_difference = $current_time - $time_ago;
        $seconds = $time_difference;
        // $minutes = round($seconds / 60);           // value 60 is seconds
        // $hours = round($seconds / 3600);           //value 3600 is 60 minutes * 60 sec
        $days = round($seconds / (60 * 60 * 24));          //86400 = 24 * 60 * 60;
        $weeks = round($seconds / 604800);          // 7*24*60*60;
        $months = round($seconds / 2629440, 2);     //((365+365+365+365+366)/5/12)*24*60*60
        $years = round($seconds / 31553280);     //(365+365+365+365+366)/5 * 24 * 60 * 60
        if ($days <= 7) {
            if ($days == 1) {
                return "1 day";
            } else {
                return "$days days";
            }
        }else if ($weeks <= 4.3) //4.3 == 52/12
        {
            if ($weeks == 1) {
                return "1 week";
            } else {
                return "$weeks weeks";
            }
        } else if ($months <= 12) {
            $months = floor($months);
            if ($months == 1) {
                return "1 month";
            } else {
                return "$months months";
            }
        } else {
            if ($years == 1) {
                return "1 year";
            } else {
                return "$years years";
            }
        }
    }
}

if (!function_exists('calculate_timestamp')) {
    function calculate_timestamp($time_difference)
    {
        $seconds = $time_difference;
        // $minutes = round($seconds / 60);           // value 60 is seconds
        // $hours = round($seconds / 3600);           //value 3600 is 60 minutes * 60 sec
        $days = round($seconds / (60 * 60 * 24));          //86400 = 24 * 60 * 60;
        $weeks = round($seconds / 604800);          // 7*24*60*60;
        $months = round($seconds / 2629440, 2);     //((365+365+365+365+366)/5/12)*24*60*60
        $years = round($seconds / 31553280);     //(365+365+365+365+366)/5 * 24 * 60 * 60
        $yearnr = round($seconds / 31553280, 2);     //(365+365+365+365+366)/5 * 24 * 60 * 60
        if ($days <= 7) {
            if ($days == 1) {
                return "1 day";
            } else {
                return "$days days";
            }
        }else if ($weeks <= 4.3) //4.3 == 52/12
        {
            if ($weeks == 1) {
                return "1 week";
            } else {
                return "$weeks weeks";
            }
        } else if ($months <= 12) {
            $months = floor($months);
            if ($months == 1) {
                return "1 month";
            } else {
                return "$months months";
            }
        } else {
            $whole = floor($yearnr);

            if ($yearnr < 2) $wordyear =  "1 year";
            else $wordyear = "$whole years";

            $fraction = $yearnr - $whole;
            if($fraction > 0){
                $month_num = round($fraction * 12, 0, PHP_ROUND_HALF_UP);
                // $month_num = floor($month_num);
                if(!empty($month_num)){
                    $monthspell = ($month_num > 1) ? " months" : " month";
                    $wordyear .= " ".$month_num.$monthspell;
                }
            }

            return $wordyear;
        }
    }
}

if(!function_exists('s3_upload')){
    function s3_upload($files, $pathwithname="invoice/struck")
    {
          $ci =& get_instance();
          if(!isset($ci->s3)) $ci->load->library("S3", NULL, "s3");
          $ext = $ext = pathinfo($files["name"], PATHINFO_EXTENSION);
          $fileName = $pathwithname."_".time().".$ext";
          $tmpName = $files["tmp_name"];
          $bucket = getenv('S3_BUCKET');
          if(empty($bucket)) $bucket = getenv('/qa/ptf_force_api/config/S3_BUCKET');
          //check bucket
          if(!empty($bucket)){
              if($ci->s3->upload($fileName, $tmpName, (string)$bucket)){
                  return $ci->s3->geturl($fileName);
              }else return false;
          }else return false;
    }
}

if(!function_exists("number_shorten")){
		function number_shorten($number, $precision = 0, $divisors = null) {
    			// Setup default $divisors if not provided
    			if (!isset($divisors)) {
    					$divisors = array(
    							pow(1000, 0) => '', // 1000^0 == 1
    							pow(1000, 1) => 'K', // Thousand
    							pow(1000, 2) => 'M', // Million
    							pow(1000, 3) => 'B', // Billion
    							pow(1000, 4) => 'T', // Trillion
    							pow(1000, 5) => 'Qa', // Quadrillion
    							pow(1000, 6) => 'Qi', // Quintillion
    					);
    			}
    			// Loop through each $divisor and find the
    			// lowest amount that matches
    			foreach ($divisors as $divisor => $shorthand) {
    					if (abs($number) < ($divisor * 1000)) {
    							// We found a match!
    							break;
    					}
    			}
    			// We found our match, or there were no matches.
    			// Either way, use the last defined value for $divisor.
    			$snumb = round($number / $divisor, 2);
    			$_snumb = explode(".", $snumb);
    			$sacom = (count($_snumb) > 1) ? strlen($_snumb[1]) : 0;
    			$fcom = (count($_snumb) > 1) ? substr($_snumb[1], 0, 1) : 0;
    			if($fcom > 0){
    					$nformat = number_format($snumb, 1) . $shorthand;
    			}else $nformat = number_format($snumb) . $shorthand;
    			return $nformat;
		}
}

if(!function_exists('set_thumbnail')){
    function set_thumbnail($url, $filename, $width = 150, $height = true) {
      	 // download and create gd image
      	 $image = ImageCreateFromString(file_get_contents($url));

      	 // calculate resized ratio
      	 // Note: if $height is set to TRUE then we automatically calculate the height based on the ratio
      	 $height = $height === true ? (ImageSY($image) * $width / ImageSX($image)) : $height;
      	 $width_origin  = imagesx($image);
      	 $height_origin = imagesy($image);
      	 $centreX = round($width_origin / 2);
      	 $centreY = round($height_origin / 2);
      	 // create image
      	 // $output = ImageCreateTrueColor($width, $height);
      	 $output = imagecrop($image, ['x' => $centreX, 'y' => $centreY, 'width' => $width, 'height' => $height]);;

      	 // ImageCopyResampled($output, $image, 0, 0, 0, 0, $width, $height, ImageSX($image), ImageSY($image));

      	 // save image
      	 ImageJPEG($output, $filename, 95);
      	 // return resized image
      	 return $output; // if you need to use it
    }
}

if(!function_exists("stripTagsInArrayElements")){
		function stripTagsInArrayElements($input, $easy = false, $throwByFoundObject = true, $item_array=array())
		{
		        if ($easy) {
		            $output = array_map(function($v){
		                return trim(strip_tags($v));
		            }, $input);
		        } else {
		            $output = $input;
		            foreach ($output as $key => $value) {
		                if (is_string($value)) {
		                    $output[$key] = trim(strip_tags($value));
		                } elseif (is_array($value)) {
		                    if(isset($value["pubDate"])) unset($value["pubDate"]);

		                    if(isset($value["category"]) && !is_array($value["category"])){
		                      $value["category"] = array($value["category"]);
		                    }

		                    if(isset($value["guid"])){
													 $content_image = "https://via.placeholder.com/600x400.png/000FFF/FFF?text=Default+Image+News";
		                       if(!isset($value["enclosure"]) && !isset($value["media_content"])) $value["enclosure"] = array("@atrributes"=>array("url"=>"https://via.placeholder.com/600x400.png/000FFF/FFF?text=Default+Image+News", "length"=>"1504", "type"=>"image/png"));
													 else if(!isset($value["enclosure"]) && isset($value["media_content"])){
														 	$value["enclosure"]["@atrributes"] = $value["media_content"]["@attributes"];
															$type = "image/jpeg";
															if(!empty($value["media_content"]["@attributes"]["url"])){
																	$content_image = $value["media_content"]["@attributes"]["url"];
																	$url = $value["media_content"]["@attributes"]["url"];
																	$files = explode("/", $value["media_content"]["@attributes"]["url"]);
																	$image = Thumbnail($url, "contents/assets/images/".end($files), 200);
																	$value["enclosure"]["@atrributes"]["url"] = base_url()."contents/assets/images/".end($files);
																	$exts = (count($files) > 0) ? explode(".", end($files)) : array();
																	$ext = (count($exts) > 0) ? strtolower(end($exts)) : "";
																	switch($ext){
																			case "jpg" : $type = "image/jpeg";break;
																			case "png" : $type = "image/png";break;
																			case "gif" : $type = "image/gif";break;
																			case "webm" : $type = "image/webm";break;
																			default : $type = "image/jpeg";break;
																	}
															}
														 	$value["enclosure"]["@atrributes"]["length"] = "1054";
														 	$value["enclosure"]["@atrributes"]["type"] = $type;
															unset($value["media_content"], $value["media_title"], $value["media_description"]);
													 }else{
														 	$value["enclosure"]["@atrributes"] = $value["enclosure"]["@attributes"];
															$content_image = (isset($value["enclosure"]["@attributes"]["url"])) ? $value["enclosure"]["@attributes"]["url"] : $content_image;
															unset($value["enclosure"]["@attributes"]);
													 }
													 $value["content_image"] = $content_image;
													 if(!empty($item_array)){
														 	$value["identity"] = $item_array;
													 }
												}
		                    $output[$key] = stripTagsInArrayElements($value);
		                } elseif (is_object($value) && $throwByFoundObject) {
		                    echo 'Object found in Array by key ' . $key;
		                }
		            }
		        }
		        return $output;
		}
}

function download_page($path){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$path);
    curl_setopt($ch, CURLOPT_FAILONERROR,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    $retValue = curl_exec($ch);
    curl_close($ch);
    return $retValue;
}


if(!function_exists("json_prepare_xml")){
		function json_prepare_xml($domNode) {
				foreach($domNode->childNodes as $node) {
						if($node->hasChildNodes()) {
										json_prepare_xml($node);
						}else{
								if($domNode->hasAttributes() && strlen($domNode->nodeValue)){
										 $domNode->setAttribute("nodeValue", $node->textContent);
										 $node->nodeValue = "";
								}
						}
				}
		}
}

if(!function_exists("xml2JSON")){
	function xml2JSON($url="", $xml=""){
			$ctx = stream_context_create(array(
					'http' => array(
							'timeout' => 5
							)
					)
			);
			$dom = new DOMDocument();
			$xmlfile = (!empty($xml)) ? $xml : file_get_contents($url, 0, $ctx);
			$xmlfile = str_replace(array("\n", "\r", "\t"), '', $xmlfile);
			$xmlfile = trim(str_replace('"', "'", $xmlfile));
			$dom->loadXML($xmlfile);
			json_prepare_xml($dom);
			$sxml = simplexml_load_string($dom->saveXML());
			$json = json_decode(json_encode($sxml));
			return $json;
	}
}

if(!function_exists("crop_image")){
	function crop_image($url, $width, $height, $x = 0, $y = 0 )
	{
			$file    = file_get_contents( $url );
			$imagick = new Imagick;

			$imagick->readImageBlob( $file );
			$imagick->cropImage( $width, $height, $x, $y );

			$base64 = base64_encode( $imagick->getImageBlob() );

			return "data:image/jpeg;base64," . $base64;
	}
}

if(!function_exists("removeNamespaceFromXML")){
		function removeNamespaceFromXML( $xml ){
				// Because I know all of the the namespaces that will possibly appear in
				// in the XML string I can just hard code them and check for
				// them to remove them
				$toRemove = ['media'];
				// This is part of a regex I will use to remove the namespace declaration from string
				$nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';
				// Cycle through each namespace and remove it from the XML string
				foreach( $toRemove as $remove ) {
				        // First remove the namespace from the opening of the tag
				        $xml = str_replace('<' . $remove . ':', '<'.$remove.'_', $xml);
				        // Now remove the namespace from the closing of the tag
				        $xml = str_replace('</' . $remove . ':', '</'.$remove.'_', $xml);
				        // This XML uses the name space with CommentText, so remove that too
				        $xml = str_replace($remove . ':commentText', 'commentText', $xml);
				        // Complete the pattern for RegEx to remove this namespace declaration
				        $pattern = "/xmlns:{$remove}{$nameSpaceDefRegEx}/";
				        // Remove the actual namespace declaration using the Pattern
				        $xml = preg_replace($pattern, '', $xml, 1);
				}
				// Return sanitized and cleaned up XML with no namespaces
				return $xml;
		}
}

if(!function_exists("xmlJSON")){
		function xmlJSON($url="", $xml=""){
		  if(!empty($url)){
		      $ctx = stream_context_create(array(
		          'http' => array(
		              'timeout' => 5
		              )
		          )
		      );
		      $xml_string = file_get_contents($url, 0, $ctx);
					$xml_string = str_replace(array("\n", "\r", "\t"), '', $xml_string);
					$xml_string = trim(str_replace('"', "'", $xml_string));
					$xml_string = str_replace(PHP_EOL, '', $xml_string);;
		      $xml = simplexml_load_string(removeNamespaceFromXML($xml_string), 'SimpleXMLElement', LIBXML_NOCDATA);
					$ns = $xml->getDocNamespaces(true);
					foreach ( $ns as $prefix => $URI )   {
					    $xml->registerXPathNamespace($prefix, $URI);
					}
		      $json = json_decode(json_encode($xml), TRUE);
		      return $json;
		  }else if(!empty($xml)){
		      $xml = simplexml_load_string($xml);
		      $json = json_decode(json_encode($xml), TRUE);
		      return $json;
		  }
		  return FALSE;
		}
}

if(!function_exists("FosMerge")){
		function FosMerge($arr1, $arr2) {
		    $res=array();
		    $arr1=array_reverse($arr1);
		    $arr2=array_reverse($arr2);
		    foreach ($arr1 as $a1) {
		        if (count($arr1)==0) {
		            break;
		        }
		        array_push($res, array_pop($arr1));
		        if (count($arr2)!=0) {
		            array_push($res, array_pop($arr2));
		        }
		    }
		    return array_merge($res, $arr2);
		}
}

if(!function_exists('typeTrans')){
	function typeTrans($type_trans=0){
			switch($type_trans){
					case 1 : $typest = "Transfer In";break;
					case 2 : $typest = "Transfer";break;
					case 5 : $typest = "DO Payment";break;
					case 21 : $typest = "Voucher Creations";break;
					default : $typest = "Transfer";break;
			}
			return $typest;
	}
}

if(!function_exists('setUserType')){
	function setUserType($user_type=0){
			switch($user_type){
				case 0 : $regmer = "Inactive";break;
				case 1 : $regmer = "Reguler";break;
				case 3 : $regmer = "Merchant";break;
				case 4 : $regmer = "VIP";break;
				case 5 : $regmer = "Inactive";break;
				default : $regmer = "";break;
			}
			return $regmer;
	}
}

if (!function_exists("random_number")) {
    function random_number($digits=6)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
}

if(!function_exists("set_views")){
		function set_views($page="dashboard/dashboard", $data){
			$ci =& get_instance();
			$data["views"] = $page;
			$ci->load->view("index", $data);
		}
}

if(!function_exists('bulan_indo')){
	function bulan_indo($month='January'){
		$month = strtolower($month);
		$monthi = "Januari";
		switch($month){
				 case "january" : $monthi = "Januari";break;
				 case "01" : $monthi = "Januari";break;
				 case "february" : $monthi = "Februari";break;
				 case "02" : $monthi = "Februari";break;
				 case "march" : $monthi = "Maret";break;
				 case "03" : $monthi = "Maret";break;
				 case "april" : $monthi = "April";break;
				 case "04" : $monthi = "April";break;
				 case "may" : $monthi = "Mei";break;
				 case "05" : $monthi = "Mei";break;
				 case "june" : $monthi = "Juni";break;
				 case "06" : $monthi = "Juni";break;
				 case "july" : $monthi = "Juli";break;
				 case "07" : $monthi = "Juli";break;
				 case "august" : $monthi = "Agustus";break;
				 case "08" : $monthi = "Agustus";break;
				 case "september" : $monthi = "September";break;
				 case "09" : $monthi = "September";break;
				 case "october" : $monthi = "Oktober";break;
				 case "10" : $monthi = "Oktober";break;
				 case "november" : $monthi = "Nopember";break;
				 case "11" : $monthi = "Nopember";break;
				 case "december" : $monthi = "Desember";break;
				 case "12" : $monthi = "Desember";break;
				 default : $monthi = "Januari";break;
		}
		return $monthi;
	}
}

if(!function_exists("api_access")){
		function api_access($url, $data=array(), $auth=false, $type="POST", $token="", $devices=array())
		{
				$ci =& get_instance();
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
				if($type == "POST"){
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				}else curl_setopt($ch, CURLOPT_POST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				if(!$auth){
						if(empty($token)){
							$headers = $ci->input->get_request_header('Authorization');
							if (!empty($headers)) {
								if (preg_match('/Bearer\s(\S+)/', $headers , $matches)) $token = $matches[1];
							}
						}
						$arr_header = array(
								'Authorization: Bearer '.$token,
								'Content-Type: application/json'
						);
				}else{
					curl_setopt($ch, CURLOPT_ENCODING, '');
					$device_id = 'tappnote-connect';
					$device_name = 'tappnote-connect';
					if(!empty($devices) && is_array($devices)){
							$device_id = (isset($devices["id"]) && !empty($devices["id"])) ? $devices["id"] : 'nusapay-connect';
							$device_name = (isset($devices["name"]) && !empty($devices["name"])) ? $devices["name"] : 'nusapay-connect';
					}
					$arr_header = array(
						'X-Device-ID: '.$device_id,
						'X-Device-Name: '.$device_name
					);
				}
				curl_setopt($ch, CURLOPT_HEADER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
				// $result["info"] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
				$result = curl_exec($ch);
				return $result;
		}
}

if(!function_exists('generateKokattoAPISignedRequest')){
		function generateKokattoAPISignedRequest($parameters, $secretKey) {
        // construct $map like below (sorted & include all parameters):
        // print
        // [campaignName] => OPM
        // [clientId] => 8002
        // [codeLength] => 6
        // [destination] => 6285695744459
        // [timestamp] => 2021-04-22T14:37:49+07:00
        //sort map by key
        ksort($parameters);

        //construct map into query string
        $query = http_build_query($parameters);
        // print $query = campaignName=OPM&clientId=1111&codeLength=6&destination=082148893982&timestamp=2021-04-22T14%3A37%3A49+07%3A00

        //md5 hash query string
        $queryMd5 = md5($query);
        // print generated md5 query

        //generate hmac hash with sha256 method
        $queryHmacSha256 = hash_hmac('sha256', $queryMd5, $secretKey);
        // print generate hmac

        //url encode hmac hash
        return strtoupper(urlencode($queryHmacSha256));
        // return encode url hmac
        // insert the signature into request body
    }
}

if(!function_exists("curl_api")){
		function curl_api($url, $data=array(), $type="GET", $token="")
		{
				$ci =& get_instance();
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
				if($type == "POST"){
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				}else curl_setopt($ch, CURLOPT_POST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				if(!empty($token)){
						if(strlen($token) < 5){
							$headers = $ci->input->get_request_header('Authorization');
							if (!empty($headers)) {
									if (preg_match('/Bearer\s(\S+)/', $headers , $matches)) $token = $matches[1];
							}
						}
						$arr_header = array(
								'Authorization: Bearer '.$token,
								'Content-Type: application/json'
						);
				}else{
						$arr_header = array(
								'Content-Type: application/json'
						);
				}
				curl_setopt($ch, CURLOPT_HEADER, TRUE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_header);
				// $result["info"] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
				$result = curl_exec($ch);
				return $result;
		}
}

if(!function_exists("single_post")){
		function single_post($url)
		{
				$rst = curl_api($url);
				$headers=array();
				$data=explode("\n",$rst);
				array_shift($data);
				foreach($data as $part){
						$middle=explode(":",$part);
						error_reporting(0);
						$headers[trim($middle[0])] = trim($middle[1]);
				}
				$resval = (array)json_decode(end($data), true);
				return $resval;
		}
}

if(!function_exists("xapi_post")){
		function xapi_post($url, $request, $token="")
		{
				if(is_array($request)) $request = json_encode($request);
				$rst = curl_api($url, $request, "POST", $token);
				$headers=array();
				$data=explode("\n",$rst);
				array_shift($data);
				foreach($data as $part){
						$middle=explode(":",$part);
						error_reporting(0);
						$headers[trim($middle[0])] = trim($middle[1]);
				}
				$resval = (array)json_decode(end($data), true);
				return $resval;
		}
}

if(!function_exists("resp_data")){
	function resp_data($message = "", $resp_code = 0, $meta = NULL){
			$ci =& get_instance();
			if(is_array($message) || is_object($message)){
					$meta = $message;
					$message = "";
			}
			$stat = array(
					"code"=>$resp_code,
					"message"=>$message
			);

			$attipe = (is_array($meta)) ? [] : null;
			$stat["data"] = (!empty($meta)) ? $meta : $attipe;
			$ci->response($stat, 200);
	}
}

if(!function_exists('get_uuid')){
		function get_uuid(){
				$ci =& get_instance();
        $column_func = (getenv('DB_DRIVER') == 'mysqli') ? "uuid()" : "md5(random()::text || clock_timestamp()::text)::uuid";
				$query = $ci->db->query("SELECT $column_func as uiyd");
				$guid = ($query && $query->num_rows() > 0) ? $query->row()->uiyd : "";
				return $guid;
		}
}

if(!function_exists('pintap_system_data')){
		function pintap_system_data($name='Pintap News'){
        $pintapdata = [
            'name'=>$name,
            'profile_picture_url'=>'https://cdn-ptf.pintap.id/static/news-icon.png'
        ];
        return $pintapdata;
		}
}

if (!function_exists('send_notification')) {
    function send_notification($registration_ids, $message, $type = "", $param = [])
    {
        $return_array = array();
        $fields = array(
            'registration_ids' => $registration_ids,
            'data' => array(
                "activity" => $type,
                "param" => $param,
                "desc" => $message
            ),
            'priority' => 10
        );
        $fkey = getenv('FIREBASE_KEY');
        if(empty($fkey)) $fkey = getenv('/qa/ptf_force_api/config/FIREBASE_KEY');
        $headers = array(
            'Authorization: key='.$fkey, // FIREBASE_API_KEY_FOR_ANDROID_NOTIFICATION
            'Content-Type: application/json'
        );
        $return_array["fields"] = $fields;
        $return_array["headers"] = $headers;
// Open connection
        $ch = curl_init();
// Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
// Execute post
        $result = curl_exec($ch);
        if ($result === false) {
            die('Curl failed:' . curl_errno($ch));
        }
// Close connection
        curl_close($ch);
        $return_array["result"] = $result;
        return $return_array;
    }
}

if (!function_exists('sendNotification')) {
			function sendNotification($device_tokens, $message, $data)
			{
					// $SERVER_API_KEY = getenv('FIREBASE_KEY');
					$SERVER_API_KEY = 'AAAAPJPmcV8:APA91bH-pVkGdVg8m8oBMCs33vvwpVNwZV26LW8lkkZzlQyPa0lXxsHcl5yNMjxNX8dVXbeGmhsXJlAKAiBnsZuQma2ino78n5fyElQGe45P3FrkjHZVZE6PZhsmv33eCzT-xxIGUPGT';

					// payload data, it will vary according to requirement
					if(is_array($device_tokens)){
							$data = [
									"registration_ids" => $device_tokens, // for multiple device ids
									"notification" => $message,
									"data" => $data,
							];
					}else{
						$data = [
								"to" => $device_tokens, // for single device ids
								"notification" => $message,
								"data" => $data,
						];
					}

					$dataString = json_encode($data);

					$headers = [
							'Authorization: key=' . $SERVER_API_KEY,
							'Content-Type: application/json',
					];

					$ch = curl_init();

					curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

					$response = curl_exec($ch);

					curl_close($ch);

					return $response;
			}
}

if (!function_exists('send_android_notification')) {
    function send_android_notification($registration_ids, $message, $type = "", $param = [], $title='test notif')
    {
				$ci =& get_instance();
				$json = null;

        $ci->load->library('fcm');
        $ci->fcm->setTitle($title);
        $ci->fcm->setMessage($message);
        $ci->fcm->setIsBackground(false);
        // set payload as null
        $payload = array('notification' => $message);
        $ci->fcm->setPayload($payload);
        // $ci->fcm->setImage('https://firebase.google.com/_static/9f55fd91be/images/firebase/lockup.png');
        $json = $ci->fcm->getPush();
				$result = $ci->fcm->sendMultiple($registration_ids, $json);
				return $result;
    }
}

if (!function_exists('setpush_notif')) {
    function setpush_notif($user_id = '', $notif_text = '', $activity = '', $params = [], $title='', $notif_image='')
    {
        $ci =& get_instance();

        if (!class_exists('REST_Controller')) {
            $ci->load->library("REST_Controller");
        }
        //set default types of this if empty
        if (empty($activity)) {
            $activity = 'send-otp';
        }

        if (!empty($user_id)) {
            // $ci->db->group_by("fcm_id");
            // $getdevice = $ci->db->get_where("motorist_devices", array("motorist_id" => $user_id));
            // if ($getdevice && $getdevice->num_rows() > 0) {
								$topic = '/topics/user_'.$user_id;
								$registid = array();
                // foreach ($getdevice->result() as $gav) {
                //     $registid[] = $gav->fcm_id;
                // }
                if (empty($params)) $params = $user_id;
								// $sndnotif = send_android_notification($registid, $notif_text, $activity, $params);
								$notif = [
								  'title' => $title,
								  'body' =>$notif_text,
								  'alert' => $title,
								  'sound' => "default",
								];

								$data_notif = [
										'title'=>$title,
										'body'=>$notif_text,
										'priority' => 'high',
    								'content_available' => true
								];

								if(!empty($notif_image)){
										$notif['image'] = $notif_image;
										$data_notif['image'] = $notif_image;
								}
                $sndnotif = sendNotification($topic, $notif, $data_notif);
								$requezzz = json_encode(['notif'=>$notif, 'data'=>$data_notif], true);
                $activities = "SET NOTIFICATION WITH RESULT : " . (string)json_encode($sndnotif);
                $ci->db->insert("app_log", array("id"=>get_uuid(), "user_id" => $user_id, "activities" => $activity, "activities_url" => current_url(), 'requests'=>$requezzz, 'responses'=> $activities));
								$owners = [
										'id'=>'',
										'owner'=>'system',
										'activity'=>$activity
								];
								$data_insertnot = [
										'title'=>$title,
										'message'=>$notif_text,
										'data'=>$params
								];
                $ci->db->insert("notifications", array("id"=>get_uuid(), "sales_id" => $user_id, "owner"=>json_encode($owners), "activity" => $activity, 'data'=> json_encode($data_insertnot)));
            // }
        }
    }
}

if (!function_exists('testpush_notif')) {
    function testpush_notif($user_id = '', $title='', $notif_text = '', $activity = '', $activity_id='', $params = [], $owners=[])
    {
        $ci =& get_instance();

        if (!class_exists('REST_Controller')) {
            $ci->load->library("REST_Controller");
        }

        $rezp = REST_Controller::HTTP_BAD_REQUEST;
        $message = "";
        $respOutput=[];

        //set default types of this if empty
        if (empty($activity)) {
            $activity = 'send-otp';
        }

        if (!empty($user_id)) {
            // $ci->db->group_by("fcm_id");
            // $getdevice = $ci->db->get_where("motorist_devices", array("motorist_id" => $user_id));
            // if ($getdevice && $getdevice->num_rows() > 0) {
								$topic = '/topics/user_'.$user_id;
								$registid = array();
                // foreach ($getdevice->result() as $gav) {
                //     $registid[] = $gav->fcm_id;
                // }
                if(empty($params)) $params = null;
								// $sndnotif = send_android_notification($registid, $notif_text, $activity, $params);
								$notif = [
								  'title' => $title,
								  'body' =>$notif_text,
								  'alert' => $title,
								  'sound' => "default",
								];

                $applog_id = get_uuid();
                $notif_id = get_uuid();

								$data_notif = [
										'title'=>$title,
										'body'=>$notif_text,
										'priority' => 'high',
                    'activity'=>$activity,
                    'activity_id'=>$activity_id,
                    'notif_id'=>$notif_id,
                    'user'=>$user_id,
    								'content_available' => true
								];

                if(empty($owners)){
                    $logo_owner = ($activity == 'news') ? 'https://cdn-ptf.pintap.id/static/news-icon.png' : 'https://cdn-ptf.pintap.id/static/pintap-force-icon.png';
                    $owners = [
                        'id'=>'',
                        'type'=>'system',
                        'owner'=>'Pintap Sales Force',
                        'logo_url'=>$logo_owner
                    ];
                }

                $data_insertnot = [
                    'title'=>$title,
                    'message'=>$notif_text,
                    'data'=>$params
                ];
                $ci->db->insert("notification", array("id"=>$notif_id, "sales_id" => $user_id, "owner"=>json_encode($owners), "activity" => $activity, "source"=>$activity, "source_id"=>$activity_id, 'data'=> json_encode($data_insertnot)));


								$sndnotif = sendNotification($topic, $notif, $data_notif);
								$requezzz = json_encode(['notif'=>$notif, 'data'=>$data_notif], true);
                $activities = "SET NOTIFICATION WITH RESULT : " . (string)json_encode($sndnotif);
                $ci->db->insert("app_log", array("id"=>$applog_id, "user_id" => $user_id, "activities" => $activity, "activities_url" => current_url(), 'requests'=>$requezzz, 'responses'=> $activities));
            // }
                $rezp = 0;
                $message = "notif sent successfully";
                $respNotif = ($sndnotif !== false && !is_array($sndnotif)) ? json_decode($sndnotif, true) : $sndnotif;
                $respOutput = ['request'=>['notif'=>$notif, 'data'=>$data_notif], "response"=>$respNotif];
        }else $message = "user_id is required";

        $output = ['code'=>$rezp, 'message'=>$message];
        if(!empty($respOutput)) $output['data']=$respOutput;
        return $output;
    }
}

if (!function_exists('notif_backoffice')) {
    function notif_backoffice($actor='', $activity = '', $activity_type='', $sender = '', $sender_type='', $params=[], $object='')
    {
        $ci =& get_instance();

        if (!class_exists('REST_Controller')) {
            $ci->load->library("REST_Controller");
        }

        $rezp = REST_Controller::HTTP_BAD_REQUEST;
        $message = "";
        $respOutput=[];

        //set default types of this if empty
        if (empty($activity)) {
            $activity = 'registration';
        }

        if (empty($activity_type)) {
            $activity_type = 'salesman_registration';
        }

        if (empty($sender_type)) {
            $sender_type = 'system';
        }

        if (!empty($actor)) {

                if(!empty(getenv('BASE_URL'))){
                    if(getenv('BASE_URL') == 'https://dev-xapi-salesforce.pintap.id' || getenv('BASE_URL') == 'http://sforce.test'){
                      $url = "https://dev-ptf-api.pintap.id/notification.web.add";
                    }else{
                      $url = "https://ptf-api.pintap.id/notification.web.add";
                    }
                }else{
                    $url = "https://qa-ptf-api.pintap.id/notification.web.add";
                }

                $sqact = $ci->db->select("sales_name as name")->get_where("sales_force", ["id"=>$actor]);
                $actorName = ($sqact && $sqact->num_rows() > 0) ? $sqact->row()->name : "";

                if(empty($sender)){
                    $sqad = $ci->db->limit(1,0 )->select("id")->get("admin");
                    $sender = ($sqad && $sqad->num_rows() > 0) ? $sqad->row()->id : "thEUEkEyLGgsOcoFecx6BeFnuSB2";
                }

                if(empty($params)){
                    $params = [
                        'id' => $actor
                    ];
                }

            		$notif = [
								  'userId' => $sender,
								  'userRole' => "ADMIN",
                  'page' => $activity_type,
								  'pageData' => json_encode($params),
								  'actor' => $actorName,
                  'type' => $activity,
                  'sender' => $sender_type
								];

                if(isset($object)) $notif['object'] = $object;

                $data_notif["notif"][0] = $notif;

                $applog_id = get_uuid();

                $sndnotif = curl_api($url, json_encode($data_notif), "POST");

                $headers=[];

        				$datanotif=explode("\n",$sndnotif);
        				array_shift($datanotif);
        				foreach($datanotif as $part){
        						$middle=explode(":",$part);
        						error_reporting(0);
        						$headers[trim($middle[0])] = trim($middle[1]);
        				}
        				$resnotif = (array)json_decode(end($datanotif), true);

								$requezzz = json_encode(['notif'=>$notif, 'data'=>$data_notif], true);
                $activities = "send notif to backoffice with result : " . (string)json_encode($resnotif);
                $ci->db->insert("app_log", array("id"=>$applog_id, "user_id" => $actor, "activities" => $activity, "activities_url" => current_url(), 'requests'=>$requezzz, 'responses'=> $activities));

                $rezp = 0;
                $message = "notif sent successfully";
                $respNotif = ($resnotif !== false && !is_array($resnotif)) ? json_decode($resnotif, true) : $resnotif;
                $respOutput = ['request'=>['notif'=>$notif, 'data'=>$data_notif], "response"=>$respNotif];

        }else $message = "actor is required";

        $output = ['code'=>$rezp, 'message'=>$message];
        if(!empty($respOutput)) $output['data']=$respOutput;
        return $output;
    }
}

if(!function_exists('get_location')){
		function get_location($lat='', $long=''){
				$keyapi = getenv('GOOGLE_KEY');
        if(empty($keyapi)) $keyapi = getenv('/qa/ptf_force_api/config/GOOGLE_KEY');
				$data = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$long&sensor=false&language=id&key=$keyapi");
				$data = json_decode($data);
				$address = null;
				$country = "Not found";
				$state = "Not found";
				$district = "Not found";
				$subdistrict = "Not found";
				$city = "Not found";
				if(isset($data->results[0])){
						$add_array  = $data->results;
						$add_array = $add_array[0];
						$address = (isset($add_array->formatted_address)) ? $add_array->formatted_address : $address;
						$addcomp = $add_array->address_components;
						foreach ($addcomp as $key) {
							  if($key->types[0] == 'administrative_area_level_4'){
							    	$subdistrict = str_replace('Kelurahan ', '', $key->long_name);
							  }
							  if($key->types[0] == 'administrative_area_level_3'){
							    	$district = str_replace('Kecamatan ', '', $key->long_name);
							  }
							  if($key->types[0] == 'administrative_area_level_2'){
							    	$city = $key->long_name;
							  }
							  if($key->types[0] == 'administrative_area_level_1'){
							    	$state = $key->long_name;
							  }
							  if($key->types[0] == 'country'){
							    	$country = $key->long_name;
							  }
						}
				}

				$data_loc = [
						'subdistrict'=>$subdistrict,
						'district'=>$district,
						'city'=>$city,
						'state'=>$state,
						'country'=>$country,
						'address'=>$address
				];

				return $data_loc;
		}
}

if(!function_exists('query_street')){
		function query_street($input=''){
				$keyapi = getenv('GOOGLE_KEY');
        if(empty($keyapi)) $keyapi = getenv('/qa/ptf_force_api/config/GOOGLE_KEY');
				$data = file_get_contents("https://maps.googleapis.com/maps/api/place/queryautocomplete/json?input=$input&language=id&key=$keyapi");
				$data = json_decode($data);
				$add_array = [];
				if(isset($data->predictions)){
						$add_array = $data->predictions;
				}
				return $add_array;
		}
}

if(!function_exists('calculate_distance')){
  	function calculate_distance($lat1, $lon1, $lat2, $lon2, $unit="K") {
    	  $theta = $lon1 - $lon2;
    	  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    	  $dist = acos($dist);
    	  $dist = rad2deg($dist);
    	  $miles = $dist * 60 * 1.1515;
    	  $unit = strtoupper($unit);

    	  if ($unit == "K") {
    				$reskm = $miles * 1.609344;
    				if($reskm < 1){
    						$rezkm = $reskm;
    				}else $rezkm = round($reskm, 2);

    	      return $rezkm;
    	  } else if ($unit == "N") {
    	      return ($miles * 0.8684);
    	  } else {
    	      return $miles;
    	  }
  	}
}

if(!function_exists("set_response")){
	function set_response($message = "", $resp_code = 0, $meta = NULL){
			$ci =& get_instance();
			if(is_array($message) || is_object($message)){
					$meta = $message;
					$message = "";
			}
			$stat = array(
					"code"=>$resp_code,
					"message"=>$message
			);
			if($resp_code == 0){
				$stat["data"] = (!empty($meta)) ? $meta : [];
			}else if(!empty($meta)) $stat["data"] = $meta;
			$ci->response($stat, 200);
	}
}

if(!function_exists("stringEncryption")){
		function stringEncryption($action, $string){
			  $output = false;
			  $encrypt_method = 'AES-256-CBC';                // Default
			  $secret_key = 'tapOn#Key!';               // Change the key!
			  $secret_iv = '!NP@_$2';  // Change the init vector!
			  // hash
			  $key = hash('sha256', $secret_key);
			  // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
			  $iv = substr(hash('sha256', $secret_iv), 0, 16);
			  if( $action == 'encrypt' ) {
			      $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			      $output = base64_encode($output);
			  }
			  else if( $action == 'decrypt' ){
			      $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
			  }
			  return $output;
		}
}

if(!function_exists("secret_decode")){
	function secret_decode($token=""){
				$data = array();
				if(!empty($token)){
							$data = json_decode(stringEncryption("decrypt", $token));
							if(empty($data)) $data = array();
				}
				return $data;
	}
}

if(!function_exists("generateRandomString")){
		function generateRandomString($length = 10) {
		    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		    $charactersLength = strlen($characters);
		    $randomString = '';
		    for ($i = 0; $i < $length; $i++) {
		        $randomString .= $characters[rand(0, $charactersLength - 1)];
		    }
		    return $randomString;
		}
}

function date_id($date, $format = 'l, j F Y', $wib=true)
{
    /*
        Format :

        d - The day of the month (from 01 to 31)
        D - A textual representation of a day (three letters)
        j - The day of the month without leading zeros (1 to 31)
        l - (lowercase 'L') - A full textual representation of a day
        N - The ISO-8601 numeric representation of a day (1 for Monday, 7 for Sunday)
        S - The English ordinal suffix for the day of the month (2 characters st, nd, rd or th. Works well with j)
        w - A numeric representation of the day (0 for Sunday, 6 for Saturday)
        z - The day of the year (from 0 through 365)
        W - The ISO-8601 week number of year (weeks starting on Monday)
        F - A full textual representation of a month (January through December)
        m - A numeric representation of a month (from 01 to 12)
        M - A short textual representation of a month (three letters)
        n - A numeric representation of a month, without leading zeros (1 to 12)
        t - The number of days in the given month
        L - Whether it's a leap year (1 if it is a leap year, 0 otherwise)
        o - The ISO-8601 year number
        Y - A four digit representation of a year
        y - A two digit representation of a year
        a - Lowercase am or pm
        A - Uppercase AM or PM
        B - Swatch Internet time (000 to 999)
        g - 12-hour format of an hour (1 to 12)
        G - 24-hour format of an hour (0 to 23)
        h - 12-hour format of an hour (01 to 12)
        H - 24-hour format of an hour (00 to 23)
        i - Minutes with leading zeros (00 to 59)
        s - Seconds, with leading zeros (00 to 59)
        u - Microseconds (added in PHP 5.2.2)
        e - The timezone identifier (Examples: UTC, GMT, Atlantic/Azores)
        I - (capital i) - Whether the date is in daylights savings time (1 if Daylight Savings Time, 0 otherwise)
        O - Difference to Greenwich time (GMT) in hours (Example: +0100)
        P - Difference to Greenwich time (GMT) in hours:minutes (added in PHP 5.1.3)
        T - Timezone abbreviations (Examples: EST, MDT)
        Z - Timezone offset in seconds. The offset for timezones west of UTC is negative (-43200 to 50400)
        c - The ISO-8601 date (e.g. 2013-05-05T16:34:42+00:00)
        r - The RFC 2822 formatted date (e.g. Fri, 12 Apr 2013 12:01:05 +0200)
        U - The seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
    */

    $hari = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu'
    ];

    $bulan_M = [
        'Jan' => 'Jan',
        'Feb' => 'Feb',
        'Mar' => 'Mar',
        'Apr' => 'Apr',
        'May' => 'Mei',
        'Jun' => 'Jun',
        'Jul' => 'Jul',
        'Aug' => 'Agu',
        'Sep' => 'Sep',
        'Oct' => 'Okt',
        'Nov' => 'Nov',
        'Dec' => 'Des'
    ];

    $bulan_F = [
        'January'   => 'Januari',
        'February'  => 'Februari',
        'March'     => 'Maret',
        'April'     => 'April',
        'May'       => 'Mei',
        'June'      => 'Juni',
        'July'      => 'Juli',
        'August'    => 'Agustus',
        'September' => 'September',
        'October'   => 'Oktober',
        'November'  => 'November',
        'December'  => 'Desember'
    ];

    $strtotime = (!$wib) ? strtotime($date) : strtotime($date.'+7hours');

    // January
    if($format == 'F')
    {
        $Fm = date('F', $strtotime);
        return $bulan_F[$Fm];
    }

    // January 2019
    if($format == 'F Y')
    {
        $Y = date('Y', $strtotime);
        $Fm = date('F', $strtotime);
        return $bulan_F[$Fm].' '.$Y;
    }

    // 1 Jan 2019
    if($format == 'j M Y')
    {
        $j = date('j', $strtotime);
        $M = date('M', $strtotime);
        $Y = date('Y', $strtotime);

        return $j.' '.$bulan_M[$M].' '.$Y;
    }

    // 1 Jan 2019 11:01
    if($format == 'j M Y H:i')
    {
        $j = date('j', $strtotime);
        $M = date('M', $strtotime);
        $Y = date('Y', $strtotime);
				$time = date('H:i', $strtotime);

        return $j.' '.$bulan_M[$M].' '.$Y.' '.$time;
    }

    // 1 Januari 2019
    elseif($format == 'j F Y')
    {
        $j = date('j', $strtotime);
        $F = date('F', $strtotime);
        $Y = date('Y', $strtotime);

        return $j.' '.$bulan_F[$F].' '.$Y;
    }

    // Selasa, 1 Jan 2019
    elseif($format == 'l, j M Y')
    {
        $l = date('l', $strtotime);
        $j = date('j', $strtotime);
        $M = date('M', $strtotime);
        $Y = date('Y', $strtotime);

        return $hari[$l].', '.$j.' '.$bulan_M[$M].' '.$Y;
    }

    // Selasa, 1 Jan 2019 07:00
    elseif($format == 'l, j M Y H:i')
    {
        $l = date('l', $strtotime);
        $j = date('j', $strtotime);
        $M = date('M', $strtotime);
        $Y = date('Y', $strtotime);
        $time = date('H:i', $strtotime);

        return $hari[$l].', '.$j.' '.$bulan_M[$M].' '.$Y.' '.$time;
    }

    // Selasa, 1 Januari 2019
    elseif($format == 'l, j F Y')
    {
        $l = date('l', $strtotime);
        $j = date('j', $strtotime);
        $F = date('F', $strtotime);
        $Y = date('Y', $strtotime);

        return $hari[$l].', '.$j.' '.$bulan_F[$F].' '.$Y;
    }

    // Selasa, 1 Januari 2019 07:00
    elseif($format == 'l, j F Y H:i')
    {
        $l = date('l', $strtotime);
        $j = date('j', $strtotime);
        $F = date('F', $strtotime);
        $Y = date('Y', $strtotime);
        $time = date('H:i', $strtotime);

        return $hari[$l].', '.$j.' '.$bulan_F[$F].' '.$Y.' '.$time;
    }
}

function AWS_S3_PresignDownload($canonical_uri, $expires = 604800) {
    // Creates a signed download link for an AWS S3 file
    // Based on https://gist.github.com/kelvinmo/d78be66c4f36415a6b80
		$AWSAccessKeyId = (!empty(getenv('S3_KEY'))) ? getenv('S3_KEY') : getenv('/qa/ptf_force_api/config/S3_KEY');
		$AWSSecretAccessKey = (!empty(getenv('S3_SECRET'))) ? getenv('S3_SECRET') : getenv('/qa/ptf_force_api/config/S3_SECRET');
		$BucketName = (!empty(getenv('S3_BUCKET'))) ? getenv('S3_BUCKET') : getenv('/qa/ptf_force_api/config/S3_BUCKET');
		$AWSRegion = 'ap-southeast-1';
    $encoded_uri = str_replace('%2F', '/', rawurlencode($canonical_uri));

    // Specify the hostname for the S3 endpoint
    $hostname =  trim($BucketName . ".s3-" . $AWSRegion . ".amazonaws.com");
    $header_string = "host:" . $hostname . "\n";
    $signed_headers_string = "host";
		$timenow = time() + 14 * 60 * 60;
    $date_text = gmdate('Ymd', $timenow);
    $time_text = $date_text . 'T000000Z';
    $algorithm = 'AWS4-HMAC-SHA256';
    $scope = $date_text . "/" . $AWSRegion . "/s3/aws4_request";

    $x_amz_params = array(
        'X-Amz-Algorithm' => $algorithm,
        'X-Amz-Credential' => $AWSAccessKeyId . '/' . $scope,
        'X-Amz-Date' => $time_text,
        'X-Amz-SignedHeaders' => $signed_headers_string
    );

    if ($expires > 0) {
        // 'Expires' is the number of seconds until the request becomes invalid
        $x_amz_params['X-Amz-Expires'] = $expires;
    }

    ksort($x_amz_params);

    $query_string = "";
    foreach ($x_amz_params as $key => $value) {
        $query_string .= rawurlencode($key) . '=' . rawurlencode($value) . "&";
    }
    $query_string = substr($query_string, 0, -1);

    $canonical_request = "GET\n" . $encoded_uri . "\n" . $query_string . "\n" . $header_string . "\n" . $signed_headers_string . "\nUNSIGNED-PAYLOAD";
    $string_to_sign = $algorithm . "\n" . $time_text . "\n" . $scope . "\n" . hash('sha256', $canonical_request, false);
    $signing_key = hash_hmac('sha256', 'aws4_request', hash_hmac('sha256', 's3', hash_hmac('sha256', $AWSRegion, hash_hmac('sha256', $date_text, 'AWS4' . $AWSSecretAccessKey, true), true), true), true);
    $signature = hash_hmac('sha256', $string_to_sign, $signing_key);

    return 'https://' . $hostname . $encoded_uri . '?' . $query_string . '&X-Amz-Signature=' . $signature;

}
