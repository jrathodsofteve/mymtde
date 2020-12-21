<?php

function wh_log($log_msg)
{
    $log_filename = "log";
	$dir = "log";
    if (!file_exists($log_filename))
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'log_' . date('d-M-Y') . '.log';
    // if you don't add `FILE_APPEND`, the file will be erased each time you add a log
    file_put_contents("$dir/$log_file_data", $log_msg . "\n", FILE_APPEND);
}

$email = $_POST['data']['email'];

$err = false;
if (isset($_POST["data"])) {
    $input_filled = true;

    $req_fields = array(
        "email",
        "password",
        "passwordconfirm",
        "firstname",
        "lastname",
        "job",
    );

    foreach($req_fields as $field){
        if(empty($_POST["data"][$field])){
            $input_filled = false;
            echo "<div class='medium-12 columns'><div class='alert alert-danger'>Bitte füllen Sie alle Pflichtfelder aus!</div></div>";
            break;
        }
    }

    require("php/api.php");
    $response = api("register");
    if ($response["success"] && $input_filled == true) {
		$log = date('d-m-Y').' : '.$email.','.print_r($_SERVER, true);
		wh_log($log);
        $maglify = isset($_GET["maglify"]) && $_GET["maglify"] == "1";
        $mobile = isset($_GET["mobile"]) && $_GET["mobile"] == "1";
        header("Location: success.php?for=register" . ($mobile ? "&mobile=1" : "") . ($maglify ? "&maglify=1" : ""));
    } elseif($input_filled == true) {
        $err = true;
    }
}

// print_r($_POST);
?>
<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="css/purified.css">
        <link rel="stylesheet" type="text/css" href="css/bootstrap-datepicker.standalone.min.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">
        <script src="js/jquery-3.2.1.min.js"></script>
        <script src="js/bootstrap-datepicker.min.js"></script>
        <script src="js/app.js"></script>
        <script src="js/functions.js"></script>
        <script src="js/register.js"></script>
        <script>
            apiPath = "php/api.php";
        </script>
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
            <form method="post" id="register_form" class="row padded" validate enctype="multipart/form-data">
                <fieldset>
                    <legend>Registrierung</legend>
                </fieldset>
                <div class="row form-group info-row">
                    <div class="medium-12 columns">
                        Legen Sie jetzt bitte Ihre Zugangsdaten an. Mit der Registrierung bekommen Sie Zugriff auf aktuelle, verlässliche Nachrichten,
                        Kongressberichte und Interviews, redaktionelle News- und Fachletter sowie Fortbildungen für Ärzte.
                        Als registrierter Nutzer können Sie außerdem viele Inhalte kostenfrei im Volltext lesen.
                        <!-- Schon registriert? » <?php
                        if(isset($_GET["maglify"]) && $_GET["maglify"] == "1")
                        {
                            if(isset($_GET["mobile"]) && $_GET["mobile"] == "1")
                            {
                                ?><a href="http://www.medical-tribune.de/epaper/close/">Zum Login</a><?php
                            }
                            else
                            {
                                ?><a target="_parent" href="http://www.medical-tribune.de/epaper/?/login/">Zum Login</a><?php
                            }
                        }
                        else
                        {
                            ?><a href="login.php">Zum Login</a><?php
                        }
                        ?>
                         -->
                    </div>
                </div>
                <br>
                <input type="hidden" value="<?= isset($_GET["redirect_url"]) ? $_GET["redirect_url"] : "" ?>" name="redirect_url">
                <div class="row form-group">
                    <div class="medium-3 columns">
                        <label for="formhandler-email" class="inline">
                            E-Mail <span>*</span>
                        </label>
                    </div>
                    <div class="medium-9 columns">
                        <input title="Geben Sie hier Ihre E-Mail-Adresse ein" required name="data[email]" id="formhandler-email" type="email">
                    </div>
                </div>

                <?php
                include("templates/password-form.html");
                include("templates/form.php");
                ?>

                <div class="form-group check">
                    <input id="formhandler-terms_and_condition" required type="checkbox" class="type-checkbox" name="data[terms_and_condition]" value="1">
                    <label for="formhandler-terms_and_condition" class="inline">
                        Ja, ich stimme den <a target="_blank" href="https://www.medical-tribune.de/agb/">Nutzungsbedingungen</a> und den <a target="_blank" href="https://www.medical-tribune.de/datenschutzbestimmungen/">Datenschutzbestimmungen</a> von medical-tribune.de inklusive des Single-Sign-on-Dienstes (SSO) zu.
                        <span>*</span>
                    </label>
                </div>
                <div class="form-group check" style="margin-bottom:0">
                    <div class="row form-group">
                        <div class="medium-12 columns">
                            <b>Ich möchte folgende kostenlose und jederzeit abbestellbare Newsletter erhalten:</b>
                        </div>
                    </div>
                </div>
                <div class="form-group check">
                    <div class="row form-group">
                        <div class="medium-12 columns">
                            <?php include("templates/newsletters.php"); ?>
                        </div>
                    </div>
                </div>
                <br>
                <div class="row form-group submit">
                    <div class="medium-12 columns">
                        <input type="submit" id="form_submit" name="data[submit]" value="Jetzt registrieren" class="button expanded submit">
                    </div>
                </div>
                <p class="notice">Die Felder, die mit einem Asterisk bzw. Stern ( * ) markiert sind, müssen ausgefüllt werden.</p>
                <div class="row form-group info-row">
                    <div class="medium-12 columns">
                        Haben Sie noch Fragen zur Registrierung? Ausführliche Informationen finden Sie <a target="_blank" href="https://www.medical-tribune.de/faq/">in unseren FAQ</a>.
                    </div>
                </div>
            </form>
        <?php } ?>
        <script type="text/javascript" src="js/iframeResizer.contentWindow.min.js"></script>
    </body>
</html>
