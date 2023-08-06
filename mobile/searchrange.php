<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_search_range.php');

$controller = new CSearchRangeController($_REQUEST);

$controller->JSON($controller->RunAction());