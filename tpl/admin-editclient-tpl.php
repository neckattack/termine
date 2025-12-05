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
					<label for="patient_billing_required">Patientenrechnung notwendig</label>
					<input type="checkbox" id="patient_billing_required" name="patient_billing_required" value="1" <?=(isset($patient_billing_required) && (int)$patient_billing_required===1)?'checked="checked"':''?> />
				</div>

				<div class="row">
					<label for="patient_invoice_flag">Masseurnamen anzeigen</label>
					<input type="checkbox" id="patient_invoice_flag" name="patient_invoice_flag" value="1" <?=(isset($patient_invoice_flag) && (int)$patient_invoice_flag===1)?'checked="checked"':''?> />
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

				<div id="billing-all-section">
					<!-- Neue Default-Felder: oberhalb von 'Tage' -->
					<div class="row">
						<label for="default_diagnosis">Default Diagnose</label>
						<textarea id="default_diagnosis" name="default_diagnosis" rows="4" cols="60"><?=(isset($default_diagnosis)?htmlspecialchars($default_diagnosis):'')?></textarea>
					</div>

					<div class="row">
						<label for="default_service_ids">Default Services</label>
						<select id="default_service_ids" name="default_service_ids[]" multiple="multiple" class="multiselect">
							<?php if (isset($services) && is_array($services)) {?>
							<?php foreach ($services as $s) { 
								$sel = (isset($default_service_ids) && in_array((int)$s['id'], (array)$default_service_ids, true)) ? 'selected="selected"' : '';
							?>
							<option value="<?=$s['id']?>" <?=$sel?>><?=$s['code']?> – <?=$s['title']?></option>
							<?php }?>
							<?php }?>
						</select>
					</div>

					<!-- Anzeige: Client-spezifische Default-Berechnung (nur Anzeige) -->
					<div class="row" id="billing-calc-section">
						<label>Default Services – Berechnung</label>
						<?php 
							$svcIndex = array();
							if (isset($services) && is_array($services)) {
								foreach ($services as $s) { $svcIndex[(int)$s['id']] = $s; }
							}
							$calcIds = isset($default_service_ids) ? (array)$default_service_ids : array();
							$totalClient = 0.0;
						?>
						<div id="svc-calc" style="max-width: 860px;">
							<table style="width:100%; border-collapse:collapse;">
								<thead>
									<tr>
										<th style="text-align:left; border-bottom:1px solid #ccc; padding:4px 6px;">Service</th>
										<th style="text-align:right; border-bottom:1px solid #ccc; padding:4px 6px;">min</th>
										<th style="text-align:right; border-bottom:1px solid #ccc; padding:4px 6px;">mittel</th>
										<th style="text-align:right; border-bottom:1px solid #ccc; padding:4px 6px;">max</th>
										<th style="text-align:right; border-bottom:1px solid #ccc; padding:4px 6px;">Client-Preis</th>
									</tr>
								</thead>
								<tbody id="svc-calc-body">
									<?php foreach ($calcIds as $sid) { $sid=(int)$sid; if (!isset($svcIndex[$sid])) continue; $s=$svcIndex[$sid]; $cp = isset($client_service_prices[$sid]) ? (float)$client_service_prices[$sid] : (isset($s['fee_mid'])?(float)$s['fee_mid']:0.0); $totalClient += $cp; ?>
									<tr>
										<td style="padding:4px 6px; border-bottom:1px solid #eee;"><?=$s['code']?> – <?=$s['title']?></td>
										<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;"><?=isset($s['fee_min'])?number_format((float)$s['fee_min'],2,',','.'):'-'?></td>
										<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;"><?=isset($s['fee_mid'])?number_format((float)$s['fee_mid'],2,',','.'):'-'?></td>
										<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;"><?=isset($s['fee_max'])?number_format((float)$s['fee_max'],2,',','.'):'-'?></td>
										<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;"><strong><?=number_format($cp,2,',','.')?></strong></td>
									</tr>
									<?php } ?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="4" style="padding:6px; text-align:right;">Summe</td>
										<td id="svc-calc-sum" style="padding:6px; text-align:right;"><strong><?=number_format($totalClient,2,',','.')?></strong></td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>

				<script>
				(function(){
                    // Toggle billing section by checkbox
                    function toggleBilling(){
                        var boxAll = document.getElementById('billing-all-section');
                        var cb  = document.getElementById('patient_billing_required');
                        if (!boxAll || !cb) return;
                        boxAll.style.display = cb.checked ? 'block' : 'none';
                    }

					var svcMap = <?php 
						$map = array();
						foreach ($services as $s) {
							$sid = (int)$s['id'];
							$map[$sid] = array(
								'code' => (string)$s['code'],
								'title' => (string)$s['title'],
								'fee_min' => isset($s['fee_min']) ? (float)$s['fee_min'] : null,
								'fee_mid' => isset($s['fee_mid']) ? (float)$s['fee_mid'] : null,
								'fee_max' => isset($s['fee_max']) ? (float)$s['fee_max'] : null,
								'client_price' => isset($client_service_prices[$sid]) ? (float)$client_service_prices[$sid] : (isset($s['fee_mid']) ? (float)$s['fee_mid'] : 0)
							);
						}
						echo json_encode($map);
					?>;

					function renderCalc(selectedIds){
						var body = document.getElementById('svc-calc-body');
						if(!body) return;
						var sum = 0.0, rowsHtml = '';
						(selectedIds||[]).forEach(function(id){
							id = parseInt(id,10);
							var s = svcMap[id];
							if(!s) return;
							var cp = (typeof s.client_price==='number') ? s.client_price : (s.fee_mid||0);
							sum += cp;
							rowsHtml += '<tr>'+
								'<td style="padding:4px 6px; border-bottom:1px solid #eee;">'+s.code+' – '+s.title+'</td>'+
								'<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;">'+(s.fee_min!=null?cpn(s.fee_min):'-')+'</td>'+
								'<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;">'+(s.fee_mid!=null?cpn(s.fee_mid):'-')+'</td>'+
								'<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;">'+(s.fee_max!=null?cpn(s.fee_max):'-')+'</td>'+
								'<td style="padding:4px 6px; text-align:right; border-bottom:1px solid #eee;">'+
									'<input type="number" step="0.01" min="0" name="service_prices['+id+']" value="'+cp.toFixed(2)+'" class="svc-price" style="width:110px; text-align:right;" />'+
								'</td>'+
							'</tr>';
						});
						body.innerHTML = rowsHtml;
						var sumTd = document.getElementById('svc-calc-sum');
						if(sumTd) sumTd.innerHTML = '<strong>'+cpn(sum)+'</strong>';
						bindPriceInputs();
					}

					function cpn(n){
						return (n||0).toFixed(2).replace('.',',');
					}

					var select = document.getElementById('default_service_ids');
					if(select){
						select.addEventListener('change', function(){
							var ids = Array.prototype.slice.call(select.options).filter(function(o){return o.selected;}).map(function(o){return o.value;});
							renderCalc(ids);
						});
						// Initial render
						var ids0 = Array.prototype.slice.call(select.options).filter(function(o){return o.selected;}).map(function(o){return o.value;});
						renderCalc(ids0);
					}

                    var cbBilling = document.getElementById('patient_billing_required');
                    if (cbBilling){
                        cbBilling.addEventListener('change', toggleBilling);
                        toggleBilling();
                    }

					function bindPriceInputs(){
						var inputs = document.querySelectorAll('#svc-calc-body .svc-price');
						inputs.forEach(function(inp){
							inp.addEventListener('input', recomputeSum);
							inp.addEventListener('change', recomputeSum);
						});
					}

					function recomputeSum(){
						var inputs = document.querySelectorAll('#svc-calc-body .svc-price');
						var sum = 0.0;
						inputs.forEach(function(inp){
							var v = parseFloat(inp.value.replace(',', '.'));
							if(!isNaN(v) && v >= 0){ sum += v; }
						});
						var sumTd = document.getElementById('svc-calc-sum');
						if(sumTd) sumTd.innerHTML = '<strong>'+cpn(sum)+'</strong>';
					}
				})();
				</script>

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

				<div class="row">
					<label for="show_past_dates">Vergangene Termine anzeigen</label>
					<input type="checkbox" id="show_past_dates" value="1" />
					<span style="font-size: 12px; color: #666; margin-left: 10px;">(Standard: nur aktuelle und zukünftige Termine)</span>
				</div>

				<div class="row row-date-section" style="position: relative;">
					<label>Tage</label>
					<div class="dates">
						<div style="clear: both; width: 100%; margin-bottom: 10px;">
							<a href="#" id="addDate" style="display: block; font-weight: bold;">+ Hinzufügen</a>
						</div>
						<?php foreach ($cDays AS $day) {?>
						<div id="date_<?=$day["id"]?>" class="date-entry" data-date="<?=isset($day["date"]) ? $day["date"] : ""?>">
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
					</div>
					<div id='copyDateBackdrop' style="display:none; position: fixed; left:0; top:0; width:100%; height:100%; background: rgba(0,0,0,0.2); z-index: 9998;"></div>
					<div id='copyDate' style="display:none; position: fixed; top: 10%; left: 50%; background-color: white; padding: 30px; transform: translate(-50%, 0); border: 2px solid gray; border-radius: 5px; box-shadow: 1px 2px 15px gray; z-index: 9999; max-width: 500px;">
						<span class='close-modal' style="text-align: right; height: 25px; width: 25px; color: red; margin-bottom: 20px; cursor: pointer; position: absolute; top: 10px; right: 10px; font-weight: 600; font-size: 20px;">X</span>
						<h3 style="margin-top: 0; margin-bottom: 15px;">Termine kopieren</h3>
						<p style="font-size: 13px; color: #666; margin-bottom: 15px;">Wähle mehrere Tage aus, zu denen die Termine kopiert werden sollen:</p>
						<div id="multiDatePicker" style="margin-bottom: 15px;"></div>
						<div style="margin-bottom: 15px;">
							<strong>Ausgewählte Tage (<span id="selectedCount">0</span>):</strong>
							<div id="selectedDatesList" style="margin-top: 8px; padding: 10px; background: #f5f5f5; border-radius: 4px; min-height: 40px; max-height: 150px; overflow-y: auto;"></div>
						</div>
						<button style="padding: 8px 20px; background: #f4a900; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;" class="copy-data">Termine kopieren</button>
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
    <?php
        // Bei neuem Kunden (clientID==0) auf 24h voreinstellen, falls leer/0
        $bdh_raw = isset($booking_deadline_hours) ? (int)$booking_deadline_hours : 0;
        $isNewClient = isset($clientID) ? ((int)$clientID === 0) : (isset($R['cid']) ? ((int)$R['cid']===0) : true);
        $__bdh = ($isNewClient && ($bdh_raw === 0)) ? 24 : $bdh_raw;
    ?>
    <!-- Tag: stornovorlauf – Dropdown erweitert um 7 Tage (168h) -->
    <select id="booking_deadline_hours" name="booking_deadline_hours">
        <option value="0" <?=($__bdh==0)?'selected="selected"':''?>>Immer möglich</option>
        <option value="12" <?=($__bdh==12)?'selected="selected"':''?>>Bis 12h vorher</option>
        <option value="24" <?=($__bdh==24)?'selected="selected"':''?>>Bis 24h vorher</option>
        <option value="48" <?=($__bdh==48)?'selected="selected"':''?>>Bis 48h vorher</option>
        <option value="168" <?=($__bdh==168)?'selected="selected"':''?>>Bis 7 Tage vorher</option>
        <option value="custom">Benutzerdefiniert</option>
    </select>
    <input type="number" min="1" step="1" id="booking_deadline_hours_custom" name="booking_deadline_hours_custom" style="display:none;width:80px;" placeholder="Stunden" value="<?=($__bdh!=0&&$__bdh!=12&&$__bdh!=24&&$__bdh!=48&&$__bdh!=168)?$__bdh:''?>" />
</div>
<script>
(function waitForJQuery() {
    if (typeof jQuery === 'undefined') {
        setTimeout(waitForJQuery, 50);
        return;
    }
    jQuery(function($){
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
})();
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

				<script type="text/javascript">
				// Warte bis jQuery vom Footer geladen ist
				(function waitForJQuery() {
					if (typeof jQuery === 'undefined') {
						setTimeout(waitForJQuery, 50);
						return;
					}
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
				})(); // Ende waitForJQuery
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
	var selectedDates = [];

	function formatDateForDisplay(dateStr) {
		// Convert yyyy-mm-dd to dd.mm.yyyy for display
		var parts = dateStr.split('-');
		return parts[2] + '.' + parts[1] + '.' + parts[0];
	}

	// Warte bis jQuery vom Footer geladen ist
	(function waitForJQuery() {
		if (typeof jQuery === 'undefined') {
			setTimeout(waitForJQuery, 50);
			return;
		}
		
		// jQuery ist geladen, jetzt Code ausführen
		jQuery(function($) {

	function updateSelectedDatesList() {
		var $list = $('#selectedDatesList');
		var $count = $('#selectedCount');
		
		if (selectedDates.length === 0) {
			$list.html('<span style="color: #999;">Noch keine Tage ausgewählt</span>');
		} else {
			var html = selectedDates.map(function(date) {
				return '<span style="display: inline-block; background: white; padding: 4px 10px; margin: 2px; border-radius: 3px; font-size: 13px;">' +
					formatDateForDisplay(date) +
					' <a href="javascript:void(0)" data-date="' + date + '" class="remove-date" style="color: #d00; text-decoration: none; font-weight: bold; margin-left: 5px;">×</a>' +
					'</span>';
			}).join('');
			$list.html(html);
		}
		$count.text(selectedDates.length);
	}

	$(document).on('click', '.remove-date', function() {
		var dateToRemove = $(this).data('date');
		selectedDates = selectedDates.filter(function(d) { return d !== dateToRemove; });
		updateSelectedDatesList();
		$('#multiDatePicker').datepicker('refresh');
	});

	$(document).on('click', '.copy-date', function() {
		var id = $(this).data('id');
		showModal(id);
	})

	$(document).on('click', '.copy-data', function() {
		if (selectedDates.length === 0) {
			alert('Bitte wähle mindestens einen Tag aus');
			return;
		}

		var myDateId = $(this).attr('data-id');
		var url = '/admin/ajax/editclient.php?action=copydate';
		var cid = 0;
		var currentUrl = window.location.href;
		var regex = /[?&]cid=([^&]+)/;
		var match = currentUrl.match(regex);

		if (match) {
			cid = match[1];
		} 

		$.ajax({
			url: url,
			dataType: 'json',
			type: 'post',
			data: {newDates: selectedDates, id: myDateId, cid: cid},
			success: function(response){
				if(response.success == 1) {
					alert('Termine erfolgreich zu ' + selectedDates.length + ' Tag(en) kopiert!');
					window.location.reload();
				} else {
					alert('Fehler beim Kopieren. Bitte versuche es erneut.');
				}
			}
		})
	})

	function showModal(id) {
		// Reset selected dates
		selectedDates = [];
		updateSelectedDatesList();

		// Initialize datepicker
		$('#multiDatePicker').datepicker('destroy');
		$('#multiDatePicker').datepicker({
			dateFormat: 'yy-mm-dd',
			firstDay: 1,
			monthNames: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
			monthNamesShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
			dayNames: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
			dayNamesShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
			dayNamesMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
			beforeShowDay: function(date) {
				var dateStr = $.datepicker.formatDate('yy-mm-dd', date);
				var isSelected = selectedDates.indexOf(dateStr) !== -1;
				return [true, isSelected ? 'ui-state-highlight' : ''];
			},
			onSelect: function(dateText) {
				var idx = selectedDates.indexOf(dateText);
				if (idx === -1) {
					selectedDates.push(dateText);
					selectedDates.sort();
				} else {
					selectedDates.splice(idx, 1);
				}
				updateSelectedDatesList();
				$(this).datepicker('refresh');
			}
		});

		var $bd = $('#copyDateBackdrop');
		var $md = $('#copyDate');
		if ($bd.parent()[0] !== document.body) { $bd.appendTo('body'); }
		if ($md.parent()[0] !== document.body) { $md.appendTo('body'); }
		$bd.show();
		$md.show();
		$('.copy-data').attr('data-id', id);
	}

	$('.close-modal').click(function() {
		$('#copyDate').hide();
		$('#copyDateBackdrop').hide();
		$('.copy-data').attr('data-id', null);
		selectedDates = [];
	})

	$(document).on('keydown', function(e){
		if (e.key === 'Escape') {
			$('#copyDate').hide();
			$('#copyDateBackdrop').hide();
			$('.copy-data').attr('data-id', null);
			selectedDates = [];
		}
	});

	// Termine Filter und Sortierung
	$(document).ready(function() {
		var $datesContainer = $('.dates');
		var $dateEntries = $('.date-entry');
		var $checkbox = $('#show_past_dates');
		var today = new Date();
		today.setHours(0, 0, 0, 0);

		// Funktion zum Parsen des deutschen Datumsformats (dd.mm.yyyy)
		function parseGermanDate(dateStr) {
			if (!dateStr || dateStr.trim() === '') return null;
			var parts = dateStr.trim().split('.');
			if (parts.length !== 3) return null;
			// dd.mm.yyyy -> yyyy-mm-dd
			return new Date(parts[2], parts[1] - 1, parts[0]);
		}

		// Sortiere Termine: nächste zuerst (aufsteigend)
		var sortedEntries = $dateEntries.toArray().sort(function(a, b) {
			var dateA = parseGermanDate($(a).attr('data-date'));
			var dateB = parseGermanDate($(b).attr('data-date'));
			
			if (!dateA && !dateB) return 0;
			if (!dateA) return 1;
			if (!dateB) return -1;
			
			return dateA - dateB; // Nächste zuerst (aufsteigend)
		});

		// Entferne alle date-entries und füge sie sortiert wieder ein
		$dateEntries.detach();
		var $addDateWrapper = $('#addDate').parent(); // Get the wrapper div, not the link
		var $lastEntry = $addDateWrapper;
		$.each(sortedEntries, function(index, entry) {
			$(entry).insertAfter($lastEntry);
			$lastEntry = $(entry); // Update reference so next entry is inserted after this one
		});

		// Funktion zum Ein-/Ausblenden vergangener Termine
		function togglePastDates() {
			var showPast = $checkbox.is(':checked');
			
			$('.date-entry').each(function() {
				var dateStr = $(this).attr('data-date');
				var entryDate = parseGermanDate(dateStr);
				
				if (entryDate && entryDate < today) {
					// Vergangener Termin
					if (showPast) {
						$(this).show();
					} else {
						$(this).hide();
					}
				} else {
					// Aktueller oder zukünftiger Termin
					$(this).show();
				}
			});
		}

		// Initial: vergangene Termine ausblenden
		togglePastDates();

		// Event Listener für Checkbox
		$checkbox.on('change', function() {
			togglePastDates();
		});
	});

		}); // Ende jQuery(function($)
	})(); // Ende waitForJQuery
</script>