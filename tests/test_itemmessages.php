<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_itemmessages.php');
?>
<form action="" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="uploadedfile[]" id="uploadedfile">
    <input type="file" name="uploadedfile[]" id="uploadedfile">
    <input type="submit" value="Upload Image" name="submit">
</form>
<?php


$mock = array();


$mock['action'] = "itemmessages";
$mock['uid'] = 1;
$mock['messageway'] = 'inbox';
var_dump($mock);
echo "<br />-----------TEST PARAMETRELERI SONU------------<br />";

$controller = new CItemMessagesController($mock);
echo "<br />-----------SONUC------------";
var_dump($controller->RunAction()); 


/*
$mock['action'] = "exchanged";
$mock['uid'] = 1;
$mock['imsgrsid'] = 1;
$mock['message'] = 'Selam kankaaaaaammmmmmmmmmms';
var_dump($mock);
echo "<br />-----------TEST PARAMETRELERI SONU------------<br />";

$controller = new CItemMessagesController($mock);
echo "<br />-----------SONUC------------";
var_dump($controller->RunAction());
*/

/* Itemmessages Read Testi */
/*
$mock['action'] = "read";
$mock['uid'] = 1;
$mock['imsgrsid'] = 2;
$mock['message'] = 'Selam kankaaaaaammmmmmmmmmms';
var_dump($mock);
echo "<br />-----------TEST PARAMETRELERI SONU------------<br />";

$controller = new CItemMessagesController($mock);
echo "<br />-----------SONUC------------";
var_dump($controller->RunAction());

*/



/* Followings Testi */
/*
 $mock['action'] = "removeItem";
 $mock['uid'] = 7;
 $mock['imsgrsid'] = 5;
 
$controller = new CItemMessagesController($mock);
echo "-----------SONUC------------";
var_dump($controller->RunAction());
*/

