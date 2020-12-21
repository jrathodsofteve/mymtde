<?php

require_once("php/api.php");

if (isset($_POST["submit"])) {
    $response = api("confirm_newsletters");
    if ($response["success"]) {
        header("Location: success.php?for=newsletters_confirmed_success");
    }
} else {
    $response = api("get_confirm_newsletters_data");
    if ($response["success"]) {
        $data = $response["data"];
        $newsletter_data = $data["newsletters"];
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
        <?php if (isset($response) && !$response["success"]) { ?>
            <div class="row">
                <div class="medium-12 columns">
                        <div class="alert alert-danger">
                            Ein Fehler ist aufgetreten. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support (<a href="mailto:online@medical-tribune.de">online@medical-tribune.de</a>).
                        </div>
                        <div hidden>
                            <?= json_encode($response) ?>
                        </div>
                </div> 
            </div>
        <?php } else { ?>
            <form method="post" class="row padded">
                <fieldset>
                    <legend>Neues Datenschutzrecht</legend>
                </fieldset>
                <input type="hidden" name="data[customer_id]" value="<?= $data["customer_id"] ?>">
                <div class="row form-group">
                    <div class="medium-3 columns">
                        <label class="inline">
                            Vorname
                        </label>
                    </div>
                    <div class="medium-9 columns">
                        <input type="text" disabled value="<?= $data["firstname"] ?>">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-3 columns">
                        <label class="inline">
                            Nachname
                        </label>
                    </div>
                    <div class="medium-9 columns">
                        <input type="text" disabled value="<?= $data["lastname"] ?>">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="medium-3 columns">
                        <label class="inline">
                            E-Mail
                        </label>
                    </div>
                    <div class="medium-9 columns">
                        <input type="text" disabled value="<?= $data["email"] ?>">
                    </div>
                </div>
                <br>
                <div class="form-group check">
                    <div class="row form-group">
                        <div class="medium-12 columns">
                            <b>Abbonierte Newsletter:</b>
                        </div>
                    </div>
                </div>
                <?php include("templates/newsletters.php"); ?>
                <br>
                <div class="row form-group submit">
                    <div class="medium-12 columns">
                        <input type="submit" name="submit" value="Abschicken" class="button expanded submit">
                    </div>
                </div>
            </form>
        <?php } ?>
    </body>
</html>
