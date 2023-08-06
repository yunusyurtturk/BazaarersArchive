<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/DI/class_container.php');

abstract  class CBaseController
{

	protected $uid = 0;
	protected $passcode;
	protected $request;
	protected $db;
	
	function __construct(array $request, array $dependicies = array())
	{
		$this->request = $request; //Ilk satır!

		if((isset($request['uid']) && $this->GetRequest('uid') > 0) && (isset($request['passcode']))){
			$this->uid = $this->GetRequest('uid');
			$this->passcode = $this->GetRequest('passcode');
		}else if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && isset($_SESSION['uid']) && $_SESSION['uid'] > 0 && isset($_SESSION['passcode'])){
			$this->uid = $_SESSION['uid'];
			$this->passcode = $_SESSION['passcode'];

		}else{
			
		}
		
		if(isset($dependicies['db'])){
			$this->db = $dependicies['db'];
		}else{
			
			$this->DIContainer = new CContainer();
			$this->db = $this->DIContainer->GetDBService();
		}
		
		
	}
	function GetUid()
	{
		return $this->uid;

	}
	function LoggedIn(){

		if(isset($this->passcode) && !empty($this->passcode)) {
			if ($this->uid > 0 && !empty($this->passcode)) {

				$this->db->Prepare('SELECT uid FROM users WHERE uid=:uid AND passcode=:passcode');

				$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
				$params[] = new CDBParam('passcode', $this->passcode, PDO::PARAM_STR);

				if ($this->db->Execute($params)) {

					if ($this->db->RowCount() > 0) {

						return true;
					}
				}

			}
		}else{

			if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true && isset($_SESSION['uid']) && $_SESSION['uid'] > 0 && isset($_SESSION['passcode'])){

				return true;
			}
		}
			
		return false;

	}
	function GetRequest($key){
		$key = trim($key);
		
		if(isset($this->request[$key])){
			return trim($this->request[$key]);
		}
		return false;
	}
	function JSON($value){
		
		print(json_encode($value));
	}
	
	abstract  function RunAction();
	
}