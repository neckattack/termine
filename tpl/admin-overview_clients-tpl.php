<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * admin-overview_clients-tpl.php
 * Admin Client Overview
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = "Kundenverwaltung";
require ROOT."/tpl/header-tpl.php";
//farooq - 23/05/2024
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'alphabetical';
if($sortBy != 'alphabetical') {
	$url = ABSURL . '/web/admin/overview_clients.php?sort_by=alphabetical';
	$label = 'Sortieren Sie nach alphabetischer Reihenfolge';
} else {
	$url = ABSURL . '/web/admin/overview_clients.php?sort_by=upcoming';
	$label = 'Sortieren nach bevorstehender Bestellung';
}
?>

	<div id="content">
		<ul class="menu">
			<li><a href="overview_clients.php">&raquo; Kundenverwaltung</a></li>
			<?php if ($SUPERADMIN === true) {?>
			<li><a href="overview_groups.php">&raquo; Gruppenverwaltung</a></li>
			<li><a href="overview_users.php">&raquo; Benutzerverwaltung</a></li>
			<?php }?>
			<li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
		</ul>
		
		<div id="display-options">
			<label for="showGroup">Gruppe Anzeigen:</label>
			<select id="showGroup" name="showGroup">
				<option value="">Alle</option>
				<?php foreach ($groupList AS $group) {?>
				<option value="<?=$group["group_id"]?>" <?=((int)$group["group_id"] === $group_id) ? 'selected="selected"': ''?>><?=$group["group_name"]?></option>
				<?php }?>
			</select>
			<br />

			<label for="showDisabled">Deaktivierte Anzeigen:</label>
			<select id="showDisabled" name="showDisabled">
				<option value="0">Nein</option>
				<option value="1">Ja</option>
			</select>
		</div>

		<a href="editclient.php">Kunde Hinzufügen</a>

		<ul id="clientlist" class="general-list">
			<?php if (count($clientList) > 0) {?>
			<?php foreach ($clientList AS $client) {?>
			<li id="client_<?=$client["id"]?>"<?=($client["enabled"] == 1)?"":" class=\"disabled\""?>>
				<!-- <span><strong><a href="editclient.php?cid=<?=$client["id"]?>"><?=$client["name"]?></a></strong> (<?=formatStartEndDate($client["first"], $client["last"])?>)</span> -->
				<span style="min-width:700px;"><?=formatStartEndDate($client["first"], $client["last"])?><strong><a href="editclient.php?cid=<?=$client["id"]?>"><?=$client["name"]?></a> </strong></span>
				<a style="text-align:right;" 
					href="<?= ABSURL . 'web/admin/editslots.php?id=' . htmlspecialchars($client['date_id'], ENT_QUOTES, 'UTF-8') ?>">
					<?php 
						if (isset($_GET['sort_by']) && $_GET['sort_by'] == 'upcoming') {
							$totalAppointments = $client["total_appointments"];
							$bookedAppointments = $client["total_booked"];
							$totalBookedTime = $client["total_booked_time"];
							$totalSlotTime = $client["total_slot_time"];
							
							$formattedAppointments = ($totalAppointments < 10) ? sprintf("%02d", $totalAppointments) : $totalAppointments;
							$formattedBookedAppointments = ($bookedAppointments < 10) ? sprintf("%02d", $bookedAppointments) : $bookedAppointments;
							$formattedBookedTime = ($totalBookedTime !== null) ? sprintf("%02d:%02d", floor($totalBookedTime / 60), $totalBookedTime % 60) : "00:00";
							$formattedSlotTime = ($totalSlotTime !== null) ? sprintf("%02d:%02d", floor($totalSlotTime / 60), $totalSlotTime % 60) : "00:00";

							// echo "(" . htmlspecialchars($formattedBookedAppointments, ENT_QUOTES, 'UTF-8') . "/" . htmlspecialchars($formattedAppointments, ENT_QUOTES, 'UTF-8') . ")";
							echo "" . htmlspecialchars($formattedBookedTime, ENT_QUOTES, 'UTF-8') . "";
							// echo "" . htmlspecialchars($formattedSlotTime, ENT_QUOTES, 'UTF-8') . " hours";
						}
					?>
				</a>
				<a style="padding:inherit;" class="export" href="export.php?cid=<?=$client["id"]?>">Export CSV</a>
				<a class="change" href="editclient.php?cid=<?=$client["id"]?>">Ändern</a>
				<a class="delete disable" href="editclient.php?action=disableclient&amp;id=<?=$client["id"]?>">Deaktivieren</a>
				<a class="enable" href="editclient.php?action=enableclient&amp;id=<?=$client["id"]?>">Aktivieren</a>
			</li>
			<?php }?>
			<?php } else {?>
			<li>Noch kein Kunde vorhanden.</li>
			<?php }?>
		</ul>
		
	</div>
	
<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>  