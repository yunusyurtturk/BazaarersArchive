<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/oop/globals.php');
require_once(BASE_PATH.'/model/defs/err_base_defs.php');

define('SOCIAL_NETWORKS_BASE', 1);
define('SOCIAL_NETWORK_FACEBOOK', SOCIAL_NETWORKS_BASE + 0);
define('SOCIAL_NETWORK_TWITTER',  SOCIAL_NETWORKS_BASE + 1);
define('SOCIAL_NETWORK_WHATSAPP', SOCIAL_NETWORKS_BASE + 2);

class CSocialNetworks
{
	public static $Facebook = SOCIAL_NETWORK_FACEBOOK;
	public static $Twitter  = SOCIAL_NETWORK_TWITTER;
	public static $WhatsApp = SOCIAL_NETWORK_WHATSAPP;
}
