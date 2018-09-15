<?php
include_once(APPPATH . 'models/CommonBase_model.php');

use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class Uploads_model extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'uploads';
	protected $primaryKey = 'id';
	protected $fillable = ['user_id','created_by','FileName','Date'];
	
	public function uploadFile($uploadarray){
		try{
			$uploadID = self::create($uploadarray)->id;
			return $uploadID;
		}
		catch(Exception $ex){
			throw new Exception($ex);	
		}
	}
	
}
?>