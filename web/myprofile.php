<?php
require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/controller/mobile/controller_main_page.php');
require_once(BASE_PATH.'/controller/mobile/controller_homescreen.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/items/class_items.php');


require_once(BASE_PATH.'/model/log/logger.php');

require_once('view_funcs/view.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');
require_once(BASE_PATH.'/web/view_funcs/pages/CMyProfilePageTemplateValues.php');


$logger = CLogger::GetLogger();

$templateEngine = new CTemplate();

$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

$view = new CWebView();

$baseTemplateInputs = new SViewBaseTemplateInputs();




$mainPageController = new CMainPageController($_REQUEST);

$isLoggedIn = $mainPageController->LoggedIn();

$lastItems = $mainPageController->RunAction();


$baseTemplateInputs->loggedIn = $isLoggedIn;

///* SITE BASLIYOR */
//if ($isLoggedIn) {
//
//	$userMenu = $view->GetLoggedInTopMenuTemplateValues();
//} else {
//
//	$userMenu = $view->GetNotLoggedInTopMenuTemplateValues();
//}


//$genericTemplateArray = array_merge($view->GetHeaderTemplateValues(), $view->GetTopMenuTemplateValues());
//$userMenu = array_merge($userMenu, $genericTemplateArray);


if($isLoggedIn) {

    $myProfilePageTemplateInputs = new SMyProfilePageTemplateInputs($baseTemplateInputs);

	switch ($action) {

		case 'items':
		    /* USER */
            $baseTemplateInputs->currentPage->myItems = true;

			$user = new CUser($mainPageController->GetUid());
            $myProfilePageTemplateInputs->user = $user;
            $myProfilePageTemplateInputs->action = $action;

            /* ITEMS */
            $myProfilePageTemplateInputs->items = array();

			$userItemIDs = $user->GetItemIDs();

			if(is_array($userItemIDs) && count($userItemIDs) > 0){

				$itemInListGetter = new CItemInList();

				foreach($userItemIDs as &$itemID){
                    $myProfilePageTemplateInputs->items[] = $itemInListGetter->GetItem($itemID);
				}
			}

			$primaryTemplate = (!isset($primaryTemplate))?$templateEngine->loadTemplate('/pages/index.mustache'):$primaryTemplate;

			if(!isset($headerMessage)){

				$headerMessage = _('Your items:');
			}

            $myProfilePageTemplateInputs->baseParams->headerMessagee = &$headerMessage;

            $pageTemplate = new CMyProfilePageTemplateValues($myProfilePageTemplateInputs);

            echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());

		break;
		case 'readNews':
            $baseTemplateInputs->currentPage->news = true;
			$user = new CUser($mainPageController->GetUid());
			$result['error'] = !$user->SetAllNewsAsRead();
		break;
		case "changePassword":
		case "change_password":

        require_once('../controller/mobile/controller_user.php');
        $controller = new CUserController($_REQUEST);

        $controller->JSON($controller->RunAction());

        break;
		case "changeProfilePic":

			$controller = new CHomeScreenController(is_array($_REQUEST)?$_REQUEST:array());
			$files = (is_array($_FILES) && isset($_FILES['uploadedfile']))?$_FILES['uploadedfile']:array();
			$result = $controller->RunAction($files);

		case "myprofile":
		default:
        $baseTemplateInputs->currentPage->profile = true;
			$_REQUEST['action'] = 'myprofile'; // Diğer case ifadesinden gelmesi durumunda

			$homeController = new CHomeScreenController($_REQUEST);

            $userID = $homeController->GetUid();

            $primaryTemplate = (!isset($primaryTemplate))?$templateEngine->loadTemplate('/pages/myprofile.mustache'):$primaryTemplate;

			$result = $homeController->RunAction();


            $myProfilePageTemplateInputs->myProfileUserInfo->uid                    = $userID;
            $myProfilePageTemplateInputs->myProfileUserInfo->followerCount          = $result['followerCount'];
            $myProfilePageTemplateInputs->myProfileUserInfo->followedsItemsCount    = $result['followedsItemsCount'];
            $myProfilePageTemplateInputs->myProfileUserInfo->itemCount              = $result['itemCount'];
            $myProfilePageTemplateInputs->myProfileUserInfo->socialConnections->fb  = $result['isFBConnected'];
            $myProfilePageTemplateInputs->myProfileUserInfo->followCount            = $result['followCount'];
            $myProfilePageTemplateInputs->myProfileUserInfo->username               = $result['username'];
            $myProfilePageTemplateInputs->myProfileUserInfo->email                  = $result['usermail'];
            $myProfilePageTemplateInputs->myProfileUserInfo->info                   = $result['userinfo'];
            $myProfilePageTemplateInputs->myProfileUserInfo->userPic                = $result['userpic'];

            $myProfilePageTemplateInputs->baseParams->news                          = $result['news'];

            $myProfilePageTemplateInputs->socialShares->fb->icon = '/oop/web/images/facebook_64.png';
            $myProfilePageTemplateInputs->socialShares->twitter->icon = '/oop/web/images/twitter_64.png';
            $myProfilePageTemplateInputs->socialShares->whatsapp->icon = '/oop/web/images/whatsapp_64.png';

            $myProfilePageTemplateInputs->socialShares->fb->shareUrl = 'http://www.facebook.com/sharer/sharer.php?u=www.bazaarers.com/user/'.$userID;
            $myProfilePageTemplateInputs->socialShares->twitter->shareUrl = "https://twitter.com/intent/tweet?text=" . _('Check my profile at @Bazaarers: www.bazaarers.com/user/'.$userID) . "&via=Bazaarers";
            $myProfilePageTemplateInputs->socialShares->whatsapp->shareUrl = '';

			//$resultArray = array_merge($userMenu, $result);
//
			//$resultArray = array_merge($resultArray, $view->GetMyProfileTemplateValues(true));
			//$resultArray = array_merge($resultArray, $view->GetMyProfileEmailOpsTemplateValues($result['usermail']));
			//$resultArray = array_merge($resultArray, $view->GetMyProfilePasswordOpsTemplateValues(true));
			//$resultArray = array_merge($resultArray, $view->GetMyProfileProfileOpsTemplateValues( ));
			//$resultArray = array_merge($resultArray, $view->GetMyProfileLocationOpsTemplateValues( ));
			//$resultArray = array_merge($resultArray, $view->GetMyProfileUserpicOpsTemplateValues( ));

//			$resultArray['whatsapp-share-url'] = '';
//			$resultArray['facebook-share-url'] = 'http://www.facebook.com/sharer/sharer.php?u=www.bazaarers.com/user/'.$uid;
//			$resultArray['twitter-share-url'] = "https://twitter.com/intent/tweet?text=" . _('Check my profile at @Bazaarers: www.bazaarers.com/user/'.$uid) . "&via=Bazaarers";
//
//			$resultArray['whatsapp-icon'] = '/oop/web/images/whatsapp_64.png';
//			$resultArray['twitter-icon'] = '/oop/web/images/twitter_64.png';
//			$resultArray['facebook-icon']  = '/oop/web/images/facebook_64.png';
//
            $pageTemplate = new CMyProfilePageTemplateValues($myProfilePageTemplateInputs);
        echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());

	}


}





