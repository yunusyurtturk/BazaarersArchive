<?php
class CMyGroupsController
{
	private $action;
	private $uid;
	private $passcode;
	private $db;
	private $DIContainer;
	private $dependicies;

	function __construct($action,  array $dependicies = array()){

		$this->action   = $action;


		if(isset($dependicies['db'])){
			$this->db = $dependicies['db'];
		}else{
			$this->DIContainer = new CContainer();
			$this->db = $this->DIContainer->GetDBService();
		}



	}
	private function RunAction(){
		$returnVal = array();
		
		switch($this->action){
			
			default:
				$returnVal['groupCount'] = 0;
				$returnVal = array_merge((array)$returnVal, (array)$this->GetMyGroups());
				 
				
				
		}
		return $returnVal;
	}
	private function GetMyGroups(){
		$returnVal = array();
		$user = new CUser($this->uid, array('db' => $this->db));
		$userGroups = $user->GetGroups(array('gid', 'gname', 'gdescription', 'gpic'));
		
		foreach ($userGroups as $key=>$group){
			
			
			$returnVal['groups'][] = array('gid'=>$group['gid'], 'is_member'=> true, 'gname'=>$group['gname'], 'gdescription'=>$group['gdescription'], 'gpic'=>  get_grouppic($group['gid']));
			
		}
		return $returnVal;
	}
}