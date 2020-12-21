<?php

require_once("api.php");

define("MAX_PAGE_SIZE", 5000);

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DB);
if ($conn->connect_error) {
    var_dump($conn->connect_error);
    exit(1);
} else {
    $conn->set_charset("utf8");
}

// echo "<pre>";
foreach ($cr_group_ids as $name => $id) {
    $cr_data = [];
    $page = 0;
    do {
        try {
            $users = $rest->get("/groups/$id/receivers", ["pagesize" => MAX_PAGE_SIZE, "page" => $page++, "activeonly" => true]);
        } catch (\Exception $e) {
            $error = [
                "exception" => $e,
                "rest_error" => $rest->error
            ];
            var_dump($error);
            exit(1);
        }
        $cr_data = array_merge($cr_data, array_map(function($user) { return $user->email; }, $users));
    } while (count($users) == MAX_PAGE_SIZE);

    // var_dump($cr_data);
    $cr_data = array_flip($cr_data);

    $sql = "SELECT oc_customer.customer_id, email, $name FROM oc_customer NATURAL JOIN oc_customer_infos WHERE status=1";
    $result = $conn->query($sql);
    if (!$result || $result->num_rows === 0) {
        var_dump($conn->error, "no results", $sql);
        exit(1);
    }
    for ($data = array(); $tmp = $result->fetch_array();) $data[] = $tmp; // fetch_all equivalent

    $emails = array_map(function($e) { return $e[1]; }, $data);
    $values = array_map(function($e) { return [$e[2], $e[0]]; }, $data);    // pairs of value and db id

    $db_data = array_combine($emails, $values);
    
    $to_add = array_filter(array_intersect_key($db_data, $cr_data), function($e) { return !$e[0]; });
    $to_del = array_filter(array_diff_key($db_data, $cr_data), function($e) { return $e[0]; });


    foreach ([$to_del, $to_add] as $val => $arr) {
        if (count($arr)) {
            $sql_arr = array_map(function($e) { return "customer_id='{$e[1]}'"; }, $arr);
            $sql = implode(" OR ", $sql_arr);
            $sql = "UPDATE oc_customer_infos SET $name=$val WHERE $sql";
    
            $result = $conn->query($sql);
            if (!$result) {
                var_dump("error executing update", $conn->error, $sql);
                exit(1);
            }
        }
    }

    // output
    echo "Newsletter: " . $name . " (" . $id . ")\n";
    echo "Added: " . count($to_add) . "\n";
    echo "Removed: " . count($to_del) . "\n";
    echo "\n";

}
// echo "</pre>";

?>
