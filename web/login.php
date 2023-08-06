<?php 


require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_login.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');

require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateParams.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateHolder.php');

require_once('view_funcs/view.php');

require_once('web/config.php');


$templateEngine = new CTemplate();
$templateParams = new CTemplateParams();

$view = new CWebView();

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';


$controller = new CLoginController($_REQUEST);


switch($action) {
	case 'logout':
		if ($controller->LoggedIn()) {
			require_once(BASE_PATH.'/controller/mobile/controller_user.php');

			$controller = new CUserController($_REQUEST);

			$logoutResult = $controller->RunAction();
			if(true == $logoutResult['logged_out']){

				unset($_SESSION['uid']);
				unset($_SESSION['logged_in']);
				unset($_SESSION['passcode']);
                unset($_SESSION['lat']);
                unset($_SESSION['lng']);

				header( "Location: index.php" );
			}
		} else {

			header("Location: index.php");
		}
		break;
	break;

	case 'login':
	default:
		if (!$controller->LoggedIn()) {

			$loginPageTemplate = $templateEngine->loadTemplate('/pages/regorlog.mustache');
			$templateParams->AddNotAuthUserTopMenu();
			$templateParams->AddHeaderTemplateValues();
			$templateParams->AddTopMenuTemplateValues();
			$templateParams->AddLoginTemplateValues();

			if (empty($_REQUEST)) {

				echo
				$templateEngine->Render($loginPageTemplate, $templateParams->GetParams());
			} else {


				$loginResult = $controller->RunAction();

				if (false == $loginResult['error']) {


					$_SESSION['uid'] = $loginResult['userid'];
					$_SESSION['logged_in'] = true;
					$_SESSION['passcode'] = $loginResult['passcode'];

                    $locationUpToDate = false;
                    $location = null;

                    if(isset($_SESSION['lat']) && isset($_SESSION['lng'])){
                        $location = new CLocation($_SESSION['lat'], $_SESSION['lng']);

                        if($location->IsValid()){

                            $locationUpToDate = true;
                        }
                    }

                    if(true == $locationUpToDate){

                        $user = new CUser($loginResult['userid']);
                        $user->SetLocation($location);


                    }else{

                        if(isset($loginResult['lat']) && isset($loginResult['lng'])){
                            $location = new CLocation($_SESSION['lat'], $_SESSION['lng']);
                            $_SESSION['lat'] = $loginResult['lat'];
                            $_SESSION['lng'] = $loginResult['lng'];
                        }else{
                        }



                    }



					header("Location: index.php");
				} else {
					$templateParams->AddLoginFailedTemplateValues();
					echo
					$templateEngine->Render($loginPageTemplate, $templateParams->GetParams());
				}
			}
		} else {

			header("Location: index.php");
		}
	break;

}

