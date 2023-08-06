<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');

class CSearchController extends CBaseController
{
	private $action;
	
	
	function __construct(array $request,  array $dependicies = array()){
		
		parent::__construct($request, $dependicies);
	
		$this->action   = $this->GetRequest('action');
		
	

	}
	function RunAction(){
		require_once(BASE_PATH.'/model/users/class_user.php');
	
		$returnVal = array();
		$search     = $this->GetRequest('term');
		$startAt     = $this->GetRequest('start');
		
		$DIContainer = new CContainer();
		$db = $DIContainer->GetDBService(true);
		
		$searchingUser = new CUser($this->uid, array('db' => $db));
		switch($this->action){
			case "user":
				
				require_once(BASE_PATH.'/model/search/class_user_search.php');
				
				$searcher = new CUserSearch($search, array('db' => $this->db));
				$searcher->SetSearchingUser($searchingUser);
				
				$returnVal['users'] = $searcher->Search(array('start' => $startAt));
				$returnVal['count'] = count($returnVal['users']);
				
			break;
			case "item":
				
				require_once(BASE_PATH.'/model/search/class_item_search.php');
				
				$searcher = new CItemSearch($search, array('db' => $this->db));
				$returnVal['items'] = $searcher->Search(array('start'=> $startAt));
				$returnVal['count'] = count($returnVal['items']);
			break;
			case "group":
				
			break;
			default:
				
				
			
		}
		return $returnVal;
		
	}
	
	
}