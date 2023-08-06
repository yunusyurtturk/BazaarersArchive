<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/class_misc.php');
require_once(BASE_PATH.'/model/users/class_user.php');
require_once(BASE_PATH.'/model/items/class_new_item.php');
require_once(BASE_PATH.'/controller/mobile/controller_base.php');
require_once(BASE_PATH.'/model/log/logger.php');

class CEditItemController extends CBaseController
{
	private $action;
	private $iid;
    private $editcode;
	
	function __construct(array $request, array $dependicies = array()){
	
		parent::__construct($request, $dependicies);

        $this->iid = $this->GetRequest('iid');
        $this->editcode = $this->GetRequest('editcode');
		$this->action   = $this->GetRequest('action');

		
	}
	function RunAction(array &$uploadedFiles = array()){

		$returnVal = array();
		$returnVal['error'] = true;
		$item = new CItems($this->iid);

        $editable = false;

        if(!empty($this->iid) && is_numeric($this->iid) && $this->iid > 0){

            $item = new CItems($this->iid, array('db' => $this->db));

            if($item->GetOwnerID() == $this->GetUid()  ){

                $editable = true;
            }
        }

		switch($this->action){

			case "edititem":

			default:

				if($this->LoggedIn() && true === $editable) {

                    if((isset($_REQUEST['formapproved'])
                        && !empty($_REQUEST['formapproved'])
                        && $_REQUEST['formapproved'] == 'formapproved'))
                    {
                        $returnVal['error'] = true;

                        $iid            = $this->GetRequest('iid');
                        $itemName       = $this->GetRequest('itemName');
                        $formapproved   = $this->GetRequest('formapproved');
                        $category       = $this->GetRequest('category');
                        $header = $this->GetRequest('header');
                        $description = $this->GetRequest('description');
                        $priceType = $this->GetRequest('priceType');
                        $price = $this->GetRequest('price');
                        $mainpic = $this->GetRequest('mainpic');

                        $amount = $this->GetRequest('amount');
                        //$files = (!empty($this->GetRequest('uploadedfile'))?$this->GetRequest('uploadedfile'):array());

                        $logger = CLogger::GetLogger();

                        $newItemInfo = new CNewItemInfo($header, $description, $category, $mainpic,
                            $amount, $this->uid, time(), $price, $priceType, 0,
                            $uploadedFiles);
                        $newItemInfo->SetItemID($iid);

                        $addItemErrors = new SAddItemFormErrors();
                        $IsValidResult = $newItemInfo->IsValid($addItemErrors, true);

                        $returnVal['errors'] = &$addItemErrors;

                        if(false == $IsValidResult['error']){

                            $returnVal['message'] = _('So successfull//');

                            $item = new CItems($iid, array('db' => $this->db));
                            $updateResult = $item->UpdateItemInfo($newItemInfo);
                            if(false == $updateResult['error']){

                                header( "Location: /item/".$iid);
                            }else{


                            }
                        }else{

                            $returnVal['message'] = _('Item information is not valid');
                        }


                    }else{

                        $returnVal['error'] = false;
                        $returnVal['title']         = $item->GetTitle();
                        $returnVal['description']   = $item->GetDescription();
                        $returnVal['price']         = $item->GetPrice();
                        $returnVal['priceType']     = $item->GetPriceType();
                        $returnVal['amount']        = $item->GetCount();
                        $returnVal['cat']           = $item->GetCategoryID();
                        $returnVal['mainpic']       = $item->GetMainPic();
                        $returnVal['message'] = _('Form not approved');
                    }

				}else{
					$returnVal['error'] = true;
					$returnVal['message'] = _('Not Authenticated');
				}
				
			
		}

		return $returnVal;
		
	}
	
	
}