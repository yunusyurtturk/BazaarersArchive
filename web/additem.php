<?php

require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/additem_controller.php');

require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');
require_once(BASE_PATH.'/model/paths/CPaths.php');

require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');

require_once('view_funcs/view.php');
require_once('view_funcs/pages/CAddItemPageTemplateValues.php');

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

$controller = new CAddNewItemController(is_array($_REQUEST)?$_REQUEST:array());
$isLoggedIn = $controller->LoggedIn();

$baseTemplateInputs->loggedIn  =  $isLoggedIn;
$baseTemplateInputs->isLocated =  IsLocated();
if($baseTemplateInputs->isLocated) {
    $baseTemplateInputs->location = new CLocation(GetLat(), GetLng());
}

switch($action){
	case 'removeImage':
		$returnVal = array();
		$returnVal['error'] = true;
		$returnVal['message'] = _('Error while deleting');
		
		if($controller->LoggedIn())
		{
			$picNameBasic = 'pic_' . $controller->GetUid() . '_' . $_REQUEST['imageID'] . '.jpg';

			if (unlink(TEMP_ITEM_PICS_PATH.$picNameBasic)){
				$returnVal['error'] = false;
				$returnVal['message'] = _('Image removed');
			}


		}

		echo json_encode($returnVal);
	break;
	case 'previouslyAddedImages':
		$returnVal['error'] = true;
		if($controller->LoggedIn()){

			for($i = 0; $i < 5; $i++){

				$picNameBasic = 'pic_'.$controller->GetUid().'_'.$i.'.jpg';

				if(file_exists(TEMP_ITEM_PICS_PATH.$picNameBasic)){

					$returnVal['images'][] = array(
						'name' => 	$picNameBasic,
						'size' =>   filesize(TEMP_ITEM_PICS_PATH.$picNameBasic),
						'url'  =>   $view->GetSiteAddress().TEMP_ITEM_PICS_PATH_RELATIVE.$picNameBasic,
						'removeUrl'  =>   $view->GetSiteAddress().'/additem.php?action=removeImage&imageID='.$i
					);
				}
			}
		}



		echo json_encode($returnVal);
	break;
	case 'uploadImage':
		$returnVal['error'] = true;
		if($controller->LoggedIn()){

			$picName = '';
            $tempImageID = 0;
			for($i = 0; $i < 5; $i++){

				$picNameBasic = 'pic_'.$controller->GetUid().'_'.$i.'.jpg';
				if(!file_exists(TEMP_ITEM_PICS_PATH.$picNameBasic)){

					$picName = $picNameBasic;
                    $tempImageID = $i;
					break;
				}
			}

			if(empty($picName)){

				$fileTime = time();
				$picName = '';

				for($i = 0; $i < 5; $i++){

					$picNameBasic = 'pic_'.$controller->GetUid().'_'.$i.'.jpg';

					if($fileTime > filemtime(TEMP_ITEM_PICS_PATH.$picNameBasic)){

						$fileTime = filemtime(TEMP_ITEM_PICS_PATH.$picNameBasic);
						$picName = $picNameBasic;

					}
				}
			}
			$dir = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'tempimages'.DIRECTORY_SEPARATOR;

			
			if(move_uploaded_file($_FILES['uploadedfile']["tmp_name"], TEMP_ITEM_PICS_PATH.$picName)){
				
				$returnVal['error'] = false;
				$returnVal['removeUrl'] = '/additem.php?action=removeImage&imageID='.$tempImageID;
			}else{

				$logger->DLog(0,
					__FUNCTION__,
					__CLASS__,
					func_get_args(),
					$_SERVER['PHP_SELF'],
					$_SERVER['QUERY_STRING'],
					'Error: Kullanicinin yukledigi resim dosyasi tmp_itempics dizinine tasinamadi. Hedef dosyanin adi:' .(TEMP_ITEM_PICS_PATH.$picName));
			}
		}
		echo json_encode($returnVal);
	break;
	default:

        require_once('view_funcs/pages/CAddItemPageTemplateValues.php');
        $newItemDataReceived = false;
        $itemAddedSuccesfully = false;

        $baseTemplateInputs->currentPage->addItem = true;

        /** @var   SAddItemFormErrors */
        $errorCauses = new SAddItemFormErrors();


        if((isset($_REQUEST['formapproved'])
            && !empty($_REQUEST['formapproved'])
            && $_REQUEST['formapproved'] == 'formapproved'))
        {
            $newItemDataReceived = true;

            $files = array();
            for($i = 0; $i < 5; $i++){

                $picNameBasic = 'pic_'.$controller->GetUid().'_'.$i.'.jpg';

                if(file_exists(TEMP_ITEM_PICS_PATH.$picNameBasic)){

                    $files['name'][] = $picNameBasic;
                    $files['type'][] = 'image/jpeg';
                    $files['tmp_name'][] = TEMP_ITEM_PICS_PATH.$picNameBasic;
                    $files['error'][] = UPLOAD_ERR_OK;
                    $files['size'][] = filesize(TEMP_ITEM_PICS_PATH.$picNameBasic);

                }
            }

            $addItemResult = $controller->RunAction($files);

            if(false === $addItemResult['error']){

                $itemAddedSuccesfully = true;
                header( "Location: /item/".$addItemResult['iid']);

            }else{

                $errorMessage = $addItemResult['message'];
                $errorCauses  = $addItemResult['errors'];
            }
        }


        /*
         * Bu kısıma gelindiyse ürün ekle ya yapılmamış, ya da başarısızlıkla sonuçlanmış demektir.
         * Gerekli template girdileri verilerek sayfa çıktısı verilmelidir.
         */
        if(false === $newItemDataReceived || false === $itemAddedSuccesfully){



            $baseTemplateInputs->headerMessage = _('Add Item Form');

            if(true == $isLoggedIn){
                require_once(BASE_PATH.'/web/adapters/CNewsAdapter.php');

                $user = new CUser($controller->GetUid());

                $baseTemplateInputs->news = CNewsAdapter::SAdaptNews($user->GetNews(30));

                $DIContainer = new CContainer();
                $db = $DIContainer->GetDBService();
                $baseTemplateInputs->notifications = CUser::SGetUnreadNewsCount($controller->GetUid(), $db);
            }

            $addItemPageTemplateInputs = new SAddItemPageTemplateInputs($baseTemplateInputs);

            $addItemPageTemplateInputs->action = $action;
            $addItemPageTemplateInputs->isNewItemInfoExist = $newItemDataReceived;

            if(true === $newItemDataReceived){

                $addItemPageTemplateInputs->isNewItemInfoExist = true;
                $addItemPageTemplateInputs->isError = true;
                $addItemPageTemplateInputs->errorMessage = $errorMessage;

                $addItemPageTemplateInputs->newItemInfo->title       = $_REQUEST['header'];
                $addItemPageTemplateInputs->newItemInfo->description = $_REQUEST['description'];
                $addItemPageTemplateInputs->newItemInfo->price       = isset($_REQUEST['price'])?$_REQUEST['price']:'';
                $addItemPageTemplateInputs->newItemInfo->priceType   = isset($_REQUEST['priceType'])?$_REQUEST['priceType']:'';
                $addItemPageTemplateInputs->newItemInfo->amount      = $_REQUEST['amount'];
                $addItemPageTemplateInputs->newItemInfo->cat         = isset($_REQUEST['category'])?$_REQUEST['category']:'0';


                $addItemPageTemplateInputs->errorCauses->images        = $errorCauses->images;
                $addItemPageTemplateInputs->errorCauses->amount        = $errorCauses->amount;
                $addItemPageTemplateInputs->errorCauses->category      = $errorCauses->category;
                $addItemPageTemplateInputs->errorCauses->description   = $errorCauses->description;
                $addItemPageTemplateInputs->errorCauses->price         = $errorCauses->price;
                $addItemPageTemplateInputs->errorCauses->priceType     = $errorCauses->priceType;
                $addItemPageTemplateInputs->errorCauses->name          = $errorCauses->name;
                $addItemPageTemplateInputs->errorCauses->other         = $errorCauses->other;



            }

            $pageTemplate = new CAddItemPageTemplateValues($addItemPageTemplateInputs);
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






