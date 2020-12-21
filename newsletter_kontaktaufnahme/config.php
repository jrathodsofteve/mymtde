<?php

define("SERVER_ROOT", "https://my.medical-tribune.de/postalcard_confirmation/");

// db stuff
define("DB_SERVER", "localhost");
define("DB_USERNAME", "mtde_confirm_new");
define("DB_PASSWORD", "?9MGl!cGTQFb");
define("DB_DB", "mtde_confirm_newsletters");

// cleverreach stuff
define("CR_CLIENT_ID", 23622);
define("CR_USERNAME", "soft-evolution");
define("CR_PASSWORD", "061FGtboccWc");

// mail stuff
define("MAIL_SENDER", "registrierung@medical-tribune.de");
define("MAIL_SENDER_NAME", "Medical Tribune");
define("MAIL_SMTP_HOST", "mail.medical-tribune.de");
define("MAIL_SMTP_USERNAME", "registrierung@medical-tribune.de");
define("MAIL_SMTP_PASSWORD", "].0TbKxf)R_^");

$read_ids = [
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
    "gynletter" => 494035
];
$write_ids = [
    "praxisletter" => 491208,
    "onkoletter" => 491225,
    "pneumoletter" => 491226,
    "kardioletter" => 491229,
    "neuroletter" => 491230,
    "gastroletter" => 491233,
    "infoletter" => 491235,
    "honorarletter" => 491237,
    "diabetesletter" => 491238,
    "dermaletter" => 494033,
    "paediatrieletter" => 494034,
    "gynletter" => 494035
];

$remove_from = [];

?>
