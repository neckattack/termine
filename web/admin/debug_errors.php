<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * debug_errors.php
 * Debug-Seite zum Anzeigen von JavaScript-Fehlern aus localStorage
 */

error_reporting(-1);
ini_set('display_errors', 1);
$PAGE = basename(__FILE__);
require "_root_.php";
require ROOT."/inc/_include.php";
require ROOT."/inc/admincheck.php";

$ADMIN = true;
$title = "JavaScript-Fehler Debug";
require ROOT."/tpl/header-tpl.php";
?>

<div id="content">
	<h2>JavaScript-Fehler Debug</h2>
	
	<p style="background:#fff3cd; padding:10px; border-left:3px solid #ffc107; margin:15px 0;">
		<strong>ℹ️ Hinweis:</strong> Diese Seite zeigt JavaScript-Fehler, die im Browser der Benutzer aufgetreten sind und in localStorage gespeichert wurden.
		Die Fehler werden automatisch beim Auftreten erfasst.
	</p>
	
	<div style="margin:20px 0;">
		<button onclick="showStoredErrors()" style="padding:8px 16px; background:#2b7; color:#fff; border:0; border-radius:4px; cursor:pointer; margin-right:10px;">
			Gespeicherte Fehler anzeigen
		</button>
		<button onclick="clearStoredErrors()" style="padding:8px 16px; background:#dc3545; color:#fff; border:0; border-radius:4px; cursor:pointer;">
			Fehler löschen
		</button>
	</div>
	
	<div id="error-display" style="background:#f8f9fa; padding:20px; border:1px solid #ddd; border-radius:4px; margin-top:20px; min-height:100px;">
		<p style="color:#666;">Klicke auf "Gespeicherte Fehler anzeigen" um die Fehler aus deinem Browser zu laden.</p>
	</div>
	
	<h3 style="margin-top:30px;">Browser-Informationen</h3>
	<div id="browser-info" style="background:#f8f9fa; padding:15px; border:1px solid #ddd; border-radius:4px; font-family:monospace; font-size:13px;">
		Lade...
	</div>
	
	<h3 style="margin-top:30px;">So verwendest du diese Seite:</h3>
	<ol style="line-height:1.8;">
		<li><strong>Für dich selbst:</strong> Öffne diese Seite in Firefox, navigiere zur Problemseite, und schau dann hier nach Fehlern.</li>
		<li><strong>Für andere Benutzer:</strong> Bitte sie, diese Schritte zu befolgen:
			<ol style="margin-top:8px;">
				<li>Mit Firefox auf die Problemseite gehen (wo der weiße Bildschirm erscheint)</li>
				<li>F12 drücken → Console-Tab öffnen</li>
				<li>Screenshot der Fehler machen und dir schicken</li>
				<li>ODER: Diese Debug-Seite öffnen und "Gespeicherte Fehler anzeigen" klicken</li>
			</ol>
		</li>
		<li><strong>Fehleranalyse:</strong> Die Fehler zeigen dir:
			<ul style="margin-top:8px;">
				<li>Fehlermeldung</li>
				<li>Datei und Zeilennummer</li>
				<li>Browser-Version</li>
				<li>Zeitpunkt</li>
				<li>URL wo der Fehler auftrat</li>
			</ul>
		</li>
	</ol>
</div>

<script>
function showStoredErrors() {
	var display = document.getElementById('error-display');
	
	try {
		var errors = JSON.parse(localStorage.getItem('jsErrors') || '[]');
		
		if (errors.length === 0) {
			display.innerHTML = '<p style="color:#28a745; font-weight:600;">✅ Keine Fehler gefunden! Alles läuft gut.</p>';
			return;
		}
		
		var html = '<h4>Gefundene Fehler (' + errors.length + '):</h4>';
		html += '<div style="max-height:600px; overflow-y:auto;">';
		
		errors.reverse().forEach(function(err, idx) {
			var time = new Date(err.timestamp).toLocaleString('de-DE');
			html += '<div style="background:#fff; padding:15px; margin:10px 0; border-left:4px solid #dc3545; border-radius:4px;">';
			html += '<div style="display:flex; justify-content:space-between; margin-bottom:8px;">';
			html += '<strong style="color:#dc3545;">#' + (errors.length - idx) + ': ' + escapeHtml(err.msg) + '</strong>';
			html += '<span style="color:#666; font-size:12px;">' + time + '</span>';
			html += '</div>';
			html += '<div style="font-family:monospace; font-size:12px; color:#666; margin-top:8px;">';
			html += '<div><strong>Datei:</strong> ' + escapeHtml(err.file) + '</div>';
			html += '<div><strong>Zeile:</strong> ' + err.line + ':' + err.col + '</div>';
			html += '<div><strong>Browser:</strong> ' + escapeHtml(err.browser) + '</div>';
			html += '<div><strong>URL:</strong> ' + escapeHtml(err.url) + '</div>';
			html += '</div>';
			html += '</div>';
		});
		
		html += '</div>';
		display.innerHTML = html;
		
	} catch(e) {
		display.innerHTML = '<p style="color:#dc3545;">❌ Fehler beim Laden: ' + escapeHtml(e.message) + '</p>';
	}
}

function clearStoredErrors() {
	if (!confirm('Möchtest du wirklich alle gespeicherten Fehler löschen?')) return;
	
	try {
		localStorage.removeItem('jsErrors');
		document.getElementById('error-display').innerHTML = '<p style="color:#28a745; font-weight:600;">✅ Alle Fehler gelöscht.</p>';
	} catch(e) {
		alert('Fehler beim Löschen: ' + e.message);
	}
}

function escapeHtml(text) {
	var div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

// Browser-Info anzeigen
(function(){
	var ua = navigator.userAgent;
	var isFirefox = ua.indexOf('Firefox') > -1;
	var isChrome = ua.indexOf('Chrome') > -1;
	var isSafari = ua.indexOf('Safari') > -1 && !isChrome;
	var browserName = isFirefox ? 'Firefox' : (isChrome ? 'Chrome' : (isSafari ? 'Safari' : 'Unbekannt'));
	
	var info = '<strong>Browser:</strong> ' + browserName + '<br>';
	info += '<strong>User Agent:</strong> ' + escapeHtml(ua) + '<br>';
	info += '<strong>Sprache:</strong> ' + navigator.language + '<br>';
	info += '<strong>Platform:</strong> ' + navigator.platform + '<br>';
	info += '<strong>localStorage verfügbar:</strong> ' + (window.localStorage ? 'Ja' : 'Nein');
	
	document.getElementById('browser-info').innerHTML = info;
})();
</script>

<?php
require ROOT."/tpl/footer-tpl.php";
?>
