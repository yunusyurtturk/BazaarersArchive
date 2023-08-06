<?php
require_once(BASE_PATH.'/model/paths/CPaths.php');

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 3:19
 * 
 * Adapts categories for Web View
 */

require_once(BASE_PATH . '/model/news/class_news_action_creater.php');

class CNewsAdapter
{
    static function SAdaptNews(&$userNews)
    {
        $returnVal = array();

        if (!empty($userNews) && count($userNews) > 0) {
            foreach ($userNews as $news) {

                $newsArray =  array('news-text' => $news['news'], 'news-date' => $news['date'],
                    'action' => CNewsWebActionCreator::GetAction($news['actionType'], $news['primaryID']));

                if(false == $news['isRead']){

                    $newsArray['unread'] = true;
                }
                $returnVal['news'][] = $newsArray;


            }
        } else {

            $returnVal['news-error-message'] = _('You have no news yet');
        }

        return $returnVal;
    }
}