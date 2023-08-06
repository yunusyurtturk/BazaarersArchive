<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');

require_once('../controller/mobile/controller_following.php');
require_once(BASE_PATH.'/model/items/module_items_defs.php');


$mock = array();
/* Follow - Unfollow Testi */
/*
$mock['action'] = "follow";
$mock['uid'] = 1;
$mock['userid'] = 2;
*/
/* Followed's Items Testi */
/*
$mock['action'] = "followeds_items";
$mock['uid'] = 1;
$mock['userid'] = 2;
*/
/* Followers Testi */
/*
$mock['action'] = "followers";
$mock['uid'] = 1;
*/

/* Followings Testi */

 $mock['action'] = "followings";
 $mock['uid'] = 4;


var_dump($mock);
$controller = new CFollowingController($mock);

var_dump($controller->RunAction());

