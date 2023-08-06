<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CViewBaseTemplateValues.php');
require_once ('CIndexPageBaseTemplateValues.php');

$view = new CWebView();

class SAddItemFormNotLoggedInInputs
{

    public $loginText;
    public $loginAddr;
    public $notLoggedInMessage;

    function __construct()
    {
        $this->loginText = 'Click to log in';
        $this->loginAddr = '/login';
        $this->notLoggedInMessage = _('You must login to add items');
    }
}
class SAddItemFormPricingTypeInputs
{

    public $pricingTypeName;
    public $pricingTypeValue;
    public $selected;
    public $shouldHavePriceValue;
    public $priceTypeText;

}

class SAddItemFormErrorInputs
{
    public $itemnameHasError;
    public $categoryHasError;
    public $amountHasError;
    public $descriptionHasError;
    public $imagesHasError;
    public $priceHasError;
    public $pricingHasError;
}
class SAddItemFormInputs
{
    public $additemFormTitle;
    public $additemFormAction;
    public $itemnameHint;
    public $itemnameInputName;
    public $amountHint;
    public $amountInputName;
    public $categoryHint;
    public $categoryInputName;
    public $imagesHint;
    public $pricingTypeHint;
    public $pricingTypeName;
    public $priceHint;
    public $priceInputName;
    public $descriptionHint;
    public $descriptionInputName;
    public $mainpicInputName;
    public $hiddenName;
    public $hiddenValue;

    /** @var  $errors SAddItemFormErrorInputs */
    public $errors;
    public $categories;

    /** @var  $pricingTypes SAddItemFormPricingTypeInputs */
    public $pricingTypes = array();




    public function __construct()
    {
        $this->additemFormTitle = _('Add Item Form');
        $this->additemFormAction = 'additem.php?action=additem';
        $this->itemnameHint = _('Title');
        $this->itemnameInputName = 'header';
        $this->amountHint = _('Amount');
        $this->amountInputName = 'amount';
        $this->categoryHint = _('Category');
        $this->categoryInputName = 'category';
        $this->imagesHint = _('Images');
        $this->pricingTypeHint = _('Pricing');
        $this->pricingTypeName = 'priceType';
        $this->priceHint = _('Price');
        $this->priceInputName = 'price';
        $this->descriptionHint = _('Description');
        $this->descriptionInputName = 'description';
        $this->mainpicInputName = 'mainpic';
        $this->hiddenName = 'formapproved';
        $this->hiddenValue = 'formapproved';

    }


}
class SAddItemPageTemplateInputs
{
    /** @var  $baseInputs SViewBaseTemplateInputs */
    public  $baseInputs;


    public $action;
    /** @var  $formInputs SAddItemFormInputs */
    public $formInputs;

    public $isError;
    public $errorMessage;
    public $previouslyUploadedImagesGetUrl;

    public $isNewItemInfoExist;
    /** @var   SNewItemInfo */
    public $newItemInfo;

    /** @var   SAddItemFormNotLoggedInInputs */
    public $notLoggedInInputs;

    /** @var   SAddItemFormErrors */
    public $errorCauses;


    function __construct(SViewBaseTemplateInputs &$baseInputs)
    {
        $this->baseInputs = $baseInputs;
        $this->formInputs = new SAddItemFormInputs();
        $this->notLoggedInInputs = new SAddItemFormNotLoggedInInputs();
        $this->previouslyUploadedImagesGetUrl = '/additem.php?action=previouslyAddedImages';

        $this->newItemInfo = new SNewItemInfo();
        $this->errorCauses = new SAddItemFormErrors();

    }
}
class SNewItemInfo
{
    public $title ='';
    public $description ='';
    public $priceType ='';
    public $price ='';
    public $amount ='';
    public $cat = 0;
    public $mainpic = '';
    public $images = array();




}
class CAddItemPageTemplateValues extends CViewBaseTemplateValues
{
    private $addItemParams;
    private $editMode;

    function __construct(SAddItemPageTemplateInputs $params)
    {
        $this->addItemParams = $params;
        parent::__construct($params->baseInputs);
        parent::GetValues();
    }
    function SetEditMode(){
        $this->editMode = true;
    }

    function &GetValues()
    {
        $trueDef = true;
        $zero = 0;
        if(true === $this->params->loggedIn){

            $this->templateParams->Add('logged-in', $trueDef);
            $this->templateParams->Add('previously-uploaded-images-get-url', $this->addItemParams->previouslyUploadedImagesGetUrl);

            $this->templateParams->Add('selected-category-id', $zero );

            if(true === $this->addItemParams->isNewItemInfoExist){


                /*
                            $selectedCategories = array();
                            $selectedCatCount = count($selectedCatIDs);
                            if($selectedCatCount > 0){

                                $this->templateParams->Add('seleted-categories',     $trueDef);
                            }
                            for($i = $selectedCatCount; $i--; $i>=0){

                                $selectedCategories['seleted-categories'] = array('category-input-name' => CCategory::SGetName($selectedCatIDs[$i], $db), 'category-input-value' => $selectedCatIDs[$i]);
                            }*/

                $this->templateParams->Add('itemname-value',     $this->addItemParams->newItemInfo->title);
                $this->templateParams->Add('description-value',  $this->addItemParams->newItemInfo->description);
                $this->templateParams->Add('price-value',        $this->addItemParams->newItemInfo->price);
                $this->templateParams->Add('amount-value',       $this->addItemParams->newItemInfo->amount);

                $this->templateParams->Add('selected-category-id', $this->addItemParams->newItemInfo->cat );

                $this->templateParams->Add('mainpic-value',       $this->addItemParams->newItemInfo->mainpic);

            }

            /* 1 - Form Inputs */
            $formApprovedValue = 'formapproved';
            $this->templateParams->Add('additem-form-title' , $this->addItemParams->formInputs->additemFormTitle);
            $this->templateParams->Add('additem-form-action', $this->addItemParams->formInputs->additemFormAction);

            $this->templateParams->Add('itemname-hint'      , $this->addItemParams->formInputs->itemnameHint);
            $this->templateParams->Add('itemname-input-name', $this->addItemParams->formInputs->itemnameInputName);

            $this->templateParams->Add('amount-hint'        , $this->addItemParams->formInputs->amountHint);
            $this->templateParams->Add('amount-input-name'  , $this->addItemParams->formInputs->amountInputName);
            $this->templateParams->Add('category-hint'      , $this->addItemParams->formInputs->categoryHint);
            $this->templateParams->Add('category-input-name', $this->addItemParams->formInputs->categoryInputName);
            $this->templateParams->Add('images-hint'        , $this->addItemParams->formInputs->imagesHint);
            $this->templateParams->Add('pricing-type-hint'  , $this->addItemParams->formInputs->pricingTypeHint);
            $this->templateParams->Add('pricing-type-name'  , $this->addItemParams->formInputs->pricingTypeName);

            $this->templateParams->Add('price-hint'             , $this->addItemParams->formInputs->priceHint);
            $this->templateParams->Add('price-input-name'       , $this->addItemParams->formInputs->priceInputName);
            $this->templateParams->Add('description-hint'       , $this->addItemParams->formInputs->descriptionHint);
            $this->templateParams->Add('description-input-name' , $this->addItemParams->formInputs->descriptionInputName);
            $this->templateParams->Add('mainpic-name'            , $this->addItemParams->formInputs->mainpicInputName);
            $this->templateParams->Add('hidden-name'            , $formApprovedValue);
            $this->templateParams->Add('hidden-value'           , $formApprovedValue);




            /*************************************/
            if($this->addItemParams->isError) {

                $this->templateParams->Add('error-message'      , $this->addItemParams->errorMessage);
                if($this->addItemParams->errorCauses->description){

                    $this->templateParams->Add('description-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->name){

                    $this->templateParams->Add('itemname-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->priceType){

                    $this->templateParams->Add('pricing-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->amount){

                    $this->templateParams->Add('amount-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->category){

                    $this->templateParams->Add('category-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->images){

                    $this->templateParams->Add('images-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->price){

                    $this->templateParams->Add('price-has-error'      , $trueDef);
                }
                if($this->addItemParams->errorCauses->other){

                    $this->templateParams->Add('other-has-error'      , $trueDef);
                }


            }

            /* 2 - Categories - */

            require_once(BASE_PATH.'/controller/mobile/controller_cats.php');
            require_once(BASE_PATH.'/model/DI/class_container.php');
            require_once(BASE_PATH.'/model/category/class_categories.php');

            $DIContainer = new CContainer();
            $db = $DIContainer->GetDBService();

            $deepestChildSelected = false;
            $selectedCatIDs = CCategory::SGetParentsRecursive($this->addItemParams->newItemInfo->cat, $db);
            $selectedCatCount = count($selectedCatIDs);

            $categoriesArray = array();

            for($selectedCatIndex = 0; $selectedCatIndex < $selectedCatCount; $selectedCatIndex++)
            {
                $catID = $selectedCatIDs[$selectedCatIndex];
                $categories = new CCategoriesController(array('parent' => $catID));
                $childCats = $categories->RunAction();


                $childCount = (isset($childCats['catnames']) && is_array($childCats['catnames']))?count($childCats['catnames']):0;

                if($childCount > 0){
                    $categories = array('categories');
                    for ($i = 0; $i < $childCount; $i++) {

                        $newCat = array('catid' => $childCats['catids'][$i], 'catname' => $childCats['catnames'][$i]);

                        if(($selectedCatCount) > ($selectedCatIndex + 1 )
                            && ($childCats['catids'][$i] == $selectedCatIDs[$selectedCatIndex + 1])){

                            $newCat['selected'] = true;
                        }
                        $categories['categories'][] = $newCat;
                    }
                    $categoriesArray[] = $categories;
                }else{

                    $deepestChildSelected = true;
                }



            }
            $this->templateParams->Add('seleted-categories', $categoriesArray);
            if(true === $deepestChildSelected){

                $this->templateParams->Add('deepest-category-selected', $trueDef);
            }


            /* 3 - Price Types */

            $isPriceTypeSelected = array(PRICE_TYPE_PRICE => false, PRICE_TYPE_FREE => false, PRICE_TYPE_DEAL => false);
            if(!isset($this->addItemParams->newItemInfo->priceType)
                || empty($this->addItemParams->newItemInfo->priceType)
                || !(
                    $this->addItemParams->newItemInfo->priceType == PRICE_TYPE_PRICE
                    || $this->addItemParams->newItemInfo->priceType == PRICE_TYPE_FREE
                    || $this->addItemParams->newItemInfo->priceType == PRICE_TYPE_DEAL) ){

                $isPriceTypeSelected[PRICE_TYPE_PRICE] = true;
                $this->addItemParams->newItemInfo->priceType = PRICE_TYPE_PRICE;
            }else{

                $isPriceTypeSelected[$this->addItemParams->newItemInfo->priceType] = true;
            }

            $pricingTypes = array();
            $pricingTypes[] = array('pricing-type-name' => 'priceType',
                'pricing-type-value' => PRICE_TYPE_PRICE,
                'selected' => $isPriceTypeSelected[PRICE_TYPE_PRICE],
                'should-have-price-value' => 'true',
                'price-type-text' => _('Price')
            );
            $pricingTypes[] = array('pricing-type-name' => 'priceType',
                'pricing-type-value' => PRICE_TYPE_FREE,
                'selected' => $isPriceTypeSelected[PRICE_TYPE_FREE],
                'should-have-price-value' => 'false',
                'price-type-text' => _('Free')
            );
            $pricingTypes[] = array('pricing-type-name' => 'priceType',
                'pricing-type-value' => PRICE_TYPE_DEAL,
                'selected' => $isPriceTypeSelected[PRICE_TYPE_DEAL],
                'should-have-price-value' => 'false',
                'price-type-text' => _('Deal')
            );
            $this->templateParams->Add('pricing-types', $pricingTypes);

            if($this->addItemParams->newItemInfo->priceType != PRICE_TYPE_PRICE) {

                $this->templateParams->Add('price-disabled', $trueDef);
            }

            if($this->addItemParams->newItemInfo->priceType == PRICE_TYPE_DEAL){

                $this->templateParams->Add('price-value', _('Deal'));

            }else if($this->addItemParams->newItemInfo->priceType == PRICE_TYPE_FREE){

                $this->templateParams->Add('price-value', _('Free'));

            }









            /* Additional Templates */
            require_once(BASE_PATH.'/model/DI/class_container.php');
            require_once(BASE_PATH.'/model/category/class_categories.php');
            $DIContainer = new CContainer();
            $db = $DIContainer->GetDBService(true);

            $cats = CCategory::SGetChildsRecursive(0, $db);

            $this->templateParams->AddTemplate()->CategoriesInAddItemForm($cats);
            $this->templateParams->AddTemplate()->CategoriesSelector();


        }else{

            $this->templateParams->Add('login-text', $this->addItemParams->notLoggedInInputs->loginText);
            $this->templateParams->Add('login-addr', $this->addItemParams->notLoggedInInputs->loginAddr);
            $this->templateParams->Add('not-logged-in-message', $this->addItemParams->notLoggedInInputs->notLoggedInMessage);


        }



        return $this->templateParams->GetParams();

    }
}