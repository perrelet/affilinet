<?php
/*
Plugin Name: Affilinet
Description: Affiliates across an entire network of websites? Who'd of funked it!?
Version: 1.0.0
Author: Digitalis Web Design
*/

if (!defined( 'WPINC' )) die;

define('AFFILINET_VERSION', 		'1.0.0');
define('AFFILINET_PATH', 			plugin_dir_path(__FILE__));
define('AFFILINET_URI',				plugin_dir_url(__FILE__));
define('AFFILINET_TEXT_DOMAIN',		'affilinet');
define('AFFILINET_OPTIONS_SLUG',	'affilinet-settings');

require_once AFFILINET_PATH . 'include/affilinet.class.php';

$Affilinet = new Affilinet\Affilinet();
$Affilinet->run();

function Affilinet () {
	global $Affilinet;
	return $Affilinet;
}

require_once AFFILINET_PATH . 'functions.php';
