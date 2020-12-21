<?php

$err = false;
if (isset($_POST["password"]) && isset($_POST["password_confirm"])) {
    require("php/api.php");
    $response = api("reset_password");
    if (!$response["success"]) {
        $err = true;
    } else {
        header("Location: success.php?for=password");
    }
}
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="css/purified.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">
        <script src="js/jquery-3.2.1.min.js"></script>
        <script src="js/functions.js"></script>
        <script src="js/new-password.js"></script>
    </head>
    <body>
        <?php if ($err) { ?>
            <div class="row">
                <div class="medium-12 columns">
                    <div class="alert alert-danger">
                    <div class="alert alert-danger">
                        Ein Fehler ist aufgetreten. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support (<a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a>).
                    </div>
                    <div>
                        <?= json_encode($response) ?>
                    </div>
                </div> 
            </div>
        <?php } else { ?>
            <form method="post" id="reset_form" class="row large-8">
                <input type="hidden" name="token" value="<?= $_GET["token"] ?>">
                <div class="row form-group info-row">
                    <div class="medium-12 columns">
                        Bitte geben Sie erneut Ihre E-Mail-Adresse sowie ihr neues Wunschpasswort ein.
                    </div>
                </div>
                <div class="row form-group info-row">
                    <div class="medium-12 columns">
                        Ihr Passwort muss mindestens 8 bis maximal 40 Zeichen lang sein und mindestens eine Ziffer sowie einen Klein- und einen Großbuchstaben enthalten.
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="text" placeholder="E-Mail" name="email" required>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="password" placeholder="Passwort" name="password" required>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="password" placeholder="Passwort wiederholen" name="password_confirm" required>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <button type="submit" id="submit-button" class="button expanded submit">Absenden</button>
                    </div>
                </div>
            </form>
        <?php } ?>
    </body>
</html>