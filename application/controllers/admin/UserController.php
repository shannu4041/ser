<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH . 'controllers/admin/BaseController.php');

use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;
class UserController extends BaseController {
	protected $auth_account_id;
	public function __construct()
	{
		
		 parent::__construct();
		 
		 $this->load->library(array('session','form_validation','excel','ValidationTypes'));
		 $this->load->helper(array('form','html','Util'));
		 $this->load->database();
		 $this->load->model('User');
		 $this->load->model('Userdetails_model');
		  $this->load->model('UserProfiles_model');
		  $this->load->model('Address_model');
		 $this->load->model('Customdata_model');
		 log_custom_message("Admin User Controller Constructor Called");
		 
		 $actionController =$this->router->fetch_class();

		 $check=BaseController::getSecureProfileAccess($actionController);
		 
			$accessValue=isset($check[0]->has_access)?$check[0]->has_access:0;
			if($accessValue == 0){
				echo json_encode(array('success'=>false,'message'=>ACCESS_RESULT));
				return;
			}
		 
	}
	
	public function getUserAll()
	{
		try{
			 log_custom_message("Get All Users Method Called");
			// echo "sajhsd";die;
			$paging = file_get_contents("php://input");
			$getuserdata = $this->User->getAllUsers(json_decode($paging));
			echo json_encode(array('success'=>true,'data'=>$getuserdata['data'],'totalrecords'=>$getuserdata['TotalRecords']));
		}
		catch(Exception $ex){
			throw new Exception($ex);	
		}
    }
	
	public function addUser(){ 
		try{
			log_custom_message("  Add User Method Called");
			
			$postdata = file_get_contents("php://input");
			$data = json_decode($postdata);
			
			$_POST =  json_decode(file_get_contents("php://input"), true);
			
			$userdetailsdata = array();
			$postData=array();
			$postData = inputFieldValidation($_POST, "first_name","First Name", $userdetailsdata, "first_name", [ValidationTypes::REQUIRED,ValidationTypes::INPUT_NAME], $postData,"usersdetailsarray");
			$postData = inputFieldValidation($_POST, "last_name","Last Name", $userdetailsdata,"last_name",[ValidationTypes::REQUIRED,ValidationTypes::INPUT_NAME], $postData,"usersdetailsarray");
			$postData= inputFieldValidation($_POST, "mobileno","Mobile Number", $userdetailsdata,"mobileno",[ValidationTypes::REQUIRED], $postData,"usersdetailsarray");
			$postData= inputFieldValidation($_POST, "emp_code","Employee Code", $userdetailsdata,"emp_code",[ValidationTypes::REQUIRED], $postData,"usersdetailsarray");
			
			$userdata = array();
			$postData= inputFieldValidation($_POST, "email","Email", $userdata, "email", [ValidationTypes::REQUIRED,ValidationTypes::EMAIL], $postData,"usersarray");
			$postData= inputFieldValidation($_POST, "user_password","Password", $userdata, "password", [ValidationTypes::REQUIRED], $postData,"usersarray");
			
			$userProfiles = isset($data->profile_id) ? $data->profile_id : '';
							
				$userprofile=array();
				
				if(isset($userProfiles) && !empty($userProfiles))
				{
						
					foreach($userProfiles as $key=>$udata)
					{
			
						$profile_id  = $udata;
						$postData = dataFieldValidation($profile_id, "User Profile", $userprofile, "profile_id", "", $postData, "userprofilearray".$key);
					}
				}
				
			
			$addressdata = [];
			$address=$data->address->address;
			$city = $data->address->city;
			$street = $data->address->street;
			$state = $data->address->state;
		
			$postData = dataFieldValidation($address,"Address", $addressdata, "address", "", $postData,"addressarray");
			$postData = dataFieldValidation($street,"Street",$addressdata,"street","", $postData,"addressarray");
			$postData= dataFieldValidation($city,"City", $addressdata,"city","", $postData,"addressarray");
			$postData= dataFieldValidation($state,"State",$addressdata,"state","", $postData,"addressarray");
			$postData= dataFieldValidation($data->address->pincode,"Zip",$addressdata,"pincode","", $postData,"addressarray");
			$postData= dataFieldValidation($data->address->country,"Country",$addressdata,"country","", $postData,"addressarray");
			if(isset($postData['errorslist']) && is_array($postData['errorslist'])){
				if(count($postData['errorslist'])>0){
					echo json_encode(array('success'=>false,'message'=>$postData['errorslist']));
					return;
				}
			}
			
			//print_r($postData);die();
			$email = isset($data->email) ? $data->email : '';
			$password = isset($data->user_password) ? $data->user_password : '';
			
			$result=uniqueMail($email);
			
			if($result>0)
			{
				echo json_encode(array('success'=>false,'message'=>EMAIL_EXISTS_MSG));
				return;
			}
			
			$empcode = isset($data->emp_code) ? $data->emp_code : '';
			
			$coderesult=uniqueEmpCode($empcode);
			
			if($coderesult>0)
			{
				echo json_encode(array('success'=>false,'message'=>EMP_CODE_EXISTS_MSG));
				return;
			}
			
			$loginId = $this->ion_auth->get_user_id();
			$iscreatedlog=isCreatedLog($loginId);
			
			//$password = randomPassword();
			
			 $location = getLongLat($address.', '.$city.', '.$state);
			 $latitude = isset($location['latitude']) ? $location['latitude'] : '';
			 $longitude = isset($location['longitude']) ? $location['longitude'] : '';
			
			 if(empty($latitude) || empty($longitude) ){
					 $prelocation = getLongLat($city.', '.$state);
					$latitude  =  isset($prelocation['latitude']) ? $prelocation['latitude'] : '';
					$longitude =  isset($prelocation['longitude']) ? $prelocation['longitude'] : '';	
			 }
			
			$insertdata = array('active' => 1);
			
				$group =array('2');
			
			$userid=$this->ion_auth->register(NULL, $password, $email, $insertdata, $group);
			
			//Address Array merge
			if(isset($postData['dbinput']['addressarray']) && is_array($postData['dbinput']['addressarray'])){
			   $latlongarray=array('latitude' => $latitude,'longitude'=>$longitude);
			   
				$addressarray= array_merge($iscreatedlog,$latlongarray);
				$postData['dbinput']['addressarray']= array_merge($postData['dbinput']['addressarray'],$addressarray);
			}
			$useraddressId=$this->Address_model->addressAdd($postData['dbinput']['addressarray']);
			
			
			
			//User Details Array merge
			if(isset($postData['dbinput']['usersdetailsarray']) && is_array($postData['dbinput']['usersdetailsarray'])){
				$userdetailsarray=array('user_id' => $userid,'address_id'=>$useraddressId);
				$postData['dbinput']['usersdetailsarray']= array_merge($postData['dbinput']['usersdetailsarray'],$iscreatedlog,$userdetailsarray);
			}
			$userdetailsId=$this->Userdetails_model->addUserDetails($postData['dbinput']['usersdetailsarray']);
			
			
						
				$userprofile=array();
				if(isset($userProfiles) && is_array($userProfiles))
				{
					
					foreach($userProfiles as $key=>$udata)
					{
			
						$profile_id  = $udata;
						$userProfileArray=array('user_id'=>$userid,'profile_id'=>$profile_id);
						$userProfileId=$this->UserProfiles_model->addUserProfileDetails($userProfileArray);
					}
				}
				
					
			$subject='Account Details';
			$hiuser = ucwords($data->first_name.' '.$data->last_name);
			$body=Customdata_model::where('content_type','=','Account Details')->first()->content;
			$body=str_replace("{Name}",$hiuser,$body);
			$body=str_replace("{First Name}",$data->first_name,$body);
			$body=str_replace("{Last Name}",$data->last_name,$body);
			$body=str_replace("{Email}",$email,$body);
			$body=str_replace("{Password}",$password,$body);
			
			$body.="<br> <p style='color:#cccccc;text-align:justify;text-justify:inter-word;font-size:13px;'>NOTE: The information in this message and any attached files contain information intended for the exclusive use of the individual or entity to whom it is addressed and may contain information that is proprietary, privileged, confidential and/or exempt from disclosure under applicable law. If the reader of this message is not the intended recipient, or an employee or agent responsible for delivering this message to the intended recipient, you are hereby notified that any dissemination, distribution or copying of this communication is strictly prohibited and subject to legal sanction. If you have received this communication in error, please notify us immediately by replying to the message and deleting it from your computer without making any copies.
			</p> ";
			
			//sendEmail("noreply@synlogics.com","Administrator",$email, $subject, $body);
			
			if($userid )
			{
				echo json_encode(array('success'=>true,'message'=>SAVE_MSG));
				return;
			}
			else{
				echo json_encode(array('success'=>false,'message'=>OOPS_ADMINISTRATOR));
				return;
			}
		}catch(Exception $ex){
			
			echo json_encode(array('success'=>false,'message'=>OOPS_ADMINISTRATOR));
			
		} 
	}
	
	public function updateUser(){
		try{
			log_custom_message("Update User Method Called");
			
			$postdata = file_get_contents("php://input");
			$data = json_decode($postdata);
			
			$_POST =  json_decode(file_get_contents("php://input"), true);
			
			$user_id=isset($data->id) ? $data->id : '';
			
			$userdetailsdata = array();
			$postData=array();
			$postData = inputFieldValidation($_POST, "first_name","First Name", $userdetailsdata, "first_name", [ValidationTypes::REQUIRED,ValidationTypes::INPUT_NAME], $postData,"usersdetailsarray");
			$postData = inputFieldValidation($_POST, "last_name","Last Name", $userdetailsdata,"last_name",[ValidationTypes::REQUIRED,ValidationTypes::INPUT_NAME], $postData,"usersdetailsarray");
			$postData= inputFieldValidation($_POST, "mobileno","Phone Number", $userdetailsdata,"mobileno",[ValidationTypes::REQUIRED], $postData,"usersdetailsarray");
			$postData= inputFieldValidation($_POST, "emp_code","Employee Code", $userdetailsdata,"emp_code",[ValidationTypes::REQUIRED], $postData,"usersdetailsarray");
			
			$userdata = array();
			$postData= inputFieldValidation($_POST, "id","User ID", $userdata,"id",[ValidationTypes::REQUIRED], $postData,"usersarray");
			$postData= inputFieldValidation($_POST, "email","Email", $userdata, "email", [ValidationTypes::REQUIRED,ValidationTypes::EMAIL], $postData,"usersarray");
			
			
			$userProfiles = isset($data->profile_id) ? $data->profile_id : '';
			
				$userprofile=array();
				if(isset($userProfiles) && !empty($userProfiles))
				{
						
					foreach($userProfiles as $key=>$udata)
					{
			
						$profile_id  = $udata;
						$postData = dataFieldValidation($profile_id, "User Profile", $userprofile, "profile_id", [ValidationTypes::REQUIRED], $postData, "userprofilearray".$key);
					}
				}
				
			
			$addressdata = [];
			
			$address=$data->address->address;
			$city = $data->address->city;
			$street = $data->address->street;
			$state = $data->address->state;
		
			$postData = dataFieldValidation($address,"Address", $addressdata, "address", "", $postData,"addressarray");
			$postData = dataFieldValidation($street,"Street",$addressdata,"street","", $postData,"addressarray");
			$postData= dataFieldValidation($city,"City", $addressdata,"city","", $postData,"addressarray");
			$postData= dataFieldValidation($state, "State",$addressdata,"state","", $postData,"addressarray");
			$postData= dataFieldValidation($data->address->pincode, "Zip",$addressdata,"pincode","", $postData,"addressarray");
			$postData= dataFieldValidation($data->address->country, "Country",$addressdata,"country","", $postData,"addressarray");
			if(isset($postData['errorslist']) && is_array($postData['errorslist'])){
				if(count($postData['errorslist'])>0){
					echo json_encode(array('success'=>false,'message'=>$postData['errorslist']));
					return;
				}
			}
			//print_r($postData);die();
			
			$email = isset($data->email) ? $data->email : '';
			$result=uniqueMail($email,$user_id);
			
			if($result>0)
			{
				echo json_encode(array('success'=>false,'message'=>EMAIL_EXISTS_MSG));
				return;
			}
			
			
			$empcode = isset($data->emp_code) ? $data->emp_code : '';
			
			$coderesult=uniqueEmpCode($empcode,$user_id);
			
			if($coderesult>0)
			{
				echo json_encode(array('success'=>false,'message'=>EMP_CODE_EXISTS_MSG));
				return;
			}
			
			$loginId = $this->ion_auth->get_user_id();
			
			
			
			$location = getLongLat($address.', '.$city.', '.$state);
			$latitude = isset($location['latitude']) ? $location['latitude'] : '';
			$longitude = isset($location['longitude']) ? $location['longitude'] : '';
			
			if(empty($latitude) || empty($longitude) ){
					$prelocation = getLongLat($city.', '.$state);
					$latitude = isset($prelocation['latitude']) ? $prelocation['latitude'] : '';
					$longitude = isset($prelocation['longitude']) ? $prelocation['longitude'] : '';	
			}
			
			
			$addressId=isset($data->address_id)?$data->address_id:'';
			if($addressId<=0){
				echo json_encode(array('success'=>false,'message'=>ADDRESS_ISSUE));
				return;
			}
			//echo $addressId; $die();
			
			//Users Array
			
			$this->ion_auth->update($user_id, $postData['dbinput']['usersarray']);
			$isupdatelog=isUpdateLog($loginId);
			//Address Array merge
			if(isset($postData['dbinput']['addressarray']) && is_array($postData['dbinput']['addressarray'])){
			   $latlongarray=array('latitude' => $latitude,'longitude'=>$longitude);
			  
				$addressarray= array_merge($isupdatelog,$latlongarray);
				$postData['dbinput']['addressarray']= array_merge($postData['dbinput']['addressarray'],$addressarray);
			}
			$useraddressId=$this->Address_model->addressUpdate($postData['dbinput']['addressarray'],$addressId);
			
			
			
			
			//User Details Array merge
			if(isset($postData['dbinput']['usersdetailsarray']) && is_array($postData['dbinput']['usersdetailsarray'])){
				$userdetailsarray=array('user_id' => $user_id,'address_id'=>$addressId);
				
				$userdetails= array_merge($isupdatelog,$userdetailsarray);
				$postData['dbinput']['usersdetailsarray']= array_merge($postData['dbinput']['usersdetailsarray'],$userdetails);
			}
			
			$userdetailsId=$this->Userdetails_model->updateUserDetails($postData['dbinput']['usersdetailsarray'],$user_id);
			
			//User Profile and BusinessGroup array
				
				$userprofile=array();
				if(isset($userProfiles) && !empty($userProfiles))
				{   
					$deleteUserprofileId=$this->UserProfiles_model->deleteUserProfiledata($user_id);	
					foreach($userProfiles as $key=>$udata)
					{
			
						$profile_id  = $udata;
						$userProfileArray=array('user_id'=>$user_id,'profile_id'=>$profile_id);
						$userProfileId=$this->UserProfiles_model->addUserProfileDetails($userProfileArray);
					}
				}
				
			
			if($user_id )
			{
				echo json_encode(array('success'=>true,'message'=>UPDATE_MSG));
				return;
			}
			else{
				echo json_encode(array('success'=>false,'message'=>OOPS_ADMINISTRATOR));
				return;
			}
		 }catch(Exception $ex){
			//var_dump($ex);
			echo json_encode(array('success'=>false,'message'=>OOPS_ADMINISTRATOR));
			
		} 
	}
	
	public function getUserById($user_id){
		
		log_custom_message("Get UserById Method Called");
		
		$data = $this->Userdetails_model->getUserAccountByDataId($user_id,$this->auth_account_id);
		$data['address'] = isset($this->Address_model->getAddressbyid($data['address_id'])[0]) ? $this->Address_model->getAddressbyid($data['address_id'])[0] : "";
		$data['profile_id']=$this->UserProfiles_model->userProfileData($data['id']);
		
		echo json_encode(array('data'=>$data,'success'=>true));
	}
	
}
?>
