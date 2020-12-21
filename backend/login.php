<?php

session_start();
if (isset($_POST["logout"])) {
    session_destroy();
    session_start();
} else if (isset($_SESSION["id"])) {
    header("Location: index.php");
} else if (isset($_POST["username"]) && isset($_POST["password"])) {
    require("../php/api.php");
    $response = api("login/admin");
    if ($response["success"]) {
        if ($response["login"] !== true) {
            $fail = "Benutzername und Passwort stimmen nicht überein!";
        } else {
            $_SESSION["id"] = $response["id"];
            $_SESSION["pw_hash"] = $response["pw_hash"];
            $_SESSION["is_admin"] = true;
            header("Location: index.php");
        }
    }
}

?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="../css/purified.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
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
        <?php } else { ?>
            <form method="post" id="login_form" class="row large-8">
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="text" placeholder="E-Mail" name="username" required>
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
                        <?= isset($fail) && $fail ? '<div class="alert alert-danger">' . $fail . '</div>' : '' ?>
                    </div>
                </div>
            </form>
        <?php } ?>
    </body>
</html>
