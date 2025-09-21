<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * footer.tpl.php
 * The footer
 */

?>
	<div id="footer">
		<p>© NeckAttack® Mobile Massage | <a rel="external" href="https://www.neckattack.net/kontakt/impressum/">Impressum</a></p>
	</div>
</div>

<!-- Include Scripts -->
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>-->
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="<?=WEBDIR?>js/jquery-1.8.3.js"></script>
<script type="text/javascript" src="<?=WEBDIR?>js/jquery-ui-1.9.2.min.js"></script>
<script type="text/javascript" src="<?=WEBDIR?>js/ui.checkboxes.js"></script>
<script type="text/javascript" src="<?=WEBDIR?>js/jquery.multiselect.min.js"></script>
<script type="text/javascript" src="<?=WEBDIR?>js/jquery.multiselect.filter.js"></script>
<script type="text/javascript" src="<?=WEBDIR?>js/jquery.validate.min.js"></script>
<!-- Set custom options -->
<script type="text/javascript">
	var gOptions = {
		dateFormat  : "<?=$jsDatestr?>"
	};
</script>
<script type="text/javascript">
	window.translations = <?php echo json_encode($lang); ?>;
</script>
<!-- Include custom code -->
<script type="text/javascript" src="<?=WEBDIR?>js/page.js"></script>

<?php if (isset($ADMIN) && $ADMIN === true) {?>
<script type="text/javascript" src="<?=WEBDIR?>js/admin.js"></script>
<?php }?>

</body>
</html>