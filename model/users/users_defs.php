<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/defs/err_base_defs.php');

define('ERR_LOGIN_NO_ERROR', 			0);
define('ERR_LOGIN_USER_NOT_FOUND', 			ERR_LOGIN_NO_ERROR + 1);
define('ERR_NEW_USER_NO_ERROR', 		0);
define('ERR_SEARCH_RANGE_NO_ERROR', 		0);

define('ERR_NEW_USER_REGISTERED_BUT_LOGIN', ERR_NEW_USER_BASE + 1);
define('ERR_NEW_USER_NO_AFFECTED_ROWS', 	ERR_NEW_USER_BASE + 2);
define('ERR_NEW_USER_NOT_EXECUTED', 		ERR_NEW_USER_BASE + 3);
define('ERR_NEW_USER_CANT_REGISTERED', 		ERR_NEW_USER_BASE + 4);
define('ERR_NEW_USER_ERROR', 				ERR_NEW_USER_BASE + 5);
define('ERR_NEW_USER_NOT_AVAILABLE', 		ERR_NEW_USER_BASE + 6);
define('ERR_NEW_USER_INVALID_CREDIENTIALS', ERR_NEW_USER_BASE + 7);



define('ERR_SEARCH_RANGE_INVALID_LOCATION', 				ERR_SEARCH_RANGE_BASE + 0);

