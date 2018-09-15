<?php defined('BASEPATH') OR exit('No direct script access allowed');
use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;
ini_set('memory_limit','2048M');

class Welcome extends MY_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->library(array('form_validation','Excel_reader','ValidationTypes'));
		$this->load->helper(array('form','html','Util'));
		$this->load->database();
		$this->load->model('User');
		
		log_custom_message("Welcome Controller Constructor Called");
	}

	public function index()
	{
		
		echo "You are not authorized to access this service point";
	}
	
	public function is_user_logged_in(){ 
		if($this->is_user_logged_in) {
			$data = [
					'user_id'  => $this->auth_user_id,
					'username' => $this->auth_username,
					'user_roles'    => $this->auth_user_roles,
					'issuperadmin'     => $this->issuperadmin,
					'email'    => $this->auth_email
				];
			
			echo json_encode(array("success"=> true, "data" => $data));
			return;
		} else {
			echo json_encode(array("success"=> false));
			return;
		}
	}
	
	public function login(){
		try{
			log_custom_message("Login Method Called");
			if($this->is_user_logged_in) {
				$data = [
						'user_id'  => $this->auth_user_id,
						'username' => $this->auth_username,
						'user_roles'    => $this->auth_user_roles,
						'email'    => $this->auth_email
					];
				
				echo json_encode(array("success"=> true, "data" => $data));
				return;
			}
			
			$postdata = file_get_contents("php://input");
			$data = json_decode($postdata);
			$_POST =  json_decode(file_get_contents("php://input"), true);
			
			$dbData = [];
			$errors=array();
			$errors = inputFieldValidation($_POST, "Email", "Email", $dbData, "email", [ValidationTypes::REQUIRED,ValidationTypes::EMAIL], $errors);
			$errors = inputFieldValidation($_POST, "Password","Password", $dbData,"password",[ValidationTypes::REQUIRED,ValidationTypes::MIN_MAX_PASSWORD], $errors);
			
			if(isset($postData['errorslist']) && is_array($errors['errorslist'])){
				if(count($errors['errorslist'])>0){
					echo json_encode(array('success'=>false,'message'=>$errors['errorslist']));
					return;
				}
			}
			
			$username  = isset($data->Email) ? $data->Email : 'admin@example.com';
			$password = isset($data->Password) ? $data->Password : 'password';
			
			
			$remember = false;
			if (isset($data->rememberme)){
				$remember = $data->rememberme;
			}
			
			if (empty($username) || empty($password))
			{   
				echo json_encode(array('success'=>false,'message'=>INVALID_USER_PWD_MSG));
				return;
			}
		    			
			$usr_result=$this->ion_auth->login($username, $password, $remember);
			if ($usr_result === true) //active user record is present
			{
				
				$user = User::find($this->ion_auth->get_user_id());
				$email = $user->email;
				$username = $user->username;
				$userroledata=User::userAccountsRole($this->ion_auth->get_user_id());
				$userroles=[];
				foreach($userroledata as $value){
					
					$userroles[]=$value->name;
					
				}
				
				//set the session variables
				$sessiondata = [
					'user_id'  => $this->ion_auth->get_user_id(),
					'username' => $username,
					'user_roles' => $userroles,
					'email'    => $email
				];
				$this->session->set_userdata($sessiondata);
				
				echo json_encode( array("success"=> true, 'data'=>$sessiondata));
				return;
			}
			else
			{
				echo json_encode( array("success"=> false, 'message'=> INVALID_USER_PWD_MSG));
				return;
			}
			
			
		}catch(Exception $ex){
			 log_custom_message("Error:" . $ex. print_r($_REQUEST, TRUE)
							. "\nJSON Data:\n" . file_get_contents("php://input"));
		}
	}
	
 }
?>