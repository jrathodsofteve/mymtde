<?php

require_once("php/api.php");

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_POST["logout"])) {
    session_destroy();
    session_start();
}
if (isset($_GET["sso_id"])) {
    $response = api("sso");
    if ($response["success"]) {
        $_SESSION["id"] = $response["id"];
    }
}
if (!isset($_SESSION['id']) || !api("check_session_validity")["valid"]) {
    session_destroy();
    header("Location: login.php");
    die();
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
        </script>
    </head>
    <body>
        <form method="post" id="profile_form" class="row large-8" validate enctype="multipart/form-data">
            <?php
                if (isset($_POST["data"])) {
                    $response = api(
                        (isset($_POST["data"]["password_old"]) ?
                            "change_password" :
                            (isset($_POST["data"]["email"]) ?
                                "change_email" :
                                "update"
                            )
                        )
                    );
                    if (!$response["success"]) { ?>
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
                        <div class="medium-12 columns">
                            <div class="alert alert-success">
                                Die Änderungen wurden gespeichert!
                            </div>
                            <?php if (isset($response["newNewsletters"]) && $response["newNewsletters"] === true) { ?>
                                <div class="alert alert-info">
                                    Sie haben eine E-Mail erhalten, um Ihre neuen Newsletter-Anmeldungen zu bestätigen.
                                </div>
                            <?php } else if (isset($_POST["data"]["email"])) { ?>
                                <div class="alert alert-info">
                                    Sie haben eine E-Mail erhalten, um Ihre neue E-Mail-Adresse zu bestätigen.
                                </div>
                            <?php } ?>
                        </div>
                    <?php
                    }
                }
            ?>
            <fieldset style="float:left">
                <legend>Profil</legend>
            </fieldset>
            <fieldset class="edit-legend">
                <legend>
                    <button type="button" id="edit_button" hidden>Bearbeiten</button>
                    <button type="button" id="cancel_edit_button">Bearbeiten abbrechen</button>
                    <span style="margin: 0 10px;color: #e4e4e4;font-weight: normal">|</span>
                    <button type="submit" name="logout" value="1" id="logout-btn">Logout</button>
                </legend>
            </fieldset>
            <div class="row form-group">
                <div class="medium-3 columns">
                    <label for="formhandler-email" class="inline">
                        E-Mail <span>*</span>
                    </label>
                </div>
                <div class="medium-9 columns">
                    <div id="change_email_button_div">
                        <button type="button" class="button" id="change_email_button">Bearbeiten</button>
                        <button type="button" class="button" id="cancel_email_change_button" hidden>
                            <i class="fas fa-times"></i>
                        </button>
                        <button type="submit" class="button" id="save_email_change_button" disabled hidden>Speichern</button>
                    </div>
                    <div id="email-div">
                        <input title="Geben Sie hier Ihre E-Mail-Adresse ein" required name="data[email]" id="formhandler-email" type="email" disabled>
                    </div>
                </div>
            </div>
            <div class="row form-group" id="change_pw_div">
                <div class="medium-3 columns">
                    <label for="formhandler-password" class="inline">
                        Passwort
                    </label>
                </div>
                <div class="medium-9 columns">
                    <button type="button" class="button" id="change_pw_button">Ändern...</button>
                </div>
            </div>
            <div id="new_pw_div" hidden>
                <div class="row form-group">
                    <div class="medium-3 columns">
                        <label for="formhandler-password" class="inline">
                            Aktuelles Passwort <span>*</span>
                        </label>
                    </div>
                    <div class="medium-9 columns">
                        <input title="Geben Sie hier ihr aktuelles Passwort ein" name="data[password_old]" required id="formhandler-password_old" type="password" disabled>
                    </div>
                </div>
                <?php include("templates/password-form.html") ?>
                <div class="row form-group" id="save_pw_div">
                    <div class="medium-3 columns"></div>
                    <div class="medium-9 columns">
                        <button type="button" class="button" id="cancel_pw_change_button">Abbrechen</button>
                        <button type="submit" class="button" id="save_pw_change_button">Speichern</button>
                    </div>
                </div>
            </div>

            <?php include("templates/form.php"); ?>

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
            <div class="row form-group">
                <div class="medium-12 columns">
                    <small><a id="deleteBtn">Konto löschen</a></small>
                </div>
            </div>
        </form>
        <div class="row" id="deletedDiv">
            <div class="medium-12 columns">
                <div class="alert alert-info">
                    Zur Bestätigung der Löschung Ihres Kontos klicken Sie bitte auf den Link in der E-Mail, die Sie erhalten haben.
                </div>
            </div>
        </div>
        <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
    </body>
</html>
