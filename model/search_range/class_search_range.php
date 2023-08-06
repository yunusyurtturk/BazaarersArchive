<?php


class CSearchRange
{
	public $location;
	private $db;
	
	function __construct(CLocation $location, array $params = array())
	{
		$this->location = $location;
		if(isset($params['db'])){
			
			$this->db = $params['db'];
		}
	}
	function SuggestCloseUsers($radius, $uid, $count)
	{
		$returnVal = array();
		$returnVal['result'] = 0;
		
		$this->db->Prepare('SELECT uid, username, lastactive, distance 
							FROM (SELECT users.uid, users.username, users.lastactive, locations.lid, locations.type, locations.lat, locations.lng,  ( 6371 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ) AS distance 
								FROM locations,users where locations.lid = users.lid and users.uid!=:uid  ORDER BY distance ASC LIMIT 13) AS suggestedUsers 
							ORDER BY RAND() LIMIT :count');
		$params[] = new CDBParam('lat',    strval($this->location->lat), 	PDO::PARAM_STR);
		$params[] = new CDBParam('lng',    strval($this->location->lng), 	PDO::PARAM_STR);
		$params[] = new CDBParam('uid',    $uid, PDO::PARAM_INT );
		$params[] = new CDBParam('radius', $radius, PDO::PARAM_INT );
		$params[] = new CDBParam('count',  $count, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				require_once(BASE_PATH.'/model/users/class_user.php');
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				if(!empty($uid)){
					
					$db2 = $DIContainer->GetDBService(true);
					$user = new CUser($uid, array('db' => $db2));
				}else{
					$user = null;
				}

				$userInListGetter = new CUserInList(array('db' => $db));

				while($fetch = $this->db->Fetch())
				{

					$returnVal['users'][] = $userInListGetter->GetUser($fetch['uid'], $user);

				}
			}else{
				$returnVal['result'] = 1;
			}
			
		}else{
			$returnVal['result'] = 1;
		}
		return $returnVal;
	}
	function UsersInRange($radius, $uid = 0, $page = 0, $page_size = 0)
	{
		$returnVal = array();
		
		$returnVal['more'] = false;
		$returnVal['prevPage'] = $page;
		
		if(isset($page) && $page_size !== 0){
			
			$limiter = 'LIMIT :start, :size';
		}else{
			$limiter = '';
		}
		$returnVal['count'] = 0;
		
		$queryTotalUserCount = 'SELECT COUNT(users.uid) as totalUserCount, 
							locations.lat as lat, 
							locations.lng as lng, 
							( 6371 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ) AS distance 
							FROM locations,users 
						    WHERE locations.lid = users.lid and users.uid!= :uid 
							HAVING distance < :radius
							LIMIT 1';
		$totalUserCount = 0;
		$params[] = new CDBParam('lat',    strval($this->location->lat), 	PDO::PARAM_STR);
		$params[] = new CDBParam('lng',    strval($this->location->lng), 	PDO::PARAM_STR);
		$params[] = new CDBParam('uid',    $uid, PDO::PARAM_INT );
		$params[] = new CDBParam('radius', $radius, PDO::PARAM_INT );
		
		$this->db->Prepare($queryTotalUserCount);
		if($this->db->Execute($params)){
			$fetch  = $this->db->Fetch();
			$totalUserCount = $fetch['totalUserCount'];
		}
		$returnVal['header-message'] = _('Listing users around you');
		$returnVal['totalCount'] = $totalUserCount;
		
		$this->db->Prepare('SELECT users.uid as uid, users.username as username, users.lastactive as lastactive, 
							locations.lid as lid, locations.type as loctype, locations.lat as lat, locations.lng as lng, 
							( 6371 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ) AS distance 
							FROM locations,users 
						    WHERE locations.lid = users.lid and users.uid!= :uid 
							HAVING distance < :radius
							ORDER BY distance '
							.$limiter);
		
		
		if(isset($page) && $page_size !== 0){
			$params[] = new CDBParam('start',  $page * $page_size, 	PDO::PARAM_INT);
			$params[] = new CDBParam('size',   $page_size, 	PDO::PARAM_INT);
		}
		
		if($this->db->Execute($params)){
			
			$returnVal['users'] = array();
			if($this->db->RowCount() > 0)
			{
				if($totalUserCount > (($page * $page_size) + $this->db->RowCount())){
					
					$returnVal['more'] = true;
					$returnVal['prevPage'] = $page++;
				}
				$returnVal['count'] = $this->db->RowCount();
				require_once(BASE_PATH.'/model/users/class_user.php');
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				$db2 = $DIContainer->GetDBService(true);
				
				if(0 !== $uid){
					$user = new CUser($uid, array('db' => $db));
				}else{
					$is_following = false;
				}

				$userInListGetter = new CUserInList();

				while($fetch = $this->db->Fetch())
				{
					$returnVal['users'][] = $userInListGetter->GetUser($fetch['uid'], $user);

				}
			}else{
				$returnVal['suggestedUsers'] = $this->SuggestCloseUsers($radius, $uid, $page_size);
			}
		}else{
			
		}
		
		return $returnVal;
	}
	private function &GetItemsOfUsers(array $fields = array(), array $uids = array(), $page, $page_size)
	{
		$returnVal = array();
		$returnVal['more'] = false;
		$returnVal['prevPage'] = $page;
		
		$fetchAll = array();

		if(count($uids) > 0){
			$inQuery = implode(',', $uids);
			foreach ($fields as $key=>$value){
					
				$fields[$key] = 'items.'.$value.' as '.$fields[$key];
			}
			$fields_query_str = implode(', ',$fields);
			$query = 'SELECT '.$fields_query_str.', items.amount as amount FROM items WHERE uid IN('.$inQuery.') HAVING amount > 0 LIMIT :start, :count';

			$queryTotalItemCount = 'SELECT COUNT(iid) as totalItemCount FROM items WHERE uid IN('.$inQuery.') HAVING amount > 0';
			$totalItemCount = 0;
			
			$this->db->Prepare($queryTotalItemCount);
			if($this->db->Execute()){
				$fetch  = $this->db->Fetch();
				$totalItemCount = $fetch['totalItemCount'];
			}
			
			$params[] = new CDBParam('start',    $page * $page_size, 	PDO::PARAM_INT);
			$params[] = new CDBParam('count',    $page_size, 	PDO::PARAM_INT);
			
			$this->db->Prepare($query);
				
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					
					if($totalItemCount > (($page * $page_size) + $this->db->RowCount())){
						
						$returnVal['more'] = true;
						$returnVal['prevPage'] = $page++;
					}
					
					$returnVal['items'] = $this->db->FetchAll();
				}else{
		
						
				}
			}else{
		
			}
				
		}else{
				
		}
		return $returnVal;
	}
	private function SuggestedCloseItems($uid, $limit){
		$returnVal = array();
		$returnVal['result'] = 1;
		
		$this->db->Prepare('SELECT * 
							FROM (select iid,header, items.uid, amount,category from items,(SELECT users.uid,locations.lid, locations.type, locations.lat, locations.lng, ( 6371 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ) AS distance FROM locations,users where  locations.lid=users.lid and users.uid != :uid   ORDER BY distance DESC) 
						    	as usersInRange 
								where usersInRange.uid=items.uid 
								HAVING items.amount > 0   
								ORDER BY usersInRange.distance ASC) 
							AS results ORDER BY RAND() LIMIT :limit');
		
		$params[] = new CDBParam('lat',    strval($this->location->lat), 	PDO::PARAM_STR);
		$params[] = new CDBParam('lng',    strval($this->location->lng), 	PDO::PARAM_STR);
		$params[] = new CDBParam('uid',    $uid, PDO::PARAM_INT );
		$params[] = new CDBParam('limit',  $limit, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			
			if($this->db->RowCount() > 0){
				
				require_once(BASE_PATH.'/model/items/class_items.php');
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				$db2 = $DIContainer->GetDBService(true);
				
				while($fetch = $this->db->Fetch())
				{
					$item = new CItems($fetch['iid'], array('db' => $db));
                    $returnVal['items'][] = array('iid'=>$fetch['iid'], 'header'=>$fetch['header'], 'amount'=>$fetch['amount'], 
                    		'price'=>$item->GetPriceStr(), 'priceStr' =>  $item->GetPriceStr(), 'itempic'=>$item->GetMainPic(), 'itemownerid' => $fetch['uid'], 'itemowner'=>CUser::SGetUsername($fetch['uid'], $db2) );
                    $returnVal['result'] = 0;
				}
			}else{
				$returnVal['result'] = 1;
			}
				
		}else{
			$returnVal['result'] = 1;
		}
		return $returnVal;
	}
	function ItemsInRange($radius, $uid = 0, $page, $page_size){
		
		$returnVal = array();

        $currentUser = new CUser($uid, array('db' => $this->db));

        $currentUserLocation = $currentUser->GetLocation();
		
		$returnVal['count'] = 0;
		$returnVal['header-message'] = _('Listing items around you');

		$users = $this->UsersInRange($radius, $uid);
		
		if(isset($users['users']) && $users['count'] > 0){
			
			$userIDs = array_column($users['users'], 'uid');
			
			$foundedItems = &$this->GetItemsOfUsers(array('iid', 'header', 'description', 'uid'), $userIDs, $page, $page_size);
			
			$returnVal['items'] = &$foundedItems['items'];
			$returnVal['more']  = &$foundedItems['more'];
			$returnVal['prevPage']  = &$foundedItems['prevPage'];
			
			require_once(BASE_PATH.'/model/in_list_getters/CItemInList.php');

			$itemInListGetter = new CItemInList($currentUserLocation);
			
			foreach ($returnVal['items'] as &$userItem){ /* By reference!! */

				$userItem = $itemInListGetter->GetItem($userItem['iid']);
			}
			$returnVal['count'] = count($returnVal['items']);
		}
		
		if($returnVal['count'] < 1){
			$returnVal['suggestedItems'] = $this->SuggestedCloseItems($uid, 10);
		}
		
			

		
		return $returnVal;
	}
	function GroupsInRange($radius, $uid, $page, $page_size)
	{
		$returnVal = array();
		$returnVal['count'] = 0;
		
		$this->db->Prepare('SELECT groups.gid,groups.gname,locations.lid, locations.type, locations.lat, locations.lng, ( 6371 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ) AS distance 
						    FROM locations,groups 
							WHERE  locations.lid=groups.lid  
							HAVING distance < :radius 
							ORDER BY groups.gid DESC');
		
		$params[] = new CDBParam('lat',    strval($this->location->lat), 	PDO::PARAM_STR);
		$params[] = new CDBParam('lng',    strval($this->location->lng), 	PDO::PARAM_STR);
		$params[] = new CDBParam('radius', $radius, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			$returnVal['groups'] = array();
			if($this->db->RowCount() > 0)
			{
				$returnVal['count'] = $this->db->RowCount();
				require_once(BASE_PATH.'/model/group/class_group.php');
				
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				
				while($fetch = $this->db->Fetch())
				{
					$groupInRange = new CGroup($fetch['gid'], array('db' => $db));
					$isGroupMember = $groupInRange->HasMember($uid);
					$returnVal['groups'][] = array('gid'=>$fetch['gid'], 'is_member'=> $isGroupMember, 'gname'=>strip_tags($fetch['gname']), 'gpic'=> $groupInRange->GetPic(), 'itemcount'=> $groupInRange->GetItemCount(), 'membercount'=> $groupInRange->GetMemberCount());
				}
			}else{
				$returnVal['suggestedGroups'] = $this->SuggestedCloseGroups($uid, 10);
			}
		}
		return $returnVal;
	}
	private function SuggestedCloseGroups($uid, $limit){
		$returnVal = array();
		$returnVal['result'] = 1;
	
		$this->db->Prepare('SELECT * FROM (SELECT groups.gid,groups.gname,locations.lid, locations.type, locations.lat, locations.lng, ( 6371 * acos( cos( radians(:lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) * sin( radians( lat ) ) ) ) AS distance FROM locations,groups where  locations.lid=groups.lid  ORDER BY distance DESC LIMIT 13) 
							AS suggestedGroups 
							ORDER BY RAND() 
							LIMIT :limit');
	
		$params[] = new CDBParam('lat',    strval($this->location->lat), 	PDO::PARAM_STR);
		$params[] = new CDBParam('lng',    strval($this->location->lng), 	PDO::PARAM_STR);
		$params[] = new CDBParam('limit',  $limit, PDO::PARAM_INT );
	
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				require_once(BASE_PATH.'/model/group/class_group.php');
				
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				
				while($fetch = $this->db->Fetch())
				{
					$suggestedGroup = new CGroup($fetch['gid'], array('db' => $db));
					$returnVal['groups'][] = array('gid'=>$fetch['gid'], $suggestedGroup->HasMember($uid), 'gname'=>strip_tags($fetch['gname']), 'gpic'=>  $suggestedGroup->GetPic(), 'itemcount'=>  $suggestedGroup->GetItemCount(), 'membercount'=>$suggestedGroup->GetMemberCount());
        		}
			}else{
				$returnVal['result'] = 1;
			}
	
		}else{
			$returnVal['result'] = 1;
		}
		return $returnVal;
	}
	
	
	
}