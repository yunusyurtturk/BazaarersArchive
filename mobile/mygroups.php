<?php
header('Content-Type: text/html; charset=utf-8');

$action=trim(strip_tags(htmlspecialchars($_REQUEST['action'])));

require('../controller/mobile/controller_register.php');

$controller = new CRegisterController($action);
$controller->JSON($controller->RunAction());