<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/additem_controller.php');


$controller = new CAddNewItemController(is_array($_REQUEST)?$_REQUEST:array());

$files = (is_array($_FILES) && isset($_FILES['uploadedfile']))?$_FILES['uploadedfile']:array();

$controller->JSON($controller->RunAction($files));
