<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
//require_once(BASE_PATH.'/model/location/class_location.php');
require_once(BASE_PATH.'/controller/mobile/result_code_base_defs.php');
require_once(BASE_PATH.'/model/users/class_user_account.php');

require_once(BASE_PATH.'/model/in_list_getters/CUserInList.php');




class CUserController extends CBaseController
{
	private $action;
	
	
	private $location;
	private $user;
	
	function __construct($request, array $dependicies = array()){
	
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');
		
		if($this->LoggedIn()){
			
			$this->user = new CUser($this->uid, array('db' => $this->db));
			$this->location = $this->user->GetLocation();
		}else{
			
			$lat = $this->GetRequest('lat');
			$lng = $this->GetRequest('lng');
			$this->location =  new CLocation($lat, $lat);
		}
		
	}
	function RunAction(){
		
		$returnVal = array();
		switch($this->action){
			
			case "getItems":
				$userid     = $this->GetRequest('userid');
				$returnVal['items'] = array();
				if(is_numeric($userid) && $userid > 0){
					require_once(BASE_PATH.'/model/items/class_items.php');
					
					$otherUser = new CUser($userid, array('db' => $this->db));
					$container = new CContainer();
					$db = $container->GetDBService(true);
					
					$userItems =  $otherUser->GetItems(array('iid', 'header', 'amount', 'price', 'priceType', 'category'));
					
					foreach($userItems as $userItem){
						$item = new CItems($userItem['iid'], array('db' => $db));
						
						$returnVal['items'][] = array('iid' => $userItem['iid'], 'header'=>$userItem['header'],
													  'itemowner' => $otherUser->GetUsername(),
													  'itempic' => $item->GetMainPic(),
													  'price' => $item->GetPriceStr(),
													  'mainpic' => $item->GetMainPic(),
													  'category' => $item->GetCategoryID(),
													  'catname' => $item->GetCategoryName());
					}
				}
			break;
			case "getFollowers":
				$userid     = $this->GetRequest('userid');
				if(is_numeric($userid) && $userid > 0){
					$otherUser = new CUser($userid, array('db' => $this->db));
					$followers = $otherUser->GetFollowers(array('uid', 'username',  'lastactive'));
					$returnVal['users'] = array();
					

					$userInListGetter = new CUserInList();
					
					foreach($followers as $key=>$follower){
						$returnVal['users'][] = $userInListGetter->GetUser($follower['uid'], $this->user);
					}
				}
			break;
			case "getFollowings":
				$userid     = $this->GetRequest('userid');
				if(is_numeric($userid) && $userid > 0){
					$otherUser = new CUser($userid, array('db' => $this->db));
					
					$followers = $otherUser->GetFollowings(array('uid', 'username',  'lastactive'));
					$returnVal['users'] = array();

					$userInListGetter = new CUserInList();
					
					foreach($followers as $key=>$following){

						$returnVal['users'][] = $userInListGetter->GetUser($following['uid'], $this->user);
					}
				}
			
			break;
			case "getGroups":
				$returnVal['groups'] = array();
				$returnVal['groupCount'] = 0;
				$groups = $this->user->GetGroups(array('gid', 'gname', 'gpic'));
				
				require_once(BASE_PATH.'/model/group/class_group.php');
				$container = new CContainer();
				$db = $container->GetDBService(true);
				if(is_array($groups)){
					foreach($groups as $key => $group){
					
						$userGroup = new CGroup($group['gid'], array('db' => $db));
						$isMember = $userGroup->HasMember($this->uid);
						$distance = CLocations::GetDistanceBetween($userGroup->GetLocation(), $this->location);
						$memberCount = $userGroup->GetMemberCount();
					
						$returnVal['groups'][] = array('gid'=>$group['gid'], 
													   'is_member'=> $isMember , 
													   'gname'=>$group['gname'], 
													   'gpic'=>  $group['gid'], 
													   'membercount'=>$memberCount );
		            
					
					}
				}
			break;
			case "user_info_page":
			case "userInfo":
				if(null != $this->user){
					
					$returnVal = $this->user->GetData(array('uid',  'username', 'info'));
					$returnVal['userpic'] = $this->user->GetPic();
				}else{
					
					$returnVal['error'] = true;
					$returnVal['message'] = _("Error occured. No such user");
				}
				
				
			break;
			case "changePassword":
			case "change_password":
				$currentPassword = $this->GetRequest('current_password');
				$newPassword     = $this->GetRequest('new_password');
				
				$changeResult 	 =  $this->ChangePassword($currentPassword, $newPassword);
				
				if(false === $changeResult['error']){
					$returnVal['success'] = true;
					$returnVal['message'] = _('Password changed');
				}else{
					$returnVal['success'] = false;
					$returnVal['message'] = $changeResult['message'];
				}
				
			break;
			case "change_location_type":
				
				$this->UpdateLocationType(LOCATION_TYPE_USER_PUBLIC);
			break;
			case "update_about":
				$info_about = $this->GetRequest('about');
				if($this->UpdateAbout($info_about)){
					$returnVal['error'] = false;
				}else{
					$returnVal['error'] = true;
				}
			
			break;
			case "updateLocation":
				
				$location = new CLocation($lat, $lng);
				$this->updateLocation($location, LOCATION_TYPE_USER_PUBLIC);
			break;
			case "removeLocation":
				$returnVal = $this->user->RemoveLocation();
			break;
			case "settings":
				$user = new CUser($this->uid, array('db' => $this->db));
				$userData = $user->GetData(array('uid', 'username',  'lastactive'));
				$returnVal = array('username'=>  $user->GetUsername(),  'userpic'=>$user->GetPic(), 'lastactive' =>  CMisc::TimeDiffToString($user->GetLastActiveTime()));
                
			break;
			case "logout":
				$returnVal['logged_out'] = false;
				if($this->Logout()){
					$returnVal['logged_out'] = true;
				}
			break;
			default:
				$userid     = $this->GetRequest('userid');
				if(is_numeric($userid) && $userid > 0){
					$otherUser = new CUser($userid, array('db' => $this->db));
					$userLocation = $otherUser->GetLocation();
					$returnVal['userinfo'] = array('userid'=>$userid, 'userpic'=>  $otherUser->GetPic(), 
												   'about' => $otherUser->GetUserAbout(),
												   'username'=>$otherUser->GetUsername(), 'lid' => $otherUser->GetLid(), 
												   'lat' => $userLocation->getLat(), 'lng' => $userLocation->getLng(), 
												   'lastactive'=>date('d.m.Y',$otherUser->GetLastActiveTime()));
					$itemIDs = $otherUser->GetItemIDs();
					$returnVal['followerCount']  = $otherUser->GetFollowerCount();
					$returnVal['followingCount'] = $otherUser->GetFollowingCount();
					require_once(BASE_PATH.'/model/items/class_items.php');
					$returnVal['items'] = array();
					foreach($itemIDs as $itemID){
						$item = new CItems($itemID, array('db' => $this->db));
						$returnVal['items'][] = array('iid' => $itemID, 'header'=> $item->GetTitle(),
														'itemowner' => $userid, 'itempic'=>$item->GetMainPic(),
														'price' => $item->GetPriceStr(), 'priceStr' =>  $item->GetPriceStr(),
													    'category' => $item->GetCategoryID(), 'catname'=> $item->GetCategoryName());
						
					}
					$returnVal['logged_in'] = $this->LoggedIn();
				}
				if(isset($returnVal['logged_in']) && $returnVal['logged_in']){
					
					$returnVal['following'] = $this->user->IsFollowing($userid);
				}
				
				
				
				
			
		}
		
		return $returnVal;
		
		
		
	}
	private function UpdateLocationType($locationType)
	{
		$userAccount = $this->DIContainer->GetUserAccountService($this->uid);
		$lid = $this->user->GetLid();
		
		if(0 != $lid){
			
			if($userAccount->SetLocationType($locationType)){
				return true;
			}
		}
		return false;
		
	}
	private function Logout()
	{
		$userAccount = $this->DIContainer->GetUserAccountService($this->uid);
		if($userAccount->SetPasscode(CMisc::CreateRandomString(40))){
			return true;
		}else{
			return false;
		}
	}
	private function UpdateLocation(CLocation $location)
	{
		$userAccount = $this->DIContainer->GetUserAccountService($this->uid);
		if($userAccount->SetLocation($location)){
			return true;
		}else{
			return false;
		}
	}
	private function UpdateAbout($info_about)
	{
		$user = new CUser($this->uid, array('db' => $this->db));
		if($user->SetUserAbout($info_about)){
			return true;
		}else{
			return false;
		}
	}
	private function ChangePassword($currentPassword, $newPassword)
	{
		$returnVal['error'] = true;
		
		if(!empty($currentPassword) && !empty($newPassword)){
			
			$userAccount = new CUserAccount($this->uid, array('db' => $this->db));
			$changeResult = $userAccount->SetNewPassword($currentPassword, $newPassword);
			
			if(false === $changeResult['error']){
				
				$returnVal['error'] = false;
			}else{
				
				$returnVal['message'] = $changeResult['message'];
			}
		}else{
			$returnVal['message'] = _("Missing password or new password information");
		}
		
		return $returnVal;
	}
	
	
	
}