<?php

session_start();
$fail = false;
if (isset($_POST["logout"])) {
    session_destroy();
    session_start();
} else if (isset($_SESSION["id"])) {
    header("Location: profile.php");
} else if (isset($_POST["username"]) && isset($_POST["password"])) {
    require("php/api.php");
    $response = api("login");
    if ($response["success"]) {
        if ($response["login"] !== true) {
            $fail = [];
            if (isset($response["email_status"]) && $response["email_status"] == 0) {
                $fail[] = "Bitte bestätigen Sie Ihre E-Mail-Adresse.";
            }
            if (isset($response["doc_status"]) && $response["doc_status"] == 0) {
                $fail[] = "Bitte warten Sie, bis wir Ihr Dokument verifiziert haben.";
            }
            if (count($fail) == 0) {
                $fail[] = "Benutzername und Passwort stimmen nicht überein!";
            }
            $fail = implode("<br>", $fail);
        } else {
            $_SESSION["id"] = $response["id"];
            $_SESSION["pw_hash"] = $response["pw_hash"];
            header("Location: profile.php");
        }
    }
} else if (isset($_POST["username"])) {
    require("php/api.php");
    $response = api("send_reset_password");
    if ($response["success"] && !$response["notConfirmed"]) {
        header("Location: success.php?for=reset");
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
        <?php if (isset($response["success"]) && $response["success"] === false) { ?>
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
        <?php } else if (isset($response["notConfirmed"]) && $response["notConfirmed"]) { ?>
            <div class="row">
                <div class="medium-12 columns">
                    <div class="alert alert-info">
                        Sie müssen erst Ihre E-Mail-Adresse bestätigen.
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <form method="post" id="login_form" class="row large-8">
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="text" placeholder="E-Mail" name="username" required>
                    </div>
                </div>
                <?php if (!isset($_GET["reset"])) { ?>
                    <div class="row form-group">
                        <div class="medium-12 columns">
                            <input type="password" placeholder="Passwort" name="password">
                        </div>
                    </div>
                <?php } ?>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <button type="submit" id="submit-button" class="button expanded submit"><?= isset($_GET["reset"]) ? "Absenden" : "Login" ?></button>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <?= $fail ? '<div class="alert alert-danger">' . $fail . '</div>' : '' ?>
                    </div>
                </div>
                <?php if (!isset($_GET["reset"])) { ?>
                    <div class="row form-group">
                        <div class="medium-12 columns" align="center">
                            <small><a href="?reset">Passwort zurücksetzen</a></small>
                        </div>
                    </div>
                <?php } ?>
            </form>
        <?php } ?>
        <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
    </body>
</html>
