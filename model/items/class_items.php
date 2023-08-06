<?php
require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/db/class_db.php');

require_once(BASE_PATH.'/model/image/images_cfg.php');

class CItems extends CModelBaseWithDB
{
	private $id;
	private $is_exist =false;
	private $module_table_name = 'items';
	private $id_alias = 'iid';
	private $info = array();
	
	
	function __construct($id, array $dependicies = array())
	{
		parent::__construct($dependicies);
		
		$this->id = $id;
		if($this->IsExist()){
			
			$this->is_exist = true;
		}
		$this->info[$this->id_alias] = $id;

	}
	function GetIsExist()
	{
		return $this->is_exist;
	}
	private function IsExist()
	{
		$this->db->Prepare('SELECT iid, amount FROM items WHERE '.$this->id_alias.' =:'.$this->id_alias.' HAVING amount > 0');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			
		if($this->db->Execute($params)){
		
			if($this->db->RowCount() > 0){
				
				return true;
			}
		
		}
		
		return false;
	}

    private function AddImageToDB($destFileName){

        $returnVal['error'] = true;

        $this->db->Prepare('INSERT INTO itempics (iid, filename) VALUES (:iid, :filename)');
        $params[] = new CDBParam('iid', $this->id, PDO::PARAM_INT );
        $params[] = new CDBParam('filename', $destFileName, PDO::PARAM_STR);

        if(!$this->db->Execute($params)){
            if($this->db->RowCount() > 0){

                $returnVal['error'] = false;
            }
        }

        return $returnVal;
    }

    function UpdateItemInfo(CNewItemInfo &$itemInfo)
    {
        $returnVal['error'] = true;

        $this->db->Prepare('UPDATE items SET uid=:uid, category=:category, header=:header,  description=:description,  mainpic = :mainpic, 
                            price = :price, priceType = :priceType, amount = :amount WHERE iid=:iid');
        $params[] = new CDBParam('iid',         $itemInfo->iid, PDO::PARAM_INT );

        $params[] = new CDBParam('uid',         $itemInfo->uid, PDO::PARAM_INT );
        $params[] = new CDBParam('category',    $itemInfo->category, PDO::PARAM_INT );
        $params[] = new CDBParam('header',      $itemInfo->title, PDO::PARAM_STR );
        $params[] = new CDBParam('description', $itemInfo->description, PDO::PARAM_STR );

        $params[] = new CDBParam('mainpic',     $itemInfo->mainpic, PDO::PARAM_STR );
        $params[] = new CDBParam('price',       $itemInfo->price, PDO::PARAM_INT );
        $params[] = new CDBParam('priceType',   $itemInfo->priceType, PDO::PARAM_INT );
        $params[] = new CDBParam('amount',      $itemInfo->count, PDO::PARAM_INT );

        if($this->db->Execute($params)){
            if($this->db->RowCount() > 0){
                $returnVal['error'] = false;
            }else{

                $returnVal['message'] = _('No record updated');
            }
        }else{
            $returnVal['message'] = _('Query not executed');
        }
        return $returnVal;
    }
	function GetInfo(){
		return $this->info;
	}
	private function GetInfoArray()
	{
		return $this->info;
	}
	private function InsertInfo($key, $value){
		$this->info[$key] = $value;
	}
	private function GetId(){
	
		return $this->id;
	}
	function SetData(array $fields){
		
		foreach($fields as $data=>$value){
			$this->InsertInfo($data, $value);
		}
		
	}
	function GetData(array $fields){
		
		if(is_array($fields)){
				
			foreach ($fields as $key){
		
				if(isset($this->GetInfoArray()[$key])){
						
					unset($fields[$key]);
				}else{
						
				}
			}
				
			$rows = implode(', ',$fields);
			$this->db->Prepare('SELECT '.$rows.' FROM items WHERE '.$this->id_alias.' =:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			
			if($this->db->Execute($params)){
				
				if($this->db->RowCount() > 0){
					$fetch = $this->db->FetchAll();
					$this->info = array_merge($this->info, $fetch[0]);
					return $this->info;
				}
		
			}
		}
		
		return false;
		
	}
	function IsSold()
	{
		return false;
	}
	function GetMainPic(){
		$alias = 'mainpic';
		
		$set = false;
		
		$this->db->Prepare('select '.$alias.' from items where '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		
		$this->db->Execute($params);
		
		if($this->db->rowCount()>0)
		{
			$fetch = $this->db->Fetch();
			$mainpic = $fetch[$alias];
			if(!empty($mainpic) ){
				
				
				if(file_exists(IMAGE_CFG_ITEMPICS_PATH.$mainpic))
				{
					$this->InsertInfo($alias, $mainpic);
					$set = true;
				}
			}
		

		}
		// Eger mainpic yoksa urun resimlerinden birisini mainpic olarak sec ve dondur
		if(false == $set){
			$params = array();
			$this->db->prepare('select filename as '.$alias.' from items, itempics where items.'.$this->id_alias.'=:'.$this->id_alias.' and items.'.$this->id_alias.'=itempics.'.$this->id_alias.' limit 1');
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			
			if($this->db->Execute($params)){
					
				if($this->db->RowCount() > 0){
			
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
					$set = true;
				}
			}
		}
		if(false == $set){
			
			$this->InsertInfo($alias,'not_found.jpg');
		}
		
		
		return $this->GetInfoArray()[$alias];
	}
	function SetMainPic($main_pic){
		
		$alias = 'mainpic';
		$this->db->Prepare('UPDATE '.$this->module_table_name.' SET '.$alias.' = '.$main_pic.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				$fetch = $query->fetch();
				$mainpic = stripslashes($fetch['mainpic']);
				if(file_exists('itempics/'.$mainpic))
				{
					$this->InsertInfo($alias,$mainpic);
				}
			
			
			}
		}
		
		return $this->GetInfoArray()[$alias];
	}

	private function RemoveNotExistedImages(array &$imageFileNames){

        foreach ($imageFileNames as &$image) {
            if(!file_exists(ITEM_PICS_PATH.$image)){

                $this->RemoveImage('notExisted', $image);
                unset($image);
            }
        }
    }
	function GetPics(){
		
		$alias = 'pics';
		if(!isset($this->GetInfoArray()[$alias])){
			$this->db->Prepare('SELECT filename FROM itempics WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );

			if($this->db->Execute($params)){
				
				if($this->db->RowCount()>0)
				{
					
					$fetchAll = $this->db->FetchAll();
					$fetchAll = array_column($fetchAll, 'filename');  /* Diziyi duzgun formata cevir */

                    $this->RemoveNotExistedImages($fetchAll);
					//array_walk($fetchAll, function(&$filename){$filename = IMAGE_CFG_ITEMPICS_PATH.$filename;});
					$this->InsertInfo($alias, $fetchAll);
				}
			}
		}
		if(isset($this->GetInfoArray()[$alias])){
			return $this->GetInfoArray()[$alias];
		}else{
			return '';
		}
	}
	function GetOwnerName(){
		$alias = 'username';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
			
			$this->db->Prepare('SELECT users.username as '.$alias.' FROM items, users WHERE '.$this->module_table_name.'.'.$this->id_alias.'=:'.$this->id_alias.' AND '.$this->module_table_name.'.uid = users.uid');
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			
			$query = $this->db->Execute($params);
			if($query)
			{
				
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}
		if(false == $set){
			$this->InsertInfo($alias, 'Itemowner Name?');
		}
		return $this->GetInfoArray()[$alias];
	
	}

	function AddImage($imageSrc, $destFileName){

	    $returnVal['error'] = true;
        if(move_uploaded_file($imageSrc, ITEM_PICS_PATH.$destFileName)){

            $returnVal['error'] = false;


            $result = $this->AddImageToDB($destFileName);
            if(true == $result['error']){

                $returnVal['error'] = false;
                $returnVal['removeUrl'] = '/edititem.php?action=removeImage';
            }else{

                $returnVal['reason'] = 'Failed to add record';
            }


        }else{

        }
        return $returnVal;

    }
	function GetOwnerID(){
		$alias = 'uid';
		$setxy = 0;
		if(!isset($this->GetInfoArray()[$alias])){

			$this->db->Prepare('SELECT users.uid as '.$alias.' FROM items, users WHERE '.$this->module_table_name.'.'.$this->id_alias.'=:'.$this->id_alias.' AND '.$this->module_table_name.'.uid = users.uid');
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$setxy =1;
					
					$fetch = $this->db->Fetch();

					$this->InsertInfo($alias, $fetch[$alias]);
					
				}
				
			}
			
		}
		if(!isset($this->GetInfoArray()[$alias]) && false == $setxy){
			$this->InsertInfo($alias, 0);
		}
		return $this->GetInfoArray()[$alias];
	}
	function GetAddTime(){
		$alias = 'addtime';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
		
			$this->db->Prepare('SELECT '.$alias.' FROM '.$this->module_table_name.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}
		if(false == $set){
			$this->InsertInfo($alias, 0);
		}
		return $this->GetInfoArray()[$alias];
	
	}
	static function SGetItemName($itemID)
	{
		$alias = 'header';
		$module_table_name = 'items';
		$id_alias = 'iid';
		
		$DIContainer = new CContainer();
		$db = $DIContainer->GetDBService(true);
		
		$db->Prepare('SELECT '.$alias.' FROM '.$module_table_name.' WHERE '.$id_alias.'=:'.$id_alias);
		$params[] = new CDBParam($id_alias, $itemID, PDO::PARAM_INT );
		$query = $db->Execute($params);
		if($query)
		{
			if($db->RowCount() > 0){
				$fetch = $db->Fetch();
				return $fetch[$alias];
			}
		}
		return null;
		
	}
	function GetTitle(){
		$alias = 'header';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
			$this->db->Prepare('SELECT '.$alias.' FROM '.$this->module_table_name.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}
		if(false == $set){
			$this->InsertInfo($alias, 0);
		}
		return $this->GetInfoArray()[$alias];
	
	}
	function GetDescription()
	{
		$alias = 'description';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
			$this->db->Prepare('SELECT '.$alias.' FROM '.$this->module_table_name.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
			if(false == $set){
				$this->InsertInfo($alias, '');
			}
		}
		
		return $this->GetInfoArray()[$alias];
	}
	function GetCategoryID(){
		$alias = 'category';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
		
			$this->db->Prepare('SELECT '.$alias.' FROM '.$this->module_table_name.' WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}
		if(false == $set){
			$this->InsertInfo($alias, 0);
		}
		return $this->GetInfoArray()[$alias];
	}
	function GetCategoryName(){
		$alias = 'catname';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
		
			$this->db->Prepare('SELECT categories.catname as '.$alias.' FROM items, categories WHERE '.$this->module_table_name.'.'.$this->id_alias.'=:'.$this->id_alias.'. AND '.$this->module_table_name.'.category = categories.cid');
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
		}
		if(false == $set){
			$this->InsertInfo($alias, 0);
		}
		return $this->GetInfoArray()[$alias];
	}
	
	
	function GetVirtualAmount(){
		$alias = 'amount';
		$this->db->Prepare('SELECT * from itemmessagers, itemexchanges WHERE itemmessagers.iid=:'.$this->id_alias.' AND itemmessagers.imsgrsid = itemexchanges.imsgrsid AND itemmessagers.owneragreed=1 and itemmessagers.desireragreed=1 AND (itemexchanges.is_given=0 or itemexchanges.is_taken=0)');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$this->db->Execute($params);
		$virtualGivens = $this->db->RowCount();
		$amount = $this->GetCount();
		
		//$returnVal = 0;
		//return ($itemData['amount'] - $queryNotGivens->rowCount());
		return ($amount - $virtualGivens);
	}
	function GetCount(){
		$alias = 'amount';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
	
			$this->db->Prepare('SELECT amount as '.$alias.' FROM items WHERE '.$this->module_table_name.'.'.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
			if(false == $set){
			
				$this->InsertInfo($alias, 0);
			}
		}
		
		return $this->GetInfoArray()[$alias];
	}
	/**
	 * Bu metod ornegin bir istekci urun sahibine mesaj atacagi zaman mesaj atabilir olup olmadigini kontrol icin 
	 * @param unknown $uid
	 * @return boolean
	 */
	function IsAboutToGiven($uid){
		
		$about_to_given = false;
		$this->db->Prepare('select * from itemmessagers where iid=:'.$this->id_alias.' AND (itemowner=:uid or desirer=:uid) and owneragreed=1 and desireragreed=1 limit 1');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT );
		$this->db->Execute($params);
		if($this->db->RowCount()>0) //Eğer ürün şu anki kişiye verilmek üzere anlaşıldıysa yada ürünün sahibi ise
		{
			$about_to_given = true;
		}
		return $about_to_given;
	}
	

	private function GetPriceValue(){
		$alias = 'price';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
				
			$this->db->Prepare('SELECT '.$alias.' FROM items WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
			
			if(false == $set){
				$this->InsertInfo($alias, '?');
			}
		}
		
		return $this->GetInfoArray()[$alias];
	}
	function GetPriceType(){
		$alias = 'priceType';
		$set = false;

		if(!isset($this->GetInfoArray()[$alias])){

			$this->db->Prepare('SELECT '.$alias.' FROM items WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{

				if($this->db->RowCount() > 0){

					$set =true;
					$fetch = $this->db->Fetch();

					$this->InsertInfo($alias, $fetch[$alias]);

				}
			}
		}else{
		    $set = true;
        }

		if(false == $set){
		    echo "**********************";
			$this->InsertInfo($alias, 0);
		}
		return $this->GetInfoArray()[$alias];
	}
	private function GetPriceUnit()
	{
		$alias = 'punit';
		$set = false;
		if(!isset($this->GetInfoArray()[$alias])){
	
			$this->db->Prepare('SELECT '.$alias.' FROM items WHERE '.$this->id_alias.'=:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
			$query = $this->db->Execute($params);
			if($query)
			{
				if($this->db->RowCount() > 0){
					$set =true;
					$fetch = $this->db->Fetch();
					$this->InsertInfo($alias, $fetch[$alias]);
				}
			}
			
			if(false == $set){
				$this->InsertInfo($alias, ' ');
			}
		}
		
		if(0 == $this->GetInfoArray()[$alias] || is_numeric($this->GetInfoArray()[$alias])){
			$this->InsertInfo($alias, ' ');
		}
		return $this->GetInfoArray()[$alias];
	}
	function GetPriceStr(){
		
		
		$type = $this->GetPriceType();
		$price = _("Free");
		
		if(PRICE_TYPE_DEAL == $type){
			$price = _("Deal");
		}else if(PRICE_TYPE_PRICE == $type){
			
			$price = _("Price").': '.$this->GetPriceValue().' '._($this->GetPriceUnit());
		}
		return $price;
		
	}

	function GetPrice()
    {
        $type = $this->GetPriceType();
        $price = _("Free");

        if(PRICE_TYPE_DEAL == $type){
            $price = _("Deal");
        }else if(PRICE_TYPE_PRICE == $type){

            $price = $this->GetPriceValue();
        }
        return $price;
    }
	function RemoveImage($uid, $imageName){

	    $returnVal = false;
	    if($this->GetIsOwner($uid) ||$uid == 'notExisted'){

            $this->db->Prepare('DELETE FROM itempics  WHERE iid=:iid and filename=:filename');
            $params[] = new CDBParam('iid', $this->id, PDO::PARAM_INT );
            $params[] = new CDBParam('filename', $imageName, PDO::PARAM_INT );
            $query = $this->db->Execute($params);
            if($query)
            {
                if($this->db->RowCount() > 0){
                    $returnVal = true;
                }
            }
        }

        return $returnVal;
    }
	function GetIsOwner($uid){
		if($this->GetOwnerID() == $uid){
			return true;
		}
		
		return false;
	}
	
	function GetImageCount()
	{
		$alias = 'imagecount';
		$relatedAlias = 'pics';
		if(!isset($this->GetInfoArray()[$alias])){
			
			if(!isset($this->GetInfoArray()[$relatedAlias]) ){
			
				$this->db->Prepare('SELECT '.$alias.' FROM items WHERE '.$this->id_alias.'=:'.$this->id_alias);
				$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
				$query = $this->db->Execute($params);
				if($query)
				{
					if($this->db->RowCount() > 0){
						$fetch = $this->db->Fetch();
						$this->InsertInfo($alias, $fetch[$alias]);
					}
				}
			}else{
				$count = count($this->GetInfoArray()[$relatedAlias]);
				$this->InsertInfo($alias, $count);
			}
		}

		if(!isset($this->GetInfoArray()[$alias])){

			$this->InsertInfo($alias, '');
		}
		return $this->GetInfoArray()[$alias];
		
		
	}
	
	static function SIsNew($time)
	{
		if((time() - $time) > (259200 * 6)){		/* 3 gun, 60*60*24*3 */

			return false;
		}
		return true;

	}
	
}