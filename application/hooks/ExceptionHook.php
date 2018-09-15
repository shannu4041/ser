<?php
require_once('custom_log.php');

class ExceptionHook
{
	public function SetExceptionHandler()
	{
		set_exception_handler(array($this, 'HandleExceptions'));
	}
	
	public function HandleExceptions($exception)
	{
		$CI = & get_instance();
		$CI->load->helper('Util');
		echo $exception->getMessage();
		 $msg ='Exception of type \''.get_class($exception).'\' occurred with Message: '.$exception->getMessage().' in File '.$exception->getFile().' at Line '.$exception->getLine();
		$msg .="\r\nStacktrace:\r\n";
		$msg .=$exception->getTraceAsString();
		//var_dump('Yes');die();
		log_custom_message($msg,TRUE);
		
		$clog = new Custom_log();
		$clog->logMessages();
		
		header($CI->cprotocol . ' 352 ' . OOPS_ADMINISTRATOR); 
	}
}
?>