<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CIndexPageBaseTemplateValues.php');

class SIndexPageFollowTemplateInputs
{
    public  $baseInputs;


    public $action;
    public $users;

    function __construct(SViewBaseTemplateInputs $baseInputs)
    {
        $this->baseInputs = $baseInputs;
    }


}
class CIndexPageFollowTemplateValues extends CViewBaseTemplateValues
{
    private $followPageParams;

    function __construct(SIndexPageFollowTemplateInputs $params)
    {
        $this->followPageParams = $params;
        parent::__construct($params->baseInputs);
        parent::GetValues();
    }

    function &GetValues()
    {

        $this->templateParams->Merge($this->followPageParams->users);


        if($this->followPageParams->action == 'followers' || $this->followPageParams->action  == 'followings'){

            if(!isset($this->followPageParams->users['users']) || count($this->followPageParams->users['users']) < 1){

                $this->templateParams->Add('error-message',  _('No user found'));
            }
        }

        return $this->templateParams->GetParams();

    }
}