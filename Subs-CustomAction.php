<?php
/**********************************************************************************
* Subs-CustomAction.php                                                                *
***********************************************************************************
* Software Version:           4.0                                                 *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

/* Actions to append to ?action= */
function ca_action(&$actionArray)
{
	global $modSettings;

	$actionArray['ca_edit'] = array('CustomAction.php' , 'CustomActionEdit');
	$actionArray['ca_list'] = array('CustomAction.php' , 'CustomActionList');
	if (!empty($modSettings['ca_list'])) {
		$ca_temp = explode(',', $modSettings['ca_list']);
		foreach ($ca_temp as $key => $value)
			$actionArray[$value] = array('CustomAction.php', 'ViewCustomAction');
	}
}
	
function ca_menubutton(&$buttons)
{
	global $scripturl, $modSettings, $context, $ca_action_list, $txt;
	//Load the language
	loadLanguage('CustomAction');

	// Any custom actions in modSettings?
	$ca_full = (!empty($modSettings['ca_cache']) ? unserialize($modSettings['ca_cache']) : array());
	$ca_action_list = array();
	//Show a main menu button with all actions listed inside
	$has_actions = false;
	$own_actions = false;
	if (!empty($ca_full)) {
		//Run through all actions
		foreach ($ca_full as $button) {
			//update the total actions array to, later in the file, highlight the "button" when on a custom action
			$ca_action_list[] = $button[0];
			//If we are allowed to see them, then we should see them
			if (!empty($button[2]) && ca_allowedTo($button[2]))
					$has_actions = true;
			//Is any of the actions ours?
			if (!empty($context['user']['id']) && ($context['user']['id'] == $button[3]))
				$own_actions = true;
			//If we evalueate both conditions we don't need to run this anymore!
			if ($has_actions && $own_actions)
				break;
		}
	}
	$ca_sub_buttons = array(); //array for sub-buttons
	//Do we have any actions of our own? Then we can just list them
	//We also list them if we are allowed to change any or edit any
	if ($own_actions || (!empty($ca_full) && (allowedTo('edit_custom_action_any') || allowedTo('remove_custom_action_any')))) {
		$ca_sub_buttons[] = array(
			'title' => $txt['ca_list_actions'],
			'href' => $scripturl . '?action=ca_list',
			'show' => true, //No need to check here
		);
	}
	//Are we allowed to create actions? Then we can also link that
	if (allowedTo('ca_createAction')) {
		$ca_sub_buttons[] = array(
			'title' => $txt['ca_make_new'],
			'href' => $scripturl . '?action=ca_edit',
			'show' => true, //No need to check here
		);
	}
	if ($has_actions) {
		foreach ($ca_full as $button) {
			if (!empty($button[4]))
				$ca_sub_buttons[] = array(
					'title' => $button[1],
					'href' => $scripturl . '?action=' . $button[0],
					'show' => !empty($button[2]) && ca_allowedTo($button[2]),
				);
		}	
	}		
	//Is there anything to create a button?
	if (!empty($ca_sub_buttons)) {
		//Custom Actions menu button
		$buttons['ca_actions'] = array(
			'title' => $txt['ca_shorttitle'],
			'href' => $ca_sub_buttons[0]['href'], //We need a link, so be it the first action in the menu...
			'show' => true,
			'sub_buttons' => $ca_sub_buttons,
			'is_last' => !$context['right_to_left'],
			'action_hook' => 1, //We have actions, thank you :)
		);		
	}
}

function ca_currentaction(&$current_action)
{
	global $context, $ca_action_list;

	if (($context['current_action'] == 'ca_edit') || ($context['current_action'] == 'ca_list') || in_array($context['current_action'],$ca_action_list))
		$current_action = 'ca_actions';
}

function ca_permissions(&$permissionGroups, &$permissionList)
{
	//Load the language
	loadLanguage('CustomAction');
	//Our permissions
	$permissions = array('createAction', 'editAction_any', 'editAction_own', 'removeAction_any', 'removeAction_own');
	
	$permissionGroups['membergroup']['simple'] = array('ca_per_simple');
	$permissionGroups['membergroup']['classic'] = array('ca_per_classic');

	foreach ($permissions as $p)
		$permissionList['membergroup']['ca_'. $p] = array(
														false,
														'ca_per_simple',
														'ca_per_classic'
													);
}

function ca_load_css()
{
	global $context, $settings;
	$context['css_files']['CustomAction.css'] = array(
													'filename' => $settings['theme_url'] . '/css/CustomAction.css?alph21',
													'force_current' => '',
													'seed' => '?alph21',
												);
}

// Custom Actions MOD - Check if current user can view a certain action
function ca_allowedTo($action_perms = array())
{
	global $user_info;
	
	// You're never allowed to do something if your data hasn't been loaded yet!
	if (empty($user_info))
		return false;
	//Something went wrong if this triggers...
	if (empty($action_perms))
		return false;
	//If the action is for everyone, go for it. Or are we superman?
	if (in_array('-2', $action_perms) || $user_info['is_admin'])
		return true;

	//Finally, are we allowed?
	if (count(array_intersect($action_perms, $user_info['groups'])) > 0)
		return true;
	else
		return false;
}
?>