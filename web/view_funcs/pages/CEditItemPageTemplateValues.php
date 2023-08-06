<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CViewBaseTemplateValues.php');
require_once ('CIndexPageBaseTemplateValues.php');

require_once ('CAddItemPageTemplateValues.php');

$view = new CWebView();


class SEditItemFormInputs extends SAddItemFormInputs
{
    public $iid;
    public function __construct($iid)
    {
        parent::__construct();

        $this->iid = $iid;
        $this->additemFormAction = 'edititem.php?action=edited&iid='.$this->iid;
        $this->additemFormTitle = _('Edit Item');
    }


}
class SEditItemPageTemplateInputs extends SAddItemPageTemplateInputs
{

    public $iid;
    function __construct($itemID, SViewBaseTemplateInputs &$baseInputs)
    {
        parent::__construct($baseInputs);

        $this->iid = $itemID;
        $this->formInputs = new SEditItemFormInputs($itemID);

        $this->previouslyUploadedImagesGetUrl = '/edititem.php?action=previouslyAddedImages&iid='.$itemID;



    }
}

class CEditItemPageTemplateValues extends CAddItemPageTemplateValues
{
    private $editItemParams;
    private $editMode;

    function __construct(SEditItemPageTemplateInputs $params)
    {
        $this->editItemParams = $params;

        parent::__construct($params);
        parent::GetValues();
    }

    function &GetValues()
    {

        $trueDef = true;
        $zero = 0;
        if(true === $this->params->loggedIn){

            $this->templateParams->Add('previously-uploaded-images-get-url', $this->editItemParams->previouslyUploadedImagesGetUrl);
            $this->templateParams->Add('item-id',                           $this->editItemParams->iid);



            /* 1 - Form Inputs */
            $formApprovedValue = 'formapproved';
            $this->templateParams->Add('edit-mode' , $trueDef);

            $this->templateParams->Add('additem-form-title' , $this->editItemParams->formInputs->additemFormTitle);
            $this->templateParams->Add('additem-form-action', $this->editItemParams->formInputs->additemFormAction);




        }else{

            $this->templateParams->Add('login-text', $this->editItemParams->notLoggedInInputs->loginText);
            $this->templateParams->Add('login-addr', $this->editItemParams->notLoggedInInputs->loginAddr);
            $this->templateParams->Add('not-logged-in-message', $this->editItemParams->notLoggedInInputs->notLoggedInMessage);


        }



        return $this->templateParams->GetParams();

    }
}