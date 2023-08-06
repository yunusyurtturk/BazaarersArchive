<?php
header('Content-Type: text/html; charset=utf-8');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../web/config.php');

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

require_once(BASE_PATH.'/web/view_funcs/view.php');
require_once(BASE_PATH.'/web/view_funcs/pages/CIndexPageTemplateValues.php');


require_once(BASE_PATH.'/controller/admin/CAdminController.php');

$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$view = new CWebView();

$templateEngine = new CTemplate();


$controller = new CAdminController($_REQUEST);

$isLoggedIn = $controller->LoggedIn();

$controller->RunAction();