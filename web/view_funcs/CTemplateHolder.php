<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once(BASE_PATH.'/model/paths/CPaths.php');
require_once(BASE_PATH.'/model/libraries/Mustache/Autoloader.php');

require_once(BASE_PATH.'/web/view_funcs/view.php');

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/4/2016
 * Time: 22:51
 */





class CTemplateHolder
{
    private $templatesArray;

    function __construct(array &$templateArray)
    {
        $this->templatesArray = &$templateArray;

    }

    function ItemMessages(){
        $this->templatesArray[] = array('template-id' => 'item-message-template',
            'template' => CWebView::LoadTemplate(CTemplatePaths::ItemMessages()));
    }
    function ItemMessageSendForm(){
        $this->templatesArray[] = array('template-id' => 'item-message-send-form',
            'template' => CWebView::LoadTemplate(CTemplatePaths::ItemMessageSendForm()));
    }
    function ItemMessageAgreementForm(){

        $this->templatesArray[] = array('template-id' => 'item-message-agreement-boxes-template',
            'template' => CWebView::LoadTemplate(CTemplatePaths::ItemMessageAgreement()));
    }
    function CategoriesInAddItemForm(&$cats){

        $this->templatesArray[] = array('template-id' => 'categories',
            'template' => json_encode($cats));

    }

    function CategoriesSelector(){
        $this->templatesArray[] = array('template-id' => 'category-selection',
            'template' => CWebView::LoadTemplate(CTemplatePaths::CategorySelector()));
    }

    function ItemInList(){

        $this->templatesArray[] = array('template-id' => 'item-in-list-template',
            'template' => CWebView::LoadTemplate(CTemplatePaths::ItemInList()));

    }

    function UserInList(){
       
        $this->templatesArray[] = array('template-id' => 'user-in-list-template',
            'template' => CWebView::LoadTemplate(CTemplatePaths::UserInList()));
    }
    
    function ListHeaderMessage(){

        $this->templatesArray[] = array('template-id' => 'list-header-message-template',
            'template' => CWebView::LoadTemplate(CTemplatePaths::ListHeaderMessage()));
    }


}
