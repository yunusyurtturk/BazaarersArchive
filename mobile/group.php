<?php
header('Content-Type: text/html; charset=utf-8');

$uid=trim(strip_tags(htmlspecialchars($_POST['userid'])));
$passcode=trim(strip_tags(htmlspecialchars($_POST['passcode'])));
$action=trim(strip_tags(htmlspecialchars($_REQUEST['action'])));
$gid=trim(strip_tags(htmlspecialchars($_REQUEST['gid'])));

require('../controller/mobile/group.php');

$controller = new CGroupController($action, $gid, $uid, $passcode);

$controller->JSON($controller->RunAction());