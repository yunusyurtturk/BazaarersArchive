<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/additem_controller.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');
?>
<form action="additem.php" method="post" enctype="multipart/form-data">
    Select image to upload:
    <input type="file" name="uploadedfile[]" id="uploadedfile">
    <input type="file" name="uploadedfile[]" id="uploadedfile">
    <input type="submit" value="Upload Image" name="submit">
</form>
<?php
$mock = array();
$mock['header'] = 'Ürün Başlığı';
$mock['description'] = 'Ürünün açıklaması bu bu yeterince uzundur';

$mock['addtime'] = time();
$mock['amount'] = 1;
$mock['uid'] = 2;
$mock['category'] =105;
$mock['price'] = 10;
$mock['priceType'] = PRICE_TYPE_FREE;
$mock['priceUnit'] = 0;
$mock['uploadedfile'] = $_FILES['uploadedfile'];
$mock['mainpic'] = $_FILES['uploadedfile']['name'][0];
var_dump($mock);
$controller = new CAddNewItemController($mock);

$controller->JSON($controller->RunAction((is_array($_FILES) && isset($_FILES['uploadedfile']))?$_FILES['uploadedfile']:array()));


