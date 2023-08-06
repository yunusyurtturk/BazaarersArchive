<?php
require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/log/logger.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');

class CAllItems extends CModelBaseWithDB
{

 	public function __construct(array $dependicies = array()){
 		
 		parent::__construct($dependicies);
 		


 		

 	}
	function GetLastItems(array $fields = array(), array $filters = array('start' => 0, 'size' => 20)){
		$returnVal = array();
		require_once(BASE_PATH.'/model/items/class_items.php');

			foreach ($fields as $key=>$value){

				$fields[$key] = 'items.'.$value;
			}
			$fields_query_str = implode(', ',$fields);
			$this->db->Prepare('SELECT '.$fields_query_str.', amount FROM items HAVING items.amount > 0 ORDER BY addtime DESC LIMIT :start, :size');

			$params[] = new CDBParam('start', $filters['start'], PDO::PARAM_INT);
			$params[] = new CDBParam('size', $filters['size'], PDO::PARAM_INT);
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){

					$returnVal = $this->db->FetchAll();
				}
			}else{

			}


		return $returnVal;


	}
	
}