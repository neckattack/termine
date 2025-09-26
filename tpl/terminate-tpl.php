<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * landingpage.tpl.php
 * Default page for index.php
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */
?>

<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hier können Sie Ihre Buchung verwalten</title>

    <link href="/bootstrap/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bootstrap/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="/bootstrap/vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
    <link href="/bootstrap/css/stylish-portfolio.css" rel="stylesheet">
    <style>

      .disabled span {
        color: #dee2e6;
      }
	  
	  .pay-dropdown {
		position: absolute; 
		right: 0; 
		bottom: 10px; 
		padding: 20px 0 10px 0;
		transform: translateY(100%);
		display: flex;
		flex-direction: column;
		display: none;
		z-index: 2;
		width: 129px;
	  }

	  .modal-dialog {
		margin-top: 50vh;
    	transform: translateY(-50%) !important;
	  }

	  .pay-list {
		  float: right;
	  }

	  .pay-list ul {
		padding-right: .5rem;
		padding-left: .5rem;
	  }

	  .pay-list li {
		margin-bottom: .5rem
	  }

	  .pay-list li:last-child {
		  margin-bottom: 0;
	  }

	  .pay-list__button {
		  text-align: left;
		  background: none;
		  width: 100%;
		  box-shadow: none;
	  }

	  .stripe-button-el {
		  text-align: left;
		  background: none;
		  width: 100%;
		  box-shadow: none;
	  }

	  .pay-list__button:hover {
		  color: #d39e00;
	  }

	  .dropdown-menu {
		min-width: 146px;
	  }
    </style>
  </head>
  <body id="page-top">

    <!-- Header -->
    <header class="masthead d-flex">
      <div class="container text-center my-auto">
        <div><img class="logo" alt="Neckattack logo" src="/bootstrap/img/logo.png"></div>
      </div>
      <div class="overlay"></div>
    </header>

    <?php if ( $reservations ) { ?>

	<?php 
		// Header
		$title  = $client["name"];
		$gText  = $client["greeting_text"];
	?>

	<div class="col-md-12">
      	<div style="padding-bottom: 30px;" class="text-center">
      		<h2 class="text-info">Willkommen zurück <?= $reservations[0]['name'] ?>!</h2>
          	<h5>Hier können Sie Ihre Buchung verwalten</h5>
	    </div>
  	</div>

    <section class="elow">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
				    <div class="col-md-12">
				    <?php
				    	if (!empty($client["image"])) { ?>
				    		<img  src="/get_group_logo.php?id=<?=$client['id']?>&type=client" style="margin: 9px;margin-right: 25px; float: left;width: auto;height: 130px;"/>
				    <?php } ?>
				        <h1 class="mb-1"><?php echo $title; ?></h1>
				        <h3 class="mb-5"><pre style="white-space:pre-line;"><em><?php echo $gText; ?></em></pre></h3>
				    </div>
				</div>
			</div>
		</div>
    </section>

    <!-- Content -->
    <section class="container text-center content-block">

            <!-- Tag: stornovorlauf – Überschrift und Untertitel für bessere UX -->
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center">Terminverschiebung</h2>
                    <div class="text-muted" style="margin-top:4px;">Bitte einen neuen Termin aussuchen</div>
                </div>
            </div>
            <br>

            <div class="row">
                <div class="col-md-12 text-info">
					<strong>Alter Termin: <?php echo date('d.m.Y', strtotime($selected_reservation[0]['date'])) . ': (' . $selected_reservation[0]['time_start'] . '-' . $selected_reservation[0]['time_end'] . ')' ?></strong>
				</div>
			</div>
            <br>
            <br>
        <div class="row">
		<?php $price = ($client["price"]) ? floatval(str_replace(',','.',$client['price'])) : 0; ?>
		
		<div class="col-md-12">
			<form method="post" action="<?=$_SERVER["REQUEST_URI"]?>" class="my-form">
			
			<p id="toolate" <?=($toolate === true) ? 'style="display: block;"': ''?>>Leider sind einige Ihrer gewünschten Termine inzwischen bereits vergeben. Bitte überprüfen Sie Ihre Auswahl.</p>
			<p id="error" <?=($error === true) ? 'style="display: block;"': ''?>>Leider ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut!</p>
			<p id="success" <?=($success === true) ? 'style="display: block;"': ''?>>Sie haben sich erfolgreich für die markierten Termine eingetragen.</p>

          	<div class="col-md-4">
				<div class="fLeft dates">
					<h3>Datum:</h3>
					<?php if ($result !== false) {
						foreach ($result["dates"] AS $key => $date) {
					?>
					<div class="form-check my-date">
						<label for="date_<?=$date["id"]?>">
							<input type="radio" id="date_<?=$date["id"]?>" name="date" value="<?=$date["id"]?>" />
							<span><?=$date["date"]?></span><sup class="datetimescount"></sup>
						</label>
					</div>
					<?php } } ?>
					<noscript><p><input type="submit" name="select_date" value="Anzeigen" /></p></noscript>
				</div>
          	</div>

          	<div class="col-md-4">
				<!-- Tag: stornovorlauf – Infozeile ausgeblendet (Neues Datum wählen) -->
                <h5 class='select-a-date' style="display:none;"></h5>
				<div class="fLeft times tni my-times" style="<?= ((int)$date_id>0)?'':'display:none;' ?>">
					<h3>Neuen Termin auswählen:</h3>
					<?php if ($result !== false) {
						foreach ($result["dates"] AS $key => $date) {
					?>
					<div id="times_<?=$date["id"]?>" data-date-id="<?=$date["id"]?>" class="timesCont" <?= ((int)$date["id"] !== $date_id) ? 'hidden' : '' ?>>
						<?php if (isset($result["times_assoc"][$date["id"]])) {
							foreach ($result["times_assoc"][$date["id"]] AS $time) {
                                $class1 = (isset($time["taken"])) ? "disabled" : "";
                                $class2 = "";
                                $disabledText = ($class1) ? " disabled=\"disabled\"" : "";
                                // Tag: stornovorlauf – Fristprüfung pro Slot
                                $deadline = isset($client['booking_deadline_hours']) ? (int)$client['booking_deadline_hours'] : 0;
                                $rawDate = $date['date'];
                                $rawTime = $time['time_start'];
                                $dt = DateTime::createFromFormat('d.m.Y H:i:s', $rawDate.' '.$rawTime);
                                if (!$dt) { $dt = DateTime::createFromFormat('d.m.Y H:i', $rawDate.' '.substr($rawTime,0,5)); }
                                if (!$dt) { $dt = DateTime::createFromFormat('Y-m-d H:i:s', $rawDate.' '.$rawTime); }
                                $slotTime = $dt ? $dt->getTimestamp() : strtotime($rawDate.' '.$rawTime);
                                $now = time();
                                $buchbar = ($deadline === 0 || ($slotTime - $now) > $deadline * 3600);
                                if (!$buchbar) {
                                    $disabledText = ' disabled="disabled" title="Buchung nur bis '.$deadline.' Stunden vor Termin möglich"';
                                }
                                if (isset($_GET['dbg']) && $_GET['dbg']=='1') {
                                    echo "<!-- move_row time_id={$time['id']} date={$rawDate} start={$rawTime} deadline={$deadline} slotTs={$slotTime} nowTs={$now} allow=".($buchbar?'1':'0')." -->";
                                }
                        ?>
                            <div class="form-check">
                                <label class="<?=$class1?>" for="time_<?=$time["id"]?>">
                                    <input type="checkbox" id="time_<?=$time["id"]?>" name="times[]" value="<?=$time["id"]?>" <?= $disabledText ?> />
                                    <span class=""><?=$time["time_start"]?> - <?=$time["time_end"]?></span>
                                </label>
                            </div>

						<?php } } ?>
					</div>
					<?php } } ?>
				</div>
      		</div>

         	<div class="col-md-4">

                <div class="fRight registerMe">
					<h3>Ihre Buchung:</h3>

						<div class="form-group text-left times_info_block">
							<div class="text-warning" style="display: none;">Alles abbrechen?</div>
							<div class="text-info"></div>
							<div class="text-danger not-more-than-one" style="display: none;">Sie können nicht mehr als <?= count($reservations_times) ?> auswählen.</div>
							<div class="text-danger select-one-timeslot" style="display: none;">Sie müssen ein Zeitfenster auswählen.</div>
							
						</div>

						                        <div class="form-group text-right">
                            <!-- Tag: stornovorlauf – nativer Submit für zuverlässige Übermittlung -->
                            <button class="btn btn-warning submit-form" type="submit">Anwenden</button>
                        </div>

                        <!-- Tag: stornovorlauf – Zurück zur Übersicht unter Anwenden platzieren -->
                        <div class="form-group text-right" style="margin-top: 12px;">
                            <a href="<?php echo ABSURL."web/bookings.php?e=".$_REQUEST['e']; ?>" class="btn btn-danger">Zurück zur Übersicht</a>
                        </div>

						<input type="hidden" name="h" value="<?=$client["hashlink"]?>" />
						<input type="hidden" name="id" value="<?=$client["id"]?>" />
				</div>
        	</div>

			</form>
		</div>
		
			<div class="col-md-12">
				<div class="text-center footer"><p>© NeckAttack® Mobile Massage | <a rel="external" href="http://www.neckattack.net/kontakt/impressum/" target="_blank">Impressum</a></p></div>
			</div>
        </div>
	</section>

	<script src="/js/jquery-1.8.3.js"></script>
	<script src="/js/jquery-ui-1.9.2.min.js"></script>
	<script src="/js/ui.checkboxes.js"></script>
	<script src="/js/jquery.validate.min.js"></script>
	<script src="https://unpkg.com/popper.js/dist/umd/popper.min.js"></script>
	<script src="/bootstrap/vendor/bootstrap/js/bootstrap.js"></script>
	

	<script>
		var clientName = "<?= $title ?>";
		var gOptions = {
			dateFormat  : "dd.mm.yy"
		};
		var reservations_max_count = <?= count($reservations_times); ?>;
		var pp = <?= $price ?>;
	</script>
	<script src="/js/page.js"></script>
    <script>
        jQuery(document).ready(function ($) {
            $('.submit-form').on('click', function(e){
                e.preventDefault();
                $('.select-one-timeslot').hide();
                var checkedCount = $('input[name="times[]"]:checked').length;
                console.log('[terminate] apply clicked, checkedCount=', checkedCount);
                if (checkedCount >= 1) {
                    $('.my-form').trigger('submit');
                } else {
                    $('.select-one-timeslot').show();
                }
            });

            $('.my-date').on('click', function() {
                $('.select-a-date').hide();
                $('.my-times').show();
            });
        });
    </script>
	<script>
		jQuery(document).ready(function ($) {
			$('.tni input').trigger('change');

			$(".tni input").click(function(){
				var umnog = $('input[name="times[]"]:checked').length;
				if ( umnog > reservations_max_count ) {
					$('.times_info_block .not-more-than-one').show();
					return false;
				} else {
					$('.times_info_block .not-more-than-one').hide();
				}
			});
		});
	</script>

	<?php } else { ?>
		
	<div class="col-md-12">
      	<div style="padding-top: 50px;" class="text-center">
      		<h1 class="text-danger">Keine Buchungen für Sie</h1>
          	<h3>Überprüfen Sie den Link</h3>
	    </div>
  	</div>
	
	<?php } ?>

</body>
</html>