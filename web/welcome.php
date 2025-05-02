<?php

/* —— UTF-8: charset=utf-8 —— encoding="utf-8" —— */
/**
 * add.php
 * Add a client
 */

error_reporting(-1);        // Enable PHP error reporting (set to 0 to disable)
$PAGE = basename(__FILE__);
require "_root_.php";       // Defines the ROOT constant
require ROOT."/inc/_include.php";

$R = $_REQUEST;
?>

<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
	<meta name="robots" content="noindex" />
    <title>Neckattack</title>
    <link href="bootstrap/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bootstrap/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700,300italic,400italic,700italic" rel="stylesheet" type="text/css">
    <link href="bootstrap/vendor/simple-line-icons/css/simple-line-icons.css" rel="stylesheet">
    <link href="bootstrap/css/stylish-portfolio.css" rel="stylesheet">
  </head>

  <body id="page-top">

    <!-- Header -->
    <header class="masthead d-flex">
      <div class="container text-center my-auto">
        <div><img class="logo" src="bootstrap/img/logo.png"></div>
      </div>
      <div class="overlay"></div>
    </header>

    <section class="elow">
    <div class="text-center">
        <h1 class="mb-1">Willkommen</h1>
        <h3 class="mb-5"><em>bei neckAttack!</em></h3>
    </div>
    </section>

    <!-- Content -->
      <section class="container text-center content-block">
        <div class="row">


			<?php 

			if (isset($R["code"])) {
			  $header = "Location: ".ABSURL.$R["code"];
			  header($header);
			  exit;
			}


			$sql = "SELECT `id`, `name`, `hashlink` FROM `clients` WHERE `enabled` = '1' ORDER BY `name`";
			$clients = $DB->PreparedSelect($sql, array(), false, false);

			require ROOT."/tpl/_include.php";
			?> 


              </div>


        </div>
      </section>

  </body>

</html>