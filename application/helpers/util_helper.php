<?php

require_once(APPPATH.'/hooks/custom_log.php');

function log_custom_message($str){
	$CI =& get_instance();
	
	$CI->custom_log_messages[] = date('Y-m-d H:i:s') . ' - ' . $str;
}

function setCustomerDBNameIntoEloquent($dbName){
	global $capsule;
		
	$container = $capsule->getContainer();
	
	$connections = $container['config']['database.connections'];

	$connections['customerdbconnection']['database']=$dbName;
	
	$container['config']['database.connections'] = $connections;
}

function writeLogsAndDie(){
	$clog = new Custom_log();
	$clog->logMessages();
	die();
}
/**
 * Calculate center of given coordinates
 * @param  array    $coordinates    Each array of coordinate pairs
 * @return array                    Center of coordinates
 */
function getCoordsCenter($coordinates) {    
    $lats = $lons = array();
   
    foreach ($coordinates as $key => $value) {
        //echo $value[0];die();
        array_push($lats, $value['lat']);
        array_push($lons, $value['lng']);
    }
    $minlat = min($lats);
    $maxlat = max($lats);
    $minlon = min($lons);
    $maxlon = max($lons);
    $lat = $maxlat - (($maxlat - $minlat) / 2);
    $lng = $maxlon - (($maxlon - $minlon) / 2);
    return array("lat" => $lat, "lng" => $lng);
}

/*
 inputFieldValidation function info:
 $postdata is $_POST / $_GET data from html form
 $postVar  is varible names from htm form
 $labelname  is used to render the lables
 $dbData is array and store the fields data 
 $dbVar is database column name
 $validations is validation formats
 $inputdata final return data array
 $tablename is optional or pass the tablename for 
*/

function inputFieldValidation($postdata, $postVar,$labelname=null, &$dbData, $dbVar, $validations,  $inputdata = null, $tablename = 'data'){
	
	if ($inputdata == null || empty($inputdata) || !is_array($inputdata)){
		$inputdata = [];
	}
	
	$val = 	isset($postdata[$postVar])? $postdata[$postVar] : NULL;

	$dbData[$dbVar] = $val;
    
	if(empty($validations)){
		$inputdata['dbinput'][$tablename] = $dbData;
		return $inputdata;
	}
	
	foreach($validations as $validation)
	{   
	    $dateformat = '';
		switch($validation)
		{
			case 'email':
				if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
					$inputdata['errorslist'][]=$labelname.' Invalid Email Format';	 
				}
			break;
			case 'password':
				
				if(!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $val)){
					$inputdata['errorslist'][]=$labelname.' The Password does not meet the requirements!';	 
				}	
			break;
			case 'inputnumber':
				if (!filter_var($val, FILTER_VALIDATE_INT)) {
					$inputdata['errorslist'][]=$labelname.' Invalid Number';	 
				}
			break;
			case 'inputstring':
				if (!filter_var($val, FILTER_VALIDATE_STRING)) {
					$inputdata['errorslist'][]=$labelname.' Invalid String';	 
				}
			break;
			case 'url':
				if (!filter_var($val, FILTER_VALIDATE_URL)) {
					$inputdata['errorslist'][]=$labelname.' Invalid URL address';	 
				}
			break;
			case 'ipaddress':
				if (!filter_var($val, FILTER_VALIDATE_IP )) {
					$inputdata['errorslist'][]=$labelname.' Invalid IP Address';	 
				}
			break;
			case 'inputdate':
			
				if(!isItValidDate($val)) 
				{
					$inputdata['errorslist'][]=$labelname.' Entered Date is invalid..!!';	 
				}
				$dateformat=1;
				
			break;	
			case 'required':
				if(empty($val)){
					$inputdata['errorslist'][]=$labelname.' field is required!!';
				}
			break;	
			case 'minmaxpassword':
				if(strlen($val) < 8)
				{
				   $inputdata['errorslist'][] = $labelname." Field Name is too short, minimum is 12 characters (20 max).";
				}
				else if(strlen($val) > 20)
				{
				   $inputdata['errorslist'][] = $labelname." is too long, maximum is 20 characters.";
				}
			break;
		    case 'minlength':
				if(strlen($val) < 6)
				{
				   $inputdata['errorslist'][] = $labelname." is too short, minimum is 6 characters.";
				}
			break;
			case 'maxlength':
				if(strlen($val) >20)
				{
				   $inputdata['errorslist'][] = $labelname." is too long, maximum is 20 characters.";
				}
			break;
			case 'fieldname':
				if (!preg_match('/^[\p{L} ]+$/u', $val)){
					$inputdata['errorslist'][] = $labelname.' must contain Character and Spaces only!';
				}
			break;	
		}
	}

	if($dateformat==1){
		
		//$dbData[$dbVar] = $val;
		$dateFormatCha	= date("Y-m-d", strtotime($val));
		$dbData[$dbVar] = $dateFormatCha;
		$inputdata['dbinput'][$tablename] = $dbData;
    }
	else
	{		
		$inputdata['dbinput'][$tablename] = $dbData;
	}
	
	return $inputdata;
}


function dataFieldValidation($inputdata, $labelname=null, &$dbData, $dbVar, $validations,  
                                           $returndata = null, $tablename = 'data')
	{
	
	if ($returndata == null || empty($returndata) || !is_array($returndata)){
		$returndata = [];
	}
	
	$val = 	$inputdata;
	$dbData[$dbVar] = $val;
    
	if(empty($validations)){
		$returndata['dbinput'][$tablename] = $dbData;
		return $returndata;
	}
	
	foreach($validations as $validation)
	{
		$dateformat=0;
		switch($validation)
		{
			case 'email':
				if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
					$returndata['errorslist'][]=$labelname.' Invalid Email Format';	 
				}
			break;
			case 'password':
				
				if(!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $val)){
					$returndata['errorslist'][]=$labelname.' The Password does not meet the requirements!';	 
				}	
			break;
			case 'inputnumber':
				if (!filter_var($val, FILTER_VALIDATE_INT)) {
					$returndata['errorslist'][]=$labelname.' Invalid Number';	 
				}
			break;
			case 'inputstring':
				if (!filter_var($val, FILTER_VALIDATE_STRING)) {
					$returndata['errorslist'][]=$labelname.' Invalid String';	 
				}
			break;
			case 'url':
				if (!filter_var($val, FILTER_VALIDATE_URL)) {
					$returndata['errorslist'][]=$labelname.' Invalid URL address';	 
				}
			break;
			case 'ipaddress':
				if (!filter_var($val, FILTER_VALIDATE_IP )) {
					$returndata['errorslist'][]=$labelname.' Invalid IP Address';	 
				}
			break;
			case 'inputdate':
				
				if(!isItValidDate($val)) 
				{   
					$returndata['errorslist'][]=$labelname.' Entered Date is invalid..!!';
					
				}
				$dateformat=1;
			
			break;	
			case 'required':
				if(empty($val)){
					$returndata['errorslist'][]=$labelname.' field is required!!';
				}
			break;	
			case 'minmaxpassword':
				if(strlen($val) < 8)
				{
				   $returndata['errorslist'][] = $labelname." Field Name is too short, minimum is 12 characters (20 max).";
				}
				else if(strlen($val) > 20)
				{
				   $returndata['errorslist'][] = $labelname." is too long, maximum is 20 characters.";
				}
			break;
		    case 'minlength':
				if(strlen($val) < 6)
				{
				   $returndata['errorslist'][] = $labelname." is too short, minimum is 6 characters.";
				}
			break;
			case 'maxlength':
				if(strlen($val) >20)
				{
				   $returndata['errorslist'][] = $labelname." is too long, maximum is 20 characters.";
				}
			break;
			case 'fieldname':
				if (!preg_match('/^[\p{L} ]+$/u', $val)){
					$returndata['errorslist'][] = $labelname.' must contain Character and Spaces only!';
				}
			break;	
		}
	}
	 
	
	if($dateformat==1){
		
		$dbData2=date("Y-m-d", strtotime($val));
		$dbData[$dbVar] = $dbData2;
		$returndata['dbinput'][$tablename] = $dbData;		
	}
	else{
		$returndata['dbinput'][$tablename] = $dbData;	
	}
	return $returndata;
}

function isItValidDate($date){
	//MM/DD/YYYY Format
	if(preg_match("/^(\d{2})\/(\d{2})\/(\d{4})$/", $date, $matches)) 
	{
		if(checkdate($matches[1], $matches[2], $matches[3]))
		{
		  return true;
		}
	}
}

function isCreatedLog($userid=null){
	
	$isCreatedArray=array('created_on'=>date("Y-m-d H:i:s"),'created_ip'=>getUserIP(),'created_by'=>$userid);
	return $isCreatedArray;
	
}

function isUpdateLog($userid=null){
	
	$isCreatedArray=array('modified_on'=>date("Y-m-d H:i:s"),'modified_ip'=>getUserIP(),'modified_by'=>$userid);
	return $isCreatedArray;
	
}
function encode($string,$key) {
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
	$j=0;$hash='';
    for ($i = 0; $i < $strLen; $i++) {
        $ordStr = ord(substr($string,$i,1));
        if ($j == $keyLen) { $j = 0; }
        $ordKey = ord(substr($key,$j,1));
        $j++;
        $hash .= strrev(base_convert(dechex($ordStr + $ordKey),16,36));
    }
    return $hash;
}
function decode($string,$key) {
    $key = sha1($key);
    $strLen = strlen($string);
    $keyLen = strlen($key);
	$j=0;$hash='';
    for ($i = 0; $i < $strLen; $i+=2) {
        $ordStr = hexdec(base_convert(strrev(substr($string,$i,2)),36,16));
        if ($j == $keyLen) { $j = 0; }
        $ordKey = ord(substr($key,$j,1));
        $j++;
        $hash .= chr($ordStr - $ordKey);
    }
    return $hash;
}
//find Address from Latitude and Longitude
function getAddress($lat,$lng)
{
$url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.trim($lat).','.trim($lng).'&sensor=false';
$json = @file_get_contents($url);
$jsondata = json_decode($json,true);

$status = $jsondata["status"];
	
	if($status=="OK"){
		$countrycode=getCountryCode($jsondata);
		$countryid=getCountryId($countrycode);
		$address = array('address'=>getAddressValue($jsondata),'street' => getStreet($jsondata),'city' => getCity($jsondata),'state' => getState($jsondata),'pincode' => getPostalCode($jsondata),'country' =>$countryid );
		return $address;
	}
	else{
		return array();
	}
}
//find Country ID from country code
function getCountryId($countrycode){
	$CI=& get_instance();
	$CI->load->database();
	$sql = "SELECT id FROM countries WHERE shortcode='".$countrycode."'"; 
	$query = $CI->db->query($sql);
	$countryid = $query->row()->id;
	return $countryid;
}
//get Address from Google Json data
function getAddressValue($jsondata) {
    return longNameGivenType("premise", $jsondata["results"][0]["address_components"]). ', ' .longNameGivenType("sublocality_level_2", $jsondata["results"][0]["address_components"]). ', ' .longNameGivenType("sublocality_level_3", $jsondata["results"][0]["address_components"]);
}
function getCountry($jsondata) {
    return longNameGivenType("country", $jsondata["results"][0]["address_components"]);
}
function getState($jsondata){
    return longNameGivenType("administrative_area_level_1", $jsondata["results"][0]["address_components"], true);
}
function getCity($jsondata) {
    return longNameGivenType("locality", $jsondata["results"][0]["address_components"]);
}
function getStreet($jsondata) {
    return longNameGivenType("street_number", $jsondata["results"][0]["address_components"]) . ', ' . longNameGivenType("route", $jsondata["results"][0]["address_components"]). ', ' .longNameGivenType("sublocality_level_1", $jsondata["results"][0]["address_components"]);
}
function getPostalCode($jsondata) {
    return longNameGivenType("postal_code", $jsondata["results"][0]["address_components"]);
}
function getCountryCode($jsondata) {
    return longNameGivenType("country", $jsondata["results"][0]["address_components"], true);
}

/*
* Searching in Google Geo json, return the long name given the type. 
* (If short_name is true, return short name)
*/
function longNameGivenType($type, $array, $short_name = false) {
    foreach( $array as $value) {
        if (in_array($type, $value["types"])) {
            if ($short_name)    
                return $value["short_name"];
            return $value["long_name"];
        }
    }
}
//Tracker ID Generation
function trackerIdGenerate(){
	$date = date_create();
	$timestamp=date_timestamp_get($date);
	$random=rand(0000,9999);
	$uniqueid=floor($timestamp+$random);
	return $uniqueid;
}
//Distance Between two points
function getDistanceBetweenPoints($lat1, $lon1, $lat2, $lon2) {
   
	 $radius = 3959;  //approximate mean radius of the earth in miles, can change to any unit of measurement, will get results back in that unit

    $delta_Rad_Lat = deg2rad($lat2 - $lat1);  //Latitude delta in radians
    $delta_Rad_Lon = deg2rad($lon2 - $lon1);  //Longitude delta in radians
    $rad_Lat1 = deg2rad($lat1);  //Latitude 1 in radians
    $rad_Lat2 = deg2rad($lat2);  //Latitude 2 in radians

    $sq_Half_Chord = sin($delta_Rad_Lat / 2) * sin($delta_Rad_Lat / 2) + cos($rad_Lat1) * cos($rad_Lat2) * sin($delta_Rad_Lon / 2) * sin($delta_Rad_Lon / 2);  //Square of half the chord length
    $ang_Dist_Rad = 2 * asin(sqrt($sq_Half_Chord));  //Angular distance in radians
    $distance = $radius * $ang_Dist_Rad;  

    return $distance;  
}
#---- OLD ----
function getLongLat($address){
	
	$loc = array();
	$geo = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false');
	$geo = json_decode($geo, true);
	if ($geo['status'] = 'OK') {
	$loc['latitude'] = isset($geo['results'][0]['geometry']['location']['lat']) ? $geo['results'][0]['geometry']['location']['lat'] : '';
	$loc['longitude'] = isset($geo['results'][0]['geometry']['location']['lng']) ? $geo['results'][0]['geometry']['location']['lng'] : '';
    return $loc;
    }else{
     return false;
    }
  
}
function sendEmail($from, $fromName, $to, $subject, $body, $attachments = null)
{
	$CI =& get_instance();
	
	if (isset($CI->config->config['environment']) && $CI->config->config['environment'] == 'test'){
		
		$CI->load->library('email', $CI->config->config['smtpsettings']);
		
		$CI->email->initialize($CI->config->config['smtpsettings']);
	} else{
		
		$CI->load->library('email', $CI->config->config['smtpsettingslive']);
		
		$CI->email->initialize($CI->config->config['smtpsettingslive']);
	}
	$CI->email->set_newline("\r\n");
	
	$CI->email->from($from, $fromName);
	
	if (isset($CI->config->config['environment']) && $CI->config->config['environment'] == 'test'){
		if (endsWith($to, "hyperthread.in")){
			$CI->email->to($to);
			$CI->email->cc("testing@hyperthread.in");
			$CI->email->subject($subject);
		} else{
			$CI->email->to("testing@hyperthread.in");
			$CI->email->subject($subject.' - ' .$to);
		}
	} else{
		$CI->email->to($to);
		$CI->email->subject($subject);
		$CI->email->cc("testing@hyperthread.in");
	}
	$CI->email->message($body);
	
	if ($attachments != null && !empty($attachments)){
        foreach($attachments as $a){
			$CI->email->attach(FCPATH.$a);
        }
	}

	try
	{
		$ss = $CI->email->send();
		//var_dump($CI->email->print_debugger());
		//die();
		return true;
	}
	catch (Exception $e) 
	{
		//var_dump($CI->email->print_debugger());
		//var_dump($e);
		return false;
	}
	return false;
}
function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); 
    $alphaLength = strlen($alphabet) - 1; 
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); 
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
}

function endsWith($haystack, $needle) {
	// search forward starting from end minus needle length characters
	return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

function uniqueMail($email,$id = null)
{  
	$CI=& get_instance();
	$CI->load->database();
	if($id != 0){
		$idcondition="\n AND id!=".$id;
	}
	else{
		$idcondition="";
	}
	$sql = "SELECT count(*) as count FROM users WHERE email='".$email."' $idcondition"; 
	$query = $CI->db->query($sql);
	$row = $query->row()->count;
	return $row;
}
function getUserIP()
{
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$user_ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$user_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$user_ip = $_SERVER['REMOTE_ADDR'];
	}
	
	return $user_ip;
}
function getHostURL($includebaseurl = false)
{
	if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
		return trim('https://'.$_SERVER['HTTP_HOST'] . '/' . ($includebaseurl  == true ? base_url() : ''));
	} else {
		return trim('http://'.$_SERVER['HTTP_HOST'] . '/' . ($includebaseurl  == true ? base_url() : ''));
	}
}
function getBaseURL($includebaseurl = false)
{
	if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on"){
		return trim('https://'.$_SERVER['HTTP_HOST'] . '/');
	} else {
		return trim('http://'.$_SERVER['HTTP_HOST'] . '/' );
	}
}
function removeSplChar($filename){
    
	$filename = preg_replace("/[^a-zA-Z0-9.]/", "", $filename);
	$filename = strtolower(pathinfo($filename, PATHINFO_FILENAME)) . '.' . strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	//$filename = preg_replace('/.+/', '.', $filename);
	return $filename;
   
}

function uniqueEmpCode($empcode,$id = null)
{  
	$CI=& get_instance();
	$CI->load->database();
	if($id != 0){
		$idcondition="\n AND user_id!=".$id;
	}
	else{
		$idcondition="";
	}
	$sql = "SELECT count(*) as count FROM user_details WHERE emp_code='".$empcode."' $idcondition"; 
	$query = $CI->db->query($sql);
	$row = $query->row()->count;
	return $row;
}

?>