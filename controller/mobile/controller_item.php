<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/items/class_items.php');
require_once(BASE_PATH.'/model/log/logger.php');

class CItemController extends CBaseController
{
	private $action;
	
	private $iid;
	
	function __construct(array $request, array $dependicies = array()){
	
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');
		$this->iid = $this->GetRequest('iid');
	

	}
	function RunAction(){
	
		$returnVal = array();
		$returnVal['error'] = true;
		$item = new CItems($this->iid, array('db' => $this->db));
		
		
		switch($this->action){
			
			case "removeitem":
			case "removeItem":
				if($this->LoggedIn()){
					require_once(BASE_PATH.'/model/items/class_item_control.php');

					$itemControl = new CItemControl($this->iid, $this->uid, array('db', $this->db));
					$removeResult = $itemControl->RemoveItem();
					$returnVal['error'] = $removeResult['error'];
					$returnVal['message'] = $removeResult['message'];

				}else{
					
					$logger = CLogger::GetLogger();
					
					$logger->Log($this->uid,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							6,
							'Unauthorized user to remove item: '. $this->iid);
				}
			break;
			default:
				
				if(is_numeric($this->iid)){
					
					$itemData = $item->GetData(array('header, amount, description, price, priceType, addtime'));
	
					$itemPics = $item->GetPics();
					if($itemData){
						$returnVal['error'] = false;
						require_once(BASE_PATH.'/model/users/class_user.php');
						
						$container = new CContainer();
						$db = $container->GetDBService(true);
						
						$itemOwner = new CUser($item->GetOwnerID(), array('db' => $db));
						
						$returnVal = array('code'=>1, 'iid'=>$this->iid, 'location' => $itemOwner->GetLocation(), 
										   'amount'=>$itemData['amount'], 'header'=>$itemData['header'], 'itempic'=>$item->GetMainPic(), 
										   'itempics' => $itemPics,  'description'=>$itemData['description'], 'price'=>$item->GetPriceStr(),
										   'itemownerid'=>  $item->GetOwnerID(), 'itemownername'=>  $itemOwner->GetUsername(), 
											'ownerpic' => $itemOwner->GetPic(),
										   'adddate' => CMisc::TimeDiffToString($itemData['addtime']));
					}
				}
			
			
		}
		return $returnVal;
		
	}
	
	
}