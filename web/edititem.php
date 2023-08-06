<?php

require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/edititem_controller.php');

require_once(BASE_PATH.'/model/items/class_items.php');

require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');
require_once(BASE_PATH.'/model/paths/CPaths.php');

require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');

require_once('view_funcs/view.php');
require_once('view_funcs/pages/CEditItemPageTemplateValues.php');

require_once(BASE_PATH.'/model/log/logger.php');

function GetLat(){

    return $_SESSION['lat'];
}
function GetLng(){
    return $_SESSION['lng'];
}
function IsLocated()
{
    if(isset($_SESSION) && isset($_SESSION['lat']) && isset($_SESSION['lng'])){
        return true;
    }else{
        return false;
    }
}


$logger = CLogger::GetLogger();

$templateEngine = new CTemplate();

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$view = new CWebView();
$baseTemplateInputs = new SViewBaseTemplateInputs();


//$addItemPageTemplate 		  = $mustache->loadTemplate('/pages/additem.mustache');

$controller = new CEditItemController(is_array($_REQUEST)?$_REQUEST:array());

$isLoggedIn = $controller->LoggedIn();

$baseTemplateInputs->loggedIn  =  $isLoggedIn;
$baseTemplateInputs->isLocated =  IsLocated();

if($baseTemplateInputs->isLocated) {
    $baseTemplateInputs->location = new CLocation(GetLat(), GetLng());
}

$itemID = (isset($_REQUEST['iid']) && is_numeric($_REQUEST['iid']))?$_REQUEST['iid']:0;
$editCode = (isset($_REQUEST['editcode']) && is_numeric($_REQUEST['editcode']))?$_REQUEST['editcode']:0;
$editable = true;

$logger = CLogger::GetLogger();

switch($action){
    case 'previouslyAddedImages':
        $returnVal['error'] = true;
        if($controller->LoggedIn()){

            $item = new CItems($itemID);

            $returnVal['images'] = $item->GetPics();


            foreach ($returnVal['images'] as &$image) {

                $imageName = $image;
                $image = IMAGE_CFG_ITEMPICS_PATH.$image;
                if(file_exists($image)){

                    $image = array(
                        'name' => 	$imageName,
                        'size' =>   filesize($image),
                        'url'  =>   $view->GetSiteAddress().'/itempics/'.$imageName,
                        'removeUrl'  =>   $view->GetSiteAddress().'/edititem.php?action=removeImage&iid='.$itemID.'&image='.$imageName
                    );
                }
            }


        }



        echo json_encode($returnVal);
    break;
    case 'removeImage':
        $imageName = (isset($_REQUEST['image']))?$_REQUEST['image']:'';

        $returnVal = array();
        $returnVal['error'] = true;
        $returnVal['message'] = _('Error while deleting');

        if(true == $editable && !empty($imageName) && is_numeric($itemID) && $itemID > 0){

                $item = new CItems($itemID);
                $images = $item->GetPics();

                if(in_array($imageName, $images)){

                    if (unlink(ITEM_PICS_PATH.$imageName)){
                        $returnVal['error'] = false;
                        $returnVal['message'] = _('Image removed');

                        $removeResult = $item->RemoveImage($controller->GetUid(), $imageName);
                        if(false == $removeResult){

                            $logger->DLog(0,
                                __FUNCTION__,
                                __CLASS__,
                                func_get_args(),
                                $_SERVER['PHP_SELF'],
                                $_SERVER['QUERY_STRING'],
                                'Error: Fotoğraf dosya sisteminden silindi fakat DB\'den silinemedi: ItemID = '.$itemID.' ve FileName = ' .($imageName));
                        }
                    }else{
                        $returnVal['message'] = _('Error while deleting image');
                    }

                }else{
                    $returnVal['message'] = _('Image not belong to item');
                }



        }else{

            $returnVal['message'] = _('Unauthorized action');
        }

        echo json_encode($returnVal);
        break;
    case 'uploadImage':
        $returnVal['error'] = true;

        if($controller->LoggedIn() ){
            $item = new CItems($itemID);

            if($controller->GetUid() == $item->GetOwnerID()){
                $itempicsDir = ITEM_PICS_PATH;

                $imageCount = $item->GetImageCount();
                if($imageCount <= IMAGE_CFG_MAX_ITEM_IMAGE_COUNT){

                    $filename = '';

                    while (true) {
                        $filename = str_replace(".", "", uniqid("", true)). '.jpg';
                        if (!file_exists($itempicsDir . $filename)) {

                            break;
                        }
                    }

                    $result = $item->AddImage($_FILES['uploadedfile']["tmp_name"], $filename);
                    if(false == $result['error']){

                        $returnVal['error'] = false;
                    }


                }

            }

        }else{

        }
        echo json_encode($returnVal);
    break;
	case 'edited':

	default:
    $itemEditedSuccesfully = false;
        require_once('view_funcs/pages/CAddItemPageTemplateValues.php');


        /** @var   SAddItemFormErrors */
        $errorCauses = new SAddItemFormErrors();


        if((isset($_REQUEST['formapproved'])
            && !empty($_REQUEST['formapproved'])
            && $_REQUEST['formapproved'] == 'formapproved'))
        {
            $editedItemDataReceived = true;
            $addItemResult = $controller->RunAction();

            if(false === $addItemResult['error']){

                $itemEditedSuccesfully = true;
                //header( "Location: /item/".$addItemResult['iid']);
                die();

            }else{

                $errorMessage = $addItemResult['message'];
                $errorCauses  = $addItemResult['errors'];

            }
        }

        if(false == $itemEditedSuccesfully){

            unset($_REQUEST['formapproved']);

            $baseTemplateInputs->headerMessage = _('Edit Item Form');

            if(true == $isLoggedIn){
                require_once(BASE_PATH.'/web/adapters/CNewsAdapter.php');

                $user = new CUser($controller->GetUid());

                $baseTemplateInputs->news = CNewsAdapter::SAdaptNews($user->GetNews(30));

                $DIContainer = new CContainer();
                $db = $DIContainer->GetDBService();
                $baseTemplateInputs->notifications = CUser::SGetUnreadNewsCount($controller->GetUid(), $db);

            }

            $addItemPageTemplateInputs = new SEditItemPageTemplateInputs($itemID, $baseTemplateInputs);


            $addItemPageTemplateInputs->action = $action;


            $fields = $controller->RunAction();

            if(!isset($fields['error']) || false == $fields['error']){

                $addItemPageTemplateInputs->isError = true;
                //$addItemPageTemplateInputs->errorMessage = $errorMessage;
                $addItemPageTemplateInputs->isNewItemInfoExist = true;

                $addItemPageTemplateInputs->newItemInfo->title       = $fields['title']      ;
                $addItemPageTemplateInputs->newItemInfo->description = $fields['description'];
                $addItemPageTemplateInputs->newItemInfo->price       = $fields['price']      ;
                $addItemPageTemplateInputs->newItemInfo->priceType   = $fields['priceType']  ;
                $addItemPageTemplateInputs->newItemInfo->amount      = $fields['amount']     ;
                $addItemPageTemplateInputs->newItemInfo->cat         = $fields['cat']        ;
                $addItemPageTemplateInputs->newItemInfo->mainpic     = $fields['mainpic']        ;


                $addItemPageTemplateInputs->errorCauses->images        = $errorCauses->images;
                $addItemPageTemplateInputs->errorCauses->amount        = $errorCauses->amount;
                $addItemPageTemplateInputs->errorCauses->category      = $errorCauses->category;
                $addItemPageTemplateInputs->errorCauses->description   = $errorCauses->description;
                $addItemPageTemplateInputs->errorCauses->price         = $errorCauses->price;
                $addItemPageTemplateInputs->errorCauses->priceType     = $errorCauses->priceType;
                $addItemPageTemplateInputs->errorCauses->name          = $errorCauses->name;
                $addItemPageTemplateInputs->errorCauses->other         = $errorCauses->other;

            }



            $pageTemplate = new CEditItemPageTemplateValues($addItemPageTemplateInputs);


            $primaryTemplate = (!isset($primaryTemplate))?$templateEngine->loadTemplate('/pages/additem.mustache'):$primaryTemplate;


            echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());
        }












/*
		$resultTemplateValues = array_merge($view->GetHeaderTemplateValues(), $view->GetTopMenuTemplateValues());
		if ($controller->LoggedIn()) {

			$userMenu = $view->GetLoggedInTopMenuTemplateValues();
		} else {

			$userMenu = $view->GetNotLoggedInTopMenuTemplateValues();
		}
		$resultTemplateValues = array_merge($resultTemplateValues, $userMenu);
*/

//		if($controller->LoggedIn()){
//
//			$additemFormTemplateValues = $view->GetAddItemFormTemplateParams(null, isset($_REQUEST['priceType'])?array('price-type' => $_REQUEST['priceType']):null);
//
//			require_once(BASE_PATH.'/model/DI/class_container.php');
//
//
//			$additemFormTemplateValues['templates'][] = array('template-id' => 'categories',
//				'template' => json_encode($cats));
//
//			$additemFormTemplateValues['templates'][] = array('template-id' => 'category-selection',
//				'template' => $view::LoadTemplate(BASE_PATH.'/web/templates/partials/additem-form-category-selection'));
//
//
//
//			if(isset($_REQUEST) && isset($_REQUEST['formapproved']) && $_REQUEST['formapproved']){
//
//				$files = array();
//				for($i = 0; $i < 5; $i++){
//
//					$picNameBasic = 'pic_'.$controller->GetUid().'_'.$i.'.jpg';
//
//					if(file_exists(TEMP_ITEM_PICS_PATH.$picNameBasic)){
//
//						$files['name'][] = $picNameBasic;
//						$files['type'][] = 'image/jpeg';
//						$files['tmp_name'][] = TEMP_ITEM_PICS_PATH.$picNameBasic;
//						$files['error'][] = UPLOAD_ERR_OK;
//						$files['size'][] = filesize(TEMP_ITEM_PICS_PATH.$picNameBasic);
//
//					}
//				}
//
//				$addItemResult = $controller->RunAction($files);
//
//				if(true == $addItemResult['error']){
//
//					$additemFormTemplateValues['itemname-value'] 	= $_REQUEST['header'];
//					$additemFormTemplateValues['description-value'] = $_REQUEST['description'];
//					$additemFormTemplateValues['price-value'] 		= isset($_REQUEST['price'])?$_REQUEST['price']:'';
//					$additemFormTemplateValues['amount-value'] 		= $_REQUEST['amount'];
//
//
//					$addItemResult['error-message'] = $addItemResult['message'];
//				}else{
//
//					header( "Location: /item/".$addItemResult['iid']);
//				}
//
//				$additemFormTemplateValues = array_merge($additemFormTemplateValues, $addItemResult);
//			}else{
//
//
//			}
//			$resultTemplateValues['logged-in'] = true;
//			$resultTemplateValues['previously-uploaded-images-get-url'] = '/additem.php?action=previouslyAddedImages';
//
//			$templateParams = array_merge($resultTemplateValues, $additemFormTemplateValues);
//
//		}else{
//
//			$resultTemplateValues['login-text'] = 'Click to log in';
//			$resultTemplateValues['login-addr'] = '/login';
//			$resultTemplateValues['not-logged-in-message'] = _('You must login to add items');
//			$templateParams = &$resultTemplateValues;
//
//		}
//
//
//		echo
//		$addItemPageTemplate->render($templateParams);
}


/*

echo '<pre>';
var_dump($cats);
echo '</pre>';*/






