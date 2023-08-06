<?php


require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/db/class_db.php');


class CContainer
{
	
	protected $parameters 	 = array();
	static protected $shared = array();
	
	
	
	public function __construct(array $parameters = array())
	{
		
		if(isset($parameters['db.params'])){
			$this->parameters['db.params'] = $parameters['db.params'];
		}else{
			$this->parameters['db.params'] = new CDBConnectionParams();
		}
	
		$this->parameters['user.params'] = (isset($parameters['user.params']))?$parameters['user.params']:'';
		
	}
	
	public function GetDBService($newInstance = false){
		
		if(true === $newInstance){
			$params = $this->parameters['db.params'];
			$db = new CDBConnection($params);
			
			return $db;
		}else{
		
			if(isset(self::$shared['db'])){
				
				return self::$shared['db'];
			}else{
				
				$params = $this->parameters['db.params'];
				$db = new CDBConnection($params);
				
				self::$shared['db'] = $db;
				
				return $db;
			}
		}
		
	}
	public function GetUserService($uid){
	
		if(isset(self::$shared['user'])){
				
			return self::$shared['user'];
		}else{
				
			$user = new CUser($uid, array('db' => $this->GetDBService()));
			self::$shared['user'] = $user;
				
			return self::$shared['user'];
		}
	
	}
	public function GetUserAccountService($uid){
		
		$alias = 'useraccount';
		if(isset(self::$shared[$alias])){
	
			return self::$shared[$alias];
		}else{
	
			$user = new CUserAccount($uid, array('db' => $this->GetDBService()));
			self::$shared[$alias] = $user;
	
			return self::$shared[$alias];
		}
	
	}
	public function GetGroupService($gid){
	
		$alias = 'group';
		if(isset(self::$shared[$alias])){
	
			return self::$shared[$alias];
		}else{
	
			$group = new CGroup($gid, array('db' => $this->GetDBService()));
			self::$shared[$alias] = $group;
	
			return self::$shared[$alias];
		}
	
	}
	public function GetImageService(){
	
		$alias = 'image';
		if(isset(self::$shared[$alias])){
	
			return self::$shared[$alias];
		}else{
	
			$group = new CGroup($gid, array('db' => $this->GetDBService()));
			self::$shared[$alias] = $group;
	
			return self::$shared[$alias];
		}
	
	}
	
	
}