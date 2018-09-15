<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_controller']= array(
                   'class'    => 'ExceptionHook',
                   'function' => 'SetExceptionHandler',
                   'filename' => 'ExceptionHook.php',
                   'filepath' => 'hooks'
                  );

$hook['post_controller'] = array(     // 'post_controller' indicated execution of hooks after controller is finished
    'class' => 'Custom_log',             // Name of Class
    'function' => 'logMessages',     // Name of function to be executed in from Class
    'filename' => 'custom_log.php',    // Name of the Hook file
    'filepath' => 'hooks'         // Name of folder where Hook file is stored
);	
