<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../mobile/imshow.php');


$mock = array();
$mock['action']  = 'h';
$mock['im']  =   'upload_0_41.jpg';
$mock['screen']  = 35;

 
$controller = new CControllerImShow($mock);

var_dump($controller->RunAction());
