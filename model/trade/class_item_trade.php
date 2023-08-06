<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');
require_once(BASE_PATH.'/model/items/class_items.php');

class CItemTrade extends CModelBaseWithDB
{
	
	private $id;
	private $iid;
	private $uid;
	
	
	private $module_table_name = 'itemmessagers';
	private $id_alias = 'imsgrsid';
	private $info = array();
	
	function __construct($imsgrsid = 0, $uid, array $dependicies = array())
	{
		parent::__construct($dependicies);
		
		$this->uid = $uid;
		$this->id = $imsgrsid;
		
		if($this->GetData(array('*'))){
			$this->iid = $this->info['iid'];
		}
	}
	
	function ToggleAgreement(){
		$returnVal['error'] = true;
		$item = new CItems($this->iid, array('db' => $this->db));
		if(!$item->IsSold()){
			if(!$this->IsItemExchangedPreviously()){

				if($this->UpdateAgreement()){

					$returnVal['error'] = false;
				}else{

					$returnVal['step'] = 2;
				}
			}else{
				$returnVal['step'] = 3;
			}
		}else{
			$returnVal['step'] = 1;
		}
		return $returnVal;
	}
	function GetDesirerID(){
		
		return $this->GetInfoArray()['desirer'];
	}
	function GetItemOwnerID(){
		return $this->GetInfoArray()['itemowner'];
	}
	function ToggleExchange(){
		$item = new CItems($this->iid, array('db' => $this->db));
		if(!$item->IsSold()){
			if(!$this->IsItemExchangedPreviously()){
				return $this->UpdateExchange();
			}
		}
		return false;
	}
	function GetTradingStates()
	{
		$alias = 'agreementStatus';
		if(!isset($this->GetInfoArray()[$alias])){
			
			$steps = array(false, false, false, false);
			$exchangeStatus  = $this->GetExchangeStatus();
			$agreementStatus = $this->GetAgreementStatus();
			if(true === $exchangeStatus['ex_status'] && $exchangeStatus['is_given']==true && $exchangeStatus['is_taken']==true){
				
				$steps = array(true, true, true, true);
			}else{
				
				if(true === $agreementStatus['owneragreed']){
					
					$steps[0] = true;
					if(true === $agreementStatus['desireragreed']){
						
						$steps[1] = true;
						if(true === $exchangeStatus['is_given']){
							
							$steps[2] = true;
						}else if(true === $exchangeStatus['is_taken']){ // En bastaki if ifadesinden dolayi burada sadece bir kosula giriyoruz
							
							$steps[3] = true;
						}
					}
				}
			}
			if($this->uid == $this->GetItemOwnerID()){
				
				$steps['itemowner'] = true;
			}else{
				$steps['itemowner'] = false;
			}
			$this->InsertInfo($alias, $steps);
		}
		return $this->GetInfoArray()[$alias];
		
	}
	function GetCheckboxStatus()
	{
		$tradingStates = $this->GetTradingStates();
		$returnVal = array(false, false, false, false);
		
		if($this->uid == $this->GetItemOwnerID()){
			$returnVal[0] = true; // Itemowner'in onay kutusu her halukarda aktif sekilde kalacak
			if(true == $tradingStates[1]){
				
				$returnVal[2] = true;
			}
		}else{
			if(true == $tradingStates[0]){
				
				$returnVal[1] = true;
				if($tradingStates[1]){
					$returnVal[3] = true;
				}
			}
		}
		return $returnVal;
	}
	private function GetAgreementStatus()
	{
		$returnVal = array();
		$returnVal['owneragreed'] = false;
		$returnVal['desireragreed'] = false;
		
		if($this->GetInfoArray()['owneragreed']){
			$returnVal['owneragreed'] = true;
		}
		if($this->GetInfoArray()['desireragreed']){
			$returnVal['desireragreed'] = true;
		}
		
		return $returnVal;
	}
	private function GetExchangeStatus()
	{
		$returnVal = array();
		$returnVal['ex_status'] = false;
		$returnVal['is_given']  = false;
		$returnVal['is_taken']  = false;
		
		$this->db->Prepare('SELECT * FROM itemexchanges WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
		
				$fetch = $this->db->Fetch();
				
				$returnVal['ex_status'] = true;
				$returnVal['is_given'] = ($fetch['is_given']==1)?true:false;
				$returnVal['is_taken'] = ($fetch['is_taken']==1)?true:false;
				
			}
		}
		
		return $returnVal;
	}
	private function IsPreviouslyMessaged()
	{
		$this->db->Prepare('SELECT * FROM itemmessagers WHERE '.$this->id_alias.'=:'.$this->id_alias.' AND iid=:iid   limit 1');
		$params[] = new CDBParam('iid', $this->iid, PDO::PARAM_INT );
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );

		if($this->db->Execute($params)){
			
			if($this->db->RowCount() > 0){
				return true;
			}
		}
		return false;
	}
    static function SIsPreviouslyMessaged($desirer, $iid, CDBConnection $db)
    {
        $item = new CItems($iid, array('db' => $db));

        if($desirer != $item->GetOwnerID()){

            $db->Prepare('SELECT * FROM itemmessagers WHERE desirer=:desirer AND iid=:iid   limit 1');
            $params[] = new CDBParam('iid', $iid, PDO::PARAM_INT );
            $params[] = new CDBParam('desirer', $desirer, PDO::PARAM_INT );

            if($db->Execute($params)){

                if($db->RowCount() > 0){
                    return true;
                }
            }
        }

        return false;
    }
	private function UpdateExchange()
	{
		if($this->IsPreviouslyMessaged()){
				
			if($this->InsertExchangeRecordIfNotExist()){
				
				if(!$this->IsItemExchangedPreviously()){ // Ürün alışverişi gerçekleşmiş ise Exchange durumu değiştirilememeli kontrolü
					
					if($this->uid == $this->GetInfoArray()['itemowner']){
						$whoIsAgreed = 'is_given';
					}else{
						$whoIsAgreed = 'is_taken';
					}
						
					$this->db->Prepare('UPDATE itemexchanges 
										SET '.$whoIsAgreed.'=
									    	IF('.$whoIsAgreed.' = 1, 0, 1) 
										WHERE '.$this->id_alias.'=:'.$this->id_alias);
					
					$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
					if($this->db->Execute($params)){
			
						if($this->db->RowCount() > 0){
							
							/* Now Check if exchanged by both sides, or not yet */
							if(!$this->IsItemExchangedPreviously()){ // Ürün alışverişi gerçekleşmiş ise Exchange durumu değiştirilememeli kontrolü
								/* Haberini iki tarafa da bildir */
							}else{ // Karsi tarafa haber gonder
								
							}
							return true;
						}
					}
				}
			}
			
		}
		return false;
	}
	private function InsertExchangeRecordIfNotExist()
	{
		$this->db->Prepare('select ieid from itemexchanges where imsgrsid=:'.$this->id_alias.' limit 1');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$this->db->Execute($params);
		if($this->db->RowCount() < 1)  //Eğer daha önce Aldım yada Verdim den birisi işaretlenmediyse, yeni kayıt oluştur
		{
			$create = $this->db->Prepare("INSERT INTO itemexchanges (imsgrsid, date, is_given, is_taken) values (:imsgrsid, :date, :is_given, :is_taken)");
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$params[] = new CDBParam('date', time(), PDO::PARAM_INT );
			$params[] = new CDBParam('is_given', 0, PDO::PARAM_INT );
			$params[] = new CDBParam('is_taken', 0, PDO::PARAM_INT );
			
			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					return true;
				}
			}
		}else{
			return true;
		}
		return false;
	}
	private function UpdateAgreement()
	{
		$isAgreementUpdateable = true;
		if($this->IsPreviouslyMessaged()){
			
			if($this->uid == $this->GetInfoArray()['itemowner']){
				$value = ($this->GetInfoArray()['owneragreed']==1)?"0, desireragreed=0":"1";
				$whoIsAgreed = 'owneragreed';
			}else{
				if(0 == $this->GetInfoArray()['owneragreed']){
					$isAgreementUpdateable = false;
				}
				$value = ($this->GetInfoArray()['desireragreed']==1)?"0":"1";
				$whoIsAgreed = 'desireragreed';
			}
			if($isAgreementUpdateable){
				$this->db->Prepare('UPDATE itemmessagers SET '.$whoIsAgreed.'='.$value.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
				$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
				if($this->db->Execute($params)){
					
					if($this->db->RowCount() > 0){
						return true;
					}
				}
			}else{
				echo '2';
			}
		}else{
			echo '1';
		}
		
		return false;
	}
	
	private function GetInfoArray()
	{
		return $this->info;
	}
	private function InsertInfo($key, $value){
		$this->info[$key] = $value;
	}
	
	function GetData(array $fields){
		
		if(is_array($fields)){
				
			if($fields[0] == '*'){
				$rows = '*';
			}else{
				foreach ($fields as $key){
			
					if(isset($this->GetInfoArray()[$key])){
							
						unset($fields[$key]);
					}else{
							
					}
				}
				$rows = implode(', ',$fields);
			}
				
			
			$this->db->Prepare('SELECT '.$rows.' FROM '.$this->module_table_name.' WHERE '.$this->id_alias.' =:'.$this->id_alias.' AND (itemowner=:uid or desirer=:uid)
								LIMIT 1');
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );

			if($this->db->Execute($params)){
				if($this->db->RowCount() > 0){
					$fetch = $this->db->Fetch();
					$this->info = array_merge($this->info, $fetch);
					return true;
					
				}
		
			}
		}
		return false;
		
	}
	/**
	 * İlgili imsgrsid 'nin trade 'i gerçekleşmiş mi bakar. Diğer imsgrsid 'lerle ilgilenmez
	 * Satılmış bir ürünün satış durumunun değiştirilmemesi için
	 */
	function IsItemExchangedPreviously()
	{
		$alias = 'mainpic';
		
		$set = false;
		$this->db->Prepare('SELECT * from itemexchanges
							WHERE imsgrsid=:imsgrsid and   (is_given=1 and is_taken=1) limit 1');
		$params[] = new CDBParam('imsgrsid', $this->id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			
			if($this->db->rowCount() > 0)
			{
				return true;
			
			}
		}
		return false;
	}
	function GetExchangeStates(){
		
	}

	
}