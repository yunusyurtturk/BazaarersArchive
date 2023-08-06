<?php

/**
 * Created by PhpStorm.
 * User: Elektronik
 * Date: 6/5/2016
 * Time: 3:45
 */
class CUserInList extends CModelBaseWithDB
{
    private $uid;

    function __construct(array $dependicies = array())
    {
        parent::__construct($dependicies);

    }

    function &GetUser(&$userID, CUser &$currentUser = null)
    {
        $user = new CUser($userID, array('db' => $this->db));

        if(null != $currentUser){


            $is_following = $currentUser->IsFollowing($userID);
            $distance     = $currentUser->GetDistanceWith($userID);
        }else{
            $is_following = false;
            $distance = '?';
        }


        $returnVal = array(
            'uid'=>$userID,
            'is_following'=> $is_following,
            'distance'=>  $distance,
            'userpic'=>  $user->GetPic(),
            'username'=>$user->GetUsername(),
            'itemcount'=>  $user->GetItemCount().' '._('items'),
            'userpath' => '/user/',
            'userpicpath' => '/userpics/',
            'lastactive'=>date('d.m.Y', $user->GetLastActiveTime()));

        return $returnVal;

    }
}