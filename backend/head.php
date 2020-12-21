<?php

require_once("../php/api.php");

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_GET["logout"])) {
    session_destroy();
    session_start();
}
if (!isset($_SESSION['id']) || !api("check_session_validity/admin")["valid"]) {
    session_destroy();
    header("Location: login.php");
    die();
}

?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="../css/purified.css">
        <link rel="stylesheet" type="text/css" href="../css/bootstrap-datepicker.standalone.min.css">
        <link href="https://use.fontawesome.com/releases/v5.0.0/css/all.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">
        <script src="../js/jquery-3.2.1.min.js"></script>
        <script src="../js/bootstrap-datepicker.min.js"></script>
        <script src="../js/functions.js"></script>
        <script src="../js/app.js"></script>
        <script src="../js/register.js"></script>
        <script src="../js/profile.js"></script>
        <script src="backend.js"></script>
        <script>
            apiPath = "../php/api.php";
        </script>
    </head>
    <body>
        <div class="row">
            <div class="row" id="alert-row">
                <div class="medium-12 columns">
                <?php
                    if (isset($response) && !$response["success"]) { ?>
                        <div class="alert alert-danger">
                            Ein Fehler ist aufgetreten. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support (<a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a>).
                        </div>
                        <div>
                            <?= json_encode($response) ?>
                        </div>
                    <?php } else if (isset($_SESSION["success_message_for"])) { ?>
                        <div class="alert alert-success">
                            <?= $_SESSION["success_message_for"] == "register" ? "Der Benutzer wurder erfolgreich angelegt!" : "Die Änderungen wurden gespeichert!" ?>
                        </div>
                    <?php 
                    unset($_SESSION["success_message_for"]);
                    } ?>
                </div>
            </div>
            <fieldset style="float:left">
                <legend>
                    <a href="index.php">Benutzer</a>
                    <span class="separator">|</span>
                    <a href="info.php">Statistiken</a>
                </legend>
            </fieldset>
            <fieldset class="edit-legend">
                <legend>
                    <div id="cancel-edit-div" hidden>
                        <button type="button" id="cancel_edit_button">Abbrechen</button>
                        <span class="separator">|</span>
                    </div>
                    <a href="?logout">
                        <button type="button">Logout</button>
                    </a>
                </legend>
            </fieldset>
        </div>