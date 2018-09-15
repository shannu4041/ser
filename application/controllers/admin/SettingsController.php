<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH . 'controllers/admin/BaseController.php');
use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;
class SettingsController extends BaseController {
	public function __construct()
	{
		 parent::__construct();
		 
		 $this->load->library(array('session','form_validation','ValidationTypes','excel'));
		 $this->load->helper(array('form','html'));
		 $this->load->database();
    	 $this->load->model('Settings_model');
	}
	
	public function getSettingData()
	{
		echo json_encode($this->Settings_model->getsettings());	
    }
	
	public function AddUpdateModel()
	{
		
	}
}
?>