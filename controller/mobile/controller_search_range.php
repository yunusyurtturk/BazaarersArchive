<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/location/class_location.php');
require_once(BASE_PATH.'/model/search_range/class_search_range.php');
require_once(BASE_PATH.'/model/search_range/search_range_defs.php');
require_once(BASE_PATH.'/controller/mobile/result_code_base_defs.php');

class CSearchRangeController extends CBaseController
{
	private $action;
	
	
	private $location;
	private $radius = 30;
	
	function __construct($request, array $dependicies = array()){
		
		
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');
		$lat = $this->GetRequest('lat');
		$lng = $this->GetRequest('lng');
		
		$this->location = new CLocation($lat, $lng);
		$this->radius = ($this->GetRequest('radius'))?$this->GetRequest('radius'):$this->radius;
		
	}
	function GetLocation()
    {
        return $this->location;
    }
	function RunAction()
	{
		require_once(BASE_PATH.'/model/users/users_defs.php');

		$returnVal = array();
		$returnVal['result'] = RESULT_NO_ERROR;
		$returnVal['has_current_location'] = false;
		$returnVal['is_located'] = false;
		if($this->LoggedIn()){

			require_once(BASE_PATH.'/model/users/class_user.php');
			$user = new CUser($this->uid, array('db' => $this->db));
			if($user->GetLocation()){
				$returnVal['is_located'] = true;
			}
		}

		if($this->location->IsValid())
		{
			$returnVal['has_current_location'] = true;
		}

		if(false == $returnVal['is_located'] && true == $returnVal['has_current_location'])
		{
			require_once(BASE_PATH.'/model/users/class_user.php');
			$user = new CUser($this->uid, array('db' => $this->db));
			if($this->LoggedIn()){

				if($user->SetLocation($this->location)){

					$returnVal['locationUpdated'] = true;
				}
			}
		}


		if($returnVal['has_current_location']){

			$lastPage = $this->GetRequest('prevPage');
			if(false === $lastPage){
				$lastPage = 0;
			}

			$rangeSearcher = new CSearchRange($this->location, array('db' => $this->db));

			switch($this->action){

				case "users":
					require_once(BASE_PATH.'/model/in_list_getters/CUserInList.php');
					$returnVal = array_merge((array)$returnVal, $rangeSearcher->UsersInRange($this->radius, $this->uid, $lastPage, SEARCH_RANGE_DEFAULT_RESULT_LIMIT));

				break;
				case "groups":
					$returnVal = array_merge((array)$returnVal, $rangeSearcher->GroupsInRange($this->radius, $this->uid, $lastPage, SEARCH_RANGE_DEFAULT_RESULT_LIMIT));

				break;
				case "relevants":
				case "items":
				default:
					$returnVal = array_merge((array)$returnVal, $rangeSearcher->ItemsInRange($this->radius, $this->uid, $lastPage,  SEARCH_RANGE_DEFAULT_RESULT_LIMIT));
				break;
			}
		}else{
			$returnVal['result'] = ERR_SEARCH_RANGE_INVALID_LOCATION;
		}
		
		return $returnVal;
	}
	
	
}