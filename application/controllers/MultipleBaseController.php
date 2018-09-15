<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Query\Expression as raw;
include_once(APPPATH . 'controllers/CommonBaseController.php');
class MultipleBaseController extends CommonBaseController {
	public function __construct($redirectType = "Angular")
	{
		parent::__construct($redirectType);
		
		if(!isset($this->session->userdata) || count($this->session->userdata('user_roles')) == 0 || 
			!in_array('Admin',$this->session->userdata('user_roles')) || !in_array('SuperAdmin',$this->session->userdata('user_roles'))) {
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			if ($redirectType == "Normal"){
				echo json_encode(array('success'=>false,'message'=>INVALID_USER_PWD_MSG));
			} else{
				header($this->cprotocol . ' 401');
			}
			writeLogsAndDie();
		}
	}
}
?>