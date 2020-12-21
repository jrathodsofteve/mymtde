<?php

require_once("functions.php");

$error = init();
if ($error) die(var_dump($error));

global $write_ids, $remove_from;

if (isset($_POST["submit"])) {
    if (!isset($_POST["data"])) die("No data given");

    $data = $_POST["data"];
    if (!isset($data["newsletter"]) || (isset($data["permission-granted"]) && !$data["permission-granted"])) {
        $data["newsletter"] = [];
    }

    $valid_newsletters = array_keys(array_intersect_key(array_filter($data["newsletter"]), $write_ids));

    if (isset($data["permission-granted"])) {
        $invalid_newsletters = array_diff(array_keys($write_ids), $valid_newsletters);

        /*$sql = "INSERT INTO postalcard_confirmations
                    (email, spm_id, spm_source" . implode(", ", array_merge([""], $valid_newsletters)) . ")
                VALUES
                    ('" . $conn->escape_string($data["email"]) . "', " .
                    "'" . $conn->escape_string($data["spm_id"]) . "', " .
                    "'" . $conn->escape_string($data["spm_source"]) . "'" .
                    implode(", ", array_merge([""], array_fill(0, count($valid_newsletters), 1))) . ")";
        $result = $conn->query($sql);*/
        $usermail = $data['email'];
        $sql = mysqli_query($conn, "SELECT * FROM postalcard_confirmations WHERE email='$usermail'");
        $c = mysqli_num_rows($sql);

        //Check for permission and send mail
        if ($data['permission-granted'] == 2){
          if($c < 1){
            $sql = "INSERT INTO postalcard_confirmations_temp
                        (email, spm_id, spm_source" . implode(", ", array_merge([""], $valid_newsletters)) . ")
                    VALUES
                        ('" . $conn->escape_string($data["email"]) . "', " .
                        "'" . $conn->escape_string($data["spm_id"]) . "', " .
                        "'" . $conn->escape_string($data["spm_source"]) . "'" .
                        implode(", ", array_merge([""], array_fill(0, count($valid_newsletters), 1))) . ")";
            $result = $conn->query($sql);
          }
          $result = modifyNewsletters(NEWSLETTER_MODE_ADD);

          //Build NL FOR GET]
          $nl = http_build_query($data['newsletter']);
          $error = sendEmail("Bitte bestätigen Sie Ihre Newsletter-Anmeldungen", $data["email"], sprintf(CONFIRM_NEWSLETTERS_TEMPLATE, 'https://my.medical-tribune.de/postalcard_confirmation/success.php?success=1&email='.$usermail.'&'.$nl, getNLString($valid_newsletters)));
        }else{
          $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $invalid_newsletters);
          if(!empty($c)){
            $sql = "DELETE FROM postalcard_confirmations WHERE email='$usermail'";
            $result = $conn->query($sql);
          }
        }
        //die($conn->error . "\n" . $sql);
        // remove from some groups
        $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $remove_from, null, true);
        if (count($result) > 0) die(var_dump($result));

        // delete newsletters
        $result = modifyNewsletters(NEWSLETTER_MODE_DELETE_REAL, $invalid_newsletters);
        if (count($result) > 0) die(var_dump($result));

        if ($data["permission-granted"]) {
            // register newsletters
            $result = modifyNewsletters(NEWSLETTER_MODE_ADD, $valid_newsletters);
            if (count($result) > 0) die(var_dump($result));

            if ($error) die($error);
        }

        header("Location: success.php?success=" . $data["permission-granted"]);
    } else {
        $token = token();
        $sql = "INSERT INTO subscribe_tokens
                    (email, gender, firstname, lastname, company, spm_id, spm_source, token" . implode(", ", array_merge([""], $valid_newsletters)) . ")
                VALUES
                    ('" . $conn->escape_string($data["email"]) . "', " .
                    "'" . $conn->escape_string($data["gender"]) . "', " .
                    "'" . $conn->escape_string($data["firstname"]) . "', " .
                    "'" . $conn->escape_string($data["lastname"]) . "', " .
                    "'" . $conn->escape_string($data["company"]) . "', " .
                    (isset($data["spm_id"]) ? "'" . $conn->escape_string($data["spm_id"]) . "'" : "NULL") . ", " .
                    (isset($data["spm_source"]) ? "'" . $conn->escape_string($data["spm_source"]) . "'" : "NULL") . ", " .
                    "'" . $token . "'" .
                    implode(", ", array_merge([""], array_fill(0, count($valid_newsletters), 1))) . ")";
        $result = $conn->query($sql);
        if (!$result) die($conn->error . "\n" . $sql);

        /*$error = sendEmail("Bitte bestätigen Sie Ihre Newsletter-Anmeldungen", $data["email"],
                    sprintf(CONFIRM_NEWSLETTERS_TEMPLATE,
                                getNLString($valid_newsletters),
                                SERVER_ROOT . "confirm.php?token=$token"));*/
        //if ($error) die($error);
        header("Location: success.php?success=2");
    }
    die();
} else {
    if (empty($_GET["new"])) {
        $result_data = [];
        foreach ($read_ids as $name => $id) {
            $result_data[$name] = (bool)isRegistered($id, get("email"));
        }
    } else {
        $result_data = array_fill_keys(array_keys($write_ids), true);
    }
}


include("template_parts/head.html");

?>
<?php if (isset($response) && !$response["success"]) { ?>
    <div class="row">
        <div class="medium-12 columns">
                <div class="alert alert-danger">
                    Ein Fehler ist aufgetreten. Falls das Problem bestehen bleibt, kontaktieren Sie bitte den Support (<a href="mailto:support@s-p-m.ch">support@s-p-m.ch</a>).
                </div>
                <div hidden>
                    <?= json_encode($response) ?>
                </div>
        </div>
    </div>
<?php } else { ?>

  <?php
  $sub_email = $_GET['email'];

  $conn->select_db('mtde_shop');
  $result = $conn->query("SELECT customer_id FROM oc_customer WHERE email='$sub_email'");
  $user_id_raw = mysqli_fetch_assoc($result);
  $user_id = $user_id_raw['customer_id'];

  $sql = "SELECT * FROM oc_customer_infos WHERE customer_id = '$user_id'";
  $result_raw = $conn->query($sql);
  $result_data = $result_raw->fetch_assoc();
  $conn->select_db('mtde_confirm_newsletters');

  //print_r($_GET);
  //print_r($result_data);
   ?>
    <form method="post" class="row padded">
        <?php include("template_parts/header.html"); ?>
        <div class="row form-group info-row">
            <div class="medium-12 columns">
              Sie haben sich per Postkarte für unseren PraxisLetter angemeldet. Vielen Dank! Hiermit bitten wir Sie diese Anmeldung abzuschließen, indem Sie uns Ihre E-Mail-Adresse und Ihr Abonnement nochmals bestätigen.
            </div>
        </div>
        <br>
        <input type="hidden" name="data[spm_id]" value="<?= get("spm_id") ?>">
        <input type="hidden" name="data[spm_source]" value="<?= get("spm_source") ?>">
        <div class="row form-group">
            <div class="medium-3 columns">
                <label class="inline">
                    E-Mail-Adresse:<span>*</span>
                </label>
            </div>
            <div class="medium-9 columns">
                <input type="email" required name="data[email]" value="<?= get("email") ?>">
            </div>
        </div>
        <br>
        <div class="<?= get("new") ? "optin" : "" ?>">
            <div class="row form-group optin-row">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="radio" id="permission-granted-yes" required name="data[permission-granted]" value="2" checked <?= get("new") ? "disabled" : "" ?>>
                        Ja, ich möchte die folgenden kostenlosen und jederzeit abbestellbaren Newsletter erhalten:
                    </label>
                </div>
            </div>
            <div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="all-newsletters-cb">
                        Alle auswählen
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="praxisletter-cb" name="data[newsletter][praxisletter]" value="1" checked <?= !empty($result_data["praxisletter"]) ? "checked" : "" ?>>
                        PraxisLetter für Ärzte - News zu Medizin, Praxisführung und Arzneimittelmarkt (wöchentlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="onkoletter-cb" name="data[newsletter][onkoletter]" value="1" <?= !empty($result_data["onkoletter"]) ? "checked" : "" ?>>
                        OnkoLetter für Fachärzte - News aus Onkologie und Hämatologie mit Neuzulassungen und Pipeline-Präparaten (zweiwöchentlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="diabetesletter-cb" name="data[newsletter][diabetesletter]" value="1" <?= !empty($result_data["diabetesletter"]) ? "checked" : "" ?>>
                        DiabetesLetter für Ärzte - News aus der Diabetologie (zweiwöchentlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="pneumoletter-cb" name="data[newsletter][pneumoletter]" value="1" <?= !empty($result_data["pneumoletter"]) ? "checked" : "" ?>>
                        PneumoLetter für Fachärzte - News aus Pneumologie und Allergologie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="kardioletter-cb" name="data[newsletter][kardioletter]" value="1" <?= !empty($result_data["kardioletter"]) ? "checked" : "" ?>>
                        KardioLetter für Fachärzte - News aus Kardiologie und Angiologie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="neuroletter-cb" name="data[newsletter][neuroletter]" value="1" <?= !empty($result_data["neuroletter"]) ? "checked" : "" ?>>
                        NeuroLetter für Fachärzte - News aus Neurologie und Psychiatrie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="gastroletter-cb" name="data[newsletter][gastroletter]" value="1" <?= !empty($result_data["gastroletter"]) ? "checked" : "" ?>>
                        GastroLetter für Fachärzte - News aus Gastroenterologie und Hepatologie (monatlich)
                    </label>
                </div>
            </div>

			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="dermaletter-cb" name="data[newsletter][dermaletter]" value="1" <?= !empty($result_data["dermaletter"]) ? "checked" : "" ?>>
                        DermaLetter für Ärzte - News aus der Dermatologie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="paediatrieletter-cb" name="data[newsletter][paediatrieletter]" value="1" <?= !empty($result_data["paediatrieletter"]) ? "checked" : "" ?>>
                        PädiatrieLetter für Ärzte - News aus der Pädiatrie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="gynletter-cb" name="data[newsletter][gynletter]" value="1" <?= !empty($result_data["gynletter"]) ? "checked" : "" ?>>
                        GynLetter für Fachärzte - News aus der Gynäkologie (monatlich)
                    </label>
                </div>
            </div>

            <div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="infoletter-cb" name="data[newsletter][infoletter]" value="1" <?= !empty($result_data["infoletter"]) ? "checked" : "" ?>>
                        InfoLetter mit Cartoon fürs Praxisteam - Der gute Start in die Arbeitswoche (wöchentlich)
                    </label>
                </div>
            </div>
            <div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="honorarletter-cb" name="data[newsletter][honorarletter]" value="1" <?= !empty($result_data["honorarletter"]) ? "checked" : "" ?>>
                        Honorarletter für Niedergelassene - Abrechnungstipps sowie News zu EBM und GOÄ u.a. (monatlich)
                    </label>
                </div>
            </div>
            <div class="row form-group optin-row">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="radio" id="permission-granted-no" required name="data[permission-granted]" value="0" <?= get("new") ? "disabled" : "" ?>>
                        Nein, ich möchte doch keine Newsletter erhalten.
                    </label>
                </div>
            </div>
        </div>
        <br>
        <div class="row form-group submit">
            <div class="medium-12 columns">
                <input type="submit" name="submit" value="Absenden" class="button expanded submit mtde-button">
            </div>
        </div>
    </form>
<?php
}
include("template_parts/footer.php");
?>
