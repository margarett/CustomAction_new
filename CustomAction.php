<?php
/**********************************************************************************
* CustomAction.php                                                                *
***********************************************************************************
* Software Version:           4.0                                                 *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');

function ViewCustomAction()
{
	global $context, $smcFunc, $db_prefix, $txt;
	
	// So which custom action is this?
	$request = $smcFunc['db_query']('', '
		SELECT id_action, name, permissions_mode, action_type, header, body, id_author
		FROM {db_prefix}custom_actions
		WHERE url = {string:url}
			AND enabled = 1',
		array(
			'url' => $context['current_action'],
		)
	);

	$context['action'] = $smcFunc['db_fetch_assoc']($request);

	$smcFunc['db_free_result']($request);

	// By any chance are we in a sub-action?
	if (!empty($_REQUEST['sa']))
	{
		$request = $smcFunc['db_query']('', '
			SELECT id_action, name, permissions_mode, action_type, header, body, id_author,
			FROM {db_prefix}custom_actions
			WHERE url = {string:url}
				AND enabled = 1
				AND id_parent = {int:id_parent}',
			array(
				'id_parent' => $context['action']['id_action'],
				'url' => $_REQUEST['sa'],
			)
		);

		if ($smcFunc['db_num_rows']($request) != 0)
		{
			$sub = $smcFunc['db_fetch_assoc']($request);

			$smcFunc['db_free_result']($request);

			$context['action']['name'] = $sub['name'];
			// Do we have our own permissions?
			if ($sub['permissions_mode'] != 2)
			{
				$context['action']['id_action'] = $sub['id_action'];
				$context['action']['permissions_mode'] = $sub['permissions_mode'];
			}
			$context['action']['action_type'] = $sub['action_type'];
			$context['action']['header'] = $sub['header'];
			$context['action']['body'] = $sub['body'];
		}
	}

	// Are we even allowed to be here? Let's go with easy steps
	$allowed = false;
	if ($context['action']['permissions_mode'] != 1) //If not 1 then it's 0, so we are all allowed :)
		$allowed = true;
	else
	{
		//check. are we allowed to access this action?
		if (allowedTo('ca_' . $context['action']['id_action']))
			$allowed = true;
		else {
			//Another chance yet... Can we edit or remove other people's actions?
			if (allowedTo('edit_custom_page_any') || allowedTo('remove_custom_page_any'))
				$allowed = true;
			else {
				//Last chance! Are we the author of this action?!
				if ($context['user']['id'] == $context['action']['id_author'])
					$allowed = true;
			}
		}
	}
	if (!$allowed)
		fatal_lang_error('custom_action_view_not_allowed', false);
		
	// Do this first to allow it to be overwritten by PHP source file code.
	$context['page_title'] = $context['action']['name'];

	switch ($context['action']['action_type'])
	{

	// Do we need to parse any BBC?
	case 0:
		$context['action']['body'] = parse_bbc($context['action']['body']);
		break;
	case 1:
	// Any HTML headers?
		$context['html_headers'] .= $context['action']['header'];
		break;
	// We have some more stuff to do for PHP actions.
	case 2:
		fixPHP($context['action']['header']);
		fixPHP($context['action']['body']);

		eval($context['action']['header']);
	}

	// Get the templates sorted out!
	loadTemplate('CustomAction');
	$context['sub_template'] = 'view_custom_action';
}

// Get rid of any <? or <?php at the start of code.
function fixPHP(&$code)
{
	$code = preg_replace('~^\s*<\?(php)?~', '', $code);
}

function CustomActionList()
{
	global $context, $txt, $sourcedir, $scripturl, $db_prefix, $smcFunc;
	loadLanguage('CustomAction');
	$context['page_title'] = $txt['ca_list_title'];
	loadTemplate('CustomAction');	
	$context['sub_template'] = 'show_custom_action';
	
	// Are we listing sub-actions?
	if (!empty($_REQUEST['id_action']))
	{
		$id_action = (int) $_REQUEST['id_action'];

		$request = $smcFunc['db_query']('', '
			SELECT name, url, id_author
			FROM {db_prefix}custom_actions
			WHERE id_action = {int:id_action}',
			array(
				'id_action' => $id_action,
			)
		);

		// Found the parent action?
		if ($smcFunc['db_num_rows']($request) != 0)
		{
			list ($parent_name, $parent_url, $id_author) = $smcFunc['db_fetch_row']($request);
			$parent = $id_action;
		}
		else
			$parent = 0;

		$smcFunc['db_free_result']($request);
	}
	else
		$parent = 0;

	// Load up our list.
	require_once($sourcedir . '/Subs-List.php');
	
	$listOptions = array(
		'id' => 'custom_actions',
		'title' => $parent ? sprintf($txt['ca_list_title_subs'], $parent_name) : $txt['ca_list_title'],
		'base_href' => $scripturl . '?action=ca_edit' . ($parent ? ';action=' . $parent : ''),
		'default_sort_col' => 'action_name',
		'no_items_label' => $parent ? sprintf($txt['ca_list_none_sub'], $parent_name) :$txt['ca_list_none'],
		'items_per_page' => 25,
		'get_items' => array(
			'function' => 'list_getCustomActions',
			'params' => array(
				$parent,
			),
		),
		'get_count' => array(
			'function' => 'list_getCustomActionSize',
			'params' => array(
				$parent,
			),
		),
		'columns' => array(
			'action_name' => array(
				'header' => array(
					'value' => $txt['ca_name'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl;

						return $rowData[\'enabled\'] ? \'<a href="\' . $scripturl  . \'?action=' . ($parent ? $parent_url . ';sa=' : '') . '\' . $rowData[\'url\'] . \'">\' . $rowData[\'name\'] . \'</a>\' : $rowData[\'name\'];'),
					// Limit the width if we have the sub-action column.
					'style' => 'width: ' . ($parent ? '62%' : '50%') . ';',
				),
				'sort' => array(
					'default' => 'ca.name',
					'reverse' => 'ca.name DESC',
				),
			),
			'action_type' => array(
				'header' => array(
					'value' => $txt['ca_type'],
					'style' => 'text-align: left;',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						return isset($txt[\'custom_action_type_\' . $rowData[\'action_type\']]) ? $txt[\'custom_action_type_\' . $rowData[\'action_type\']] : $rowData[\'action_type\'];'),
					'style' => 'width: 15%;',
				),
				'sort' => array(
					'default' => 'ca.action_type',
					'reverse' => 'ca.action_type DESC',
				),
			),
			'sub_actions' => array(
				'header' => array(
					'value' => $txt['ca_list_subs'],
					'class' => 'centercol',
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $scripturl;

						return \'<a href="\' . $scripturl . \'?action=ca_list;ca_action=\' . $rowData[\'id_action\'] . \'">\' . $rowData[\'sub_actions\'] . \'</a>\';'),
					'style' => 'width: 12%; text-align: center;',
					'class' => 'centercol',
				),
				'sort' => array(
					'default' => 'COUNT(sa.id_action)',
					'reverse' => 'COUNT(sa.id_action) DESC',
				),
			),
			'enabled' => array(
				'header' => array(
					'value' => $txt['ca_list_enabled'],
				),
				'data' => array(
					'function' => create_function('$rowData', '
						global $txt;

						return $rowData[\'enabled\'] ? $txt[\'yes\'] : $txt[\'no\'];'),
					'style' => 'width: 8%; text-align: center;',
				),
				'sort' => array(
					'default' => 'ca.enabled DESC',
					'reverse' => 'ca.enabled',
				),
			),
			'modify' => array(
				'header' => array(
					'value' => $txt['modify'],
					'class' => 'centercol',
				),
				'data' => array(
					'sprintf' => array(
						'format' => '<a href="' . $scripturl . '?action=ca_edit;ca_action=%1$s">' . $txt['modify'] . '</a>',
						'params' => array(
							'id_action' => false,
						),
					),
					//'style' => 'width: 15%; text-align: center;',
					'class' => 'centercol',
				),
			),
		),
		'additional_rows' => array(
			array(
				'position' => 'below_table_data',
				'value' => '<a href="' . $scripturl . '?action=ca_edit' . ($parent ? ';id_parent=' . $parent : '') . '">' . $txt['ca_make_new' . ($parent ? '_sub' : '')] . '</a>',
				//'value' => '<a class="button_link" href="' . $scripturl . '?action=admin;area=membergroups;sa=add;postgroup">' . $txt['membergroups_add_group'] . '</a>',				
//				'class' => 'titlebg',
			),
		),
	);

	// Will we be needing the sub-action column?
	if ($parent)
		unset($listOptions['columns']['sub_actions']);

	createList($listOptions);
}

function list_getCustomActions($start, $items_per_page, $sort, $parent)
{
	global $smcFunc, $db_prefix, $context;

	//Initialize our list array
	$list = array();
	
	//A guest? No list...
	if (empty($context['user']['is_logged']))
		return $list;
	
	// Load all the actions.
	if ($parent)
		$request = $smcFunc['db_query']('', '
			SELECT ca.id_action, ca.name, ca.url, ca.action_type, 
			ca.enabled, ca.permissions_mode, ca.id_author
			FROM {db_prefix}custom_actions AS ca
			WHERE ca.id_parent = {int:id_parent}
			ORDER BY ' . $sort . '
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
				'id_parent' => $parent,
			)
		);
	else
		$request = $smcFunc['db_query']('', '
			SELECT ca.id_action, ca.name, ca.url, ca.action_type, COUNT(sa.id_action) AS sub_actions,
			ca.enabled, ca.permissions_mode, ca.id_author
			FROM {db_prefix}custom_actions AS ca
				LEFT JOIN {db_prefix}custom_actions AS sa ON (ca.id_action = sa.id_parent)
			WHERE ca.id_parent = 0
			GROUP BY ca.id_action, ca.name, ca.url, ca.action_type, ca.enabled
			ORDER BY ' . $sort . '
			LIMIT ' . $start . ', ' . $items_per_page,
			array(
			)
		);
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		//We need to process what we read. Carefully now. New conditions can be added as desired.
		//Bruce all mighty admin is included here
		if (allowedTo('edit_custom_page_any') || allowedTo('remove_custom_page_any'))
			$list[] = $row;
		//am I the author? If so, of course I can read
		elseif (!empty($context['user']['id']) && ($context['user']['id'] == $row['id_author']))
			$list[] = $row;
		//everyone can read and it is enabled so let it be :)
		elseif (($row['permissions_mode'] == 0) && ($row['enabled'] == 1))
			$list[] = $row;
	}
	$smcFunc['db_free_result']($request);
// echo '<pre>';
// print_r($list);
// echo '</pre>';
// exit;
	return $list;
}

function list_getCustomActionSize($parent)
{
	global $smcFunc, $db_prefix, $context;

	// A guest? No list...
	if (empty($context['user']['is_logged']))
		return 0;

	$request = $smcFunc['db_query']('', '
		SELECT COUNT(*)
		FROM {db_prefix}custom_actions
		WHERE id_parent = {int:id_parent}',
		array(
			'id_parent' => $parent,
		)
	);

	list ($numCustomActions) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	return $numCustomActions;
}

function CustomActionEdit($actionerrors = array())
{
	global $context, $txt, $smcFunc, $db_prefix, $sourcedir, $user_info, $scripturl;
	
	//A guest? Bye, bye!
	if (empty($context['user']['is_logged']))
		fatal_lang_error('custom_action_guest_not_allowed', false);

	loadLanguage('CustomAction');
	loadTemplate('CustomAction');	
	$context['sub_template'] = 'edit_custom_action';
	
	//We need this for membergroup names...
	loadLanguage('Admin');

	// Needed for BBC actions.
	require_once($sourcedir . '/Subs-Post.php');
	
	//This will be handy. For now, this is just the administrator but it can be extended otherwise ;-)
	$can_edit_groups = (!empty($context['user']['is_admin']) ? 1 : 0);
	$can_choose_type = (!empty($context['user']['is_admin']) ? 1 : 0);	
	
	//Do we have a parent action requested?
	$parent = (!empty($_REQUEST['ca_parent']) ? (int)($_REQUEST['ca_parent']) : '');
	//Action?
	$action = (!empty($_REQUEST['ca_action']) ? (int)($_REQUEST['ca_action']) : '');
	
	// Saving?
	if (isset($_POST['save']))
	{
		checkSession();	
		//Get me a list of all actions! There is quite a lot to do with this query...
		$request = $smcFunc['db_query']('', '
			SELECT id_action, id_parent, url, permissions_mode, id_author
			FROM {db_prefix}custom_actions'
		);
		$actions = array();
		while ($row = $smcFunc['db_fetch_assoc']($request))
			$actions[$row['id_action']] = $row;
		$smcFunc['db_free_result']($request);

		//Action type. Some checking that *should* not be needed but, nevertheless...
		$actiontype = !empty($_POST['type']) ? (int)$_POST['type'] : 0;
		if (in_array($actiontype, array(0, 1, 2)))
			$type = $actiontype;
		else
			$type = 0; //Defaults BBC

		//Already some useful attributions
		$enabled = !empty($_POST['enabled']) ? 1 : 0; // Is the action enabled?
		$menu = !empty($_POST['menubutton']) && !$parent ? 1 : 0; // A menu button?
		// Clean the body and headers.
		//$header = !empty($_POST['header']) ? $_POST['header'] : '';
		$name = !empty($_POST['name']) ? $_POST['name'] : '';
		$url = !empty($_POST['url']) ? $_POST['url'] : '';
		$author = $context['user']['id'];
		
		//BBC needs to be parsed
		if ($type == 0)
		{
			$body = !empty($_POST['bbc_body']) ? $_POST['bbc_body'] : '';
			$body = $smcFunc['htmlspecialchars']($body);
			preparsecode($body);
			// No headers for us!
			$header = '';
		}
		elseif ($type == 1) //HTML?
		{
			$body = !empty($_POST['html_body']) ? $_POST['html_body'] : '';
			$header = !empty($_POST['html_header']) ? $_POST['html_header'] : '';
		}
		else //PHP
		{
			$body = !empty($_POST['php_body']) ? $_POST['php_body'] : '';
			$header = !empty($_POST['php_header']) ? $_POST['php_header'] : '';		
		}

		//a useful array of reserved URLs
		$reserved_urls = array('action', 'index');
		
		// Check for errors
		if (empty($name)) //Empty name?
			$actionerrors[] = $txt['ca_error_empty_name'];
		if (empty($url)) //Empty URL?
			$actionerrors[] = $txt['ca_error_empty_url'];
		// Do we have a valid URL?
		$url = strtolower($url);
		if (preg_match('~[^a-z0-9_]~', $url))
			$actionerrors[] = $txt['ca_error_invalid_url'];
		//Is this a reserved URL?
		if (in_array($url, $reserved_urls))
			$actionerrors[] = $txt['ca_error_reserved_url'];
		// And, is this URL already taken?!
		elseif (empty($action) && !empty($url)) { //Only do this when creating a new action!
			foreach ($actions as $temp) {
				if (strcasecmp($temp['url'], $url) == 0) {
					$actionerrors[] = $txt['ca_error_duplicate_url'];
					break;
				}
			}
		}
		//And do we have content?
		if (empty($body))
				$actionerrors[] = $txt['ca_error_empty_body'];
		
		//So, permissions. And error checking while at it :)
		//If I'm not allowed to choose permissions, I just stick -2 in it or take whatever is already in the database.
		$action_groups = array();
		if (!$can_edit_groups)
		{
			if (empty($action)) //new action, period
				$action_groups[] = '-2';
			else
				//Just pick existing permissions in the database
				$action_groups = explode(',', $actions[$action]['permissions_mode']);
		}
		else //I'm Bruce All Mighty, I *HAVE* to choose permissions!
		{
			foreach ($_POST as $key => $value)
				if (stristr($key, 'perm_group'))
					$action_groups[] = substr($key,10);
		}
		
		//Can we select allowed membergroups and we choose nothing?
		if (($can_edit_groups == 1) && empty($action_groups))
				$actionerrors[] = $txt['ca_error_empty_groups'];
		//Any errors? Return, please
		if (!empty($actionerrors)) {
			unset($_POST['save']);
			return CustomActionEdit($actionerrors);
		}
		
		$permissions_mode = implode($action_groups, ',');

		// Update the database.
		if (!empty($action)) //Editing an already existent action
		{
			$smcFunc['db_query']('', '
				UPDATE {db_prefix}custom_actions
				SET name = {string:name}, url = {string:url}, enabled = {int:enabled}, permissions_mode = {string:permissions_mode},
					action_type = {int:action_type}, menu = {int:menu}, header = {string:header}, body = {string:body}
				WHERE id_action = {int:id_action}',
				array(
					'id_action' => $action,
					'name' => $name,
					'url' => $url,
					'enabled' => $enabled,
					'permissions_mode' => $permissions_mode,
					'action_type' => $type,
					'menu' => $menu,
					'header' => $header,
					'body' => $body,
				)
			);
		}
		// A new action.
		else
		{
			$id_parent = !empty($parent) ? $parent : 0;
			// Insert the data.
			$smcFunc['db_insert']('',
				'{db_prefix}custom_actions',
				array(
					'id_parent' => 'int', 'name' => 'string', 'url' => 'string', 'enabled' => 'int',
					'permissions_mode' => 'string', 'action_type' => 'int', 'menu' => 'int', 'header' => 'string', 'body' => 'string', 'id_author' => 'int',
				),
				array(
					$id_parent, $name, $url, $enabled,
					$permissions_mode, $type, $menu, $header, $body, $author,
				),
				array('id_action')
			);
		}

		// Recache.
		recacheCustomActions();

		redirectexit('action=ca_list' . ($parent ? ';ca_action=' . $parent : ''));
	}
	// Deleting?
	elseif (isset($_REQUEST['delete']))
	{
		checkSession();

		// Before we do anything we need to know what to redirect to when we're done.
		$request = $smcFunc['db_query']('', '
			SELECT id_parent
			FROM {db_prefix}custom_actions
			WHERE id_action = {int:id_action}',
			array(
				'id_action' => $context['id_action'],
			)
		);

		if ($smcFunc['db_num_rows']($request) == 0)
			fatal_lang_error('custom_action_not_found', false);

		list ($context['id_parent']) = $smcFunc['db_fetch_row']($request);

		$smcFunc['db_free_result']($request);

		$to_delete = array($context['id_action']);
		// Does this action have any children we need to kill, too?
		$request = $smcFunc['db_query']('', '
			SELECT id_action
			FROM {db_prefix}custom_actions
			WHERE id_parent = {int:id_parent}',
			array(
				'id_parent' => $context['id_action'],
			)
		);

		while ($row = $smcFunc['db_fetch_assoc']($request))
			$to_delete[] = $row['id_action'];
		$smcFunc['db_free_result']($request);

		// Kill them actions :)
		$smcFunc['db_query']('', '
			DELETE FROM {db_prefix}custom_actions
			WHERE id_action IN ({array_int:to_delete})',
			array(
				'to_delete' => $to_delete,
			)
		);

		// We'll need to recache.
		recacheCustomActions();

		redirectexit('action=ca_list' . ($context['id_parent'] ? ';id_action=' . $context['id_parent'	] : ''));
	}
	// Are we editing or creating a new action?
	else
	{
		//New action or edit action?
		if (empty($action)) {
			$context['page_title'] = $txt['ca_new_title'];
			// Are we allowed to create new actions?
			if (!allowedTo('ca_createAction'))
				fatal_lang_error('ca_create_not_allowed', false); //You can't be here, dude!

			//Get some data, because we *might* be returning from an error, right?
			$aux_ca_name = !empty($_POST['name']) ? $_POST['name'] : '';
			$aux_ca_url = !empty($_POST['url']) ? $_POST['url'] : '';
			$aux_id_action = ''; //New action, no ID
			$aux_id_parent = !empty($parent) ? $parent : '';
			$aux_enabled = !empty($_POST['enabled']) ? 1 : 0;
			$aux_type = !empty($_POST['type']) ? $_POST['type'] : 0;
			$aux_menu = !empty($_POST['menubutton']) ? 1 : 0;
			$aux_bbc_body = !empty($_POST['bbc_body']) ? $_POST['bbc_body'] : '';
			$aux_header = !empty($_POST['header']) ? $_POST['header'] : '';
			$aux_body = !empty($_POST['body']) ? $_POST['body'] : '';
			//Permissions are a bit more tricky...
			//Find the "perm_group" items posted, if any...
			$action_groups = array();
			if (isset($_POST))
				foreach ($_POST as $key => $value)
					if (stristr($key, 'perm_group'))
						$action_groups[] = substr($key,10);
			$aux_permissions = !empty($action_groups) ? $action_groups : array();
		}
		else {
			$context['page_title'] = $txt['ca_edit_title'];
			//Retrieve some actions data
			$request = $smcFunc['db_query']('', '
				SELECT id_parent, name, url, enabled, permissions_mode, action_type, menu, header, body, id_author
				FROM {db_prefix}custom_actions
				WHERE id_action = {int:id_action}',
				array(
					'id_action' => $action,
				)
			);
			if ($smcFunc['db_num_rows']($request) == 0)
				fatal_lang_error('custom_action_not_found', false);
			$actiondata = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
			$allowed = false; //Control variable
			if (allowedTo('ca_editAction_any')) //Can we edit anyone's actions?
				$allowed = true;
			elseif (allowedTo('ca_editAction_own') && ($context['user']['id'] == $actiondata['id_author']))
				$allowed = true;
			if (!$allowed)
				fatal_lang_error('ca_edit_not_allowed', false); //You can't be here, dude!				
			//Get some data, because we *might* be returning from an error, right? Like a new action, but instead, data from the table :)
			$aux_ca_name = !empty($_POST['name']) ? $_POST['name'] : $actiondata['name'];
			$aux_ca_url = !empty($_POST['url']) ? $_POST['url'] : $actiondata['url'];
			$aux_id_action = $action;
			$aux_id_parent = !empty($parent) ? $parent : '';
			$aux_enabled = !empty($_POST['enabled']) ? 1 : $actiondata['enabled'];
			$aux_type = !empty($_POST['type']) ? $_POST['type'] : $actiondata['action_type'];
			$aux_menu = !empty($_POST['menubutton']) ? 1 : $actiondata['menu'];
			$aux_bbc_body = !empty($_POST['bbc_body']) ? $_POST['bbc_body'] : $actiondata['body'];
			$aux_header = !empty($_POST['header']) ? $_POST['header'] : $actiondata['header'];
			$aux_body = !empty($_POST['body']) ? $_POST['body'] : $actiondata['body'];
			//Permissions are a bit more tricky...
			//Find the "perm_group" items posted, if any...
			$action_groups = array();
			if (isset($_POST))
				foreach ($_POST as $key => $value)
					if (stristr($key, 'perm_group'))
						$action_groups[] = substr($key,10);
			$aux_permissions = !empty($action_groups) ? $action_groups : explode(',', $actiondata['permissions_mode']);
		}

		// A quick check if we are creating a new action or sub-action
		if (!empty($parent)) {
			//Need to retrieve the owner and permissions of the "parent" action
			$request = $smcFunc['db_query']('', '
				SELECT id_author, permissions_mode
				FROM {db_prefix}custom_actions
				WHERE id_action = {int:id_action}',
				array(
					'id_action' => $parent,
				)
			);

			if ($smcFunc['db_num_rows']($request) == 0)
				fatal_lang_error('ca_not_found', false);
			$parentdata = $smcFunc['db_fetch_assoc']($request);
			$smcFunc['db_free_result']($request);
			
			//We cannot create sub-actions of other people's actions. Including admins, sorry...
			if ($context['user']['id'] != $parentdata['id_author'])
				fatal_lang_error('ca_create_sub_not_allowed', false);
			//Store parent permissions
			$parent_perm = $parentdata['permissions_mode'];
		}
		//Now... Since we *might* be getting here either from new/edit or via error, we need to see if there are some chosen permissions already
		//So, if we are admins, we should fetch the list of membergroups available, so that he can choose the permissions for this action
		if ($can_edit_groups == 1) {
			$membergroups = array();
			//Add "Everyone"
			$membergroups[] = array(
								'id_group' => -2,
								'membergroup_name' => $txt['ca_membergroup_everyone'],
							);			
			//Add "guests"
			$membergroups[] = array(
								'id_group' => -1,
								'membergroup_name' => $txt['membergroups_guests'],
							);
			//Add "regular members"
			$membergroups[] = array(
								'id_group' => 0,
								'membergroup_name' => $txt['membergroups_members'],
							);							
			$request = $smcFunc['db_query']('', '
				SELECT id_group, group_name AS membergroup_name
				FROM {db_prefix}membergroups
				ORDER BY id_group'
			);
			while ($row = $smcFunc['db_fetch_assoc']($request)) {
				if ($row['id_group'] != 1) //Please, do not add administrator to the select list. It just makes no sense!
					$membergroups[] = $row;
			}
			$smcFunc['db_free_result']($request);
							
			//We need a single-dimension array for the membergroups
			$membergroups_ids = array();
			foreach ($membergroups as $group)
				$membergroups_ids[] = $group['id_group'];	
		}
		//Finally, deal with access permissions
		//If parent action permissions exist, these are the permissions!
		if (!empty($parent_perm))
			$permissions_mode = explode(',', $parent_perm); //Parent action permissions.
		elseif (!empty($aux_permissions)) //Existing permissions defined?
			$permissions_mode = $aux_permissions;
		else
			$permissions_mode = array(-2); //Everyone is default.

		// Set up the default options.
		$context['action'] = array(
			'ca_name' => $aux_ca_name,
			'ca_url' => $aux_ca_url,
			'id_action' => $aux_id_action,
			'id_parent' => $aux_id_parent,
			'enabled' => $aux_enabled,
			'type' => $aux_type,
			'menu' => $aux_menu,
			'header' => $aux_header,
			'body' => $aux_body,
			'can_edit_groups' => $can_edit_groups,
			'can_choose_type' => $can_choose_type,
			'permissions_mode' => $permissions_mode,
			'selectable_membergroups' => isset($membergroups) ? $membergroups : '',
			'selectable_membergroups_ids' => isset($membergroups_ids) ? $membergroups_ids : '',
			'params_caption' => (empty($action) ? $txt['ca_new_general'] : $txt['ca_edit_general']),
			'body_caption' => (empty($action) ? $txt['ca_new_body'] : $txt['ca_edit_body']),
			'errors' => $actionerrors,
		);
		// Needed for the editor and message icons.
		require_once($sourcedir . '/Subs-Editor.php');

		//$context['submit_label'] = !empty($action) ? $txt['save'] : $txt['post'];
		// Now create the editor.
		$editorOptions = array(
			'id' => 'bbc_body',
			'value' => $aux_bbc_body,
			'labels' => array(
				'post_button' => (empty($action) ? $txt['ca_new_button'] : $txt['ca_edit_button']),
			),
			// add height and width for the editor
			'height' => '275px',
			'width' => '100%',
			'preview_type' => 0, //no preview. not ready, sorry
		);
		create_control_richedit($editorOptions);
		// Store the ID.
		$context['post_box_name'] = $editorOptions['id'];
		
		//We don't want to show the shortcut texts...
		$context['shortcuts_text'] = '';
		
		// Build the link tree.
		$context['linktree'][] = array(
			'url' => $scripturl . '?action=ca_list',
			'name' => $txt['ca_shorttitle'],
		);
		if (empty($action) && empty($parent))
			$context['linktree'][] = array(
				'name' => '<em>' . $txt['ca_new_title'] . '</em>',
			);
		elseif (empty($action) && !empty($parent))
			$context['linktree'][] = array(
				'name' => '<em>' . $txt['ca_make_new_sub'] . '</em>'
			);
		elseif (!empty($action))
			$context['linktree'][] = array(
				'name' => '<em>' . $txt['ca_edit_title'] . '</em>'
			);
	}

}

function recacheCustomActions()
{
	global $smcFunc, $db_prefix, $context, $user_info;

	// Get all the action names.
	$request = $smcFunc['db_query']('', '
		SELECT id_action, name, url, permissions_mode, menu, id_author
		FROM {db_prefix}custom_actions
		WHERE id_parent = 0
			AND enabled = 1',
		array(
		)
	);

	$cache = array();
	$cache_list = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		$cache_list[] = $row['url'];
		//Custom actions data
		$cache[] = array(
			0 => $row['url'],
			1 => $row['name'],
			2 => explode(',',$row['permissions_mode']),
			3 => $row['id_author'],
			4 => $row['menu'],
		);
	}

	$smcFunc['db_free_result']($request);

	updateSettings(array(
		'ca_cache' => serialize($cache),
		'ca_list' => implode(',', $cache_list),
	));

	// Try to at least clear the cache for them.
	//cache_put_data('menu_buttons-' . implode('_', $user_info['groups']) . '-' . $user_info['language'], null);
}

?>