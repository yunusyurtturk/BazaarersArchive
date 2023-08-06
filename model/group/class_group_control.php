<?php

class CGroupControl
{
	private $id; //gid
	private $db;
	
	function __construct($gid, array $params = array()){
		$this->id = $gid;
		if(isset($params['db'])){
				
			$this->db = $params['db'];
		}
	}
	function IsMember($uid)
	{
		$query = $this->db->Prepare('select gid from gmembers where gid=? and uid=?');
		$params[] = new CDBParam('gid',  $this->id, PDO::PARAM_INT);
		$params[] = new CDBParam('uid',  $uid, PDO::PARAM_INT);
		
		$this->db->Execute($params);
		if($this->db->RowCount() > 0){
			return true;
		}else{
			return false;
		}
	}
	function AddMember($uid){
		
		if($this->IsMember()){
			$time=time();
			$query = $this->db->Prepare('insert into gmembers(gid,uid,gmembertime) values (:gid, :uid, :time)');
			$params[] = new CDBParam('gid',  $this->id, PDO::PARAM_INT);
			$params[] = new CDBParam('uid',  $uid, PDO::PARAM_INT);
			$params[] = new CDBParam('time', $uid, PDO::PARAM_INT);
			
			return $this->db->Execute($params);
		}else{
			return false;
		}

	}
	private function RemoveAllMembers(){
		$this->db->Prepare('delete from gmembers where gid=:gid');
		$params[] = new CDBParam('gid', $this->id, PDO::PARAM_INT);
		$executed = $this->db->Execute($params);
		
		if($executed){
			if($this->db->RowCount() > 0){
				
			}else{
				
			}
			return ERROR_NO_ERROR;
		}
			
		else
			return ERROR_ERROR;
	}
	private function GetLid(){

		$this->db->Prepare('select lid as '.$alias.' from groups where gid=:gid');
		$params[] = new CDBParam('gid', $this->id, PDO::PARAM_INT);
	
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				return $fetch['lid'];
			}
		}

		return 0;
	}
	function SetPic($admin, $locale){
		
	}
	function RemoveGroup(CLocation $loc, $admin, $locality){
		
	}
	function RemoveMember($uid){
		$this->db->Prepare('delete from gmembers where gid=:gid and uid=:uid');
		$params[] = new CDBParam('gid', $this->id, PDO::PARAM_INT);
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT);
		$executed = $this->db->Execute($params);
		
		if($executed && $this->db->RowCount() > 0)
			return ERROR_NO_ERROR;
		else
			return ERROR_ERROR;
	}
	function GetDistanceBetween(CLocation $loc1, CLocation $loc2){
		
	}
	
}