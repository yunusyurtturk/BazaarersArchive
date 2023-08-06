<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_homescreen.php');

$controller = new CHomeScreenController(is_array($_REQUEST)?$_REQUEST:array());

$controller->JSON($controller->RunAction((is_array($_FILES) && isset($_FILES['uploadedfile']))?$_FILES['uploadedfile']:array()));
