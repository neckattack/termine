<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * edituser-tpl.php
 * Edit/add a user
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = (isset($R["id"]) && $R["id"] > 0) ? "Benutzer editieren" : "Benutzer hinzufügen";
$gText = $username;
require ROOT."/tpl/header-tpl.php";
?>

	<div id="content">
		<form id="editUser" class="edit" action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
			<div id="errorMsg"><ul><li>errorMsg</li></ul></div>
			<p id="success">Der Benutzer wurde erfolgreich eingetragen/geändert.</p>
			<p id="error">Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.</p>
			<p id="deleted">Die Gruppe und alle ihre Mitglieder wurden erfolgreich gelöscht.</p>
			
			<div>
				<div class="row">
					<label for="user_username">Benutzername</label>
					<input type="text" id="user_username" name="user_username" value="<?=$username?>" />
				</div>
				
				<div class="row">
					<label for="user_password1">Passwort</label>
					<input type="password" id="user_password1" name="user_password1" value="" />
				</div>

				<div class="row">
					<label for="user_password2">Wiederholen</label>
					<input type="password" id="user_password2" name="user_password2" value="" />
				</div>
				<br />

				<div class="row">
					<label for="user_gender">Anrede</label>
					<select id="user_gender" name="user_gender">
						<option value="0" <?=($gender == 0) ? 'selected="selected"' : ''?>>---</option>
						<option value="1" <?=($gender == 1) ? 'selected="selected"' : ''?>>Herr</option>
						<option value="2" <?=($gender == 2) ? 'selected="selected"' : ''?>>Frau</option>
					</select>
				</div>

				<div class="row">
					<label for="user_first_name">Vorname</label>
					<input type="text" id="user_first_name" name="user_first_name" value="<?=$first_name?>" />
				</div>
				
				<div class="row">
					<label for="user_last_name">Nachname</label>
					<input type="text" id="user_last_name" name="user_last_name" value="<?=$last_name?>" />
				</div>
				
				<div class="row">
					<label for="user_email">E-Mail</label>
					<input type="text" id="user_email" name="user_email" value="<?=$email?>" />
				</div>

				<div class="row">
					<label for="user_phone">Telefon</label>
					<input type="text" id="user_phone" name="user_phone" value="<?=$phone?>" />
				</div>
				<br />
				
				<div class="row">
					<label>Mitgliedschaften:</label>
					<div class="memberships">
						<h4>
							Kunden
							<?php if ($SUPERADMIN === true) {?>
							<a id="addToClientLink" class="addLink add-client" href="#addmember.php?action=client&amp;user=<?=$id?>">Hinzufügen</a>
							<?php }?>
						</h4>

						<ul class="general-list client-list">
							<?php if (count($memberships["clients"]) > 0) {?>
							<?php foreach ($memberships["clients"] AS $membership) {?>
							<li id="entry_client_<?=$membership["client_id"]?>">
								<span><?=$membership["client_name"]?></span>
								<a class="change" href="editclient.php?cid=<?=$membership["client_id"]?>">Kunde Ändern</a>
								<a class="delete" href="edituser.php?action=removeuser&amp;type=client&amp;tid=<?=$membership["client_id"]?>&amp;id=<?=$entry["id"]?>">Von Kunde entfernen</a>
							</li>
							<?php }?>
							<?php } else {?>
							<li id="entry_client_0" class="entry_0">Keine Kunden-Mitgliedschaften</li>
							<?php }?>
						</ul>


						<?php if ($SUPERADMIN === true) {?>
						<div id="addToClientCont" class="addToCont add-to-client">
							<select id="addToClient" name="addToClient">
								<option value="">-Bitte wählen-</option>
								<?php foreach ($clientList AS $client) {?>
								<option value="<?=$client["id"]?>"><?=$client["name"]?></option>
								<?php }?>
							</select>
							<button type="button" class="add" name="addToClientButton">Hinzufügen</button>
							<button type="button" class="cancel" name="cancelButton">Abbruch</button>
						</div>
						<?php }?>



						<h4>
							Gruppen
							<?php if ($SUPERADMIN === true) {?>
							<a id="addToGroupLink" class="addLink add-group" href="#addmember.php?action=group&amp;user=<?=$id?>">Hinzufügen</a>
							<?php }?>
						</h4>

						<ul class="general-list group-list">
							<?php if (count($memberships["groups"]) > 0) {?>
							<?php foreach ($memberships["groups"] AS $membership) {?>
							<li id="entry_group_<?=$membership["group_id"]?>">
								<span><?=$membership["group_name"]?></span>
								<?php if ($SUPERADMIN === true) {?>
								<a class="change" href="editgroup.php?id=<?=$membership["group_id"]?>">Gruppe Ändern</a>
								<a class="delete" href="edituser.php?action=removeuser&amp;type=group&amp;tid=<?=$membership["group_id"]?>&amp;id=<?=$entry["id"]?>">Aus Gruppe entfernen</a>
								<?php }?>
							</li>
							<?php }?>
							<?php } else {?>
							<li id="entry_group_0" class="entry_0">Keine Gruppen-Mitgliedschaften</li>
							<?php }?>
						</ul>
						

						<?php if ($SUPERADMIN === true) {?>
						<div id="addToGroupCont" class="addToCont add-to-group">
							<select id="addToGroup" name="addToGroup">
								<option value="">-Bitte wählen-</option>
								<?php foreach ($groupList AS $group) {?>
								<option value="<?=$group["group_id"]?>"><?=$group["group_name"]?></option>
								<?php }?>
							</select>
							<button type="button" class="add" name="addToGroupButton">Hinzufügen</button>
							<button type="button" class="cancel" name="cancelButton">Abbruch</button>
						</div>
						<?php }?>
					</div>
				</div>


				<input type="hidden" id="id" name="id" value="<?=$id?>" />
				<input type="submit" id="submit" name="submit" value="Speichern" />
			</div>
		</form>
        
	</div>
	
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>  