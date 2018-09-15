<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

//Constant for Messages
define("MAINTANANCE_MSG","Service is under maintanance.");

define("OOPS_ADMINISTRATOR","Oops! something went wrong. Please contact Administrator");
define("SAVE_MSG","Saved Successfully");
define("EMAIL_EXISTS_MSG","Email Already Exists");
define("UPDATE_MSG","Updated Successfully");
define("UPDATE_FAILED_MSG","Couldn't Update Succesfully");
define("DELETE_MSG","Deleted Successfully");
define("DELETE_FAIL_MSG","Delete couldn't be performed successfully");	
define("INVALID_MSG","Invalid Details");
define("INVALID_TOKEN_MSG","Invalid Token");
define("INVALID_USER_PWD_MSG","Incorrect Username or Password");
define("LOGIN_INACTIVE_MSG","Your login is inactive. Please contact Administrator");
define("LOGIN_SUCS_MSG","Login Successfully");
define("ACC_INACTIVE_MSG","Your account is inactive. Please contact Administrator ");
define("ACC_AUTH_MSG","User not Authorized. Please Contact Administrator");
define("JOB_ASIGN_MSG","New Job has been assigned to you");
define("WORK_PRO_MSG","Please work properly");
define("EMAIL_ACT_MSG","Email will be send to the Administrator for activation");
define("INVALID_USER_MSG","Please enter Username");
define("INVALID_PWD_MSG","Please enter Password");
define("NO_DATA","No records found");
define("BAND_USER","Banned User");
define("VRFY_USER","Email will be send to the your Email for verification");
define("REGISTER_MAIL_VAL_MSG","This email is not registered or already activated");
define("CONFIRMATION_MAIL_MSG","Confirmation Email has been sent. Please check your mailbox for the token link");
define("PWD_CHANGE_MSG","Password Changed Successfully");
define("ADDRESS_ISSUE","Address Data Missing");
define("RESET_EMAIL","Reset Password has been sent to your Email. Please check your mailbox");
define("RESET_EMAIL_POPUP","Reset Password has been sent to your Email. Password is ");
define("PASSWORD_NOT_MATCH","New password  and confirm password doesn't match");
define("NO_GET_ID","No id in URL. Please pass id in URL");
define("DELETE_ACTION_NO","Delete action not allowed for this record.");
define("SAVE_MSG_AND_PASSWORD", "Saved Successfully. Password is: ");
define("LANG_KEY_EXIST", "Language key already exist");
define("TRACKER_ID_EXIST", "Tracker id already exist");
define("ASSIGNED_SUCCESSFULLY","Successfully Assigned");
define("NOT_ASSIGNED","Not Assigned Successfully");
define("UN_ASSIGNED","UnAssigned Successfully ");
define("ALREADY_UNASSIGNED","Tracker is already un-assigned");
define("TRACKER_ID_EXIST_MASTER", "Tracker id already exist in Super Admin");
define("TRACKER_DOES_NOT_EXIST_MASTER", "Tracker does not exist");
define("DWNLOAD_MSG","Downloaded Successfully ");
define("PROF_MENU_ID_ERR","Profile ID or Menu ID Missing");
define("TRACKER_ID_AVAILBLE","Tracker id Available");
define("VEHICLE_NOT_AVAILBLE","Currently Vehicals are not available");
define("ACCESS_RESULT","Access denied, you are not authorized to perform this action");
define("MSTR_TRAKER_ISSUE","Master Tracker Issue");
define("MSTR_TRAKER_HISTRY_ISSUE","Master Tracker History Data Unavailable");
define("TRAKER_ISSUE","Tracker ID  Unavailable");
define("INVALID_ACCOUNT","Invalid Account ID Provided");
define("EMP_CODE_EXISTS_MSG","Employee Code Already Exists");
define("EMP_CODE_NOT_EXISTS_MSG","Employee Code Not Exists");
//Vehicle page Messages
define("VEHICLE_INVALID","Invalid Vehicle ");
define("TRACKER_ACCOUNT_UNASSIGN_NOT_PERMITTED_WRONG_STATUS","Tracker can not be unassigned from account unless it is in Unassigned Status");


//User Groups Constants
define("SUPER_ADMIN",1);
define("ACCOUNT_ADMIN",2);
define("USERS",3);



//AES ENCRYPT KEY
define("ENCRYPT_KEY","123456");

//tables
define('TBL_USERS','users');