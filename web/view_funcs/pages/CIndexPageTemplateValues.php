<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CViewBaseTemplateValues.php');


class SIndexPageTemplateInputs
{
    public  $baseInputs;

    public $isNewMember = false;

    public $items = false;

    

    function __construct(SViewBaseTemplateInputs $baseInputs)
    {
        $this->baseInputs = $baseInputs;
    }
}
class CIndexPageTemplateValues extends CViewBaseTemplateValues
{

    /** @var SIndexPageTemplateInputs */
    private $indexParams;


    function __construct(SIndexPageTemplateInputs $params)
    {
        $this->indexParams = $params;
        parent::__construct($params->baseInputs);
        parent::GetValues();
    }

    function &GetValues()
    {


        $this->templateParams->AddTemplate()->ItemInList();
        $this->templateParams->AddTemplate()->UserInList();
        $this->templateParams->AddTemplate()->ListHeaderMessage();



        if(true === $this->indexParams->isNewMember){

            $this->templateParams->AddNewUserWelcomeTemplateValues();
        }




        if(!isset($this->indexParams->items['has_current_location']) || (isset($this->indexParams->items['has_current_location']) && false === $this->indexParams->items['has_current_location'])){

            $tempTrue = true;
            $this->templateParams->Add('header-message', _('Listing items recently added'));
            $this->templateParams->Add('request-location', $tempTrue);
        }else{

            $hasLocation = true;
            $this->templateParams->Add('has-location', $hasLocation);

        }




        if(!isset($this->indexParams->items['items'])) {

            if(isset($this->indexParams->items['has_current_location']) && false === $this->indexParams->items['has_current_location']){

                $this->templateParams->Add('error-message', _('Can\'t explore your neighbourhood because we couldn\'t determine your location. Please enable your GPS and share your location'));
            }else {
                $this->templateParams->Add('items',$this->indexParams->items);
            }
        }else{

            $this->templateParams->Merge($this->indexParams->items);
        }

        return $this->templateParams->GetParams();

    }
}