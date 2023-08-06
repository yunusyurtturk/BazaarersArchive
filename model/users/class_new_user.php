<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/location/class_location.php');
require_once(BASE_PATH.'/model/location/class_locations.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');
require_once(BASE_PATH.'/model/users/users_defs.php');

class CNewUserInfo
{
	public $username;
	public $password;
	public $password2;
	public $email;
}
class CNewUser extends CModelBaseWithDB
{

	
	private $new_user_info;
	
	private $info;
	private $module_table_name ='users';
	
	
	function __construct(CNewUserInfo $new_user, array $dependicies = array())
	{
		parent::__construct($dependicies);
		$this->new_user_info = $new_user;
	}
	

	private function GetUserInfo()
	{
		
	}
	/**
	 * Email adresi veya username ile daha once kayit olunmus mu diye bakar
	 * @return boolean
	 */
	function IsUserCredientialsAvailable()
	{
		$returnVal['result'] = false;
		$this->db->Prepare('SELECT username, email FROM users WHERE username=:username or email=:email');
		$params[] = new CDBParam('username', $this->new_user_info->username, PDO::PARAM_STR );
		$params[] = new CDBParam('email', $this->new_user_info->email, PDO::PARAM_STR );
		$query = $this->db->Execute($params);
		
		if($query){
			if($this->db->RowCount() > 0){
				
				$fetch = $this->db->Fetch();
				if($fetch['username'] == $this->new_user_info->username){
					$returnVal['conflict'] = 'username';
				}else{
					$returnVal['conflict'] = 'email';
				}
			}else{
				
				$returnVal['result'] = true;
			}
		}
		return $returnVal;
	}
	private function IsPasswordValid()
	{
		$returnVal = false;
		if(empty($this->new_user_info->password) || empty($this->new_user_info->password2)){
			
			$returnVal = false;
		}else if(strlen($password)<6 || strlen($password)>16){
			
			$returnVal = false;
		}
	}
	private function IsPasswordsMatch(){
		if($this->new_user_info->password == $this->new_user_info->password2){
			return true;
		}else{
			return false;
		}
		
	}
	private function IsUsernameValid()
	{
		$returVal = false;
		if(empty($this->new_user_info->username)){
			
			return $returnVal;
		}else if(strlen($username)>16 || strlen($username)<2){
			
			return $returnVal;
		}else{
			$returnVal = true;
		}
		
		
		return $returnVal;
	}
	private function IsEmailValid()
	{
		$returnVal = false;
		
		if(filter_var($this->new_user_info->email, FILTER_VALIDATE_EMAIL)){
			$returnVal = true;
		}
		return $returnVal;
	}
	function IsUserCredientialsValid(&$errorMessage)
	{
		$returnVal = false;

		if(empty($this->new_user_info->username)){
			
			$errorMessage = _("Type your username");
		}else if(strlen($this->new_user_info->username) > 16|| strlen($this->new_user_info->username) < 2){
			
			$errorMessage = _("Username should be between 2-16 in length");
		}else if(empty($this->new_user_info->password)){
			
			$errorMessage = _("Type your password");
		}else if(empty($this->new_user_info->email)){
			
			$errorMessage = _("Type your email");
		}else if(strlen($this->new_user_info->password) < 6 || (strlen($this->new_user_info->password) > 16)){
			
			$errorMessage = _("Password should be between 6-16 in length");
		}else if(!(CMisc::IsEmail($this->new_user_info->email))){
			
			$errorMessage = _("Invalid email address");
		}else if(strlen($this->new_user_info->email) > 79){
			
			$errorMessage = _("Email adress is too long");
		}else if(strlen($this->new_user_info->email) < 5){ 
			
			$errorMessage = _("Email adress is too short");
		}else{
			$returnVal = true;
		}
		
		return $returnVal;
	}
	function Register(){
		require_once(BASE_PATH.'/model/defs/global_definitions.php');
		require_once(BASE_PATH.'/model/users/class_user.php');
		require_once(BASE_PATH.'/model/class_misc.php');
		
		$returnVal = array();
		$returnVal['result'] = ERR_NEW_USER_ERROR;
		$errorMessage;
		
		if($this->IsUserCredientialsValid($errorMessage)){
			
			$userCredit = $this->IsUserCredientialsAvailable();
			
			if(true == $userCredit['result']){
				
				$time = time();
				$passcode = CMisc::CreateRandomString(40);
				$activated = 1;
				$this->db->Prepare('INSERT INTO users (username, password, email, lastactive, lastactive2, signupdate, activated, regip, passcode) values 
																	   (:username, :password, :email, :lastactive, :lastactive2, :signupdate, :activated, :regip, :passcode)');
				$params[] = new CDBParam('username', $this->new_user_info->username, PDO::PARAM_STR );
				$params[] = new CDBParam('password', sha1($this->new_user_info->password), PDO::PARAM_STR );
				$params[] = new CDBParam('email', $this->new_user_info->email, PDO::PARAM_STR );
				$params[] = new CDBParam('lastactive',  $time, PDO::PARAM_INT );
				$params[] = new CDBParam('lastactive2', $time, PDO::PARAM_INT );
				$params[] = new CDBParam('signupdate',  $time, PDO::PARAM_INT );
				$params[] = new CDBParam('activated', $activated, PDO::PARAM_INT );
				$params[] = new CDBParam('regip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR );
				$params[] = new CDBParam('passcode', $passcode, PDO::PARAM_STR );
				
				if($this->db->Execute($params)){
					
					if($this->db->RowCount() > 0){
						
						$uid = $this->db->GetLastInsertID();
						
						$user = new CUserAccount($uid, array('db' => $this->db));
						$userLogin = $user->Login($this->new_user_info->username, $this->new_user_info->password);
						
						if(ERR_LOGIN_NO_ERROR == $userLogin['result']){
							
							$returnVal = array_merge($returnVal, $userLogin);
							$returnVal['error'] = ERR_NEW_USER_NO_ERROR;
							$returnVal['result'] = ERR_NEW_USER_NO_ERROR;
							$returnVal['message'] = $errorMessage;
						}else{
							$returnVal['result'] = ERR_NEW_USER_REGISTERED_BUT_LOGIN;
							$returnVal['message'] = _('Your account is created but cannot logged in. Try to log in again');
						}
						
					}else{
						$returnVal['message'] = _('Problem occured');
						$returnVal['result'] = ERR_NEW_USER_NO_AFFECTED_ROWS;
					}
				}else{
					$returnVal['result'] = ERR_NEW_USER_NOT_EXECUTED;
				}
			}else{
				
				$returnVal['result'] = ERR_NEW_USER_NOT_AVAILABLE;
				if('username' == $userCredit['conflict']){
					
					$returnVal['message'] = _('Username already taken');
				}else{
					
					$returnVal['message'] = _('An account with this email is already exist');
				}
			}
		}else{
			
			$returnVal['result'] = ERR_NEW_USER_INVALID_CREDIENTIALS;
			$returnVal['message'] = $errorMessage;

		}
	return $returnVal;
	}
}

