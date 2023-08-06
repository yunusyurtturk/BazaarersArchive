<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_item.php');
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

 $mock['action'] = "removeItem";
 $mock['uid'] = 1;
 $mock['iid'] = 88;
 
$controller = new CItemController($mock);

var_dump($controller->RunAction());
