<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CViewBaseTemplateValues.php');
require_once ('CIndexPageBaseTemplateValues.php');



class SLoginForm{

    public $formAction;

    public $emailInputName;
    public $passwordInputName;

    public $formTitle;
    public $submitButtonText;
    public $cancelButtonText;

    public $emailHint;
    public $passwordHint;

    public function __construct()
    {
        $this->formAction = '/login.php';
        $this->emailInputName = 'email';
        $this->passwordInputName = 'password';
        $this->formTitle =  _('Sign In / Sign Up');
        $this->submitButtonText =  _('Sign In');
        $this->emailHint = _('Email');
        $this->passwordHint = _('Password');
        $this->cancelButtonText = _('Cancel');
    }

}

class SRegisterForm{

    public $formAction;

    public $emailInputName;
    public $usernameInputName;
    public $passwordInputName;

    public $formTitle;
    public $submitButtonText;
    public $cancelButtonText;

    public $emailHint;
    public $passwordHint;
    public $usernameHint;

    public function __construct()
    {
        $this->formAction = '/register.php';
        $this->emailInputName = 'regemail';
        $this->usernameInputName = 'regusername';
        $this->passwordInputName = 'regpassword';
        $this->formTitle =  _('Sign In / Sign Up');
        $this->submitButtonText =  _('Sign Up');
        $this->emailHint = _('Email');
        $this->passwordHint = _('Password');
        $this->usernameHint = _('Username');
        $this->cancelButtonText = _('Cancel');
    }



}


class SRegOrLogTemplateInputs
{
    /** @var   SViewBaseTemplateInputs */
    public  $baseInputs;


    public $action;

    /** @var   SLoginForm */
    public $loginForm;

    /** @var   SRegisterForm */
    public $registerForm;

    public $loginTabText;
    public $registerTabText;
    public $forgetPasswordText;

    public $error;
    public $errorMessage;



    function __construct(SViewBaseTemplateInputs &$baseInputs)
    {
        $this->baseInputs = $baseInputs;

        $this->loginForm = new SLoginForm();
        $this->registerForm = new SRegisterForm();

        $this->loginTabText = _('Sign In');
        $this->registerTabText = _('Sign Up');
        $this->forgetPasswordText = _('Forget Password?');


    }
}

class CRegOrLogPageTemplateValues extends CViewBaseTemplateValues
{
    private $regOrLogPageParams;

    function __construct(SRegOrLogTemplateInputs $params)
    {
        $this->regOrLogPageParams = $params;
        parent::__construct($params->baseInputs);
        parent::GetValues();
    }

    function &GetValues()
    {
        $trueDef = true;
        $zero = 0;
        if(false === $this->params->loggedIn){

            if('register' == $this->regOrLogPageParams->action){
                $this->templateParams->Add('register',     $trueDef);
            }

            if($this->regOrLogPageParams->error){

                $this->templateParams->Add('error-message',    $this->regOrLogPageParams->errorMessage);
            }
            $this->templateParams->Add('login-tab-text',    $this->regOrLogPageParams->loginTabText);
            $this->templateParams->Add('register-tab-text',  $this->regOrLogPageParams->registerTabText);

            $this->templateParams->Add('login-form-action', $this->regOrLogPageParams->loginForm->formAction);
            $this->templateParams->Add('register-form-action', $this->regOrLogPageParams->registerForm->formAction);

            $this->templateParams->Add('login-email-name', $this->regOrLogPageParams->loginForm->emailInputName);
            $this->templateParams->Add('login-password-name', $this->regOrLogPageParams->loginForm->passwordInputName);

            $this->templateParams->Add('register-username-name', $this->regOrLogPageParams->registerForm->usernameInputName);
            $this->templateParams->Add('register-password-name', $this->regOrLogPageParams->registerForm->passwordInputName);
            $this->templateParams->Add('register-email-name', $this->regOrLogPageParams->registerForm->emailInputName);

            $this->templateParams->Add('login-form-title',  $this->regOrLogPageParams->loginForm->formTitle);

            $this->templateParams->Add('email-hint',        $this->regOrLogPageParams->loginForm->emailHint);
            $this->templateParams->Add('password-hint',     $this->regOrLogPageParams->loginForm->passwordHint);
            $this->templateParams->Add('login-button-text',     $this->regOrLogPageParams->loginForm->submitButtonText);
            $this->templateParams->Add('forget-password-text',     $this->regOrLogPageParams->forgetPasswordText);
            $this->templateParams->Add('username-hint',     $this->regOrLogPageParams->registerForm->usernameHint);

            $this->templateParams->Add('register-button-text',     $this->regOrLogPageParams->registerForm->submitButtonText);




        }else{

            $this->templateParams->Add('login-text', $this->regOrLogPageParams->notLoggedInInputs->loginText);
            $this->templateParams->Add('login-addr', $this->regOrLogPageParams->notLoggedInInputs->loginAddr);
            $this->templateParams->Add('not-logged-in-message', $this->regOrLogPageParams->notLoggedInInputs->notLoggedInMessage);


        }



        return $this->templateParams->GetParams();

    }
}