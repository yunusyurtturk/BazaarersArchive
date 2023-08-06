<?php

class CGroup
{
	private $location;
	private $id;
	private $info_array = array();
	private $db;
	private $module_table_name;
	private $user_info = array();
	private $id_alias = 'gid';
	
	function __construct($id = 0, array $params = array())
	{
		$this->module_table_name = 'groups';
		$this->SetId($id);
		if(isset($params['db'])){
				
			$this->db = $params['db'];
		}
		$this->Init($id);
	}
	function GetPic(){
		
	}
	function GetItemCount(){
		
	}
	function GetMemberCount()
	{
		
	}
	private function Init($id, array $fields = array()){
		
		if(count($fields) > 0){
			foreach ($fields as $key){
			
				if(isset($this->GetInfo()[$key])){
			
					echo $key.' =  degeri daha once set edildigi icin tekrar sorgulanmayacak<br />';
					unset($fields[$key]);
				}else{
			
					echo $key.' degeri aranacak<br />';
				}
			}
			$rows = implode(', ',$fields);
			$this->db->Prepare('select $rows from '.$this->module_table_name.' where '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id_alias, PDO::PARAM_INT);
		
		
			echo '<b>'."select $rows from '.$this->module_table_name.' where '.$this->id_alias.'=:'.$this->id_alias.'".'<b />';
			if($this->db->Execute($params)){
					
				if($this->db->RowCount() > 0){
		
					$fetch = $this->db->fetch();
				}
			}
		}
	}
	private function InsertInfo($key, $value){
		$this->info_array[$key] = $value;
	}
	private function GetId(){

		return $this->id;
	}
	private function SetId($id){
		$alias = 'id';
		if(0 != $id){
			
			$this->id = $id;
			$this->InsertInfo($alias, $this->id);
		}
	}
	private function GetInfoArray()
	{
		return $this->info_array;
	}
	function GetData(array $fields = array())
	{
		if(is_array($fields)){
				
			foreach ($fields as $key){
		
				if(isset($this->GetInfo()[$key])){
						
					echo $key.' =  degeri daha once set edildigi icin tekrar sorgulanmayacak<br />';
					unset($fields[$key]);
				}else{
						
					echo $key.' degeri aranacak<br />';
				}
			}
				
			$rows = implode(', ',$fields);
			$this->db->Prepare('SELECT '.$rows.' FROM groups WHERE gid=:gid');
			$params[] = new CDBParam('gid', $this->id, PDO::PARAM_INT );
			if($this->db->Execute($params)){
					
				if($this->db->RowCount() > 0){
						
					$fetch = $this->db->Fetch();
					$this->info_array = array_merge($this->info_array, $fetch);
				}
		
			}
		}
		
		return $this->GetInfoArray();
	}
	function GetLocation()
	{
		$alias = 'location';
		if(!isset($this->GetInfoArray()[$alias])){
		
			$this->db->Prepare('SELECT locations.lat as lat, locations.lng as lng FROM locations, '.$this->module_table_name.' WHERE '.$this->module_table_name.'.'.$this->id_alias.' = :'.$this->id_alias.' and '.$this->module_table_name.'.lid = locations.lid');
			$params[] = new CDBParam($this->id_alias, $this->GetId(), PDO::PARAM_INT);
		
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, new CLocation($fetch['lat'], $fetch['lng']));
				}else{
					
					$this->InsertInfo($alias, null);
				}
			}else{
				
			}
		}
			
		return $this->GetInfoArray()[$alias];
	}
	function GetMemberIDs()
	{
		$ids = $this->GetMembers(array('uid'));
		$merged = array();
		if(is_array($ids)){
			$merged = array_column($ids, 'uid');  /* Diziyi duzgun formata cevir */
		}
		return $merged;
	}
	function HasMember($uid)
	{
		$this->db->Prepare('select gid from gmembers where gid=:gid and uid=:uid');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT);
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				return true;
			}
			
		}
		return false;
		
	}
	function GetMembers(array $fields = array())
	{
		$alias = 'members';
		$table_name = 'gmembers';
		$aliases = $fields;
		if(!isset($this->GetInfoArray()[$alias])){
		
			foreach ($fields as $key=>$value){
					
				$fields[$key] = 'users.'.$value.' as '.$aliases[$key];
			}
			$fields_query_str = implode(', ',$fields);
			$query = 'SELECT '.$fields_query_str.' FROM '.$table_name.', users WHERE '.$table_name.'.'.$this->id_alias.'=:'.$this->id_alias.' and '.$table_name.'.uid = users.uid';
			echo '<br /><b>'.$query.'<b />';

			$this->db->Prepare($query);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT);

			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch_all = $this->db->FetchAll();
					echo 'Buradayiz';
					$this->InsertInfo($alias, $fetch_all);
				}else{
		
					$this->InsertInfo($alias, null);
				}
			}else{
				
			}
		}
			
		return $this->GetInfoArray()[$alias];
		

	}
	function GetItems(array $fields = array())
	{
		
		/* Once memberlar'in IDsini cek */
		$members = $this->GetMemberIDs();
		return $this->GetItemsOfGroupMembers($fields, $members);
		
		
	}
	private function GetItemsOfGroupMembers(array $fields = array(), array $uids = array()){

		$fetchAll = array();
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
		
	
		return $fetchAll;
	}

	
}