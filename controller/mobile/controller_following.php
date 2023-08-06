<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/items/class_new_item.php');

require_once(BASE_PATH.'/model/users/class_user.php');

require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/in_list_getters/CUserInList.php');


class CFollowingController extends CBaseController
{
	private $action;
	
	
	function __construct(array $request, array $dependicies = array()){
		
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');
		
	}
	
	
	function RunAction(){
	
		$returnVal = array();
		$user = new CUser($this->uid);
		
		switch($this->action){
			
			case "follow":
				$followedID= $this->GetRequest('followedID');
				
				$returnVal['process'] = false;
				$returnVal['uid'] = $this->uid;
				$returnVal['userid'] = $followedID;
				
				if($followedID != $this->uid && !empty($followedID)){
					
					if(!$user->IsFollowing($followedID)){
						
						if($user->Follow($followedID)){
							
							$returnVal['process'] = true;
							$returnVal['followed'] = true;
							$returnVal['message'] = _("Followed");
							
							require_once(BASE_PATH.'/model/news/class_user_news.php');
							require_once(BASE_PATH.'/model/news/class_news_defs.php');
							
							$news = new CUserNews(0, $this->uid, new CBasicNewsOptions(0, 0, 0), array('db'=> $this->db));
							$newsAddResult = $news->Add($followedID, NEWS_TYPE_USER_FOLLOWED, array($this->uid), array(NEWS_PARAM_TYPE_USER));
							$returnVal['nid'] = $newsAddResult['nid'];
							if(false === $newsAddResult['error']){
								
							}else{
								$returnVal['errCode'] = $newsAddResult['errCode'] ;
							}
						}
					}else{
						
						if($user->Unfollow($followedID)){
							$returnVal['process']  = true;
							$returnVal['followed'] = false;
							$returnVal['message']  = _("Not following anymore");
						}
					}
				}
			break;
			case "followeds_items":
				$returnVal = array_merge($returnVal, $user->GetFollowingsItems());
			break;
			case "followers":
				$followers = $user->GetFollowers(array('uid', 'username', 'lastactive'));
				$returnVal['users'] = array();
				if(count($followers) > 0){

					$userInListGetter = new CUserInList();
					
					foreach($followers as $follower){

						$returnVal['users'][] = $userInListGetter->GetUser($follower['uid'], $user);

					}
				}
			break;
			case "fbfriends":
				$fbFriendsData = $this->GetRequest("friends");

				$returnVal['users'] = array();
				
				$friendIDs = $user->GetFBFriends($fbFriendsData);
				if(count($friendIDs) > 0){

					$userInListGetter = new CUserInList();

					foreach($friendIDs as $friendID){

						$returnVal['users'][] = $userInListGetter->GetUser($friendID, $user);
							
					}
				}
				
				
			break;
			case "followings":
			default:
				$followings = $user->GetFollowings(array('uid', 'username', 'lastactive'));
				$returnVal['users'] = array();
				if(count($followings) > 0){

					$userInListGetter = new CUserInList();

					foreach($followings as $following){

						$returnVal['users'][] = $userInListGetter->GetUser($following['uid'], $user);
							
					}
				}
			
			
		}
		return $returnVal;
		
	}
	
	
}