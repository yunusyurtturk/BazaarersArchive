<?php
require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/controller/mobile/controller_user.php');
require_once(BASE_PATH.'/controller/mobile/controller_homescreen.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');

require_once('view_funcs/view.php');

$view = new CWebView();


/* Mustache Initialisations */
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
	'loader' => new Mustache_Loader_FilesystemLoader(BASE_PATH.'/web/templates'),
	'partials_loader' => new Mustache_Loader_FilesystemLoader(BASE_PATH.'/web/templates/partials')
));

$homeScreenController = new CUserController($_REQUEST);


$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';


$resultTemplateValues = array_merge($view->GetHeaderTemplateValues(), $view->GetTopMenuTemplateValues());
if ($homeScreenController->LoggedIn()) {

	$userMenu = $view->GetLoggedInTopMenuTemplateValues();
	$userMenu['loggedIn'] = true;
} else {

	$userMenu = $view->GetNotLoggedInTopMenuTemplateValues();
}
$resultTemplateValues = array_merge($resultTemplateValues, $userMenu);

switch($action){

	case 'follow':
		require_once(BASE_PATH.'/controller/mobile/controller_following.php');

		$followController = new CFollowingController($_REQUEST);
		$result = $followController->RunAction();
		

		echo json_encode($result);
		break;


}


