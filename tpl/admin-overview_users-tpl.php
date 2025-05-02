<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * overview_users.php
 * Admin User index
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = "Benutzerverwaltung";
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
		
		<a href="edituser.php">Benutzer hinzufügen</a>

		<ul id="userlist" class="general-list">
			<?php foreach ($userList AS $entry) {?>
			<li id="entry_<?=$entry["id"]?>">
				<span><strong><a href="edituser.php?id=<?=$entry["id"]?>"><?=$entry["name"]?></strong></a> (<a class="mail" href="mailto:<?=$entry["email"]?>"><?=$entry["email"]?></a>)</span>
				<a class="change" href="edituser.php?id=<?=$entry["id"]?>">Ändern</a>
				<?php /* Can't delete the Superadmin */ ?>
				<?php if ($entry["id"] != 1) {?>
				<a class="delete" href="edituser.php?action=deleteuser&amp;id=<?=$entry["id"]?>">Löschen</a>
				<?php }?>
			</li>
			<?php }?>
		</ul>
		
	</div>
	
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>  