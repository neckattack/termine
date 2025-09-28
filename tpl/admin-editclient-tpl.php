<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * admin-editclient-tpl.php
 * Edit/add a client
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = (isset($R["cid"]) && $R["cid"] > 0) ? "Kunde editieren" : "Kunde hinzufügen";
$gText = $cName;
require ROOT."/tpl/header-tpl.php";
?>

	<div id="content">
		<form id="editClient" class="edit" action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
			<div id="errorMsg"><ul><li>Bitte wählen Sie mindestens einen Termin aus</li></ul></div>
			<p id="success">Der Kunde wurde erfolgreich eingetragen/geändert.</p>
			<p id="successImage">Das Bild wurde erfolgreich hochgeladen.</p>
			<p id="error">Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.</p>
			<p id="deleted">Der Tag und alle mit ihm verknüpften Termine wurden erfolgreich gelöscht.</p>

			<div class="zebra-rows">
				<div class="row">
					<label for="cName">Name</label>
					<input type="text" id="cName" name="cName" value="<?=$cName?>" />
				</div>

				<div class="row">
					<label for="cEnabled">Aktiviert</label>
					<input type="checkbox" id="cEnabled" name="cEnabled" <?=($cEnabled==1)?"checked=\"checked\"":""?> value="1" />
				</div>

				<div class="row">
					<?php $disabled = ((int) $_SESSION["userid"] === $contact_client_id) ? 'disabled="disabled"' : ''; ?>
					<label for="contact_masseur_id">Ansprechpartner (Masseur)</label>
					<select id="contact_masseur_id" name="contact_masseur_id" <?=$disabled?>>
						<option value="0" <?=($contact_masseur_id == 0) ? 'selected="selected"' : ''?>>Nicht zugeordnet</option>
						<?php /*
						<?php foreach ($client["contacts"]["mixed"] AS $contact) {?>
						<option value="<?=$contact["id"]?>" <?=($contact_masseur_id == $contact["id"]) ? 'selected="selected"' : ''?>><?=$contact["first_name"]?> <?=$contact["last_name"]?></option>
						<?php }?>
						*/ ?>
						<?php foreach ($userList AS $contact) {?>
						<option value="<?=$contact["id"]?>" <?=($contact_masseur_id == $contact["id"]) ? 'selected="selected"' : ''?>><?=$contact["name"]?></option>
						<?php }?>
					</select>
				</div>

				<div class="row">
					<?php $disabled = ((int) $_SESSION["userid"] === $contact_client_id) ? 'disabled="disabled"' : ''; ?>
					<label for="contact_client_id">Ansprechpartner (Kunde)</label>
					<select id="contact_client_id" name="contact_client_id" <?=$disabled?>>
						<option value="0" <?=($contact_client_id == 0) ? 'selected="selected"' : ''?>>Nicht zugeordnet</option>
						<?php /*
						<?php foreach ($client["contacts"]["mixed"] AS $contact) {?>
						<option value="<?=$contact["id"]?>" <?=($contact_client_id == $contact["id"]) ? 'selected="selected"' : ''?>><?=$contact["first_name"]?> <?=$contact["last_name"]?></option>
						<?php }?>
						*/ ?>
						<?php foreach ($userList AS $contact) {?>
						<option value="<?=$contact["id"]?>" <?=($contact_client_id == $contact["id"]) ? 'selected="selected"' : ''?>><?=$contact["name"]?></option>
						<?php }?>
					</select>
				</div>

				<div class="row row-date-section" style="position: relative;">
					<label>Tage</label>
					<div class="dates">
						<?php foreach ($cDays AS $day) {?>
						<div id="date_<?=$day["id"]?>">
							<input type="text" class="datePicker" name="cDays[]" value="<?=isset($day["date"]) ? $day["date"] : ""?>" />
							<?php if (isset($day["id"]) && $day["id"] > 0) {?>
							<a href="editslots.php?id=<?=$day["id"]?>">Termine</a>
							<a href="editclient.php?action=deletedate&amp;id=<?=$day["id"]?>" class="delete">Löschen</a>
							
							<a href="javascript:void(0)" data-id="<?=$day['id']?>" class="copy-date">Copy</a>
							
							<select name="date_masseur_id[<?= $day["id"] ?>]" data-id="<?= $day["id"] ?>" class="date-masseur">
								<option value="0" <?=($day["masseur_id"] == 0) ? 'selected="selected"' : ''?>>Nicht zugeordnet</option>
								<?php foreach ($userList AS $contact) {?>
								<option value="<?=$contact["id"]?>" <?=($day["masseur_id"] == $contact["id"]) ? 'selected="selected"' : ''?>><?=$contact["name"]?></option>
								<?php }?>
							</select>
							<?php }?>
						</div>
						<?php }?>
						<a href="#" id="addDate">Hinzufügen</a>
					</div>
					<div id='copyDate' style="display:none; position: fixed; left: 50%; background-color: white; padding: 30px; transform: translate(-50%, 50%); border: 2px solid gray; border-radius: 5px; box-shadow: 1px 2px 15px gray;">
						<span class='close-modal' style="text-align: right; height: 25px; width: 25px; color: red; margin-bottom: 50px; cursor: pointer; position: relative; top: -20px; right: -20px; font-weight: 600;">X</span>
						<label>Select New Date</label> <br>
						<input type="text" class="datePicker" name="newDate[]" value="<?=isset($day["date"]) ? $day["date"] : ""?>" />
						<br><br>
						<button><a style="line-height: 25px; text-decoration: none;" href="javascript:void(0)" class="copy-data">Copy</a></button>
					</div>
				</div>
            <div class="row">
					<label for="ExistingImage">Existing Image</label>
					<img  src="../get_group_logo.php?id=<?=$_GET['cid']?>&type=client " style="width: 100px; padding-left:450px"/>
				</div>
				<div class="row">
					<label for="cText">Begrüßungstext</label>
					<textarea id="cText" name="cText" rows="7" cols="70"><?=$cText?></textarea>

<div class="row">
    <label for="booking_deadline_hours">Buchungs-/Stornofrist</label>
    <!-- Tag: stornovorlauf – Dropdown erweitert um 7 Tage (168h) -->
    <select id="booking_deadline_hours" name="booking_deadline_hours">
        <option value="0" <?=($booking_deadline_hours==0||$booking_deadline_hours==null)?'selected="selected"':''?>>Immer möglich</option>
        <option value="24" <?=($booking_deadline_hours==24)?'selected="selected"':''?>>Bis 24h vorher</option>
        <option value="48" <?=($booking_deadline_hours==48)?'selected="selected"':''?>>Bis 48h vorher</option>
        <option value="168" <?=($booking_deadline_hours==168)?'selected="selected"':''?>>Bis 7 Tage vorher</option>
        <option value="custom">Benutzerdefiniert</option>
    </select>
    <input type="number" min="1" step="1" id="booking_deadline_hours_custom" name="booking_deadline_hours_custom" style="display:none;width:80px;" placeholder="Stunden" value="<?=($booking_deadline_hours!=0&&$booking_deadline_hours!=24&&$booking_deadline_hours!=48&&$booking_deadline_hours!=168)?$booking_deadline_hours:''?>" />
</div>
<script>
$(function(){
    $('#booking_deadline_hours').change(function(){
        if($(this).val()==='custom') {
            $('#booking_deadline_hours_custom').show();
        } else {
            $('#booking_deadline_hours_custom').hide();
        }
    });
    if($('#booking_deadline_hours').val()==='custom') {
        $('#booking_deadline_hours_custom').show();
    }
});
</script>
				</div>

				<!-- Tag: doppelbuchung_ui – Doppelbuchungen vermeiden (nur UI, Speicherung folgt) -->
				<div class="row">
					<label for="avoid_double_bookings">Doppelbuchungen vermeiden</label>
					<select id="avoid_double_bookings" name="avoid_double_bookings">
						<option value="none" <?=($avoid_double_bookings_mode==='none')?'selected="selected"':''?>>Nie</option>
						<option value="per_date" <?=($avoid_double_bookings_mode==='per_date')?'selected="selected"':''?>>pro Datum</option>
						<option value="per_client" <?=($avoid_double_bookings_mode==='per_client')?'selected="selected"':''?>>pro Kunde</option>
					</select>
				</div>

				<div class="row">
					<label for="emailText"><a href="#tooltip-email" class="tooltip">Text der E-Mail</a></label>
					<textarea id="emailText" name="emailText" rows="7" cols="70"><?=$emailText?></textarea>
				</div>


				<?php /* Group assignment only possible for super admins */ ?>
				<?php if ($SUPERADMIN === true) {?>
				<div class="row">
					<label for="group_id">Gruppe</label>
					<select id="group_id" name="group_id">
						<option value="0">Keine</option>
						<?php foreach ($groupList AS $group) {?>
						<option value="<?=$group["group_id"]?>" <?=((int) $group["group_id"] === $group_id) ? 'selected="selected"' : ''?>><?=$group["group_name"]?></option>
						<?php }?>
					</select>
				</div>
				<?php } else {?>
				<input type="hidden" id="group_id" name="group_id" value="<?=$group_id?>" />
				<?php }?>


				<?php /* Usser assignments only possible for super admins? */ ?>
				<?php if ($SUPERADMIN === true) {?>
				<div class="row">
					<label for="user_ids">Benutzer</label>
					<select id="user_ids" name="user_ids[]" multiple="multiple" class="multiselect">
						<?php foreach ($userList AS $user) {?>
						<option value="<?=$user["id"]?>" <?=(isset($user_ids[$user["id"]])) ? 'selected="selected"' : ''?>><?=$user["name"]?></option>
						<?php }?>
					</select>
				</div>
				<?php } else {?>
				<?php /*
				<input type="hidden" id="group_id" name="group_id" value="<?=$group_id?>" />
				*/ ?>
				<?php }?>


				<div class="row">
					<label for="cLink">Link</label>
					<input type="text" id="cLink" name="cLink" value="<?=$hash["hashlink"]?>" readonly="readonly" size="40" />
				</div>


				<div class="row">
					<label for="payment">Bezahlfunktion</label>
					<input type="checkbox" id="chek_payment" name="chek_payment"<?php if(!empty($price)) { echo "checked";} ?> value="1" />

				</div>

				<div style="display:none;" class="row">
					<label for="price">Price</label>
					<input type="text" id="price" name="price" value="<?=$price?>" size="20" />&nbsp;EUR
				</div>

				<script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
				<script type="text/javascript">
				    jQuery(document).ready(function($) {

						$(function() {
							if($("#chek_payment").is(":checked")) {
							        $("#price").parent().show();
							    } else {
							    	$("#price").parent().hide(); }

							$("#chek_payment").on( "click", function() {
							    if($(this).is(":checked")) {
							        $("#price").parent().show();
							    } else { $("#price").parent().hide(); $("#price").val("0"); }
							})
						});
				    });
				</script>

				<?php /* // Disable
				<label for="upload_image">Bild</label>
				<div id="imageLinks">
					<button type="button" id="saveImageLink">Bild hochladen</button>
					<button type="button" id="deleteImage" class="<?=($hasImage !== true) ? "hide" : ""?>">Bild entfernen</button>
				</div>
				<br />

				<div id="previewImage">
					<a href="../get_image.php?s=<?=base64_encode("id=".$clientID."&type=client")?>" rel="external"><img src="../get_image.php?s=<?=base64_encode("id=".$clientID."&type=client")?>" alt="Kundenbild" /></a>
				</div>
				*/ ?>

                <input type="hidden" name="action" value="ajaxSend">
				<input type="hidden" id="cHash" name="cHash" value="<?=$hash["hash"]?>" />
				<input type="hidden" id="cid" name="cid" value="<?=$clientID?>" />
				<input type="submit" id="submit" name="submit" value="Speichern" />
			</div>
		</form>

		<div id="saveImageCont1">
			<form id="saveImageForm" action="ajax/image_handler.php" method="post" enctype="multipart/form-data">
				<div>
                externes Firmenlogo auswähle
					<input type="file" id="upload_image" name="upload_image" />
					<input type="submit" id="saveImage" name="saveImage" value="Bild Speichern" />
					<input type="reset" value="Abbruch" />
					<input type="hidden" id="owner_id" name="owner_id" value="<?=$clientID?>" />
					<input type="hidden" id="owner_type" name="owner_type" value="client" />
					<input type="hidden" name="action" value="save" />
				</div>
			</form>
			<form id="saveImageForm" action="ajax/image_handler.php" method="post" enctype="multipart/form-data">
				<div>
					<input type="submit" id="deleteImage" name="deleteImage" value="Delete" />
					<input type="hidden" id="owner_id" name="owner_id" value="<?=$clientID?>" />
					<input type="hidden" id="owner_type" name="owner_type" value="client" />
					<input type="hidden" name="action" value="delete" />
				</div>
			</form>

		</div>


		<!-- Tooltip -->
		<div id="tooltip-email" class="tooltip" style="display: none;">
			<h3>Verfügbare Variablen innerhalb des Textes:</h3>
			<dl>
				<dt>&lt;&lt;Benutzername&gt;&gt;</dt>
				<dd>
					Zeigt den Namen des angemeldeten Kunden an.<br />
					(Das Feld "Name" bei der Buchung)
				</dd>

				<dt>&lt;&lt;Termine&gt;&gt;</dt>
				<dd>Zeigt alle gebuchten Termine des Kunden an.</dd>

				<dt>&lt;&lt;Ansprechpartner&gt;&gt;</dt>
				<dd>Zeigt den Namen des Ansprechpartners (Masseur) an.</dd>

				<dt>&lt;&lt;Telefonnummer&gt;&gt;</dt>
				<dd>Zeigt die Telefonnummer des Ansprechpartners (Masseur) an.</dd>

				<dt>&lt;&lt;StornierenLink&gt;&gt;</dt>
				<dd>Zeigt den Link zum Ändern oder Stornieren von Aufträgen an.</dd>
			</dl>
		</div>
	</div>

<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>

<script>
	$(document).on('click', '.copy-date', function() {
		var id = $(this).data('id');
		showModal(id);

	})

	$(document).on('click', '.copy-data', function() {
		//ajax call with date id and new date
		var newDates = $('[name="newDate[]"]').map(function() {
            return $(this).val(); // Get the value of each input
        }).get();
		var myDateId = $(this).attr('data-id');
		
		var url = '/admin/ajax/editclient.php?action=copydate';
		var cid = 0;
		// Get the current URL
		var currentUrl = window.location.href;

		// Regular expression to find 'cid' parameter
		var regex = /[?&]cid=([^&]+)/;

		// Match the 'cid' parameter in the URL
		var match = currentUrl.match(regex);

		if (match) {
			cid = match[1]; // Extracted 'cid' value
		} 

		$.ajax({
			url: url,
			dataType: 'json',
			type: 'post',
			data: {newDates: newDates, id: myDateId, cid: cid},
			success: function(response){
				if(response.status == 1) {
					alert('Data added successfully');
					window.location.reload();
				} else {
					alert('Something went wrong, please try again');
				}
			}
		})
	})

	function showModal(id) {
		console.log(1)
		$('#copyDate').show();
		console.log(2)
		$('.copy-data').attr('data-id', id);
		console.log(3)
	}

	$('.close-modal').click(function() {
		$('#copyDate').hide();
		$('.copy-data').attr('data-id', null);
	})
</script>