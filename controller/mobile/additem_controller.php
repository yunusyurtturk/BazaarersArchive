<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/items/class_new_item.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/log/logger.php');

class CAddNewItemController extends CBaseController
{
	private $action;
	
	
	function __construct(array $request, array $dependicies = array()){
	
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');

		
	}
	function RunAction(array &$uploadedFiles = array()){

		$returnVal = array();
		$returnVal['error'] = true;
		$user = new CUser($this->uid);
		switch($this->action){
			
			
			/*
			 *  @input 
			 * 
			 */
			case "additem":
			default:
				if($this->LoggedIn()) {
					$returnVal['error'] = true;
					CMisc::BufferOn();
					$itemName = $this->GetRequest('itemName');
					$formapproved = $this->GetRequest('formapproved');
					$category = $this->GetRequest('category');
					$header = $this->GetRequest('header');
					$description = $this->GetRequest('description');
					$priceType = $this->GetRequest('priceType');
					$price = $this->GetRequest('price');
					$mainpic = $this->GetRequest('mainpic');

					$amount = $this->GetRequest('amount');
					//$files = (!empty($this->GetRequest('uploadedfile'))?$this->GetRequest('uploadedfile'):array());

                    $logger = CLogger::GetLogger();

					$newItemInfo = new CNewItemInfo($header, $description, $category, $mainpic,
						$amount, $this->uid, time(), $price, $priceType, 0,
						$uploadedFiles);

                    $addItemErrors = new SAddItemFormErrors();


                    $newItem = new CNewItem($newItemInfo, array('db' => $this->db));

                    $result = $newItem->AddItem($addItemErrors);
                    if ($result['isAdded'] == true) {
                        $returnVal['error'] = false;

                        require_once(BASE_PATH . '/model/items/class_items.php');
                        $newAddedItem = new CItems($result['iid'], array('db' => $this->db));

                        $returnVal['iid'] = $result['iid'];
                        $returnVal['header'] = $newAddedItem->GetTitle();
                        $returnVal['description'] = $newAddedItem->GetDescription();
                        $returnVal['itempic'] = $newAddedItem->GetMainPic();
                        $returnVal['linkStr'] = CMisc::StringToURL($returnVal['header']);

                        require_once(BASE_PATH.'/model/news/class_user_news.php');
                        require_once(BASE_PATH.'/model/news/class_news_defs.php');

                        $user = new CUser($this->uid, array('db'=> $this->db));
                        $userFriends = $user->GetFollowerIDs();

                        $news = new CUserNews(0, $this->uid, new CBasicNewsOptions(0, 0, 0), array('db'=> $this->db));
                        $news->BulkAdd($userFriends, NEWS_TYPE_NEW_ITEM_BY_FRIEND, array($this->uid, $result['iid']), array(NEWS_PARAM_TYPE_USER, NEWS_PARAM_TYPE_ITEM));


                    } else {

                        $returnVal['error'] = true;
                        $returnVal['message'] = $result['errorDescription'];
                        $addItemErrors->other = true;
                        $returnVal['errors'] = $addItemErrors;
                    }

                    CMisc::BufferOff(false);

				}else{
					$returnVal['error'] = true;
					$returnVal['message'] = _('Not Signed In');
				}
				
			
		}
		return $returnVal;
		
	}
	
	
}