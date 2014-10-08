<?php
/*
 * @package Custom Actions
 * @version 4.0
 * @license http://creativecommons.org/licenses/by/3.0
 */ 

//Permissions related strings
$txt['permissiongroup_simple_ca_per_simple'] = 'Custom Actions MOD';
$txt['permissiongroup_simple_ca_per_classic'] = 'Custom Actions MOD';
$txt['permissionhelp_ca_createAction'] = 'Custom Actions MOD <br />Membergroups with this permission will be able to create Custom Actions';
$txt['permissionname_ca_createAction'] = 'Can create Custom Actions';
$txt['permissionhelp_ca_editAction'] = 'Custom Actions MOD <br />Membergroups with this permission will be able to EDIT own or any Custom Actions';
$txt['permissionname_ca_editAction'] = 'Can edit Custom Actions';
$txt['permissionname_ca_editAction_own'] = 'Own Actions';
$txt['permissionname_ca_editAction_any'] = 'Any Actions';
$txt['permissionhelp_ca_removeAction'] = 'Custom Actions MOD <br />Membergroups with this permission will be able to REMOVE own or any Custom Actions';
$txt['permissionname_ca_removeAction'] = 'Can remove Custom Actions';
$txt['permissionname_ca_removeAction_own'] = 'Own Actions';
$txt['permissionname_ca_removeAction_any'] = 'Any Actions';

//Error messages
$txt['ca_guest_not_allowed'] = 'Custom Actions Error - Guest access not allowed';
$txt['ca_not_found'] = 'The requested action was not found';
$txt['ca_create_not_allowed'] = 'Custom Actions Error - You are not allowed to create new actions';
$txt['ca_create_sub_not_allowed'] = 'Custom Actions Error - You are not allowed to create sub-actions of actions you don\'t own';
$txt['ca_edit_not_allowed'] = 'Custom Actions Error - You are not allowed to edit this action';
$txt['ca_error_submit'] = 'The Custom Action has the following error or errors that must be corrected before continuing:';
$txt['ca_error_empty_name'] = 'The name for this Custom Action was left blank';
$txt['ca_error_empty_url'] = 'The URL for this Custom Action was left blank';
$txt['ca_error_invalid_url'] = 'Action URLs may only contain letters, numbers and underscores';
$txt['ca_error_duplicate_url'] = 'Action URL already in use. Please choose another URL';
$txt['ca_error_reserved_url'] = 'Action URL is reserved for non-use. Please choose another URL';
$txt['ca_error_empty_body'] = 'The content for this Custom Action was left blank';
$txt['ca_error_empty_groups'] = 'You didn\'t select any membergroups allowed to view this Action';
//Edit page strings
$txt['ca_name'] = 'Action name';
$txt['ca_url'] = 'Action URL';
$txt['ca_menubutton'] = 'Show button on menu?';
$txt['ca_enabled'] = 'Action enabled?';
$txt['ca_new_general'] = 'New Custom Action - General parameters';
$txt['ca_new_body'] = 'New Custom Action - Custom Action content';
$txt['ca_edit_general'] = 'Custom Action - General parameters';
$txt['ca_edit_body'] = 'Custom Action - Custom Action content';
$txt['ca_new_button'] = 'Post new action';
$txt['ca_edit_button'] = 'Submit changes to action';
$txt['ca_html_header_desc'] = 'HTML - This code will be displayed in the header section';
$txt['ca_html_body_desc'] = 'HTML - This code will be displayed in the body section.';
$txt['ca_php_header_desc'] = 'PHP - This code will be evaluated before any templates are displayed. If you don\'t understand this you should just put all your code in the template code box. No output should be displayed here.';
$txt['ca_php_body_desc'] = 'PHP - You should display all output here.';
//Membergroups allowed to see actions
$txt['ca_membergroup_everyone'] = 'Everyone';
$txt['ca_select_membergroups'] = 'Visible to';
$txt['ca_only_admin_change_permissions'] = 'Custom Actions are, by default, visible to anyone. Please contact an Administrator if you want to restrict this.';
$txt['ca_permissions_same_as_parent'] = 'This Sub-Action will inherit parent\'s permissions. Please contact an Administrator if you want to restrict this.';
//Action type --> BBC, HTML, PHP?
$txt['ca_type'] = 'Action type';
$txt['ca_type_0'] = 'BBC';
$txt['ca_type_1'] = 'HTML';
$txt['ca_type_2'] = 'PHP';
//Menu buttons strings
$txt['ca_shorttitle'] = 'Custom Actions';
$txt['ca_make_new'] = 'New Action';
$txt['ca_make_new_sub'] = 'New Sub-Action';
$txt['ca_list_actions'] = 'Actions List';
//Page titles
$txt['ca_edit_title'] = 'Edit Custom Action';
$txt['ca_new_title'] = 'New Custom Action';
//Actions list related strings
$txt['ca_list_enabled'] = 'Enabled';
$txt['ca_list_subs'] = 'Sub-Actions';
$txt['ca_list_title'] = 'List of available Actions';
$txt['ca_list_title_subs'] = 'Sub-Actions For "%1$s"';
$txt['ca_list_none'] = 'You have not created any Custom Actions yet!';
$txt['ca_list_none_sub'] = 'You have not created any Sub-Actions for the "%1$s" Action yet!';


?>