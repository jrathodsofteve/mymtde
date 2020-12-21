<?php

// website for mail
define("WEBSITE_ROOT", "https://my.medical-tribune.de");

// email for documents
define("DOC_EMAIL", "online@medical-tribune.de");
//define("DOC_EMAIL2", "abo-service@medical-tribune.de");

// db stuff
define("DB_SERVER", "46.232.181.187");
define("DB_USERNAME", "mtde_shop");
define("DB_PASSWORD", "jo%x*U2qq)Q2");
define("DB_DB", "mtde_shop");

// cleverreach stuff
define("CR_CLIENT_ID", 23622);
define("CR_USERNAME", "soft-evolution");
define("CR_PASSWORD", "061FGtboccWc");
// cleverreach group ids
$cr_group_ids_read = array(
    "praxisletter" => 163331,
    "onkoletter" => 163332,
    "pneumoletter" => 469963,
    "kardioletter" => 469960,
    "neuroletter" => 469961,
    "gastroletter" => 483467,
    "infoletter" => 163333,
    "honorarletter" => 163334,
    "diabetesletter" => 486615,
    "dermaletter" => 494033,
    "paediatrieletter" => 494034,
    "gynletter" => 494035,
	"rheumaletter" => 509789
);
$cr_group_ids = array(
    "praxisletter" => 491208,
    "cmeletter" => 514892,
    "onkoletter" => 491225,
    "pneumoletter" => 491226,
    "coronaletter" => 513623,
    "kardioletter" => 491229,
    "neuroletter" => 491230,
    "gastroletter" => 491233,
    "infoletter" => 491235,
    "honorarletter" => 491237,
    "diabetesletter" => 491238,
    "dermaletter" => 494033,
    "paediatrieletter" => 494034,
    "gynletter" => 494035,
	"rheumaletter" => 509789
);
$exkl_letter_ids = array(
    'praxisletter' => 510064,
    'cmeletter' => 514893,
    'onkoletter' => 510065,
    "coronaletter" => 513623,
    'diabetesletter' => 510066,
    'pneumoletter' => 510067,
    'kardioletter' => 510068,
    'neuroletter' => 510077,
    'gastroletter' => 510078,
    'dermaletter' => 510079,
    'rheumaletter' => 510080,
    'paediatrieletter' => 510081,
    'gynletter' => 510082,
    'infoletter' => 510083,
    'honorarletter' => 510084
);

// mail stuff
define("MAIL_SENDER", "registrierung@medical-tribune.de");
define("MAIL_SENDER_NAME", "Medical Tribune");
define("MAIL_SMTP_HOST", "mail.medical-tribune.de");
define("MAIL_SMTP_USERNAME", "registrierung@medical-tribune.de");
define("MAIL_SMTP_PASSWORD", "].0TbKxf)R_^");

?>
