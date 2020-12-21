<?php

require("api.php");
$response = api("confirm");
if ($response["success"] === true) {
    header("Location: ../success.php?for=" . $_GET["type"] . "_success");
}else{
    header("Location: https://www.medical-tribune.de/profil/login/ ");
}

?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <link rel="stylesheet" type="text/css" href="../css/purified.css">
        <link rel="stylesheet" type="text/css" href="../css/style.css">
        <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans+Condensed:300,300i,700|Source+Sans+Pro:400,400i,700,700i&amp;amp;subset=latin-ext" media="all">
    </head>
    <body>
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
    </body>
</html>
