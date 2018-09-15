<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$config['base_url'] = 'http://datacollectionsservice.hyperthread.in';
$config['maintanance'] = FALSE;
$config['maintananceallowips'] = '183.82.112.15';

$config['log_threshold'] = 2;

$config['smtpsettings'] = Array(
	'protocol' => 'smtp',
	'smtp_host' => 'smtp.gmail.com',
	'smtp_port' => 465,
	'smtp_user' => 'noreply@synlogics.com',
	'smtp_pass' => 'Hyper@4321q',
	'mailtype'  => 'html', 
	'charset'   => 'UTF-8',
	'wordwrap' => TRUE
);

$config['environment'] = 'production';
$config['applepush'] = 'production';