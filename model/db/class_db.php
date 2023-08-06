<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "132423132");
define("DB_NAME", "takas");

require('class_db_param.php');
require_once(BASE_PATH.'/model/error/class_error.php');


class CDBConnectionParams
{
	public $host;
	public $user;
	public $pass;
	public $dbname;
	function __construct(){
		$this->host = DB_HOST;
		$this->user = DB_USER;
		$this->pass = DB_PASS;
		$this->dbname = DB_NAME;
	}
}


class CDBConnection
{
	private $params;
	private $db;
	
	private $prepared;
	
	function __construct(CDBConnectionParams $db_params){
		
		$this->params = $db_params;
		$this->ConnectDB();
	}
	
	function ConnectDB()
	{
		
		$returnVal = false;
		
		try {
			$this->db = new PDO('mysql:host='.$this->params->host.';dbname='.$this->params->dbname.';charset=UTF8', $this->params->user, $this->params->pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			
			$returnVal = true;
		}
		catch(PDOException $e)
		{
			CError::LogError($e->getFile(), $e->getLine(), $e->getMessage());
		}
		return $returnVal;
	}
	function ErrorInfo(array $params = array()){
		
		return $this->prepared->errorInfo();
	}
	function EnableErrorInfo(){
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
	}
	
	function Execute(array $params = array()){
		/* Ilk elemana bak, obje degil ise sirali bir integer arrayidir. Direkt Execute'e parametre olarak gecir */
		if(isset($params[0]) && !is_object($params[0])){

			return $this->prepared->execute($params);
		}else{
			/* @var $param CDBParam */
			foreach ($params as $param){
		
				$this->prepared->bindParam($param->name, $param->value, $param->type);
					
			}
			
			return $this->prepared->execute();
		}
		
		
	}
	function RowCount(){
		return $this->prepared->rowCount();
	
	}
	function Fetch(){
		return $this->prepared->fetch(PDO::FETCH_ASSOC);
	
	}
	function FetchAll(){
		return $this->prepared->fetchAll(PDO::FETCH_ASSOC);
	
	}
	function Prepare($query){
		$this->prepared = $this->db->prepare($query);
	}
	function GetLastInsertID(){
		return $this->db->lastInsertId();
	}
	function Bind(array $values){
		
		/* @var $param CDBParam */
		foreach ($values as $param){
		
			$this->prepared->bindParam($param->name, $param->value, $param->type);
			
		}
		
	}
}

