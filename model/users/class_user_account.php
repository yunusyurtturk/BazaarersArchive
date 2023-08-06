<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/log/logger.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');
require_once(BASE_PATH.'/model/class_misc.php');

define('FORGETTEN_PASSWORD_ERROR', 0);
define('FORGETTEN_PASSWORD_LAST_KEY_STILL_FRESH', FORGETTEN_PASSWORD_ERROR + 1);
define('FORGETTEN_PASSWORD_LAST_KEY_UPDATED', FORGETTEN_PASSWORD_ERROR + 2);
define('FORGETTEN_PASSWORD_NEW_KEY_CREATED', FORGETTEN_PASSWORD_ERROR + 3);
define('FORGETTEN_PASSWORD_TOO_MANY_ATTEMT', FORGETTEN_PASSWORD_ERROR + 4);

define('UPDATE_PASSWORD_ERROR', 0);
define('UPDATE_PASSWORD_NO_ERROR', UPDATE_PASSWORD_ERROR + 1);
define('UPDATE_PASSWORD_NEW_PASSWORD_LINK_OUTDATED', UPDATE_PASSWORD_ERROR + 2);

class CPasswordResetMailContents
{
	public $header;
	public $content;
	function __construct($username, $uid, $newPassword, $keycode)
	{
		$this->header = _('Bazaarers Password Reset');
		$this->content = sprintf(
				_('<b>Hello %1$s</b><br />
				<p>We received that you forget your email and want to reset it. 
				Click the link below and we\'ll update your password. 
				<br />
				This link will be active for 12 hours.<br />
				Your new password is: <b>%2$s</b><br />
				<br />
				<a href="https://'.$_SERVER["SERVER_NAME"].'/oop/web/register.php?action=reset&userid=%3$s&keycode=%4$s">Update Password </a>

				</p>
				'),$username, $newPassword, $uid, $keycode);
		
		
	}
}

class CUserAccount extends CModelBaseWithDB
{
	private $uid;
	private $module_table_name = 'users';
	
	private function GenerateNewPassword(){
		
		return CMisc::CreateRandomString(8);
	}
	function __construct($uid = 0, array $dependicies = array())
	{
		parent::__construct($dependicies);
		
		$this->uid = $uid;
	}
	private function RemoveUsedForgettenPasswordEntry($fpid)
	{
		$this->db->Prepare('DELETE FROM forgottenpasswords WHERE fpid=:fpid');
		$params[] = new CDBParam('fpid', $fpid, PDO::PARAM_STR );
		
		if($this->db->Execute($params)){
			
			return true;
			
		}
		
		return false;
	}
	private function IsKeyCodeValid($keycode)
	{
		$returnVal['error'] = true;
		$returnVal['errCode'] = 0;
		
		$this->db->Prepare('SELECT fpid, newpassword, keytime FROM forgottenpasswords WHERE uid=:uid AND confirmkey=:keycode');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_STR );
		$params[] = new CDBParam('keycode', $keycode, PDO::PARAM_STR );
			
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				$fetch = $this->db->Fetch();
				if($fetch['keytime'] > (time() - 60*60*4)){
					
					$returnVal['error'] = false;
					$returnVal['newPassword'] = $fetch['newpassword'];
					$returnVal['errCode'] = UPDATE_PASSWORD_NO_ERROR;
					$returnVal['fpid'] = $fetch['fpid'];
				}else{
					echo time();
					$returnVal['errCode'] = UPDATE_PASSWORD_NEW_PASSWORD_LINK_OUTDATED;
				}
				
			}
		}
		
		return $returnVal;
		
	}
	function UpdateForgettenPassword($keycode)
	{
		$returnVal['error'] = true;
		$returnVal['errCode'] = UPDATE_PASSWORD_ERROR;
		if($this->uid != 0){
			
			$keyCodeResult = $this->IsKeyCodeValid($keycode);
			
			if($keyCodeResult['error'] === false){
				
				$returnVal['newPassword'] = &$keyCodeResult['newPassword'];
				$updatePasswordResult = $this->UpdatePassword($keyCodeResult['newPassword']);
				if($updatePasswordResult['error'] === false){
					
					$returnVal['error'] = false;
					if(!$this->RemoveUsedForgettenPasswordEntry($keyCodeResult['fpid'])){
						
						$logger = CLogger::GetLogger();
						$logger->Log($this->uid,
								__FUNCTION__,
								__CLASS__,
								func_get_args(),
								$_SERVER['PHP_SELF'],
								$_SERVER['QUERY_STRING'],
								12,
								'Keycode entry couldnt be deleted from forgettenpasswords, fpid = '. $keyCodeResult['fpid'] );
					}
				}else{
					
					$logger = CLogger::GetLogger();
					$logger->Log($this->uid,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							21,
							'Update forgetten password event, Keycode is valid but cant updated passwor. Keycode = '. $keycode );
						
				}
				
			}else{
				
				$returnVal['errCode'] = &$keyCodeResult['errCode'];
			}
			
		}
		
		return $returnVal;
		
	}
	function Login($email, $password){
		require_once(BASE_PATH.'/model/users/users_defs.php');
	
		$returnVal = array();
		$returnVal['error'] = true;
		$returnVal['code'] = 1;
		if(CMisc::IsEmail($email)){
			$query = 'select uid, username, lid, passcode, lastactive from users where email=:loginID and password=:password';
		}else{
			$query = 'select uid, username, lid, passcode, lastactive from users where username=:loginID and password=:password';
		}
		$this->db->Prepare($query);
		$params[] = new CDBParam('loginID', $email, PDO::PARAM_STR);
		$params[] = new CDBParam('password', sha1($password), PDO::PARAM_STR);
		

	
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0 ){
				
				$fetch = $this->db->Fetch();
				$returnVal['result']  = ERR_LOGIN_NO_ERROR;
				$returnVal['username']= $fetch['username'];
				$returnVal['passcode']= $fetch['passcode'];
				$returnVal['userid']  = $fetch['uid'];
				$this->uid = $fetch['uid'];
				$returnVal['code'] = 0;
				$returnVal['error'] = false;
				$returnVal['result']  = ERR_LOGIN_NO_ERROR;
	
				if(empty($returnVal['passcode'])){
					
					$returnVal['passcode'] = $this->GetPassCode();
						
				}
				require_once(BASE_PATH.'/model/users/class_user.php');
				$DIContainer = new CContainer();
				$db = $DIContainer->GetDBService(true);
				$user = new CUser($this->uid, array('db' => $db));

				if(0 != $user->GetLid()){

					$location = $user->GetLocation();
					$returnVal['lat'] = $location->lat;
					$returnVal['lng'] = $location->lng;
						
				}else{

                }
				
				
				$returnVal['isFBConnected'] = $user->IsFbConnected(); 
			}else{
	
				$returnVal['result']  = ERR_LOGIN_USER_NOT_FOUND;
			}
		}
	
		return $returnVal;
	}
	function GetPassCode()
	{
		$this->db->Prepare('SELECT passcode FROM users WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_STR );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				if(empty($fetch['passcode'])){
					$newPassCode = CMisc::CreateRandomString(40);
					if($this->SetPasscode($newPassCode)){
						return $newPassCode;
					}
				}else{
					$fetch = $this->db->Fetch();
					return $fetch['passcode'];
				}
			}
		}
		
		return false;
	}
	function SetPasscode($passcode){
	
		$this->db->Prepare('UPDATE '.$this->module_table_name.' SET passcode = :passcode WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('passcode', $passcode, PDO::PARAM_STR );
			
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				return true;
			}
		}
		return false;
	}
	
	private function GetUserIDOfEmail($email)
	{
		$returnVal = false;
		
		$this->db->Prepare('SELECT uid, username  FROM users WHERE email=:email limit 1');
		$params[] = new CDBParam('email', $email, PDO::PARAM_STR );
		
		if($this->db->Execute($params)){
				
			if($this->db->RowCount() > 0){
		
				
		
				$returnVal = true;
		
			}
		}
		
		
		return $returnVal;
		
	}
	function ForgetPassword($email)
	{
		$returnVal = array();
		$returnVal['error'] = true;
		$uid = $this->GetUserIDOfEmail($email);
		
		if(false !== $uid){
			
			$forgettenPWInfo = $this->SetForgettenPasswordInfo($uid);
			if($forgettenPWInfo['error'] === false){
				
				require_once(BASE_PATH.'/model/users/class_user.php');
				require_once(BASE_PATH.'/model/email/class_email.php');
				
				$user = new CUser($uid, array('db'=> $this->db));
				
				$mailContent = new CPasswordResetMailContents($user->GetUsername(), $uid, $forgettenPWInfo['newPassword'], $forgettenPWInfo['keycode']);
				$mail = new CEmail ();
				$mail->SetContent ( $mailContent->content);
				$mail->SetSubject ( $mailContent->header);
				
				$mail->SetTo ( $email );
				
				$send = $mail->Send ();
				if ($send ['error']) {
					$returnVal['error'] = true;
					
					$logger = CLogger::GetLogger();
					$logger->Log(0,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							21,
							'Forget password event, Email not sent.  Sendmail message: '. $send['message'] . ' - Username = '. $user->GetUsername(). ' - uid='.$uid.' and email: '. $email);
					
				} else {
					$returnVal['error'] = false;
				}
				
				
			}else{
				
				if($forgettenPWInfo['returnCode'] == FORGETTEN_PASSWORD_TOO_MANY_ATTEMT){
					
					$logger = CLogger::GetLogger();
					$logger->Log(0,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							21,
							'Forget password event, too many succesfull attempt to refresh password uid='.$uid.' and email: '. $email);
						
				}else{
					
					$logger = CLogger::GetLogger();
					$logger->Log(0,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							21,
							'Forget password event, ForgattenPassword Info function returned as false for uid='.$uid.' and email: '. $email);
						
				}
			}
		}else{
			
			$logger = CLogger::GetLogger();
			$logger->Log(0,
					__FUNCTION__,
					__CLASS__,
					func_get_args(),
					$_SERVER['PHP_SELF'],
					$_SERVER['QUERY_STRING'],
					21,
					'Forget password event, email not found: '. $email);
			
		}
		
		return $returnVal;
	}
	private function SetForgettenPasswordInfo($uid)
	{
		$returnVal['error'] = true;
		$returnVal['returnCode'] = FORGETTEN_PASSWORD_ERROR;
		$this->db->Prepare('select fpid, uid, confirmkey, keytime, count from forgottenpasswords where uid=:uid');
		$params[] = new CDBParam('uid', $uid, PDO::PARAM_INT );
		
		if($this->db->Execute($params)){
			
			$newPassword = CMisc::CreateRandomString(8);
			$confirmKey = CMisc::CreateRandomString(20);
			
			$params[] = new CDBParam('time', time(), PDO::PARAM_INT );
			$params[] = new CDBParam('password', $newPassword, PDO::PARAM_STR );
			$params[] = new CDBParam('key', $confirmKey, PDO::PARAM_STR );
		
			$returnVal['newPassword'] = $newPassword;
			if($this->db->RowCount()>0)
			{
				$fetch = $this->db->Fetch();
				$count = $fetch['count'];
				$oldKeyCode = $fetch['confirmkey'];
				
				if($fetch['keytime'] > (time() - 60*60*12)){	/* if keytime is newer than 12 hours */
					
					if($count < 3){
						$returnVal['error']  = false;
						$returnVal['returnCode'] = FORGETTEN_PASSWORD_LAST_KEY_STILL_FRESH;
						$returnVal['keycode'] = $oldKeyCode;
						
						$this->db->Prepare('UPDATE forgottenpasswords SET count = count + 1 WHERE fpid = '.$fetch['fpid']);
						$this->db->Execute();
						
					}else{
						
						$returnVal['error']  = true;
						$returnVal['returnCode'] = FORGETTEN_PASSWORD_TOO_MANY_ATTEMT;
					}
				}else{
					
					$this->db->Prepare('update forgottenpasswords set newpassword=:password, confirmkey=:key, keytime=:time, count = 1 WHERE uid=:uid');
					
					if($this->db->Execute($params)){
						$returnVal['error']  = false;
						$returnVal['returnCode'] = FORGETTEN_PASSWORD_LAST_KEY_UPDATED;
						$returnVal['keycode'] = $confirmKey;
					}
				}
				
				
			
			}
			else{
				$this->db->Prepare('insert into forgottenpasswords (uid, newpassword, confirmkey, keytime) values (:uid, :password, :key, :time)');

				if($this->db->Execute($params)){
					$returnVal['error']  = false;
					$returnVal['returnCode'] = FORGETTEN_PASSWORD_LAST_KEY_STILL_FRESH;
					$returnVal['keycode'] = $confirmKey;
				}
			}
		}
		
		return $returnVal;
	}
	function SetLocationType($locationType){
		
		$user = new CUser(array('db' => $this->db));
		$lid = $user->GetLid();
		
		$this->db->Prepare('UPDATE locations SET type = :type WHERE lid=:lid');
		$params[] = new CDBParam('lid', $lid, PDO::PARAM_INT );
		$params[] = new CDBParam('type', $locationType, PDO::PARAM_INT );
		
			if($this->db->Execute($params)){
				if($this->db->RowCount()>0)
				{
					return true;
				}
			}
	}
	private function IsCurrentPasscodeCorrect($passcode){
		$this->db->Prepare('SELECT passcode FROM users WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		//$params[] = new CDBParam('passcode', $passcode, PDO::PARAM_STR );

		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				$fetch = $this->db->Fetch();
				if($passcode == $fetch['posscode']){
					return true;
				}else if(empty($fetch['passcode'])){
					return true;
				}	
			}
		}
	
		return false;
	
	}
	private function UpdatePassword($newPassword)
	{
		$returnVal['error'] = true;
		if(!empty($newPassword) && strlen($newPassword) > 5){
			$this->db->Prepare('UPDATE '.$this->module_table_name.' SET password = :password WHERE uid=:uid');
				
			$hashedPassword = Cmisc::Hash($newPassword);
			$params[] = new CDBParam('uid', (int)$this->uid, PDO::PARAM_INT );
			$params[] = new CDBParam('password', $hashedPassword, PDO::PARAM_STR );
				
			if($this->db->Execute($params)){
				
				$returnVal['error'] = false;
			}else{
				
			}
		}else{
			
			
		}
		return $returnVal;
	}
	function SetNewPassword($currentPassword, $newPassword)
	{
		$returnVal['error'] = true;
		$returnVal['message'] = "";
		if($currentPassword != $newPassword){
			
			if($this->IsPasswordValid($newPassword)){
				
				if($this->IsCurrentPasswordCorrect($currentPassword)){
					
					$updatePasswordResult = $this->UpdatePassword($newPassword);
					
					$returnVal['error'] = $updatePasswordResult['error'];
					
					
				}else{
					$returnVal['message'] = _("Current password is wrong");
				}
			}else{
				$returnVal['message'] = _("New password is Invalid. Should be between 6-16 characters");
			}
		}else{
			$returnVal['message'] = _("Current and new password cannot be same");
		}
		
		return $returnVal;
	}
	private function IsPasswordValid($password)
	{
		$returnVal = false;
		
		if(empty($password)){
				
			$errorMessage = _("Type your password");
		}else if(strlen($password) < 6 || (strlen($password) > 16)){
				
			$errorMessage = _("Password should be between 6-16 in length");
		}else{
			$returnVal = true;
		}
		
		return $returnVal;
	}
	private function IsCurrentPasswordCorrect($currentPassword){
		$this->db->Prepare('SELECT password FROM users WHERE uid=:uid and  password = :password');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('password', Cmisc::Hash($currentPassword), PDO::PARAM_STR );
		
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				return true;
					
					
			}
		}
		
		return false;
		
	}
	
	function IsActivated()
	{
		$alias = 'activated';
		$query = $this->db->Prepare('SELECT '.$alias.' FROM '.$module_table_name.' WHERE uid=:uid');
		$params[] = new CDBParam('uid', $this->uid, PDO::PARAM_INT);
		
		if($this->db->Execute($params)){
			if($this->db->RowCount()>0)
			{
				return true;	
			}
		}
		
		return false;
		
	}
	
	
	
}