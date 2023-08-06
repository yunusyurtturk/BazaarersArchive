<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 18:57
 */

require_once ('CIndexPageBaseTemplateValues.php');

class SFollowedsItemsTemplateInputs
{
    public  $baseInputs;


    public $action;
    public $userPicPath;
    public $userPath;
    public $items;

    function __construct(SViewBaseTemplateInputs $baseInputs)
    {
        $this->baseInputs = $baseInputs;
    }


}
class CFollowedsItemsTemplateValues extends CViewBaseTemplateValues
{
    private $followedsItemsParams;

    function __construct(SFollowedsItemsTemplateInputs $params)
    {
        $this->followedsItemsParams = $params;
        parent::__construct($params->baseInputs);
        parent::GetValues();
    }

    function &GetValues()
    {

        $this->templateParams->Merge($this->followedsItemsParams->items);


        if($this->followedsItemsParams->action == 'followers' || $this->followedsItemsParams->action  == 'followings'){

            if(!isset($this->followedsItemsParams->users['users']) || count($this->followedsItemsParams->users['users']) < 1){

                $this->templateParams->Add('error-message',  _('No user found'));
            }
        }


        if($this->followedsItemsParams->action == 'followeds_items' || $this->followedsItemsParams->action == 'follower_items'){

            if(!isset($this->followedsItemsParams->items['items']) || count($this->followedsItemsParams->items['items']) < 1){

                $this->templateParams->Add('error-message',  _('No item found'));
            }
        }


        return $this->templateParams->GetParams();

    }
}