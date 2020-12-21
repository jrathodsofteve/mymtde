<?php

include("template_parts/head.html");

require_once("functions.php");

if (get("success") == "") die();

?>
<div class="row padded" style="max-width: 500px">
    <?php include("template_parts/header.html"); ?>
    <div class="row form-group">
        <div class="medium-12 columns">
            <?php if (get("success") == 0) { ?>
                Schade, dass wir Sie nicht mehr Informationen versorgen dürfen.<br />
				Fall Sie sich anders entscheiden, können Sie sich jederzeit wieder für unsere Newsletter anmelden.
            <?php } else if (get("success") == 1) { ?>
                Vielen Dank für Ihre Rückmeldung.<br />
				Sie erhalten in Kürze eine Bestätigung per E-Mail.
            <?php } else if (get("success") == 2) { ?>
                Vielen Dank für Ihre Newsletter-Anmeldung.
                Um diese zu bestätigen, klicken Sie bitte auf den Link in der Mail, die wir Ihnen soeben geschickt haben.
            <?php } else if (get("success") == 3) { ?>
                Ihre Newsletter-Anmeldung war erfolgreich!
            <?php } else die(); ?>
            <br><br>
            Mit freundlichen Grüssen<br>
            Ihr Team der Medical Tribune Verlagsgesellschaft mbH
        </div>
    </div>
    <br><br>
    <?php include("template_parts/foot.html"); ?>
</div>
