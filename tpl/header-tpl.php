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
	<link rel="stylesheet" type="text/css" media="screen" href="<?=WEBDIR?>css/styles.css" />

	<script type="text/javascript" src="https://use.typekit.com/pee6aqm.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
	<meta name="robots" content="noindex" />
	<?php if (isset($REDIRECT) && $REDIRECT === true) {?>
	<meta http-equiv="refresh" content="3;url=overview_clients.php" />
	<?php }?>
	</head>
<body>
<div class="page <?=PAGE?>">
	<div id="header">
  		<?php /* The logo */ ?>
		<a href="<?=ABSURL?>" class="logo"><img src="<?=WEBDIR?>images/logo.png" alt="Logo" /></a>
  <?
  if(intval($client['id'])>0 &strlen($client['image'])>100)
  {

    ?>

<img  src="../get_group_logo.php?id=<?=$client['id']?>&type=client " style="width: 160px; padding-left:414px"/>
    <?

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
			<?php if (isset($ADMIN) && $ADMIN === true) {?>
			<div class="logout">
				<?php if (isset($previous)) {?>
				<a href="<?=$previous?>">Zurück</a>
				<?php }?>
				<a href="edituser.php?id=<?=$_SESSION["userid"]?>">Profil</a>
				<a href="index.php?do=logout">Logout</a>
			</div>
			<?php }?>

			<?php if (isset($title[0])) {?><h1><?=$title?></h1><?php }?>
			<?php if (isset($gText[0])) {?><p><?=nl2br($gText)?></p><?php }?>
		</div>
	</div>
