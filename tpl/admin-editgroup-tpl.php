<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editgroup-tpl.php
 * Edit/add a group
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = (isset($R["id"]) && $R["id"] > 0) ? "Gruppe editieren" : "Gruppe hinzufügen";
$gText = $name;
require ROOT."/tpl/header-tpl.php";
?>

	<div id="content">
		<form id="editGroup" class="edit" action="<?=$_SERVER["REQUEST_URI"]?>" method="post" enctype= "multipart/form-data">
			<div id="errorMsg"><ul><li>errorMsg</li></ul></div>
			<p id="success">Die Gruppe wurde erfolgreich eingetragen/geändert.</p>
			<p id="successImage">Das Bild wurde erfolgreich hochgeladen.</p>
			<p id="error">Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.</p>
			<p id="deleted">Die Gruppe und alle ihre Mitglieder wurden erfolgreich gelöscht.</p>

			<div>
				<div class="row">
					<label for="name">Name</label>
					<input type="text" id="name" name="name" value="<?=$name?>" />
				</div>
				
			<div>
				
				
			
				<div class="row">
					<label for="info">Informationen</label>
					<textarea id="info" name="info" rows="7" cols="70"><?=$info?></textarea>
				</div>
				
				<?php /* // Disable
				<div class="row">
					<label for="upload_image">Bild</label>
					<div id="imageLinks">
						<button type="button" id="saveImageLink">Bild hochladen</button>
						<button type="button" id="deleteImage" class="<?=($hasImage !== true) ? "hide" : ""?>">Bild entfernen</button>
					</div>
					<br />

					<div id="previewImage">
						<a href="../get_image.php?s=<?=base64_encode("id=".$id."&type=group")?>" rel="external"><img src="../get_image.php?s=<?=base64_encode("id=".$id."&type=group")?>" alt="Gruppenbild" /></a>
					</div>
				</div>
				*/ ?>

				<br />
				
				<div class="row">
					<label>Mitglieder:</label>
					<div class="memberships">
						<h4>
							Benutzer
							<?php if ($SUPERADMIN === true) {?>
							<a id="addToGroupLink" class="addLink add-user" href="#addmember.php?group=<?=$id?>">Hinzufügen</a>
							<?php }?>
						</h4>
						<ul class="general-list user-list">
							<?php if (count($members["users"]) > 0) {?>
							<?php foreach ($members["users"] AS $member) {?>
							<li id="entry_user_<?=$member["id"]?>">
								<span><?=$member["name"]?> (<a class="mail" href="mailto:<?=$member["email"]?>"><?=$member["email"]?></a>)</span>
								<a class="change" href="edituser.php?id=<?=$member["id"]?>">Mitglied Ändern</a>
								<a class="delete" href="edituser.php?action=removeuser&amp;type=user&amp;tid=<?=$entry["group_id"]?>&amp;id=<?=$member["id"]?>">Aus Gruppe entfernen</a>
							</li>
							<?php }?>
							<?php } else {?>
							<li id="entry_user_0" class="entry_0">Keine Mitglieder bisher</li>
							<?php }?>
						</ul>

						
						<?php if ($SUPERADMIN === true) {?>
						<div id="addToUserCont" class="addToCont add-to-user">
							<select id="addToGroup" name="addToGroup">
								<option value="">-Bitte wählen-</option>
								<?php foreach ($userList AS $user) {?>
								<option value="<?=$user["id"]?>"><?=$user["name"]?> (<?=$user["email"]?>)</option>
								<?php }?>
							</select>
							<button type="button" class="add" name="addToGroupButton">Hinzufügen</button>
							<button type="button" class="cancel" name="cancelButton">Abbruch</button>
						</div>
						<?php }?>


						<h4>
							Kunden
							<?php if ($SUPERADMIN === true) {?>
							<a id="addToGroupLink" class="addLink add-client" href="#">Hinzufügen</a>
							<?php }?>
						</h4>
						<ul class="general-list client-list">
							<?php if (count($members["clients"]) > 0) {?>
							<?php foreach ($members["clients"] AS $member) {?>
							<li id="entry_client_<?=$member["id"]?>">
								<span><?=$member["name"]?></span>
								<a class="change" href="editclient.php?cid=<?=$member["id"]?>">Kunde Ändern</a>
								<a class="delete" href="editclient.php?action=removeclient&amp;tid=<?=$entry["group_id"]?>&amp;id=<?=$member["id"]?>">Aus Gruppe entfernen</a>
							</li>
							<?php }?>
							<?php } else {?>
							<li id="entry_client_0" class="entry_0">Keine Mitglieder bisher</li>
							<?php }?>
						</ul>

						
						<?php if ($SUPERADMIN === true) {?>
						<div id="addToClientCont" class="addToCont addToContClient add-to-client">
							<select id="addToClient" name="addToGroup">
								<option value="">-Bitte wählen-</option>
								<?php foreach ($clientList AS $client) {?>
								<option value="<?=$client["id"]?>"><?=$client["name"]?></option>
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

		<div id="saveImageCont">
        
			<form id="saveImageForm" action="ajax/image_handler.php" method="post" enctype="multipart/form-data">
				<div>
					<input type="file" id="upload_image" name="upload_image" />
					<input type="submit" id="saveImage" name="saveImage" value="Bild Speichern" />
					<input type="reset" value="Abbruch" />
					<input type="hidden" id="owner_id" name="owner_id" value="<?=$id?>" />
					<input type="hidden" id="owner_type" name="owner_type" value="group" />
					<input type="hidden" name="action" value="save" />
				</div>
			</form>
		</div>
	</div>
	
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>  