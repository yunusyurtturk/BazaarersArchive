<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/location/class_location.php');
require_once(BASE_PATH.'/model/location/class_locations.php');
require_once(BASE_PATH.'/model/image/class_image_manip.php');
require_once(BASE_PATH.'/model/socials/social_defs.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');

class CUser extends CModelBaseWithDB
{
	private $uid = null;
	private $is_exist = false;
	private $passcode;
	private $user_info = array();
	
	function __construct($uid, array $dependicies = array())
	{
		parent::__construct($dependicies);
		
		$this->uid = $uid;
		$this->is_exist = ($this->GetIsExist()?true:false);
		
		$this->user_info['uid'] = $uid;
	
		$this->Init($uid);
	}
	function IsExist()
	{
		return $this->is_exist;
	}
	private function GetIsExist()
	{
		$this->db->Prepare('select uid from users where uid=:uid');
		
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0){
		
				return true;
			}
		}
		
		return false;
	}
	function GetInfo(){
		return $this->user_info;
	}
	private function GetInfoArray()
	{
		return $this->user_info;
	}
	private function InsertInfo($key, $value){
		$this->user_info[$key] = $value;
	}
	
	function ChangeUserPic($pic)
	{
		$returnVal = array();
		$returnVal['error'] = true;
		
		$changed = false;
		$imageOps = new CImageManipulation($pic);
		// $tempImage = $imageOps->AddTempImage();
		if(true || false !== $tempImage ){
			
			$resizedPic = &$imageOps->ResizeImage(400, 400);

			if($resizedPic){
				
				$dir = BASE_PATH.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'userpics';
				$filename = $dir.DIRECTORY_SEPARATOR.$this->uid.'.jpg';
				if($imageOps->SaveImageTo($filename, $resizedPic, 'userpics')){
					
					$changed = true;
					$returnVal['error'] = false;
				}else{
					
				}
				
			}else{
				
				$returnVal['errCode'] = 22;
			}
		}else{
			
			$returnVal['errCode'] = 3;
		}
		if(true == $changed){
			$this->InsertInfo('userpic', $this->uid.'.jpg');
		}
		return $returnVal;
		
	}

	function GetLastItemMessages($messageWay, $limitStart, $limitEnd){
		
		$itemMessages = new CItemMessages(0, 0, $this->uid, array('db' => $this->db));
		
		return $itemMessages->GetLastItemMessages($messageWay, $limitStart, $limitEnd);
	}
	function Init($uid){
		
		$this->db->Prepare('select uid, username from users where uid=:uid');
		
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);
		if($this->db->Execute($params)){
			
			if($this->db->RowCount() > 0){
				
				$fetch = $this->db->Fetch();
				$this->user_info = array_merge($this->user_info, $fetch);
			}
		}
	}
	function GetUserID()
	{
		return $this->uid;
	}
	function GetPassCode(){
		$alias = 'passcode';
		if(!isset($this->user_info[$alias])){
			
			$this->db->Prepare('select passcode as '.$alias.' from users where uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);

			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->fetch();
					$this->InsertInfo($alias, $fetch[$alias]);

				}
			}
		}	
		return $this->GetInfoArray()[$alias];

			
			
		
		
	}
	function DeleteNews($nid){
		
		
		$returnVal = array();
		$returnVal['error'] = true;
		
		$this->db->Prepare('DELETE FROM news WHERE nid=:nid and uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('nid', $nid, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			
			if($this->db->RowCount() > 0){
				
				$returnVal['error'] = false;
                $returnVal['is_deleted'] = true;
                $returnVal['nid'] = $nid;
				
			}
		}
		
		return $returnVal;
		
	}
	function UpdateFBMobileID(&$fbUserID)
	{
		
		require_once(BASE_PATH.'/model/socials/social_defs.php');
		
		$this->db->Prepare('SELECT uid FROM userSocials WHERE uid =:uid and network=:network');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('network', SOCIAL_NETWORK_FACEBOOK, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			
			$params[] = new CDBParam('networkID', $fbUserID, PDO::PARAM_INT);
			
			if($this->db->RowCount() > 0){
				
				$this->db->Prepare('UPDATE userSocials SET networkID=:networkID WHERE uid=:uid AND network=:network');
				if($this->db->Execute($params)){
					
					if($this->db->RowCount() > 0){
						
						return true;
					}
				}
		
			}else{
				$this->db->Prepare('INSERT INTO userSocials (networkID, network, uid) 
									VALUES(:networkID, :network, :uid)');
				
				if($this->db->Execute($params)){
					
					if($this->db->RowCount() > 0){
						
						return true;
					}
				}
				
			}
		}
		return false;
	}

	static function SGetUnreadNewsCount($uid, CDBConnection $db)
    {
        $returnVal = array();
        $returnVal['error'] = true;
        $returnVal['count'] = 0;

        $db->Prepare('SELECT nid, addtime FROM news WHERE uid =:uid and is_read=0 ORDER BY addtime DESC');
        $params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);

        if($db->Execute($params)){

            $returnVal['error'] = false;
            if($db->RowCount() > 0){

                $returnVal['count'] = $db->RowCount();
                $fetch = $db->Fetch();
                $returnVal['lastnewstime'] = $fetch['addtime'];

            }
        }

        return $returnVal;
    }
	function GetNotifications()
	{
		$returnVal = array();
		$returnVal['error'] = true;
		$returnVal['count'] = 0;
		
		$this->db->Prepare('SELECT nid, addtime FROM news WHERE uid =:uid and is_read=0 ORDER BY addtime DESC');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			
			$returnVal['error'] = false;
			if($this->db->RowCount() > 0){
				
				$returnVal['count'] = $this->db->RowCount();
				$fetch = $this->db->Fetch();
				$returnVal['lastnewstime'] = $fetch['addtime'];
				
			}
		}
		
		return $returnVal;
		
	}
	private function IsConnectedToNetwork($network)
	{
		$returnVal = false;
		$this->db->Prepare('SELECT uid FROM userSocials WHERE network=:network and uid=:uid');
		
		$params[] = new CDBParam('uid',   $this->uid,   PDO::PARAM_INT);
		$params[] = new CDBParam('network', $network, PDO::PARAM_STR);
		
		if($this->db->Execute($params)){
		
			if($this->db->RowCount() > 0){
					
				$returnVal = true;
			}
		}
		return $returnVal;
		
	}
	function IsFbConnected()
	{
		
		return $this->IsConnectedToNetwork(CSocialNetworks::$Facebook);
	}
	static function GetUserIDFromSocialNetworkID($network, $networkID, CDBConnection $db)
	{
		$returnVal = 0;
		$db->Prepare('SELECT uid FROM userSocials WHERE network=:network and networkID=:networkID');
		
		$params[] = new CDBParam('network',   $network,   PDO::PARAM_INT);
		$params[] = new CDBParam('networkID', $networkID, PDO::PARAM_INT);

		if($db->Execute($params)){

			if($db->RowCount() > 0){
					
				$fetch = $db->Fetch();
				$returnVal = $fetch['uid'];
			}
		}
		return $returnVal;
	}
	static function SGetUsername($uid, CDBConnection $db)
	{
		
		$db->Prepare('SELECT username  FROM users WHERE uid=:uid');
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);
		if($db->Execute($params)){
		
			if($db->RowCount() > 0){
					
				$fetch = $db->Fetch();
				return $fetch['username'];
			}else{
				return false;
			}
		}
	}
	function GetUsername()
	{
		$alias = 'username';
		if(!isset($this->user_info[$alias])){
				
			$this->db->Prepare('select username as '.$alias.' from users where uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
			if($this->db->Execute($params)){
				
				if($this->db->RowCount() > 0){
					
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}else{
					return false;
				}
			}
		}
		return $this->GetInfoArray()[$alias];;
	}
	function GetLastActiveTime()
	{
		$alias = 'lastactive';
		if(!isset($this->user_info[$alias])){
	
			$this->db->Prepare('select '.$alias.' as '.$alias.' from users where uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
	
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}
			
		return $this->GetInfoArray()[$alias];
	}
	function GetLid(){
		$alias = 'lid';

		if(!isset($this->user_info[$alias])){

			$this->db->Prepare('select lid as '.$alias.' from users where uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
			if($this->db->Execute($params)){

				if($this->db->RowCount() > 0){

					$fetch = $this->db->Fetch();

					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}else{
        }
		return $this->GetInfoArray()[$alias];
	}
	function GetRegisterTime()
	{
		$alias = 'signupdate';
		$isset = false;
		if(!isset($this->user_info[$alias])){

			$this->db->Prepare('select '.$alias.' from users where uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);

			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
					$isset = true;
				}
			}
			if(false == $isset){
				$this->InsertInfo($alias, '');
			}
		}

		return $this->GetInfoArray()[$alias];

	}
	function GetRegisterDate()
	{
		$registerTime = $this->GetRegisterTime();

		return _('Member since ').date('d.m.Y', $registerTime);

	}
	function GetUserAbout(){
		$alias = 'userinfo';
		$isset = false;
		if(!isset($this->user_info[$alias])){
		
			$this->db->Prepare('select info as '.$alias.' from users where uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
					$isset = true;
				}
			}
			if(false == $isset){
				$this->InsertInfo($alias, '');
			}
		}
			
		return $this->GetInfoArray()[$alias];
	}
	static function GetUserPic($uid){
		require_once(BASE_PATH.'/model/image/images_cfg.php');
		
		$returnVal = 'default.png';
		if(is_file(IMAGE_CFG_USERPICS_PATH.$uid.'.jpg')){
				
			$returnVal = $uid.'.jpg';
		}
		
		return $returnVal;
	}


	function GetPic(){
		require_once(BASE_PATH.'/model/image/images_cfg.php');

		$alias = 'userpic';
		if(!isset($this->user_info[$alias])){
			if(is_file(IMAGE_CFG_USERPICS_PATH.$this->uid.'.jpg')){
				 
				$this->InsertInfo($alias, $this->uid.'.jpg');
			}
			else{
				$this->InsertInfo($alias, 'default.png');
			}
		}
		
		return $this->GetInfoArray()[$alias];
	}
	function SetAllNewsAsRead()
	{
		$returnVal = false;
		require_once(BASE_PATH.'/model/news/class_user_news.php');
		$this->db->Prepare('UPDATE news SET is_read=:is_read WHERE uid=:uid');
		$params[] = new CDBParam('uid', (int)$this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('is_read', (int)1, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			$returnVal = true;
			if($this->db->RowCount() > 0){
				
				
			}
		}
		return $returnVal;
	}
	function GetNews($limit = 50){
		$returnVal = array();
		require_once(BASE_PATH.'/model/news/class_user_news.php');
		require_once(BASE_PATH.'/model/class_misc.php');
		
		$this->db->Prepare('SELECT * FROM news WHERE uid =:uid ORDER BY is_read, addtime DESC LIMIT :limit');
		$params[] = new CDBParam('uid', (int)$this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('limit', (int)$limit, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				while($fetch = $this->db->Fetch()){
					
					$newsOptions = new CBasicNewsOptions($fetch['format'], $fetch['params'], $fetch['paramtypes']);
					$news = new CUserNews($fetch['nid'], $this->uid, $newsOptions, array('db' => $this->db));
					
					
					$returnVal[] = array('nid'=>$fetch['nid'], 'format'=> $fetch['format'],
												 'actionType' => $news->GetActionType(),
												 'primaryID'=>$news->GetPrimaryTypeID(),
												 'isRead'=> ($fetch['is_read']>0)?true:false,
												 'date'=>  CMisc::TimeDiffToString($fetch['addtime']),
												 'news'=> $news->GetFormattedNews($newsOptions));

				}
			}
		}

		return $returnVal;
	}
	function GetLocationType(){
		
		$alias = 'loctype';
		if(!isset($this->user_info[$alias])){
		
			$this->db->Prepare('select locations.type as '.$alias.' from  users, locations where users.uid = :uid and users.lid = locations.lid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}else{
					$this->InsertInfo($alias, LOCATION_TYPE_NO_LOCATION);
				}
			}
		}
			
		return $this->GetInfoArray()[$alias];
	
	}
	static function SGetLocation($uid){

		$returnVal = false;


		$DIContainer = new CContainer();
		$db = $DIContainer->GetDBService(true);


		$db->Prepare('SELECT locations.lat as lat, locations.lng as lng FROM locations, users WHERE users.uid = :uid and users.lid = locations.lid');
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);

		if($db->Execute($params)){
			if($db->RowCount() > 0){
				$fetch = $db->Fetch();
				$returnVal =  new CLocation($fetch['lat'], $fetch['lng']);
			}
		}

		return $returnVal;

	}
	function GetLocation(){
	
		$alias = 'location';
		if(!isset($this->user_info[$alias])){
		
			$this->db->Prepare('SELECT locations.lat as lat, locations.lng as lng FROM locations, users WHERE users.uid = :uid and users.lid = locations.lid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, new CLocation($fetch['lat'], $fetch['lng']));
				}else{
					
					$this->InsertInfo($alias, new CLocation(0, 0));
				}
			}else{
				
			}
		}
			
		return $this->GetInfoArray()[$alias];

	}
	private function AddLocation(CLocation $location){

        require_once(BASE_PATH.'/model/defs/location_types.php');
		$this->db->Prepare('INSERT INTO locations(type, lat, lng, address) values(:type, :lat, :lng, :address) ');
		$params[] = new CDBParam('type', 	LOCATION_TYPE_USER_PUBLIC,  PDO::PARAM_INT);
		$params[] = new CDBParam('lat', 	strval($location->lat), 	PDO::PARAM_STR);
		$params[] = new CDBParam('lng', 	strval($location->lng), 	PDO::PARAM_STR );
		$params[] = new CDBParam('address', '', 						PDO::PARAM_STR);

		
		if($this->db->Execute($params)){
			return $this->db->GetLastInsertID();
		}else{
			return 0;
		}
	}
	private function SetLid($lid){
		$alias ='lid';
        if(0 != $this->uid){

            $this->db->Prepare('UPDATE users SET lid=:lid WHERE uid=:uid');
            $params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
            $params[] = new CDBParam('lid', $lid, 		PDO::PARAM_INT);

            if($this->db->Execute($params)){
                $this->InsertInfo($alias, $lid);
            }
        }

	}
	function SetLocation(CLocation $location){
		
		$alias ='location';
		if($location->IsValid()){

			$lid = $this->GetLid();
			
			if(0 == $lid){

				$lid = $this->AddLocation($location);
			}
			if($this->SetLid($lid)){
				$this->InsertInfo($alias, $location);

				return true;
			}
		}
		return false;

	}
	private function ClearLid(){
		$alias ='lid';
		$this->db->Prepare('UPDATE users SET lid=:lid WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('lid', 0, 			PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			$this->InsertInfo($alias, 0);
			return true;
		}
		return false;
	}
	function RemoveLocation(){
		$alias ='location';
		$this->db->Prepare('DELETE locations FROM locations INNER JOIN users ON users.lid=locations.lid WHERE users.uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			
			$this->ClearLid();
			unset($this->user_info[$alias]);
			return true;
		}
		return false;
	}
	function GetData(array $fields){
		
		if(is_array($fields)){
			
			foreach ($fields as $key){
				
				if(isset($this->user_info[$key])){
					
					//echo $key.' =  degeri daha once set edildigi icin tekrar sorgulanmayacak<br />';
					unset($fields[$key]);
				}else{
					
					//echo $key.' degeri aranacak<br />';
				}
			}
			
			$rows = implode(', ',$fields);
			$this->db->Prepare('SELECT '.$rows.' FROM users WHERE uid=:uid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
			if($this->db->Execute($params)){
			
				if($this->db->RowCount() > 0){
					
					$fetch = $this->db->Fetch();
					$this->user_info = array_merge($this->user_info, $fetch);
				}
				
			}	
		}
	
		return $this->user_info;
	}
	private function GetFollowingOrFollowers($get = 'followers', array &$fields = array())
	{
		$returnVal = array();
		$followlist_col = 'followed';
		$join_col = 'uid';
			
		if('followings' == $get){
			$followlist_col = 'uid';
			$join_col = 'followed';
		}
		$aliases = $fields;
		/* Set requested columns */
		foreach ($fields as $key=>$value){
		
			$fields[$key] = 'users.'.$value.' as '.$aliases[$key];
		}
		$fields_query_str = implode(', ',$fields);
		$this->db->Prepare('SELECT '.$fields_query_str.' FROM followlist, users 
							WHERE followlist.'.$followlist_col.'=:uid AND followlist.'.$join_col.' = users.uid ');

		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
		
			if($this->db->RowCount() > 0){
				
				$returnVal = $this->db->FetchAll();
			}
		
		}
		return $returnVal;
		
	}
	
	
	function GetFollowings(array $fields = array()){
		
		return $this->GetFollowingOrFollowers('followings', $fields);
	}
	function GetFollowingsItemCount(){
		
		$returnVal = 0;
		$this->db->Prepare('select COUNT(iid) as followeds_items_count from followlist, items where followlist.uid=:uid and followlist.followed = items.uid and items.amount > 0 ORDER BY items.addtime DESC');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			$fetch = $this->db->Fetch();
			return $fetch['followeds_items_count'];
		}
		return 0;
	}
	function GetFollowingsItems(){
		
		$returnVal = array();
		$returnVal['items'] = array();
		$this->db->Prepare('SELECT items.iid, items.header, items.amount, items.uid, items.price, items.priceType, items.category 
							FROM followlist, items 
							WHERE followlist.uid=:uid and followlist.followed = items.uid 
							AND items.amount > 0 ORDER BY items.addtime DESC');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);


		
		$returnVal['count'] = 0;
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				require_once(BASE_PATH.'/model/items/class_items.php');
				require_once(BASE_PATH.'/model/category/class_categories.php');

				$returnVal['count'] = $this->db->RowCount();

				$itemInListGetter = new CItemInList();

				while($fetch = $this->db->Fetch())
				{
					$returnVal['items'][] = $itemInListGetter->GetItem($fetch['iid']);
				}
			}
		}
		return $returnVal;
	}
	function GetFollowingCount()
	{
		$returnVal = count($this->GetFollowingIDs());
		return $returnVal;
	}
	function GetFollowerCount()
	{
		$returnVal = count($this->GetFollowerIDs());
		return $returnVal;
	}
	function GetFollowingIDs(){
		/*
		$fields = array('uid');
		return $this->GetFollowingOrFollowers('followings', $fields);*/
		$fields = array('uid');
		$ids = $this->GetFollowingOrFollowers('followings', $fields);
		$merged = array_column($ids, 'uid'); /* Diziyi duzgun formata cevir */
		return $merged;
	}
	function GetFollowers(array $fields = array())
	{
		return $this->GetFollowingOrFollowers($get = 'followers', $fields);
	}
	function GetFollowerIDs(){
		$fields = array('uid');
		$ids = $this->GetFollowingOrFollowers('followers', $fields);
		$merged = array_column($ids, 'uid');  /* Diziyi duzgun formata cevir */
		return $merged;
	}
	function Follow($followed){
		$this->db->Prepare('INSERT INTO followlist(uid, followed) VALUES (:uid, :followed)');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('followed', $followed, PDO::PARAM_INT);
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				return true;
			}
		}
		return false;
	}
	function Unfollow($followed){
		$this->db->Prepare('DELETE FROM followlist WHERE uid=:uid AND followed=:followed');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('followed', $followed, PDO::PARAM_INT);
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				return true;
			}
		}
		return false;
	}
	function GetFBFriends(&$fbFriendsData)
	{
		require_once(BASE_PATH.'/model/socials/social_defs.php');

		$jsonFriends = json_decode($fbFriendsData, true);
		$DIContainer = new CContainer();
		$db = $DIContainer->GetDBService(true);
		$returnVal = array();

		
		foreach($jsonFriends as $fbFriend){

			$uid = CUser::GetUserIDFromSocialNetworkID(CSocialNetworks::$Facebook, $fbFriend['id'], $db);

			if(0 !== $uid){
				
				$returnVal[] = $uid;
				
				
			}else{
				break;
			}
		}
		
		return $returnVal;
	}
	function GetItemsRandomly($count, $excludeItemID = 0)
	{
		$returnVal = array();
		$this->db->Prepare('SELECT iid, amount  FROM items, users WHERE items.uid = :uid and items.uid = users.uid and iid != :excludedItem HAVING items.amount > 0 ORDER BY RAND() LIMIT :count');

		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('count', $count, PDO::PARAM_INT);
		$params[] = new CDBParam('excludedItem', $excludeItemID, PDO::PARAM_INT);


		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){

				$itemInListGetter = new CItemInList();

				while($fetch = $this->db->Fetch())
				{
					$returnVal['items'][] = $itemInListGetter->GetItem($fetch['iid']);
				}
			}else{
			}
		}else{
		}

		return $returnVal;

	}
	function GetItems(array $fields = array()){
		$alias = 'items';
		$aliases = $fields;
		if(!isset($this->user_info[$alias])){
		
			foreach ($fields as $key=>$value){
			
				$fields[$key] = 'items.'.$value.' as '.$aliases[$key];
			}
			$fields_query_str = implode(', ',$fields);
			$this->db->Prepare('SELECT '.$fields_query_str.', amount FROM items, users WHERE items.uid = :uid and items.uid = users.uid HAVING items.amount > 0 ORDER BY addtime DESC');
			
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					
					$fetch_all = $this->db->FetchAll();
					$this->InsertInfo($alias, $fetch_all);
				}else{
						
					$this->InsertInfo($alias, array());
				}
			}else{
				
			}
		}
		
		return $this->GetInfoArray()[$alias];
	}
	
	function GetItemIDs(){
		$merged = array();
		$ids = $this->GetItems(array('iid'));
		if(is_array($ids)){
			$merged = array_column($ids, 'iid');  /* Diziyi duzgun formata cevir */
		}
		return $merged;
	}
	
	function SetUserAbout($info){
		$alias ='info';
		$this->db->Prepare('UPDATE users SET info=:info WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('info', $info, 		PDO::PARAM_STR);
			
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				$this->InsertInfo($alias, $info);
				return true;
			}
		}
		return false;
	}
	function IsFollowedBy($userid){
	
		$this->db->Prepare('select flid FROM followlist WHERE uid=:uid and followed=:followed');
		$params[] = new CDBParam('uid',		 $userid, PDO::PARAM_INT);
		$params[] = new CDBParam('followed', $this->uid, 	 PDO::PARAM_INT);
	
	
		if($this->db->Execute($params)){
	
			if($this->db->RowCount() > 0){
	
				return true;
			}
		}
		return false;
	}
	function IsFollowing($userid){
		
		$this->db->Prepare('select flid FROM followlist WHERE uid=:uid and followed=:followed');
		$params[] = new CDBParam('uid',		 $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('followed', $userid, 	 PDO::PARAM_INT);
		
		
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0){
				
				return true;
			}
		}
		return false;
	}
	function GetDistanceWith($userid){
		if($userid > 0){
			$otherUser = new CUser($userid);
			$loc1 = $this->GetLocation();
			$loc2 = $otherUser->GetLocation();
			
			if((($loc1 instanceof  CLocation) && ($loc2 instanceof  CLocation)) && $loc1->IsValid() && $loc2->IsValid()){
				return CLocations::GetDistanceBetween($loc1, $loc2);
			}
			
		}else{
			return '?';
		}
	}
	
	function GetGroups(array $fields = array()){
		$alias = 'groups';
		$aliases = $fields;
		if(!isset($this->user_info[$alias])){
		
			foreach ($fields as $key=>$value){
					
				$fields[$key] = 'groups.'.$value.' as '.$aliases[$key];
			}
			$fields_query_str = implode(', ',$fields);
			
			$this->db->Prepare('SELECT '.$fields_query_str.' FROM groups, gmembers WHERE gmembers.uid = :uid and gmembers.gid = groups.gid');
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch_all = $this->db->FetchAll();
					$this->InsertInfo($alias, $fetch_all);
				}else{
		
					$this->InsertInfo($alias, null);
				}
			}else{
				
			}
		}
			
		return $this->GetInfoArray()[$alias];
	}
	
	function GetGroupIDs(){
		$ids = $this->GetGroups(array('gid'));
		$merged = array_column($ids, 'gid');  /* Diziyi duzgun formata cevir */
		return $merged;
	}
	
	function GetPicName(){
		if(file_exists(realpath('.').DIRECTORY_SEPARATOR.USER_PICS_DIR_NAME.DIRECTORY_SEPARATOR.$this->uid.'.jpg'))
		{
			return $this->uid.'.jpg';
		}else{
			return 'default.png';
		}
	}
	
	function GetPicFullPath(){
		$filepath = realpath('.').DIRECTORY_SEPARATOR.USER_PICS_DIR_NAME.DIRECTORY_SEPARATOR;
		$file 	  = $this->uid.'.jpg';
		if(file_exists($filepath))
		{
			return $filepath.$file;
		}else{
			return $filepath.'default.png';
		}
	}
	
	function GetItemMessages($message_way, $limit_start, $limit_end)
	{
		$returnVal = array();
		$whose_messages = 'desirer';
		if($message_way == 'inbox'){
			$whose_messages = 'itemowner';
		}
		
		$this->db->Prepare('SELECT * from itemmessages,(
                              SELECT imid, itemmessagers.iid, itemowner, desirer, owneragreed, desireragreed FROM itemmessagers, lastitemmessages
                              WHERE itemmessagers.".$whose_messages." = :userid and itemmessagers.imsgrsid = lastitemmessages.imsgrsid
                          ) AS lastmessage
                          WHERE lastmessage.imid = itemmessages.imid
                          ORDER BY itemmessages.imid DESC
                          LIMIT :limit_start, :limit_end');
		$params[] = new CDBParam('userid', $this->uid, PDO::PARAM_INT);
		$params[] = new CDBParam('limit_start', 0, PDO::PARAM_INT);
		$params[] = new CDBParam('limit_end', 100, PDO::PARAM_INT);

		if($this->db->Execute($params)){
			
			$return_val['count'] = $query->rowCount();
			
			if($this->db->RowCount() > 0){
				while($fetch = $this->db->Fetch()){
					
					$interlocutorID = ($this->uid == $fetch['itemowner'])?$fetch['desirer']:$fetch['itemowner'];
					$interlocutor = new CUser($interlocutorID, array('db' => $this->db));
					$item = new CItems($fetch['iid'], array('db' => $this->db));
					$return_val['messages'][] = array(
							'imsgrsid'     => $fetch['imsgrsid'],
							'iid'          => $fetch['iid'],
							'owner'        => $fetch['itemowner'],
							'desirer'      => $fetch['desirer'],
							'owneragreed'  => $fetch['owneragreed'],
							'desireragreed'=> $fetch['desireragreed'],
							'isread'       => $fetch['isread'],
							'time'         => $fetch['date'],
							'sender'       => $fetch['sender'],
							'itemname'     => $item->GetTitle(),
							'senderpic'    => $interlocutor->GetPic(),
							'sendername'   => $interlocutor->GetUsername(),
							'itempic'      => $item->GetMainPic(),
							'message'      => $fetch['message']);
					
				}
				
			}else{
				
			}
		}
	}
	
	
	
	
	
	function RemoveFromMaillist(){
	
	}
	
	function AddToMaillist(){
	
	}
	
	function GetEmail(){
		$returnVal = null;
		$this->db->Prepare('SELECT email FROM users WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				$fetch = $this->db->Fetch();
				$returnVal = $fetch['email'];
			}
		}
		return $returnVal;
	}
	
	function SetEmail(){
	
	}
	
	function GetItemCount(){
		
		return count($this->GetItemIDs());
	}
	

	
}