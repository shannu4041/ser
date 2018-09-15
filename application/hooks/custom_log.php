<?php

class Custom_log {
 
    function __construct() {
       // Anything except exit() :P
    }
 
    // Name of function same as mentioned in Hooks Config
    function logMessages() {
		
		//echo 'Yes';
		
		$CI = & get_instance();
		
		if (!empty($CI->custom_log_messages)){
			$userName = '';
			
			if (!empty(config_item('auth_email'))){
				$userName = config_item('auth_email') . ' - ';
			}
			
			$filepath = APPPATH . 'logs/'. $userName . date('Y-m-d') . '.php'; // Creating Query Log file with today's date in application/logs folder
			
			$handle = fopen($filepath, "a+");                 // Opening file with pointer at the end of the file
	 
			flock($handle,LOCK_EX);
	 
			$custom_log_messages = $CI->custom_log_messages;                   // Get execution time of all the queries executed by controller
			foreach ($CI->custom_log_messages as $msg) { 
				//echo $query;
				fwrite($handle, $msg . "\n\n");              // Writing it in the log file
			}
			
			flock($handle,LOCK_UN);
			
			fclose($handle);      // Close the file
		}
    }
}