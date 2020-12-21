<?php

require_once("libs/rest_client.php");
require_once("config.php");

$rest = $conn = null;

function get($name, $src = null) {
    if (!$src) $src = $_GET;
    return isset($src[$name]) ? $src[$name] : "";
}

function init() {
    global $rest, $conn;

    if (!$conn) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DB);
        if ($conn->connect_error) {
            return $conn->connect_error;
        }
        $conn->set_charset("utf8");
    }

    if (!$rest) {
        $rest = new CR\tools\rest("https://rest.cleverreach.com/v2");
        $rest->throwExceptions = true;
        try {
            $token = $rest->post('/login', 
                array(
                    "client_id" => CR_CLIENT_ID,
                    "login" => CR_USERNAME,
                    "password" => CR_PASSWORD
                )
            );
            $rest->setAuthMode("jwt", $token);
        } catch (\Exception $e){
            return $e;
        }
    }
}

function sendEmail($subject, $mail, $body) {
    require_once('libs/phpmailer/Exception.php');
    require_once('libs/phpmailer/OAuth.php');
    require_once('libs/phpmailer/SMTP.php');
    require_once('libs/phpmailer/POP3.php');
    require_once('libs/phpmailer/PHPMailer.php');

    PHPMailer\PHPMailer\PHPMailer::$validator = "html5";        // to avoid conflicts between the form and the mailer
    $email = new PHPMailer\PHPMailer\PHPMailer();
    // $email->IsSMTP();
    $email->SMTPSecure = false;
    $email->Port = 25;
    $email->Host = MAIL_SMTP_HOST;
    $email->SMTPAuth = false;
    $email->Username = MAIL_SMTP_USERNAME;
    $email->Password = MAIL_SMTP_PASSWORD;
    $email->CharSet = 'UTF-8';
    $email->IsHTML(true);
    $email->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $email->From = MAIL_SENDER;
    $email->FromName = MAIL_SENDER_NAME;
    $email->Subject = $subject;
    $email->Body = $body;
    // $email->SMTPDebug = 4;

    if ($email->AddEmbeddedImage(dirname(__FILE__) . '/img/mtde_logo.png', 'logo') === false) {
        return "Adding image failed: " . $email->ErrorInfo;
    }                                           
    if ($email->AddAddress($mail) === false) {
        return "Adding mail receiver failed: " . $email->ErrorInfo;
    }
    if ($email->Send() === false) {
        return "Sending email failed: " . $email->ErrorInfo;
    }
}

function constructMailReceiver($data = null) {
    if (!$data) $data = $_POST["data"];

    $gender = get("gender", $data);
    if ($gender == "w") $salutation = "Dear Mrs.";
    else if ($gender == "m") $salutation = "Dear Mr.";
    else $salutation = "Dear reader";

    return array(
        "email"			=> get("email", $data),
        "registered"	=> time(),
        "activated"		=> time(),
        "attributes"	=> array(
                            "salutation" => $salutation,
                            "firstname" => get("firstname", $data),
                            "lastname" => get("lastname", $data),
                            "spm_id" => get("spm_id", $data),
                            "spm_source" => get("spm_source", $data),
                        )
    );
}

function getNLString($arr) {
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
    $str = implode("<br>", array_map(function($name) use ($realNames) { return $realNames[$name]; }, $arr));
    if (!$str) {
        $str = "Keine";
    }
    return $str;
}

function isRegistered($id, $email) {
    global $rest;
    $errors = [];
    try {
        return $rest->get("/groups/{$id}/receivers/" . $email);
    } catch (\Exception $e){
        if (json_decode($rest->error)->error->code !== 404) {
            $errors[] = [
                "exception" => $e,
                "rest_error" => $rest->error
            ];
        }
    }
    if (count($errors)) die(var_dump($errors));
    return false;
}

// newsletter constants
define("NEWSLETTER_MODE_ADD", 0);
define("NEWSLETTER_MODE_UPDATE", 1);
define("NEWSLETTER_MODE_DELETE", 2);
define("NEWSLETTER_MODE_DELETE_REAL", 3);

function modifyNewsletters($mode, $nl, $data = null, $is_ids = false) {
    if (count($nl) == 0) {
        return;
    }

    $response = [];
    global $write_ids, $rest;

    foreach ($nl as $key => $val) {
        if ($is_ids) {
            $group_id = $val;
        } else if (isset($write_ids[$val])) {
            $group_id = $write_ids[$val];
        } else if (isset($write_ids[$key])) {
            $group_id = $write_ids[$key];
        } else {
            $response["errors"][] = "invalid group id: $key/$val";
            continue;
        }

        $receiver = constructMailReceiver($data);
        $registered = isRegistered($group_id, $receiver["email"]);
        try {
            if ($mode == NEWSLETTER_MODE_ADD) {
                if (!$registered) {
                    $rest->post("/groups/" . $group_id . "/receivers", $receiver);
                } else {
                    $rest->put("/groups/" . $group_id . "/receivers/" . $receiver['email'] . "/setactive");
                    $rest->put("/groups/" . $group_id . "/receivers/" . $receiver["email"], $receiver);
                }
            } else if ($mode == NEWSLETTER_MODE_UPDATE && $registered) {
                $rest->put("/groups/" . $group_id . "/receivers/" . $receiver["email"], $receiver);
            } else if ($mode == NEWSLETTER_MODE_DELETE && $registered) {
                $rest->put("/groups/" . $group_id . "/receivers/" . $receiver["email"] . "/setinactive");
            } else if ($mode == NEWSLETTER_MODE_DELETE_REAL && $registered) {
                $rest->delete("/groups/" . $group_id . "/receivers/" . $receiver["email"]);
            }
        } catch (\Exception $e){
            $response["errors"][] = [
                "exception" => $e,
                "rest_error" => $rest->error
            ];
        }
    }
    return $response;
}

function token($length = 32) {
    $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max = strlen($string) - 1;
    $token = '';
    for ($i = 0; $i < $length; $i++) {
        $token .= $string[mt_rand(0, $max)];
    } 
    return $token;
}

ob_start();
include("template_parts/foot.html");
$foot = ob_get_clean();

define("NEWSLETTERS_CONFIRMED_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
<h3>Neue Datenschutz-Grundverordnung (DSGVO)</h3>
Vielen Dank für Ihre Bestätigung.<br>
Wir danken Ihnen für die Einwilligung, dass wir weiterhin mit Ihnen in Kontakt bleiben dürfen.<br>
Sie haben folgende Newsletter abonniert:<br><br>
%s
<br><br>
Sie können diese Einwilligung jederzeit widerrufen. Darüber hinaus enthält jede unserer Mails an Sie die Möglichkeit, sich abzumelden.<br>
Mit freundlichen Grüßen,<br>
Ihr Team der Medical Tribune
<br><br>
Medical Tribune Verlagsgesellschaft mbH,
Unter den Eichen 5, 65195 Wiesbaden, Telefon 0611 9746-0, online@medical-tribune.de, www.medical-tribune.de, Registergericht Amtsgericht Wiesbaden, HRB 12808, Umsatzsteueridentifikationsnummer
DE206862684, Geschäftsführer: Alexander Paasch, Dr. Karl Ulrich
<br><br>
HTML
. $foot
);

define("CONFIRM_NEWSLETTERS_TEMPLATE", <<<HTML
<img src="cid:logo">
<br><br>
Vielen Dank für Ihre Newsletter-Anmeldungen.<br>
Sie haben folgende Newsletter abonniert:<br><br>
%s
<br><br>
Um die Anmeldung abzuschließen, klicken Sie bitte auf folgenden Link:
<br><br>
<a href="%s">Hier klicken</a>
<br><br>
Mit freundlichen Grüßen,<br>
Ihr Team der Medical Tribune
<br><br>
Medical Tribune Verlagsgesellschaft mbH,
Unter den Eichen 5, 65195 Wiesbaden, Telefon 0611 9746-0, online@medical-tribune.de, www.medical-tribune.de, Registergericht Amtsgericht Wiesbaden, HRB 12808, Umsatzsteueridentifikationsnummer
DE206862684, Geschäftsführer: Alexander Paasch, Dr. Karl Ulrich
<br><br>
<br><br>
HTML
. $foot
);

?>