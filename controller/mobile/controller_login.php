<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/users/class_user_account.php');

class CLoginController extends CBaseController
{
	private $action;
	private $email;
	private $password;
	
	private $location;
	private $radius;
	
	function __construct($request, array $dependicies = array()){
		
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');;
		$this->email = $this->GetRequest('email');
		$this->password = $this->GetRequest('password');

	}
	function RunAction(){
		
		$returnVal = array();
		switch($this->action){
			
			default:
				$email= $this->GetRequest('email');
				$password= $this->GetRequest('password');
				$formapproved= $this->GetRequest('formapproved');
				
				$returnVal = array_merge((array)$returnVal, (array)$this->Login($email, $password));

                if(ERR_LOGIN_NO_ERROR == $returnVal['result']){
                    $this->uid = $returnVal['userid'];

                }
				
		}
		
		return $returnVal;
		
	}
	private function Login($loginId, $password)
	{
		$returnVal = array();
		$userAccount = new CUserAccount(0, array('db' => $this->db));
		return $userAccount->Login($loginId, $password);
	}
}
