<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once (BASE_PATH.'/model/paths/CPaths.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateParams.php');
require_once(BASE_PATH.'/web/view_funcs/CTemplateHolder.php');


class SViewCurrentPageEnum
{
    public $explore;
    public $followingItems;
    public $followers;
    public $followings;
    public $myItems;
    public $addItem;
    public $news;
    public $profile;
    public $itemMessages;
}

class SViewBaseTemplateInputs
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

    /** @var SViewCurrentPageEnum */
    public  $currentPage;

    public  $templates = array();

    function __construct()
    {

        $this->currentPage = new SViewCurrentPageEnum();


    }
}
class CViewBaseTemplateValues
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

        $trueDef = true;
        if (true == $this->params->loggedIn) {

            $this->templateParams->AddAuthUserTopMenu();
            $this->templateParams->Add('loggedIn', $isLoggedIn);
        } else {

            $this->templateParams->AddNotAuthUserTopMenu();
        }



        $this->templateParams->AddHeaderTemplateValues();
        $this->templateParams->AddTopMenuTemplateValues();



        $this->templateParams->AddCategoryValues(0);

        if(true == $this->params->currentPage->explore){
            $this->templateParams->Add('searchrange-selected', $trueDef);
        }

        if(true == $this->params->currentPage->followingItems){

            $this->templateParams->Add('following-items-selected', $trueDef);
        }

        if(true == $this->params->currentPage->followers){
            $this->templateParams->Add('followers-selected', $trueDef);
        }

        if(true == $this->params->currentPage->followings){
            $this->templateParams->Add('followings-selected', $trueDef);
        }

        if(true == $this->params->currentPage->myItems){
            $this->templateParams->Add('my-items-selected', $trueDef);
        }












        if(isset($this->params->news) && isset($this->params->news['news'])  && is_array($this->params->news['news']) && count($this->params->news['news']) > 0){
            $this->templateParams->Add('news', $this->params->news['news']);
        }else{
            $this->templateParams->Add('news-error-message', _('You have no news yet'));
        }

        if($this->params->notifications['count'] > 0) {

            $readNewsUrl = READ_NEWS_URL;
            $this->templateParams->Add('unread-news-count', $this->params->notifications['count']);
            $this->templateParams->Add('read-news-action',  $readNewsUrl);
        }

        $this->templateParams->Add('list-users-text',       _('List Users'));
        $this->templateParams->Add('list-items-text',       _('List Items'));
        $this->templateParams->Add('update-location-text',  _('Update Location'));
        $this->templateParams->Add('header-message',        $this->params->headerMessage);


        

        return $this->templateParams->GetParams();

    }
}