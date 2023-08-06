<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');


class CBasicNewsOptions
{
	public $type;
	public $params;
	public $paramTypes;
	
	function __construct($format, $params, $paramTypes){
		if(!is_array($params)){
			$params = preg_split('/\s+/', $params);
		}
		if(!is_array($paramTypes)){
			$paramTypes = preg_split('/\s+/', $paramTypes);
		}
		$this->type = $format;
		$this->params = $params;
		$this->paramTypes = $paramTypes;
	}
}
class CUserNews extends CModelBaseWithDB
{
	
	private $id;
	private $uid;
	private $module_table_name = 'news';
	private $id_alias = 'nid';
	private $newsOptions;
	private $info_array = array();
	
	
	function __construct($id = 0, $uid, CBasicNewsOptions $options, array $dependicies = array())
	{
		parent::__construct($dependicies);
		
		$this->id = $id;
		$this->uid  = $uid;
		$this->info[$this->id_alias] = $id;
		
		$this->newsOptions = $options;

	}
	
	
	private function GetInfoArray()
	{
		return $this->info_array;
	}
	private function InsertInfo($key, $value){
		$this->info_array[$key] = $value;
	}
	private function GetId(){
	
		return $this->id;
	}
	function Delete()
	{
		$returnVal['error'] = true;
		
		$this->db->Prepare('DELETE FROM '.$module_table_name.' WHERE nid=:nid and uid=:uid');
		$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				$returnVal['error'] = false;
				$returnVal['is_deleted'] = true;
				$returnVal['nid'] = $nid;
				
			}
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
			$this->db->Prepare('SELECT '.$rows.' FROM '.$module_table_name.' WHERE '.$this->id_alias.' =:'.$this->id_alias);
			$params[] = new CDBParam($this->id_alias, $this->id, PDO::PARAM_INT );
				
			if($this->db->Execute($params)){
					
				if($this->db->RowCount() > 0){
	
					$fetch = $this->db->FetchAll();
					$this->InsertInfo('news', $fetch);
				}
	
			}
		}
	
	}
	function GetActionType()
	{
		require_once(BASE_PATH.'/model/news/class_news_defs.php');
		$actionType = false;
		
		switch($this->newsOptions->type){
			case NEWS_TYPE_ITEM_MESSAGE:
			case NEWS_TYPE_ITEM_EXCHANGED_INFORM_ITEMOWNER:
			case NEWS_TYPE_ITEM_EXCHANGED_INFORM_DESIRER:
			case NEWS_TYPE_AGREEMENT_ACHIVED_FOR_SELL:
			case NEWS_TYPE_AGREEMENT_ACHIVED_FOR_BUY:
			case NEWS_TYPE_ITEMOWNER_CONFIRMED_WAITS_FOR_YOU:
			case NEWS_TYPE_DESIRER_CONFIRMED_WAITS_FOR_YOU:
			case NEWS_TYPE_ITEMOWNER_CONFIRMED_WAITS_FOR_DESIRER:
			case NEWS_TYPE_DESIRER_CONFIRMED_WAITS_FOR_ITEMOWNER:

				$actionType = NEWS_ACTION_TYPE_ITEMMESSAGE;
				break;
			case NEWS_TYPE_ITEM_LIKE:
			case NEWS_TYPE_ITEM_COMMENT:
			case NEWS_TYPE_ITEMOWNER_AGREED_ON_GIVE:
			case NEWS_TYPE_NEW_ITEM_BY_FRIEND:

				$actionType = NEWS_ACTION_TYPE_ITEM;
				break;
			case NEWS_TYPE_USER_FOLLOWED:
				$actionType = NEWS_ACTION_TYPE_USER;
				break;

		}
		return $actionType;
	}
	function GetPrimaryTypeID()
	{
		require_once(BASE_PATH.'/model/news/class_news_defs.php');
		$primaryParamType = false;
		switch($this->newsOptions->type){
			case NEWS_TYPE_ITEM_MESSAGE:
			case NEWS_TYPE_ITEM_EXCHANGED_INFORM_ITEMOWNER:
			case NEWS_TYPE_ITEM_EXCHANGED_INFORM_DESIRER:
			case NEWS_TYPE_AGREEMENT_ACHIVED_FOR_SELL:
			case NEWS_TYPE_AGREEMENT_ACHIVED_FOR_BUY:
			case NEWS_TYPE_ITEMOWNER_CONFIRMED_WAITS_FOR_YOU:
			case NEWS_TYPE_DESIRER_CONFIRMED_WAITS_FOR_YOU:
			case NEWS_TYPE_ITEMOWNER_CONFIRMED_WAITS_FOR_DESIRER:
			case NEWS_TYPE_DESIRER_CONFIRMED_WAITS_FOR_ITEMOWNER:
				
				$primaryParamType = NEWS_PARAM_TYPE_ITEMMESSAGE;
			break;
			case NEWS_TYPE_ITEM_LIKE:
			case NEWS_TYPE_ITEM_COMMENT:
			case NEWS_TYPE_ITEMOWNER_AGREED_ON_GIVE:
			case NEWS_TYPE_NEW_ITEM_BY_FRIEND:
				
				$primaryParamType = NEWS_PARAM_TYPE_ITEM;
			break;
			case NEWS_TYPE_USER_FOLLOWED:
				$primaryParamType = NEWS_PARAM_TYPE_USER;
			break;
				
		}
		
		$primaryTypeIndex = array_search($primaryParamType, $this->newsOptions->paramTypes);
		
		if(!isset($this->newsOptions->params[$primaryTypeIndex])){
			$primaryTypeIndex = 0;
		}
		return $this->newsOptions->params[$primaryTypeIndex];
	}
	
	
	function GetNews($limit = 50)
	{
		/*
		$returnVal = array();
		$this->db->Prepare('SELECT * FROM news WHERE uid =:uid ORDER BY is_read, addtime DESC LIMIT :limit');
		$params[] = new CDBParam('uid', (int)$this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('limit', (int)$limit, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				$returnVal['count'] = $this->db->RowCount();
				while($fetch = $this->db->Fetch()){
					
					$returnVal['news'][] = array('nid'=>$fetch['nid'], 'format'=> $fetch['format'], 'primaryID'=>0, 'is_read'=> ($fetch['is_read']>0)?true:false, 
												 'date'=>  CMisc::TimeDiffToString($fetch['addtime']), 
												 'news'=> m_get_formatted_news( $format, $params, $paramtypes));
					
				}
			}
		}
		return $returnVal;*/
	}
	/**
	 * PrepareNews() metodundaki NEWS_TYPE_ITEM_MESSAGE gibi ifadeler $news_format 'a ornek olarak verilebilir
	 * NEWS_PARAM_TYPE_USER gibi ifadeler $param_type 'a ornek olarak verilebilir
	 * @param unknown $uid
	 * @param unknown $news_format
	 * @param array $params
	 * @param array $param_types
	 * @return string|number
	 */
	function Add($uid, $news_format, array $params,array $param_types, $primaryID = null){
		
		$returnVal['error'] = true;
		$returnVal['errCode'] = 0;
		$returnVal['nid'] = 0;
		if(is_array($params) && is_array($param_types)){
		
			if(sizeof($params) !== sizeof($param_types)){
				
				$returnVal['errCode'] = 1;
			}else if(sizeof($params) < 1 || sizeof($param_types) < 1){
				
				$returnVal['errCode'] = 2;
			}
			else{
				$returnVal['errCode'] = 8;
				$params = implode(' ', $params);
				$paramTypes = implode(' ', $param_types);
			
				$nid = $this->IsUserHasThisMessageBefore($uid, $news_format, $params, $paramTypes);
				if(0 !== $nid){
					$returnVal['errCode'] = 3;
					$returnVal['nid'] = $nid;
					if(!$this->SetExistingNewsAsNotRead($nid)){
						$returnVal['errCode'] = 5;
						$nid = 0;
					}
				}else{
					$returnVal['errCode'] = 4;
					$returnVal['nid'] = $this->InsertNewNews($uid, $news_format, $params, $paramTypes);
				}
			}
			
		}else{
			$returnVal['errCode'] = 6;
		}
		return $returnVal;
		
	}

	/**
	 * Bu fonksiyon dongu seklinde degil de sql sorgusundan toplu sekilde ekleme yapsa daha iyi olabilir
	 * Bu sefer de haber onceden var mı yoksa yok mu diye bakmak sıkıntı olacak ama
	 * @param array $uids
	 * @param $news_format
	 * @param array $params
	 * @param array $param_types
	 * @param null $primaryID
	 */
	function BulkAdd(array $uids, $news_format, array $params,array $param_types, $primaryID = null){

		$returnVal['error'] = true;
		$returnVal['errCode'] = 0;
		foreach ($uids as $uid){

			$this->Add($uid, $news_format, $params, $param_types, $primaryID);
		}


	}
	private function InsertNewNews($uid, $newsFormat, $newsParams, $paramTypes){
		
		$this->db->Prepare('insert into news(uid, format, params, paramtypes,addtime) values (:uid, :format, :params, :paramtypes,:addtime)');
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT );
		$params[] = new CDBParam('format', $newsFormat, PDO::PARAM_INT );
		$params[] = new CDBParam('params', $newsParams, PDO::PARAM_STR );
		$params[] = new CDBParam('paramtypes', $paramTypes, PDO::PARAM_STR );
		$params[] = new CDBParam('addtime', time(), PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0){
					
				$fetch = $this->db->Fetch();
				return $this->db->GetLastInsertID();
			}
		}
		
		return 0;
	}
	private function SetExistingNewsAsNotRead($nid){

		$this->db->Prepare('UPDATE news SET is_read=:isRead, addtime=:time WHERE nid=:nid');
		$params[] = new CDBParam('nid', $nid, PDO::PARAM_INT );
		$params[] = new CDBParam('isRead', 0, PDO::PARAM_INT );
		$params[] = new CDBParam('time', time(), PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			
			if($this->db->RowCount() > 0){
			
				return true;
			}
		}
		
		return false;
	}
	private function IsUserHasThisMessageBefore($uid, $newsFormat, $newsParams, $paramTypes){
		$this->db->Prepare('SELECT nid FROM news 
							WHERE uid=:uid AND format=:newsFormat AND params=:params AND paramtypes=:paramTypes  LIMIT 1');
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT );
		$params[] = new CDBParam('newsFormat', $newsFormat, PDO::PARAM_INT );
		$params[] = new CDBParam('paramTypes', $paramTypes, PDO::PARAM_INT );
		$params[] = new CDBParam('params', 	   $newsParams, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				
				$fetch = $this->db->Fetch();
				return $fetch['nid'];
			}
		}
		return 0;
		
	}
	private function FormatNews(array $param, array $param_types){
		$returnVal = array();
	}
	function GetFormattedNews(CBasicNewsOptions $newsOptions){
		
		require_once(BASE_PATH.'/model/news/english_user_news_format.php');

		$abstractNews = new CUserNewsFormatter($newsOptions, array('db' => $this->db));
		return $abstractNews->GetFormattedNews();

	}
	private function GetNewsImage($nid, $format){
		
	}
	private function GetNewsPrimaryType(){
		
	}
	
	
	
	
	
}