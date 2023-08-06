<?php
require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/controller/mobile/controller_main_page.php');
require_once(BASE_PATH.'/controller/mobile/controller_cats.php');
require_once(BASE_PATH.'/controller/mobile/controller_following.php');
require_once(BASE_PATH.'/controller/mobile/controller_search_range.php');


require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/items/class_items.php');

require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateParams.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateHolder.php');

require_once('view_funcs/view.php');
require_once('view_funcs/pages/CIndexPageTemplateValues.php');




$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$view = new CWebView();

$templateEngine = new CTemplate();



$mainPageController = new CMainPageController($_REQUEST);

$isLoggedIn = $mainPageController->LoggedIn();

$indexBaseTemplateInputs = new SViewBaseTemplateInputs();



$indexBaseTemplateInputs->loggedIn  =  $isLoggedIn;
$indexBaseTemplateInputs->isLocated =  $mainPageController->IsLocated();


if($indexBaseTemplateInputs->isLocated) {
	$indexBaseTemplateInputs->location = new CLocation($mainPageController->GetLat(), $mainPageController->GetLng());
}



switch($action){

	case 'users':
		require_once(BASE_PATH.'/web/adapters/CUsersAdapter.php');

		$controller = new CSearchRangeController($_REQUEST);
		$returnVal = $controller->RunAction();

		$adapter = new CUsersAdapter();
		$adapter->AdaptSearchRangeUsers($returnVal, $controller->LoggedIn());

		echo json_encode($returnVal);
		break;
    case 'items':

        $returnVal = array();

        if( !$indexBaseTemplateInputs->isLocated){

            $returnVal['header-message'] =  _('Listing items recently added');
            $returnVal['items'] = $mainPageController->RunAction();
        }else{

            $controller = new CSearchRangeController(array('action' => 'items',
                'lat' => $mainPageController->GetLat(),
                'lng' => $mainPageController->GetLng(),
                'radius' => 500
            ));
            $returnVal = $controller->RunAction();
        }

        echo json_encode($returnVal);
        break;
    break;
	case 'userLocation':
		$returnVal = $mainPageController->RunAction();
		$controller = new CSearchRangeController(array('action' => 'items',
			'lat' => $returnVal['lat'],
			'lng' => $returnVal['lng'],
			'radius' => 500
		));

		echo $controller->JSON($controller->RunAction());
    break;

	case "followers":

		if(!isset($headerMessage)){

			$headerMessage = _('People following you');
            $indexBaseTemplateInputs->currentPage->followers = true;
		}
	case "followings":

        if(true == $isLoggedIn){
            require_once(BASE_PATH.'/web/adapters/CNewsAdapter.php');
            $indexBaseTemplateInputs->news = CNewsAdapter::SAdaptNews($mainPageController->GetNews(30));

        }
		require_once('view_funcs/pages/CIndexPageFollowTemplateValues.php');

		if(!isset($headerMessage)){

			$headerMessage = _('People you follow:');
            $indexBaseTemplateInputs->currentPage->followings = true;
		}

		$indexBaseTemplateInputs->headerMessage = $headerMessage;

		$controller = new CFollowingController($_REQUEST);
		$returnVal = $controller->RunAction();

		require_once(BASE_PATH.'/web/adapters/CUsersAdapter.php');
		$adapter = new CUsersAdapter();
		$adapter->AdaptSearchRangeUsers($returnVal, $controller->LoggedIn());

        $primaryTemplate = (!isset($primaryTemplate))?$templateEngine->loadTemplate('/pages/followers.mustache'):$primaryTemplate;

        $followTemplateInputs = new SIndexPageFollowTemplateInputs($indexBaseTemplateInputs);
		$followTemplateInputs->users = $returnVal;
		$followTemplateInputs->action = $action;





		$pageTemplate = new CIndexPageFollowTemplateValues($followTemplateInputs);

		echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());



		break;
	case "followeds_items":
        if(!isset($headerMessage)){

            $indexBaseTemplateInputs->currentPage->followingItems = true;
            $headerMessage = _('Items of people you follow:');
        }
	case 'follower_items':

        if(true == $isLoggedIn){
            require_once(BASE_PATH.'/web/adapters/CNewsAdapter.php');
            $indexBaseTemplateInputs->news = CNewsAdapter::SAdaptNews($mainPageController->GetNews(30));

            $indexBaseTemplateInputs->currentPage->followingItems = true;
        }


        require_once('view_funcs/pages/CFollowedsItemsTemplateValues.php');
		$primaryTemplate = (!isset($primaryTemplate))?$templateEngine->loadTemplate('/pages/index.mustache'):$primaryTemplate;
        $controller = new CFollowingController($_REQUEST);
        $returnVal = $controller->RunAction();
        $returnVal['userpicpath'] = 'userpics'.DIRECTORY_SEPARATOR;
        $returnVal['userpath'] = 'user'.DIRECTORY_SEPARATOR;


        $indexBaseTemplateInputs->headerMessage = $headerMessage;

        $followedsItemsTemplateInputs               = new SFollowedsItemsTemplateInputs($indexBaseTemplateInputs);
        $followedsItemsTemplateInputs->userPicPath  = 'userpics'.DIRECTORY_SEPARATOR;
        $followedsItemsTemplateInputs->userPath     = 'user'.DIRECTORY_SEPARATOR;
        $followedsItemsTemplateInputs->items        = &$returnVal;
        $followedsItemsTemplateInputs->action       = $action;

        $pageTemplate = new CFollowedsItemsTemplateValues($followedsItemsTemplateInputs);

		echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());
		break;

	case 'newMember':

		// $templateParams->AddNewUserWelcomeTemplateValues();
		$isNewMember = true;


	default:

        $indexBaseTemplateInputs->currentPage->explore = true;

        if(true == $isLoggedIn){
            require_once(BASE_PATH.'/web/adapters/CNewsAdapter.php');
            $indexBaseTemplateInputs->news = CNewsAdapter::SAdaptNews($mainPageController->GetNews(30));

            $DIContainer = new CContainer();
            $db = $DIContainer->GetDBService();
            $indexBaseTemplateInputs->notifications = CUser::SGetUnreadNewsCount($mainPageController->GetUid(), $db);



        }

		$indexTemplateInputs = new SIndexPageTemplateInputs($indexBaseTemplateInputs);




		$indexTemplateInputs->isNewMember = (isset($isNewMember) && $isNewMember==true)?true:false;


		if( !$indexBaseTemplateInputs->isLocated){

			$indexTemplateInputs->items = $mainPageController->RunAction();


		}else{

			$controller = new CSearchRangeController(array('action' => 'items',
				'lat' => $mainPageController->GetLat(),
				'lng' => $mainPageController->GetLng(),
				'radius' => 500
			));
			$indexTemplateInputs->items = $controller->RunAction();
		}
		$primaryTemplate = (!isset($primaryTemplate))?$templateEngine->LoadTemplate('/pages/index.mustache'):$primaryTemplate;

		$pageTemplate = new CIndexPageTemplateValues($indexTemplateInputs);

		echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());


}






