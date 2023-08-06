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



class SMyProfileSocialShareUnits
{
    public $shareUrl;
    public $icon;
}
class SMyProfileSocialShares
{
    /** @var  SMyProfileSocialShareUnits */
    public $fb;
    /** @var  SMyProfileSocialShareUnits */
    public $twitter;
    /** @var  SMyProfileSocialShareUnits */
    public $whatsapp;

    public function __construct()
    {
        $this->fb = new SMyProfileSocialShareUnits();
        $this->twitter = new SMyProfileSocialShareUnits();
        $this->whatsapp = new SMyProfileSocialShareUnits();
    }

}
class SMyProfileUserSocialNetworks
{
    public $fb;
    public $twitter;
    public $whatsapp;
}
class SMyProfileUserInfo
{

    public $uid;
    public $username;
    public $email;
    public $location;
    public $info;
    public $userPic;

    /** @var  $isFBConnected SMyProfileUserSocialNetworks */
    public $socialConnections;

    public $followCount;
    public $followerCount;
    public $followedsItemsCount;
    public $itemCount;


    public function __construct()
    {
        $this->socialConnections = new SMyProfileUserSocialNetworks();
    }

}
class SMyProfilePageTemplateInputs
{
    /** @var  $baseInputs SViewBaseTemplateInputs */
    public  $baseParams;


    public $action;
    /** @var  $formInputs SAddItemFormInputs */
    public $formInputs;

    public $isError;
    public $errorMessage;

    /** @var  $myProfileUserInfo SMyProfileUserInfo */
    public $myProfileUserInfo;

    /** @var  SMyProfileUserSocialNetworks */
    public $shareAccounts;

    /** @var  SMyProfileSocialShares */
    public $socialShares;

    /** @var  $formInputs CUser */
    public $user;
    public $items;




    function __construct(SViewBaseTemplateInputs $baseParams)
    {
        $this->baseParams = $baseParams;
        $this->socialShares = new SMyProfileSocialShares();
        $this->myProfileUserInfo = new SMyProfileUserInfo();

    }
}

class CMyProfilePageTemplateValues extends CViewBaseTemplateValues
{
    private $myProfileParams;

    function __construct(SMyProfilePageTemplateInputs $params)
    {
        $this->myProfileParams = $params;
        parent::__construct($params->baseParams);
        parent::GetValues();
    }

    function &GetValues()
    {
        require_once('view_funcs/view.php');
        $view = new CWebView();

        $trueDef = true;
        if(true === $this->params->loggedIn){

            $this->templateParams->Add('logged-in', $trueDef);

            $this->templateParams->Add('header-message',_('Your items'));

            if('items' == $this->myProfileParams->action){

                if(is_array($this->myProfileParams->items) && count($this->myProfileParams->items) > 0) {
                    $this->templateParams->Add('items', $this->myProfileParams->items);
                }else{

                    $this->templateParams->Add('error-message', _('You don\'t have any items yet'));
                }
            }

            /* SPECIFIC PAGE TEMPLATE PARAMS */
            $this->templateParams->Add('followerCount',         $this->myProfileParams->myProfileUserInfo->followerCount  );
            $this->templateParams->Add('followedsItemsCount',   $this->myProfileParams->myProfileUserInfo->followedsItemsCount  );
            $this->templateParams->Add('itemCount',             $this->myProfileParams->myProfileUserInfo->itemCount  );
            $this->templateParams->Add('isFBConnected',         $this->myProfileParams->myProfileUserInfo->socialConnections->fb  );
            $this->templateParams->Add('followCount',           $this->myProfileParams->myProfileUserInfo->followCount  );
            $this->templateParams->Add('username',              $this->myProfileParams->myProfileUserInfo->username  );
            $this->templateParams->Add('usermail',              $this->myProfileParams->myProfileUserInfo->email  );
            $this->templateParams->Add('userinfo',              $this->myProfileParams->myProfileUserInfo->info  );
            $this->templateParams->Add('userpic',               $this->myProfileParams->myProfileUserInfo->userPic  );

            $this->templateParams->Add('news',                  $this->myProfileParams->baseParams->news  );

            $this->templateParams->Add('facebook-share-url',                  $this->myProfileParams->socialShares->fb->shareUrl  );
            $this->templateParams->Add('facebook-icon',                  $this->myProfileParams->socialShares->fb->icon  );
            $this->templateParams->Add('twitter-share-url',                  $this->myProfileParams->socialShares->twitter->shareUrl  );
            $this->templateParams->Add('twitter-icon',                  $this->myProfileParams->socialShares->twitter->icon  );
            $this->templateParams->Add('whatsapp-share-url',                  $this->myProfileParams->socialShares->whatsapp->shareUrl  );
            $this->templateParams->Add('whatsapp-icon',                  $this->myProfileParams->socialShares->whatsapp->icon  );

            $this->templateParams->Merge($view->GetMyProfileTemplateValues(true));
            $this->templateParams->Merge($view->GetMyProfileEmailOpsTemplateValues($this->myProfileParams->myProfileUserInfo->email));
            $this->templateParams->Merge($view->GetMyProfilePasswordOpsTemplateValues(true));
            $this->templateParams->Merge($view->GetMyProfileProfileOpsTemplateValues());
            $this->templateParams->Merge($view->GetMyProfileLocationOpsTemplateValues());
            $this->templateParams->Merge($view->GetMyProfileUserpicOpsTemplateValues());


        }else{

            $this->templateParams->Add('login-text', $this->myProfileParams->notLoggedInInputs->loginText);
            $this->templateParams->Add('login-addr', $this->myProfileParams->notLoggedInInputs->loginAddr);
            $this->templateParams->Add('not-logged-in-message', $this->myProfileParams->notLoggedInInputs->notLoggedInMessage);

        }




        /*************************************/

        /* Additional Templates */
        return $this->templateParams->GetParams();

    }
}