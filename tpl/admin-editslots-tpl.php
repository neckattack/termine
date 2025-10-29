<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * editslots_tpl.php
 * Show/Add/Edit reservation slots for a time vaue
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = (isset($R["id"]) && $R["id"] > 0) ? "Reservierungen editieren" : "Reservierungen hinzufügen";
$gText = $infoText;
require ROOT."/tpl/header-tpl.php";
?>
	<?php
	/*
	echo "<pre>";
	print_r($_SERVER);
	echo "</pre>";
	//*/
	?>

	<div id="content">
		<form id="generateSlots" action="<?= ABSURL . 'web/admin/editslots.php?id=' . $_GET['id'] ?>" method="post">
			<div>
				<?php foreach ($durations AS $key => $duration) {?>
				<div class="block">Terminblock: <input type="text" name="starttimes[]" size="5" value="<?=$startTimes[$key]?>" /> bis <input type="text" name="endtimes[]" size="5" value="<?=$endTimes[$key]?>" /> Uhr. Dauer: jeweils <input type="text" name="durations[]" size="3" value="<?=$duration?>" /> Minuten.</div>
				<?php }?>
				<div id="addBlock"><a class="addblock" href="editslots.php?action=addblock&amp;id=<?=$R["id"]?>">Weiterer Terminblock</a></div>
			</div>
			<div>
				<input type="submit" id="generateSlotsSubmit" value="Erstellen" />
				<input type="hidden" name="id" id="id" value="<?=$R["id"]?>" />
				<input type="hidden" name="date_id" id="date_id" value="<?=$slots[0]["date_id"]?>" />
				<input type="hidden" name="generate" id="generate" value="true" />
			</div>
		</form>

		<div class="slots">
			<h3><?=$slots[0]["date"]?></h3>
			<ul id="slots">
				<?php if (isset($slots[0]["time_start"][0]))                ?>
				<?php 
                require_once ROOT.'/controller/admin-overview_services-controller.php';
                $services = getAllGebuehServices();
                $defaultDiagnosis = isset($slots[0]['default_diagnosis']) ? (string)$slots[0]['default_diagnosis'] : '';
                $defaultServiceIds = array();
                if (isset($slots[0]['default_service_ids']) && trim($slots[0]['default_service_ids'])!=='') {
                    $defaultServiceIds = array_values(array_filter(array_map('intval', explode(',', $slots[0]['default_service_ids'])), function($v){ return $v>0; }));
                }
                foreach ($slots AS $slot) {?>
				<?php if (isset($slot["time_start"][0])) {?>
				<li<?=(isset($slot["res_id"][0])) ? " id=\"res_".$slot["res_id"]."\"" : ""?>>
					<span class="time"><?=$slot["time_start"]?> - <?=$slot["time_end"]?></span>
					                    <span class="name"><?php if (isset($slot["res_name"][1])) {?><a href="mailto:<?=$slot["email"]?>"><?=$slot["res_name"]?></a><?php } else {?>- frei -<?php }?></span>
                    <?php if (isset($slot['res_id']) && $slot['res_id']>0) { ?>
                    <div class="inline-form" style="display:inline-block; margin-left:10px;">
                        <input type="text" name="res_diagnosis[<?=$slot['res_id']?>]" value="<?=htmlspecialchars($defaultDiagnosis)?>" placeholder="Diagnose" style="width:220px;" />
                        <select name="res_services[<?=$slot['res_id']?>][]" multiple="multiple" class="multiselect" style="min-width:260px;">
                            <?php if (is_array($services)) { foreach ($services as $s) { $sel = in_array((int)$s['id'], $defaultServiceIds, true) ? 'selected="selected"' : ''; ?>
                                <option value="<?=$s['id']?>" <?=$sel?>><?=$s['code']?> – <?=$s['title']?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <?php } ?>
					<?php if (isset($slot["res_id"])) {?><a href="editslots.php?action=deletereservation&amp;id=<?=$slot["res_id"]?>" class="delete orange">Reservierung löschen</a><?php }?>
					<a href="editslots.php?action=deletetimeentry&amp;id=<?=$slot["time_id"]?>" class="delete">Termin entfernen</a>
					<?php if(isset($slot["res_id"])) { ?>
						<a title="Klicken Sie auf den Link, um die Buchungs-URL in die Zwischenablage zu kopieren" href="javascript:void(0);" data-content="<?= ABSURL ?>web/bookings.php?e=<?=md5(strtolower($slot["email"]))?>" class="copy-url">Buchungs-URL kopieren</a>
					<?php } ?>
				</li>
				<?php }?>
				<?php }?>
				<?php } else {?>
				<li>Noch kein Eintrag vorhanden</li>
				<?php }?>
			</ul>
		</div>

		<p id="error" <?=(isset($success) && $success < 1) ? ' style="display: block;"' : "" ?>>Es ist ein Fehler aufgetreten! Bitte überprüfen Sie Ihre Eingabe.</p>
		<p id="error-invalid" class="error check-for-js">Bitte überprüfen Sie Ihre Eingabe.</p>
		<p id="error-not-available" class="error check-for-js">Einige der Termine überschneiden sich. Bitte überprüfen Sie Ihre Eingabe.</p>
		<p id="success">Der Kunde wurde erfolgreich eingetragen/geändert.</p>
		<p id="deleted">Der Tag und alle mit ihm verknüpften Termine wurden erfolgreich gelöscht.</p>
	</div>

<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>

<script>
	$('.copy-url').click(function() {        
        navigator.clipboard.writeText($(this).data('content'));
		alert('Die URL wurde in die Zwischenablage kopiert');
    });
</script>