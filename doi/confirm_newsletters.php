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

        $sql = "INSERT INTO confirmations
                    (email, spm_id, spm_source" . implode(", ", array_merge([""], $valid_newsletters)) . ")
                VALUES 
                    ('" . $conn->escape_string($data["email"]) . "', " .
                    "'" . $conn->escape_string($data["spm_id"]) . "', " .
                    "'" . $conn->escape_string($data["spm_source"]) . "'" .
                    implode(", ", array_merge([""], array_fill(0, count($valid_newsletters), 1))) . ")";
        $result = $conn->query($sql);
        if (!$result) die($conn->error . "\n" . $sql);

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

            $error = sendEmail("Danke für Ihre Bestätigung", $data["email"], sprintf(NEWSLETTERS_CONFIRMED_TEMPLATE, getNLString($valid_newsletters)));
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

        $error = sendEmail("Bitte bestätigen Sie Ihre Newsletter-Anmeldungen", $data["email"], 
                    sprintf(CONFIRM_NEWSLETTERS_TEMPLATE, 
                                getNLString($valid_newsletters),
                                SERVER_ROOT . "confirm.php?token=$token"));
        if ($error) die($error);
        header("Location: success.php?success=2");
    }
    die();
} else {
    if (empty($_GET["new"])) {
        $ndata = [];
        foreach ($read_ids as $name => $id) {
            $ndata[$name] = (bool)isRegistered($id, get("email"));
        }
    } else {
        $ndata = array_fill_keys(array_keys($write_ids), true);
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
    <form method="post" class="row padded">
        <?php include("template_parts/header.html"); ?>
        <div class="row form-group info-row">
            <div class="medium-12 columns">
                Wir möchten mit Ihnen in Kontakt bleiben.
            </div>
        </div>
        <br>
        <input type="hidden" name="data[spm_id]" value="<?= get("spm_id") ?>">
        <input type="hidden" name="data[spm_source]" value="<?= get("spm_source") ?>">
        <div class="row form-group">
            <div class="medium-3 columns">
                <label class="inline">
                    E-Mailadresse:<span>*</span>
                </label>
            </div>
            <div class="medium-9 columns">
                <input type="email" required name="data[email]" value="<?= get("email") ?>">
            </div>
        </div>
        <br>
        <div class="row form-group info-row">
            <div class="medium-12 columns">              
				Nach Inkrafttreten der DSGVO am 25.05.18 werden wir Ihnen nur noch Informationen zusenden, wenn wir dafür Ihre Zustimmung haben. Diese Einwilligung können Sie jederzeit widerrufen.
            </div>
        </div>
        <div class="<?= get("new") ? "optin" : "" ?>">
            <div class="row form-group optin-row">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="radio" id="permission-granted-yes" required name="data[permission-granted]" value="1" <?= get("new") ? "disabled" : "" ?>>
                        Ja, ich bin damit einverstanden, dass die Medical Tribune Verlagsgesellschaft mbH mit mir in Kontakt bleibt.
                    </label>
                </div>
            </div>
            <div class="row form-group indented info-row">
                <div class="medium-12 columns">
                    Die folgenden kostenlosen und jederzeit abbestellbaren Newsletter möchte ich (weiterhin) erhalten:
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
                        <input type="checkbox" id="praxisletter-cb" name="data[newsletter][praxisletter]" value="1" <?= !empty($ndata["praxisletter"]) ? "checked" : "" ?>>
                        PraxisLetter für Ärzte - News zu Medizin, Praxisführung und Arzneimittelmarkt (wöchentlich)  
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="onkoletter-cb" name="data[newsletter][onkoletter]" value="1" <?= !empty($ndata["onkoletter"]) ? "checked" : "" ?>>
                        OnkoLetter für Fachärzte - News aus Onkologie und Hämatologie mit Neuzulassungen und Pipeline-Präparaten (zweiwöchentlich) 
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="diabetesletter-cb" name="data[newsletter][diabetesletter]" value="1" <?= !empty($ndata["diabetesletter"]) ? "checked" : "" ?>>
                        DiabetesLetter für Ärzte - News aus der Diabetologie (zweiwöchentlich) 
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="pneumoletter-cb" name="data[newsletter][pneumoletter]" value="1" <?= !empty($ndata["pneumoletter"]) ? "checked" : "" ?>>
                        PneumoLetter für Fachärzte - News aus Pneumologie und Allergologie (monatlich) 
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="kardioletter-cb" name="data[newsletter][kardioletter]" value="1" <?= !empty($ndata["kardioletter"]) ? "checked" : "" ?>>
                        KardioLetter für Fachärzte - News aus Kardiologie und Angiologie (monatlich) 
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="neuroletter-cb" name="data[newsletter][neuroletter]" value="1" <?= !empty($ndata["neuroletter"]) ? "checked" : "" ?>>
                        NeuroLetter für Fachärzte - News aus Neurologie und Psychiatrie (monatlich) 
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="gastroletter-cb" name="data[newsletter][gastroletter]" value="1" <?= !empty($ndata["gastroletter"]) ? "checked" : "" ?>>
                        GastroLetter für Fachärzte - News aus Gastroenterologie und Hepatologie (monatlich) 
                    </label>
                </div>
            </div>

			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="dermaletter-cb" name="data[newsletter][dermaletter]" value="1" <?= !empty($ndata["dermaletter"]) ? "checked" : "" ?>>
                        DermaLetter für Ärzte - News aus der Dermatologie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="paediatrieletter-cb" name="data[newsletter][paediatrieletter]" value="1" <?= !empty($ndata["paediatrieletter"]) ? "checked" : "" ?>>
                        PädiatrieLetter für Ärzte - News aus der Pädiatrie (monatlich)
                    </label>
                </div>
            </div>
			<div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="gynletter-cb" name="data[newsletter][gynletter]" value="1" <?= !empty($ndata["gynletter"]) ? "checked" : "" ?>>
                        GynLetter für Fachärzte - News aus der Gynäkologie (monatlich)
                    </label>
                </div>
            </div>

            <div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="infoletter-cb" name="data[newsletter][infoletter]" value="1" <?= !empty($ndata["infoletter"]) ? "checked" : "" ?>>
                        InfoLetter mit Cartoon fürs Praxisteam - Der gute Start in die Arbeitswoche (wöchentlich) 
                    </label>
                </div>
            </div>
            <div class="row form-group indented">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="checkbox" id="honorarletter-cb" name="data[newsletter][honorarletter]" value="1" <?= !empty($ndata["honorarletter"]) ? "checked" : "" ?>>
                        Honorarletter für Niedergelassene - Abrechnungstipps sowie News zu EBM und GOÄ u.a. (monatlich)
                    </label>
                </div>
            </div>
            <div class="row form-group optin-row">
                <div class="medium-12 columns">
                    <label class="inline">
                        <input type="radio" id="permission-granted-no" required name="data[permission-granted]" value="0" <?= get("new") ? "disabled" : "" ?>>
                        Nein, ich möchte keine Newsletter mehr erhalten.
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
