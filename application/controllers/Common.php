<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH . 'controllers/CommonBaseController.php');
ob_start();
use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule; 
class Common extends CommonBaseController {
	
	public function __construct()
	{
		parent::__construct();
		 $this->load->library(array('form_validation'));
		 $this->load->helper(array('url','html','form','Util','language'));
		 $this->load->database();
		 $this->load->model('User');
		 $this->load->model('UserGroups_model');
		 $this->load->model('Countries_model');
		
		 
	}	
	
	public function logout() {
		session_start();
		$this->ion_auth->logout();
		
		if ($this->redirectType == "Normal"){
			echo json_encode(array('success'=>true));
		} else {
			header($this->cprotocol . ' 350 ' . ' /');
		}
		return;
	}
	
	public function getUsers(){
		
		$userdata=User::join('user_details','user_details.user_id','=','users.id')->select(new Raw ("concat (user_details.first_name,' ',user_details.last_name) as name"),"users.id")->get();
		echo json_encode(array('data'=>$userdata,'success'=>true));
		
	}
	
	public function fileuploadtotemp() {
		
		if(isset($_FILES) && isset($_FILES['file'])){  
			$errors= array();
			$names = array();
			$file_name = $_FILES['file']['name'];
			$file_size =$_FILES['file']['size'];
			$file_tmp =$_FILES['file']['tmp_name'];
			$file_type=$_FILES['file']['type'];   
			$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			$extensions = array("pem","jpeg","jpg","png","xls","csv","xlsx","mp4","wmv","pdf","doc");  
			$dir =FCPATH.'/temp';
			if(!is_dir($dir)){
				mkdir($dir);					
			}

			if(in_array($file_ext,$extensions )=== false){
				 $error=$file_name . ' - extension not allowed, please choose a JPEG or PNG file.';
			}
			if($file_size > 1004857600){
				$error=$file_name . ' - File size cannot exceed 100 MB';
			}
			if(empty($error)==true){
				try{
					$filename =  "/temp/".rand(0,100000).'_'.removeSplChar($file_name);
					move_uploaded_file($file_tmp,FCPATH.$filename);
					$names[] = $filename;
				}
				catch(Exception $e){
					$errors[] = "Unknown error occurred.";
					
				}
			} else{
				$errors[] = $error;
				
			}
			
			if (count($errors) > 0){
				
				echo json_encode(array('success'=>false,'data'=>$errors));
			} else{
				echo json_encode(array('success'=>true,'data'=>$names));
			}
		}
		else{
			$errors= array();
			$errors[]="No file uploaded";
			echo json_encode(array('success'=>false,'data'=>$errors));
		
		}
	}
	
	public function changepassword(){
  
		$postdata = file_get_contents("php://input");
		$data = json_decode($postdata);
	
		$oldpassword = isset($data->oldpassword) ? $data->oldpassword : '';
		$newpassword = isset($data->newpassword) ? $data->newpassword : '';
		$confirmpassword = isset($data->confirmpassword) ? $data->confirmpassword : '';
		
		$userData = array();
		$upostData =array();
		$upostData = dataFieldValidation($oldpassword, "Old Password", $userData, "oldpassword", [ValidationTypes::REQUIRED], $upostData, "users");
		$upostData = dataFieldValidation($newpassword, "New Password", $userData, "newpassword", [ValidationTypes::REQUIRED, ValidationTypes::PASSWORD], $upostData, "users");
		$upostData = dataFieldValidation($confirmpassword, "Confirm Password", $userData, "confirmpassword", [ValidationTypes::REQUIRED, ValidationTypes::PASSWORD], $upostData, "users");
		
		if($newpassword != $confirmpassword )
        {
			echo json_encode(array('success'=>false,'message'=>PASSWORD_NOT_MATCH));
			return;
		}			
		
		if(isset($upostData['errorslist']) && is_array($upostData['errorslist'])){
			if(count($upostData['errorslist'])>0){
			 echo json_encode(array('success'=>false,'message'=>$upostData['errorslist']));
			 return;
			}
		}
        
		$identity=$this->session->userdata('email');
                   
		if(($this->ion_auth_model->change_password($identity, $oldpassword, $newpassword)))
        {
           echo json_encode(array('success'=>true,'message'=>PWD_CHANGE_MSG));
		   return;  
        }
       
        
		echo json_encode(array('success'=>false,'message'=>INVALID_PWD_MSG));
	}
	
	public function base64ToImage(){
		$postdata = file_get_contents("php://input");
		$data = json_decode($postdata);
		$_POST =  json_decode(file_get_contents("php://input"), true);
		$imgBytes = isset($data->img) ? $data->img : '';
		$data = base64_decode($imgBytes);
		$imgName = uniqueCode().'.png';
		$file = 'temp/' . $imgName;    
		$success = file_put_contents($file, $data);
		if($success){
			echo json_encode(array('success'=>true,'data'=>'/'.$file,'message'=>UPLOAD_MSG));
		}else{
			echo json_encode(array('success'=>true,'message'=>UPLOAD_FAILED_MSG));
		}
	}
	
	public function getCountries()
	{ 
		try{
			$result= $this->Countries_model->getAllCountry();
			echo json_encode(array('data'=>$result,'success'=>true));
		}catch(Exception $ex){
			header($this->cprotocol . ' 418 '.'/'.base_url());
		}
	}
	
	public function getUserProfileAction()
	{   
	    log_custom_message("Common Controller - getUserProfileAction Method Called");
		$loginId    = $this->ion_auth->get_user_id();
		
			
			
			if (in_array('Admin',$this->session->userdata('user_roles'))){
				$proActionsData = Capsule::Select("SELECT a.action_class,1 as access FROM menus m JOIN actions as a ON a.menu_id=m.id");
			} else {
				$proActionsData = Capsule::Select("select a.action_class,b.access from (SELECT a.id as action_id,a.action_class FROM menus m JOIN actions as a ON a.menu_id=m.id) a join (SELECT action_id, case when sum(has_access) >= 1 then 1 else 0 end as access FROM profiles pr JOIN profile_actions AS pa ON pr.id = pa.profile_id where pr.id IN (SELECT profile_id FROM `user_profiles` WHERE user_id = ".$loginId.") group by action_id) b on a.action_id = b.action_id");
			}
			
			$proActions = array();
			foreach($proActionsData as $aval)
			{
				$proActions[$aval->action_class] = $aval->access;
			}
				
			echo json_encode(array('success'=>true,'data'=>$proActions));
		
	}
	
}
?>
