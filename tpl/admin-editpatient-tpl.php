<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * admin-editpatient-tpl.php
 * Edit/add a patient
 * Expects $PATIENT array from controller (web/admin/editpatient.php)
 */

// Header
$title = (isset($PATIENT['id']) && (int)$PATIENT['id'] > 0) ? 'Patient editieren' : 'Patient hinzufügen';
$gText = isset($PATIENT['email']) ? $PATIENT['email'] : '';
require ROOT.'/tpl/header-tpl.php';
?>

	<div id="content">
		<form id="editPatient" class="edit" action="<?=$_SERVER['REQUEST_URI']?>" method="post">
			<p id="success">Der Patient wurde erfolgreich eingetragen/geändert.</p>
			<p id="error">Es ist ein Fehler aufgetreten! Bitte versuchen Sie es erneut.</p>

			<div>
				<div style="float:left;width:48%;">
					<div class="row">
						<label for="patient_first_name">Vorname</label>
						<input type="text" id="patient_first_name" name="patient_first_name" value="<?=htmlspecialchars($PATIENT['first_name'])?>" />
					</div>

					<div class="row">
						<label for="patient_last_name">Nachname</label>
						<input type="text" id="patient_last_name" name="patient_last_name" value="<?=htmlspecialchars($PATIENT['last_name'])?>" />
					</div>

					<div class="row">
						<label for="patient_email_view">E-Mail</label>
						<span id="patient_email_view" style="display:inline-block; padding-top:4px;">
							<?php $em = htmlspecialchars($PATIENT['email']); ?>
							<a href="mailto:<?=$em?>" title="E-Mail an <?=$em?> senden"><?=$em?></a>
						</span>
						<input type="hidden" name="patient_email" value="<?=htmlspecialchars($PATIENT['email'])?>" />
					</div>

					<div class="row">
						<label for="patient_phone">Telefon</label>
						<input type="text" id="patient_phone" name="patient_phone" value="<?=htmlspecialchars($PATIENT['phone'])?>" />
					</div>

					<div class="row">
						<label for="patient_gender">Anrede</label>
						<select id="patient_gender" name="patient_gender">
							<?php $gd = isset($PATIENT['gender']) ? (int)$PATIENT['gender'] : 0; ?>
							<option value="0" <?=($gd===0)?'selected="selected"':''?>>---</option>
							<option value="1" <?=($gd===1)?'selected="selected"':''?>>Herr</option>
							<option value="2" <?=($gd===2)?'selected="selected"':''?>>Frau</option>
						</select>
					</div>
				</div>

				<div style="float:right;width:48%;">
					<div class="row">
						<label for="patient_birthdate">Geburtsdatum</label>
						<input type="date" id="patient_birthdate" name="patient_birthdate" value="<?=htmlspecialchars($PATIENT['birthdate'])?>" />
					</div>

					<div class="row">
						<label for="patient_address">Adresse</label>
						<input type="text" id="patient_address" name="patient_address" value="<?=htmlspecialchars($PATIENT['address'])?>" />
					</div>

					<div class="row">
						<label for="patient_tax_number">Steuernummer</label>
						<input type="text" id="patient_tax_number" name="patient_tax_number" value="<?=htmlspecialchars($PATIENT['tax_number'])?>" />
					</div>

					<div class="row">
						<label for="patient_diagnosis">Diagnose</label>
						<textarea id="patient_diagnosis" name="patient_diagnosis" rows="5" cols="60" style="width:95%;max-width:95%;box-sizing:border-box;"><?=htmlspecialchars($PATIENT['diagnosis'])?></textarea>
					</div>

					<!-- Passwort wird separat gespeichert -->
				</div>

				<div style="clear:both;"></div>
				<input type="hidden" name="pid" value="<?= (int)$PATIENT['id'] ?>" />
				<input type="submit" name="save_patient" value="Speichern" />
			</div>
		</form>

		<!-- Separates Passwort-Formular -->
		<form id="editPatientPassword" class="edit" action="<?=$_SERVER['REQUEST_URI']?>" method="post" style="margin-top:40px; padding-top:8px; border-top:1px solid #eee;">
			<h4 style="margin:0 0 12px 0; text-align:left;">Passwort ändern</h4>
			<div class="row">
				<label for="patient_password1">Neues Passwort</label>
				<input type="password" id="patient_password1" name="patient_password1" value="" />
			</div>
			<div class="row">
				<label for="patient_password2">Wiederholen</label>
				<input type="password" id="patient_password2" name="patient_password2" value="" />
			</div>
			<input type="hidden" name="pid" value="<?= (int)$PATIENT['id'] ?>" />
			<input type="submit" name="save_patient_password" value="Passwort speichern" />
		</form>
	</div>

<?php
// Footer
require ROOT.'/tpl/footer-tpl.php';
?>
