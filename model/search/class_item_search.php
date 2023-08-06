<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');

require_once(BASE_PATH.'/model/defs/global_definitions.php');

class CItemSearch extends CModelBaseWithDB
{
	
	private $term;
	
	function __construct($term, array $dependicies = array())
	{
		parent::__construct($dependicies);
		$this->term = $term;
	}
	
	public function SanitizeSearchTerm()
	{
		
	}
	public function Search(array $options = array())
	{
		$returnVal = array();
		
		$this->db->Prepare('SELECT iid, amount FROM items WHERE header LIKE :header HAVING amount > 0');
		$params[] = new CDBParam('header', '%'.$this->term.'%', PDO::PARAM_STR );
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0){
					
				require_once(BASE_PATH.'/model/items/class_items.php');
				require_once(BASE_PATH.'/model/in_list_getters/CItemInList.php');

				$itemInListGetter = new CItemInList();

				while($fetch = $this->db->Fetch()){

					$returnVal[] = $itemInListGetter->GetItem($fetch['iid']);

				}
			}
		
		}
		
		return $returnVal;
		
		
		
	}
	
	
}