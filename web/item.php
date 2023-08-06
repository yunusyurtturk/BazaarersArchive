<?php
require_once('web/config.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/controller/mobile/controller_main_page.php');
require_once(BASE_PATH.'/controller/mobile/controller_item.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/items/class_items.php');

require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/paths/CPaths.php');

require_once('view_funcs/view.php');

/* Mustache Initialisations */
Mustache_Autoloader::register();

$mustache = new Mustache_Engine(array(
	'loader' => new Mustache_Loader_FilesystemLoader(BASE_PATH.'/web/templates'),
	'partials_loader' => new Mustache_Loader_FilesystemLoader(BASE_PATH.'/web/templates/partials')
));

$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';

$itemID = (isset($_REQUEST['iid']) && is_numeric($_REQUEST['iid']))?$_REQUEST['iid']:0;
$view = new CWebView();
$item = new CItems($itemID);


$itemPageTemplate 		  = $mustache->loadTemplate('/pages/item.mustache');

$mainPageController = new CMainPageController($_REQUEST);
$resultTemplateValues = array_merge($view->GetHeaderTemplateValues(), $view->GetTopMenuTemplateValues());
if ($mainPageController->LoggedIn()) {

	$userMenu = $view->GetLoggedInTopMenuTemplateValues();
} else {

	$userMenu = $view->GetNotLoggedInTopMenuTemplateValues();
}
$resultTemplateValues = array_merge($resultTemplateValues, $userMenu);



switch($action){
	case 'removeItem':
	case 'removeitem':

		$controller = new CItemController($_REQUEST);
		$returnVal = $controller->RunAction();

		echo json_encode($returnVal);
	break;
	default:


		if($item->GetIsExist()) {
			$images = $item->GetPics();
			$imageCount = $item->GetImageCount();
			$imageCounter = 0;


			/* Rearrange images */
			$newImages = array();
			if (isset($images) && is_array($images) && count($images) > 0) {
				$newImages[] = array('image-index-class' => 'active',
					'image' => $images[0],
					'image-alt' => '');

				for ($counter = 1; $counter < $imageCount; $counter++) {

					$newImages[] = array('image-index-class' => '',
						'image' => $images[$counter],
						'image-alt' => '');

				}
			}


			/* Image Indicators for Colosual */
			$imageIndicators = array();
			$imageIndicators[] = array('image-index' => 0, 'image-index-class' => 'active');

			for ($counter = 1; $counter < $imageCount; $counter++) {
				$imageIndicators[] = array('image-index' => $counter, 'image-index-class' => '');
			}

			$ownerLocation = CUser::SGetLocation($item->GetOwnerID());
			
			
			$itemTemplateValues = array(
			    'item-id' => $itemID,
				'title' => $item->GetTitle(),
				'description' => nl2br($item->GetDescription()),

				'item-image-indicators' => $imageIndicators,
				'images' => $newImages,

				'send-message-button-text' => _('Contact Seller'),


				'itemowner-translate' => _('Seller'),
				'addtime-translate' => _('Added'),
				'count-translate' => _('Amount'),
				'price-translate' => _('Price'),
				'user-id' => $item->GetOwnerID(),
				'user-name' => $item->GetOwnerName(),
				'time' => CMisc::TimeDiffToString($item->GetAddTime()),
				'count' => $imageCount,
				'price' => $item->GetPriceStr()
			);

            if($mainPageController->LoggedIn()){

                require_once(BASE_PATH.'/model/trade/class_item_trade.php');
                $DIContainer = new CContainer();
                $db = $DIContainer->GetDBService(true);
                if(CItemTrade::SIsPreviouslyMessaged($mainPageController->GetUid(), $itemID, $db)){

                    $resultTemplateValues['previously-messaged'] = true;
                    $resultTemplateValues['itemmessages-url-text'] = _('See message history');
                    $resultTemplateValues['itemmessages-url'] = '/itemmessages';

                }


                if($mainPageController->GetUid() == $item->GetOwnerID()) {

                    $editCode = 'afdhhjy4';
                    $itemTemplateValues['edit-item-info-target-url'] = ITEM_EDIT_PATH . '?iid=' . $itemID . '&editcode=' . $editCode;
                }
            }

			if(false != $ownerLocation){

				if($ownerLocation->IsValid()){

					$itemTemplateValues['item-location-image-url']
						= 'https://maps.googleapis.com/maps/api/staticmap?zoom=12&size=512x256&format=jpg&maptype=roadmap&center='.$ownerLocation->getLat().','.$ownerLocation->getLng().'&key=SECRET_KEY';
				}
			}


			$resultTemplateValues['itemmessage-form-textarea-label'] = _('Message:');
			$resultTemplateValues['itemmessage-form-textarea-name'] = 'message';

			$resultTemplateValues['itemmessage-form-iid-value'] = $itemID;
			$resultTemplateValues['itemmessage-form-iid-name'] = 'iid';

			$resultTemplateValues['itemmessage-form-conversation-value'] = 0;
			$resultTemplateValues['itemmessage-form-conversation-name'] = 'imsgrsid';

			$resultTemplateValues['itemmessage-send-form-button-text'] = _('Send');
			$resultTemplateValues['itemmessage-send-form-action'] = '/oop/web/itemmessages.php?action=send';

			$resultTemplateValues['whatsapp-share-url'] = '';
			$resultTemplateValues['facebook-share-url'] = 'http://www.facebook.com/sharer/sharer.php?u=www.bazaarers.com/item/'.$itemID;
			$resultTemplateValues['twitter-share-url'] = "https://twitter.com/intent/tweet?text=" . _('Check the item '.($itemTemplateValues['title']).' at @Bazaarers: www.bazaarers.com/item/'.$itemID) . "&via=Bazaarers";

			$resultTemplateValues['whatsapp-icon'] = '/oop/web/images/whatsapp_64.png';
			$resultTemplateValues['twitter-icon'] = '/oop/web/images/twitter_64.png';
			$resultTemplateValues['facebook-icon']  = '/oop/web/images/facebook_64.png';

			$resultTemplateValues = array_merge($resultTemplateValues, $itemTemplateValues);
			$resultTemplateValues = array_merge($resultTemplateValues, array('templates' => array(
				array('template-id' => 'item-in-list-template',
					'template' => $view::LoadTemplate(BASE_PATH . '/web/templates/partials/item-in-list')),
				array('template-id' => 'item-in-list-template',
					'template' => $view::LoadTemplate(BASE_PATH . '/web/templates/partials/itemmessage')),
				array('template-id' => 'user-in-list-template',
					'template' => $view::LoadTemplate(BASE_PATH . '/web/templates/partials/header')))));

			if ($mainPageController->LoggedIn()){

				$resultTemplateValues['logged-in'] = true;

				if ($_SESSION['uid'] == $item->GetOwnerID()) {

					$resultTemplateValues['is-item-owner'] = true;
					$resultTemplateValues['remove-item-url'] = '/oop/web/item.php?action=removeItem&iid=' . $itemID;
					$resultTemplateValues['remove-item-text'] = _('Remove this item');
				}
			}else{

				$resultTemplateValues['not-logged-in-message'] = _('You must log in to send message');
			}

			/* Urun sahibinin diger urunleri */
			$itemOwner = new CUser($item->GetOwnerID());

			$userItems = $itemOwner->GetItemsRandomly(3, $itemID);
			if(is_array($userItems) && count($userItems) > 0) {

				$resultTemplateValues['other-items-of-user'] = &$userItems;
			}else{
				$resultTemplateValues['other-items-of-user']['error-message'] = _('User doesn\'t have any more items');
			}
			$resultTemplateValues['other-items-of-user-text'] = _('Other item\'s of the user');
			/* **************************** */

			/* Urun sahibi hakkinda */
			$resultTemplateValues['ownername'] = _('About item owner');
			$resultTemplateValues['ownerpic']  = USER_PIC_PATH.$itemOwner->GetPic();
			$resultTemplateValues['ownerinfo'] = $itemOwner->GetUserAbout();
			$resultTemplateValues['owner-register-time'] = $itemOwner->GetRegisterDate();


		}else{
			$resultTemplateValues['item-not-exist'] = _('true');
			$resultTemplateValues['error-message'] = _('Item not exist');
		}

		/* SITE BASLIYOR */

		echo

		$itemPageTemplate->render($resultTemplateValues);
}








