<?php
include_once(APPPATH . 'models/CommonBase_model.php');

use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class User extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'users';
	protected $primaryKey = 'id';
	protected $fillable = ['ip_address','username','password','salt','email','activation_code','forgotten_password_code','forgotten_password_time','remember_code','created_on','last_login','active','first_name','last_name','company','phone'];
	
	public function userdetails() {
		return $this->hasOne('Userdetails_model','user_id');
	}
	
	public function address() {
		return $this->hasOne('Address_model','id','address_id')->select('id','address','street','city','state','country','pincode');
	}
	
	public static function userAccountsRole($id)
	{
		$userroledata = self::join('users_groups','users_groups.user_id','=','users.id')->join('groups','groups.id','=','users_groups.group_id')->where('users.id','=',$id)->select('user_id','group_id','groups.name')->get();
		
		return $userroledata;
	}
	
	public function userdetailsmin() {
		return $this->hasOne('Userdetails_model','user_id')->select(['first_name','last_name','mobileno','profile_pic_path']);
	}
	
	public static function findByEmail($email){
		
		$userdata = self::join('user_details','user_details.user_id','=','users.id')->where('email','=',$email)->get(['users.id','users.username','users.active','user_details.first_name','user_details.last_name']);
		return $userdata; 
	}
	public static function findById($id){
		
		$userdata = self::join('user_details','user_details.user_id','=','users.id')->where('users.id','=',$id)->get(['users.id','users.username','users.active','user_details.first_name','user_details.last_name']);
		return $userdata; 
	}
	
	public function getUserByIdData($user_id){
		try{
			$baseurl=getBaseURL(true);
			$userdata=self::join('user_details','user_details.user_id','=','users.id')
						->where('users.id',$user_id)
						->get(['users.id','users.email','users.active as status','user_details.first_name','user_details.last_name','user_details.mobileno',new raw('concat("'.$baseurl.'","",user_details.profile_pic_path) as profile_pic_path'),'user_details.address_id','user_details.dob']);
			//$userdata=self::with('address')->find($user_id);
			
			return 	$userdata[0];		
		}
		catch(Exception $ex){
			throw new Exception($ex); //return false;
		}
	}
	public function updateUserData($data,$user_id){
		try{
			
		 return self::where('id','=',$user_id)->Update($data); 
		
		}
		catch(Exception $ex){
			throw new Exception($ex); //return false;
		}
	}
	
	
	public function getAllUsers($paging){
		
			if (!isset($paging)){
				$paging = new StdClass;
			}
				
			if(!isset($paging->sortBy))
					$paging->sortBy = 'a.id';
				
			if(!isset($paging->sortDirection))
				$paging->sortDirection = 'desc'; 
				
			$limit=" ";
			if(!isset($paging->pageSize)){
					$paging->pageSize = 5;
			}
			else if(($paging->pageSize)!=-1) { 
				$limit=" limit ".$paging->page.",".$paging->pageSize;
				//echo $limit;
			}
					
			if(!isset($paging->page)){
				$paging->page = 1;
			}
			if(isset($paging->export_type)){
				$limit="";
			}
			$serQry='';$serQryDB='';$serQryProfile='';
			if(isset($paging->search))
			{
				$searchFilter = $paging->search;
				
				$username = isset($searchFilter->name)?$searchFilter->name:'';
				$profile_id = isset($searchFilter->profile_id)?$searchFilter->profile_id:'';
				$contact_email = isset($searchFilter->contact_email)?$searchFilter->contact_email:'';
				$emp_code = isset($searchFilter->emp_code)?$searchFilter->emp_code:'';
				
				if($username && !empty($username))
				{
					
					//$serQry = $serQry.' AND (a.first_name like "%'.$username.'%" or a.last_name LIKE "%'.$username.'%") ';
					$serQry = $serQry.' AND (a.name like "%'.$username.'%") ';
					
				}
				if($contact_email && !empty($contact_email))
				{
					
					$serQry = $serQry.' AND  a.email LIKE "%'.$contact_email.'%" ';
					
				}
				
				if($emp_code && !empty($emp_code))
				{
					
					$serQry = $serQry.' AND  a.emp_code LIKE "%'.$emp_code.'%" ';
					
				}
				
				if(is_array($profile_id) && !empty($profile_id))
				{
					$profile_id = implode(',', $profile_id);
					$serQryProfile = $serQryProfile.' AND  p.id in ('.$profile_id.') ';
					
				}
				
			
			}
	    $baseurl=getBaseURL(true);
		$userdata=Capsule::Select("select a.id,a.email,a.name,group_concat(b.profile_id) as profile_id,group_concat(p.name) as profile_name,a.mobileno,a.emp_code from (select u.id, u.email,concat (ud.first_name,' ',ud.last_name) as name,ud.mobileno,ud.emp_code  FROM users u JOIN user_details ud on u.id=ud.user_id JOIN users_groups ug on u.id=ug.user_id where group_id=2 group by u.id) as a LEFT JOIN (select * from user_profiles ) as b on b.user_id=a.id LEFT JOIN profiles p on p.id=b.profile_id where a.id!=0 ".$serQryProfile."  ".$serQry." group by a.id order by  ".$paging->sortBy." ".$paging->sortDirection.$limit);
		
		$userdatacount=Capsule::Select("select count(cnt) as cnt from (select count(*) as cnt from (select u.id, u.email,concat (ud.first_name,' ',ud.last_name) as name,ud.mobileno FROM users u JOIN user_details ud on u.id=ud.user_id  JOIN users_groups ug on u.id=ug.user_id where group_id=2 group by u.id) as a LEFT JOIN (select * from  user_profiles where id!=0 ) as b on b.user_id=a.id LEFT JOIN profiles p on p.id=b.profile_id where a.id!=0 ".$serQryProfile." ".$serQry." group by a.id) as d");
		
		
			return array('data'=>$userdata,'TotalRecords'=>$userdatacount[0]->cnt);
		
	}
	
}
?>
