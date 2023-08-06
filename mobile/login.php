<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_login.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');

$controller = new CLoginController($_REQUEST);

$controller->JSON($controller->RunAction());