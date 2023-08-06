<?php
require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/controller/mobile/controller_user.php');
require_once(BASE_PATH.'/controller/mobile/controller_homescreen.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');

require_once('view_funcs/view.php');

require_once(BASE_PATH.'/model/in_list_getters/CItemInList.php');

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
	case "getItems":
	case "getFollowers":
	case "getFollowings":
	case "getGroups":
	case "user_info_page":
	case "userInfo":
	case "changePassword":
	case "change_password":
	case "change_location_type":
	case "update_about":
	case "updateLocation":
	case "removeLocation":
	case "settings":
	case "logout":
		$controller = new CUserController($_REQUEST);
		$controller->JSON($controller->RunAction());

	break;


	case 'news':

		if($homeScreenController->LoggedIn()){

			require_once(BASE_PATH.'/model/news/class_news_action_creater.php');
			$user = new CUser($_SESSION['uid']);

			$userNews = $user->GetNews(30);
			$userPageTemplate 		  = $mustache->loadTemplate('/pages/user-news.mustache');

			if(!empty($userNews) && count($userNews) > 0) {
				foreach ($userNews as $news) {

					$newsArray = array('news-text' => $news['news'], 'news-date' => $news['date'],
						'action' => CNewsWebActionCreator::GetAction($news['actionType'], $news['primaryID'])
					);
					if(false == $news['isRead']){

						$newsArray['unread'] = true;
					}

					$resultTemplateValues['news'][] = &$newsArray;

				}
			}else{

				$resultTemplateValues['error-message'] = _('You have no news yet');
				
			}
			$resultTemplateValues = array_merge($resultTemplateValues, $view->GetLoggedInTopMenuTemplateValues());


		}else{

		}
		echo $userPageTemplate->render($resultTemplateValues);
	break;

	default:


		$userPageTemplate 		  = $mustache->loadTemplate('/pages/user.mustache');


		$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;
		$user = new CUser($uid);

		if($user->IsExist()){

			$userItems = $user->GetItems(array('iid', 'header', 'mainpic'));
			if(is_array($userItems) && count($userItems) > 0){

				$itemInListGetter = new CItemInList();

				foreach($userItems as &$item){

					$item = $itemInListGetter->GetItem($item['iid']);

					$item['user-profile-url'] = '/user/'.$item['uid'];

				}
			}
		}

		if(isset($_SESSION['uid'])){
			if($user->GetUserID() != $_SESSION['uid']) {

				$currentUser = new CUser($_SESSION['uid']);

				$followButtonTemplates = array('uid' => $user->GetUserID(),
					'follow-button-text' => _('Follow'),
					'follow-url' => '/oop/web/following.php?action=follow'
				);

				if ($user->IsFollowedBy($_SESSION['uid'])) {
					$followButtonTemplates['follow-is-following'] = true;
					$followButtonTemplates['follow-button-text'] = _('Unfollow');
				}

				$resultTemplateValues = array_merge($resultTemplateValues, $followButtonTemplates);
			}else{
				$resultTemplateValues['is-own-profile'] = true;
			}
		}

		$resultArray = array('userid' 	=> $user->GetUserID(),
			'username' 	=> $user->GetUsername(),
			'userpic' 	=> $user->GetPic(),
			'userinfo'  => $user->GetUserAbout(),
			'items' 	=> isset($userItems)?$userItems:false,
			'no-item-message' => _('This user doesn\'t have any items yet'));

		$resultArray['whatsapp-share-url'] = '';
		$resultArray['facebook-share-url'] = 'http://www.facebook.com/sharer/sharer.php?u=www.bazaarers.com/user/'.$uid;
		$resultArray['twitter-share-url'] = "https://twitter.com/intent/tweet?text=" . _('Check '.$resultArray['username'].'\'s items at @Bazaarers: www.bazaarers.com/user/'.$uid) . "&via=Bazaarers";

		$resultArray['whatsapp-icon'] = '/oop/web/images/whatsapp_64.png';
		$resultArray['twitter-icon'] = '/oop/web/images/twitter_64.png';
		$resultArray['facebook-icon']  = '/oop/web/images/facebook_64.png';

		$resultTemplateValues = array_merge($resultTemplateValues, $resultArray);

		$resultTemplateValues = array_merge($resultTemplateValues,
			array(
				'templates' => array(
					array('template-id'=>'item-in-list-template',
						'template'=> $view::LoadTemplate(BASE_PATH.'/web/templates/partials/item-in-list')),
					array('template-id'=>'user-in-list-template',
						'template'=> $view::LoadTemplate(BASE_PATH.'/web/templates/partials/user-in-list'))),




				'og-type' =>  'product.group',
				'og-title' =>  _('Kişi Ürünleri - ').$user->GetUsername(),
				'og-description' =>  $user->GetUsername().' '._('\'s items'),
				'og-image' =>  $user->GetPic(),







			)
		);
		echo $userPageTemplate->render($resultTemplateValues);
}


