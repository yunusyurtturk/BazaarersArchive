<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');

require_once(BASE_PATH.'/model/defs/global_definitions.php');

class CUserSearch extends CModelBaseWithDB
{
	
	private $term;
	private $searchingUser;
	
	function __construct($term, array $dependicies = array())
	{
		parent::__construct($dependicies);
		$this->term = $term;
	}
	
	public function SanitizeSearchTerm()
	{
		
	}
	public function SetSearchingUser(CUser &$user){
		$this->searchingUser = $user;
	}
	public function Search($startAt, array $options = array())
	{
		$returnVal = array();
		
		$this->db->Prepare('SELECT uid, username FROM users WHERE username LIKE :username');
		$params[] = new CDBParam('username', '%'.$this->term.'%', PDO::PARAM_STR );
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0){
					
				require_once(BASE_PATH.'/model/users/class_user.php');
				require_once(BASE_PATH.'/model/in_list_getters/CUserInList.php');

				$userInListGetter = new CUserInList();
				
				while($fetch = $this->db->Fetch()){

					$returnVal[] = $userInListGetter->GetUser($fetch['uid'], $searchingUser);


				}
			}
		
		}
		
		return $returnVal;
		
		
		
	}
	
	
}