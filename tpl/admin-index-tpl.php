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
		<div class="admin-login-layout" style="display:flex;min-height:100vh;width:100%;background:#f5f5f5;">
			<div class="admin-login-visual" style="flex:1 1 50%;position:relative;overflow:hidden;">
				<!-- Linke Bildhälfte: Massage-Foto -->
				<div class="admin-login-visual-inner" style="position:absolute;top:0;left:0;right:0;bottom:0;background-size:cover;background-position:center;background-repeat:no-repeat;background-image:url('../images/login-massage.png');"></div>
			</div>
			<div class="admin-login-panel" style="flex:1 1 50%;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px 20px;background:#ffffff;">
				<h1 class="admin-login-title">Login</h1>
				<form id="login" method="post" action="<?=$_SERVER["PHP_SELF"]?>">
					<div class="admin-login-fields" style="width:100%;max-width:360px;display:flex;flex-direction:column;gap:12px;">
						<input type="text" name="username" value="" placeholder="Benutzername" />
						<input type="password" name="password" value="" placeholder="Passwort" />
						<input type="submit" name="submit" value="Login" />
					</div>
				</form>
			</div>
		</div>
		<?php }?>
	</div>

<?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>
