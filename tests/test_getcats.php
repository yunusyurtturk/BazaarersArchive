<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_cats.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');


$mock = array();


/* Followings Testi */

 $mock['action'] = "followings";
 $mock['catid'] = 0;


$controller = new CCategoriesController($mock);
echo '----------------------------------';
var_dump($controller->RunAction());
echo '----------------------------------';
$mock['catid'] = 4;


$controller = new CCategoriesController($mock);

var_dump($controller->RunAction());
echo '----------------------------------';
$mock['catid'] = 1;


$controller = new CCategoriesController($mock);

var_dump($controller->RunAction());
echo '----------------------------------';
$mock['catid'] = 124111;


$controller = new CCategoriesController($mock);

var_dump($controller->RunAction());
echo '----------------------------------';
$mock['catid'] = 4;


$controller = new CCategoriesController($mock);

var_dump($controller->RunAction());
