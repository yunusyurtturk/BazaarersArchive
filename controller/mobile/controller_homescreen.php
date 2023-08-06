<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');


class CHomeScreenController extends CBaseController
{
	private $action;
	
	
	function __construct(array $request,   array $dependicies = array()){
	
		parent::__construct($request, $dependicies);
	
		$this->action   = $this->GetRequest('action');
	}
	function RunAction(array &$uploadedFiles = array()){
	
		$returnVal = array();
		$returnVal['error'] = false;
		$user = new CUser($this->uid, array('db'=>$this->db));
		
		switch($this->action){
			
			case "isMobileUserFriend":
				
				$friendlist=trim(strip_tags(htmlspecialchars($_POST['friendlist'])));
				$returnVal['error'] = true;
				/*
				if(count($uids) > 0){
						
					$inQuery = implode(',', array_fill(0, count($uids), '?'));
					foreach ($fields as $key=>$value){
							
						$fields[$key] = 'items.'.$value.' as '.$fields[$key];
					}
					$fields_query_str = implode(', ',$fields);
					$query = 'SELECT '.$fields_query_str.' FROM items WHERE uid IN('.$inQuery.')';
				
					$this->db->Prepare($query);
						
					if($this->db->Execute($uids)){
						if($this->db->RowCount() > 0){
							$fetchAll = $this->db->FetchAll();
						}else{
				
								
						}
					}else{
				
					}
						
				}else{
						
				}
				
				
				$this->db->query("SELECT uid, profile FROM socialaccount WHERE profile IN ($friendlist)");
				//$query->execute(array($friendlist));
				$returnVal['count'] = $query->rowCount();
				while($fetch = $query->fetch()){
					$returnVal[$fetch['profile']] = array('uid'=>$fetch['uid'], 'username'=>  get_username($fetch['uid']), 'is_following'=>is_following($userid, $fetch['uid']));
				}*/
			break;
			case "updateFBMobileID":
				$fbUserID = $this->GetRequest("fbUserID");
				$returnVal['result'] = $user->UpdateFBMobileID($fbUserID);
			break;
			case "deleteNews":
				require_once(BASE_PATH.'/model/news/class_user_news.php');
				
				$nid = $this->GetRequest('nid');
				$returnVal = $user->DeleteNews($nid);
				
				
			break;
			case "notifications":
				$returnVal = $user->GetNotifications();
			break;
			case "groups":
				$groups = $user->GetGroups(array('gid', 'gname', 'gpic'));
				foreach($groups as $group){
					
					$returnVal['groups'][] = array('gid' => $group['gid'], 'gname' => $group['gname'], 'gpic' => $group['gpic']);
				}
				
			break;
			case "changeProfilePic":
				$returnVal['error'] = true;

				CMisc::BufferOn();

				if(is_array($uploadedFiles) && count($uploadedFiles) > 0)
				{
					$imageFiles = CMisc::ReArrayFiles($uploadedFiles);

					$returnVal = $user->ChangeUserPic($imageFiles[0]);

				}
				$returnVal['misc'] = CMisc::GetBufferContent();
				CMisc::BufferOff();
				$returnVal['userpic']    = $user->GetPic();
				header("Location: /myprofile?profilePicChanged");

			break;
			case "items":
				require_once(BASE_PATH.'/model/items/class_items.php');
				$items = $user->GetItems(array('iid', 'header', 'price', 'mainpic'));
				
				$returnVal['count'] = count($items);
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				
				if($returnVal['count'] > 0){
					foreach($items as $currentItem){
						
						$item = new CItems($currentItem['iid'], array('db',$db));
						$returnVal['items'][] = array('iid' => $currentItem['iid'], 'header' => $currentItem['header'],
													  'price'=>$item->GetPriceStr(), 'priceStr' =>  $item->GetPriceStr(), 'itemowner'=> $user->GetUsername(),
													  'itempic' => $item->GetMainPic());
						 
					}
				}else{
					$returnVal['items'] = array();
				}
			break;
			case "news":
			default:


				$returnVal['followCount'] = 0;
				$returnVal['followerCount'] = 0;
				$returnVal['followedsItemsCount'] = 0;
				$returnVal['itemCount'] = 0;
				$returnVal['isFBConnected'] = $user->IsFbConnected();
				
				
				$returnVal['followCount'] = count($user->GetFollowingIDs());
				$returnVal['followerCount'] = count($user->GetFollowerIDs());
				$returnVal['followedsItemsCount'] = $user->GetFollowingsItemCount();
				$returnVal['itemCount'] = $user->GetItemCount();
				
				$returnVal['username']   = $user->GetUsername();
				$returnVal['usermail']   = $user->GetEmail();
				$returnVal['userinfo']   = $user->GetUserAbout();
				$returnVal['userpic']    = $user->GetPic();
				$returnVal['news'] = $user->GetNews();
				
				
				
				$user->SetAllNewsAsRead();
				
				
		}
		
		return $returnVal;
		
	}
	
	
}