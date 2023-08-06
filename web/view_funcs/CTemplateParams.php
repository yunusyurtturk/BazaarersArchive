<?php
require_once($_SERVER["DOCUMENT_ROOT"] . '/oop/globals.php');

require_once(BASE_PATH . '/model/paths/CPaths.php');
require_once(BASE_PATH . '/model/libraries/Mustache/Autoloader.php');

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/4/2016
 * Time: 22:51
 */
class CTemplateParams
{
    private $params = array();
    private $locale;

    /** @var CTemplateHolder $templateHolder */
    private $templateHolder;

    /** @var CWebView $view */
    private $view;

    public function __construct($locale = 'en_US')
    {
        $this->locale = $locale;
        $this->params['templates'] = array();
        $this->templateHolder = new CTemplateHolder($this->params['templates']);

        $this->view = new CWebView();
    }

    function &GetParams()
    {
        return $this->params;
    }
    function Add($key, &$value)
    {

        $this->params[$key] = $value;

    }
    function AddTo($key, $value){

        if(!isset($this->params[$key])){

            $this->params[$key] = array();
        }

        $this->params[$key][] = $value;
    }


    function Merge(&$array){

        $this->params = array_merge($this->params, $array);
    }

    function AddTemplate()
    {

        return $this->templateHolder;
    }


    function AddCategoryValues($catID){

        require_once(BASE_PATH.'/controller/mobile/controller_cats.php');

        $categories = new CCategoriesController(array('parent' => $catID));
        $childCats = $categories->RunAction();
        $childCatsFormatted = array();

        if ($childCats['hassubcats']) {

            $resultArray['categories-header'] = _('Categories');
            $childCount = count($childCats['catnames']);
            for ($i = 0; $i < $childCount; $i++) {

                $childCatsFormatted[] = array('cat-id' => $childCats['catids'][$i], 'category-link' => '/cat/'.$childCats['catids'][$i], 'category-name' => $childCats['catnames'][$i]);
            }
        }

        $this->params['categories'] = &$childCatsFormatted;
    }
    function AddLocaleValues()
    {

        $locales = array('en_US' => array('name' => 'English', 'chooseLangText' => 'Language'),
                         'tr_TR' => array('name' => 'Türkçe', 'chooseLangText' => 'Dil')
                        );
        $currentLocale = $this->locale;

        if(!isset($locales[$currentLocale]) ){
            $currentLocale = 'en_US';
        }

        $currentLocaleText = $locales[$currentLocale]['name'];
        $currentLocaleChooseLangText = $locales[$currentLocale]['chooseLangText'];


        $this->params = array_merge($this->params, array(
            'locale-english-url' => '/index.php?locale=en_US',
            'locale-turkish-url' => '/index.php?locale=tr_TR',

            'locale-english-text' => 'English',
            'locale-turkish-text' => 'Türkçe',

            'current-locale-text' => $currentLocaleText,
            'choose-locale-text' => $currentLocaleChooseLangText
        )
        );

    }
    function AddAuthUserTopMenu()
    {
        $this->AddLocaleValues();
        $this->params = array_merge($this->params, array(
            'is-logged-in' => true,
            'top-menu-add-item-text' => _('Add'),
            'top-menu-news-text' => _('News'),
            'top-menu-my-profile-text' => _('Profile'),
            'top-menu-item-messages-text' => _('Messages'),
            'top-menu-logout-text' => _('Logout'),


            'top-menu-add-item-link' => '/additem',
            'top-menu-news-link' => '/news',
            'top-menu-my-profile-link' => '/myprofile',
            'top-menu-item-messages-link' => '/itemmessages',
            'top-menu-logout-link' => '/logout',

            'searchrange-link' => '/explore',
            'followings-items-link' => '/followeds-items',

            'followers-link' => '/followers',
            'followings-link' => '/followings',
            'my-items-link' => '/myitems',


            'user-top-menu-explore-text' => _('Explore'),
            'user-top-menu-followings-items-text' => _('Items of people you follow'),

            'user-top-menu-followers-text' => _('Followers'),
            'user-top-menu-followings-text' => _('Followings'),
            'user-top-menu-my-items-text' => _('Items')
        ));
    }

    function AddNotAuthUserTopMenu()
    {
        $this->AddLocaleValues();
        $this->params = array_merge($this->params, array(

            'top-menu-add-item-text' => _('Add'),
            'top-menu-add-item-link' => '/additem',

            'top-menu-login-text' => _('Sign In'),
            'top-menu-register-text' => _('Sign Up'),

            'top-menu-login-link' => '/login',
            'top-menu-register-link' => '/register',
        ));
    }

    function AddHeaderTemplateValues()
    {

        $this->params = array_merge($this->params, array('page-title' => 'Bazaarers',
            'site-addr' => $this->view->GetSiteAddress(),


        ));
    }

    function AddTopMenuTemplateValues()
    {

        $this->params = array_merge($this->params, array('logo-image' => $this->view->GetLogoImage(),
            'logo-image-alt' => '',
            'header-text' => $this->view->GetHeaderText(),
            'header-text-secondary' => $this->view->GetHeaderSecondaryText()));
    }

    function AddNewUserWelcomeTemplateValues()
    {
        $this->params = array_merge($this->params,
            array(
                'new-user' => true,
                'welcome-message-header' => _('Your account has been created'),
                'welcome-message-description' => _('Welcome to Bazaarers. We just sent you an email regarding to your account. 
                                                    You can log in with your username/email and password combination. Ready to sell your first item?')
            )
        );

    }

    function AddLoginTemplateValues()
    {
        $this->params = array_merge($this->params,
        array(



            'login-form-action'  => '/login.php',
            'register-form-action'  => '/register.php',
            'login-email-name' => 'email',
            'login-password-name' => 'password',
            'register-username-name' => 'regusername',
            'register-password-name' => 'regpassword',
            'register-email-name' => 'regemail',

            'login-form-title' => _('Sign In / Sign Up'),
            'login-tab-text' =>  _('Sign In'),
            'register-tab-text' => _('Sign Up'),
            'email-hint' => _('Email'),
            'password-hint' => ('Password'),
            'login-button-text' => _('Sign In'),
            'forget-password-text' => _('Forget Password'),
            'username-hint' => _('Username'),
            'email-hint' => _('Email'),
            'password-hint' => _('Password'),
            'register-button-text' => _('Sign Up')
        ));
    }

    function AddLoginFailedTemplateValues()
    {
        $this->params = array_merge($this->params,
            array(
                'login-result-message'  => _('Failed to login')
            ));
    }








    function InitMainPageParams($isLoggedIn)
    {
        require_once(BASE_PATH.'/controller/mobile/controller_cats.php');
        
        if ($isLoggedIn) {
            $this->AddAuthUserTopMenu();
            $this->Add('loggedIn', $isLoggedIn);
        } else {

            $this->AddNotAuthUserTopMenu();
        }

        $this->AddHeaderTemplateValues();
        $this->AddTopMenuTemplateValues();

        $this->AddTemplate()->ItemInList();
        $this->AddTemplate()->UserInList();
        $this->AddTemplate()->ListHeaderMessage();

        $this->AddCategoryValues(0);

    }


}