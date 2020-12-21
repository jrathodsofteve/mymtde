<?php

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}
session_destroy();
session_start();

$err = false;
if (isset($_POST["username"]) && isset($_POST["password"])) {
    require_once("php/api.php");
    $response = api("login");
    if (!$response["success"]) {
        $err = true;
    } else if ($response["login"] !== true) {
        $fail = "Benutzername und Passwort stimmen nicht überein!";
    } else {
        $_SESSION["id"] = $response["id"];
        $response = api("delete_account");
        if (!$response["success"]) {
            $err = true;
        } else {
            header("Location: success.php?for=delete");
        }
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
    </head>
    <body>
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
            <form method="post" id="login_form" class="row large-8">
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <div class="alert alert-info">
                            Bitte geben Sie Ihr Passwort ein, um das Löschen Ihres Kontos zu bestätigen.
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="text" disabled value="<?= $_GET["mail"] ?>">
                        <input type="hidden" name="username" value="<?= $_GET["mail"] ?>">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="password" placeholder="Passwort" name="password">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <button type="submit" id="submit-button" class="button expanded submit">Login</button>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <?= isset($fail) ? '<div class="alert alert-danger">' . $fail . '</div>' : '' ?>
                    </div>
                </div>
            </form>
        <?php } ?>
    </body>
</html>