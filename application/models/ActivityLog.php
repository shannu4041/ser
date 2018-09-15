<?php
include_once(APPPATH . 'models/CommonBase_model.php');

use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class ActivityLog extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'activity_logs';
	protected $primaryKey = 'id';
	protected $fillable = ['user_id','activity_desc','request_url','request_referer','request_type','data','request_ip','request_datetime','created_at','created_by','created_ip'];
	
}
?>
