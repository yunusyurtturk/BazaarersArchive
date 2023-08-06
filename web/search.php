<?php 


require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_search.php');
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



$primaryTemplate = (!isset($primaryTemplate))?$templateEngine->loadTemplate('/pages/index.mustache'):$primaryTemplate;

$controller = new CSearchController($_REQUEST);
$result = $controller->RunAction();

$searchTerm = ($_REQUEST['term']);

switch($action) {
	case 'user':
	case 'item':
	case 'group':
	default:

        $templateParams->InitMainPageParams($controller->LoggedIn());
        $templateParams->Add('header-message', _('Results for "'.$searchTerm.'"'));

        if(mb_strlen($searchTerm, 'UTF-8') < 2){

            $templateParams->Add('error-message', _('Type at least 2 characters'));
        }else{


            $templateParams->Merge($result);

            if($result['count'] < 1){

                $templateParams->Add('error-message', _('No results found'));
            }
        }


		echo
			$templateEngine->Render($primaryTemplate, $templateParams->GetParams());
	break;

}

