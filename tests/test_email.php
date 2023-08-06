<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/defs/global_definitions.php');
require_once(BASE_PATH.'/model/email/class_email.php');
$mock = array();




 
$mail = new CEmail();
$mail->SetContent("Deneme Emaili");
$mail->SetSubject("YunusY, Deneme");

$mail->SetTo("yunus_423@hotmail.com");

$send = $mail->Send();
if($send['error']){
	echo 'Hata oluştu';
	echo $send['message'];
}else{
	echo 'Gönderildi';
}