<?php
require_once('web/config.php');

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/controller/mobile/controller_itemmessages.php');
require_once(BASE_PATH.'/controller/mobile/controller_main_page.php');
require_once(BASE_PATH.'/controller/mobile/controller_homescreen.php');

require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/items/class_items.php');


require_once('view_funcs/view.php');

require_once(BASE_PATH.'/web/view_funcs/CTemplate.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateParams.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateHolder.php');

require_once('view_funcs/pages/CItemMessagesPageTemplateValues.php');

$uid = (isset($_REQUEST['uid']) && is_numeric($_REQUEST['uid']))?$_REQUEST['uid']:0;
$action = isset($_REQUEST['action'])?$_REQUEST['action']:'';
$view = new CWebView();

$templateEngine = new CTemplate();


$mainPageController = new CMainPageController($_REQUEST);
$isLoggedIn = $mainPageController->LoggedIn();

$viewTemplateInputs = new SViewBaseTemplateInputs();

$viewTemplateInputs->loggedIn  =  $isLoggedIn;
$viewTemplateInputs->isLocated =  $mainPageController->IsLocated();

if($viewTemplateInputs->isLocated) {
    $viewTemplateInputs->location = new CLocation($mainPageController->GetLat(), $mainPageController->GetLng());
}

if(true == $isLoggedIn){
    require_once(BASE_PATH.'/web/adapters/CNewsAdapter.php');
    $viewTemplateInputs->news = CNewsAdapter::SAdaptNews($mainPageController->GetNews(30));

    $DIContainer = new CContainer();
    $db = $DIContainer->GetDBService();

    $viewTemplateInputs->notifications = CUser::SGetUnreadNewsCount($mainPageController->GetUid(), $db);
}


$itemMessagesTemplateInputs = new SItemMessagesPageTemplateInputs($viewTemplateInputs);








if($isLoggedIn) {

	$returnVal['error'] = true;

	switch($action) {
		case 'reportConversation':
			$controller = new CItemMessagesController($_REQUEST);

			$result = $controller->RunAction();
			echo json_encode($result);
		break;
		case 'send':
			$controller = new CItemMessagesController($_REQUEST);

			$result = $controller->RunAction();
			echo json_encode($result);
	
		break;
		case 'exchanged':
		case 'agreement':
			$controller = new CItemMessagesController($_REQUEST);

			$result = $controller->RunAction();
			echo json_encode($result);
		break;
		case 'read':
			$controller = new CItemMessagesController($_REQUEST);

			$result = $controller->RunAction();

			$result['desirer-agreement-input-name'] = 'desireragreed';
			$result['checkbox-desirer-agreed-text'] = _('Deal');
			$result['owner-agreement-input-name'] = 'owneragreed';
			$result['checkbox-desirer-agreed-text'] = _('Deal');

			$result['desirer-got-input-name'] = '';
			$result['desirer-got-text'] = _('I got the item');
			$result['owner-gave-input-name'] ='';
			$result['owner-gave-text'] = _('I gave the item');

			$result['agreement-toggle-link'] = '/oop/web/itemmessages.php?action=agreement&imsgrsid='.$result['imsgrsid'].'&iid='.$result['iid'];
			$result['exchange-toggle-link']  = '/oop/web/itemmessages.php?action=exchanged&imsgrsid='.$result['imsgrsid'].'&iid='.$result['iid'];

			echo json_encode($result);

		break;
		case 'itemmessages':
		default:
			$result = array();

            $primaryTemplate = (!isset($primaryTemplate))?$templateEngine->LoadTemplate('/pages/itemmessages.mustache'):$primaryTemplate;

            /* INBOX */

			$_REQUEST['messageway'] = 'inbox';
			$_REQUEST['action'] 	 = 'itemmessages';
			$controller = new CItemMessagesController($_REQUEST);

			$inboxResult =  $controller->RunAction();

			if(isset($inboxResult['messages'])) {


				$inboxResult['has-messages'] = true;

				array_walk($inboxResult['messages'], function (& $messages) {

					$messages['time'] = Cmisc::TimeDiffToString($messages['time']);
					$messages['senderpic'] = '/userpics/'.$messages['senderpic'];
					$messages['itempic'] = '/itempics/'.$messages['itempic'];
				});

                $itemMessagesTemplateInputs->inbox->messages = &$inboxResult;
			}else{

                $itemMessagesTemplateInputs->inbox->messages = null;
			}

			/* OUTBOX */
			$_REQUEST['messageway'] = 'outbox';
			$controller = new CItemMessagesController($_REQUEST);
			$outboxResult =  $controller->RunAction();
			if(isset($outboxResult['messages'])) {

				$outboxResult['has-messages'] = true;
				$result['has-messages'] = true;

				array_walk($outboxResult['messages'], function (& $messages) {

					$messages['time'] = Cmisc::TimeDiffToString($messages['time']);
					$messages['senderpic'] = '/userpics/'.$messages['senderpic'];
					$messages['itempic'] = '/itempics/'.$messages['itempic'];
				});

                $itemMessagesTemplateInputs->outbox->messages = &$outboxResult;
			}else{

                $itemMessagesTemplateInputs->outbox->messages = null;

			}
			$result['outbox'] = &$outboxResult;



//		$result['itempath'] = '/item';
//		$result['item-messages-inbox-name'] = _('Inbox');
//		$result['item-messages-outbox-name'] = _('Sent');
//		$result['item-messages-select-message-message'] = _('Select a message to see conservation');
//		$result['conversation-display-url'] = '/oop/web/itemmessages.php?action=read';
//		$result['itemmessages-header-select-message-text'] = _('Select a conversation');
//		$result['itemmessages-header-text'] = _('Conversation Archive');

//		$result['desirer-agreement-input-name'] = 'desireragreed';
//		$result['checkbox-desirer-agreed-text'] = _('Deal');
//		$result['owner-agreement-input-name'] = 'owneragreed';
//		$result['checkbox-desirer-agreed-text'] = _('Deal');

//		$result['desirer-got-input-name'] = '';
//		$result['desirer-got-text'] = _('I got the item');
//		$result['owner-gave-input-name'] ='';
//		$result['owner-gave-text'] = _('I gave the item');

//		$result['itemmessage-form-textarea-label'] = _('Message:');
//		$result['itemmessage-form-textarea-name'] = 'message';

//		$result['itemmessage-form-iid-value'] = 0;
//		$result['itemmessage-form-iid-name'] = 'iid';

//		$result['itemmessage-form-conversation-value'] = 0;
//		$result['itemmessage-form-conversation-name'] = 'imsgrsid';
			
//		$result['itemmessage-send-form-button-text'] = _('Send');
//		$result['itemmessage-send-form-action'] = '/oop/web/itemmessages.php?action=send';

//		$result['report-conversation-action'] = //;
//		$result['report-conversation-conversation-reason-input-name'] = 'reason';
//		$result['report-conversation-conversation-id-input-name'] = 'imsgrsid';


        $pageTemplate = new CItemMessagesTemplateValues($itemMessagesTemplateInputs);

        echo $templateEngine->Render($primaryTemplate, $pageTemplate->GetValues());
		break;

	}


}





