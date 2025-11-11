<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * landingpage.tpl.php
 * Default page for index.php
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
$title  = $client["name"];
$gText  = $client["greeting_text"];
?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?= $title ?> | Neckattack</title>

    <link href="/bootstrap/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bootstrap/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="/bootstrap/vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
    <link href="/bootstrap/css/stylish-portfolio.css" rel="stylesheet">
    <style>

      .disabled span {
        color: #dee2e6;
      }

	  .modal-dialog {
		margin-top: 50vh;
    	transform: translateY(-50%) !important;
	  }

	  .pay-list {
		 float: right;
	  }

	  .pay-list__button-wrapper {
	     margin: 0.7rem 0;
	  }

	  .pay-list__title {
	     margin: 0.5rem 0;
	  }
    </style>
  </head>
  <body id="page-top">

    <!-- Header -->
    <header class="masthead d-flex">
      <div class="container text-center my-auto">
        <div><img class="logo" alt="<?php echo $title; ?>" src="/bootstrap/img/logo.png"></div>
      </div>
      <div class="overlay"></div>
    </header>

    <section class="elow">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
				    <div class="col-md-12">
				    <?php
				    	if (!empty($client["image"])) { ?>
				    		<img  src="/get_group_logo.php?id=<?=$client['id']?>&type=client" alt="Logo for <?php echo $title; ?>"  style="margin: 9px;margin-right: 25px; float: left;width: auto;height: 130px;"/>
				    <?php } ?>
				        <h1 class="mb-1"><?php echo $title; ?></h1>
				        <h3 class="mb-5"><span style="white-space:pre-line;"><em><?php echo $gText; ?></em></span></h3>
				    </div>
				</div>
			</div>
		</div>
    </section>

    <!-- Content -->
      <section class="container text-center content-block">
		<?php $price = ($client["price"]) ? floatval(str_replace(',','.',$client['price'])) : 0;
		if ($price ==0 || empty($price)) { ?>
			<form id="register" method="post" action="<?=$_SERVER["REQUEST_URI"]?>">
		<?php } ?>

        <div class="row">
			<div class="col-md-12">
			<!-- Error Message(s) -->
			<div style="display:none;" id="errorMsg" <?=($error > 0) ? 'style="display: block;"' : ''?>>
				<ul>
					<?php if ($error > 0) {?>
					<?php foreach ($messages AS $msg) {?>
						<li><?=$msg?></strong></li>
					<?php }?>
					<?php } else {?>
						<li>Bitte wählen Sie mindestens einen Termin aus</li>
					<?php }?>
				</ul>
			</div>
			
			<p id="toolate" <?=($toolate === true) ? 'style="display: block;"': ''?>>Leider sind einige Ihrer gewünschten Termine inzwischen bereits vergeben. Bitte überprüfen Sie Ihre Auswahl.</p>
			<p id="error">Leider ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut!</p>
			<p id="success" <?=($success === true) ? 'style="display: block;"': ''?>>Sie haben sich erfolgreich für die markierten Termine eingetragen.</p>
			
			</div>
		</div>

        <div class="row">
			<div class="col-md-12">
				<div style="display: none;" class="my_chek_name">Bitte geben Sie Ihren Namen an<br/></div>
				<div style="display: none;" class="my_chek_email">Bitte geben Sie eine gültige E-Mail-Adresse an<br/></div>
				<div style="display: none;" class="my_chek_chebox">Bitte wählen Sie mindestens einen Termin aus<br/></div>
				<div style="display: none;" class="my_chek_payment"></div>
			</div>
			<!-- Error Message(s) -->
		</div>
        <div class="row">
              <div class="col-md-4">
				<div class="fLeft dates">
					<h3>Datum:</h3>
					<?php if ($result !== false) {
						foreach ($result["dates"] AS $key => $date) {
					?>
					<div class="form-check">
						<label for="date_<?=$date["id"]?>">
							<input type="radio" id="date_<?=$date["id"]?>" name="date" <?=((int)$date["id"] === $date_id) ? 'checked="checked"': ""?> value="<?=$date["id"]?>" />
							<span><?=$date["date"]?></span><sup class="datetimescount"></sup>
						</label>
					</div>
					<?php } } ?>
					<noscript><p><input type="submit" name="select_date" value="Anzeigen" /></p></noscript>
				</div>
              </div>

              <div class="col-md-4">
				<div class="fLeft times tni">
					<h3>Termin auswählen:</h3>
					<?php if ($result !== false) {
						foreach ($result["dates"] AS $key => $date) {
					?>
					<div id="times_<?=$date["id"]?>" data-date-id="<?=$date["id"]?>" class="timesCont" <?= ((int)$date["id"] !== $date_id) ? 'hidden' : '' ?>>
						<?php if (isset($result["times_assoc"][$date["id"]])) {
							foreach ($result["times_assoc"][$date["id"]] AS $time) {
								$class1 = (isset($time["taken"])) ? "disabled" : "";
								$class2 = ($success === true && isset($reserved["times"]) && in_array($time["id"], $reserved["times"])) ? " success" : "";
								$class3 = ($toolate === true && isset($taken) && in_array($time["id"], $taken)) ? " toolate" : ""; 
						?>
							<div class="form-check">
								<label class="<?=$class1?><?=$class2?><?=$class3?>" for="time_<?=$time["id"]?>">
									<input type="checkbox" id="time_<?=$time["id"]?>" name="times[]" value="<?=$time["id"]?>"<?=(isset($time["taken"]))?" disabled=\"disabled\"":""?> />
									<span ><?=$time["time_start"]?> - <?=$time["time_end"]?></span>
								</label>
							</div>

						<?php } } ?>
					</div>
					<?php } } ?>
				</div>
              </div>

             <div class="col-md-4">

                <div class="fRight registerMe">
					<h3>Anmelden als:</h3>

					<?php

					                    if ($price ==0 || empty($price)) { ?>
                        <div class="cnt">
                            <input type="text" id="name" name="name" value="<?=(isset($P["name"])) ? $P["name"] : "Name"?>" placeholder="Name" /><br />
                        </div>
                        <div class="cnt">
                            <input type="text" id="email" name="email" value="<?=(isset($P["email"])) ? $P["email"] : "E-Mail"?>" placeholder="E-Mail" /><br />
                        </div>
                        <?php if (!empty($client['patient_billing_required'])) { ?>
                        <div class="form-group text-left" style="margin-top:6px;">
                            <label style="display:block; font-weight:600;">Rechnungsadresse</label>
                            <div class="cnt"><input type="text" name="street" value="<?= isset($P['street'])?htmlspecialchars($P['street']):'' ?>" placeholder="Straße" /></div>
                            <div class="cnt"><input type="text" name="house_no" value="<?= isset($P['house_no'])?htmlspecialchars($P['house_no']):'' ?>" placeholder="Hausnummer" /></div>
                            <div class="cnt"><input type="text" name="zip" value="<?= isset($P['zip'])?htmlspecialchars($P['zip']):'' ?>" placeholder="PLZ" /></div>
                            <div class="cnt"><input type="text" name="city" value="<?= isset($P['city'])?htmlspecialchars($P['city']):'' ?>" placeholder="Stadt" /></div>
                            <div class="cnt"><input type="text" name="birthdate" value="<?= isset($P['birthdate'])?htmlspecialchars($P['birthdate']):'' ?>" placeholder="Geburtsdatum (TT.MM.JJJJ)" /></div>
                        </div>
                        <?php } ?>

							<div class="form-group text-left times_info_block">
								<div class="text-warning">Bitte wählen Sie mindestens einen Termin aus</div>
								<div class="text-info"></div>
							</div>

							<div class="form-group text-left small">
								Hiermit bestätige ich, die <a href="http://neckattack.net/datenschutz/" rel="nofollow" target="_blank">Datenschutzbestimmungen</a> gelesen zu haben und akzeptiere diese.
							</div>

							<input class="btn btn-warning" style="float: right" type="submit" id="submit" name="submit" value="Anmelden"                    <?php } else { ?>

                        <div class="form-group">
                            <input type="text" id="name" class="form-control"   value="<?=(isset($P["name"])) ? $P["name"] : "Name"?>" placeholder="Name" />
                        </div>

                        <div class="form-group">
                            <input type="text" id="email" class="form-control"  value="<?=(isset($P["email"])) ? $P["email"] : "E-Mail"?>" placeholder="E-Mail" />
                        </div>
                        <?php if (!empty($client['patient_billing_required'])) { ?>
                        <div class="form-group text-left" style="margin-top:6px;">
                            <label style="display:block; font-weight:600;">Rechnungsadresse</label>
                            <div class="form-row">
                                <div class="col-12" style="margin-bottom:6px;"><input type="text" class="form-control" name="street" value="<?= isset($P['street'])?htmlspecialchars($P['street']):'' ?>" placeholder="Straße" /></div>
                                <div class="col-12" style="margin-bottom:6px;"><input type="text" class="form-control" name="house_no" value="<?= isset($P['house_no'])?htmlspecialchars($P['house_no']):'' ?>" placeholder="Hausnummer" /></div>
                                <div class="col-12" style="margin-bottom:6px;"><input type="text" class="form-control" name="zip" value="<?= isset($P['zip'])?htmlspecialchars($P['zip']):'' ?>" placeholder="PLZ" /></div>
                                <div class="col-12" style="margin-bottom:6px;"><input type="text" class="form-control" name="city" value="<?= isset($P['city'])?htmlspecialchars($P['city']):'' ?>" placeholder="Stadt" /></div>
                                <div class="col-12" style="margin-bottom:6px;"><input type="text" class="form-control" name="birthdate" value="<?= isset($P['birthdate'])?htmlspecialchars($P['birthdate']):'' ?>" placeholder="Geburtsdatum (TT.MM.JJJJ)" /></div>
                            </div>
                        </div>
                        <?php } ?>

							<div class="form-group text-left times_info_block">
								<div class="text-warning">Bitte wählen Sie mindestens einen Termin aus</div>
								<div class="text-info"></div>
							</div>
{{ ... }}

							<div class="form-group text-left small">
								Hiermit bestätige ich, die <a href="http://neckattack.net/datenschutz/" rel="nofollow" target="_blank">Datenschutzbestimmungen</a> gelesen zu haben und akzeptiere diese.
							</div>

							<div class="form-group clearfix" style="position: relative;">
								<div class="pay-list text-right">
									<div class="pay-list__title">Jetzt bezahlen:</div>
									<div class="pay-list__button-wrapper">
										<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
											<input type="hidden" name="business" value="<?= $config['paypal_email'] ?>">
											<input type="hidden" name="cmd" value="_xclick">
											<input type="hidden" name="charset" value="utf-8">
											<input type="hidden" name="lc" value="DE">
											<input type="hidden" name="currency_code" value="<?= $config['paypal_currency'] ?>">
											<input type="hidden" name="amount" value="<?php echo $price; ?>">
											<input id="ctm_name" type="hidden" name="item_name" value="<?php echo $title; ?>" />  
											<input id="ctm_email" type="hidden" name="custom" value="" />
											<input type="hidden" name="return" value="<?= ABSURL.'thanks.php?hash='.$client["hashlink"] ?>" />
											<input type="hidden" name="cancel_return" value="<?= ABSURL.$client["hashlink"] ?>" />
											<input type="hidden" name="notify_url" value="<?php echo ABSURL.'pay.php'; ?>" />
											<input type="hidden" name="item_number" value="<?php echo $client["hashlink"]; ?>">
											<input type="submit" hidden="hidden" name="submit" value="PayPal" />
											<button class="btn btn-warning" type="submit" id="submit"><i class="fa fa-paypal"></i> &nbsp; PayPal</button>
										</form>
									</div>
									<div class="pay-list__button-wrapper">
										<button type="button" id="stripeButton" class="btn btn-warning"><i class="fa fa-credit-card"></i> &nbsp; Kreditkarte</button>
									</div>
								</div>
								<?php /*
								<div class="dropdown pay-list">
									<button class="btn btn-warning dropdown-toggle" type="button" data-toggle="dropdown">Jetzt bezahlen
									<span class="caret"></span></button>
									<ul class="dropdown-menu">
										<li>
										</li>
										<li>
											<?php /*
											<form action="your-server-side-code" method="POST">
												<script
													src="https://checkout.stripe.com/checkout.js" class="stripe-button"
													data-key="pk_test_iCbGXnutxq2eNJrLMFm2VCua"
													data-amount="999"
													data-name="asdf@sdaf"
													data-description="Example charge"
													data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
													data-locale="auto">
												</script>
											</form>
											*/ ?>
								<?php /*
										</li>
									</ul>
								</div>
								*/ ?>

								<div class="pp_price"><div class="nki">€</div><div class="price" data-price="<?= $price ?>"><?= number_format($price, 2, ',', ''); ?></div></div>
							</div>
					<?php } ?>
								<input type="hidden" name="h" value="<?=$client["hashlink"]?>" />
								<input type="hidden" name="id" value="<?=$clientId?>" />
				</div>
             </div>

        </div>

		<?php if ($price ==0 || empty($price)) { ?> </form> <?php } ?>

        <div class="row">
			<div class="col-md-12">
				<div class="text-center footer"><p>© NeckAttack® Mobile Massage | <a rel="external" href="http://www.neckattack.net/kontakt/impressum/" target="_blank">Impressum</a></p></div>
			</div>
        </div>
      </section>

	<script src="/js/jquery-1.8.3.js"></script>
	<script src="/js/jquery-ui-1.9.2.min.js"></script>
	<script src="/js/ui.checkboxes.js"></script>
	<?php /*
	<script src="/js/jquery.multiselect.min.js"></script>
	<script src="/js/jquery.multiselect.filter.js"></script>
	*/ ?>
	<script src="/js/jquery.validate.min.js"></script>
	<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
	<script src="/bootstrap/vendor/bootstrap/js/bootstrap.js"></script>
	

	<script>
		var clientName = "<?= $title ?>";
		var gOptions = {
			dateFormat  : "dd.mm.yy"
		};
		var pp = <?= $price ?>;
	</script>
	<script src="/js/page.js"></script>

<?php if ($price !=0 && !empty($price)) { ?>
	<script>
		jQuery(document).ready(function ($) {

          	$("#submit").on('click', function(event) {
            	if ( $(".price").text() !== '' ) {

					var dateId = $('input[name="date"]:checked').val();

					if ($("#name").val() == 'Name') { 
						$(".my_chek_name").show().delay(2000).fadeOut();
						event.preventDefault();
					}

					var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
					if(pattern.test($("#email").val())){
						$(this).css({'border' : '1px solid #569b44'});
					} else {
						$(".my_chek_email").show().delay(2000).fadeOut();
						event.preventDefault();
					}

					if (!$('input[name="times[]"]').is(":checked")) {
						$(".my_chek_chebox").show().delay(2000).fadeOut();
						event.preventDefault();
					}

					var hashlink = "<?=$client["hashlink"]?>";
					var clientId = "<?=$clientId?>";
					var name = $("#name").val();
					var email = $("#email").val();
					var description = "[" + hashlink + "] \"" + (clientName.length > 25 ? clientName.substr(0,25)+"…" :  clientName) +"\"";

					var times = [];
					jQuery('input[name="times[]"]:checked').each(function() {
						times.push($(this).val());
					});
					
					var times_info_block_text = $('.times_info_block .text-info').text().replace(/\s+/g,'');
					
					var custom = {
						h: hashlink,
						id: clientId,
						name: name,
						email: email,
						times: times
					};

					description += " ::: on " + times_info_block_text;
					description += " by " + name + " ("+email+")";

					$('#ctm_name').val(description);
					$('#ctm_email').val(JSON.stringify(custom));

					// event.preventDefault();

				}
			});
		});
	</script>

	<script src="https://checkout.stripe.com/checkout.js"></script>


	<script>
        var pp = $(".price").data('price');

		var handler = StripeCheckout.configure({
			key: '<?=$config['stripe_public_token']?>',
			image: '/images/logo-2.png',
			locale: 'auto',
			token: function(token) {
				var dateId = $('input[name="date"]:checked').val();
				var times = [];
				jQuery('input[name="times[]"]:checked').each(function() {
					times.push($(this).val());
				});

				var name = $("#name").val();
				var hashlink = "<?=$client["hashlink"]?>";
				var clientId = "<?=$clientId?>";
				var description = "[" + hashlink + "] \"" + (clientName.length > 25 ? clientName.substr(0,25)+"…" :  clientName) +"\"";

				var times_info_block_text = $('.times_info_block .text-info').text().replace(/\s+/g,'');

				description += " ::: on " + times_info_block_text;
				description += " by " + name + " ("+token.email+")";

				var amount = pp * times.length;
				if ( amount == 0 ) {
					amount = pp;
				}
				amount = Math.round(amount * 1000) / 10; // convert to rounded cents (1 -> 100)

				$.ajax({
					method: 'get',
					url: "/stripe_pay.php",
					data: {
							token: token.id, 
							amount: amount,
							currency:"<?=$config['stripe_currency']?>",
							email: token.email,
							description: description,
							name: name,
							times: times,
							clientId: clientId,
						},
					success: function(data){
						if(JSON.parse(data).success == true){
							window.location.replace('thanks.php?hash='+hashlink);
						} else if ( JSON.parse(data).message ) {
							$(".my_chek_payment").text(JSON.parse(data).message).show().delay(5000).fadeOut();
							// do something if not success
						} else {
							$(".my_chek_payment").text('Oops! Payment error').show().delay(5000).fadeOut();
						}
					}
				});
				// You can access the token ID with `token.id`.
				// Get the token ID to your server-side code for use.
			}
		});

		document.getElementById('stripeButton').addEventListener('click', function(e) {
			if ( $(".price").text() !== '' ) {
				var error = false;
				var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
				if(pattern.test($("#email").val())){
					$(this).css({'border' : '1px solid #569b44'});
				} else {
					error = true;
					$(".my_chek_email").show().delay(2000).fadeOut();
					e.preventDefault();
				}

				var dateId = $('input[name="date"]:checked').val();
				if (!$('input[name="times[]"]').is(":checked")) {
					error = true;
					$(".my_chek_chebox").show().delay(2000).fadeOut();
					e.preventDefault();
				}

				var email = $("#email").val();

				var umnog = $('.timesCont input[type=checkbox]:checked').length;

				var amount = pp * umnog;
				if ( amount == 0 ) {
					amount = pp;
				}
				amount = Math.round(amount * 1000) / 10; // convert to rounded cents (1 -> 100)

				if(!error) {
					// Open Checkout with further options:
					handler.open({
						name: "<?=$config['stripe_email']?>",
						description: '',
						amount: amount,
						currency: "<?=$config['stripe_currency']?>",
						email: email
					});
					e.preventDefault();

				}
			}		
		});

		// Close Checkout on page navigation:
		window.addEventListener('popstate', function() {
			handler.close();
		});
	</script>
<?php } ?>
</body>
</html>