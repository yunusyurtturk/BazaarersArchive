<?php
require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/controller/mobile/controller_main_page.php');
require_once(BASE_PATH.'/controller/mobile/controller_cats.php');
require_once(BASE_PATH.'/controller/mobile/controller_following.php');

require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/items/class_items.php');


require_once('view_funcs/view.php');


/* Mustache Initialisations */
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
	'loader' => new Mustache_Loader_FilesystemLoader(BASE_PATH.'/web/templates'),
	'partials_loader' => new Mustache_Loader_FilesystemLoader(BASE_PATH.'/web/templates/partials')
));


$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$view = new CWebView();


	$mainPageController = new CMainPageController($_REQUEST);
	$categories = new CCategoriesController(array('parent' => 0));

	$lastItems = $mainPageController->RunAction();


	/* SITE BASLIYOR */
	if ($mainPageController->LoggedIn()) {

		$userMenu = $view->GetLoggedInTopMenuTemplateValues();
	} else {

		$userMenu = $view->GetNotLoggedInTopMenuTemplateValues();
	}


	$genericTemplateArray = array(


		'page-title' => 'Bazaarers',
		'site-addr' => $view->GetSiteAddressNS(),
		'templates' => array(
			array('template-id' => 'item-in-list-template',
				'template' => $view::LoadTemplate(BASE_PATH . '/web/templates/partials/item-in-list')),
			array('template-id' => 'user-in-list-template',
				'template' => $view::LoadTemplate(BASE_PATH . '/web/templates/partials/header'))),

		'logo-image' => $view->GetLogoImage(),
		'logo-image-alt' => '',
		'header-text' => $view->GetHeaderText(),
		'header-text-secondary' => $view->GetHeaderSecondaryText()

	);

	$resultArray = array_merge($userMenu, $genericTemplateArray);


	$childCats = $categories->RunAction();
	$childCatsFormatted = array();

	if ($childCats['hassubcats']) {

		$resultArray['categories-header'] = _('Categories');
		$childCount = count($childCats['catnames']);
		for ($i = 0; $i < $childCount; $i++) {

			$resultArray['categories'][] = array('category-link' => '/cat/'.$childCats['catids'][$i], 'category-name' => $childCats['catnames'][$i]);
		}
	}

	switch($action){

		case "fbfriends":
		case 'follow':
		case "followers":

		case "followings":
			$resultArray = (!isset($primaryTemplate))?$mustache->loadTemplate('/pages/followers.mustache'):$primaryTemplate;

			if(!isset($primaryTemplate['header-message'])) {
				$resultArray['header-message'] = _('People you follow:');
			}

		case "followeds_items":
		case 'follower_items':
			$primaryTemplate = (!isset($primaryTemplate))?$mustache->loadTemplate('/pages/index.mustache'):$primaryTemplate;


			$controller = new CFollowingController($_REQUEST);
			$returnVal = $controller->RunAction();

			$returnVal['userpicpath'] = 'userpics'.DIRECTORY_SEPARATOR;
			$returnVal['userpath'] = 'user'.DIRECTORY_SEPARATOR;
			$resultArray = array_merge($resultArray, $returnVal);

			if(!isset($resultArray['header-message'])){

				$resultArray['header-message'] = _('Items of people following you:');
			}
			echo
			$primaryTemplate->render(
				$resultArray
			);
			break;
		default:
			require_once(BASE_PATH.'/controller/mobile/controller_homescreen.php');

			$primaryTemplate = (!isset($primaryTemplate))?$mustache->loadTemplate('/pages/index.mustache'):$primaryTemplate;
			$_REQUEST['action'] = 'items';

			$controller = new CHomeScreenController($_REQUEST);
			$returnVal = $controller->RunAction();
			
			$resultArray['items'] = $lastItems;

			if(!isset($headerMessage)){

				$headerMessage = _('Your items:');
			}



			$resultArray['header-message'] = &$headerMessage;
			echo $primaryTemplate->render($resultArray);




	}








