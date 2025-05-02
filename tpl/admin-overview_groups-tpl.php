<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * overview_groups.php
 * Admin Group index
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = "Gruppenverwaltung";
require ROOT."/tpl/header-tpl.php";
?>

	<div id="content">
		<ul class="menu">
			<li><a href="overview_clients.php">&raquo; Kundenverwaltung</a></li>
			<?php if ($SUPERADMIN === true) {?>
			<li><a href="overview_groups.php">&raquo; Gruppenverwaltung</a></li>
			<li><a href="overview_users.php">&raquo; Benutzerverwaltung</a></li>
			<?php }?>
		</ul>
		
		<a href="editgroup.php">Gruppe hinzufügen</a>

		<ul id="grouplist" class="general-list">
			<?php foreach ($groupList AS $entry) {?>
			<?php $members = ($entry["num_members"] == 1) ? $entry["num_members"]." Mitglied" : $entry["num_members"]." Mitglieder"; ?>
			<li id="entry_<?=$entry["group_id"]?>">
				<span><strong><a href="editgroup.php?id=<?=$entry["group_id"]?>"><?=$entry["group_name"]?></strong></a> (<em><?=$members?></em>)</span>
				<a class="change" href="editgroup.php?id=<?=$entry["group_id"]?>">Ändern</a>
				<?php /* Can't delete the Superadmins */ ?>
				<?php if ($entry["group_id"] != 1) {?>
				<a class="delete" href="editgroup.php?action=deletegroup&amp;id=<?=$entry["group_id"]?>">Löschen</a>
				<?php }?>
			</li>
			<?php }?>
		</ul>
		
	</div>
	
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>  