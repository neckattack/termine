<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * adminlogin.tpl.php
 * Login form for the admin
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title = (isset($LOGGED_IN) && $LOGGED_IN === true) ? "Admin Index" : "Admin Login";
require ROOT."/tpl/header-tpl.php";
?>

	<div id="content">
		<?php if (isset($REDIRECT) && $REDIRECT === true) {?>
		<p>Login erfolgreich.</p>
		<p>Leite weiter auf <a href="overview_clients.php">Kundenübersicht</a>.</p>

		<?php } elseif ($LOGGED_IN !== true) {?>
		<?php if ($error > 0) {?>
		<p class="error">Login fehlgeschlagen</p>
		<?php }?>
		<div class="admin-login-layout">
			<div class="admin-login-visual">
				<!-- Linke Bildhälfte: Massage-Foto (Bilddatei bei Bedarf anpassen) -->
				<div class="admin-login-visual-inner"></div>
			</div>
			<div class="admin-login-panel">
				<h1 class="admin-login-title">Admin Login</h1>
				<form id="login" method="post" action="<?=$_SERVER["PHP_SELF"]?>">
					<div class="admin-login-fields">
						<input type="text" name="username" value="" placeholder="Benutzername" />
						<input type="password" name="password" value="" placeholder="Passwort" />
						<input type="submit" name="submit" value="Login" />
					</div>
				</form>
			</div>
		</div>
		<?php } else {?>
		<ul class="menu">
			<li><a href="overview_clients.php">&raquo; Kundenverwaltung</a></li>
			<?php if ($SUPERADMIN === true) {?>
			<li><a href="overview_groups.php">&raquo; Gruppenverwaltung</a></li>
			<li><a href="overview_users.php">&raquo; Benutzerverwaltung</a></li>
			<?php }?>
		</ul>
		<?php }?>
	</div>

<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>
