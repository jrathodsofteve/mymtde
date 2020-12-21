<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include("template_parts/head.html");

require_once("functions.php");

if (get("success") == "") die();
$error = init();
if ($error) die(var_dump($error));

?>
<div class="row padded" style="max-width: 500px">
    <?php include("template_parts/header.html"); ?>
    <div class="row form-group">
        <div class="medium-12 columns">
            <?php if (get("success") == 0) { ?>
                Schade, dass wir Sie nicht mehr Informationen versorgen dürfen.<br />
				Fall Sie sich anders entscheiden, können Sie sich jederzeit wieder für unsere Newsletter anmelden.
            <?php } else if (get("success") == 1) { ?>
              <?php
              $email = $_GET["email"];
              $sql = mysqli_query($conn, "INSERT INTO postalcard_confirmations (email, praxisletter, onkoletter, pneumoletter, kardioletter, neuroletter, gastroletter, infoletter, honorarletter, diabetesletter, dermaletter, paediatrieletter, gynletter)
                SELECT email, praxisletter, onkoletter, pneumoletter, kardioletter, neuroletter, gastroletter, infoletter, honorarletter, diabetesletter, dermaletter, paediatrieletter, gynletter
                FROM postalcard_confirmations_temp WHERE email='$email'");
              $sql = mysqli_query($conn, "DELETE FROM postalcard_confirmations_temp WHERE email='$email'");

              //CLEVERREACH UPDATE
              /*$sql = "SELECT * FROM postalcard_confirmation WHERE email='$email'";
              $result = $conn->query($sql);
              $data = fetch_assoc($result);
              $result = modifyNewsletters(NEWSLETTER_MODE_UPDATE);*/
               ?>
                Vielen Dank für Ihre Bestätigung.<br />
				Sie erhalten von uns ab dem heutigen Tag folgende Newsletter per Mail: <br> <br>
        <ul>


        <?php
        $realNames = [
            "praxisletter" => "Praxisletter",
            "onkoletter" => " Onkoletter",
    		"pneumoletter" => " Pneumoletter",
    		"kardioletter" => " Kardioletter",
    		"neuroletter" => " Neuroletter",
    		"gastroletter" => " Gastroletter",
    		"infoletter" => " Infoletter",
    		"honorarletter" => " Honorarletter",
    		"diabetesletter" => " Diabetesletter",
            "paediatrieletter" => "PädiatrieLetter",
            "gynletter" => "GynLetter",
            "dermaletter" => "DermaLetter"
        ];
        foreach($realNames as $key => $value){
          $nl = $_GET[$key];

          if(!empty($nl)){
            echo '<li>'.$value.'</li>';
            $rest->put("/groups/" . $write_ids[$key] . "/receivers/" . $email . "/setactive");
          }
        }
         ?>
         </ul>
            <?php } else if (get("success") == 2) { ?>
                Vielen Dank für Ihre Newsletter-Anmeldung.
                Um diese zu bestätigen, klicken Sie bitte auf den Link in der Mail, die wir Ihnen soeben geschickt haben.
            <?php } else if (get("success") == 3) { ?>
                Ihre Newsletter-Anmeldung war erfolgreich!
            <?php } else die(); ?>
            <br><br>
            Mit freundlichen Grüßen<br>
            Ihr Team der Medical Tribune Verlagsgesellschaft mbH
        </div>
    </div>
    <br><br>
    <?php include("template_parts/foot.html"); ?>
</div>
