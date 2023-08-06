<?php
$basePath = $_SERVER["DOCUMENT_ROOT"].'/oop';
require_once($basePath.'/model/DI/class_container.php');

abstract  class CModelBaseWithDB
{
	protected $db;

	function __construct(array $dependicies = array()){



		if(isset($dependicies['db'])){
			$this->db = $dependicies['db'];
		}else{
				
			$this->DIContainer = new CContainer();
			$this->db = $this->DIContainer->GetDBService(true);
		}
		
	}
	function GetRequest($key){
		if(isset($this->request[$key])){
			return $this->request[$key];
		}
		return false;
	}


}