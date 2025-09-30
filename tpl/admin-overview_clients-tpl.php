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
		<ul class="menu" style="position:relative;">
			<li><a href="overview_clients.php">&raquo; Kundenverwaltung</a></li>
			<?php if ($SUPERADMIN === true) {?>
			<li><a href="overview_groups.php">&raquo; Gruppenverwaltung</a></li>
			<li><a href="overview_users.php">&raquo; Benutzerverwaltung</a></li>
			<?php }?>
			<li><a href="<?php echo $url; ?>"><?php echo $label; ?></a></li>
			<!-- Neuer schneller Plus-Button zum Anlegen eines Kunden -->
			<li style="position:absolute; right:0; top:0; list-style:none;">
				<a href="editclient.php" title="Kunde hinzufügen" aria-label="Kunde hinzufügen" 
					style="display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:50%; background:#28a745; color:#fff; font-weight:700; text-decoration:none; box-shadow:0 1px 3px rgba(0,0,0,.2);">+
				</a>
			</li>
		</ul>

		<!-- Tag: stornovorlauf_admin_preview – Mehrfachaktion-Leiste (unter Menü) -->
		<div id="bulk-actions" style="display:flex;align-items:center;gap:8px;margin:6px 0 10px 0;">
			<label for="bulkAction" style="font-weight:600;">Mehrfachaktion</label>
			<select id="bulkAction" style="padding:4px 6px;">
				<option value="">– Bitte wählen –</option>
				<option value="mail">Mail</option>
				<option value="cancel">Canceln</option>
			</select>

			<button type="button" id="applyBulk" style="padding:6px 10px;">Anwenden</button>
		</div>

		<!-- Tag: stornovorlauf_admin_preview – Mail-Popup (ohne Funktion) -->
		<div id="mailModalBackdrop" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="mailModalTitle">
			<div class="modal">
				<header id="mailModalTitle">Mail an ausgewählte Kunden</header>
				<div class="body">
					<div style="margin-bottom:8px;">
						<label for="mailSubject" style="display:block;margin-bottom:4px;">Betreff</label>
						<input type="text" id="mailSubject" value="Kommenden Massagetermin bitte bewerben">
					</div>
					<div>
						<label for="mailMessage" style="display:block;margin-bottom:4px;">Nachricht</label>
						<textarea id="mailMessage" placeholder="">Liebes Team!

Das ist eine kurze Erinnerung an Ihren nächsten Massagetag.
Bis jetzt gibt es noch zu wenig Anmeldungen, um den Massagetag stattfinden zu lassen.

Bitte machen Sie intern noch mal Werbung, wenn die Mindestbuchungszeit von 2,5h nicht erreicht wird, werde ich den Termin leider absagen müssen.

Vielen Dank,
Ihr neckattack-Team</textarea>
					</div>
				</div>
				<div class="footer">
					<button type="button" id="mailCancel">Schließen</button>
					<button type="button" id="mailSend">Senden</button>
				</div>
			</div>
		</div>

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

		<!-- Tag: stornovorlauf_admin_preview – Layout-Fix: Kachel unter die Filter setzen -->
		<div style="clear: both;"></div>

		        <!-- Tag: stornovorlauf_admin_preview – Kacheln mit Pagination (30 pro Seite) in aktueller Sortierung -->
        <?php if (!empty($clientList)) { 
            // Serverseitige Filterung: Deaktivierte ausblenden, wenn showDisabled=0
            $showDisabledParam = isset($_GET['showDisabled']) ? (string)$_GET['showDisabled'] : '0';
            $previewClients = array_values(array_filter($clientList, function($row) use ($showDisabledParam){
                $enabledRaw = isset($row['enabled']) ? (string)$row['enabled'] : '1';
                $enabled = ($enabledRaw === '1');
                if ($showDisabledParam === '1') return true; // alle
                return $enabled; // nur aktive
            }));
        ?>
		<style>
			.admin-card{margin:16px 0 20px;padding:14px 16px;border:1px solid #e5e5e5;border-radius:10px;background:#fff;box-shadow:0 2px 6px rgba(0,0,0,.06);clear:both}
			.admin-card__row{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%}
			.admin-card__left{display:flex;align-items:baseline;gap:12px;flex-wrap:wrap}
			.admin-card__check{margin-right:6px;}
			.admin-card__date{color:#0aa;font-weight:700}
			.admin-card__name{font-size:18px;font-weight:700;margin:0}
			.admin-card__actions a{display:inline-block;margin-left:6px;padding:6px 10px;border-radius:6px;text-decoration:none;font-size:13px}
			.admin-card__actions{white-space:nowrap;margin-left:auto}
			.admin-time{color:#000;font-weight:700;margin-right:10px}
			.admin-time--low{background:#dc3545;color:#fff;padding:3px 6px;border-radius:4px}
			/* Disabled Look */
			.admin-card--disabled{background:#f7f7f7}
			.admin-card--disabled .admin-card__name a{color:#666; text-decoration: line-through;}
			.admin-card--disabled .admin-card__date{color:#888}
			.primary{background:#007bff;color:#fff}
			.secondary{background:#f0f0f0;color:#333}
			.danger{background:#dc3545;color:#fff}
			/* Burger Dropdown */
			.card-menu{position:relative;display:inline-block}
			.card-menu button{background:#f0f0f0;border:1px solid #ddd;border-radius:6px;padding:6px 10px;cursor:pointer}
			.card-menu__menu{position:absolute;right:0;top:110%;min-width:160px;background:#fff;border:1px solid #e5e5e5;border-radius:8px;box-shadow:0 6px 16px rgba(0,0,0,.12);display:none;z-index:10}
			.card-menu__menu a{display:block;padding:8px 10px;text-decoration:none;color:#333}
			.card-menu__menu a:hover{background:#f7f7f7}
			/* Modal */
			.modal-backdrop{position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.4);display:none;align-items:center;justify-content:center;z-index:9999}
			.modal{background:#fff;border-radius:10px;max-width:520px;width:92%;box-shadow:0 10px 30px rgba(0,0,0,.2)}
			.modal header{padding:12px 16px;border-bottom:1px solid #eee;font-weight:700}
			.modal .body{padding:12px 16px}
			.modal .footer{padding:12px 16px;border-top:1px solid #eee;display:flex;gap:8px;justify-content:flex-end}
			.modal input[type="text"], .modal textarea{width:100%;padding:8px;border:1px solid #ddd;border-radius:6px}
			.modal textarea{min-height:120px}
		</style>
		        <?php $__k=0; foreach ($previewClients as $c) { $range = formatStartEndDate($c['first'],$c['last']); $enabledRaw = isset($c['enabled']) ? (string)$c['enabled'] : '1'; $enabledVal = ($enabledRaw === '1') ? 1 : 0; $isDisabled = ($enabledVal === 0); $__k++; ?>
        <div class="admin-card<?= $isDisabled ? ' admin-card--disabled' : '' ?>" data-enabled="<?= $enabledVal ?>" data-idx="<?= $__k ?>">
			<div class="admin-card__row">
				<div class="admin-card__left">
					<input type="checkbox" class="admin-card__check" name="select_client[]" value="<?=(int)$c['id']?>" aria-label="Select <?=htmlspecialchars($c['name'],ENT_QUOTES,'UTF-8')?>">
					<div class="admin-card__date"><?=htmlspecialchars($range,ENT_QUOTES,'UTF-8')?></div>
					<h3 class="admin-card__name"><a href="editclient.php?cid=<?=(int)$c['id']?>"><?=htmlspecialchars($c['name'],ENT_QUOTES,'UTF-8')?></a></h3>
				</div>
				<div class="admin-card__actions">
					<?php 
					// Tag: stornovorlauf_admin_preview – gebuchte Zeit wie in Liste anzeigen (nur bei sort_by=upcoming)
					if (isset($_GET['sort_by']) && $_GET['sort_by'] === 'upcoming') {
					    $totalBookedTime = $c['total_booked_time'] ?? null; // Minuten
					    if ($totalBookedTime !== null) {
					        $timeFormatted = sprintf('%02d:%02d', floor($totalBookedTime/60), $totalBookedTime%60);
					        $isLow = ((int)$totalBookedTime) < 120; // < 02:00 Stunden
					        $cls = $isLow ? 'admin-time admin-time--low' : 'admin-time';
					        echo '<span class="'.$cls.'">'.htmlspecialchars($timeFormatted, ENT_QUOTES, 'UTF-8').'</span>';
					    }
					}
					?>
					<a class="primary" href="<?=ABSURL.'web/admin/editslots.php?id='.(int)$c['date_id']?>">Slots bearbeiten</a>
					<div class="card-menu">
						<button type="button" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display==='block' ? 'none' : 'block'">⋯</button>
						<div class="card-menu__menu" onclick="this.style.display='none'">
							<a href="export.php?cid=<?=(int)$c['id']?>">Export CSV</a>
							<?php if((int)$c['enabled']===1){?>
								<a href="editclient.php?action=disableclient&id=<?=(int)$c['id']?>">Deaktivieren</a>
							<?php } else {?>
								<a href="editclient.php?action=enableclient&id=<?=(int)$c['id']?>">Aktivieren</a>
							<?php }?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<?php }?>

        <!-- Tag: stornovorlauf_admin_preview – Pagination-UI -->
        <!-- Pagination-UI vorübergehend deaktiviert: Anzeige aller Kacheln ohne Paging -->
        <div id="admin-card-pager" style="display:none;align-items:center;gap:8px;margin:6px 0 16px 0;">
            <strong style="margin-right:6px;">Pagination</strong>
            <button type="button" id="pager-prev" style="padding:4px 8px;">«</button>
            <span id="pager-info" style="min-width:120px;display:inline-block;">–</span>
            <button type="button" id="pager-next" style="padding:4px 8px;">»</button>
        </div>

        <!-- Tag: stornovorlauf_admin_preview – JS-Filter/Pagination + Mehrfachaktion/Modal -->
        <script>
        (function(){
            try {
                // Pagination deaktiviert: pageSize sehr groß setzen
                var state = { page: 1, pageSize: 1000000 };
                function q(id){ return document.getElementById(id); }
                function getFilters(){
                    var sel = q('showDisabled');
                    if (!sel) return { showDisabled: '0' };
                    var v = (sel.value+'' );
                    if (v !== '0' && v !== '1') { v = (v.toLowerCase().indexOf('ja')===0 ? '1' : '0'); }
                    return { showDisabled: v };
                }
                function applyCardFilters(){
                    var filters = getFilters();
                    var cards = Array.prototype.slice.call(document.querySelectorAll('.admin-card'));
                    cards.sort(function(a,b){return (parseInt(a.getAttribute('data-idx'))||0)-(parseInt(b.getAttribute('data-idx'))||0)});
                    var filtered = cards.filter(function(card){
                        var enabledAttr = card.getAttribute('data-enabled');
                        var enabled = (enabledAttr !== null) ? (enabledAttr === '1') : !card.classList.contains('admin-card--disabled');
                        return (filters.showDisabled === '1') ? true : enabled;
                    });
                    var total = filtered.length;
                    var totalPages = Math.max(1, Math.ceil(total / state.pageSize));
                    if (state.page > totalPages) state.page = totalPages;
                    var start = (state.page - 1) * state.pageSize;
                    var end = start + state.pageSize;
                    var visibleSet = new Set(filtered.slice(start, end));
                    cards.forEach(function(card){ card.style.display = visibleSet.has(card) ? '' : 'none'; });
                    var info = q('pager-info');
                    if (info) info.textContent = 'Seite ' + state.page + ' / ' + totalPages + ' (' + total + ' Einträge)';
                    var prev = q('pager-prev'); var next = q('pager-next');
                    if (prev) prev.disabled = (state.page <= 1);
                    if (next) next.disabled = (state.page >= totalPages);
                }
                document.addEventListener('DOMContentLoaded', applyCardFilters);
                window.addEventListener('load', applyCardFilters);
                var sel = q('showDisabled');
                if (sel) sel.addEventListener('change', function(){ state.page = 1; applyCardFilters(); });
                var selGroup = q('showGroup');
                if (selGroup) selGroup.addEventListener('change', function(){ state.page = 1; applyCardFilters(); });
                var prev = q('pager-prev'); var next = q('pager-next');
                if (prev) prev.addEventListener('click', function(){ if (state.page>1){ state.page--; applyCardFilters(); }});
                if (next) next.addEventListener('click', function(){ state.page++; applyCardFilters(); });

                function getSelectedClientIds(){
                    var ids=[]; document.querySelectorAll('.admin-card input.admin-card__check:checked').forEach(function(cb){ ids.push(cb.value); });
                    return ids;
                }
                var applyBtn = q('applyBulk');
                if (applyBtn) applyBtn.addEventListener('click', function(){
                    try {
                        var actionSel = q('bulkAction');
                        var action = actionSel ? actionSel.value : '';
                        var ids = getSelectedClientIds();
                        if (!action) { alert('Bitte eine Aktion wählen.'); return; }
                        if (ids.length===0) { alert('Bitte mindestens einen Eintrag markieren.'); return; }
                        if (action==='mail') {
                            var mb = q('mailModalBackdrop'); if (mb) mb.style.display='flex';
                        } else if (action==='cancel') {
                            alert('Canceln (Mehrfach) – noch ohne Funktion. Ausgewählt: '+ids.join(', '));
                        }
                    } catch(e){ alert('Fehler (Bulk): '+e.message); }
                });

                var btnCancel = q('mailCancel');
                if (btnCancel) btnCancel.addEventListener('click', function(){ var mb = q('mailModalBackdrop'); if (mb) mb.style.display='none'; });
                var btnSend = q('mailSend');
                if (btnSend) btnSend.addEventListener('click', function(){
                    try {
                        var subject = (q('mailSubject') && q('mailSubject').value) || 'Kommenden Massagetermin bitte bewerben';
                        var message = (q('mailMessage') && q('mailMessage').value) || '';
                        var ids = getSelectedClientIds();
                        if (ids.length === 0) { alert('Bitte mindestens einen Eintrag markieren.'); return; }
                        var formData = new URLSearchParams();
                        formData.set('action','sendbulkmail');
                        ids.forEach(function(id){ formData.append('client_ids[]', id); });
                        formData.set('subject', subject);
                        formData.set('message', message);
                        fetch('<?= ABSURL.'web/admin/ajax/editclient.php' ?>', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                            body: formData.toString()
                        }).then(async function(r){
                            if (!r.ok) {
                                var t = await r.text();
                                throw new Error('HTTP '+r.status+': '+t.substring(0,200));
                            }
                            var ct = r.headers.get('content-type') || '';
                            if (ct.indexOf('application/json') === -1) {
                                var t = await r.text();
                                throw new Error('Unerwartetes Format: '+t.substring(0,200));
                            }
                            return r.json();
                        }).then(function(res){
                            if (res && (res.success === 1 || res.ok === true)) {
                                var sent = res.sent || 0;
                                var skippedLen = res.skipped ? res.skipped.length : 0;
                                alert('Gesendet: '+sent+' | Übersprungen: '+skippedLen);
                            } else {
                                alert('Fehler beim Senden: '+(res && (res.error || res.message) ? (res.error || res.message) : 'Unbekannt'));
                            }
                        }).catch(function(err){
                            alert('Netzwerk-/Serverfehler: '+err);
                        }).finally(function(){
                            var mb = q('mailModalBackdrop'); if (mb) mb.style.display='none';
                        });
                    } catch(e){ alert('Fehler (Mail senden): '+e.message); }
                });

                // erste Anwendung forcieren
                applyCardFilters();
            } catch(err){
                alert('Initialisierungsfehler: '+err.message);
            }
        })();
        </script>

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