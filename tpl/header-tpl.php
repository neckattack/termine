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
