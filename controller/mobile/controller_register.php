<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/users/class_user_account.php');
require_once(BASE_PATH.'/model/users/users_defs.php');

class CRegisterController extends CBaseController
{
	private $action;
	
	function __construct($request,  array $dependicies = array()){
		
		parent::__construct($request, $dependicies);
		
		$this->action   = $this->GetRequest('action');;

	}
	function RunAction(){
		$returnVal['error'] = ERR_NEW_USER_ERROR;
		$returnVal['action'] = $this->action;
		
		switch($this->action){
			
			case "forgetpassword":
				$email= $this->GetRequest('regemail');
				$userAccount = new CUserAccount(0);
				$isInformed = $userAccount->ForgetPassword($email);
				
				if(true == $isInformed['error']){
					
					$returnVal['message'] = _('An error occured. Please try again later');
					
					$logger = CLogger::GetLogger();
					
					$logger->Log(0,
							__FUNCTION__,
							__CLASS__,
							func_get_args(),
							$_SERVER['PHP_SELF'],
							$_SERVER['QUERY_STRING'],
							13,
							'Forget password failure: ForgetPassword() returned as false for  Email = '. $email);
					
				}else{
					
					$returnVal['error'] = ERR_NEW_USER_NO_ERROR;
					$returnVal['message'] = _('Information required to change your password is sent to your email address');
					
					
				}
			break;
			case "reset":
				$userid= $this->GetRequest('userid');
				$keyCode= $this->GetRequest('keycode');
				
				$returnVal['error'] = true;
				if(!empty($userid) && !empty($keyCode)){
					
					$userAccount = new CUserAccount($userid);
					$updatePasswordResult = $userAccount->UpdateForgettenPassword($keyCode);
					if($updatePasswordResult['error'] === false){
						
						$returnVal['error'] = false;
						$returnVal['message'] = _('Your password is updated successfully');
						
						$newPassCode = CMisc::CreateRandomString(40);
						$userAccount->SetPasscode($newPassCode);
					}else{
						if(UPDATE_PASSWORD_NEW_PASSWORD_LINK_OUTDATED == $updatePasswordResult['errCode']){
							
							$returnVal['message'] = _('Your password update link is outdated, so we can\'t update your password.');
						}else{
							
							$returnVal['message'] = _('A problem occured while updating your password. Try again later please');
						}
					}
					
				}else{
					
				}
				
			break;
			case "register":
			default:

				require_once(BASE_PATH.'/model/users/class_new_user.php');
				require_once(BASE_PATH.'/model/users/users_defs.php');
				require_once(BASE_PATH.'/model/email/class_email.php');

				CMisc::BufferOn();
				$username=$this->GetRequest('regusername');
				$password=$this->GetRequest('regpassword');
				$email= $this->GetRequest('regemail');
				$newUser = new CNewUserInfo();
				$newUser->username = $username;
				$newUser->password = $password;
				$newUser->password2 = $password;
				$newUser->email = $email;
				$newAccount = new CNewUser($newUser, array('db' => $this->db));

				$registration = $newAccount->Register();

				if(ERR_NEW_USER_NO_ERROR == $registration['result'] || ERR_NEW_USER_REGISTERED_BUT_LOGIN == $registration['result'])
				{

					$mail = new CEmail();
					$mail->SetTo($email);
					$mail->SetSubject(_('Welcome to Bazaarers - New Account'));
					$mail->SetContent($mail->GetContent(MAIL_CONTENT_NEW_USER,
														array('username' => $username, 'email' => $email))
									  );
					$mail->Send();


				}

				$returnVal = array_merge($returnVal, $registration);
				$returnVal['trash'] = CMisc::GetBufferContent();
				CMisc::BufferOff(false);


		}
		return $returnVal;
	}
}