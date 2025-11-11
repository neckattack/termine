<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */

/**
 * landingpage.tpl.php
 * Default page for index.php
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */

// Header
// echo "<pre>";
// print_r($client);
// echo "</pre>";

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
		.lang-switcher select {
			padding: 3px;
			width: 100px;
			border-radius: 5px;
		}
	</style>
</head>

<body id="page-top">

	<?php include_once __DIR__ . '/../inc/language-switcher.php'; ?>
	<!-- Header -->
	<header class="masthead d-flex">
		<div class="container text-center my-auto">
			<div class="header-main d-flex justify-content-center align-items-center">
				<img class="logo" alt="<?php echo $title; ?>" src="/bootstrap/img/logo.png">
				<?php language_switcher(); ?>
			</div>
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
							<img src="/get_group_logo.php?id=<?= $client['id'] ?>&type=client"
								alt="Logo for <?php echo $title; ?>"
								style="margin: 9px 0 9px 25px; float: right; width: auto; height: 130px; border: 2px solid #ccc; padding: 5px; background: #fff;" />
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
		<?php $price = ($client["price"]) ? floatval(str_replace(',', '.', $client['price'])) : 0;
		if ($price == 0 || empty($price)) { ?>
			<form id="register" method="post" action="<?= $_SERVER["REQUEST_URI"] ?>">
			<?php } ?>

			<div class="row">
				<div class="col-md-12">
					<!-- Error Message(s) -->
					<div style="display:none;" id="errorMsg" <?= ($error > 0) ? 'style="display: block;"' : '' ?>>
						<ul>
							<?php if ($error > 0) { ?>
								<?php foreach ($messages as $msg) { ?>
									<li><?= $msg ?></strong></li>
								<?php } ?>
							<?php } else { ?>
								<li><?php echo __t('check_time_message'); ?></li>
							<?php } ?>
						</ul>
					</div>

					<p id="toolate" <?= ($toolate === true) ? 'style="display: block;"' : '' ?>>
						<?= __t('toolate_message'); ?>
					</p>
					<p id="error">
						<?= __t('error_message'); ?>
					</p>
					<p id="success" <?= ($success === true) ? 'style="display: block;"' : '' ?>>
						<?= __t('success_message'); ?>
					</p>


				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div style="display: none;" class="my_chek_name"><?php echo __t('check_name_message'); ?><br /></div>
					<div style="display: none;" class="my_chek_email"><?php echo __t('check_email_message'); ?><br /></div>
					<div style="display: none;" class="my_chek_chebox"><?php echo __t('check_time_message'); ?><br /></div>
					<div style="display: none;" class="my_chek_payment"></div>
				</div>
				<!-- Error Message(s) -->
			</div>
			<div class="row">
				<div class="col-md-4">
					<div class="fLeft dates">
						<h3><?php echo __t('date_text'); ?></h3>
						<?php if ($result !== false) {
							foreach ($result["dates"] as $key => $date) {
						?>
								<div class="form-check">
									<label for="date_<?= $date["id"] ?>">
										<input type="radio" id="date_<?= $date["id"] ?>" name="date" <?= ((int)$date["id"] === $date_id) ? 'checked="checked"' : "" ?> value="<?= $date["id"] ?>" />
										<span><?= $date["date"] ?></span><sup class="datetimescount"></sup>
									</label>
								</div>
						<?php }
						} ?>
						<noscript>
							<p><input type="submit" name="select_date" value="Anzeigen" /></p>
						</noscript>
					</div>
				</div>

				<div class="col-md-4">
					<div class="fLeft times tni">
						<h3><?php echo __t('select_date'); ?></h3>
						<?php if ($result !== false) {
							foreach ($result["dates"] as $key => $date) {
						?>
								<div id="times_<?= $date["id"] ?>" data-date-id="<?= $date["id"] ?>" class="timesCont" <?= ((int)$date["id"] !== $date_id) ? 'hidden' : '' ?>>
									<?php if (isset($result["times_assoc"][$date["id"]])) {
										foreach ($result["times_assoc"][$date["id"]] as $time) {
											$class1 = (isset($time["taken"])) ? "disabled" : "";
											$class2 = ($success === true && isset($reserved["times"]) && in_array($time["id"], $reserved["times"])) ? " success" : "";
											$class3 = ($toolate === true && isset($taken) && in_array($time["id"], $taken)) ? " toolate" : "";
									?>
											<div class="form-check">
												<label class="<?= $class1 ?><?= $class2 ?><?= $class3 ?>" for="time_<?= $time["id"] ?>">
													<?php
$deadline = isset($client['booking_deadline_hours']) ? (int)$client['booking_deadline_hours'] : 0;
$rawDate = $date['date'];
$rawTime = $time['time_start'];
// Versuche, lokales Datumsformat (z.B. d.m.Y) robust zu parsen
$dt = DateTime::createFromFormat('d.m.Y H:i:s', $rawDate.' '.$rawTime);
if (!$dt) { $dt = DateTime::createFromFormat('d.m.Y H:i', $rawDate.' '.substr($rawTime,0,5)); }
if (!$dt) { $dt = DateTime::createFromFormat('Y-m-d H:i:s', $rawDate.' '.$rawTime); }
$slotTime = $dt ? $dt->getTimestamp() : strtotime($rawDate.' '.$rawTime);
$now = time();
$buchbar = ($deadline === 0 || ($slotTime - $now) > $deadline * 3600);
// Minimalinvasiver Debug-Kommentar per ?dbg=1
if (isset($_GET['dbg']) && $_GET['dbg'] == '1') {
    echo "<!-- time_id={$time['id']} date={$date['date']} start={$time['time_start']} deadline={$deadline} slotTime={$slotTime} now={$now} buchbar=".($buchbar?'1':'0')." -->";
}

if ($client['one_time_booking']) {
?>
												<input type="radio" id="time_<?= $time["id"] ?>" name="times[]" value="<?= $time["id"] ?>" <?= (isset($time["taken"])) ? " disabled=\"disabled\"" : "" ?> <?= !$buchbar ? "disabled=\"disabled\" title=\"Buchung nur bis $deadline Stunden vor Termin möglich\"" : "" ?> />
											<?php
} else {
?>
												<input type="checkbox" id="time_<?= $time["id"] ?>" name="times[]" value="<?= $time["id"] ?>" <?= (isset($time["taken"])) ? " disabled=\"disabled\"" : "" ?> <?= !$buchbar ? "disabled=\"disabled\" title=\"Buchung nur bis $deadline Stunden vor Termin möglich\"" : "" ?> />
											<?php
}
											?>
													<span><?= $time["time_start"] ?> - <?= $time["time_end"] ?></span>
												</label>
											</div>

									<?php }
									} ?>
								</div>
						<?php }
						} ?>
					</div>
				</div>

				<div class="col-md-4">

					<div class="fRight registerMe">
						<h3><?php echo __t('register_as'); ?></h3>

						<?php

                    if ($price == 0 || empty($price)) { ?>
                        <div class="cnt">
                            <input type="text" id="name" name="name" value="<?= (isset($P["name"])) ? $P["name"] : "Name" ?>" placeholder="Name" /><br />
                        </div>
                        <div class="cnt">
                            <input type="text" id="email" name="email" value="<?= (isset($P["email"])) ? $P["email"] : "E-Mail" ?>" placeholder="E-Mail" /><br />
                        </div>
                        <div id="patient-login-box" class="form-group text-left" style="display:none; margin-top:6px;">
                            <label style="display:block; font-weight:600;">Anmeldung</label>
                            <input type="password" id="patient-login-password" class="form-control" placeholder="Passwort" style="max-width:260px; margin-bottom:6px;" />
                            <button type="button" id="patient-login-btn" class="btn btn-secondary" style="padding:4px 10px;">Anmelden</button>
                            <a href="#" id="patient-reset-link" style="margin-left:10px;">Passwort vergessen?</a>
                            <span id="patient-login-msg" style="display:inline-block; margin-left:8px; color:#a00;"></span>
                            <div id="patient-reset-box" style="display:none; margin-top:8px;">
                                <button type="button" id="patient-send-reset" class="btn btn-light" style="padding:4px 10px;">Reset-Code senden</button>
                                <input type="text" id="patient-reset-code" class="form-control" placeholder="Reset-Code" style="max-width:160px; display:inline-block; margin:6px 6px 6px 0;" />
                                <input type="password" id="patient-new-password" class="form-control" placeholder="Neues Passwort" style="max-width:220px; display:inline-block; margin:6px 6px 6px 0;" />
                                <button type="button" id="patient-set-password" class="btn btn-secondary" style="padding:4px 10px;">Passwort setzen</button>
                                <span id="patient-reset-msg" style="display:inline-block; margin-left:8px; color:#0a0;"></span>
                            </div>
                        </div>
                        <?php if (!empty($client['patient_billing_required'])) { ?>
                            <div id="billing-fields" class="form-group text-left" style="margin-top:6px;">
                                <label style="display:block; font-weight:600;">Rechnungsadresse</label>
                                <div class="cnt"><input type="text" name="street" value="<?= isset($P['street'])?htmlspecialchars($P['street']):'' ?>" placeholder="Straße" /></div>
                                <div class="cnt"><input type="text" name="house_no" value="<?= isset($P['house_no'])?htmlspecialchars($P['house_no']):'' ?>" placeholder="Hausnummer" /></div>
                                <div class="cnt"><input type="text" name="zip" value="<?= isset($P['zip'])?htmlspecialchars($P['zip']):'' ?>" placeholder="PLZ" /></div>
                                <div class="cnt"><input type="text" name="city" value="<?= isset($P['city'])?htmlspecialchars($P['city']):'' ?>" placeholder="Stadt" /></div>
                                <div class="cnt"><input type="text" name="birthdate" value="<?= isset($P['birthdate'])?htmlspecialchars($P['birthdate']):'' ?>" placeholder="Geburtsdatum (TT.MM.JJJJ)" /></div>
                            </div>
                        <?php } ?>

                        <div class="form-group text-left times_info_block">
                            <div class="text-warning"><?php echo __t('check_time_message'); ?></div>
                            <div class="text-info"></div>
                        </div>

                        <input class="btn btn-warning" style="float: right" type="submit" id="submit" name="submit" value="<?= __t('sign_in_button'); ?>" />

                    <?php } else { ?>

                        <div class="form-group">
                            <input type="text" id="name" class="form-control" value="<?= (isset($P["name"])) ? $P["name"] : "Name" ?>" placeholder="Name" />
                        </div>

                        <div class="form-group">
                            <input type="text" id="email" class="form-control" value="<?= (isset($P["email"])) ? $P["email"] : "E-Mail" ?>" placeholder="E-Mail" />
                        </div>
                        <div id="patient-login-box-paid" class="form-group text-left" style="display:none; margin-top:6px;">
                            <label style="display:block; font-weight:600;">Anmeldung</label>
                            <input type="password" id="patient-login-password-paid" class="form-control" placeholder="Passwort" style="max-width:260px; margin-bottom:6px;" />
                            <button type="button" id="patient-login-btn-paid" class="btn btn-secondary" style="padding:4px 10px;">Anmelden</button>
                            <a href="#" id="patient-reset-link-paid" style="margin-left:10px;">Passwort vergessen?</a>
                            <span id="patient-login-msg-paid" style="display:inline-block; margin-left:8px; color:#a00;"></span>
                            <div id="patient-reset-box-paid" style="display:none; margin-top:8px;">
                                <button type="button" id="patient-send-reset-paid" class="btn btn-light" style="padding:4px 10px;">Reset-Code senden</button>
                                <input type="text" id="patient-reset-code-paid" class="form-control" placeholder="Reset-Code" style="max-width:160px; display:inline-block; margin:6px 6px 6px 0;" />
                                <input type="password" id="patient-new-password-paid" class="form-control" placeholder="Neues Passwort" style="max-width:220px; display:inline-block; margin:6px 6px 6px 0;" />
                                <button type="button" id="patient-set-password-paid" class="btn btn-secondary" style="padding:4px 10px;">Passwort setzen</button>
                                <span id="patient-reset-msg-paid" style="display:inline-block; margin-left:8px; color:#0a0;"></span>
                            </div>
                        </div>
                        <?php if (!empty($client['patient_billing_required'])) { ?>
                            <div id="billing-fields-paid" class="form-group text-left" style="margin-top:6px;">
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
                            <div class="text-warning"><?php echo __t('check_time_message'); ?></div>
                            <div class="text-info"></div>
                        </div>

                        <div class="form-group text-left small">
                            <?= __t('privacy_policy_confirmation'); ?>
                        </div>

							<div class="form-group text-left small">
								<?= __t('privacy_policy_confirmation'); ?>
							</div>

							<div class="form-group clearfix" style="position: relative;">

								<!--
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
											<input type="hidden" name="return" value="<?= ABSURL . 'thanks.php?hash=' . $client["hashlink"] ?>" />
											<input type="hidden" name="cancel_return" value="<?= ABSURL . $client["hashlink"] ?>" />
											<input type="hidden" name="notify_url" value="<?php echo ABSURL . 'pay.php'; ?>" />
											<input type="hidden" name="item_number" value="<?php echo $client["hashlink"]; ?>">
											<input type="submit" hidden="hidden" name="submit" value="PayPal" />
											<button class="btn btn-warning" type="submit" id="submit"><i class="fa fa-paypal"></i> &nbsp; PayPal</button>
										</form>
									</div>
									<div class="pay-list__button-wrapper">
										<button type="button" id="stripeButton" class="btn btn-warning"><i class="fa fa-credit-card"></i> &nbsp; Kreditkarte</button>
									</div>
								</div>
                                -->

								<div id="smart-button-container">
									<div style="text-align: center;">
										<div id="paypal-button-container"></div>
									</div>
								</div>
								<script src="https://www.paypal.com/sdk/js?client-id=<?= $config['paypal_client_id'] ?>&enable-funding=venmo&currency=<?= $config['paypal_currency'] ?>" data-sdk-integration-source="button-factory"></script>
								<script>
									function initPayPalButton() {

										var form_data = {};

										paypal.Buttons({
											style: {
												shape: 'rect',
												color: 'gold',
												layout: 'vertical',
												label: 'paypal',
											},

											createOrder: function(data, actions) {

												console.log('Create Order');

												var pp = $(".price").data('price');

												var times = [];
												jQuery('input[name="times[]"]:checked').each(function() {
													times.push($(this).val());
												});

												var amount = pp * times.length;
												if (amount == 0) {
													amount = pp;
												}
												amount = Math.round(amount * 1000) / 1000;

												console.log('Amount: ', amount);

												var clientName = "<?= $title ?>";
												var hashlink = "<?= $client["hashlink"] ?>";
												var name = $("#name").val();
												var email = $("#email").val();

												var description = "[" + hashlink + "] \"" + (clientName.length > 25 ? clientName.substr(0, 25) + "…" : clientName) + "\"";
												var times_info_block_text = $('.times_info_block .text-info').text().replace(/\s+/g, '');

												description += " ::: on " + times_info_block_text;
												description += " by " + name + " (" + email + ")";

												var payment_data = {
													"description": description,
													"amount": {
														"currency_code": "<?= $config['paypal_currency'] ?>",
														"value": amount
													}
												};

												return actions.order.create({
													purchase_units: [payment_data]
												});

											},

											onApprove: function(data, actions) {
												return actions.order.capture().then(function(orderData) {
													// Full available details
													console.log('Capture result', orderData, JSON.stringify(orderData, null, 2));

													// Show a success message within this page, e.g.
													const element = document.getElementById('paypal-button-container');
													element.innerHTML = "";
													element.innerHTML = "<h3>Thank you for your payment!</h3>";

													var hashlink = "<?= $client["hashlink"] ?>";
													var clientId = "<?= $clientId ?>";
													var itemId = "";
													var name = $("#name").val();
													var email = $("#email").val();

													var times = [];
													jQuery('input[name="times[]"]:checked').each(function() {
														times.push($(this).val());
													});

													var pp = $(".price").data('price');

													var amount = pp * times.length;
													if (amount == 0) {
														amount = pp;
													}
													amount = Math.round(amount * 1000) / 1000;

													var custom = {
														h: hashlink,
														id: clientId,
														name: name,
														email: email,
														times: times
													};

													console.log('custom', custom);

													var data = {
														custom: JSON.stringify(custom),
														mc_gross: amount,
														mc_currency: "<?= $config['paypal_currency']; ?>",
														payment_status: orderData.status,
														item_number: hashlink,
													};

													console.log('data', data);

													console.log('Go ajaxL');

													jQuery.post('/pay.php', data, function(response) {
														console.log(response);

														jQuery('input[name="times[]"]:checked').each(function() {
															jQuery(this).prop('checked', false);
															jQuery(this).attr('checked', false);
															jQuery(this).attr('disabled', true);
															jQuery(this).parent().addClass('disabled');
														});


													});

												});
											},

											onError: function(err) {
												console.log(err);
											}

										}).render('#paypal-button-container');
									}
									initPayPalButton();
								</script>



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

								<div class="pp_price">
									<div class="nki">€</div>
									<div class="price" data-price="<?= $price ?>"><?= number_format($price, 2, ',', ''); ?></div>
								</div>
							</div>
						<?php } ?>
						<input type="hidden" name="h" value="<?= $client["hashlink"] ?>" />
						<input type="hidden" name="id" value="<?= $clientId ?>" />
					</div>
				</div>

			</div>

			<?php if ($price == 0 || empty($price)) { ?>
			</form> <?php } ?>

		<div class="row">
			<div class="col-md-12">
				<div class="text-center footer">
					<p><?= __t('footer_copyright'); ?></p>
				</div>
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

	<script type="text/javascript">
		window.translations = <?php echo json_encode($lang); ?>;
	</script>

	<script>
		var clientName = "<?= $title ?>";
		var gOptions = {
			dateFormat: "dd.mm.yy"
		};
		var pp = <?= $price ?>;
		var billingRequired = <?= !empty($client['patient_billing_required']) ? 'true' : 'false' ?>;
	</script>
	<script src="/js/page.js?ver=<?= time() ?>"></script>
	<script>
	(function(){
		function showExistingHint(state){
			var box = document.getElementById('existing-patient-hint');
			if (!box) return;
			box.style.display = state ? 'block' : 'none';
		}
		function ensureHintBox(){
			if (document.getElementById('existing-patient-hint')) return;
			var parent = document.querySelector('.registerMe');
			if (!parent) return;
			var div = document.createElement('div');
			div.id = 'existing-patient-hint';
			div.className = 'alert alert-info';
			div.style.display = 'none';
			div.style.marginTop = '6px';
			div.innerHTML = 'Diese E-Mail gehört bereits zu einem Patienten. <a href="#" id="show-login-link">Jetzt anmelden</a>, um deine gespeicherten Daten zu verwenden.';
			parent.insertBefore(div, parent.firstChild);
		}
		document.addEventListener('DOMContentLoaded', function(){
			if (!window.billingRequired) { return; }
			ensureHintBox();
			var email = document.getElementById('email');
			if (!email) return;
			function toggleLoginBox(show){
				var free = document.getElementById('patient-login-box');
				var paid = document.getElementById('patient-login-box-paid');
				if (free) free.style.display = show ? 'block' : 'none';
				if (paid) paid.style.display = show ? 'block' : 'none';
				var bf1 = document.getElementById('billing-fields');
				var bf2 = document.getElementById('billing-fields-paid');
				if (bf1) bf1.style.display = show ? 'none' : 'block';
				if (bf2) bf2.style.display = show ? 'none' : 'block';
				// Hide extra UI while login is active
				var submitBtn = document.getElementById('submit');
				if (submitBtn) submitBtn.style.display = show ? 'none' : '';
				var tib = document.querySelectorAll('.times_info_block');
				for (var i=0;i<tib.length;i++){ tib[i].style.display = show ? 'none' : ''; }
				var chkWarn = document.querySelectorAll('.my_chek_chebox');
				for (var j=0;j<chkWarn.length;j++){ chkWarn[j].style.display = show ? 'none' : 'none'; }
			}
			email.addEventListener('blur', function(){
				var val = (email.value||'').trim();
				if (val.length < 3) { showExistingHint(false); return; }
				var xhr = new XMLHttpRequest();
				xhr.open('GET', '/ajax/patient_exists.php?email='+encodeURIComponent(val));
				xhr.onreadystatechange = function(){
					if (xhr.readyState===4 && xhr.status===200) {
						try {
							var resp = JSON.parse(xhr.responseText||'{}');
							showExistingHint(!!resp.exists);
							if (resp.exists) {
								var l = document.getElementById('show-login-link');
								if (l) {
									l.onclick = function(ev){ ev.preventDefault(); toggleLoginBox(true); };
								}
							}
						} catch(e){ showExistingHint(false); }
					}
				};
				xhr.send(null);
			});

			function doLogin(isPaid){
				var pwd = document.getElementById(isPaid?'patient-login-password-paid':'patient-login-password');
				var msg = document.getElementById(isPaid?'patient-login-msg-paid':'patient-login-msg');
				var eml = (email.value||'').trim();
				if (!pwd || !msg || !eml) return;
				msg.textContent = '';
				var xhr = new XMLHttpRequest();
				xhr.open('POST','/ajax/patient_login.php');
				xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
				xhr.onreadystatechange = function(){
					if (xhr.readyState===4) {
						try {
							var resp = JSON.parse(xhr.responseText||'{}');
							if (resp.ok) {
								// Prefill Felder und sperren Billing-Felder
								var p = resp.patient||{};
								function setVal(name, v){ var el = document.querySelector('[name="'+name+'"]'); if(el){ el.value = v||''; } }
								setVal('street', p.street);
								setVal('house_no', p.house_no);
								setVal('zip', p.zip);
								setVal('city', p.city);
								setVal('birthdate', p.birthdate && p.birthdate.indexOf('-')>0 ? (function(d){ var a=d.split('-'); return a[2]+'.'+a[1]+'.'+a[0]; })(p.birthdate): p.birthdate);
								toggleLoginBox(false);
								showExistingHint(false);
							} else {
								msg.textContent = 'Login fehlgeschlagen';
							}
						} catch(e){ msg.textContent = 'Login-Fehler'; }
					}
				};
				xhr.send('email='+encodeURIComponent(eml)+'&password='+encodeURIComponent(pwd.value||''));
			}
			var btnFree = document.getElementById('patient-login-btn');
			if (btnFree) btnFree.addEventListener('click', function(){ if (!window.billingRequired) return; doLogin(false); });
			var btnPaid = document.getElementById('patient-login-btn-paid');
			if (btnPaid) btnPaid.addEventListener('click', function(){ if (!window.billingRequired) return; doLogin(true); });

			// Reset password flows
			function setupReset(isPaid){
				var link = document.getElementById(isPaid?'patient-reset-link-paid':'patient-reset-link');
				var box  = document.getElementById(isPaid?'patient-reset-box-paid':'patient-reset-box');
				var send = document.getElementById(isPaid?'patient-send-reset-paid':'patient-send-reset');
				var code = document.getElementById(isPaid?'patient-reset-code-paid':'patient-reset-code');
				var npw  = document.getElementById(isPaid?'patient-new-password-paid':'patient-new-password');
				var set  = document.getElementById(isPaid?'patient-set-password-paid':'patient-set-password');
				var msg  = document.getElementById(isPaid?'patient-reset-msg-paid':'patient-reset-msg');
				if (link) link.addEventListener('click', function(e){ e.preventDefault(); if (box) { box.style.display = box.style.display==='none'?'block':'none'; } });
				if (send) send.addEventListener('click', function(){
					var eml = (email.value||'').trim(); if (!eml) return;
					var xhr = new XMLHttpRequest(); xhr.open('POST','/ajax/patient_send_reset.php');
					xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
					xhr.onreadystatechange = function(){ if (xhr.readyState===4){ try{ var r=JSON.parse(xhr.responseText||'{}'); msg.textContent = r.ok?'Code gesendet. Bitte E-Mail prüfen.':'Senden fehlgeschlagen'; }catch(e){ msg.textContent='Fehler beim Senden'; } } };
					xhr.send('email='+encodeURIComponent(eml));
				});
				if (set) set.addEventListener('click', function(){
					var eml = (email.value||'').trim(); var c=(code&&code.value)||''; var p=(npw&&npw.value)||''; if (!eml||!c||!p) return;
					var xhr = new XMLHttpRequest(); xhr.open('POST','/ajax/patient_set_password.php');
					xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
					xhr.onreadystatechange = function(){ if (xhr.readyState===4){ try{ var r=JSON.parse(xhr.responseText||'{}'); if (r.ok){ msg.textContent='Passwort gesetzt'; // auto login
						var pwdEl = document.getElementById(isPaid?'patient-login-password-paid':'patient-login-password');
						if (pwdEl){ pwdEl.value = p; }
						doLogin(!!isPaid);
					}else{ msg.textContent='Setzen fehlgeschlagen'; } }catch(e){ msg.textContent='Fehler beim Setzen'; } } };
					xhr.send('email='+encodeURIComponent(eml)+'&code='+encodeURIComponent(c)+'&password='+encodeURIComponent(p));
				});
			}
			setupReset(false);
			setupReset(true);
		});
	})();
	</script>

	<?php if ($price != 0 && !empty($price)) { ?>
		<script>
			jQuery(document).ready(function($) {

				$("#submit").on('click', function(event) {
					if ($(".price").text() !== '') {

						var dateId = $('input[name="date"]:checked').val();

						if ($("#name").val() == 'Name') {
							$(".my_chek_name").show().delay(2000).fadeOut();
							event.preventDefault();
						}

						var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
						if (pattern.test($("#email").val())) {
							$(this).css({
								'border': '1px solid #569b44'
							});
						} else {
							$(".my_chek_email").show().delay(2000).fadeOut();
							event.preventDefault();
						}

						if (!$('input[name="times[]"]').is(":checked")) {
							$(".my_chek_chebox").show().delay(2000).fadeOut();
							event.preventDefault();
						}

						var hashlink = "<?= $client["hashlink"] ?>";
						var clientId = "<?= $clientId ?>";
						var name = $("#name").val();
						var email = $("#email").val();


						var times = [];
						jQuery('input[name="times[]"]:checked').each(function() {
							times.push($(this).val());
						});

						var times_info_block_text = $('.times_info_block .text-info').text().replace(/\s+/g, '');

						var custom = {
							h: hashlink,
							id: clientId,
							name: name,
							email: email,
							times: times
						};

						var description = "[" + hashlink + "] \"" + (clientName.length > 25 ? clientName.substr(0, 25) + "…" : clientName) + "\"";

						description += " ::: on " + times_info_block_text;
						description += " by " + name + " (" + email + ")";

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
				key: '<?= $config['stripe_public_token'] ?>',
				image: '/images/logo-2.png',
				locale: 'auto',
				token: function(token) {
					var dateId = $('input[name="date"]:checked').val();
					var times = [];
					jQuery('input[name="times[]"]:checked').each(function() {
						times.push($(this).val());
					});

					var name = $("#name").val();
					var hashlink = "<?= $client["hashlink"] ?>";
					var clientId = "<?= $clientId ?>";
					var description = "[" + hashlink + "] \"" + (clientName.length > 25 ? clientName.substr(0, 25) + "…" : clientName) + "\"";

					var times_info_block_text = $('.times_info_block .text-info').text().replace(/\s+/g, '');

					description += " ::: on " + times_info_block_text;
					description += " by " + name + " (" + token.email + ")";

					var amount = pp * times.length;
					if (amount == 0) {
						amount = pp;
					}
					amount = Math.round(amount * 1000) / 10; // convert to rounded cents (1 -> 100)

					$.ajax({
						method: 'get',
						url: "/stripe_pay.php",
						data: {
							token: token.id,
							amount: amount,
							currency: "<?= $config['stripe_currency'] ?>",
							email: token.email,
							description: description,
							name: name,
							times: times,
							clientId: clientId,
						},
						success: function(data) {
							if (JSON.parse(data).success == true) {
								window.location.replace('thanks.php?hash=' + hashlink);
							} else if (JSON.parse(data).message) {
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
				if ($(".price").text() !== '') {
					var error = false;
					var pattern = /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i;
					if (pattern.test($("#email").val())) {
						$(this).css({
							'border': '1px solid #569b44'
						});
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
					if (amount == 0) {
						amount = pp;
					}
					amount = Math.round(amount * 1000) / 10; // convert to rounded cents (1 -> 100)

					if (!error) {
						// Open Checkout with further options:
						handler.open({
							name: "<?= $config['stripe_email'] ?>",
							description: '',
							amount: amount,
							currency: "<?= $config['stripe_currency'] ?>",
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