<?php

require_once("../php/api.php");

if (isset($_POST["data"])) {
    $cmd = isset($_POST["customer_id"]) ? ( isset($_POST["data"]["password"]) ? "change_password" : "update" ) : "register";
    $response = api($cmd);
    if ($response["success"]) {
        $_SESSION["success_message_for"] = $cmd;
        header("Location: index.php?" . http_build_query($_GET));
        die();
    }
}

$page = isset($_GET["page"]) && is_numeric($_GET["page"]) ? intval($_GET["page"]) : 1;
if ($page < 1) {
    $page = 1;
}

$query = $_GET;
unset($query["page"]);
$query = http_build_query($query);
if ($query) {
    $query .= "&";
}

// backend is always mode=1
$_GET["mode"] = 1;

include("head.php");

if(isset($_REQUEST['search']) && $_REQUEST['search'] == 'search') {
    api("saveFilter");
}
$result = api("getFilter");
if($result['data']['filter']) {
    $filters = $result['data']['filter'];
    ?>
<div class="stored-filters" style="display: none;">
    <textarea name="stored-filters" id="stored-filters" cols="30" rows="10">
                <?php echo $filters; ?>
            </textarea>
</div>
<?php
}
?>
<div class="row" id="customers-div">
    <div class="row form-group">
        <div class="medium-6 columns">
            <button class="button" type="button" id="new-user-button">Neuer Benutzer</button>
        </div>
        <div class="medium-6 columns" align="right">
            <a href="csv.php?<?= $query ?>">
                <button class="button" type="button">Als CSV exportieren</button>
            </a>
            <a href="javascript:;">
                <button class="button" id="toggle-advance-export" type="button">Advance CSV exportieren <i
                        class="fas fa-angle-down" style="margin-left: 10px;"></i></button>
            </a>
            <button class="button" type="button" id="toggle-search-button">Suche <i class="fas fa-angle-down"></i></button>
        </div>
    </div>
    <form id="advance-csv-div" method="POST" action="csv.php" style="display: none;">
        <input type="hidden" name="advance-csv" value="1" />
        <div class="row form-group filter-row default-row">
            <div class="medium-4 columns">
                <select name="filter-field[]">
                    <option value="">Wählen</option>
                    <option value="oc_customer.customer_group_id">Beruf</option>
                    <option value="status">Status</option>
                    <option value="country_id">Land</option>
                    <option value="work_area">Arbeitsbereich</option>
                    <option value="work_area_extra">Arbeitsbereich Zusatz</option>
                    <option value="association">Verband</option>
                    <option value="pharmacist_chamber">Apothekerkammer</option>
                    <option value="special_field" data-multiselect="1">Fachgebiet</option>
                    <option value="specialisation">Spezialisierung</option>
                    <option value="main_interests_1">1. Hauptinteressengebiet</option>
                    <option value="main_interests_2">2. Hauptinteressengebiet</option>
                    <option value="submit_method">Nachweisart</option>
                    <option value="form_of_employment">Form der Erwerbstätigkeit</option>
                    <option value="focus">Fokus</option>
                    <option value="subject_area">Fachrichtung</option>
                    <option value="dentist_chamber">Zahnärztekammer</option>
                    <option value="study_subject">Studienfach</option>
                    <option value="medical_association">Ärztekammer</option>

                    <option value="praxisletter" data-newsletter="">PraxisLetter</option>
                    <option value="onkoletter" data-newsletter="">OnkoLetter</option>
                    <option value="pneumoletter" data-newsletter="">PneumoLetter</option>
                    <option value="kardioletter" data-newsletter="">KardioLetter</option>
                    <option value="neuroletter" data-newsletter="">NeuroLetter</option>
                    <option value="gastroletter" data-newsletter="">GastroLetter</option>
                    <option value="infoletter" data-newsletter="">InfoLetter mit Cartoon</option>
                    <option value="honorarletter" data-newsletter="">HonorarLetter</option>
                    <option value="diabetesletter" data-newsletter="">DiabetesLetter</option>
                    <option value="paediatrieletter" data-newsletter="">PädiatrieLetter</option>
                    <option value="gynletter" data-newsletter="">GynLetter</option>
                    <option value="dermaletter" data-newsletter="">DermaLetter</option>
                </select>
            </div>
            <div class="medium-2 columns">
                <select name="compareBy[]">
                    <option value="=" selected>gleich</option>
                    <option value="!=">ungleich</option>
                </select>
            </div>
            <div class="medium-6 columns">
                <input type="text" name="compareValue[]">
                <select name="compareValue[]" disabled hidden></select>
            </div>
        </div>

        <div class="row form-group newsletter-rows">
            <div class="medium-3 columns">
                <div>
                    <select name="newletterOperator">
                        <option value="AND" selected="">UND</option>
                        <option value="OR">ODER</option>
                        <option value="XOR">ENTWEDER/ODER</option>
                    </select>
                </div>
            </div>
            <div class="csv-filter-outer">
                <div class="medium-4 columns">
                    <div class=" newsletter-checkboxes">
                        <label>
                            <input type="checkbox" name="selectAllNewsletter" value="" /> <b>Select All</b>
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[praxisletter]" value="1" /> PraxisLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[onkoletter]" value="1" /> OnkoLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[pneumoletter]" value="1" /> PneumoLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[kardioletter]" value="1" /> KardioLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[neuroletter]" value="1" /> NeuroLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[gastroletter]" value="1" /> GastroLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[infoletter]" value="1" /> InfoLetter mit Cartoon
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[honorarletter]" value="1" /> HonorarLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[diabetesletter]" value="1" /> DiabetesLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[paediatrieletter]" value="1" /> PädiatrieLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[gynletter]" value="1" /> GynLetter
                        </label>
                        <label>
                            <input type="checkbox" name="newsletter[dermaletter]" value="1" /> DermaLetter
                        </label>
                    </div>
                </div>
                <div class="medium-2 columns">
                    <select name="newsletterCompareBy">
                        <option value="=" selected>gleich</option>
                        <option value="!=">ungleich</option>
                    </select>
                </div>
                <div class="medium-6 columns">
                    <select name="newsletterCompareValue">
                        <option value="0">nicht abonniert</option>
                        <option value="1">abonniert</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row form-group search-row">
            <div class="medium-6 columns">
                <button type="submit" class="button expanded" value="export" name="export">Exportieren</button>
            </div>
            <div class="medium-3 columns">
                <button class="button add-news-letters" type="button">+ Newsletter</button>
            </div>
            <div class="medium-3 columns">
                <button class="button remove-search-row" type="button" disabled>-</button>
                <button class="button add-search-row" type="button">+</button>
            </div>
        </div>
    </form>

    <form id="search-div" method="get">
        <input type="hidden" name="indentations">
        <div class="row form-group search-row">
            <div class="medium-9 columns">
                <button type="submit" class="button expanded" value="search" name="search">Suche</button>
            </div>
            <div class="medium-3 columns">
                <div style="float:right;margin-left:30px">
                    <button class="button remove-search-row" type="button" disabled>-</button>
                    <button class="button add-search-row" type="button">+</button>
                </div>
                <div style="overflow:hidden">
                    <a href="index.php">
                        <button class="button expanded" value="search" type="button">Zurücksetzen</button>
                    </a>
                </div>
            </div>
        </div>
    </form>
    <div id="clone-search-div" hidden>
        <div class="row form-group logic-row">
            <div class="medium-3 columns">
                <button class="button indent-btn indent-btn-left" type="button" disabled><i
                        class="fas fa-caret-left"></i></button>
                <button class="button indent-btn indent-btn-right" type="button"><i
                        class="fas fa-caret-right"></i></button>
                <div style="overflow:hidden">
                    <select name="operator[]">
                        <option value="AND" selected>UND</option>
                        <option value="OR">ODER</option>
                        <option value="XOR">ENTWEDER/ODER</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row form-group filter-row">
            <div class="medium-4 columns">
                <select name="filter-field[]">
                    <option value="email" selected>E-Mail</option>
                    <option value="oc_customer.firstname">Vorname</option>
                    <option value="oc_customer.lastname">Nachname</option>
                    <option value="oc_customer.customer_group_id">Beruf</option>
                    <option value="telephone">Telefon</option>
                    <option value="status">Status</option>
                    <option value="date_added">Registrierungsdatum</option>
                    <option value="address_1">Adresse</option>
                    <option value="city">Stadt</option>
                    <option value="postcode">PLZ</option>
                    <option value="country_id">Land</option>
                    <option value="title_prefix">Titel (Präfix)</option>
                    <option value="title_suffix">Titel (Suffix)</option>
                    <option value="gender">Geschlecht</option>
                    <option value="birthday">Geburtstag</option>
                    <option value="work_area">Arbeitsbereich</option>
                    <option value="work_area_extra">Arbeitsbereich Zusatz</option>
                    <option value="association">Verband</option>
                    <option value="pharmacist_chamber">Apothekerkammer</option>
                    <option value="special_field">Fachgebiet</option>
                    <option value="specialisation">Spezialisierung</option>
                    <option value="main_interests_1">1. Hauptinteressengebiet</option>
                    <option value="main_interests_2">2. Hauptinteressengebiet</option>
                    <option value="efn">EFN</option>
                    <option value="submit_method">Nachweisart</option>
                    <option value="form_of_employment">Form der Erwerbstätigkeit</option>
                    <option value="expected_graduation_year">Voraussichtliches Abschlussjahr</option>
                    <option value="focus">Fokus</option>
                    <option value="subject_area">Fachrichtung</option>
                    <option value="dentist_chamber">Zahnärztekammer</option>
                    <option value="study_subject">Studienfach</option>
                    <option value="medical_association">Ärztekammer</option>
                    <option value="description">Beschreibung der Tätigkeit</option>
                </select>
            </div>
            <div class="medium-2 columns">
                <select name="compareBy[]">
                    <option value="=" selected>gleich</option>
                    <option value="!=">ungleich</option>
                    <option value="LIKE">enthält</option>
                    <option value="NOT LIKE">enthält nicht</option>
                    <option value=">">größer als</option>
                    <option value=">=">größer oder gleich</option>
                    <option value="<">kleiner als</option>
                    <option value="<=">kleiner oder gleich</option>
                </select>
            </div>
            <div class="medium-6 columns">
                <input type="text" name="compareValue[]">
                <select name="compareValue[]" disabled hidden></select>
            </div>
        </div>
    </div>

    <div id="customers-list">
        <?php
            $response = api("search");
            if (!$response["success"]) {
                echo "Ein Fehler ist aufgetreten: " . $response["error"];
                echo "<div class='debug'>" . json_encode($response) . "</div>";
            } else if (!isset($response["data"])) { ?>
        <div class="row no-results">
            <div class="medium-12 columns" align="center">
                <i>Keine Ergebnisse.</i>
            </div>
        </div>
        <?php
            } else {
                global $columns_list;
                echo "<div id='result-count'><i>" . $response["count"] . " Ergebnisse:</i></div>";
                echo "<div class='row form-group no-results'>";
                foreach ($columns_list as $col) {
                    echo "<div class='medium-" . $col["size"] . " columns'><b>" . (isset($col["header"]) ? $col["header"] : "") . "</b></div>";
                }
                echo "</div>";
                foreach ($response["data"] as $user) {
                    echo '<div class="row form-group" data-customer-id="' . $user["customer_id"] . '">';
                    foreach ($columns_list as $col) {
                        echo "<div class='medium-" . $col["size"] . " columns'>";
                        $args = array_map(function($field) use ($user) { return htmlspecialchars($user[$field]); }, $col["fields"]);
                        if (isset($col["func"])) echo call_user_func_array($col["func"], $args);
                        else                     echo implode(" ", $args);
                        echo "</div>";
                    }
                    echo "</div>";
                }
            }
            ?>
    </div>

    <div class="row form-group">
        <div class="medium-12 columns" align="center">
            <a id="prevBtn" class="button"
                <?= $page > 1 ? "href='?$query" . "page=" . ($page - 1) . "'" : "disabled" ?>>Zurück</a>
            <b id="pageNr"><?= $page ?></b>
            <a id="nextBtn" class="button"
                <?= isset($response["more_pages"]) && $response["more_pages"] ? "href='?$query" . "page=" . ($page + 1) . "'" : "disabled" ?>>Weiter</a>
        </div>
    </div>
</div>
<form method="post" id="admin_profile_form" class="row" validate enctype="multipart/form-data">
    <input id="customer-id" type="hidden" name="customer_id">
    <div class="row form-group profile-only">
        <div class="medium-3 columns">
            <label for="formhandler-comment" class="inline">
                Kommentar
            </label>
        </div>
        <div class="medium-9 columns">
            <textarea class="enabled-profile-only" id="formhandler-comment" name="data[comment]" rows="3"
                not-disabled></textarea>
        </div>
    </div>
    <div class="row form-group profile-only">
        <div class="medium-3 columns">
            <label for="formhandler-confirm_token" class="inline">
                Aktivierungstoken
            </label>
        </div>
        <div class="medium-9 columns">
            <input id="formhandler-confirm_token" name="data[confirm_token]" type="text" disabled no-enable>
        </div>
    </div>
    <div class="row form-group profile-only">
        <div class="medium-3 columns">
            <label for="formhandler-update_token" class="inline">
                Update-Token
            </label>
        </div>
        <div class="medium-9 columns">
            <input id="formhandler-update_token" name="data[update_token]" type="text" disabled no-enable>
        </div>
    </div>
    <div class="row form-group profile-only">
        <div class="medium-3 columns">
            <label for="formhandler-status" class="inline">
                Status
            </label>
        </div>
        <div class="medium-9 columns">
            <select id="formhandler-status" class="enabled-profile-only" name="data[status]" not-disabled>
                <?php
                    global $user_states;
                    foreach ($user_states as $key => $val) { 
                        echo "<option value='$key'>$val</option>";
                    }
                    ?>
            </select>
        </div>
    </div>
    <div class="row form-group">
        <div class="medium-3 columns">
            <label for="formhandler-email" class="inline">
                E-Mail <span>*</span>
            </label>
        </div>
        <div class="medium-9 columns">
            <input id="formhandler-email" name="data[email]" type="email" disabled>
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
    <div id="new_pw_div">
        <?php include("../templates/password-form.html") ?>
        <div class="row form-group" id="save_pw_div">
            <div class="medium-3 columns"></div>
            <div class="medium-9 columns">
                <button type="button" class="button" id="cancel_pw_change_button">Abbrechen</button>
                <button type="submit" class="button" id="save_pw_change_button">Speichern</button>
            </div>
        </div>
    </div>

    <?php include("../templates/form.php"); ?>

    <div class="form-group check">
        <div class="row form-group">
            <div class="medium-3 columns">
                <span>Newsletter-Abos:</span>
            </div>
            <div class="medium-9 columns">
                <?php include("../templates/newsletters.php"); ?>
            </div>
        </div>
    </div>
    <br>
    <div id="submit_profile_div">
        <div class="row form-group submit">
            <div class="medium-12 columns">
                <input type="submit" id="form_submit" name="data[submit]" value="Speichern"
                    class="button expanded submit">
            </div>
        </div>
        <div class="row form-group submit">
            <div class="medium-12 columns">
                <p class="notice">Die Felder, die mit einem Asterisk bzw. Stern ( * ) markiert sind, müssen ausgefüllt
                    werden.</p>
            </div>
        </div>
    </div>
    <div class="row form-group delete-row profile-only">
        <div class="medium-12 columns">
            <small><a id="details-delete-button" class="delete-button">Konto löschen</a></small>
        </div>
    </div>
</form>
</body>

</html>