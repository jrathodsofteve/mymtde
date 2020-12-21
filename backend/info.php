<?php

include("head.php");

$response = api("get/count");
if (!$response["success"]) {
    echo "Ein Fehler ist aufgetreten: " . (isset($response["error"]) ? $response["error"] : json_encode($response));
    echo "<div style='display:none'>" . json_encode($response) . "</div>";
}

?>

    <div class="row">
        <div class="row form-group">
            <div class="medium-9 columns">
                <label class="inline">Gesamtzahl registrierter Nutzer:</label>
            </div>
            <div class="medium-3 columns">
                <label class="inline"><?= $response["data"][0][0] ?></label>
            </div>
        </div>
        <br>
        <?php foreach ($count_cols as $pp => $cb) { ?>
            <div class="row form-group">
                <div class="medium-4 columns">
                    <label class="inline">...nach <?= $pp ?>:</label>
                </div>
                <div class="medium-4 columns">
                    <select id="count-by-<?= $cb ?>"></select>
                </div>
                <div class="medium-1 columns"></div>
                <div class="medium-3 columns">
                    <label class="inline" id="user-count-by-<?= $cb ?>"></label>
                </div>
            </div>
        <?php } ?>
        <br>
        <div class="row form-group">
            <div class="medium-1 columns">
                <label class="inline">Logins:</label>
            </div>
            <div class="medium-3 columns">
                <select id="logins-count-time">
                    <option value="today" selected>Heute</option>
                    <option value="last_week">Letzte Woche</option>
                    <option value="last_month">Letzter Monat</option>
                    <option value="timespan">Zeitraum</option>
                </select>
            </div>
            <div class="medium-2 columns">
                <input type="text" id="logins-count-from" readonly>
            </div>
            <div class="medium-2 columns">
                <input type="text" id="logins-count-to" readonly>
            </div>
            <div class="medium-1 columns"></div>
            <div class="medium-3 columns">
                <label class="inline" id="logins-count"></label>
            </div>
        </div>
    </div>
</body>
</html>