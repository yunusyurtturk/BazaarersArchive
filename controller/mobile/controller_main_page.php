<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');

require_once(BASE_PATH.'/model/items/class_items.php');
require_once(BASE_PATH.'/model/items/class_all_items.php');
require_once(BASE_PATH.'/controller/mobile/result_code_base_defs.php');

require_once(BASE_PATH.'/model/in_list_getters/CItemInList.php');

class CMainPageController extends CBaseController
{
	private $action;

	
	function __construct($request, array $dependicies = array()){
		
		
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');

	}

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
	function SetLocation($lat, $lng){

		$_SESSION['lat'] = $lat;
		$_SESSION['lng'] = $lng;
	}
	function RunAction()
	{
		$returnVal = array();

		switch($this->action) {
			case 'userLocation':
				if(isset($_REQUEST['lat']) && isset($_REQUEST['lng'])){

				    $location = new CLocation($_REQUEST['lat'], $_REQUEST['lng']);

                    if($location->IsValid()){

                        $_SESSION['lat'] = $_REQUEST['lat'];
                        $_SESSION['lng'] = $_REQUEST['lng'];

                        if($this->LoggedIn()){
                            $user = new CUser($this->GetUid(), array('db'=> $this->db));
                            $user->SetLocation($location);

                        }
                    }

				}else{

					require_once(BASE_PATH.'/model/users/class_user.php');
					require_once(BASE_PATH.'/model/location/class_location.php');

                    if(0 != $this->uid){

                        $user = new CUser($this->uid, array('db' => $this->db));
                        $userLocation = $user->GetLocation();

                        if(null != $userLocation && $userLocation->IsValid()){

                            $_SESSION['lat'] = $userLocation->GetLat();
                            $_SESSION['lng'] = $userLocation->GetLng();
                        }
                    }


				}
				$returnVal['lat'] = $_SESSION['lat'];
				$returnVal['lng'] = $_SESSION['lng'];

			break;
			case "searchRange":
			case "explore":
				require_once('../controller/mobile/controller_search_range.php');


				$_REQUEST['action'] = 'items';

				if(!isset($_REQUEST['lat']) && isset($_SESSION['lat'])){

					$_REQUEST['lat'] = $_SESSION['lat'];
				}
				if(!isset($_REQUEST['lng']) && isset($_SESSION['lng'])){

					$_REQUEST['lng'] = $_SESSION['lng'];
				}

				if(isset($_REQUEST['lat']) && isset($_REQUEST['lng'])){

					$this->SetLocation($_REQUEST['lat'], $_REQUEST['lng']);
				}
				$controller = new CSearchRangeController($_REQUEST);
				$returnVal = $controller->RunAction();

				$foundedItemsName = &$returnVal;
				if(isset($returnVal['suggestedItems']) && is_array($returnVal['suggestedItems']) && count($returnVal['suggestedItems']) > 0 ){

					$foundedItemsName = &$returnVal['suggestedItems'];;

				}

				if(isset($foundedItemsName['items'])) {

					$itemInListGetter = new CItemInList($controller->GetLocation());

					foreach($foundedItemsName['items'] as &$item){

						$item = $itemInListGetter->GetItem($item['iid']);

						$item['user-profile-url'] = '/user/'.$item['uid'];

					}
				}

				$returnVal = &$foundedItemsName;
			
	
			break;
			case "items":
			default:
				$allItems = new CAllItems( array('db' => $this->db));
				$returnVal = $allItems->GetLastItems(array('iid'));

                $location = null;
                if($this->LoggedIn()){

                    $user = new CUser($this->uid, array('db' => $this->db));
                    $location = $user->GetLocation();
                }

				$itemInListGetter = new CItemInList($location);

				if(is_array($returnVal) && count($returnVal) > 0){

					foreach($returnVal as &$item){

						$item = $itemInListGetter->GetItem($item['iid']);

						$item['user-profile-url'] = '/user/'.$item['uid'];

					}

				}

			break;
		}
		return $returnVal;
	}
//[YY] Daha uygun bir kontrolcuye alindiginda her defasinda yeniden CUser olusturmak zorunda kalinmaz
	function GetNews($count = 30){

		if($this->LoggedIn()){

			$user = new CUser($this->uid);
			return $user->GetNews($count);
		}
	}
//[YY] Daha uygun bir kontrolcuye alindiginda her defasinda yeniden CUser olusturmak zorunda kalinmaz
	function GetNotifications(){
		if($this->LoggedIn()) {

			$user = new CUser($this->uid);
			return $user->GetNotifications();;
		}
	}
	
	
}