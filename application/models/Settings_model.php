<?php
include_once(APPPATH . 'models/CommonBase_model.php');

use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class Settings_model extends CommonBase_model {
	public $timestamps = false;
	protected $table = 'settings';
	protected $primaryKey = 'id';
	protected $fillable = ['settings_key','settings_value'];
	
	public function getsettings() {
		echo "phavan";
		return $this::all();
		
	}
}
?>