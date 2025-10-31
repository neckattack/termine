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

		        <div class="slots">
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
				<li style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin:8px 0;">
					<span class="time" style="min-width:120px; color:#333; white-space:nowrap;">
						<?=$slot["time_start"]?> - <?=$slot["time_end"]?>
					</span>
					<span class="name" style="min-width:180px; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
						<?php if (isset($slot["res_name"][1])) {?><a href="mailto:<?=$slot["email"]?>"><?=$slot["res_name"]?></a><?php } else {?>- frei -<?php }?>
					</span>
					<?php if (isset($slot['res_id']) && $slot['res_id']>0 && (isset($patient_billing_required) && (int)$patient_billing_required===1)) { ?>
					<div class="inline-form" style="display:flex; align-items:center; gap:8px; margin-left:6px; flex:1 1 420px; min-width:320px;">
						<input type="text" name="res_diagnosis[<?=$slot['res_id']?>]" value="<?=htmlspecialchars($defaultDiagnosis)?>" placeholder="Diagnose" style="width:220px; padding:2px 6px; flex:0 0 auto;" />
						<select name="res_services[<?=$slot['res_id']?>][]" data-resid="<?=$slot['res_id']?>" multiple="multiple" class="multiselect res-services" style="flex:1 1 280px; min-width:280px; max-width:100%;">
							<?php if (is_array($services)) { foreach ($services as $s) { $sel = in_array((int)$s['id'], $selectedIds, true) ? 'selected="selected"' : ''; ?>
								<option value="<?=$s['id']?>" <?=$sel?>><?=$s['code']?> – <?=$s['title']?></option>
							<?php } } ?>
						</select>
					</div>
					<!-- Preis-Box (Anzeige, noch ohne Speichern) -->
					<?php 
						// Index der Services nach ID für schnelle Zugriffe
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
					                    <a href="#" class="toggle-calc" data-target="calc-<?=$slot['res_id']?>" style="margin-left:6px; font-size:12px;">Berechnung anzeigen</a>
                    <div id="calc-<?=$slot['res_id']?>" class="calc-box" data-resid="<?=$slot['res_id']?>" style="flex:1 1 420px; min-width:320px; display:none;">
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
					<?php } ?>
					<div style="margin-left:auto; display:flex; gap:8px; align-items:center; flex:0 0 auto;">
						<?php if (isset($slot["res_id"])) {?><a href="invoice.php?res_id=<?=$slot["res_id"]?>" class="pdf" target="_blank">PDF</a><?php }?>
						<?php if (isset($slot["res_id"])) {?><a href="editslots.php?action=deletereservation&amp;id=<?=$slot["res_id"]?>" class="delete orange">Reservierung löschen</a><?php }?>
						<a href="editslots.php?action=deletetimeentry&amp;id=<?=$slot["time_id"]?>" class="delete">Termin entfernen</a>
						<?php if(isset($slot["res_id"])) { ?>
							<a title="Klicken Sie auf den Link, um die Buchungs-URL in die Zwischenablage zu kopieren" href="javascript:void(0);" data-content="<?= ABSURL ?>web/bookings.php?e=<?=md5(strtolower($slot["email"]))?>" class="copy-url">Buchungs-URL kopieren</a>
						<?php } ?>
					</div>
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
    })();
</script>