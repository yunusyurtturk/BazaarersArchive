<?php

require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');
require_once(BASE_PATH.'/model/class_base_model_with_db.php');
require_once(BASE_PATH.'/model/log/logger.php');
require_once(BASE_PATH.'/model/image/class_image_manip.php');

class SAddItemFormErrors{
    public $name;
    public $amount;
    public $category;
    public $images;
    public $priceType;
    public $price;
    public $description;
    public $other;

    function __construct()
    {
        $this->name 	= false;
        $this->amount 		= false;
        $this->category 	= false;
        $this->images 		= false;
        $this->priceType 		= false;
        $this->price 		= false;
        $this->description  = false;
        $this->other  = false;

    }


}
class CNewItemInfo extends CModelBaseWithDB
{
	public $title;
	public $description;
	public $mainpic;
	public $addtime;
	public $count;
	public $images;
	public $uid;
	public $category;
	public $price;
	public  $priceType;
	public $priceUnit;
	private $Logger;

    public $iid;

	function __construct($title, $description, $category, $mainpic, $count, $uid, $addtime, $price, $priceType, $priceUnit, array &$images = array(), array $dependicies = array() ){

        parent::__construct($dependicies);

		$this->Logger = CLogger::GetLogger();

        $isMainpicSet = false;

		$this->title 		= $title;
		$this->description 	= $description;

		$this->count 		= $count;
		$this->uid  		= $uid;

		$this->category 		= $category;
		$this->price 		= $price;
		$this->priceType 		= $priceType;
		$this->priceUnit 		= $priceUnit;
		$this->addtime 		= $addtime;

		$this->images = $images;
        $this->mainpic = $mainpic;

		if(count($images) > 0){

			$this->images = CMisc::ReArrayFiles($images);



			foreach($this->images as $key => $image){
				if($image['error'] != 0 || empty($image['name']) || empty($image['tmp_name'] )){
					unset($this->images[$key]);
				}
			}
		}

		if(isset($mainpic) && !empty($mainpic) ){


		    if(self::SIsMainpicValid($mainpic, $this->images)){

                $this->mainpic = $mainpic ;
                $isMainpicSet = true;
            }
        }
		if(false == $isMainpicSet ){

			if(is_array($this->images) && isset($this->images[0]['name'])){

				$this->mainpic = $this->images[0]['name'];

			}
		}
	}
	public function SetItemID($iid){
	    $this->iid = $iid;
    }

    private function IsTitleValid(&$outMessage){

        if(!empty($this->title) && strlen($this->title) > 2 && strlen($this->title) < 120){

            return true;
        }else{

            $this->title = substr($this->title, 0, 120);
            $outMessage = _('Title must be less than 120 characters');
        }
        return false;
    }
    private function IsDescriptionValid(&$outMessage){

        if(strlen($this->description) < 20){

            $outMessage = _('Description is too short. Tell more about your item');
            return false;
        }else if(strlen($this->description) > 3000){

            $outMessage = _('Description is too long');
            return false;
        }
        return true;
    }
    private function IsAmountValid()
    {
        if (empty($this->count) || !is_numeric($this->count) || !ctype_digit(strval($this->count)) || $this->count == 0 || $this->count < 0) {

            return false;
        }

        return true;

    }
    private function IsImageCountValid(){

        if (count($this->images) > 5) {

            return false;
        }

        return true;

    }
    private function HasImages(){

        if(is_array($this->images) && count($this->images) > 0){

            return true;
        }
        return false;
    }
    private function IsCategoryValid(){

        if(!empty($this->category) && is_numeric($this->category)  || $this->category == 0 || $this->category < 0){

            require_once(BASE_PATH.'/model/category/class_categories.php');

            $category = new CCategory($this->category, array('db' => $this->db));

            if($category->IsExist() ){

                if(!CCategory::SHasSubCats($this->category, $this->db)){

                    return true;
                }

            }
        }
        return false;

    }
    private function IsPriceTypeValid()
    {
        if (!isset($this->priceType) || !is_numeric($this->priceType)) {

            if (!($this->priceType == PRICE_TYPE_PRICE || PRICE_TYPE_FREE == $this->priceType || PRICE_TYPE_DEAL == $this->priceType)) {

                return true;
            }
        }

        return false;
    }
    private function IsPriceValid(){

        if(is_numeric($this->priceType) && is_numeric($this->priceUnit)){

            if(PRICE_TYPE_FREE == $this->priceType || PRICE_TYPE_DEAL == $this->priceType){

                $this->price = 0;

                return true;
            }else if(PRICE_TYPE_PRICE == $this->priceType) {

                if (!(empty($this->price) || !is_numeric($this->price) || $this->price <= 0)) {

                    return true;
                }else{

                    $this->priceType = '';
                }
            }
        }else{

            $this->priceType = '';
        }


        return false;

    }
private function IsImagesValid()
{
    return true;
}
    public  function IsValid(SAddItemFormErrors &$itemInfoErrors, $editmode = false)
    {
        $returnVal['error'] = true;

        $errorMessage = '';
        if(!$this->IsTitleValid($errorMessage)){

            $returnVal['errorDescription'] = $errorMessage;

            $itemInfoErrors->name = true;
        }else if(!$this->IsDescriptionValid($errorMessage)){
            $returnVal['errorDescription'] = $errorMessage;

            $itemInfoErrors->description = true;
        }else if(!$this->IsCategoryValid()){
            $returnVal['errorDescription'] = _("Invalid category");

            $itemInfoErrors->category = true;
        }else if(!$this->IsPriceValid()){
            $returnVal['errorDescription'] = _("Invalid price");

            $itemInfoErrors->price = true;
        }else if(!$this->HasImages() && false == $editmode){
            $returnVal['errorDescription'] = _('Image(s) has problem');

            $itemInfoErrors->images = true;
        }else if(!$this->IsImagesValid() && false == $editmode){
            $returnVal['errorDescription'] = _('You can add 5 images at most');

            $itemInfoErrors->images = true;
        }else if(!$this->IsAmountValid()){

            $returnVal['errorDescription'] = _("Amount is invalid");

            $itemInfoErrors->amount = true;
        }else{

            $returnVal['error'] = false;
        }




        return $returnVal;
    }
    static function SIsMainpicValid($mainpic, array $images)
    {
        $returnVal = false;

        if(!is_array($images) || count($images) < 0){


        }else if(strlen($mainpic) < 4){

        }else{
            foreach ($images as $image){
                if($image['name'] == $mainpic){

                    $returnVal = true;
                    break;
                }

            }

        }
        return $returnVal;
    }

    static function SIsTitleValid($title, CDBConnection $db)
    {
        if (empty($title) || is_numeric($title) || strlen($title) < 2) {

            return false;
        }

        return true;
    }

    static function SIsPricingValid($priceType, $price, $priceUnit, CDBConnection $db)
    {
        if (!isset($priceType) || !is_numeric($priceType)) {

            return false;
        }
        if (!($priceType == PRICE_TYPE_PRICE || PRICE_TYPE_FREE == $priceType || PRICE_TYPE_DEAL == $priceType)) {

            return false;
        }
        if ((PRICE_TYPE_PRICE == $priceType) &&
                (empty($price) || !is_numeric($price) || $price <= 0)
        ) {

            return false;
        }

        return true;
    }

    static function SIsDescriptionValid($description, CDBConnection $db)
    {
        if (empty($description) || strlen($description) < 20 || strlen($description) > 300) {

            return false;
        }

        return true;
    }


}
class CNewItem extends CModelBaseWithDB
{
	private $item;
	private $Logger;

	function __construct(CNewItemInfo $newItem, array $dependicies = array())
	{
		parent::__construct($dependicies);

		$this->item = $newItem;
		$this->Logger = CLogger::GetLogger();
		//$this->ReArrayFiles();

	}
	private function RemoveXSS(){


	}
	private function SanitizeInputs(){


	}
	private function HasSubcat(){

		$this->db->Prepare('select catid from categories where parent=:parent limit 1');
		$params[] = new CDBParam('parent', $this->item->category, PDO::PARAM_INT );

		if($this->db->Execute($params)){

			if($this->db->RowCount() > 0){
				return true;
			}
		}
		return false;
	}
	private function IsCountValid()
	{
		if(is_numeric($this->item->count) && $this->item->count > 0){
			return true;
		}

		return false;
	}
	private function ReArrayFiles()
	{
		$this->item->images = CMisc::ReArrayFiles($this->item->images);
	}

	private function IsPriceTypeValid()
    {
        if (!isset($this->item->priceType) || !is_numeric($this->item->priceType)) {

            if (!($this->priceType == PRICE_TYPE_PRICE || PRICE_TYPE_FREE == $this->priceType || PRICE_TYPE_DEAL == $this->priceType)) {

                return true;
            }
        }

        return false;
    }
	private function IsPriceValid(){

		if(is_numeric($this->item->priceType) && is_numeric($this->item->priceUnit)){

			if(PRICE_TYPE_FREE == $this->item->priceType || PRICE_TYPE_DEAL == $this->item->priceType){

				$this->item->price = 0;

				return true;
			}else if(PRICE_TYPE_PRICE == $this->item->priceType) {

                if (!(empty($this->item->price) || !is_numeric($this->item->price) || $this->item->price <= 0)) {

                    return true;
                }else{

                    $this->item->priceType = '';
                }
            }
		}else{

            $this->item->priceType = '';
        }


		return false;

	}
	private function IsTitleValid(&$outMessage){

		if(!empty($this->item->title) && strlen($this->item->title) > 2 && strlen($this->item->title) < 120){

			return true;
		}else{

            $this->item->title = substr($this->item->title, 0, 120);
		    $outMessage = _('Title must be less than 120 characters');
        }
		return false;
	}
	private function IsDescriptionValid(&$outMessage){

		if(strlen($this->item->description) < 20){

		    $outMessage = _('Description is too short. Tell more about your item');
			return false;
		}else if(strlen($this->item->description) > 3000){

            $outMessage = _('Description is too long');
            return false;
        }
		return true;

	}
	private function IsAmountValid()
    {
        if (empty($this->item->count) || !is_numeric($this->item->count) || !ctype_digit(strval($this->item->count)) || $this->item->count == 0 || $this->item->count < 0) {

            return false;
        }

        return true;

    }
	private function IsImagesValid(){

        if (count($this->item->images) > 5) {

            return false;
        }

        return true;

	}
	private function HasImages(){

		if(is_array($this->item->images) && count($this->item->images) > 0){

			return true;
		}
		return false;
	}
	private function IsCategoryValid(){

		if(!empty($this->item->category) && is_numeric($this->item->category)  || $this->item->category == 0 || $this->item->category < 0){

			require_once(BASE_PATH.'/model/category/class_categories.php');

			$category = new CCategory($this->item->category, array('db'=>$this->db));

			if($category->IsExist() ){

                if(!CCategory::SHasSubCats($this->item->category, $this->db)){

                    return true;
                }

			}
		}
		return false;

	}
	private function AddTempItemPics()
	{
		$returnVal = array();

		foreach($this->item->images as $image){
			$newImage = new CImageManipulation($image);
			$returnVal[] = $newImage->AddTempImage();
		}
		return $returnVal;
	}
	private function GetImageNamesWithID($id)
	{
		$returnVal = array();

		foreach($this->item->images as $key=>$value)
		{
			$fileName  = explode('/', $value['tmp_name']);

			$returnVal[$key] =  $value['tmp_name'].'_'.$id.'.'.'jpg';
		}
		return $returnVal;
	}
	private function SetMainPic($iid, $mainpic)
	{
		$itempicsDir = BASE_PATH.'/resources/itempics/';


		if(file_exists($itempicsDir.$mainpic))
		{
			$this->db->Prepare('UPDATE items SET mainpic=:mainpic WHERE iid=:iid');
			$params[] = new CDBParam('iid', $iid, PDO::PARAM_INT );
			$params[] = new CDBParam('mainpic', $mainpic, PDO::PARAM_INT );
			if($this->db->Execute($params)){

				return true;
			}else{
                $this->Logger->DLog(0,
                    __FUNCTION__,
                    __CLASS__,
                    func_get_args(),
                    $_SERVER['PHP_SELF'],
                    $_SERVER['QUERY_STRING'],
                    'Mainpic güncellemesi SQL sorgusu işletilemediği için hata verdi: UPDATE items SET mainpic='.$mainpic.' WHERE iid='.$iid.'' );

            }
		}else{

            $this->Logger->DLog(0,
                __FUNCTION__,
                __CLASS__,
                func_get_args(),
                $_SERVER['PHP_SELF'],
                $_SERVER['QUERY_STRING'],
                'Mainpic yapılamadı, çünkü dosya mevcut değildi: '.($itempicsDir.$mainpic));
        }
		return false;
	}
	private function MoveTempImages($id, $mainpic)
	{
	    $mainpicName = '';
		$itempicsDir = BASE_PATH.'/resources/itempics/';
		//chmod($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'tempitempics/'.$value, 0777);
		//chmod($itempicsDir, 0777);

        /*
        $newImageNames = $this->GetImageNamesWithID($id);
        */

		$error = false;

		foreach($this->item->images as $key => $value )
		{
			$filename = '';


			while (true) {
				$filename = str_replace(".", "", uniqid("", true)). '.jpg';
				if (!file_exists($itempicsDir . $filename)) {
                    $newImageNames[] = $filename;
					break;
				}
			}

			if(rename($value['tmp_name'] , $itempicsDir.$filename)){

                if($value['name'] == $mainpic){

                    $mainpicName = $filename;
                }
				// Resize işlemini gerçekleştir
				// [YY]
				/*for($i=0; $i<sizeof($imageSizes); $i++)
				{
				$dimension = explode('_', $imageSizes[$i]);
				resize_item_pic($newImageNames[$key], $dimension[0], $dimension[1]);
				}*/

				$this->db->Prepare('INSERT INTO itempics (iid, filename) VALUES (:iid, :filename)');
				$params[] = new CDBParam('iid', $id, PDO::PARAM_INT );
				$params[] = new CDBParam('filename', $filename, PDO::PARAM_STR);

				if(!$this->db->Execute($params)){
					if($this->db->RowCount() > 0){

						$error = true;
					}
				}
			}else{

				$error = true;

			}
		}
		if(empty($mainpicName)){

            $mainpicName = $newImageNames[0];
        }
		return $mainpicName;
		//chmod($itempicsDir, 0755);
	}
	private function InsertToDB(){
		$this->db->Prepare('INSERT INTO items(uid, category, header, description, addtime, mainpic, price, priceType, amount) 
							VALUES (:uid, :category, :header, :description, :addtime, :mainpic, :price, :priceType, :amount)');
		$params[] = new CDBParam('uid', $this->item->uid, PDO::PARAM_INT );
		$params[] = new CDBParam('category', $this->item->category, PDO::PARAM_INT );
		$params[] = new CDBParam('header', $this->item->title, PDO::PARAM_STR );
		$params[] = new CDBParam('description', $this->item->description, PDO::PARAM_STR );
		$params[] = new CDBParam('addtime', time(), PDO::PARAM_INT );
		$params[] = new CDBParam('mainpic', $this->item->mainpic, PDO::PARAM_STR );
		$params[] = new CDBParam('price', $this->item->price, PDO::PARAM_INT );
		$params[] = new CDBParam('priceType', $this->item->priceType, PDO::PARAM_INT );
		$params[] = new CDBParam('amount', $this->item->count, PDO::PARAM_INT );

		if($this->db->Execute($params)){
			if($this->db->RowCount() > 0){
				return $this->db->GetLastInsertID();
			}
		}
		return 0;
	}
	function AddItem(SAddItemFormErrors &$addItemErrors){

		$returnVal['isAdded'] = false;
		$returnVal['iid'] = 0;
        $hasError = false;
        $errorMessage = '';
		if(!$this->IsTitleValid($errorMessage)){

			$returnVal['errorDescription'] = $errorMessage;
            $hasError = true;
            $addItemErrors->name = true;
		}
		if(!$this->IsDescriptionValid($errorMessage)){
			$returnVal['errorDescription'] = $errorMessage;
            $hasError = true;
            $addItemErrors->description = true;
		}
		if(!$this->IsCategoryValid()){
			$returnVal['errorDescription'] = _("Invalid category");
            $hasError = true;
            $addItemErrors->category = true;
		}
		if(!$this->IsPriceValid()){
			$returnVal['errorDescription'] = _("Invalid price");
            $hasError = true;
            $addItemErrors->price = true;
		}
		if(!$this->HasImages()){
			$returnVal['errorDescription'] = _('Image(s) has problem');
            $hasError = true;
            $addItemErrors->images = true;
		}
		if(!$this->IsImagesValid()){
			$returnVal['errorDescription'] = _('You can add 5 images at most');
            $hasError = true;
            $addItemErrors->images = true;
		}
		if(!$this->IsAmountValid()){

            $returnVal['errorDescription'] = _("Amount is invalid");
            $hasError = true;
            $addItemErrors->amount = true;
        }
		if($this->HasSubcat()){
			$returnVal['errorDescription'] = _("Category has subs");
            $hasError = true;
            $addItemErrors->category = true;
		}

		if(false === $hasError){

			$newItemID = $this->InsertToDB();
			$this->Logger->DLog(0,
				__FUNCTION__,
				__CLASS__,
				func_get_args(),
				$_SERVER['PHP_SELF'],
				$_SERVER['QUERY_STRING'],
				'Item is inserted with ID:' .$newItemID);
			if(0 !== $newItemID){

				$returnVal['isAdded'] = true;
				$returnVal['iid'] = $newItemID;

				$fistTempImageName = $this->MoveTempImages($newItemID, $this->item->mainpic);

                $this->item->mainpic = $fistTempImageName;

				$this->SetMainPic($newItemID, $fistTempImageName);
			}else{
				$returnVal['errorDescription'] = _("Item is not added");
                $addItemErrors->other = true;

			}

		}

		return $returnVal;
	}


}