<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_cats.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');

$controller = new CCategoriesController($_REQUEST);

$controller->JSON($controller->RunAction());