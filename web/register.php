<?php

require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_register.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');

require_once('view_funcs/view.php');


require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');

require_once('view_funcs/pages/CRegisterPageTemplateValues.php');

$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

$view = new CWebView();

$templateEngine = new CTemplate();

$controller = new CRegisterController($_REQUEST);
$isLoggedIn = $controller->LoggedIn();

$viewTemplateInputs = new SViewBaseTemplateInputs();

$viewTemplateInputs->loggedIn  =  $isLoggedIn;
$viewTemplateInputs->isLocated =  false;

if(!$isLoggedIn){

    $regOrLogTemplateInputs = new SRegOrLogTemplateInputs($viewTemplateInputs);

	if(!empty($_REQUEST)){

        $registerResult = $controller->RunAction();


        if(ERR_NEW_USER_NO_ERROR == $registerResult['error']){


            $_SESSION['uid'] = $registerResult['userid'];
            $_SESSION['logged_in'] = true;
            $_SESSION['passcode'] = $registerResult['passcode'];

            if(isset($registerResult['lat']) && isset($registerResult['lng'])){

                $_SESSION['lat'] = $registerResult['lat'];
                $_SESSION['lng'] = $registerResult['lng'];
            }

            header( "Location: index.php?action=newMember" );
        }else{

            $regOrLogTemplateInputs->error = true;
            $regOrLogTemplateInputs->errorMessage = $registerResult['message'];

        }

	}

    $primaryTemplate = (!isset($primaryTemplate))?$templateEngine->LoadTemplate('/pages/regorlog.mustache'):$primaryTemplate;



    $regOrLogTemplateInputs->action = 'register';

    $pageTemplateValues = new CRegOrLogPageTemplateValues($regOrLogTemplateInputs);

    echo $templateEngine->Render($primaryTemplate, $pageTemplateValues->GetValues());


}else{


}






/* SITE BASLIYOR */



