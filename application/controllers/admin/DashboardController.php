<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH . 'controllers/admin/BaseController.php');
use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;
class DashboardController extends BaseController {
	public function __construct()
	{
		 parent::__construct();
		 
		 $this->load->library(array('session','form_validation','ValidationTypes','excel'));
		 $this->load->helper(array('form','html'));
		 $this->load->database();
    	 $this->load->model('Common_model');
		 log_custom_message("Dashboard Controller Constructor Called");
	}
	//Get Dashboard Drivers, vehicals,trips count List
	public function getDashboardData()
	{
		
		
    }
}
?>