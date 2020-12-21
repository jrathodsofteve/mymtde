<?php

$err = false;
if (isset($_POST["email"])) {
    require("php/api.php");
    $response = api("send_mail");
    if (!$response["success"]) {
        $err = true;
    } else {
        header("Location: success.php?for=iframe");
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
    <body style="margin-bottom:0;padding-top:3%">
        <?php if ($err) { ?>
            <div class="container">
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
            </div>
        <?php } else { ?>
            <form method="post" class="container">
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <input type="email" placeholder="E-Mail" name="email" required>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-12 columns">
                        <button type="submit" class="button expanded submit">Absenden</button>
                    </div>
                </div>
            </form>
        <?php } ?>
        <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
    </body>
</html>
