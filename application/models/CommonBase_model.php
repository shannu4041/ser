<?php
use \Illuminate\Database\Eloquent\Model as Eloquent;
use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Query\Expression as raw;

class CommonBase_model extends Eloquent {
	
	public function __construct($arg=array()){
		parent::__construct($arg);
	}
}