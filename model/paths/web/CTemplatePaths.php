<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/4/2016
 * Time: 23:12
 */
class CTemplatePaths
{
    public static $loader;

    public static $partials;

    public static function Init()
    {
        self::$loader = BASE_PATH.'/web/templates';
        self::$partials = BASE_PATH.'/web/templates/partials';
    }

    public static function ItemMessages()
    {
        return BASE_PATH . '/web/templates/partials/itemmessage';
    }
    public static function ItemMessageSendForm()
    {
        return BASE_PATH . '/web/templates/partials/itemmessage-send-form';
    }

    public static function ItemMessageAgreement()
    {
        return BASE_PATH . '/web/templates/partials/item-message-agreement-container';
    }
    public static function CategorySelector(){
        return BASE_PATH.'/web/templates/partials/additem-form-category-selection';
    }
    public static function ItemInList(){

        return BASE_PATH . '/web/templates/partials/item-in-list';
    }

    public static function UserInList(){

        return BASE_PATH . '/web/templates/partials/user-in-list';
    }

    public static function ListHeaderMessage(){

        return BASE_PATH . '/web/templates/partials/header-message';
    }

    public static function ReadNews(){

        return BASE_PATH . '/web/templates/partials/header-message';
    }

}

CTemplatePaths::Init();