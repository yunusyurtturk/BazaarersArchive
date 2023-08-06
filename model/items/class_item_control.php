<?php
require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/log/logger.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');

class CItemControl extends CModelBaseWithDB
{
	private $id = 0;
	private $uid = 0;
	private $id_alias = 'iid';
	private $module_table_name = 'items';
	
 	public function __construct($iid, $uid, array $dependicies = array()){
 		
 		parent::__construct($dependicies);
 		
 		$this->id = $iid;
 		$this->uid = $uid;
 		

 	}
	private function DeleteRelatedEntries()
	{
		
		$this->RemoveRelatedComments();
		$this->RemoveRelatedMessages();
		$this->RemoveRelatedNews();
		$this->RemoveRelatedPics();
		
		return true;
	
	}
	private function RemoveRelatedNews()
	{
		return true;
	}
	private function RemoveRelatedPics()
	{
		$this->db->Prepare('SELECT filename FROM itempics WHERE iid=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$returnVal = true;
		if($this->db->Execute($params)){
			
			if($this->db->RowCount() > 0){
				
				$fetchAll = $this->db->FetchAll();
				$fetchAll = array_column($fetchAll, 'filename');  /* Diziyi duzgun formata cevir */
				foreach($fetchAll as $image){
					
					if(is_file(IMAGE_CFG_ITEMPICS_PATH.DIRECTORY_SEPARATOR.$image)){
						if(false === unlink(IMAGE_CFG_ITEMPICS_PATH.DIRECTORY_SEPARATOR.$image)){
							
							$returnVal = false;
						}
					}
				}
			}
		}
		
		return $returnVal;
		
	}
	/**
	 * Removes from itemmessagers and itemmessages
	 * @return boolean
	 */
	private function RemoveRelatedMessages()
	{
		
		$this->db->Prepare('DELETE itemmessagers, itemmessages 
							FROM itemmessagers
							LEFT JOIN itemmessages ON itemmessagers.imsgrsid = itemmessages.imsgrsid
							WHERE itemmessagers.iid = :'.$this->id_alias);
				
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$query = $this->db->Execute($params);

		if($query)
		{
			return true;
		}
		
		return false;
	}
	private function RemoveRelatedComments()
	{
		$this->db->Prepare('DELETE FROM itemcomments WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$query = $this->db->Execute($params);
		
		if($query)
		{
			return true;
		}
		
		return false;
	}
	private function IsOwner(){
		$this->db->Prepare('SELECT uid FROM '.$this->module_table_name.' WHERE '.$this->id_alias.'=:'.$this->id_alias.' AND uid=:uid');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		$query = $this->db->Execute($params);
		
		if($query)
		{
			if($this->db->RowCount() > 0){
				return true;
			}
		}
		
		return false;
	}
	private function IsAdmin(){
		return false;
	}
	private function IsAuthorized(){
		if($this->IsOwner() || $this->IsAdmin()){
			return true;
		}
		
		return false;
	}
	
	function RemoveItem()
	{
		$returnVal['error'] = true;
		$returnVal['message'] = _('Problem occured. Item cannot be removed');

		if($this->IsAuthorized()){
				
			if($this->DeleteRelatedEntries()){
				
				$this->db->Prepare('UPDATE '.$this->module_table_name.' SET amount = 0 WHERE '.$this->id_alias.'=:'.$this->id_alias);
				$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
				if($this->db->Execute($params)){
					
					if($this->db->RowCount()>0)
					{
						$returnVal['error'] = false;
						$returnVal['message'] = _('Item is removed');
	
					}
				}else{
					
					$logger = CLogger::GetLogger();
					$logger->Log($this->uid,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							13,
							'Remove item query couldnt executed'. $this->id);
					

				}
			}else{
				
				$logger = CLogger::GetLogger();
				$logger->Log($this->uid,
						__FUNCTION__,
						__CLASS__,
						func_get_args(),
						$_SERVER['PHP_SELF'],
						$_SERVER['QUERY_STRING'],
						11,
						'Not all related entries deleted when removing an item with id'. $this->id);
				
	
			}
			
		}else{
			$logger = CLogger::GetLogger();
			$logger->Log($this->uid,
					__FUNCTION__,
					__CLASS__,
					func_get_args(),
					$_SERVER['PHP_SELF'],
					CLogger::GetVarDumpStr($this),
					6,
					'Unauthorized attemt to remove item: '. $this->id);
			
			
		}
		return $returnVal;
	}
	function SetCategory($cid)
	{
		$alias = 'cid';
		$this->db->Prepare('UPDATE '.$this->module_table_name.' SET '.$alias.' = '.$cid.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				$this->InsertInfo($alias, $cid);
				return $this->GetInfoArray()[$alias];
	
			}
		}
	
		return false;
	}
	function SetPrice($price){
		$alias = 'price';
		$this->db->Prepare('UPDATE '.$this->module_table_name.' SET '.$alias.' = :'.$alias.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$params[] = new CDBParam($alias, $price, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				return true;
		
			}
		}
		
		return false;
	}
	function SetPriceType($type){
		$alias = 'priceType';
		$this->db->Prepare('UPDATE '.$this->module_table_name.' SET '.$alias.' = :'.$alias.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$params[] = new CDBParam($alias, $type, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				return true;
		
			}
		}
		
		return false;
	}
	function SetPriceUnit($unit){
		$alias = 'punit';
		$this->db->Prepare('UPDATE '.$this->module_table_name.' SET '.$alias.' = :'.$alias.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$params[] = new CDBParam($alias, $unit, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				return true;
		
			}
		}
		
		return false;
	}
	
}