<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

class SIndexPageBaseTemplateInputs
{
    public  $loggedIn;
    public  $news;
    public  $newsCount;
    public  $newsErrorMessage;

    public  $notifications;

    public $headerMessage;


    public  $hasCurrentLocation;
    public  $isLocated;
    public  $location;
    public  $requestLocation;

    public  $templates = array();
}
class CIndexPageBaseTemplateValues
{
    protected $isLoggedIn;
    /** @var SIndexPageTemplateInputs */
    protected $params;

    protected $templateParams;

    function __construct(SViewBaseTemplateInputs $params)
    {
        $this->params         = $params;
        $this->templateParams = new CTemplateParams(getenv('LANGUAGE'));


    }

    function &GetValues()
    {

        if (true == $this->params->loggedIn) {

            $this->templateParams->AddAuthUserTopMenu();
            $this->templateParams->Add('loggedIn', $isLoggedIn);
        } else {

            $this->templateParams->AddNotAuthUserTopMenu();
        }



        $this->templateParams->AddHeaderTemplateValues();
        $this->templateParams->AddTopMenuTemplateValues();

        $this->templateParams->AddTemplate()->ItemInList();
        $this->templateParams->AddTemplate()->UserInList();
        $this->templateParams->AddTemplate()->ListHeaderMessage();

        $this->templateParams->AddCategoryValues(0);


        if(isset($this->params->news['news-error-message'])){

            $this->templateParams->Add('news-error-message', $this->params->news['news-error-message']);
        }else{

            $this->templateParams->Add('news', $this->params->news['news']);
        }


        $this->templateParams->Add('header-message', $this->params->headerMessage);


        

        return $this->templateParams->GetParams();

    }
}