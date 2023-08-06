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
class CUsersAdapter
{
    function AdaptSearchRangeUsers(&$users, $loggedIn = false){

        if(isset($users['suggestedUsers']) && sizeof($users['suggestedUsers']) > 0){

            $users['users'] = $users['suggestedUsers']['users'];
            unset($users['suggestedUsers']);
        }

        if(isset($users) && is_array($users) && count($users) > 0) {


            foreach($users['users'] as &$user){

                $user['userpicpath'] = USER_PIC_PATH;
                $user['userpath']    = USER_PATH;
                $user['follow-user-action'] = FOLLOW_USER_ACTION.$user['uid'];

                if(true == $loggedIn) {

                    $user['logged-in'] = true;
                }
            }

        }
        
    }
}