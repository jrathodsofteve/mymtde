<?php

if (!isset($_GET["for"])) {
    die();
}
$for = $_GET["for"];

?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <?= ($for == "newsletters_success" || $for == "email_success" || $for === "password") ? '<meta http-equiv="refresh" content="6; URL=https://www.medical-tribune.de/">' : '' ?>
        <?= ($for == "register_success" && isset($_GET["redirect_url"])) ? '<meta http-equiv="refresh" content="2; URL=' . $_GET["redirect_url"] . '">' : '' ?>
        <link rel="stylesheet" type="text/css" href="css/purified.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">
    </head>
    <body>
        <div class="container">
            <div class="row">
                <?php if ($for == "register") { ?>
                    <div class="medium-12 columns">
                        <div class="alert alert-success">
                            Vielen Dank für Ihr Interesse. Bitte aktivieren Sie noch Ihr Nutzerkonto.
                        </div>
                        <br>
                        <div>
                            Um Ihre Registrierung abzuschließen, klicken Sie bitte auf den Aktivierungslink, den Sie eben von uns in einer E-Mail erhalten haben.  
                            <?php 
                            if(isset($_GET["maglify"]) && $_GET["maglify"] == "1") 
                            {
                                if(isset($_GET["mobile"]) && $_GET["mobile"] == "1") 
                                {
                                    ?><br><br>» <a href="http://www.medical-tribune.de/epaper/close/">Zurück zum Login</a><?php
                                }
                                else 
                                {
                                    ?><br><br>» <a href="http://www.medical-tribune.de/epaper/?/login/">Zurück zum Login</a><?php
                                }
                            }?>
                        </div>
                    </div> 
                <?php } else if ($for == "new-profile") { ?>
                    <div class="medium-12 columns">
                        <div class="alert alert-success">
                            Ihre Profilinformationen wurden erneuert. Vielen Dank!
                        </div>
                        <div class="alert alert-info">
                            Bitte schließen Sie den Aktualisierungsprozess Ihres MT-Nutzerkontos ab. Klicken Sie hierzu bitte auf den Link in der E-Mail mit dem Betreff ‚Bitte bestätigen Sie die Aktualisierung Ihres MT-Nutzerkontos, die wir Ihnen soeben geschickt haben. Danach sind Sie startklar und können sich wie gewohnt einloggen.
                        </div>
                    </div> 
                <?php } else if ($for == "password") { ?>
                    <div class="medium-12 columns">
                        <div class="alert alert-info">
                            Ihr Passwort wurde erfolgreich geändert.
                        </div>
                        <div class="alert alert-info">
                            Sie werden nun zur Startseite weitergeleitet...
                        </div>
                    </div>
                <?php } else if ($for == "reset") { ?>
                    <div class="medium-12 columns">
                        <div class="alert alert-success">
                            Sie haben eine E-Mail erhalten, um Ihr Passwort zurückzusetzen.
                        </div>
                    </div>
                <?php } else if ($for == "delete") { ?>
                    <div class="medium-12 columns">
                        <div class="alert alert-info">
                            Ihr Konto wurde gelöscht.
                        </div>
                    </div>
                <?php } else if ($for == "iframe") { ?>
                    <div class="medium-12 columns">
                        <div class="alert alert-success">
                            E-Mail erfolgreich versendet!
                        </div>
                    </div>
                <?php } else if ($for == "register_success") { ?>
                    <div class="container">
                        <div class="row">
                            <div class="medium-12 columns">
                                <div class="alert alert-success">
                                    Vielen Dank für die Bestätigung. Ihr Nutzerkonto ist nun aktiviert.
                                </div>
                            </div> 
                        </div>
                        <?php if (!isset($_GET["redirect_url"])) { ?>
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="medium-12 columns">
                                        Kehren Sie auf die Seite zurück, auf der Sie sich registriert haben:
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="medium-12 columns">
                                        <a href="https://www.medical-tribune.de/">www.medical-tribune.de</a>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="medium-12 columns">
                                        <a href="https://shop.medical-tribune.de/">shop.medical-tribune.de</a>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="medium-12 columns">
                                        <a href="http://egbh.medical-tribune.de/">egbh.medical-tribune.de</a>
                                    </div> 
                                </div>
                                <div class="row">
                                    <div class="medium-12 columns">
                                        <a href="http://epaper.medical-tribune.de/">epaper.medical-tribune.de</a>
                                    </div> 
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="medium-12 columns">
                                        Sie werden in Kürze weitergeleitet...
                                    </div> 
                                </div> 
                            </div>
                        <?php } ?>
                    </div>
                <?php } else if ($for == "email_success") { ?>
                    <div class="container">
                        <div class="row">
                            <div class="medium-12 columns">
                                <div class="alert alert-success">
                                    Vielen Dank für die Bestätigung. Ihre E-Mail-Adresse wurde geändert.
                                </div>
                                <div class="alert alert-info">
                                    Sie werden nun zur Startseite weitergeleitet...
                                </div>
                            </div> 
                        </div>
                    </div>
                <?php } else if ($for == "newsletters_success") { ?>
                    <div class="container">
                        <div class="row">
                            <div class="medium-12 columns">
                                <div class="alert alert-success">
                                    Ihre Newsletter-Abonnements wurden erfolgreich geändert!
                                </div>
                                <div class="alert alert-info">
                                    Sie werden nun zur Startseite weitergeleitet...
                                </div>
                            </div> 
                        </div>
                    </div>
                <?php } else if ($for == "newsletters_confirmed_success") { ?>
                    <div class="container">
                        <div class="row">
                            <div class="medium-12 columns">
                                <div class="alert alert-success">
                                    Ihre Newsletter-Abonnements wurden erfolgreich bestätigt!
                                </div>
                                <div class="alert alert-info">
                                    Sie haben eine E-Mail zur Bestätigung erhalten.
                                </div>
                            </div> 
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
    </body>
</html>        