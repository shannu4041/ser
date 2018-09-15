<?php defined('BASEPATH') OR exit('No direct script access allowed');
ob_start();
include_once(APPPATH . 'models/ActivityLog.php');
use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;

class CommonBaseController extends MY_Controller {
	protected $redirectType;
	
	public function __construct($redirectType = "Angular"){ 
		$this->redirectType = $redirectType;
		ob_clean();
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url','html','form','Util','language'));
		
		session_write_close();
		
		if ($this->config->item('maintanance') == TRUE && !in_array($_SERVER['REMOTE_ADDR'],explode(',',$this->config->item('maintananceallowips')))){
			echo MAINTANANCE_MSG; ## call view
			writeLogsAndDie();
		}
		
		 if(!$this->is_user_logged_in) {
			if ($redirectType == "Normal"){
				echo json_encode(array('success'=>false,'message'=>INVALID_USER_PWD_MSG));
			} else{
				header($this->cprotocol . ' 350 /');
			}
			writeLogsAndDie();
		} 
	}
	//security user profile implementation
	public function getSecureProfileAccess($actionMethod)
	{   
	    log_custom_message("CommonBase Controller - getSecureProfileAccess Method Called");
		$loginId    = $this->ion_auth->get_user_id();
		$account_id = $this->session->userdata('auth_account_id');
		$AccDbName  = $this->Account_model->getDBName($account_id);
		$currentdatabase=$this->db->database;
		if($AccDbName)
		{	
			$checkaccessprofile = Capsule::connection('customerdbconnection')->Select("SELECT pa.has_access FROM `profile_actions` AS pa JOIN profiles as pr ON pr.id = pa.profile_id JOIN $currentdatabase.actions  as a ON pa.action_id=a.id JOIN $currentdatabase.menus m ON a.menu_id=m.id WHERE pa.profile_id IN (SELECT profile_id FROM `user_profiles` WHERE user_id = ".$loginId.") AND a.action_method='$actionMethod' AND pa.has_access = 1 GROUP BY a.action_method ORDER BY `pa`.`has_access` ASC ");
			return	$checkaccessprofile;
		
		}
			
	}
}
?>




