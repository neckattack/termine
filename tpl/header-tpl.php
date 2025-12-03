<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * header.tpl.php
 * The Header
 * @param $title  string  Title for the page
 * @param $gText  string  (optional) Greeting text to be displayed
 */
$title = (isset($title)) ? stripslashes($title) : "";
$gText = (isset($gText)) ? stripslashes($gText) : "";
// Spezieller Titel, wenn Termine-Übersicht (kommende Termine) geöffnet ist
if (defined('PAGE') && PAGE === 'overview_clients.php'
	&& isset($_GET['sort_by']) && $_GET['sort_by'] === 'upcoming') {
	$title = 'Terminverwaltung';
}
error_reporting(E_ERROR & E_PARSE &E_WARNING);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en">
<head>
	<title><?=$title?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?=WEBDIR?>css/uithemes/ui-lightness/jquery-ui-1.9.2.custom.min.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?=WEBDIR?>css/ui.checkboxes.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?=WEBDIR?>css/jquery.multiselect.css" />
	<link rel="stylesheet" type="text/css" media="screen" href="<?=WEBDIR?>css/styles.css?v=5" />
	<style type="text/css">
		.design-switch{display:inline-block;vertical-align:middle;margin-left:8px;margin-top:0;width:44px;height:22px;border-radius:11px;background:#ddd;cursor:pointer;box-shadow:inset 0 0 3px rgba(0,0,0,0.3);}
		.design-switch-knob{position:relative;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,0.3);transition:left .2s ease-in-out;display:block;}
		.design-switch.on{background:#4caf50;}
		.design-switch.on .design-switch-knob{left:24px;}
	</style>

	<script type="text/javascript" src="https://use.typekit.com/pee6aqm.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	<meta name="robots" content="noindex" />
	<?php if (isset($REDIRECT) && $REDIRECT === true) {?>
	<meta http-equiv="refresh" content="3;url=overview_clients.php" />
	<?php }?>
</head>
<body>
<script type="text/javascript">
// Neues Admin-Design sofort beim Laden setzen (ohne 2s altes Layout), außer auf der Login-Seite
(function(){
	try {
		var path = window.location ? window.location.pathname : "";
		var isAdminLogin = /\/admin\/index\.php$/.test(path);
		if(isAdminLogin) { return; }
		var stored = null;
		if (window.localStorage) { stored = localStorage.getItem('sbNewDesign'); }
		// Default: wenn nichts gesetzt ist, neues Design aktiv
		var isOn = (stored === null || stored === '1');
		if(isOn){
			var b = document.body;
			if(!b) return;
			if(b.classList){ b.classList.add('sb-new-design'); }
			else if(b.className.indexOf('sb-new-design') === -1){ b.className += ' sb-new-design'; }
		}
	} catch(e){}
})();

// Globaler Error Handler für Debugging (besonders für Firefox-Probleme)
(function(){
	var errorCount = 0;
	var maxErrors = 5;
	
	// Fehler-Banner erstellen
	function showErrorBanner(msg, details) {
		if (errorCount >= maxErrors) return;
		errorCount++;
		
		var banner = document.createElement('div');
		banner.style.cssText = 'position:fixed;top:0;left:0;right:0;background:#ff4444;color:#fff;padding:12px 20px;z-index:99999;font-family:monospace;font-size:13px;line-height:1.4;box-shadow:0 2px 8px rgba(0,0,0,0.3);';
		banner.innerHTML = '<strong>⚠️ JavaScript-Fehler:</strong> ' + msg + (details ? '<br><small style="opacity:0.9;">' + details + '</small>' : '');
		
		// Schließen-Button
		var closeBtn = document.createElement('button');
		closeBtn.textContent = '✕';
		closeBtn.style.cssText = 'position:absolute;top:8px;right:10px;background:rgba(255,255,255,0.2);border:0;color:#fff;padding:4px 8px;cursor:pointer;border-radius:3px;';
		closeBtn.onclick = function(){ banner.remove(); };
		banner.appendChild(closeBtn);
		
		document.body.appendChild(banner);
		
		// Nach 10 Sekunden automatisch ausblenden
		setTimeout(function(){ if(banner.parentNode) banner.remove(); }, 10000);
	}
	
	// Browser-Info für Debugging
	function getBrowserInfo() {
		var ua = navigator.userAgent || '';
		var isFirefox = ua.indexOf('Firefox') > -1;
		var isChrome = ua.indexOf('Chrome') > -1;
		var isSafari = ua.indexOf('Safari') > -1 && !isChrome;
		var browser = isFirefox ? 'Firefox' : (isChrome ? 'Chrome' : (isSafari ? 'Safari' : 'Unbekannt'));
		return browser + ' | ' + ua.substring(0, 80);
	}

	// Bestimmte, bekannte Extension-Fehler ignorieren (MetaMask, Browser-Plugins, etc.)
	function isIgnoredError(msg, file) {
		msg = msg || '';
		file = file || '';
		var lowerMsg = String(msg).toLowerCase();
		var lowerFile = String(file).toLowerCase();

		// MetaMask / Wallet-Erweiterungen
		if (lowerMsg.indexOf('metamask') !== -1) return true;
		if (lowerMsg.indexOf('failed to connect to metamask') !== -1) return true;

		// Browser-Extensions (Chrome/Firefox)
		if (lowerFile.indexOf('chrome-extension://') === 0) return true;
		if (lowerFile.indexOf('moz-extension://') === 0) return true;

		// Sonstige typische Extension-Fehler können hier ergänzt werden
		return false;
	}
	
	// Globaler Error Handler
	window.addEventListener('error', function(e){
		var msg = e.message || 'Unbekannter Fehler';
		var file = e.filename || '';
		var line = e.lineno || '';
		var col = e.colno || '';
		var stack = (e.error && e.error.stack) ? e.error.stack : '';
		
		var details = 'Datei: ' + file + ' | Zeile: ' + line + ':' + col;
		
		// Extension-Fehler ignorieren
		if (isIgnoredError(msg, file)) {
			console.debug('Ignoriere Extension-Fehler:', msg, details);
			return false;
		}
		
		console.error('❌ JS-Fehler:', msg, details, '\nBrowser:', getBrowserInfo(), '\nStack:', stack);
		
		showErrorBanner(msg, details);
		
		// Fehler zur späteren Analyse in localStorage speichern
		try {
			if (window.localStorage) {
				var errors = JSON.parse(localStorage.getItem('jsErrors') || '[]');
				errors.push({
					msg: msg,
					file: file,
					line: line,
					col: col,
					browser: getBrowserInfo(),
					timestamp: new Date().toISOString(),
					url: window.location.href
				});
				// Nur die letzten 20 Fehler behalten
				if (errors.length > 20) errors = errors.slice(-20);
				localStorage.setItem('jsErrors', JSON.stringify(errors));
			}
		} catch(storageError) {
			console.warn('Konnte Fehler nicht speichern:', storageError);
		}
		
		return false; // Fehler nicht weiter propagieren
	});
	
	// Promise-Fehler abfangen (z.B. fetch-Fehler)
	window.addEventListener('unhandledrejection', function(e){
		var msg = (e.reason && e.reason.message) ? e.reason.message : String(e.reason);
		var file = '';
		// Bei manchen Promise-Fehlern steckt die Info im Stack
		if (e.reason && e.reason.stack) {
			var stack = String(e.reason.stack);
			if (stack.indexOf('chrome-extension://') !== -1) file = 'chrome-extension://';
			if (stack.indexOf('moz-extension://') !== -1) file = 'moz-extension://';
		}
		
		// Extension-Fehler ignorieren
		if (isIgnoredError(msg, file)) {
			console.debug('Ignoriere Extension-Promise-Fehler:', msg);
			return;
		}
		
		console.error('❌ Unhandled Promise Rejection:', msg, '\nBrowser:', getBrowserInfo());
		showErrorBanner('Promise-Fehler: ' + msg, 'Siehe Console für Details');
	});
	
	console.log('✅ Error Handler aktiv | Browser:', getBrowserInfo());
})();
</script>
<?php include_once __DIR__ . '/../inc/language-switcher.php'; ?>
<?php if (isset($ADMIN) && $ADMIN === true) { ?>
<div class="sb-layout">
	<div class="sb-sidebar" style="display:none;">
		<div class="sb-sidebar-inner">
			<div class="sb-sidebar-logo">
				<a href="<?=ABSURL?>" style="border:0; padding:0;">
					<img src="<?=WEBDIR?>images/logo-sidebar.png" alt="neckAttack" />
				</a>
			</div>
			<ul class="sb-sidebar-nav">
				<li><a href="overview_clients.php?sort_by=upcoming">Termine</a></li>
				<li><a href="overview_clients.php">Kunden</a></li>
				<?php
				// Gruppenverwaltung nur anzeigen, wenn SUPERADMIN und wir NICHT auf der Termin-Ansicht sind
				$showGroupsSidebar = false;
				if (isset($SUPERADMIN) && $SUPERADMIN === true) {
					$script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
					$isTermineView = (strpos($script, 'overview_clients.php') !== false
						&& isset($_GET['sort_by']) && $_GET['sort_by'] === 'upcoming');
					$showGroupsSidebar = !$isTermineView;
				}
				if ($showGroupsSidebar) {?>
				<li class="sb-sidebar-subitem"><a href="overview_groups.php">Gruppenverwaltung</a></li>
				<?php }?>
				<li><a href="overview_users.php">User</a></li>
				<li><a href="overview_services.php">Services</a></li>
				<li><a href="edituser.php?id=<?=$_SESSION['userid']?>">Profil</a></li>
				<li><a href="index.php?do=logout">Logout</a></li>
			</ul>
			<div class="sb-sidebar-footer">Admin-Ansicht</div>
		</div>
	</div>
	<div class="sb-main">
<?php } ?>
<div class="page <?=PAGE?>">
<div id="header">
  		<?php /* The logo */ ?>
		<a href="<?=ABSURL?>" class="logo"><img src="<?=WEBDIR?>images/logo.png" alt="Logo" /></a>
  <?php
  // Sicher prüfen, ob $client vorhanden ist
  if (isset($client) && is_array($client)) {
      $cid = isset($client['id']) ? (int)$client['id'] : 0;
      $cimg = isset($client['image']) ? (string)$client['image'] : '';
      if ($cid > 0 && strlen($cimg) > 100) {
          echo '<img src="../get_group_logo.php?id='.htmlspecialchars($cid, ENT_QUOTES, 'UTF-8').'&type=client" style="width: 160px; padding-left:414px"/>';
      }
  }
  ?>

		<?php /* Branding */ ?>
		<?php /* // Disable
		<?php if (isset($hasImage) && $hasImage === true && isset($imageLink{1})) {?>
		<img class="branding" src="get_image.php?<?=$imageLink?>" alt="Branding" />
		<?php }?>
		*/

         ?>

		<?php /* Greeting text */ ?>
		<div class="greetingText">
			<div class="sb-header-bar">
				<div class="sb-header-left">
					<?php if (isset($title[0])) {?><h1><?=$title?></h1><?php }?>
				</div>
				<?php if (isset($ADMIN) && $ADMIN === true) {?>
				<!-- Admin-Bereich: Suche + Design-Switch (Profil/Logout nur in Sidebar) -->
				<div class="sb-header-left">
					<!-- Admin-Suche nach Bucher-E-Mail -->
					<form method="get" action="<?=ABSURL?>web/admin/search_reservations.php" class="sb-search-form">
						<input type="text" name="email" placeholder="E-Mail suchen" value="<?= isset($_GET['email'])?htmlspecialchars($_GET['email']):'' ?>" />
						<button type="submit" title="Suchen">🔍</button>
					</form>
				</div>
				<div class="sb-header-right">
					<span class="sb-design-wrap">
						<span class="sb-design-label">Neues Design</span>
						<div id="sb-design-switch" class="design-switch" title="Neues Design umschalten">
							<div class="design-switch-knob"></div>
						</div>
					</span>
				</div>
				<?php }?>
			</div>
			<?php if (isset($gText[0])) {?><p><?=nl2br($gText)?></p><?php }?>
		</div>
	</div>

<script type="text/javascript">
(function(){
	function applyState(on){
		var body=document.body; if(!body) return;
		// Login-Seite (admin-index) soll nie das neue Design mit Sidebar/Body-Padding nutzen
		var isLoginPage = !!document.querySelector('.page.admin-index');
		var sw=document.getElementById('sb-design-switch');
		var sidebar=document.querySelector('.sb-sidebar');
		if(isLoginPage){
			body.classList.remove('sb-new-design');
			if(sw) sw.classList.remove('on');
			if(sidebar) sidebar.style.display='none';
			return;
		}
		if(on){
			body.classList.add('sb-new-design');
			if(sw) sw.classList.add('on');
			if(sidebar) sidebar.style.display='block';
		} else {
			body.classList.remove('sb-new-design');
			if(sw) sw.classList.remove('on');
			if(sidebar) sidebar.style.display='none';
		}
	}
	// Zustand aus localStorage lesen ("1" = neues Design an)
	var stored= null;
	try { stored = window.localStorage ? localStorage.getItem('sbNewDesign') : null; } catch(e) { stored = null; }
	// Default: Wenn noch nichts gesetzt ist, neues Design als Standard aktivieren
	var isOn = (stored === '1' || stored === null);
	if(document.readyState==='loading'){
		document.addEventListener('DOMContentLoaded',function(){ applyState(isOn); });
	}else{
		applyState(isOn);
	}
	var sw=document.getElementById('sb-design-switch');
	if(sw){
		sw.addEventListener('click',function(){
			var currentlyOn=document.body.classList.contains('sb-new-design');
			var on=!currentlyOn;
			applyState(on);
			try {
				if(window.localStorage){ localStorage.setItem('sbNewDesign', on ? '1' : '0'); }
			} catch(e){}
		});
	}
})();
</script>
