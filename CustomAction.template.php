<?php
/*
 * @package Custom Actions
 * @version 4.0
 * @license http://creativecommons.org/licenses/by/3.0
 */ 

// The main template for the post page.
function template_edit_custom_action()
{
	global $context, $settings, $options, $txt, $scripturl, $modSettings, $counter, $editortxt;
	
	// echo '<pre>';
	// print_r($context['action']);
	// echo '</pre>';
	// exit;

	// Start the javascript... 
	echo '
		<script type="text/javascript"><!-- // --><![CDATA[';

	// When using Go Back due to fatal_error, allow the form to be re-submitted with changes.
	if (isBrowser('is_firefox'))
		echo '
			window.addEventListener("pageshow", reActivate, false);';

	echo '
			function updateInputBoxesType()
			{ ';
	//We keep the function to avoid potential errors if the function does not exist. But the content is removed if the user cannot change types
	if ($context['action']['can_choose_type'] == 1)
		echo '
			
				type = document.getElementById("type").value;
				document.getElementById("bbc_fields").style.display = type == 0 ? "" : "none";
				document.getElementById("html_fields").style.display = type == 1 ? "" : "none";
				document.getElementById("php_fields").style.display = type == 2 ? "" : "none"; ';
	echo '			
			}
	
		// ]]></script>';
		// End of the javascript, start the form and display the link tree.		
	echo '
		<form action="', $scripturl, '?action=ca_edit', $context['action']['id_action'] ? ';ca_action=' . $context['action']['id_action'] : '', '" method="post" accept-charset="', $context['character_set'], '">';

	// Start the main table.
	echo '
			<div class="cat_bar">
				<h3 class="catbg">', $context['action']['params_caption'], '</h3>
			</div>
			<div>
				<div class="roundframe">';
				// <div class="roundframe">', isset($context['current_topic']) ? '
					// <input type="hidden" name="topic" value="' . $context['current_topic'] . '" />' : '';
	// If an error occurred, explain what happened.
	echo '
					<div class="errorbox"', empty($context['action']['errors']) ? ' style="display: none"' : '', ' id="errors">
						<dl>
							<dt>
								<strong id="error_serious">', $txt['ca_error_submit'], '</strong>
							</dt>
							<dd class="error" id="error_list">
								', empty($context['action']['errors']) ? '' : implode('<br />', $context['action']['errors']), '
							</dd>
						</dl>
					</div>';


	// The post header... important stuff
	echo '
					<dl id="post_header">';

/*
	// Guests have to put in their name and email...
	if (isset($context['name']) && isset($context['email']))
	{
		echo '
						<dt>
							<span', isset($context['post_error']['long_name']) || isset($context['post_error']['no_name']) || isset($context['post_error']['bad_name']) ? ' class="error"' : '', ' id="caption_guestname">', $txt['name'], ':</span>
						</dt>
						<dd>
							<input type="text" name="guestname" size="25" value="', $context['name'], '" tabindex="', $context['tabindex']++, '" class="input_text" />
						</dd>';

		if (empty($modSettings['guest_post_no_email']))
			echo '
						<dt>
							<span', isset($context['post_error']['no_email']) || isset($context['post_error']['bad_email']) ? ' class="error"' : '', ' id="caption_email">', $txt['email'], ':</span>
						</dt>
						<dd>
							<input type="text" name="email" size="25" value="', $context['email'], '" tabindex="', $context['tabindex']++, '" class="input_text" />
						</dd>';
	}

						//BACKUP OF THE SYNTAX TO HAVE THE ERROR IN RED
						<dt class="clear">
							<span', isset($context['ca_error']['no_name']) ? ' class="error"' : '', ' id="caption_name">', $txt['ca_name'], ':</span>
						</dt>

	
*/
	// Action name?
	echo '
						<dt class="clear">
							<span', isset($context['ca_error']['no_name']) ? ' class="error"' : '', ' id="caption_name">', $txt['ca_name'], ':</span>
						</dt>
						<dd>
							<input type="text" name="name"', $context['action']['ca_name'] == '' ? '' : ' value="' . $context['action']['ca_name'] . '"', ' tabindex="', $context['tabindex']++, '" size="20" maxlength="20"', isset($context['ca_error']['no_name']) ? ' class="error"' : ' class="input_text"', '/>
						</dd>';
	// Action URL
	echo '
						<dt class="clear">
							<span', isset($context['ca_error']['no_url']) ? ' class="error"' : '', ' id="caption_url">', $txt['ca_url'], ':</span>
						</dt>
						<dd>
							<input type="text" name="url"', $context['action']['ca_url'] == '' ? '' : ' value="' . $context['action']['ca_url'] . '"', ' tabindex="', $context['tabindex']++, '" size="20" maxlength="20"', isset($context['ca_error']['no_url']) ? ' class="error"' : ' class="input_text"', '/>
						</dd>';
	// Show menu button? Sub-actions cannot have menu buttons!
	if (empty($context['action']['id_parent']))
	echo '
						<dt class="clear">
							<span>', $txt['ca_menubutton'], ':</span>
						</dt>
						<dd>
							<input type="checkbox" name="menubutton" id="menubutton"' . (!empty($context['action']['menu']) ? ' checked="checked"' : '') . ' value="1" class="input_check" />
						</dd>';
	// Is action enabled?
	echo '
						<dt class="clear">
							<span>', $txt['ca_enabled'], ':</span>
						</dt>
						<dd>
							<input type="checkbox" name="enabled" id="enabled"' . (!empty($context['action']['enabled']) ? ' checked="checked"' : '') . ' value="1" class="input_check" />
						</dd>';
	//Select action view permissions?
	echo '
						<dt class="clear">
							<span>', $txt['ca_select_membergroups'], ':</span>
						</dt>
						<dd>';
					if ($context['action']['can_edit_groups'] == 1) {
						foreach ($context['action']['selectable_membergroups'] as $group) {
	echo '
							<input type="checkbox" name="perm_group', $group['id_group'], '" id="',$group['membergroup_name'],'" ', in_array($group['id_group'], $context['action']['permissions_mode'], true) ? ' checked="checked"' : '', '  class="input_check" />&nbsp;
							<label for="',$group['membergroup_name'],'">', $group['membergroup_name'], '</label><br />';

						}
					}
					elseif (!empty($context['action']['id_parent']))
	echo '					<span>', $txt['ca_permissions_same_as_parent'], '</span>'; //Message for parent permissions
					else
	echo '					<span>', $txt['ca_only_admin_change_permissions'], '</span>'; //Message for new action
	echo '
						</dd>';
	//can choose option type?
					if ($context['action']['can_choose_type'] == 1) {
	echo '
						<dt class="clear">
							<span>', $txt['ca_type'], ':</span>
						</dt>
						<dd>
							<select name="type" id="type" onchange="updateInputBoxesType();">
								<option value="0" ', $context['action']['type'] == 0 ? 'selected="selected"' : '', '>', $txt['ca_type_0'], '</option>
								<option value="1" ', $context['action']['type'] == 1 ? 'selected="selected"' : '', '>', $txt['ca_type_1'], '</option>
								<option value="2" ', $context['action']['type'] == 2 ? 'selected="selected"' : '', '>', $txt['ca_type_2'], '</option>
							</select>
						</dd>';
			
					}
	//Reserved space for extra fields to add, maybe?
	echo '
					</dl>';
	echo '
				</div>
			</div>
			<br class="clear" />
			<div class="cat_bar">
				<h3 class="catbg">', $context['action']['body_caption'], '</h3>
			</div>
			<div>
				<div class="roundframe">';
	// Show the actual posting area...
	echo '
					<div id="bbc_fields"' . ($context['action']['type'] != 0 ? 'style="display:none"' : '') . '>';
						//Standard message field for BBC
						template_control_richedit($context['post_box_name'], 'smileyBox_message', 'bbcBox_message');
	echo '			</div>';
	//If the user is not allowed to choose types, he won't even see this!
	if ($context['action']['can_choose_type'] == 1)
	echo '
					<div id="html_fields"' . ($context['action']['type'] != 1 ? 'style="display:none"' : '') . '>
						<! -- HEADER AND BODY FOR HTML -->
						<span class="smalltext">', $txt['ca_html_header_desc'], '</span>
						<textarea name="html_header" rows="10" style="width:100%">' . (!empty($context['action']['header']) ? $context['action']['header'] : '') . '</textarea>
						<br />
						<span class="smalltext">', $txt['ca_html_body_desc'], '</span>
						<textarea name="html_body" rows="10" style="width:100%">' . (!empty($context['action']['body']) ? $context['action']['body'] : '') . '</textarea>						
					</div>
					<div id="php_fields"' . ($context['action']['type'] != 2 ? 'style="display:none"' : '') . '>
						<! -- HEADER AND BODY FOR PHP -->
						<span class="smalltext">', $txt['ca_php_header_desc'], '</span>
						<textarea name="php_header" rows="10" style="width:100%">' . (!empty($context['action']['header']) ? $context['action']['header'] : '') . '</textarea>
						<br />
						<span class="smalltext">', $txt['ca_php_body_desc'], '</span>
						<textarea name="php_body" rows="10" style="width:100%">' . (!empty($context['action']['body']) ? $context['action']['body'] : '') . '</textarea>						
					</div>';

	// Finally, the submit buttons.
	echo '
					<br class="clear_right" />
					<span id="post_confirm_buttons">';
						template_control_richedit_buttons($context['post_box_name']);
	echo '
					</span>					
				</div>
			</div>




			<br class="clear" />';

	if (!empty($context['action']['id_parent']))
		echo '	<input type="hidden" name="parent" value="' . $context['action']['id_parent'] . '" />';
	echo '
		<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		<input type="hidden" name="save" value="save" />
		</form>';
			

}
function template_view_custom_action()
{
	global $context;
	
	switch ($context['action']['action_type'])
	{
	// HTML.
	case 0:
		echo $context['action']['body'];
		break;
	// BBC.
	case 1:
	echo '
	<div class="cat_bar">
				<h3 class="catbg"> ', $context['action']['name'],'</h3>			
			</div>
			<div class="windowbg2">
				<div class="content">', $context['action']['body'], '
				</div>
			</div><br />';
		
	//	echo $context['action']['body'];
		break;
	// PHP.
	case 2:
		eval($context['action']['body']);
		break;
	}
}

function template_show_custom_action()
{
	global $context, $txt;
	template_show_list('custom_actions');
}

?>