<?php header('Access-Control-Allow-Origin: *');


$err = false;
// print_r($_POST);

$_email = isset($_POST["email"]) ? $_POST["email"] : $_GET["email"];
$_gender = isset($_POST["gender"]) ? $_POST["gender"] : $_GET["gender"];
$_lastname = isset($_POST["lastname"]) ? $_POST["lastname"] : $_GET["lastname"];

if($_gender == 'm'){
	$_gender = 'Herr';
}else if($_gender == 'w') {
	$_gender = 'Frau';
}

if ($_email) 
{
    require("php/api.php");    
    $response = api("send_mail");

    if (!$response["success"]) 
    {
        $err = true;
    }

       // header("Location: success.php?for=iframe");
	   //echo 'success';
	   ?>
	   <!DOCTYPE html>

	<html lang="de">

    <head>

        <meta charset="utf-8">

        <link rel="stylesheet" type="text/css" href="css/purified.css">

        <link rel="stylesheet" type="text/css" href="css/style.css">

        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">

    </head>

    <body style="margin-bottom:0;padding-top:3%">
        <?php if ($err) { ?>
            <div style="max-width:600px;padding:15px;">
            <div class="row">
                <div class="medium-12 columns">
                    <div class="alert alert-danger">
                        Ein Fehler ist aufgetreten. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support (<a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a>).
                    </div>
                <?php 
                if(isset($_GET["maglify"]) && $_GET["maglify"] == "1") 
                {
                    echo var_export($response,true);

                    if(isset($_GET["mobile"]) && $_GET["mobile"] == "1") 
                    {
                        ?><p>» <a href="http://www.medical-tribune.de/epaper/close/">Zurück zum Login</a></p><?php
                    }
                    else 
                    {
                        ?><p>» <a target="_parent" href="http://www.medical-tribune.de/epaper/?/login/">Zurück zum Login</a></p><?php
                    }
                }
                ?>
                </div>
            </div>
            </div>
        <?php } else { ?>
            <div style="max-width:600px;padding:15px;">

            <div class="row">
			<div class="medium-12 columns">
				<p>
					Hallo <?php echo $_gender.' ' ?> <?php echo $_lastname ?>,
				</p>
				<p>
					wir haben unsere Webseite neugestaltet und viele Funktionen verbessert.<br /> Dies beinhaltet auch den bisherigen Login-Bereich. Leider können wir Ihr bisheriges Passwort aus datenschutzrechtlichen Gründen nicht übernehmen. Daher möchten wir Sie bitten, kurz Ihre Profildaten zu überprüfen und sich einfach ein neues Passwort (es kann auch wieder ihr altes sein) anzulegen.
					<br>Wir haben Ihnen hierzu soeben einen Link an Ihre E-Mail-Adresse <?php echo $_email ?> gesendet.
				</p>
				<p>
					Herzlichen Dank für Ihr Verständnis!
                </p>
                
                <?php 
                if(isset($_GET["maglify"]) && $_GET["maglify"] == "1") 
                {
                    if(isset($_GET["mobile"]) && $_GET["mobile"] == "1") 
                    {
                        ?><p>» <a href="http://www.medical-tribune.de/epaper/close/">Zurück zum Login</a></p><?php
                    }
                    else 
                    {
                        ?><p>» <a target="_parent" href="http://www.medical-tribune.de/epaper/?/login/">Zurück zum Login</a></p><?php
                    }
                }
                ?>


                    </div>
				</div>

        </div>
        <?php } ?>

    </body>

</html>

	   <?php

}
?>
