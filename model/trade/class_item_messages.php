<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');
require_once(BASE_PATH.'/model/items/class_items.php');

class CItemMessages extends CModelBaseWithDB
{
	private $iid;
	private $imsgrsid;
	private $uid;
	private $desirer_id;
	private $itemowner_id;
	private $module_table_name ='itemmessagers';
	private $id_alias = 'imsgrsid';

	function __construct($imsgrsid = 0, $iid = 0, $uid, array $dependicies = array())
	{

		parent::__construct($dependicies);

		$this->uid = $uid;
		$this->iid = $iid;
		$this->imsgrsid = $imsgrsid;
		if(0 !== $this->imsgrsid){

			$this->GetItemMessagersInfo();
		}
	}
	private function GetItemMessagersInfo(){

		$this->db->Prepare('SELECT * FROM itemmessagers WHERE '.$this->id_alias.' =:'.$this->id_alias.' LIMIT 1');
		$params[] = new CDBParam($this->id_alias, $this->imsgrsid, PDO::PARAM_INT );

		if($this->db->Execute($params)){

			if($this->db->RowCount() > 0){

				$fetch = $this->db->Fetch();
				$this->iid = $fetch['iid'];
				$this->itemowner_id = $fetch['itemowner'];
				$this->desirer_id = $fetch['desirer'];
				return $fetch;
			}

		}
		return false;

	}
	function IsPreviouslyReported()
	{
		$returnVal = true;
		$this->db->Prepare('SELECT imrid FROM itemMessageReports WHERE imsgrsid=:imsgrsid AND reporter = :reporter AND reported = :reported');
		$params[] = new CDBParam('imsgrsid', $this->imsgrsid, PDO::PARAM_INT );
		$params[] = new CDBParam('reporter', $this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('reported', $this->GetInterlocutor(), PDO::PARAM_INT );

		if($this->db->Execute($params)){
			if($this->db->RowCount() == 0){

				$returnVal = false;
			}
		}
		return $returnVal;
	}
	function Report(&$report)
	{
		$returnVal['error'] = true;
		if($this->IsPrevioslyMessaged()){

			if(!$this->IsMessagingBelongsToAnotherUser()){

				if(!$this->IsPreviouslyReported()){

					$this->db->Prepare('INSERT INTO itemMessageReports (imsgrsid, reporter, reported, report) VALUES (:imsgrsid, :reporter, :reported, :report)');
					$params[] = new CDBParam('imsgrsid', $this->imsgrsid, PDO::PARAM_INT );
					$params[] = new CDBParam('reporter', $this->uid, PDO::PARAM_INT );
					$params[] = new CDBParam('reported', $this->GetInterlocutor(), PDO::PARAM_INT );
					$params[] = new CDBParam('report',  $report, PDO::PARAM_STR );

					if($this->db->Execute($params)){
						if($this->db->RowCount() > 0){

							$returnVal['error'] = false;
						}else{

						}
					}else{

					}
				}else{

				}

			}else{

			}

		}else{

		}

		return $returnVal;

	}
	function GetReceiverID()
	{
		$receiverId = 0;

		if(null == $this->itemowner_id || null == $this->desirer_id){
			$this->GetItemMessagersInfo();
		}

		if(null != $this->itemowner_id && null != $this->desirer_id){

			if($this->uid == $this->itemowner_id){
				$receiverId = $this->desirer_id;
			}else{
				$receiverId = $this->itemowner_id;
			}
		}
		return $receiverId;
	}

	function SetItemID($iid){
		$this->iid = $iid;
		if(0 == $this->imsgrsid){
			$this->db->Prepare('SELECT imsgrsid FROM itemmessagers WHERE iid=:iid AND desirer=:desirer');
			$params[] = new CDBParam('iid', $iid, PDO::PARAM_INT );
			$params[] = new CDBParam('desirer', $this->uid, PDO::PARAM_INT );
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){

					$fetch = $this->db->Fetch();
					$this->imsgrsid = $fetch['imsgrsid'];
					return true;
				}
			}
			return 0;
		}else{
			return true;
		}
		return false;
	}
	function GetItemID(){
		$this->db->Prepare('SELECT iid FROM itemmessagers WHERE imsgrsid=:imsgrsid limit 1');
		$params[] = new CDBParam('imsgrsid', $this->imsgrsid, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				$fetch = $this->db->Fetch();
				return $fetch['iid'];
			}
		}
		return 0;
	}
	private function CreateNewMessagingRecord()
	{
		$item = new CItems($this->iid, array('db' => $this->db));
		$itemOwnerID = $item->GetOwnerID();
		if($this->uid != $itemOwnerID){


			$this->db->Prepare('INSERT INTO itemmessagers (iid, itemowner, desirer) VALUES (:iid, :itemowner, :desirer)');
			$params[] = new CDBParam('iid', $this->iid, PDO::PARAM_INT );
			$params[] = new CDBParam('itemowner', $itemOwnerID, PDO::PARAM_INT );
			$params[] = new CDBParam('desirer', $this->uid, PDO::PARAM_INT );


			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){

					return $this->db->GetLastInsertID();
				}
			}

		}

		return false; // Owner cant start talking with himself!?!?
	}
	function Send($message){

		$returnVal = array();
		$returnVal['error'] = true;
		$returnVal['errCode'] = 0;
		if(empty($message)){

			$returnVal['errCode'] = 5;
		}else{

			$returnVal['message'] = '';
			$returnVal['isFirstMessage'] = false;

			if($this->IsMessageSendable()){

				if( !$this->IsPrevioslyMessaged()){
					$returnVal['errCode'] = 3;
					$returnVal['isFirstMessage'] = true;
					$this->imsgrsid = $this->CreateNewMessagingRecord();
					$returnVal['newImsgrsid'] = $this->imsgrsid;

				}else{
					$returnVal['errCode'] = 2;
				}

				if(true === $this->InsertNewMessage($message)){


					$returnVal['error'] = false;
				}else{

					$returnVal['errCode'] = 4;
				}

			}else{

				$returnVal['errCode'] = 1;
			}
		}
		return $returnVal;
	}

	function GetInterlocutor(){
		$interlocutor = ($this->uid == $this->itemowner_id)?$this->desirer_id:$this->itemowner_id;

		if(null == $interlocutor || empty($interlocutor)){
			$interlocutor = 0;
		}

		return $interlocutor;
	}
	function GetLastItemMessages($messageWay, $limitStart, $limitEnd)
	{
		$returnVal = array();
		if('inbox' == $messageWay){
			$whoseMessages = 'itemowner';
            $isRead = 'itemowner_is_read';
		}else{
			$whoseMessages = 'desirer';
            $isRead = 'desirer_is_read';
		}
		/*$query = $db->prepare("SELECT * from itemmessages,(
		 SELECT * FROM itemmessagers, lastitemmessages
		 WHERE itemmessagers.".$whose_messages." = :userid and itemmessagers.imsgrsid = lastitemmessages.imsgrsid
		) AS lastmessage
		WHERE itemmessages.imid = lastmessage.imid
		LIMIT :limit_start, :limit_end");*/
		$this->db->Prepare("SELECT * from itemmessages,(
	                              SELECT imid, itemmessagers.iid, itemowner, desirer, owneragreed, desireragreed,".$isRead." FROM itemmessagers, lastitemmessages
	                              WHERE itemmessagers.".$whoseMessages." = :userid and itemmessagers.imsgrsid = lastitemmessages.imsgrsid
	                          ) AS lastmessage
	                          WHERE lastmessage.imid = itemmessages.imid
	                          ORDER BY itemmessages.imid DESC
	                          LIMIT :limit_start, :limit_end");
		$params[] = new CDBParam('userid', $this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('limit_start', (int)$limitStart, PDO::PARAM_INT );
		$params[] = new CDBParam('limit_end', (int) $limitEnd, PDO::PARAM_INT );


		if($this->db->Execute($params)){
			$returnVal['count'] = $this->db->RowCount();
			if($this->db->RowCount() > 0){
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				$db2 = $DIContainer->GetDBService(true);

				while($fetch = $this->db->Fetch())
				{
					$interlocutor = ($this->uid == $fetch['itemowner'])?$fetch['desirer']:$fetch['itemowner'];
					$user = new CUser($interlocutor, array('db' => $db));
					$item = new CItems($fetch['iid'], array('db' => $db2));

					$returnVal['messages'][] = array('imsgrsid'   =>$fetch['imsgrsid'],
							'iid'          => $fetch['iid'],
							'owner'        => $fetch['itemowner'],
							'desirer'      => $fetch['desirer'],
							'owneragreed'  => $fetch['owneragreed'],
							'desireragreed'=> $fetch['desireragreed'],
							'isread'       => $fetch[$isRead],
							'time'         => $fetch['date'],
							'sender'       => $fetch['sender'],
							'itemname'     => $item->GetTitle(),
							'senderpic'    => $user->GetPic(),
							'sendername'   => $user->GetUsername(),
							'itempic'      => $item->GetMainPic(),
							'message'      => $fetch['message']);

				}
			}
		}
		return  $returnVal;
	}
	private function GetItemMessages()
	{
		$returnVal = array();
		$itemOwner = new CUser($this->itemowner_id);
		$desirer = new CUser($this->desirer_id);

		//Get the messages
		$this->db->Prepare('select * from itemmessages where imsgrsid=:imsgrsid ORDER BY imid DESC');
		$params[] = new CDBParam('imsgrsid', $this->imsgrsid, PDO::PARAM_INT );
		if($this->db->Execute($params)){

			if($this->db->RowCount() > 0){

				while($fetch = $this->db->Fetch()){


					$senderID = $fetch['sender'];
					$sender = ($senderID == $this->itemowner_id)?$itemOwner:$desirer;

					$message = stripslashes($fetch['message']);
					$date = $fetch['date'];
					$isread = $fetch['isread'];
					if($senderID == $this->uid)
						$itemway = '1';
					else
						$itemway = '2';
					$returnVal[]= array('message'=>$message, 'sender' => $senderID, 'senderName'=>$sender->GetUsername(), 'sendDate'=> date('d.m.Y', $date), 'itemMessageWay' => $itemway);

				}

			}
		}
		return $returnVal;
	}
	function Read(){
		require_once(BASE_PATH.'/model/users/class_user.php');

		$returnVal['error'] 		= false;
		if(isset($this->imsgrsid) && !empty($this->imsgrsid)){
			if($this->IsMessagingBelongsToAnotherUser()){

				$returnVal['error'] = true;
			}else{


				/* Eğer $imsgrsid yoksa, $iid ve $uid'den $imsgrsid'yi çek
				 * Bu durumda $uid  itemowner olmamalı çünkü itemowner
				 * ürünle ilgili birden çok farklı mesajlaşmaya sahip olabilir
				 * (get_itemmessagersID() fonksiyonunun içinde bu kontrol yapılıyor ve
				 * itemowner ise false döndürüyor
				 */
				if(!isset($this->imsgrsid) || 0 == $this->imsgrsid){
					$this->GetItemMessagersID($this->iid, $this->uid);
				}
				if($this->IsPrevioslyMessaged()){

					$returnVal['messages'] 		= $this->GetItemMessages();
					$item = new CItems($this->GetItemID(), array('db' => $this->db));
					$item->GetData(array('header', 'price', 'addtime', 'uid', 'amount', 'priceType'));
					$itemData = $item->GetInfo();

					$returnVal = array_merge($returnVal, $itemData);
					$returnVal['item_remained'] = ($item->GetVirtualAmount()  > 0)?true:false;
					$returnVal['iid'] = $this->GetItemID();
					$returnVal['itempic'] = $item->GetMainPic();
					$returnVal['itemPics'] = $item->GetPics();

					$itemOwner = new CUser($returnVal['uid'], array('db' => $this->db));
					$returnVal['itemownerpic'] 		 = $itemOwner->GetPic();
					$returnVal['ownername'] 		 = $itemOwner->GetUsername();
					/*
					 * Eğer imsgrsid false döndüyse henüz bir konuşma geçmemiştir aralarında. Bu durumda ürün bilgilerini
					 * iid üzerinden giderek al
					 */
					if(0 == $this->imsgrsid){

						$currentUser = new CUser($this->uid, array('db' => $this->db));


						$returnVal['desirer'] = $this->uid;
						$returnVal['desirerName'] = $currentUser->GetUsername();
						$returnVal['desirerpic'] = $currentUser->GetPic();



					}else{

						$itemMessagers = $this->GetItemMessagersInfo();
						$returnVal = array_merge($returnVal, $itemMessagers);
						$returnVal['imsgrsid'] = $this->imsgrsid;
						$returnVal['price'] = $item->GetPriceStr();
						$returnVal['priceStr'] = $item->GetPriceStr();
						$returnVal['amount'] = $item->GetCount();
						$returnVal = array_merge($returnVal, $item->GetInfo());

						$desirer = new CUser($itemMessagers['desirer'], array('db' => $this->db));
						$returnVal['desirer'] = $itemMessagers['desirer'];
						$returnVal['desirerpic'] = $desirer->GetPic();
						$returnVal['desirerName'] = $desirer->GetUsername();

						require_once(BASE_PATH.'/model/trade/class_item_trade.php');
						$itemTrade = new CItemTrade($this->imsgrsid, $this->uid,  array('db', $this->db));
						$returnVal['agreementStatus'] = $itemTrade->GetTradingStates();
						$returnVal['checkboxStatus'] = $itemTrade->GetCheckboxStatus();
						$this->SetLastMessageIsRead();

						if($this->IsPreviouslyReported()){

							$returnVal['previouslyReported'] = true;
							$returnVal['previouslyReportedText'] = _('Reported');
						}


					}
				}

			}
		}else{
			$item = new CItems($this->iid, array('db' => $this->db));
			$item->GetData(array('header', 'price', 'addtime', 'uid', 'amount', 'priceType'));
			$itemData = $item->GetInfo();



			$returnVal = array_merge($returnVal, $itemData);
			$returnVal['item_remained'] = ($item->GetVirtualAmount()  > 0)?true:false;
			$returnVal['iid'] = $this->iid;
			$returnVal['itempic'] = $item->GetMainPic();
			$returnVal['itemPics'] = $item->GetPics();

			$itemOwner = new CUser($item->GetOwnerID(), array('db' => $this->db));
			$returnVal['itemownerpic'] 		 = $itemOwner->GetPic();
			$returnVal['ownername'] 		 = $itemOwner->GetUsername();
			$returnVal['itemowner'] 		 = $itemOwner->GetUserID();


			$desirer = new CUser($this->uid, array('db' => $this->db));
			$returnVal['desirer'] 			 = $this->uid;
			$returnVal['desirerName'] 		 = $desirer->GetUsername();
			$returnVal['desirdesirererName'] = $desirer->GetUsername();
			$returnVal['desirerpic'] 		 = $desirer->GetPic();
		}
		return $returnVal;
	}
	private function GetInfoArray()
	{
		return $this->info_array;
	}
	private function InsertNewMessage($message)
	{
		$this->db->Prepare('INSERT INTO itemmessages ('.$this->id_alias.',   sender,  message,  date,  isread) 
							VALUES 				     (:'.$this->id_alias.', :sender, :message, :time, :isread)');
		$params[] = new CDBParam($this->id_alias, $this->imsgrsid, PDO::PARAM_INT );
		$params[] = new CDBParam('sender', $this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('message', $message, PDO::PARAM_STR );
		$params[] = new CDBParam('time', time(), PDO::PARAM_INT );
		$params[] = new CDBParam('isread', 0, PDO::PARAM_INT );

		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){


				if($this->UpdateLastItemMessages($this->db->GetLastInsertID())){

					return true;
				}
			}

		}

		return false;
	}
	private function UpdateLastItemMessages($lastMessageID)
	{
		$this->db->Prepare('UPDATE lastitemmessages SET imid = :imid, itemowner_is_read=0, desirer_is_read=0, sendtime=:time WHERE  imsgrsid = :imsgrsid');
		$params[] = new CDBParam($this->id_alias, $this->imsgrsid, PDO::PARAM_INT );
		$params[] = new CDBParam('imid', $lastMessageID, PDO::PARAM_INT );
		$params[] = new CDBParam('time', time(), PDO::PARAM_INT );

		if($this->db->Execute($params)){

			if($this->db->RowCount() > 0){

				return true;
			}else{

				$this->db->Prepare('INSERT INTO lastitemmessages (imid, imsgrsid, itemowner_is_read, desirer_is_read, sendtime) values (:imid, :imsgrsid, 0, 0, :time)');
				$params[] = new CDBParam($this->id_alias, $this->imsgrsid, PDO::PARAM_INT );
				$params[] = new CDBParam('imid', $lastMessageID, PDO::PARAM_INT );
				$params[] = new CDBParam('time', time(), PDO::PARAM_INT );
				if($this->db->Execute($params)){

					if($this->db->RowCount() > 0){

						return true;
					}
				}
			}
		}

		return false;
	}
	function IsPrevioslyMessaged(){

		$this->db->Prepare('select * from itemmessagers where imsgrsid =:imsgrsid and (itemowner=:uid or desirer=:uid) limit 1');
		$params[] = new CDBParam($this->id_alias, $this->imsgrsid, PDO::PARAM_INT );
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				return true;
			}
		}

		return false;
	}
	private function IsMessagingBelongsToAnotherUser(){
		$this->db->Prepare('SELECT itemowner, desirer FROM itemmessagers 
							WHERE imsgrsid =:imsgrsid  AND NOT (itemowner=:uid or desirer=:uid)  limit 1');
		$params[] = new CDBParam('imsgrsid', $this->imsgrsid, PDO::PARAM_INT );
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );

		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){

				return true; //[yy] baskasinin mesajina yetkisiz karisilmaya calisiliniyor
			}
		}
		return false;
	}
	private function IsMessageSendable()
	{
		if(0 ==$this->imsgrsid && $this->iid){

			$item = new CItems($this->iid, array('db' => $this->db));
			if($this->uid == $item->GetOwnerID()){
				return false;
			}else{

				return true;
			}
		}else{

			if(0 != $this->imsgrsid && 0 != $this->uid){

				if($this->IsMessagingBelongsToAnotherUser()){
					return false;
				}
			}

			$aboutToGiveToThisUser = false;
			$this->db->Prepare('select * from itemmessagers where iid=:iid and (itemowner=:uid or desirer=:uid) and owneragreed=1 and desireragreed=1 limit 1');
			$params[] = new CDBParam('iid', $this->iid, PDO::PARAM_INT );
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );

			if($this->db->Execute($params)){


				if($this->db->RowCount() > 0){

					$aboutToGiveToThisUser = true;
					return true;
				}
			}

			$item = new CItems($this->iid, array('db' => $this->db));
			$virtualAmount = $item->GetVirtualAmount();

			if(($virtualAmount) < 1){

				return false;
			}else{

				return true;
			}
		}
		return false;
	}
	private function IsItemSold()
	{
		$this->db->Prepare('SELECT * FROM itemmessagers, itemexchanges 
							WHERE itemmessagers.iid=:iid  AND itemmessagers.imsgrsid = itemexchanges.imsgrsid  
								 AND  itemmessagers.owneragreed=1 and itemmessagers.desireragreed=1 
								 AND (itemexchanges.is_given=0 or itemexchanges.is_taken=0)');
		$params[] = new CDBParam('iid', $this->iid, PDO::PARAM_INT );


	}
	function SetLastMessageIsRead(){

		$itemMessagers = $this->GetItemMessagersInfo();
		$col = ($this->uid == $itemMessagers['itemowner'])?'itemowner_is_read':'desirer_is_read';
		$this->db->Prepare('UPDATE lastitemmessages SET '.$col.' = 1 WHERE imsgrsid =:imsgrsid');
		$params[] = new CDBParam($this->id_alias, $this->imsgrsid, PDO::PARAM_INT );

        $this->db->EnableErrorInfo();
		if($this->db->Execute($params)){

			if($this->db->RowCount() > 0){

				return true;
			}
		}

		return false;
	}
	function SetLastItemMessage($imsgrsid, $lid){

	}
	function GetItemMessagersID($iid, $desirer_id){
		$returnVal = 0;

		$this->db->Prepare('select imsgrsid from itemmessagers where iid=:iid and desirer=:desirer limit 1');
		$params[] = new CDBParam('iid', $iid, PDO::PARAM_INT );
		$params[] = new CDBParam('desirer', $desirer_id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){

				$fetch = $this->db->Fetch();
				if(!isset($this->imsgrsid) || 0 == $this->imsgrsid){
					$this->imsgrsid = $fetch['imsgrsid'];
				}
				$returnVal = $fetch['imsgrsid'];
			}
		}
		return $returnVal;
	}


}