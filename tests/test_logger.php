<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_item.php');
require_once(BASE_PATH.'/model/log/logger.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');

$mock = array();


/* Followings Testi */

 $mock['action'] = "removeItem";
 $mock['uid'] = 1;
 $mock['iid'] = 88;
 
$controller = new CItemController($mock);

var_dump($controller->RunAction());
