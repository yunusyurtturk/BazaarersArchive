<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_search_range.php');
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


$mock['action'] = "users";
$mock['uid'] = "1";
$mock['lat'] = "29.9";
$mock['lng'] = "32.7";
 

 
$controller = new CSearchRangeController($mock);

var_dump($controller->RunAction());
