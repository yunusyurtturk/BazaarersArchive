<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/libraries/PHPMailer/PHPMailerAutoload.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');

define('MAIL_CONTENT_NEW_USER', 		 1);
define('MAIL_CONTENT_NEW_ITEM_MESSAGES', 2);


class CEmail extends CModelBaseWithDB
{
	private $mail;
	function __construct(array $dependicies = array())
	{
		parent::__construct($dependicies);
	
		$this->Init();
		
	}
	function SetSubject($subject){
		$this->mail->Subject = $subject;
	}
	function SetContent($content){
		$this->mail->Body = $content;
	}
	function SetFrom($from, $fromName = null){
		
		if(null == $fromName){
			$this->mail->setFrom($from);
		}else{
			$this->mail->setFrom($from, $fromName);
		}
		
	}
	function SetTo($to){
		$this->mail->addAddress($to);     // Add a recipient
	}
	
	function GetSubject($subject, array $params = array()){
		
		$returnVal = null;
		
		switch($subject){
			case MAIL_CONTENT_NEW_USER:
				$returnVal = _('Welcome to Bazaarers - New Account');
			break;
			case MAIL_CONTENT_NEW_ITEM_MESSAGES:
				$returnVal = _('Bazaarers - New message received');
			break;
			
			
		}
		return $returnVal;
	}
	function GetContent($contentType, array $params = array()){
		$returnVal = null;
		
		switch($contentType){
			
			case MAIL_CONTENT_NEW_ITEM_MESSAGES:
			
				$returnVal = _('Hi '.$params['username'].' <br /><br />
					Your have received a message from '.$params['interlocutor'].' about the item '. $params['itemName'].' <br /><br />
							
					You can change your settings in your application<br /><br /><br />
					If you think this is an irrelevant e-mail please contact with support@bazaarers.com<br /><br />
					');
					
			break;
			
			case MAIL_CONTENT_NEW_USER:
				
			$returnVal = _('Hi '.$params['username'].' <br /><br />
					Your user account with the e-mail address '.$params['email'].' has been created.<br /><br />
					You can change your settings in your application<br /><br /><br />
					If you think this is an irrelevant e-mail and you didn\'t create a Bazaarers account, please connect to support@bazaarers.com<br /><br />
					');
			
			break;
		}
		
		return $returnVal;
	}
	
	function Send()
	{
		$returnVal = array();
		$returnVal['error'] = true;
		if(!$this->mail->send()) {
			$returnVal['message'] = $this->mail->ErrorInfo.'---'. _('Message could not be sent.');
			//echo 'Mailer Error: ' . $this->mail->ErrorInfo;
		} else {
			$returnVal['error'] = false;
			$returnVal['message'] =  _('Message has been sent');
		}
		
		return $returnVal;
	}
	
	private function Init()
	{
		$this->mail = new PHPMailer;
		$this->mail->isSMTP();                                      // Set mailer to use SMTP
		$this->mail->Host = 'SECRET';  // Specify main and backup SMTP servers
		$this->mail->SMTPAuth = true;                               // Enable SMTP authentication
		$this->mail->Username = 'SECRET';                 // SMTP username
		$this->mail->Password = 'SECRET';                           // SMTP password
		 $this->mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$this->mail->Port = 465;                                    // TCP port to connect to
		
		$this->mail->setFrom('SECRET', 'Bazaarers');
		$this->mail->addReplyTo('SECRET', 'Bazaarers - No Reply');
		//$this->mail->addCC('cc@example.com');
		//$this->mail->addBCC('bcc@example.com');
		
		//$this->mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
		//$this->mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
		$this->mail->isHTML(true);                                  // Set email format to HTML
		// $this->mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
		$this->mail->SMTPAuth   = true;                  // enable SMTP authentication
		$this->mail->WordWrap    = 900;
		//$this->mail->Subject = 'Here is the subject';
		//$this->mail->Body    = 'This is the HTML message body <b>in bold!</b>';
		//$this->mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
		
		$this->mail->addCustomHeader("Organization" , 'Bazaarers');
		$this->mail->addCustomHeader("Content-Transfer-encoding" , "8bit");
		
		$this->mail->addCustomHeader("Message-ID" , "<".md5(uniqid(time()))."@{$_SERVER['SERVER_NAME']}>");
		$this->mail->addCustomHeader("Content-type: text/html; charset=utf-8");
		$this->mail->addCustomHeader("MIME-Version: 1.0");
		$this->mail->addCustomHeader("X-Priority: 3");
		$this->mail->addCustomHeader("X-Mailer: PHP/". phpversion());
		
		
	}
	
}