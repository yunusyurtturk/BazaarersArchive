<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_user.php');
?>
<form action="" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="uploadedfile[]" id="uploadedfile">
    <input type="file" name="uploadedfile[]" id="uploadedfile">
    <input type="submit" value="Upload Image" name="submit">
</form>
<?php


$mock = array();


/* Followings Testi */


$mock['action'] = "logout";
$mock['uid'] = "1";
$mock['current_password'] = "132423";
$mock['new_password'] = "132423132";
 

 
$controller = new CUserController($mock);

var_dump($controller->RunAction());
