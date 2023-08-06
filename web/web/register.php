<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_register.php');
$controller = new CRegisterController($_REQUEST);

$result = $controller->RunAction();

$action = $result['action'];


switch($action){
	
	case "reset":
		
		echo $result['message'];
	break;
}