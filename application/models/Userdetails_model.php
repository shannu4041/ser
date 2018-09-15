<?php
include_once(APPPATH . 'models/CommonBase_model.php');

use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class Userdetails_model extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'user_details';
	protected $primaryKey = 'id';
	protected $fillable = ['user_id','first_name','last_name','emp_code','mobileno','address_id','created_ip','created_by','created_on','modified_ip','modified_by','modified_on'];
	
	public function user()
	{
		return $this->belongsTo('User');
	}
	public function addUserDetails($userdetailsarray){
		try{
			$userDetailsId = self::create($userdetailsarray)->id;
			return $userDetailsId;
		}
		catch(Exception $ex){
			throw new Exception($ex);	
		}
	}
	function updateUserDetails($data,$user_id)
	{
		try{
			
		 return self::where('user_id','=',$user_id)->Update($data); 
		
		}
		catch(Exception $ex){
			throw new Exception($ex); //return false;
		}
		
	}
	public function addressInfo() {
		return $this->hasOne('Address_model','id','address_id')->select('id','address','street','city','state','country','pincode');
	}
	public function getUserAccountByDataId($user_id){
		
		$useraccountdata=self::join('users','users.id','=','user_details.user_id')->where('user_details.user_id',$user_id)->get(['users.id','users.email','user_details.first_name','user_details.last_name','user_details.mobileno','user_details.address_id','user_details.emp_code']);
		return $useraccountdata[0];
	}
	
}

?>