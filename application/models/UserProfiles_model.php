<?php
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class UserProfiles_model extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'user_profiles';
	protected $primaryKey = 'id';
	protected $fillable = ['user_id','profile_id'];
	
	
	public function addUserProfileDetails($data){
		$userprofileId = self::create($data)->id;
		return $userprofileId;
	}
	public function userProfileData($user_id){
		if(!empty($user_id) && $user_id>0){
			$userprofile=array();
			$userprofiledata=self::where('user_id',$user_id)->get(['profile_id']);
			foreach($userprofiledata as $val){
				$userprofile[]=$val->profile_id;
			}
			return $userprofile;
		}else{
			return false;
		}
	}
	function deleteUserProfiledata($user_id)
	{
		try{
		
		 return self::where('user_id','=',$user_id)->delete();
		
		}
		catch(Exception $ex){
			throw new Exception($ex); //return false;
		}
		
	}
}
?>