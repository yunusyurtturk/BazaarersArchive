<?php

class CNewsWebActionCreator
{
    static function GetAction($type, $id)
    {

        $returnVal = '';
        switch($type){

            case NEWS_ACTION_TYPE_USER:
                $returnVal = '/user/'.$id;
            break;
            case NEWS_ACTION_TYPE_ITEM:
                $returnVal = '/item/'.$id;
            break;
            case NEWS_ACTION_TYPE_TIME:

            break;
            case NEWS_ACTION_TYPE_ITEMMESSAGE:
                $returnVal = '/itemmessages';
            break;
            case NEWS_ACTION_TYPE_GROUP:

            break;
        }
        
        return $returnVal;
    }
}