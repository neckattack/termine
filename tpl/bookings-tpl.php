<?php
/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * landingpage.tpl.php
 * Default page for index.php
 * @param  $title  string  Title to be displayed
 * @param  $gText  string  (optional) Greeting text to be displayed
 */
include_once __DIR__ . '/../inc/language-switcher.php';
?>

<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?= __t('welcome_back'); ?></title>

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
	  .lang-switcher select {
			padding: 3px;
			width: 100px;
			border-radius: 5px;
		}
    </style>
  </head>
  <body id="page-top">

    <!-- Header -->
    <header class="masthead d-flex">
      <div class="container text-center my-auto">
	  	<div class="header-main d-flex justify-content-center align-items-center">
        	<div><img class="logo" alt="Neckattack logo" src="/bootstrap/img/logo.png"></div>
			<?php language_switcher(); ?>
		</div>
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
      		<h2 class="text-info"><?= __t('welcome_back') ?> <?= $reservations[0]['name'] ?>!</h2>
          	<h5><?= __t('manage_your_booking') ?></h5>
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
            <div class="row">
                <div class="col-md-12">
                    <h2 class="text-center"><?= __t('your_bookings') ?></h2>
                        <table class="table table-bordered">
                            <thead>
                            <tr class="text-center">
								<th><?= __t('booking_date') ?></th>
								<th><?= __t('start_time') ?></th>
								<th><?= __t('end_time') ?></th>
								<th><?= __t('actions') ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($reservations as $reservation) {
                                $edit_link = ABSURL."web/terminate.php?e=".md5($reservation['email'])."&t=".$reservation['time_id'];
                                // Tag: stornovorlauf – Fristprüfung pro Eintrag
                                $deadline = isset($client['booking_deadline_hours']) ? (int)$client['booking_deadline_hours'] : 0;
                                $slotTs   = strtotime($reservation['date'].' '.$reservation['time_start']);
                                $nowTs    = time();
                                $allowActions = ($deadline === 0 || ($slotTs - $nowTs) > $deadline * 3600);
                                if (isset($_GET['dbg']) && $_GET['dbg'] == '1') {
                                    echo "<!-- booking_row time_id={$reservation['time_id']} date={$reservation['date']} start={$reservation['time_start']} deadline={$deadline} slotTs={$slotTs} nowTs={$nowTs} allow=".($allowActions?'1':'0')." -->";
                                }
                                ?>
                                <tr>
                                    <td class="text-center"><?= date('d.m.Y', strtotime($reservation['date'])) ?></td>
                                    <td class="text-center"><?= $reservation['time_start'] ?></td>
                                    <td class="text-center"><?= $reservation['time_end'] ?></td>
                                    <td class="text-center">
                                        <a href="<?php echo $edit_link; ?>">
                                            <button class="btn btn-success" <?= $allowActions ? '' : 'disabled title="'.__t('check_time_message').'"' ?>><?= __t('move') ?></button>
                                        </a>
                                        <button onclick="deletereservation(<?= $reservation['id'] ?>)" class="btn btn-danger" <?= $allowActions ? '' : 'disabled title="'.__t('check_time_message').'"' ?>><?= __t('cancel') ?></button>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
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
		// Erfolgspopup nach Verschieben
		(function(){
			var p = new URLSearchParams(window.location.search);
			if (p.get('moved') === '1') {
				alert('Reservierung erfolgreich verschoben');
			}
		})();
	</script>
	<script>
		jQuery(document).ready(function ($) {
			$('.tni input').trigger('change');

			$(".tni input").click(function(){
				var umnog = $('input[name="times[]"]:checked').length;
				if ( umnog > reservations_max_count ) {
					$('.times_info_block .text-danger').show();
					return false;
				} else {
					$('.times_info_block .text-danger').hide();
				}
			});
		});
	</script>

	<script>
		function deletereservation(reservationId) {
			if (confirm('<?= __t('confirm_delete') ?>')) {
				// Send AJAX request to delete reservation
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '/web/admin/ajax/editslots.php', true);
				xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhr.onreadystatechange = function() {
					if (xhr.readyState == 4 && xhr.status == 200) {
						var response = JSON.parse(xhr.responseText);
						console.log(response);
						if (response.success === 1) {
							// Reservation deleted successfully, you can perform any additional actions here if needed
							alert('<?= __t('delete_success') ?>');
							// Reload or update the page as needed
							location.reload(); // Reload the page
						} else {
							// Failed to delete reservation, handle error
							alert('<?= __t('delete_error') ?>');
						}
					}
				};
				xhr.send('action=deletereservation&id=' + reservationId + '&byUser=1');
			}
		}
	</script>

	<?php } else { ?>
		
	<div class="col-md-12">
      	<div style="padding-top: 50px;" class="text-center">
			<h1 class="text-danger"><?= __t('no_bookings_for_you') ?></h1>
			<h3><?= __t('check_the_link') ?></h3>
	    </div>
  	</div>
	
	<?php } ?>

</body>
</html>