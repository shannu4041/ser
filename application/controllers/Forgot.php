<?php defined('BASEPATH') OR exit('No direct script access allowed');

use Illuminate\Database\Query\Expression as raw;

class Forgot extends MY_Controller {
	public function __construct()
	{
	     parent::__construct();
	     
	    $this->load->library(array('session','form_validation','Excel_reader','email','ValidationTypes','ion_auth'));
		$this->load->helper(array('form','html','Util'));
		$this->load->database();
		$this->load->model('User');
		$this->load->model('Customdata_model');
		
		log_custom_message("Forgot Controller Constructor Called");  
	}
    public function forgotpassword()
	{
		
		$postdata = file_get_contents("php://input");
		$data = json_decode($postdata);
		
		$_POST =  json_decode(file_get_contents("php://input"), true);
		$email=$data->email;
		
		$dbData = [];
		$errors=array();
		$errors = inputFieldValidation($_POST, "email", $dbData, "email", [ValidationTypes::REQUIRED,ValidationTypes::EMAIL], $errors);
		
		$forgotten = $this->ion_auth->forgotten_password($email);
		if(!empty($errors)){
			echo json_encode(array('success'=>false,'message'=>$errors));
			return;
		}
		
		
		$userdata = User::findByEmail($email);
		
		if ($userdata == null || count($userdata) <= 0){
			echo json_encode(array('success'=>false,'message'=>INVALID_EMAIL_MSG));
			return;
		}
		
		$subject='Password reset';
		$url = getHostURL(true).'Forgot/passwordReset?token='.$forgotten['forgotten_password_code'];
		$name=$userdata[0]->first_name.' '.$userdata[0]->last_name;
        $hiuser = ucfirst($name);
		$body=Customdata_model::where('content_type','=','Password Reset')->first()->content;
		$body=str_replace("{Name}",$hiuser,$body);
		$body=str_replace("{First Name}",$userdata[0]->first_name,$body);
		$body=str_replace("{Last Name}",$userdata[0]->last_name,$body);
		$body=str_replace("{Email}",$email,$body);
		$body=str_replace("{URL}",$url,$body);
		$body=str_replace("{Password}",'',$body);
		$body.="<br> <p style='color:#cccccc;text-align:justify;text-justify:inter-word;font-size:13px;'>NOTE: The information in this message and any attached files contain information intended for the exclusive use of the individual or entity to whom it is addressed and may contain information that is proprietary, privileged, confidential and/or exempt from disclosure under applicable law. If the reader of this message is not the intended recipient, or an employee or agent responsible for delivering this message to the intended recipient, you are hereby notified that any dissemination, distribution or copying of this communication is strictly prohibited and subject to legal sanction. If you have received this communication in error, please notify us immediately by replying to the message and deleting it from your computer without making any copies.
		</p> ";
		
              if(sendEmail("noreply@synlogics.com","Administrator", $email, $subject, $body) && $forgotten){
				echo json_encode(array('success'=>true,'message'=>CONFIRMATION_MAIL_MSG));
                return;
            }
        else{
			echo json_encode(array('success'=>false,'message'=>REGISTER_MAIL_VAL_MSG));
			return;
        }
		
	}
	function resetPassword(){
		$postdata = file_get_contents("php://input");
		$data = json_decode($postdata);
		$_POST =  json_decode(file_get_contents("php://input"), true);
		$code=$data->token;
		$newpassword=$data->newpassword;
		$confirmpassword=$data->confirmpassword;
		$this->form_validation->set_rules('newpassword', 'New Password', 'required');
		$this->form_validation->set_rules('confirmpassword', 'Confirm New Password', 'required');
		if ($this->form_validation->run() == false) {
			
			echo json_encode(array('success'=>false,'message'=>$this->form_validation->error_array_list_for_toastr()));
			return;
		}
		
		$reset = $this->ion_auth->forgotten_password_complete($code);

		if ($reset) {  //if the reset worked then send them to the login page
			$this->ion_auth_model->change_password($reset['identity'], $reset['new_password'], $newpassword);
			echo json_encode(array('success'=>true,'message'=>PWD_CHANGE_MSG, 'url'=>getHostURL().'login'));
			return;
			    	
		}
		else { //if the reset didnt work then send them back to the forgot password page
			echo json_encode(array('success'=>false,'message'=>INVALID_TOKEN_MSG));
			return;
		}
		
	}
}
?>