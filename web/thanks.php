<?php

/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * thanks_tpl.php
 * Show Thanks Page
 */
error_reporting(-1);       // Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";       // Defines the ROOT constant
require ROOT."/inc/_include.php";

$reserve_more_link = '';
if ( isset($_REQUEST['hash']) ) {
  $reserve_more_link = "<h4><a href=\"/".$_REQUEST['hash']."\">Zurück</a></h4>";
}

?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Neckattack</title>
    <link href="bootstrap/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="bootstrap/vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
    <link href="bootstrap/css/stylish-portfolio.css" rel="stylesheet">
  </head>

<!-- Header -->
    <header class="masthead d-flex">
      <div class="container text-center my-auto">
        <div>
        <a href="http://termine.neckattack.net">
          <img class="logo" src="bootstrap/img/logo.png">
        </a>
        </div>
      </div>
      <div class="overlay"></div>
    </header>

  <body id="page-top">

  <div class="col-md-12">
      <div style="padding-top: 50px;" class="text-center">
          <h1>Danke für Ihre Buchung!</h1>
          <h3>Eine Bestätigung per eMail haben wir Ihnen soeben zugeschickt</h3>
          <?= $reserve_more_link ?>
    </div>
  </div>

    <section class="container text-center content-block">
      <div class="row">
        <div class="col-md-12" style="padding-top: 90px;">  
          <div id="footer">
            <p>© NeckAttack® Mobile Massage | <a rel="external" href="http://www.neckattack.net/kontakt/impressum/" target="_blank">Impressum</a></p>
          </div>
        </div>
      </div>
    </section> 

</body>
</html>