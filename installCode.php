<?php

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF')) 
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

	
	
$hooks = array(
	'integrate_pre_include' => '$sourcedir/Subs-CustomAction.php',
	'integrate_menu_buttons' => 'ca_menubutton',
	'integrate_current_action' => 'ca_currentaction',
	'integrate_actions' => 'ca_action',
	'integrate_load_permissions' => 'ca_permissions',
	'integrate_pre_css_output' => 'ca_load_css',
	'integrate_helpadmin' => 'ca_permissions_help',
);

foreach ($hooks as $hook => $function)
	add_integration_function($hook, $function);