<?php

require_once("functions.php");

init();

if (!isset($_GET["token"])) die("Link error.");

$sql = "SELECT * FROM subscribe_tokens WHERE token='" . $conn->escape_string(get("token")) . "'";
$result = $conn->query($sql);
if (!$result) die($conn->error . "\n" . $sql);
if ($result->num_rows == 0) die("Invalid token.");

$user = $result->fetch_assoc();

global $write_ids;
$nl = array_filter(array_intersect_key($user, $write_ids));

$sql = "INSERT INTO confirmations
            (email, spm_id, spm_source" . implode(", ", array_merge([""], array_keys($nl))) . ")
        VALUES 
            ('" . $conn->escape_string($user["email"]) . "', " .
            "'" . $conn->escape_string($user["spm_id"]) . "', " .
            "'" . $conn->escape_string($user["spm_source"]) . "'" .
            implode(", ", array_merge([""], $nl)) . ")";
$result = $conn->query($sql);
if (!$result) die($conn->error . "\n" . $sql);

$sql = "DELETE FROM subscribe_tokens WHERE id=" . $user["id"];
$result = $conn->query($sql);
if (!$result) die($conn->error . "\n" . $sql);

$result = modifyNewsletters(NEWSLETTER_MODE_ADD, $nl, $user);
if (count($result) > 0) die(var_dump($result));

header("Location: success.php?success=3");
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
                <div hidden>
                    <?= json_encode($response) ?>
                </div>
            </div> 
        </div>
    </body>
</html>
