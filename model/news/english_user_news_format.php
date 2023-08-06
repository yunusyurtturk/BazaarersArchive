<?php
require_once('class_news_defs.php');


		
class CUserNewsFormatter  extends CModelBaseWithDB
{
	public $type;
	public $params =array();
	public $paramTypes = array();
	public $message;
	private $news;
	
	
	function __construct(CBasicNewsOptions $options,  array $dependicies = array()){
		
		parent::__construct($dependicies);
		
		$this->PrepareNews();
		
		$this->type = $options->type;
		$this->params = $options->params;
		$this->paramTypes = $options->paramTypes;
	}
	function GetFormattedNews(){
		
		$formattedParams = $this->FormatNewsWithParams();

		$news = vsprintf($this->news[$this->type], $formattedParams);
		return $news;
	}
	private function FormatNewsWithParams(){
		
		$formattedParams = array();

		foreach($this->params as $key=>$value){
			
			$formattedParams[] = $this->SwapParamAccordingToParamType($value, $this->paramTypes[$key]); // Param ile paramType'lari esitleyip uygun formattaki sonuclarini aliyoruz
		}
		return $formattedParams;
	}
	

	
	private function SwapParamAccordingToParamType($param, $paramType){
		
		switch($paramType)
		{
			case NEWS_PARAM_TYPE_USER: //userID
				require_once(BASE_PATH.'/model/users/class_user.php');
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				$user = new CUser($param, array('db' => $db));
				return $user->GetUsername($param);
				break;
			case NEWS_PARAM_TYPE_ITEM: //itemID
				require_once(BASE_PATH.'/model/items/class_items.php');
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				$item = new CItems($param, array('db' => $db));
				
				return $item->GetTitle();
				break;
			case NEWS_PARAM_TYPE_TIME: //date
				return date('d.m.Y', $param);
			case NEWS_PARAM_TYPE_ITEMMESSAGE: //Click here to read the item message
				return _('here'); //$param is sth like iid/uid
				break;
			case NEWS_PARAM_TYPE_GROUP: //Click here to read the item message
				return get_group_name($param); //$param is sth like iid/uid
				break;
		}
	}
	private function PrepareNews(){
		$this->news[NEWS_TYPE_ITEM_MESSAGE] = _('You received a message about your item %2$s by %1$s. Click to read your message ');
		$this->news[NEWS_TYPE_ITEM_LIKE] = _('%1$s liked your item %2$s. ');
		$this->news[NEWS_TYPE_ITEM_COMMENT] = _('Y%1$s commented about your item %2$s.');
		
		$this->news[NEWS_TYPE_ITEMOWNER_AGREED_ON_GIVE] = _('Owner of %1$s is agreed to give the item.');
		$this->news[NEWS_TYPE_NEW_ITEM_BY_FRIEND] = _(' %1$s added a new item %2$s.');
		$this->news[NEWS_TYPE_USER_FOLLOWED] = _(' %1$s has followed you.');
		
		$this->news[NEWS_TYPE_ITEM_EXCHANGED_INFORM_ITEMOWNER] = _(' Congrulations. You have succesfully given %1$s to %2$s.');
		$this->news[NEWS_TYPE_ITEM_EXCHANGED_INFORM_DESIRER] = _(' Congrulations. You have succesfully taken item %1$s from %2$s.');
		
		$this->news[NEWS_TYPE_AGREEMENT_ACHIVED_FOR_SELL] = _('%1$s is  agreed of getting your item %2$s.');
		$this->news[NEWS_TYPE_AGREEMENT_ACHIVED_FOR_BUY] = _('%1$s is  agreed of giving his/her item %2$s.');
		
		
		$this->news[NEWS_TYPE_ITEMOWNER_CONFIRMED_WAITS_FOR_YOU] = _('Owner of %1$s confirmed that he/she gave the item %2$s and waiting for your confirmation.');
		$this->news[NEWS_TYPE_DESIRER_CONFIRMED_WAITS_FOR_YOU] = _('%1$s confirmed he/she got the item %2$s and waiting for your confirmation.');
		
		$this->news[NEWS_TYPE_ITEMOWNER_CONFIRMED_WAITS_FOR_DESIRER] = _('You confirmed that you gave %1$s. Now waiting for response of %2$s.');
		$this->news[NEWS_TYPE_DESIRER_CONFIRMED_WAITS_FOR_ITEMOWNER] = _('You confirmed that you got %1$s from  %2$s. Now waiting for his/her response.');
		
	}
}



