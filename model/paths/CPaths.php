<?php
require_once('web/CTemplatePaths.php');







define('TEMPLATE_LOADER_PATH', CTemplatePaths::$loader);
define('TEMPLATE_PARTIALS_PATH', CTemplatePaths::$partials);


define('USER_PIC_PATH', DIRECTORY_SEPARATOR.'userpics'.DIRECTORY_SEPARATOR);
define('USER_PATH',     DIRECTORY_SEPARATOR.'user'.DIRECTORY_SEPARATOR);
define('FOLLOW_USER_ACTION',     DIRECTORY_SEPARATOR.'oop'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'following.php?action=follow&followedID=');
define('READ_NEWS_URL', '/oop/web/myprofile.php?action=readNews');

define('ITEM_PICS_PATH', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'oop/resources/itempics/');
define('ITEM_PICS_PATH_RELATIVE', DIRECTORY_SEPARATOR.'oop/resources/itempics/');
define('TEMP_ITEM_PICS_PATH_RELATIVE', DIRECTORY_SEPARATOR.'oop/resources/tmp_itempics/');
define('TEMP_ITEM_PICS_PATH', $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'oop/resources/tmp_itempics/');

define('ITEM_EDIT_PATH', DIRECTORY_SEPARATOR.'oop/web/edititem.php');




