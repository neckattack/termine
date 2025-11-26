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
		<form id="generateSlots" action="<?= ABSURL . 'web/admin/editslots.php?id=' . $_GET['id'] ?>" method="post" style="position:relative;">
            <div id="save-banner" style="display:none; position:absolute; top:0; right:0; padding:6px 10px; background:#f7f7d7; border:1px solid #ddd; color:#333; border-radius:4px; box-shadow:0 1px 2px rgba(0,0,0,0.06);">
                <strong>Status:</strong> <span id="save-banner-text"></span>
            </div>
			<div>
				<?php foreach ($durations AS $key => $duration) {?>
				<div class="block">Terminblock: <input type="text" name="starttimes[]" size="5" value="<?=$startTimes[$key]?>" /> bis <input type="text" name="endtimes[]" size="5" value="<?=$endTimes[$key]?>" /> Uhr. Dauer: jeweils <input type="text" name="durations[]" size="3" value="<?=$duration?>" /> Minuten.</div>
				<?php }?>
				<div id="addBlock"><a class="addblock" href="editslots.php?action=addblock&amp;id=<?=$R["id"]?>">Weiterer Terminblock</a></div>
			</div>
			<div>
				<input type="submit" id="generateSlotsSubmit" value="Erstellen" />
				<input type="hidden" name="id" id="id" value="<?=$R["id"]?>" />
				<input type="hidden" name="date_id" id="date_id" value="<?= isset($slots[0]["date_id"]) ? (int)$slots[0]["date_id"] : 0 ?>" />
				<input type="hidden" name="generate" id="generate" value="true" />
			</div>
		</form>

		        <div style="display:flex; align-items:center; gap:10px; margin:6px 0;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer; user-select:none;">
                    <input type="checkbox" id="select-all-slots" />
                    <span>Alle belegten Slots auswählen</span>
                </label>
            </div>
            <div class="slots">
            <div style="position:relative;">
                <button type="button" id="btn-send-invoices" style="position:absolute; right:160px; top:-34px; background:#2b7; color:#fff; border:0; padding:6px 10px; border-radius:4px; cursor:pointer;">Rechnungen E‑Mailen</button>
                
                <!-- Neues E-Mail Dropdown -->
                <div style="position:absolute; right:0; top:-34px; display:inline-block;">
                    <select id="email-action-dropdown" style="padding:6px 10px; background:#2b7; color:#fff; border:0; border-radius:4px; cursor:pointer; font-weight:600;">
                        <option value="">E-Mail ▼</option>
                        <option value="invoices">Rechnungen E-Mailen</option>
                        <option value="custom">E-Mail individuell</option>
                    </select>
                </div>
            </div>
            <h3><?= isset($slots[0]["date"]) ? $slots[0]["date"] : "" ?></h3>
			<ul id="slots">
				<?php if (isset($slots[0]["time_start"][0])) { 
					require_once ROOT.'/controller/admin-overview_services-controller.php';
					$services = getAllGebuehServices();
					$defaultDiagnosis = isset($slots[0]['default_diagnosis']) ? (string)$slots[0]['default_diagnosis'] : '';
                    $defaultServiceIds = array();
                    if (isset($slots[0]['default_service_ids']) && trim($slots[0]['default_service_ids'])!=='') {
                        $defaultServiceIds = array_values(array_filter(array_map('intval', explode(',', $slots[0]['default_service_ids'])), function($v){ return $v>0; }));
                    }
                    foreach ($slots AS $slot) {
                        if (!isset($slot["time_start"])) { continue; }
                        // ausgewählte Services pro Reservierung: reservation_service_prices > fallback defaultServiceIds
                        $selectedIds = isset($reservation_service_prices[$slot['res_id']]) ? array_keys($reservation_service_prices[$slot['res_id']]) : $defaultServiceIds;
                    ?>
				<li style="display:flex; align-items:center; gap:4px; flex-wrap:wrap; margin:8px 0; width:100%;">
                    <span class="sel" style="flex:0 0 auto; min-width:18px; text-align:center;">
                        <?php if (isset($slot['res_id']) && (int)$slot['res_id']>0) { ?>
                            <input type="checkbox" class="slot-select" data-resid="<?=$slot['res_id']?>" aria-label="Slot auswählen" />
                        <?php } else { ?>
                            &nbsp;
                        <?php } ?>
                    </span>
					<span class="time" style="min-width:100px; color:#333; white-space:nowrap;">
						<?=$slot["time_start"]?> - <?=$slot["time_end"]?>
					</span>
					                <span class="name" style="flex:0 0 auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?php if (isset($slot["res_name"][1]) && isset($slot['res_id']) && (int)$slot['res_id']>0) {?><a href="<?=ABSURL?>web/admin/editslots.php?action=upsert_patient&amp;res_id=<?=$slot['res_id']?>" target="_blank" title="Patientenprofil öffnen oder anlegen (per E-Mail)"><?=$slot["res_name"]?></a><?php } else {?>- frei -<?php }?>
					</span>
					                <span class="therapist" style="flex:0 0 auto; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#333; margin-left:6px;">
						<?php
							$mName = '';
							if (isset($slot['masseur_first_name']) || isset($slot['masseur_last_name'])) {
								$fn = isset($slot['masseur_first_name']) ? trim($slot['masseur_first_name']) : '';
								$ln = isset($slot['masseur_last_name']) ? trim($slot['masseur_last_name']) : '';
								$mName = trim($fn.' '.$ln);
							}
							if (!empty($slot['masseur_id']) && $slot['masseur_id']>0 && $mName!=='') {
                                echo 'Therapeut: <a href="'.ABSURL.'web/admin/edituser.php?id='.(int)$slot['masseur_id'].'" target="_blank">'.htmlspecialchars($mName).'</a>';
                            } else {
                                echo 'Therapeut: —';
                            }
						?>
					</span>
					                    <!-- Aktionen: immer sichtbar halten, vor optionalem Formular -->
                    <div style="margin-left:auto; display:flex; gap:8px; align-items:center; flex:0 0 auto; position:relative; white-space:nowrap;">
                        <?php if (isset($slot["res_id"]) && (int)$slot['res_id']>0) {?><a href="invoice.php?res_id=<?=$slot["res_id"]?>" class="pdf" target="_blank">PDF</a><?php }?>
                        <?php if (isset($slot["res_id"]) && (int)$slot['res_id']>0) { ?>
                            <a title="Klicken Sie auf den Link, um die URL in die Zwischenablage zu kopieren" href="javascript:void(0);" data-content="<?= ABSURL ?>web/bookings.php?e=<?=md5(strtolower($slot["email"]))?>" class="copy-url">URL</a>
                        <?php } ?>
                        <button type="button" class="slot-menu-btn" data-menu="menu-<?=$slot['time_id']?>" aria-label="Aktionen" style="background:none;border:0;font-size:18px;line-height:1;padding:2px 6px;cursor:pointer">☰</button>
                        <div id="menu-<?=$slot['time_id']?>" class="slot-menu" style="display:none; position:absolute; right:0; top:22px; background:#fff; border:1px solid #ccc; box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:10; min-width:200px; font-family:inherit; font-size:13px; line-height:1.3;">
                            <div style="padding:6px 10px;">
                                <?php if (isset($slot['res_id']) && (int)$slot['res_id']>0 && (isset($patient_billing_required) && (int)$patient_billing_required===1)) { ?>
                                <a href="#" class="toggle-calc" data-target="calc-<?=$slot['res_id']?>" style="display:block; padding:4px 0; text-decoration:underline; color:#111;">Berechnung anzeigen</a>
                                <hr style="border:0;border-top:1px solid #eee;margin:6px 0;" />
                                <?php } ?>
                                <?php if (isset($slot["res_id"]) && (int)$slot['res_id']>0) {?><a href="editslots.php?action=deletereservation&amp;id=<?=$slot["res_id"]?>" class="delete orange" style="display:block; padding:4px 0; text-decoration:underline;">Reservierung löschen</a><?php }?>
                                <a href="editslots.php?action=deletetimeentry&amp;id=<?=$slot["time_id"]?>" class="delete" style="display:block; padding:4px 0; text-decoration:underline;">Termin entfernen</a>
                            </div>
                        </div>
                    </div>

                    <?php if (isset($slot['res_id']) && $slot['res_id']>0 && (isset($patient_billing_required) && (int)$patient_billing_required===1)) { ?>
                    <div id="calc-<?=$slot['res_id']?>" class="calc-box inline-form" style="display:none; align-items:flex-start; gap:10px; margin:8px 0 4px 0; flex-basis:100%; width:100%; min-width:0;">
                        <div style="flex:0 0 260px;">
                            <div style="font-weight:bold; margin:2px 0 4px;">Diagnose (individuell)</div>
                            <?php $diagVal = isset($reservation_diagnoses[$slot['res_id']]) ? (string)$reservation_diagnoses[$slot['res_id']] : $defaultDiagnosis; ?>
                            <input type="text" class="autosave-diagnosis" data-resid="<?=$slot['res_id']?>" name="res_diagnosis[<?=$slot['res_id']?>]" value="<?=htmlspecialchars($diagVal)?>" placeholder="Diagnose" style="width:100%; padding:2px 6px;" />
                            <select name="res_services[<?=$slot['res_id']?>][]" data-resid="<?=$slot['res_id']?>" multiple="multiple" class="multiselect res-services" style="margin-top:6px; width:100%; min-width:260px;">
                                <?php if (is_array($services)) { foreach ($services as $s) { $sel = in_array((int)$s['id'], $selectedIds, true) ? 'selected="selected"' : ''; ?>
                                    <option value="<?=$s['id']?>" <?=$sel?>><?=$s['code']?> – <?=$s['title']?></option>
                                <?php } } ?>
                            </select>
                        </div>
                        <?php 
                            // Detail-Tabelle mit editierbaren Preisen
                            $svcIndex = array();
                            if (is_array($services)) { foreach ($services as $s) { $svcIndex[(int)$s['id']] = $s; } }
                            $rows = array(); $sum = 0.0;
                            foreach ($selectedIds as $sid) {
                                $sid = (int)$sid; if (!isset($svcIndex[$sid])) continue; $s = $svcIndex[$sid];
                                // Preis-Priorität: Reservierung > Client > fee_mid
                                $cp = null;
                                if (isset($reservation_service_prices[$slot['res_id']]) && isset($reservation_service_prices[$slot['res_id']][$sid])) {
                                    $cp = (float)$reservation_service_prices[$slot['res_id']][$sid];
                                } elseif (isset($client_service_prices[$sid])) {
                                    $cp = (float)$client_service_prices[$sid];
                                } else {
                                    $cp = isset($s['fee_mid']) ? (float)$s['fee_mid'] : 0.0;
                                }
                                $rows[] = array('id'=>$sid,'code'=>$s['code'],'title'=>$s['title'],'price'=>$cp);
                                $sum += $cp;
                            }
                        ?>
                        <div style="flex:1 1 420px; min-width:360px;">
                            <table style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left; border-bottom:1px solid #ccc; padding:3px 6px;">Service</th>
                                        <th style="text-align:right; border-bottom:1px solid #ccc; padding:3px 6px;">Betrag (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $r) { ?>
                                    <tr>
                                        <td style="padding:3px 6px; border-bottom:1px solid #eee; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            <?=htmlspecialchars($r['code'].' – '.$r['title'])?>
                                        </td>
                                        <td style="padding:3px 6px; text-align:right; border-bottom:1px solid #eee;">
                                            <input type="number" step="0.01" min="0" name="res_prices[<?=$slot['res_id']?>][<?=$r['id']?>]" value="<?=number_format($r['price'],2,'.','')?>" class="svc-price autosave" data-resid="<?=$slot['res_id']?>" data-sid="<?=$r['id']?>" style="width:90px; text-align:right;" />
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td style="padding:4px 6px; text-align:right;">Summe</td>
                                        <td class="svc-sum" style="padding:4px 6px; text-align:right;"><strong><?=number_format($sum,2,',','.')?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <?php } ?>
				</li>
				<?php } // endforeach slots ?>
				<?php } else {?>
				<li>Noch kein Eintrag vorhanden</li>
				<?php }?>
			</ul>
			<!-- Autosave aktiv: kein globaler Speichern-Button nötig -->
		</div>

		<p id="error" <?=(isset($success) && $success < 1) ? ' style="display: block;"' : "" ?>>Es ist ein Fehler aufgetreten! Bitte überprüfen Sie Ihre Eingabe.</p>
		<p id="error-invalid" class="error check-for-js">Bitte überprüfen Sie Ihre Eingabe.</p>
		<p id="error-not-available" class="error check-for-js">Einige der Termine überschneiden sich. Bitte überprüfen Sie Ihre Eingabe.</p>
		<p id="success">Der Kunde wurde erfolgreich eingetragen/geändert.</p>

<!-- Modal für individuelle E-Mail -->
<div id="custom-email-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:30px; border-radius:8px; max-width:600px; width:90%; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0; margin-bottom:20px;">E-Mail individuell versenden</h3>
        <div style="margin-bottom:15px;">
            <label for="custom-email-subject" style="display:block; margin-bottom:5px; font-weight:600;">Betreff:</label>
            <input type="text" id="custom-email-subject" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; font-size:14px;" placeholder="Betreff eingeben..." />
        </div>
        <div style="margin-bottom:20px;">
            <label for="custom-email-body" style="display:block; margin-bottom:5px; font-weight:600;">Nachricht:</label>
            <textarea id="custom-email-body" rows="10" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; font-size:14px; resize:vertical;" placeholder="Nachricht eingeben..."></textarea>
        </div>
        <div style="display:flex; gap:10px; justify-content:flex-end;">
            <button type="button" id="custom-email-cancel" style="padding:8px 20px; background:#ccc; color:#333; border:0; border-radius:4px; cursor:pointer; font-weight:600;">Abbrechen</button>
            <button type="button" id="custom-email-send" style="padding:8px 20px; background:#2b7; color:#fff; border:0; border-radius:4px; cursor:pointer; font-weight:600;">Senden</button>
        </div>
    </div>
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
    // Live-Summe je Reservierung neu berechnen
    (function(){
        // Global banner helpers
        var banner = document.getElementById('save-banner');
        var bannerText = document.getElementById('save-banner-text');
        function showBanner(text, ok){
            if (!banner || !bannerText) return;
            banner.style.display = 'block';
            bannerText.textContent = text;
            banner.style.background = ok===true ? '#e6f7e6' : (ok===false ? '#fdeaea' : '#f7f7d7');
            banner.style.borderColor = ok===true ? '#9ad19a' : (ok===false ? '#e3a2a2' : '#ddd');
        }
        // make banner helper available globally for other modules on the page
        try { window.__sbShowBanner = showBanner; } catch(_) {}
        function fmt(n){ return (n||0).toFixed(2).replace('.',','); }
        function recomputeBoxSum(box){
            var sum = 0.0;
            box.querySelectorAll('.svc-price').forEach(function(inp){
                var v = parseFloat((inp.value||'').toString().replace(',', '.'));
                if(!isNaN(v) && v>=0) sum += v;
            });
            var td = box.querySelector('.svc-sum');
            if(td) td.innerHTML = '<strong>'+fmt(sum)+'</strong>';
        }
        document.querySelectorAll('.calc-box').forEach(function(box){
            box.addEventListener('input', function(ev){ if(ev.target && ev.target.classList.contains('svc-price')) recomputeBoxSum(box); });
            box.addEventListener('change', function(ev){ if(ev.target && ev.target.classList.contains('svc-price')) recomputeBoxSum(box); });
        });
        // Toggle show/hide calc sections (robust)
        document.addEventListener('click', function(e){
            var a = e.target.closest('.toggle-calc');
            if (!a) return;
            e.preventDefault();
            var id = a.getAttribute('data-target');
            var el = document.getElementById(id);
            if (!el) return;
            var cs = window.getComputedStyle(el);
            var vis = (cs.display !== 'none') && (el.offsetParent !== null);
            try { console.debug('[toggle-calc]', id, 'visible?', vis); } catch(_) {}
            if (vis) {
                el.style.display = 'none';
            } else {
                el.style.display = 'block';
            }
            a.textContent = vis ? 'Berechnung anzeigen' : 'Berechnung verbergen';
        });
    // IIFE remains open; helper functions below are inside
    function post(url, data){
        return fetch(url, { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, credentials: 'same-origin', body: new URLSearchParams(data).toString() });
    }
    var url = '<?= ABSURL . 'web/admin/editslots.php?id=' . (isset($_GET['id']) ? (int)$_GET['id'] : 0) ?>';
    document.querySelectorAll('.autosave').forEach(function(inp){
        function save(){
            var rid = inp.getAttribute('data-resid');
            var sid = inp.getAttribute('data-sid');
            var amount = inp.value;
            showBanner('Speichere…', null);
            try { console.debug('[autosave] POST', {rid:rid, sid:sid, amount:amount}); } catch(e) {}
            post(url, { ajax_res_price: '1', rid: rid, sid: sid, amount: amount })
                .then(function(r){
                    if(!r.ok){ throw new Error('HTTP '+r.status); }
                    var ct = r.headers.get('content-type') || '';
                    if (ct.indexOf('application/json') !== -1) { return r.json(); }
                    return r.text().then(function(){ return { ok:false }; });
                })
                .then(function(j){ 
                    try { console.debug('[autosave] RESP', j); } catch(e) {}
                    showBanner((j && j.ok) ? 'Gespeichert' : 'Fehler beim Speichern', (j && j.ok));
                })
                .catch(function(err){ 
                    try { console.error('[autosave] ERR', err); } catch(e) {}
                    showBanner('Fehler beim Speichern', false);
                });
        }
        // Debounced autosave on input as well
        var t = null;
        function schedule(){
            showBanner('…', null);
            if (t) clearTimeout(t);
            t = setTimeout(save, 600);
        }
        inp.addEventListener('input', schedule);
        inp.addEventListener('change', save);
        inp.addEventListener('blur', save);
    });

    // Autosave for services multi-select
    document.querySelectorAll('.res-services').forEach(function(sel){
        var saveTimer=null;
        function saveServices(){
            var rid = sel.getAttribute('data-resid');
            var sids;
            try {
                if (window.jQuery && typeof jQuery.fn === 'object') {
                    var v = jQuery(sel).val();
                    sids = Array.isArray(v) ? v : [];
                }
            } catch(_) {}
            if (!Array.isArray(sids)) {
                sids = Array.prototype.slice.call(sel.options).filter(function(o){ return o.selected; }).map(function(o){ return o.value; });
            }
            try { console.debug('[services-autosave] rid', rid, 'sids', sids); } catch(_) {}
            showBanner('Speichere gewählte Services…', null);
            var params = new URLSearchParams();
            params.append('ajax_res_services','1');
            params.append('rid', rid);
            sids.forEach(function(id){ params.append('sids[]', id); });
            fetch(url, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, credentials:'same-origin', body: params.toString() })
                .then(function(r){ if(!r.ok){ throw new Error('HTTP '+r.status); } var ct=r.headers.get('content-type')||''; if(ct.indexOf('application/json')!==-1){return r.json();} return r.text().then(function(){return {ok:false};}); })
                .then(function(j){ try{ console.debug('[services-autosave] resp', j);}catch(_){} showBanner((j && j.ok)?'Services gespeichert':'Fehler beim Speichern der Services', (j && j.ok)); })
                .catch(function(err){ try{ console.error('[services-autosave] err', err);}catch(_){} showBanner('Fehler beim Speichern der Services', false); });
        }
        function schedule(){ if (saveTimer) clearTimeout(saveTimer); saveTimer = setTimeout(saveServices, 300); }
        sel.addEventListener('change', schedule);
        sel.addEventListener('input', schedule);
        sel.addEventListener('keyup', function(e){ if(e.key==='Enter' || e.key===' ') schedule(); });
        sel.addEventListener('blur', schedule);
        // jQuery UI Multiselect plugin events, falls aktiv
        try {
            if (window.jQuery && typeof jQuery.fn === 'object') {
                var $sel = jQuery(sel);
                $sel.on('multiselectclick', schedule);
                $sel.on('multiselectcheckall', schedule);
                $sel.on('multiselectuncheckall', schedule);
            }
        } catch(_) {}
    });
        // Hamburger-Menüs toggeln und außerhalb schließen
        document.addEventListener('click', function(e){
            var btn = e.target.closest('.slot-menu-btn');
            // Toggle clicked menu
            if (btn){
                var id = btn.getAttribute('data-menu');
                var menu = document.getElementById(id);
                if (menu){
                    var vis = menu.style.display !== 'none' && menu.style.display !== '';
                    // close others
                    document.querySelectorAll('.slot-menu').forEach(function(m){ m.style.display='none'; });
                    menu.style.display = vis ? 'none' : 'block';
                }
                return;
            }
            // Click outside closes all
            if (!e.target.closest('.slot-menu')){
                document.querySelectorAll('.slot-menu').forEach(function(m){ m.style.display='none'; });
            }
        });
        document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ document.querySelectorAll('.slot-menu').forEach(function(m){ m.style.display='none'; }); }});
    })();
    // Autosave diagnosis
    (function(){
        var url = '<?= ABSURL . 'web/admin/editslots.php?id=' . (isset($_GET['id']) ? (int)$_GET['id'] : 0) ?>';
        function post(url, data){
            return fetch(url, { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, credentials: 'same-origin', body: new URLSearchParams(data).toString() });
        }
        document.querySelectorAll('.autosave-diagnosis').forEach(function(inp){
            var t=null;
            function save(){
                var rid = inp.getAttribute('data-resid');
                var val = inp.value || '';
                try { if (window.console) console.debug('[diagnosis-autosave]', rid, val); } catch(_){ }
                if (typeof window.__sbShowBanner === 'function') { window.__sbShowBanner('Speichere Diagnose…', null); }
                post(url, { ajax_res_diagnosis:'1', rid: rid, diagnosis: val })
                    .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
                    .then(function(j){ try{ console.debug('[diagnosis-autosave] resp', j);}catch(_){} if (typeof window.__sbShowBanner === 'function') { window.__sbShowBanner((j && j.ok)?'Gespeichert':'Fehler beim Speichern', (j && j.ok)); } })
                    .catch(function(e){ try{ console.error('[diagnosis-autosave] err', e);}catch(_){} if (typeof window.__sbShowBanner === 'function') { window.__sbShowBanner('Fehler beim Speichern', false); } });
            }
            function schedule(){ if(t) clearTimeout(t); t=setTimeout(save, 400); }
            inp.addEventListener('change', save);
            inp.addEventListener('blur', save);
            inp.addEventListener('input', schedule);
        });
    })();
    // Master-Checkbox: alle belegten Slots auswählen/abwählen
    (function(){
        var master = document.getElementById('select-all-slots');
        function getChecks(){ return Array.prototype.slice.call(document.querySelectorAll('.slot-select')); }
        function refreshMaster(){
            if (!master) return;
            var checks = getChecks().filter(function(c){ return !c.disabled; });
            var total = checks.length;
            var checked = checks.filter(function(c){ return c.checked; }).length;
            master.indeterminate = (checked>0 && checked<total);
            master.checked = (total>0 && checked===total);
        }
        if (master){
            master.addEventListener('change', function(){
                var on = !!master.checked;
                getChecks().forEach(function(c){ if(!c.disabled){ c.checked = on; }});
                refreshMaster();
            });
        }
        document.addEventListener('change', function(e){
            var cb = e.target && e.target.classList && e.target.classList.contains('slot-select');
            if (cb) refreshMaster();
        });
        // initial state
        refreshMaster();
    })();

    // Rechnungen E‑Mailen Button
    (function(){
        var btn = document.getElementById('btn-send-invoices');
        if (!btn) return;
        function getSelectedResIds(){
            var ids=[]; document.querySelectorAll('.slot-select:checked').forEach(function(cb){ var id=cb.getAttribute('data-resid'); if(id) ids.push(id); });
            return ids;
        }
        btn.addEventListener('click', function(){
            var ids = getSelectedResIds();
            if (ids.length===0){ if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Keine Slots ausgewählt', false); } return; }
            if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Sende Rechnungen…', null); }
            var params = new URLSearchParams();
            ids.forEach(function(id){ params.append('reservation_ids[]', id); });
            fetch('<?= ABSURL ?>web/admin/ajax/send_invoices.php', { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, credentials:'same-origin', body: params.toString() })
                .then(function(r){ if(!r.ok){ return r.text().then(function(t){ throw new Error('HTTP '+r.status+' '+(t||'')); }); } return r.json(); })
                .then(function(j){
                    var ok = j && j.ok===true;
                    var details = '';
                    if (j && j.results && j.results.length){
                        var fails = j.results.filter(function(r){ return r && r.ok===false; });
                        if (fails.length){ details = ' | 1. Fehler: '+(fails[0].reason||'unbekannt'); }
                    }
                    var msg = ok ? ('Rechnungen gesendet: '+(j.sent||0)+(j.failed&&j.failed.length?(', Fehler: '+j.failed.length):''))+details : 'Fehler beim Senden';
                    if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner(msg, ok); }
                })
                .catch(function(e){ if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Fehler beim Senden: '+(e&&e.message?e.message:''), false); } });
        });
    })();

    // Neues E-Mail Dropdown & Individuelles E-Mail Modal
    (function(){
        var dropdown = document.getElementById('email-action-dropdown');
        var modal = document.getElementById('custom-email-modal');
        var subjectInput = document.getElementById('custom-email-subject');
        var bodyInput = document.getElementById('custom-email-body');
        var cancelBtn = document.getElementById('custom-email-cancel');
        var sendBtn = document.getElementById('custom-email-send');
        
        if (!dropdown || !modal) return;
        
        function getSelectedResIds(){
            var ids=[]; 
            document.querySelectorAll('.slot-select:checked').forEach(function(cb){ 
                var id=cb.getAttribute('data-resid'); 
                if(id) ids.push(id); 
            });
            return ids;
        }
        
        function openModal(){
            modal.style.display = 'flex';
            subjectInput.value = '';
            bodyInput.value = '';
        }
        
        function closeModal(){
            modal.style.display = 'none';
        }
        
        // Dropdown Change Handler
        dropdown.addEventListener('change', function(){
            var action = this.value;
            if (!action) return;
            
            var ids = getSelectedResIds();
            if (ids.length === 0){
                if (typeof window.__sbShowBanner==='function'){ 
                    window.__sbShowBanner('Keine Slots ausgewählt', false); 
                }
                this.value = ''; // Reset dropdown
                return;
            }
            
            if (action === 'invoices'){
                // Rechnungen E-Mailen (bestehende Funktion)
                if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Sende Rechnungen…', null); }
                var params = new URLSearchParams();
                ids.forEach(function(id){ params.append('reservation_ids[]', id); });
                fetch('<?= ABSURL ?>web/admin/ajax/send_invoices.php', { 
                    method:'POST', 
                    headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, 
                    credentials:'same-origin', 
                    body: params.toString() 
                })
                .then(function(r){ if(!r.ok){ return r.text().then(function(t){ throw new Error('HTTP '+r.status+' '+(t||'')); }); } return r.json(); })
                .then(function(j){
                    var ok = j && j.ok===true;
                    var details = '';
                    if (j && j.results && j.results.length){
                        var fails = j.results.filter(function(r){ return r && r.ok===false; });
                        if (fails.length){ details = ' | 1. Fehler: '+(fails[0].reason||'unbekannt'); }
                    }
                    var msg = ok ? ('Rechnungen gesendet: '+(j.sent||0)+(j.failed&&j.failed.length?(', Fehler: '+j.failed.length):''))+details : 'Fehler beim Senden';
                    if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner(msg, ok); }
                })
                .catch(function(e){ if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Fehler beim Senden: '+(e&&e.message?e.message:''), false); } });
                
            } else if (action === 'custom'){
                // E-Mail individuell
                openModal();
            }
            
            this.value = ''; // Reset dropdown
        });
        
        // Modal Cancel
        cancelBtn.addEventListener('click', closeModal);
        
        // Modal Send
        sendBtn.addEventListener('click', function(){
            var subject = subjectInput.value.trim();
            var body = bodyInput.value.trim();
            
            if (!subject || !body){
                if (typeof window.__sbShowBanner==='function'){ 
                    window.__sbShowBanner('Betreff und Nachricht müssen ausgefüllt sein', false); 
                }
                return;
            }
            
            var ids = getSelectedResIds();
            if (ids.length === 0){
                if (typeof window.__sbShowBanner==='function'){ 
                    window.__sbShowBanner('Keine Slots ausgewählt', false); 
                }
                closeModal();
                return;
            }
            
            closeModal();
            if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Sende E-Mails…', null); }
            
            var params = new URLSearchParams();
            ids.forEach(function(id){ params.append('reservation_ids[]', id); });
            params.append('subject', subject);
            params.append('body', body);
            
            fetch('<?= ABSURL ?>web/admin/ajax/send_custom_emails.php', { 
                method:'POST', 
                headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'}, 
                credentials:'same-origin', 
                body: params.toString() 
            })
            .then(function(r){ if(!r.ok){ return r.text().then(function(t){ throw new Error('HTTP '+r.status+' '+(t||'')); }); } return r.json(); })
            .then(function(j){
                var ok = j && j.ok===true;
                var msg = ok ? ('E-Mails versendet: '+(j.sent||0)+(j.failed?' | Fehler: '+j.failed:'')) : 'Fehler beim Senden';
                if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner(msg, ok); }
            })
            .catch(function(e){ if (typeof window.__sbShowBanner==='function'){ window.__sbShowBanner('Fehler beim Senden: '+(e&&e.message?e.message:''), false); } });
        });
        
        // Close modal on background click
        modal.addEventListener('click', function(e){
            if (e.target === modal) closeModal();
        });
    })();
    </script>