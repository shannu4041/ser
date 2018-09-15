<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;
include_once(APPPATH . 'controllers/CommonBaseController.php');
class BaseController extends CommonBaseController {
	public function __construct($redirectType = "Angular")
	{
		parent::__construct($redirectType);
		
		 if(!isset($this->session->userdata) || count($this->session->userdata('user_roles')) == 0 || (!in_array('Admin',$this->session->userdata('user_roles')) && !in_array('User',$this->session->userdata('user_roles'))) ) {
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			if ($redirectType == "Normal"){
				echo json_encode(array('success'=>false,'message'=>INVALID_USER_PWD_MSG));
			} else{
				header($this->cprotocol . ' 401');
			}
			writeLogsAndDie();
		}
	}
	
	public function getSecureProfileAccess($actionClass)
	{   
	   // $issuperadmin = $this->ion_auth->is_admin();
		
		if (in_array('Admin',$this->session->userdata('user_roles'))){
			return array((object)array('has_access'=>1));
		} else {
			$loginId    = $this->ion_auth->get_user_id();
			
				$checkaccessprofile = Capsule::Select("SELECT pa.has_access FROM `profile_actions` AS pa JOIN profiles as pr ON pr.id = pa.profile_id JOIN actions  as a ON pa.action_id=a.id JOIN menus m ON a.menu_id=m.id WHERE pa.profile_id IN (SELECT profile_id FROM `user_profiles` WHERE user_id = ".$loginId.") AND a.action_class='$actionClass' AND pa.has_access = 1 GROUP BY a.action_class ORDER BY `pa`.`has_access` ASC ");
				
				return	$checkaccessprofile;
			
		}	
	}
	
	
}
?>