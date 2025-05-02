<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * welcome_tpl.php
 * Show an input field to enter the code value
 * DEBUG: Display list of all enabled clients
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

$DEBUG = false;

// Header
// $title  = "Willkommen";
// $gText  = "bei neckAttack!";
// require ROOT."/tpl/header-tpl.php";
?>

	<div class="col-md-12">
		<form id="enterCode" action="<?=$_SERVER["PHP_SELF"]?>" method="get">
			<div class="ctm_inpt">
				<input name="code" id="code" value="Ihr Code" />
				<input style="height: 1.4em;font-size: 26px;font-size: 2.2rem;color: #888;border: 1px solid #888;" type="submit" id="submit" value="OK" />
			</div>

			<?php if ($DEBUG === true) {?>
			<div class="debug">
				<h3>DEBUG:</h3>
				<ul>
					<?php foreach ($clients AS $client) {?>
					<li><a href="<?=WEBDIR.$client["hashlink"]?>"><?=$client["name"]?></a></li>
					<?php }?>
				</ul>
			</div>
			<?php }?>
		
		</form>
	</div>

<div class="col-md-12" style="padding-top: 90px;"> <?php
// Footer
require ROOT."/tpl/footer-tpl.php";
?>
</div>