<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}
if(!$pluginlist)
    $pluginlist = $cache->read("plugins");

$plugins->add_hook("usercp_usergroups_join_group", "eog_group_join");
$plugins->add_hook("managegroup_do_joinrequests_start", "eog_group_joinrequest");
$plugins->add_hook("admin_user_users_edit_commit", "eog_admin");

if(is_array($pluginlist['active']) && in_array("myplugins", $pluginlist['active'])) {
	$plugins->add_hook("myplugins_actions", "eog_myplugins_actions");
	$plugins->add_hook("myplugins_permission", "eog_admin_user_permissions");
} else {
	$plugins->add_hook("admin_user_menu", "eog_admin_user_menu");
	$plugins->add_hook("admin_user_action_handler", "eog_admin_user_action_handler");
	$plugins->add_hook("admin_user_permissions", "eog_admin_user_permissions");
}

function eog_info()
{
	return array(
		"name"			=> "Email on Groupchange",
		"description"	=> "Schreibt eine Email an Nutzer welche einer neuen Gruppe beitreten",
		"website"		=> "http://jonesboard.tk/",
		"author"		=> "Jones",
		"authorsite"	=> "http://jonesboard.tk",
		"version"		=> "1.0",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}

function eog_activate()
{
	global $db;

	$col = $db->build_create_table_collation();
	$db->query("CREATE TABLE `".TABLE_PREFIX."eog` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`gid` int(10),
				`subject` varchar(100) NOT NULL,
				`message` text NOT NULL,
	PRIMARY KEY (`id`) ) ENGINE=MyISAM {$col}");
}

function eog_deactivate()
{
    global $db;

	$db->drop_table("eog");
}

function eog_myplugins_actions($actions)
{
	global $page, $info;

	$actions['eog'] = array(
		"active" => "eog",
		"file" => "../user/eog.php"
	);

	$sub_menu = array();
	$sub_menu['10'] = array("id" => "eog", "title" => "Email on Groupchange", "link" => "index.php?module=myplugins-eog");

	$sidebar = new SidebarItem("Email on Groupchange");
	$sidebar->add_menu_items($sub_menu, $actions[$info]['active']);

	$page->sidebar .= $sidebar->get_markup();

	return $actions;
}

function eog_admin_user_menu($sub_menu)
{
	$sub_menu[] = array("id" => "eog", "title" => "Email on Groupchange", "link" => "index.php?module=user-eog");

	return $sub_menu;
}

function eog_admin_user_action_handler($actions)
{
	$actions['eog'] = array(
		"active" => "eog",
		"file" => "eog.php"
	);

	return $actions;
}

function eog_admin_user_permissions($admin_permissions)
{
	$admin_permissions['eog'] = "Kann die Email bei Gruppenwechsel ändern";

	return $admin_permissions;
}

function eog_group_join()
{
	global $mybb, $usergroup;
	
	eog_email($mybb->user, $usergroup);
}

function eog_group_joinrequest()
{
	global $mybb, $groupscache, $gid;

	if(is_array($mybb->input['request'])) {
		foreach($mybb->input['request'] as $uid => $what) {
			if($what == "accept") {
				$user = get_user($uid);
				eog_email($user, $groupscache[$gid]);
			}
		}
	}
}

function eog_admin()
{
	global $groupscache, $user, $updated_user;

	if($user['usergroup'] != $updated_user['usergroup'])
		eog_email($updated_user, $groupscache[$updated_user['usergroup']]);
}

function eog_email($user, $group)
{
	global $db;
	
	$query = $db->simple_select("eog", "*", "gid='{$group['gid']}'");
	if($db->num_rows($query) == 0)
	    return;
	while($eog = $db->fetch_array($query)) {
		$subject = str_replace("{user}", $user['username'], $eog['subject']);
		$subject = str_replace("{group}", $group['title'], $subject);
		$message = str_replace("{user}", $user['username'], $eog['message']);
		$message = str_replace("{group}", $group['title'], $message);

		my_mail($user['email'], $subject, $message);
	}
}
?>