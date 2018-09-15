<?php
include_once(APPPATH . 'models/CommonBase_model.php');

use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class Actions_model extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'actions';
	protected $primaryKey = 'id';
	protected $fillable = ['action_name','description','menu_id'];
	
	public function Menus()
	{
	  return $this->belongsTo('Menus_model');
	}
	public function Permissions(){
		return $this->hasMany('Profileactions_model','action_id','id')->join('profiles','profiles.id','=','profile_actions.profile_id')
		->select('profile_actions.*','profiles.id as profiles_id','profiles.name')
		->orderBy('profiles.name');
	}
	public function actionList(){
		$actionListdata=self::get(['id']);
		return $actionListdata;
	}
	public function menuIdData($menu_id){
		$getMenuIdData=self::where('menu_id','=',$menu_id)->get(['id']);
		return $getMenuIdData;
	}
	public function createActionData($data){
		$actionId = self::create($data)->id;
		return $actionId;
	}
	public function updateActionData($data,$oldmenusactionname){
		
		  $updateactionname=self::where('action_name','=',$oldmenusactionname)->Update($data);
		  return $updateactionname;
	}
	public function updateAction($id,$data){
		$actionId = self::where('id','=',$id)->Update($data);
		return $actionId;
	}
	public function deleteActionsData($actionid){
		$actiondata=self::where('id', $actionid)->delete();
		
		if($actiondata)
			{ return true; }
	    else
			{ return false; }
	}
}
?>