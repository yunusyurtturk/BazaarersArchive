<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_homescreen.php');
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

 $mock['uid'] = 22;
 $mock['nid'] = 253;
 
$controller = new CHomeScreenController($_REQUEST);

var_dump($controller->RunAction());
