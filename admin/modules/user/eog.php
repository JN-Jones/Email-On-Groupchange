<?php
if(!defined("IN_MYBB"))
{
	header("HTTP/1.0 404 Not Found");
	exit;
}

if(function_exists("myplugins_info"))
    define(MODULE, "myplugins-eog");
else
    define(MODULE, "user-eog");

$page->add_breadcrumb_item("Email on Groupchange", "index.php?module=".MODULE);

if($mybb->input['action'] == "do_add") {
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message("Da lief wohl was falsch... Bitte versuche es erneut", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}

    if(!strlen(trim($mybb->input['gid'])))
	{
		flash_message("Du hast keine Gruppe ausgewählt", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['subject'])))
	{
		flash_message("Du hast keinen Titel angegeben", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['message'])))
	{
		flash_message("Du hast keine Nachricht angegeben", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	
	$insert = array(
		"gid" => (int) $mybb->input['gid'],
		"subject" => $db->escape_string($mybb->input['subject']),
		"message" => $db->escape_string($mybb->input['message'])
	);
	$db->insert_query("eog", $insert);

	flash_message("Erfolgreich hinzugefügt", 'success');
	admin_redirect("index.php?module=".MODULE);

} elseif($mybb->input['action'] == "add") {
	$page->add_breadcrumb_item("Hinzufügen", "index.php?module=".MODULE."&amp;action=add");
	$page->output_header("Hinzufügen");
	generate_tabs("add");
	
	$form = new Form("index.php?module=".MODULE."&amp;action=do_add", "post");
	$form_container = new FormContainer("Hinzufügen");

	$add_group = $form->generate_group_select("gid");
	$form_container->output_row("Gruppe", "Wähle die Gruppe bei der diese Email geschickt werden soll", $add_group);

	$add_subject = $form->generate_text_box("subject");
	$form_container->output_row("Titel", "Hier kannst du den Titel der Email festlegen", $add_subject);

	$add_message = $form->generate_text_area("message");
	$form_container->output_row("Nachricht", "Hier kannst du die Nachricht der Email festlegen", $add_message);

	$form_container->end();

	$buttons[] = $form->generate_submit_button("Speichern");
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();

} elseif($mybb->input['action']=="delete") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message("Du hast keine Email zum Löschen ausgewählt", 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$id=(int)$mybb->input['id'];
	$db->delete_query("eog", "id='{$id}'");
	flash_message("Email erfolgreich gelöscht", 'success');
	admin_redirect("index.php?module=".MODULE);

} elseif($mybb->input['action']=="do_edit") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message("Du hast keine Email zum Bearbeiten ausgewählt", 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$id=(int)$mybb->input['id'];
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message("Da lief wohl was falsch... Bitte versuche es erneut", 'error');
		admin_redirect("index.php?module=".MODULE."&action=edit&id=$id");
	}

    if(!strlen(trim($mybb->input['gid'])))
	{
		flash_message("Du hast keine Gruppe ausgewählt", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['subject'])))
	{
		flash_message("Du hast keinen Titel angegeben", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}
	if(!strlen(trim($mybb->input['message'])))
	{
		flash_message("Du hast keine Nachricht angegeben", 'error');
		admin_redirect("index.php?module=".MODULE."&action=add");
	}


	$update = array(
		"gid" => (int) $mybb->input['gid'],
		"subject" => $db->escape_string($mybb->input['subject']),
		"message" => $db->escape_string($mybb->input['message'])
	);
	$db->update_query("eog", $update, "id='{$id}'");
	flash_message("Email erfolgreich bearbeitet", 'success');
	admin_redirect("index.php?module=".MODULE);

} elseif($mybb->input['action']=="edit") {
	if(!strlen(trim($mybb->input['id'])))
	{
		flash_message("Du hast keine Email zum Bearbeiten ausgewählt", 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$id=(int)$mybb->input['id'];
	$query = $db->simple_select("eog", "*", "id='{$id}'");
	if($db->num_rows($query) != 1)
	{
		flash_message("Interner Fehler beim Auslesen der bisherigen Email", 'error');
		admin_redirect("index.php?module=".MODULE);
	}
	$eog = $db->fetch_array($query);

	$page->add_breadcrumb_item("Bearbeiten", "index.php?module=".MODULE."&amp;action=edit&amp;id=$id");
	$page->output_header("Email");
	generate_tabs("list");

	$form = new Form("index.php?module=".MODULE."&amp;action=do_edit", "post");
	$form_container = new FormContainer("Email");

	$add_group = $form->generate_group_select("gid", array($eog['gid']));
	$form_container->output_row("Gruppe", "Wähle die Gruppe bei der diese Email geschickt werden soll", $add_group);

	$add_subject = $form->generate_text_box("subject", $eog['subject']);
	$form_container->output_row("Titel", "Hier kannst du den Titel der Email festlegen", $add_subject);

	$add_message = $form->generate_text_area("message", $eog['message']);
	$form_container->output_row("Nachricht", "Hier kannst du die Nachricht der Email festlegen", $add_message);

	echo $form->generate_hidden_field("id", $id);
	$form_container->end();

	$buttons[] = $form->generate_submit_button("Speichern");
	$buttons[] = $form->generate_reset_button($lang->reset);
	$form->output_submit_wrapper($buttons);
	$form->end();

} else {
	$page->output_header("Emails");
	generate_tabs("list");

	$table = new Table;
	$table->construct_header("Gruppe");
	$table->construct_header("Titel");
	$table->construct_header("Email");
	$table->construct_header("Optionen", array("colspan"=>"2"));

	$query = $db->simple_select("eog", "*", "", array("order_by"=>"gid"));
	if($db->num_rows($query) > 0)
	{
		while($eog = $db->fetch_array($query))
		{
			$table->construct_cell($groupscache[$eog['gid']]['title']);
			$table->construct_cell($eog['subject']);
			if(strlen($eog['message']) > 20)
			    $eog['message'] = substr($eog['message'], 0, 17)." [...]";
			$table->construct_cell($eog['message']);
			$table->construct_cell("<a href=\"index.php?module=".MODULE."&amp;action=edit&amp;id={$eog['id']}\">{$lang->edit}</a>", array('class' => 'align_center', 'width' => '10%'));
			$table->construct_cell("<a href=\"index.php?module=".MODULE."&amp;action=delete&amp;id={$eog['id']}\">{$lang->delete}</a>", array('class' => 'align_center', 'width' => '10%'));
			$table->construct_row();
		}
	} else {
		$table->construct_cell("Bisher sind keine Emails hinterlegt", array('class' => 'align_center', 'colspan' => 5));
		$table->construct_row();
	}
	$table->output("Emails");
}

$page->output_footer();

function generate_tabs($selected)
{
	global $page;

	$sub_tabs = array();
	$sub_tabs['list'] = array(
		'title' => "Emails auflisten",
		'link' => "index.php?module=".MODULE,
		'description' => "Hier sind alle Emails aufgelistet"
	);
	$sub_tabs['add'] = array(
		'title' => "Email hinzufügen",
		'link' => "index.php?module=".MODULE."&amp;action=add",
		'description' => "Hier kannst du eine Email hinzufügen"
	);

	$page->output_nav_tabs($sub_tabs, $selected);
}
?>