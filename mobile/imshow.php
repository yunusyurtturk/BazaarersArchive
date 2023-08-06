<?php
header('Content-Type: image/jpeg');

ob_start();

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_imshow.php');

require_once(BASE_PATH.'/model/items/module_items_defs.php');

$controller = new CControllerImShow($_REQUEST);

$image = $controller->RunAction();

$length = filesize($image);
header('Content-length: '.$length);

ob_clean();

if(file_exists($image)){
	readfile($image);
}else {
	readfile('/var/www/html/oop/resources/userpics/default.png');
}
ob_end_flush();
