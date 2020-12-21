<?php

if (!isset($_GET["token"])) {
    die("Link error.");
}

require("php/api.php");

$err = false;
if (isset($_POST["data"])) {
    $response = api("first_update");
    if (!$response["success"]) {
        $err = true;
    } else {
        header("Location: success.php?for=new-profile");
    }
} else {
    $response = api("check");
    if (!$response["success"]) {
        die("Ein Fehler ist aufgetreten, bitte versuchen Sie es später nochmal. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support.<br>" . ($response["error"] ? $response["error"] : json_encode($response)));
    } else if (!$response["inUse"]) {
        header("Location: https://www.medical-tribune.de/profil/registrieren/");
    } else if ($response["status"] == 1) {
        header("Location: https://www.medical-tribune.de/profil/login/");
    } else if ($response["status"] != 2) {
        die("Invalid email status.");
    }
}

?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="css/purified.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-datepicker.standalone.min.css">
        <link href="https://use.fontawesome.com/releases/v5.0.0/css/all.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">
        <script src="js/jquery-3.2.1.min.js"></script>
        <script src="js/bootstrap-datepicker.min.js"></script>
        <script src="js/functions.js"></script>
        <script src="js/app.js"></script>
        <script src="js/profile.js"></script>
        <script>
            apiPath = "php/api.php";
            enablePasswordSection = true;
            
            $(function() {
                $.post("php/api.php", { cmd: "fetch_newsletters", mail: $("#formhandler-email").val() }, function(data) {
                    var json = tryParseJSON(data);
                    if (json && json.success) {
                        for (var key in json.data) {
                            if (json.data.hasOwnProperty(key)) {
                                $("#formhandler-" + key).prop("checked", json.data[key]);
                            }
                        }
                    } else {
                        console.log("Could not fetch newsletters: ", json);
                    }
                });
            });
        </script>
    </head>
    <body>
        <header id="main-navigation">
            <div class="logo">
                <a href="http://mtde.opencart.bejatest.ch/index.php?route=common/home">
                    <img src="http://mtde.opencart.bejatest.ch/image/catalog/mtde_logo.png" title="Medical Tribune Verlagsgesellschaft GmbH" alt="Medical Tribune Verlagsgesellschaft GmbH" class="img-responsive">
                </a>
            </div>
            <div class="main-bar"></div>
            <div class="sub-bar"></div>
        </header>
        <?php if ($err) { ?>
            <div class="row">
                <div class="medium-12 columns">
                    <div class="alert alert-danger">
                        Ein Fehler ist aufgetreten. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support (<a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a>).
                    </div>
                    <div>
                        <?= json_encode($response) ?>
                    </div>
                </div> 
            </div>
        <?php } else { ?>
            <form method="post" id="profile_form" class="row large-8" validate data-new="1" enctype="multipart/form-data">
                <fieldset>
                    <legend>Aktualisierung ihres Profils</legend>
                </fieldset>
                <div class="row form-group info-row">
                    <div class="medium-12 columns">
                        Bitte überprüfen Sie kurz die Angaben in Ihrem Nutzerprofil auf Aktualität und legen sich ein neues Wunschpasswort an. Vielen Dank und weiterhin viel Freude bei der Nutzung der Online-Angebote der Medical Tribune.
                    </div>
                </div>
                <br>
                <div class="row form-group">
                    <div class="medium-3 columns">
                        <label for="formhandler-email" class="inline">
                            E-Mail <span>*</span>
                        </label>
                    </div>
                    <div class="medium-9 columns">
                        <div id="email-div">
                            <input title="Geben Sie hier Ihre E-Mail-Adresse ein" required id="formhandler-email" type="email" disabled value="<?= $response["email"] ?>">
                            <input name="data[email]" type="hidden" value="<?= $response["email"] ?>">
                        </div>
                    </div>
                </div>

                <?php
                include("templates/password-form.html");
                include("templates/form.php"); 
                ?>

                <div class="form-group check">
                    <div class="row form-group">
                        <div class="medium-3 columns">
                            <span>Newsletter-Abos:</span>
                        </div>
                        <div class="medium-9 columns">
                            <?php include("templates/newsletters.php"); ?>
                        </div>
                    </div>
                </div>
                <br>
                <div id="submit_profile_div">
                    <div class="row form-group submit">
                        <div class="medium-12 columns">
                            <input type="submit" id="form_submit" name="data[submit]" value="Speichern" class="button expanded submit">
                        </div>
                    </div>
                    <div class="row form-group submit">
                        <div class="medium-12 columns">
                            <p class="notice">Die Felder, die mit einem Asterisk bzw. Stern ( * ) markiert sind, müssen ausgefüllt werden.</p>
                        </div>
                    </div>
                </div>
            </form>
        <?php } ?>
        <footer>
            <div class="row">
                <div class="medium-3 columns">
                    <a target="_blank" href="https://www.medical-tribune.de/impressum/">Impressum</a>
                </div>
                <div class="medium-3 columns">
                    <a target="_blank" href="https://www.medical-tribune.de/agb/">AGB</a>
                </div>
                <div class="medium-3 columns">
                    <a target="_blank" href="https://www.medical-tribune.de/datenschutzbestimmungen/">Datenschutzbestimmungen</a>
                </div>
                <div class="medium-3 columns">
                    <a target="_blank" href="https://www.medical-tribune.de/verfahrensverzeichnis/">Verfahrensverzeichnis</a>
                </div>
            </div>
        </footer>
    </body>
</html>