<?php
/**********************************************************************************
* CustomAction.php                                                                *
***********************************************************************************
* Software Version:           3.0                                                 *
**********************************************************************************/

if (!defined('SMF'))
	die('Hacking attempt...');



// Custom Actions MOD - Check if current user can view a certain action
function ca_allowedTo($action_perms = array())
{
	global $user_info;
	
	// You're never allowed to do something if your data hasn't been loaded yet!
	if (empty($user_info))
		return false;
	//Something went wrong if this triggers...
	if (empty($action_perm))
		return false;
	//If the action is for everyone, go for it. Or are superman?
	if (in_array(array(-2), $action_perms) || $user_info['is_admin'])
		return true;

	//Finally, are we allowed?
	if (count(array_intersect($action_perms, $user_info['groups'])) > 0)
		return true;
	else
		return false;

}
?>