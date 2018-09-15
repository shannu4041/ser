<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Event as Evt;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => '172.16.0.23',
	'username' => 'icici_rpa',
	'password' => 'Hyper@4321q',
	'database' => 'ICICI',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

$db['customerdb'] = array(
	'dsn'	=> '',
	'hostname' => '172.16.0.23',
	'username' => 'syntm_user',
	'password' => 'syntm_pass',
	'database' => 'syn_tm_common',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

global $capsule;
$capsule = new Capsule;

$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => $db['default']['hostname'],
    'database'  => $db['default']['database'],
    'username'  => $db['default']['username'],
    'password'  => $db['default']['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => $db['default']['dbprefix'],
));

$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => $db['customerdb']['hostname'],
    'database'  => $db['customerdb']['database'],
    'username'  => $db['customerdb']['username'],
    'password'  => $db['customerdb']['password'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => $db['customerdb']['dbprefix'],
), "customerdbconnection");


$capsule->setAsGlobal();
$capsule->bootEloquent();

$events = new Illuminate\Events\Dispatcher;

$events->listen('Illuminate\Database\Events\QueryExecuted', function ($querynew) {
    // var_dump($query->sql);
    // var_dump($query->bindings);
    // var_dump($query->time);
	
	$bindings = $querynew->bindings;
	$query = $querynew->sql;
	
	foreach ($bindings as $i => $binding)
    {   
        if ($binding instanceof \DateTime)
        {   
            $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
        }
        else if (is_string($binding))
        {   
            $bindings[$i] = "'$binding'";
        }
    }

    // Insert bindings into query
    $query = str_replace(array('%', '?'), array('%%', '%s'), $query);
    $query = vsprintf($query, $bindings);
	
	$CI =& get_instance();
	
	//echo $query;

    $CI->custom_log_messages[] = date('Y-m-d H:i:s') . "\n" . $query . " \nExecution Time: " . $querynew->time;
});

$capsule->setEventDispatcher($events);