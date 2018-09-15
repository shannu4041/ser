<?php defined('BASEPATH') OR exit('No direct script access allowed');
include_once(APPPATH . 'controllers/admin/BaseController.php');

use Illuminate\Database\Query\Expression as raw;
use \Illuminate\Database\Capsule\Manager as Capsule;
class FileUploadController extends BaseController {
	public function __construct()
	{
		
		 parent::__construct();
		 
		 $this->load->library(array('session','form_validation','Excel_reader','ValidationTypes','excel'));
	
		 $this->load->helper(array('form','html','Util'));
		 $this->load->database();
		 $this->load->model('User');
		 $this->load->model('Uploads_model');
		 $this->load->model('CGIInputs_model');
		 $this->load->model('Probe_company_model');
		 $this->load->model('Probe_directors_model');
		 $this->load->model('Naukri_data_model');
		 $this->load->model('Google_model');
		 $this->load->model('Probe_model');
		 $this->load->model('Userdetails_model');
		 
		 log_custom_message("FileUpload Controller Constructor Called");
		 
	}
	
	
	public function uploadFile(){
	
		log_custom_message("Upload file Method Called");
			
		$postdata = file_get_contents("php://input");
		$data = json_decode($postdata);
		
		$_POST =  json_decode(file_get_contents("php://input"), true); 
		 
		$userfile=isset($data->filepath) ? $data->filepath:'';
		/* $empcode=isset($data->emp_code) ? $data->emp_code:'';
		
		$getuserid=Userdetails_model::where('emp_code',$empcode)->get(['user_id','emp_code']);
		if(count($getuserid)>0){
			$user_id=isset($getuserid[0]->user_id) ? $getuserid[0]->user_id:'';
		}else{
			echo json_encode(array('success'=>false,'message'=>EMP_CODE_NOT_EXISTS_MSG));
				return;
		} */
		
		
		
		 $loginId = $this->ion_auth->get_user_id();
		 $isupdatelog=isUpdateLog($loginId); 
		
		
		
		if(startsWith($userfile,'/temp/') == true){
			$filename = str_replace('/temp/','',$userfile);
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$allowed = array('xls','xlsx');
			if( ! in_array( $ext, $allowed ) ) {
			
				echo json_encode(array('success' => false, 'message' =>INVALID_FILE_MSG));
				return;
			}
			
			if(@filesize(FCPATH.$userfile)/1024 < 25600)
			{
				
				
				$filename =  "/assets/uploads/".$filename;
				@copy(FCPATH.$userfile,FCPATH.$filename);
				//@unlink(FCPATH.$userfile);
				
			}
			else{
				echo json_encode(array('success' => false, 'message' => FILE_SIZE_MSG));
				return;
			}
		}else{
			$filename=$userfile;
		}
		
		$dataArray = array('created_by'=>$loginId, 'FileName'=>$filename);
		
		$uploadId=$this->Uploads_model->uploadFile($dataArray);
		//echo FCPATH.$filename;die;
		
		 try {
                $inputFileType = PHPExcel_IOFactory::identify(FCPATH.$filename);
                $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load(FCPATH.$filename);
             } catch (Exception $e) {
                die('Error loading file "' . pathinfo($filename, PATHINFO_BASENAME)
                        . '": ' . $e->getMessage());
            } 
			
			 $sheet = $objPHPExcel->getSheet(0);
			$total_rows =$sheet->getHighestRow();
			 $total_columns ='AO'; //$sheet->getHighestColumn();
				//echo "row hight".$total_rows;die;		
			// $query = ("insert into `cgiinput` (`ID`, `uploadid`, `cgid`, `customer_full_name`) VALUES ");
			 
			 $query = ("insert into `cgiinput` (`ID`, `uploadid`, `user_id`,`cgid`, `city`,`state`,`pin`,`customer_full_name`,`dob`, `mother_maiden_name`, `father_name`, `spouse_name`,`employer_name`, `pan_nbr`, `email_id`, `aadhar_nbr`,`drivers_license_no`, `passport_no`, `voter_id_no`, `food_card_or_ration_no`,`customer_profile`, `additional_details`, `email1`, `email2`,`email3`, `email4`, `mobile1`, `mobile2`,`mobile3`, `mobile4`, `mobile5`, `mobile6`,`mobile7`, `mobile8`, `mobile9`, `address1`,`address2`, `address3`, `address4`,`tc_execution`,`google_execution`,`probe_execution`,`naukri_execution`,`status`) VALUES ");
			
			for($row =2; $row <= $total_rows; $row++) {
				
				//Read a single row of data and store it as a array.
				//This line of code selects range of the cells like A1:D1
				$single_row = $sheet->rangeToArray('A' . $row . ':' . $total_columns . $row, NULL, TRUE, FALSE);
				
				//Creating a dynamic query based on the rows from the excel file
				$query .= "(";
				$query .= "' ',";
				$query .= "'".$uploadId."',";
				$k=1;
				foreach($single_row[0] as $key=>$value) {
					//$value=str_replace("'", ' ', $value);
					//$query .= "'".trim(preg_replace("/\s+/", '', $value))."',";
					if($k==1){
						$empcode= trim(str_replace("'", ' ', $value));
						$getuserid=Userdetails_model::where('emp_code',$empcode)->get(['user_id','emp_code']);
						
							if(count($getuserid)>0){
								$user_id=isset($getuserid[0]->user_id) ? $getuserid[0]->user_id:'';
							}else{
								//echo json_encode(array('success'=>false,'message'=>EMP_CODE_NOT_EXISTS_MSG));
								///	return;
								$user_id=0;
							}
							$query .= "'".$user_id."',";
						
					}else if($k==7){
						//echo 'dat='.gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($value));die;
						$query .= "'".trim(gmdate("d/m/Y", PHPExcel_Shared_Date::ExcelToPHP($value)))."',";
					}else{
						$query .= "'".trim(str_replace("'", ' ', $value))."',";
					}
					$k++;
				}
				$openstatus=0;
				$query .= "'".$openstatus."',";
				$query = substr($query, 0, -1);
				$query .= "),";
			   
             }
			  $query = substr($query, 0, -1);
			  //echo "query=".$query;die;
			  $alldata = $this->db->query($query);
		
		if($uploadId)
		{
			
			echo json_encode(array('success'=>true,'message'=>SAVE_MSG));
			//$datta=$this->seperateCgiInputData($uploadId);
			//print_r($datta);
			//return;
		}
		else{
			echo json_encode(array('success'=>false,'message'=>OOPS_ADMINISTRATOR));
			return;
		}  
		

	}
	
	public function getCGIData(){
		try{
			log_custom_message("AccountController - getAccounts Method Called");
			$postdata = file_get_contents("php://input");
			$paging   = json_decode($postdata);
			$CGIdata = $this->CGIInputs_model->getCGIData($paging);
			echo json_encode(array('success'=>true,'data'=>$CGIdata['data'],'totalrecords'=>$CGIdata['totalrecords']));
			
		}catch(Exception $ex){
			//var_dump($ex);
			throw new Exception($ex);
		}
	}
	
	public function getCGIDetails($id){
		$data=CGIInputs_model::find($id);
		//$probedata=Probe_company_model::with('directors')->where('input_id',$id)->get();
		echo json_encode(array('success'=>true,'data'=>$data));
	}
	

	public function probedetails(){
		//try{
		//$data = Capsule::Select("SELECT *  FROM `cgiinput` ci  JOIN `probe_company` pc ON  ci.`ID` = pc.`input_id`  JOIN `probe_directors` pd ON pc.id=pd.`companyid`order by ci.`ID`");
		$companydata=$this->CGIInputs_model->getMaxCompanies();
		/* echo json_encode(array('success'=>true,'message'=>count($companydata)));
		}catch(Exception $ex){
			var_dump($ex);
			//throw new Exception($ex);
		
		} */
	}
	
	public function seperateCgiInputData($uploadId){
		
		$data=CGIInputs_model::where('uploadid',$uploadId)->get();
		//echo sizeof($data);
		foreach($data as  $d){
			
		try{
			$probedata = array('cgid'=>$d->ID,'source'=>'ICICI','source_input'=>'PAN','input'=>$d->pan_nbr,'customer_full_name'=>$d->customer_full_name,'father_name'=>$d->father_name,'mother_maiden_name'=>$d->mother_maiden_name,'spouse_name'=>$d->spouse_name,'city'=>$d->city,'state'=>$d->state,'pin'=>$d->pin,'status'=>$d->probe_execution == 1? '0':'2');
			
			$probeinsertresult=Probe_model::create($probedata);
			
			if($d->customer_full_name!='' || $d->customer_full_name!=null){
				if($d->dob!='' || $d->dob!=null){
					$year=substr($d->dob,6);
				}else{
					$year="";
				}
				 ECHO $d->ID.'<br/>';
				$nresult2 = $this->db->query("CALL sp_seperate_naukri_inputs2('".$d->ID."','ICICI', 'Customer Name,Dob', '".$d->customer_full_name."', '".$year."','0')") ;
				$this->db->close(); 
			}
			
			  if($d->email1!='' || $d->email1!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."', 'ICICI', 'Email1', '".$d->email1."','google_search_data')") ;
					 $this->db->close();
				  $nresult = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Email1', '".$d->email1."','naukri_search_data')") ;
			 $this->db->close(); 
			 }
			 if($d->email2!='' || $d->email2!=null){				 
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."', 'ICICI', 'Email2', '".$d->email2."','google_search_data')") ;
				  $this->db->close();
				  $nresult = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Email2', '".$d->email2."','naukri_search_data')") ;	
				  $this->db->close();	
			 }
			 if($d->email3!='' || $d->email3!=null){				 
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."', 'ICICI', 'Email3', '".$d->email3."','google_search_data')") ;	
				  $this->db->close();
				  $nresult = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Email3', '".$d->email3."','naukri_search_data')") ;
				  $this->db->close();	
				 
				 
			 }
			 if($d->email4!='' || $d->email4!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."', 'ICICI', 'Email4', '".$d->email4."','google_search_data')") ;	
				  $this->db->close();
				  $nresult = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Email4', '".$d->email4."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile1!='' || $d->mobile1!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile1', '".$d->mobile1."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile1', '".$d->mobile1."','truecaller_search_data')") ;
 $this->db->close();				 
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile1', '".$d->mobile1."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile2!='' || $d->mobile2!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile2', '".$d->mobile2."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile2', '".$d->mobile2."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile2', '".$d->mobile2."','naukri_search_data')") ;	
				  $this->db->close();
				 
			 }
			 if($d->mobile3!='' || $d->mobile3!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile3', '".$d->mobile3."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile3', '".$d->mobile3."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile3', '".$d->mobile3."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile4!='' || $d->mobile4!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile4', '".$d->mobile4."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile4', '".$d->mobile4."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile4', '".$d->mobile4."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile5!='' || $d->mobile5!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile5', '".$d->mobile5."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile5', '".$d->mobile5."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile5', '".$d->mobile5."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile6!='' || $d->mobile6!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile6', '".$d->mobile6."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile6', '".$d->mobile6."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile6', '".$d->mobile6."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile7!='' || $d->mobile7!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile7', '".$d->mobile7."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile7', '".$d->mobile7."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile7', '".$d->mobile7."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile8!='' || $d->mobile8!=null){
				  $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile8', '".$d->mobile8."','google_search_data')") ;	
				  $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile8', '".$d->mobile8."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile8', '".$d->mobile8."','naukri_search_data')") ;	
				  $this->db->close();
			 }
			 if($d->mobile9!='' || $d->mobile9!=null){
				 $result = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."',  'ICICI','Mobile9', '".$d->mobile9."','google_search_data')") ;	
				 $this->db->close();
				  $r = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile9', '".$d->mobile9."','truecaller_search_data')") ;	
				  $this->db->close();
				  $nr = $this->db->query("CALL sp_seperate_cgiinput_inputs('".$d->ID."','ICICI', 'Mobile9', '".$d->mobile9."','naukri_search_data')") ;	
				  $this->db->close();
			 } 
			 
			// echo 'pavan';
			$updatecgi=CGIInputs_model::where('ID',$d->ID)->Update(array('is_seperated'=>1));
			}catch(Exception $ex){
				$updatecgi=CGIInputs_model::where('uploadid',$uploadId)->Update(array('is_seperated'=>0));
			var_dump($ex);
			//throw new Exception($ex);
				break;
		
			} 

		}
		
		
	}
	public function getDistnictInputs(){
		$truecallerdata = Capsule::Select("select DISTINCT input,cgid from truecaller_search_data");
		$naukridata = Capsule::Select("select DISTINCT input,cgid from naukri_search_data");
		$googledata = Capsule::Select("select DISTINCT input,cgid from google_search_data");
		echo json_encode(array('success'=>true,'truecallerdata'=>$truecallerdata,'naukridata'=>$naukridata,'googledata'=>$googledata));
	}

}
?>
