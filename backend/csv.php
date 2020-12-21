<?php

require_once("../php/api.php");

$response = api("search/details");

$fp = fopen('php://output', 'w');
if ($fp && $response["success"]) {
    header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    fputs($fp, "\xEF\xBB\xBF");
    fputcsv($fp, $response["headers"]);
    if (isset($response["data"])) {
        foreach ($response["data"] as $row) {
            fputcsv($fp, array_values($row));
        }
    }
} else {
    echo "Ein Fehler ist aufgetreten: " . (isset($response["error"]) ? $response["error"] : json_encode($response));
    echo "<div style='display:none'>" . json_encode($response) . "</div>";
}

?>