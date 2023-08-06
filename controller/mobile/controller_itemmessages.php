<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/trade/class_item_messages.php');

class CItemMessagesController extends CBaseController
{
	private $action;
	private $iid;
	
	
	function __construct(array $request, array $dependicies = array()){
		
		parent::__construct($request, $dependicies);
		$this->action   = $this->GetRequest('action');

	}
	function RunAction(){
		
		require_once(BASE_PATH.'/model/trade/class_item_trade.php');

		$returnVal = array();
		$returnVal['error'] = true;
		switch($this->action){
			case 'reportConversation':
				$reason   = $this->GetRequest('reason');
				$imsgrsid = $this->GetRequest('imsgrsid');
				if($this->LoggedIn()){

					$itemMessages = new CItemMessages($imsgrsid, 0, $this->uid, array('db' => $this->db));
					$returnVal = $itemMessages->Report($reason);
				}else{
					
				}


			break;
			case "itemmessages":
				$messageWay= $this->GetRequest('messageway');
				require_once(BASE_PATH.'/model/users/class_user.php');
				
				$user = new CUser($this->uid, array('db' => $this->db));
				$returnVal = $user->GetLastItemMessages($messageWay, 0, 40);
	        break;
			default:
				

		        switch($this->action){
					case "agreement":
						if(null != $this->GetRequest('imsgrsid')){
							$iid = $this->GetRequest('iid');
							$imsgrsid=$this->GetRequest('imsgrsid');
							
							$this->iid = $iid;
							$itemTrade = new CItemTrade($imsgrsid, $this->uid,  array('db' => $this->db));

							$agreementResult = $itemTrade->ToggleAgreement();

							if(isset($agreementResult['error']) &&  false === $agreementResult['error']){
								$returnVal['error'] = false;
							}
						}
					break;
					case "exchanged":
						if(null != $this->GetRequest('imsgrsid')){
							$iid = $this->GetRequest('iid');
							$imsgrsid=$this->GetRequest('imsgrsid');
							
							$itemTrade = new CItemTrade($imsgrsid, $this->uid,  array('db' => $this->db));
							if($itemTrade->ToggleExchange()){
								
								$returnVal['error'] = false;
								require_once(BASE_PATH.'/model/news/class_user_news.php');
								require_once(BASE_PATH.'/model/news/class_news_defs.php');
								
								$news = new CUserNews(0, $this->uid, new CBasicNewsOptions(0, 0, 0), array('db'=> $this->db)); 
								if($itemTrade->IsItemExchangedPreviously()){ // Alisveris gerceklesti

									$news->Add($itemTrade->GetDesirerID(), NEWS_TYPE_ITEM_EXCHANGED_INFORM_DESIRER, array($iid, $itemTrade->GetItemOwnerID()), array(NEWS_PARAM_TYPE_ITEM, NEWS_PARAM_TYPE_USER));
									$news->Add($itemTrade->GetItemOwnerID(), NEWS_TYPE_ITEM_EXCHANGED_INFORM_ITEMOWNER, array($iid, $itemTrade->GetDesirerID()), array(NEWS_PARAM_TYPE_ITEM, NEWS_PARAM_TYPE_USER));
								}else{// Sadece bir taraf onayladi, karsi tarafa haber gonder
									
									
									if($this->uid === $itemTrade->GetDesirerID()){ //Desirer onayladi, karsi taraf bekleniyor
										// [yy]
									}else{
										
									}
								}
							}
						}
					break;
					default:
						
						$iid = $this->GetRequest('iid');
						
						if(null != $this->GetRequest('imsgrsid')){
							$imsgrsid=$this->GetRequest('imsgrsid');
						}else{
							$imsgrsid = 0;
						}
						
						$itemMessages = new CItemMessages($imsgrsid, $iid, $this->uid, array('db' => $this->db));
						if(0 == $imsgrsid){
							
							$itemMessages->SetItemID($iid);
						}
						
						if(!isset($iid) || empty($iid) || 0 == $iid){
							$iid = $itemMessages->GetItemID();
						}
						
						switch($this->action){
							case "send":
								//CMisc::BufferOn();
								$message= $this->GetRequest('message');
								$returnVal['error'] = true;
								if(0 == $imsgrsid){
									$this->iid = $this->GetRequest('iid');
									$itemMessages->SetItemID($this->iid);
								}
								
								$sendResult = $itemMessages->Send($message);

								if(false == $sendResult['error']){

									$returnVal['error'] = false;
									$returnVal['message'] = _('Message sent');

									require_once(BASE_PATH.'/model/users/class_user.php');

									$receiver = new CUser($itemMessages->GetInterlocutor(), array('db' => $this->db));
									$receiverMail = $receiver->GetEmail();

									$sender = new CUser($this->uid, array('db' => $this->db));
									$returnVal['senderName'] = $sender->GetUsername();
									$returnVal['sendDate'] = time();
									$returnVal['isFirstMessage'] = $sendResult['isFirstMessage'];
									if(true == $returnVal['isFirstMessage']){

										$returnVal['newImsgrsid'] = $sendResult['newImsgrsid'];
									}

									/* Send an email */
									require_once(BASE_PATH.'/model/email/class_email.php');

									$mail = new CEmail();

									$mail->SetTo($receiverMail);
									$mail->SetSubject($mail->GetSubject(MAIL_CONTENT_NEW_ITEM_MESSAGES));
									$mail->SetContent($mail->GetContent(MAIL_CONTENT_NEW_ITEM_MESSAGES,
										array('username' => $receiver->GetUsername(), 'itemName' => CItems::SGetItemName($itemMessages->GetItemID())  , 'interlocutor' => $sender->GetUsername()))
									);
									$mail->Send(); 

									/* Mesaji alan kullaniciya haberi gonder */
									require_once(BASE_PATH.'/model/news/class_user_news.php');
									require_once(BASE_PATH.'/model/news/class_news_defs.php');

									$news = new CUserNews(0, $this->uid, new CBasicNewsOptions(0, 0, 0), array('db'=> $this->db));
									$newsAddResult = $news->Add($itemMessages->GetReceiverID(), NEWS_TYPE_ITEM_MESSAGE, array($this->uid, $iid), array(NEWS_PARAM_TYPE_USER, NEWS_PARAM_TYPE_ITEM));
									$returnVal['nid'] = $newsAddResult['nid'];
									if(false === $newsAddResult['error']){

									}else{
										$returnVal['errCode'] = $newsAddResult['errCode'] ;
									}
								}else{

									$returnVal['errCode'] = $sendResult['errCode'];
									$returnVal['error'] = true;
								}

								//CMisc::BufferOff();

							break;
							case "read":
							default:
								$returnVal = $itemMessages->Read();
						}
		        }
				
				
				
		}
		
		return $returnVal;
		
	}
}
