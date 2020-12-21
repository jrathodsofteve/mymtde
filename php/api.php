<?php

#region init
require_once("config.php");
require_once("defines.php");
require_once("mail_templates.php");

if (session_status() != PHP_SESSION_ACTIVE) {
    session_start();
}

require_once("rest_client.php");
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
    var_dump($e);
    die();
}

if (isset($_POST["cmd"])) {
    $response = api();
    die(json_encode($response));
}
#endregion

function api($cmd = NULL) {
    $response = ["success" => false];
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DB);
    if ($conn->connect_error) {
        $response["error"] = $conn->connect_error;
    } else {
        $conn->set_charset("utf8");
        $return = parseCall($conn, $response, $cmd);
        if (is_string($return)) {
            $response["error"] = $return;
        }
    }
    return $response;
}

function parseCall($conn, &$response, $cmd) {
    if (!isset($_POST)) {
        $response["error"] = "no post args";
        return;
    }
    if ($cmd) {
        //...
    } else if (isset($_POST["cmd"])) {
        $cmd = $_POST["cmd"];
    } else {
        $response["error"] = "no cmd";
        return;
    }
    $cmd = explode("/", $cmd);

    switch ($cmd[0]) {
        case "get":
            if ($cmd[1] == "countries") {
                $sql = "SELECT country_id AS id, name AS val FROM oc_country";
            } else if ($cmd[1] == "jobs") {
                $sql = "SELECT customer_group_id AS id, title AS val FROM oc_customer_group";
            } else if ($cmd[1] == "data") {
                if (!isset($_POST["email"]) && !isset($_SESSION["id"])) {
                    $response["error"] = "you must be logged in to do this action";
                    return;
                }
                $sql = "SELECT oc_customer.firstname, oc_customer.lastname, customer_group_id AS job, email, telephone, password,
                               address_1 AS address, city, postcode, country_id AS country, oc_customer.custom_field, update_token,
                               status, doc_status, oc_customer_infos.*
                        FROM oc_customer
                            LEFT JOIN oc_address ON oc_customer.address_id = oc_address.address_id
                            LEFT JOIN oc_customer_infos ON oc_customer.customer_id = oc_customer_infos.customer_id
                        WHERE oc_customer.email='" . $conn->escape_string($_POST["email"]) . "'
                        OR oc_customer.customer_id='" . (isset($_SESSION["id"]) ? $_SESSION["id"] : "") . "'
                        OR oc_customer.customer_id='" . $conn->escape_string($_POST["email"]) . "'";
            } else if ($cmd[1] == "count") {
                if (!isset($_SESSION["id"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
                    return "you must be an admin for this action";
                }

                $sql = "SELECT COUNT(*) FROM oc_customer
                        LEFT JOIN oc_customer_infos
                               ON oc_customer.customer_id=oc_customer_infos.customer_id
                        LEFT JOIN oc_address
                               ON oc_customer.address_id=oc_address.address_id";
                if (isset($cmd[2])) {
                    if (!isset($_POST["value"])) {
                        return "No info specified";
                    }
                    global $count_cols;
                    if ($cmd[2] == "newsletter") {
                        global $cr_group_ids;
                        if (isset($cr_group_ids[$_POST["value"]])) {
                            $sql .= " WHERE `" . $conn->escape_string($_POST["value"]) . "`=1";
                        } else {
                            return "No valid newsletter specified";
                        }
                    } else if (in_array($cmd[2], $count_cols)) {
                        $sql .= " WHERE " . $cmd[2] . "='" . $conn->escape_string($_POST["value"]) . "'";
                    } else {
                        return "Invalid criteria";
                    }
                }
            } else if ($cmd[1] == "logins") {
                if (!isset($_SESSION["id"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
                    return "you must be an admin for this action";
                }
                if (!isset($_POST["from"]) || !isset($_POST["to"])) {
                    return "missing data";
                }

                $sql = "SELECT SUM(logins) FROM oc_customer_log
                        WHERE date >= STR_TO_DATE('" . $conn->escape_string($_POST["from"]) . "', '%d.%m.%Y')
                          AND date <= STR_TO_DATE('" . $conn->escape_string($_POST["to"])   . "', '%d.%m.%Y')";
            } else {
                $response["error"] = "unknown cmd[1]: " . $cmd[1];
                return;
            }
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows == 0 && $cmd[1] != "users") {
                $response["error"] = "No results";
                $response["query"] = $sql;
                return;
            }
            $fetching = ($cmd[1] == "count" || $cmd[1] == "logins") ? MYSQLI_BOTH : MYSQLI_ASSOC;

            while ($elem = $result->fetch_array($fetching)) {
                if ($cmd[1] == "data") {
                    if (!isset($_POST["email"]) && $_SESSION["pw_hash"] != $elem["password"]) {
                        unset($response["data"]);
                        $response["error"] = "wrong password";
                        return;
                    }
                    $cf = json_decode($elem["custom_field"], true);
                    $elem["comment"] = $cf["3"];
                    unset($elem["custom_field"]);
                    unset($elem["password"]);
                    unset($elem["info_id"]);
                    unset($elem["customer_id"]);
                    unset($elem["epaper"]);
                }
                if ($cmd[1] != "jobs" || $elem["val"] != "Default") {
                    $response["data"][] = $elem;
                }
            }

            if ($cmd[1] == "users") {
                if ($response["more_pages"] = $result->num_rows > PAGE_SIZE) {
                    array_pop($response["data"]);
                }
            }
            if (isset($cmd[2]) && $cmd[2] == "details") {
                while ($field = $result->fetch_field()) {
                    $response["headers"][] = $field->name;
                }
            }

            $response["success"] = true;
            return;
        case "login":
            // get user & pw
            if (isset($_POST["username"])) {
                $username = $_POST["username"];
            } else if (isset($_SESSION["id"])) {
                $username = $_SESSION["id"];
            } else {
                $response["error"] = "No username given";
                return;
            }

            if (!isset($_POST["password"])) {
                $response["error"] = "No password given";
                return;
            }
            $password = $_POST["password"];
            $username = $conn->escape_string($username);

            if (isset($cmd[1]) && $cmd[1] === "admin") {
                $id_field = "user_id";
                $login_field = "username";
                $table = "oc_user";
            } else {
                $id_field = "customer_id";
                $login_field = "email";
                $table = "oc_customer";
            }

            // get user if exists
            $sql = "SELECT $id_field as id, password, salt FROM $table WHERE $login_field='$username' OR $id_field='$username'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows == 0) {
                $response["success"] = true;
                $response["login"] = false;
                return;
            }
            $data = $result->fetch_assoc();

            // check pw & return
            if ($response["login"] = sha1($data["salt"] . sha1($data["salt"] . sha1($password))) === $data["password"]) {
                if ($table == "oc_customer") {
                    $sql = "UPDATE oc_customer_infos SET last_login=NOW() WHERE customer_id=" . $data["id"];
                    $result = $conn->query($sql);
                    if (!$result) {
                        $response["error"] = $conn->error;
                        $response["query"] = $sql;
                        return;
                    }
                }
                $response["id"] = $data["id"];
                $response["pw_hash"] = $data["password"];
            }
            $response["success"] = true;
            break;
        case "confirm":
            // get id
            if (!isset($_GET["token"])) {
                $response["error"] = "No token given";
                return;
            }
            $token = $_GET["token"];

            if (!isset($_GET["type"])) {
                $response["error"] = "No type given";
                return;
            }
            $type = $_GET["type"];

            // check token status
            $sql = "SELECT oc_customer.customer_id, email, oc_customer.firstname, oc_customer.lastname, new_email, address_1, city, postcode, uploaded_document
                    FROM oc_customer
                    LEFT JOIN oc_customer_infos ON oc_customer.customer_id=oc_customer_infos.customer_id
                    LEFT JOIN oc_address ON oc_customer.address_id=oc_address.address_id
                    WHERE confirm_token='$token'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows == 0) {
                $response["error"] = "Ihr Link ist ung&uuml;ltig oder abgelaufen.";
                return;
            }
            $cdata = $result->fetch_assoc();
            $id = $cdata["customer_id"];

            // get newsletters
            if (!($ndata = getNewsletters($conn, $response, $id))) return;

            if (($type == "register" || $type == "newsletters") && count(array_filter($ndata)) > 0) {
                $sql = "UPDATE oc_customer_infos SET date_newsletters_confirmed=NOW() WHERE customer_id=$id";
                $result = $conn->query($sql);
                if (!$result) {
                    $response["error"] = $conn->error;
                    $response["query"] = $sql;
                    return;
                }
            }

            switch ($type) {
                case "register":
                    $sql = "UPDATE oc_customer SET email_status=1 WHERE customer_id=$id";
                    $result = $conn->query($sql);
                    if (!$result) {
                        $response["error"] = $conn->error;
                        $response["query"] = $sql;
                        return;
                    }

                    // register newsletters
                    $result = modifyNewsletters(NEWSLETTER_MODE_ADD, $conn, $id, array_filter($ndata));
                    if (count($result) > 0) {
                        $response = array_merge($response, $result);
                        return;
                    }
                    break;
                case "email":
                    // remove old email newsletters
                    $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $conn, $id, array_filter($ndata));
                    if (count($result) > 0) {
                        $response = array_merge($response, $result);
                        return;
                    }

                    // update email address
                    $sql = "UPDATE oc_customer SET email='" . $cdata["new_email"] . "', new_email=NULL WHERE customer_id=$id";
                    $result = $conn->query($sql);
                    if (!$result) {
                        $response["error"] = $conn->error;
                        $response["query"] = $sql;
                        return;
                    }

                    // add new email newsletters
                    $result = modifyNewsletters(NEWSLETTER_MODE_ADD, $conn, $id, array_filter($ndata));
                    if (count($result) > 0) {
                        $response = array_merge($response, $result);
                        return;
                    }
                    break;
                case "newsletters":

                    // delete all invalid newsletters
                    $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $conn, $id, array_filter($ndata, function($el) { return !$el; }));
                    if (count($result) > 0) {
                        $response = array_merge($response, $result);
                        return;
                    }
                    // add all newsletters
                    $result = modifyNewsletters(NEWSLETTER_MODE_ADD, $conn, $id, array_filter($ndata));
                    if (count($result) > 0) {
                        $response = array_merge($response, $result);
                        return;
                    }
                    break;
            }

            $sql = "UPDATE oc_customer_infos SET confirm_token=NULL WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $response["success"] = true;
            break;
        case "check":
            // get token
            if (isset($_POST["token"])) {
                $token = $_POST["token"];
            } else if (isset($_GET["token"])) {
                $token = $_GET["token"];
            } else {
                $response["error"] = "No token given";
                return;
            }
            $token = $conn->escape_string($token);

            // check token
            // $field = isset($cmd[1]) && $cmd[1] == "mail" ? "email" : "update_token";
            // $sql = "SELECT email, status FROM oc_customer WHERE $field='$token'";
            $sql = "SELECT email, status FROM oc_customer WHERE email='$token'";
            if (!isset($cmd[1]) || $cmd[1] != "mail") {
                $sql .= " OR update_token='$token'";
            }
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($response["inUse"] = $result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $response["email"] = $data["email"];
                $response["status"] = $data["status"];
            }
            $response["success"] = true;
            break;
        case "check_session_validity":
            $response["valid"] = false;
            if (!isset($_SESSION["id"])) {
                $response["error"] = "Not logged in";
                return;
            }

            if (isset($cmd[1]) && $cmd[1] === "admin") {
                $id_field = "user_id";
                $table = "oc_user";
            } else {
                $id_field = "customer_id";
                $table = "oc_customer";
            }

            $sql = "SELECT $id_field FROM $table WHERE $id_field
            =" . $_SESSION["id"];
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $response["valid"] = $result->num_rows > 0;
            $response["success"] = true;
            break;
		case "sso":
            $response["valid"] = false;
            /*if (!isset($_SESSION["id"])) {
                $response["error"] = "Not logged in";
                return;
            }*/

            $sql = "SELECT * FROM oc_session WHERE session_id='" . $_GET["sso_id"]."'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $response["valid"] = $result->num_rows > 0;
            $response["success"] = true;

			$data = $result->fetch_assoc();

            // check pw & return

			/*$now = time();
			$hour = 86400;
			$now = $now - $hour;
			$session_expires = strtotime($data['expire']);*/

            if ($response["success"] == true) {
				$data = json_decode($data['data']);
			/*	$response["now"] = $now;
				$response["session_expires"] = $session_expires;*/
                $response["id"] = $data->customer_id;

            }

            break;
        case "send_delete_email":
            if (!isset($_SESSION["id"])) {
                $response["error"] = "Not logged in";
                return;
            }

            $sql = "SELECT gender, firstname, lastname, email FROM oc_customer, oc_customer_infos
                    WHERE oc_customer.customer_id = oc_customer_infos.customer_id AND oc_customer.customer_id=" . $_SESSION["id"];
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $data = $result->fetch_assoc();

            // send email
            if (!sendEmail($response, 'Löschung ihres Kontos bei Medical Tribune', $data["email"],
                    sprintf(DELETE_ACCOUNT_TEMPLATE,
                            ($data["gender"] == "m" ? "r Herr " : " Frau "),
                            $data["firstname"],
                            $data["lastname"],
                            WEBSITE_ROOT, $data["email"],
                            WEBSITE_ROOT, $data["email"]))) return;

            $response["success"] = true;
            break;
        case "fetch_newsletters":
            if (!isset($_POST["mail"])) {
                $response["error"] = "No email given";
                return;
            }
            $mail = $_POST["mail"];

            $data = [];
            global $cr_group_ids_read;
            $sql = "UPDATE oc_customer_infos, oc_customer SET ";
            foreach ($cr_group_ids_read as $name => $id) {
                $recv = isRegistered($response, $id, $mail);
                $data[$name] = $recv && $recv->active;
                $sql .= "$name=" . (int)$data[$name] . ", ";
            }
            $sql = substr($sql, 0, -2) . " WHERE oc_customer_infos.customer_id=oc_customer.customer_id AND email='$mail'";

            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            $response["data"] = $data;
            $response["success"] = true;
            return;
        case "get_confirm_newsletters_data":
            if (!isset($_GET["token"])) {
                $response["error"] = "No token given";
                return;
            }

            $sql = "SELECT customer_id, firstname, lastname, email FROM oc_customer WHERE newsletters_token='" . $_GET["token"] . "'";
            $result = $conn->query($sql);
            if (!$result || $result->num_rows == 0) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $data = $result->fetch_array(MYSQLI_ASSOC);

            global $cr_group_ids;
            foreach ($cr_group_ids as $name => $id) {
                $data["newsletters"][$name] = (bool)isRegistered($response, $id, $data["email"]);
            }

            $response["data"] = $data;
            $response["success"] = true;
            break;
        case "confirm_newsletters":
            if (!isset($_POST["data"]["customer_id"])) {
                $response["error"] = "No data given";
                return;
            }
            $data = $_POST["data"];
            $id = $data["customer_id"];

            if (!isset($data["newsletter"])) {
                $data["newsletter"] = [];
            }

            global $cr_group_ids;
            $valid_newsletters = array_keys(array_filter($data["newsletter"]));
            $invalid_newsletters = array_diff(array_keys($cr_group_ids), $valid_newsletters);

            $sql = "INSERT INTO oc_customer_newsletters
                        (customer_id" . implode(", ", array_merge([""], $valid_newsletters)) . ")
                    VALUES
                        (" . $conn->escape_string($data["customer_id"]) . implode(", ", array_merge([""], array_fill(0, count($valid_newsletters), 1))) . ")";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            $sql = "UPDATE oc_customer SET newsletters_token=NULL WHERE customer_id='" . $conn->escape_string($id) . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            $nl_arr = [];
            foreach ($cr_group_ids as $nl => $irrelevant) {
                $nl_arr[] = "$nl=" . (in_array($nl, $valid_newsletters) ? "1" : "0");
            }
            $sql = "UPDATE oc_customer_infos SET " . implode(", ", $nl_arr) . " WHERE customer_id='" . $conn->escape_string($id) . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // delete newsletters
            $result = modifyNewsletters(NEWSLETTER_MODE_DELETE_REAL, $conn, $id, $invalid_newsletters);
            if (count($result) > 0) {
                $response = array_merge($response, $result);
                return;
            }

            // // register newsletters
            $result = modifyNewsletters(NEWSLETTER_MODE_ADD, $conn, $id, $valid_newsletters);
            if (count($result) > 0) {
                $response = array_merge($response, $result);
                return;
            }

            $sql = "SELECT email FROM oc_customer WHERE customer_id='" . $conn->escape_string($id) . "'";
            $result = $conn->query($sql);
            if (!$result || $result->num_rows == 0) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $email = $result->fetch_assoc()["email"];

            if (!sendEmail($response, "Erfolgreiche Newsletter-Bestätigung", $email, NEWSLETTERS_CONFIRMED_TEMPLATE)) return;

            $response["success"] = true;
            break;
        case "first_update":
            $kv = getKeysValuesFromPOST($conn);
            if (is_string($kv)) {
                $response["error"] = $kv;
                return;
            }
            $data = $_POST["data"];

            // check email
            $sql = "SELECT * FROM oc_customer WHERE email='" . $data["email"] . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $user = $result->fetch_assoc();
            if ($result->num_rows == 0 || $user["status"] != 2) {
                $response["error"] = "Invalid Email";
                return;
            }
            $id = $user["customer_id"];

            $salt = token(9);
            $sha_password = sha1($salt . sha1($salt . sha1($data['password'])));

            // update oc_customer
            $kv[0][0] .= ", password, salt, language_id, date_added, ip, update_token";
            $kv[0][1] .= ", '" . $sha_password . "'";
            $kv[0][1] .= ", '" . $salt . "'";
            $kv[0][1] .= ", '2'";
            $kv[0][1] .= ", now()";
            $kv[0][1] .= ", '" . $_SERVER['REMOTE_ADDR'] . "'";
            $kv[0][1] .= ", NULL";

            $sql = "UPDATE oc_customer SET " . InsertToUpdate($kv[0][0], $kv[0][1]) . " WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // update oc_address
            $sql = "UPDATE oc_address SET " . InsertToUpdate($kv[1][0], $kv[1][1]) . " WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $address_id = $conn->insert_id;

            // update oc_customer_infos
            $confirm_token = token(50);
            $kv[2][0] .= ", confirm_token";
            $kv[2][1] .= ", '$confirm_token'";
            $sql = "UPDATE oc_customer_infos SET " . InsertToUpdate($kv[2][0], $kv[2][1]) . " WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // set newsletters inactive
            global $cr_group_ids;
            $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $conn, $id, $cr_group_ids);
            if (count($result) > 0) {
                $response = array_merge($response, $result);
                return;
            }

            // send email
            if (!sendEmail($response, 'Bitte bestätigen Sie die Aktualisierung Ihres MT-Nutzerkontos', $data["email"],
                    sprintf(STATUS2_TEMPLATE,
                            ($data["gender"] == "m" ? "r Herr " : " Frau "),
                            $data["firstname"],
                            $data["lastname"],
                            WEBSITE_ROOT, $confirm_token,
                            WEBSITE_ROOT, $confirm_token,
                            getNLString(array_keys($data["newsletter"]))))) return;

            // set status
            $doc_status = !isset($data["sub-job"]["uploaded_document"]);
            $sql = "UPDATE oc_customer SET status=0, doc_status=$doc_status, email_status=0 WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            $response["success"] = true;
            break;
        case "update":
            if (!isset($_SESSION["id"])) {
                $response["error"] = "you must be logged in to do this action";
                return;
            }
            $id = $_SESSION["id"];
            if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
                if (!isset($_POST["customer_id"])) {
                    $response["error"] = "no id supplied";
                    return;
                }
                $id = $conn->escape_string($_POST["customer_id"]);
            }
            $data = $_POST["data"];

            // save old newsletter stats
            if (!($old_newsletters = getNewsletters($conn, $response, $id))) return;

            // update data
            $kv = getKeysValuesFromPOST($conn, $id);
            if (is_string($kv)) {
                $response["error"] = $kv;
                return;
            }
            $tables = ["oc_customer", "oc_address", "oc_customer_infos"];
            for ($i = 0; $i < 3; $i++) {
                $sql = "UPDATE " . $tables[$i] . " SET " . InsertToUpdate($kv[$i][0], $kv[$i][1]) . " WHERE customer_id=" . $id;
                $result = $conn->query($sql);
                if (!$result) {
                    $response["error"] = $conn->error;
                    $response["query"] = $sql;
                    return;
                }
            }

            // check for newsletter changes
            $response["newNewsletters"] = false;
            $toDel = []; $toUpdate = [];
            foreach ($old_newsletters as $name => $status) {
                if (isset($data["newsletter"]) && isset($data["newsletter"][$name]) && $data["newsletter"][$name] == 1) {
                    $toUpdate[] = $name;
                    if ($status == 0) {
                        $response["newNewsletters"] = true;
                    }
                } else if ($status == 1 && (!isset($data["newsletter"]) || !isset($data["newsletter"][$name]) || $data["newsletter"][$name] == 0)) {
                    $toDel[] = $name;
                }
            }
            $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $conn, $id, $toDel);
            if (count($result) > 0) {
                $response = array_merge($response, $result);
                return;
            }
            $result = modifyNewsletters(NEWSLETTER_MODE_UPDATE, $conn, $id, $toUpdate);
            if (count($result) > 0) {
                $response = array_merge($response, $result);
                return;
            }

            // send mail if new newsletters
            if ($response["newNewsletters"]) {
                $confirm_token = token(50);
                $sql = "UPDATE oc_customer_infos SET confirm_token='" . $confirm_token . "' WHERE customer_id=" . $id;
                $result = $conn->query($sql);
                if (!$result) {
                    $response["error"] = $conn->error;
                    $response["query"] = $sql;
                    return;
                }

                $sql = "SELECT email FROM oc_customer WHERE customer_id=" . $id;
                $result = $conn->query($sql);
                if (!$result) {
                    $response["error"] = $conn->error;
                    $response["query"] = $sql;
                    return;
                }
                $maildata = $result->fetch_assoc();

                if (!sendEmail($response, 'Bitte bestätigen Sie die Änderung Ihrer Newsletter-Abonnements', $maildata["email"],
                        sprintf(NEWSLETTER_MAIL_TEMPLATE,
                                ($data["gender"] == "m" ? "r Herr " : " Frau "),
                                $data["firstname"],
                                $data["lastname"],
                                WEBSITE_ROOT, $confirm_token,
                                WEBSITE_ROOT, $confirm_token,
                                getNLString($toUpdate)))) return;
            }

            $response["success"] = true;
            break;
        case "update_status":
            if (!isset($_SESSION["id"]) || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
                $response["error"] = "FORBIDDEN";
                return;
            }
            if (!isset($_POST["customer_id"]) || !isset($_POST["status"])) {
                $response["error"] = "MISSING_DATA";
                return;
            }
            $sql = "UPDATE oc_customer SET status='" . $conn->escape_string($_POST["status"]) . "' WHERE customer_id='" . $conn->escape_string($_POST["customer_id"]) . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $response["success"] = true;
            break;
        case "change_password":
            if (!isset($_SESSION["id"])) {
                $response["error"] = "you must be logged in to do this action";
                return;
            }

            $id = $_SESSION["id"];
            if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
                if (!isset($_POST["customer_id"])) {
                    $response["error"] = "no id supplied";
                    return;
                }
                $id = $conn->escape_string($_POST["customer_id"]);
            }

            $sql = "SELECT salt, password FROM oc_customer WHERE customer_id=" . $id;
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $data = $result->fetch_assoc();

            if (isset($_POST["data"]['password_old'])) {
                $sha_password_old = sha1($data["salt"] . sha1($data["salt"] . sha1($_POST["data"]['password_old'])));
                if ($sha_password_old != $data["password"]) {
                    $response["error"] = "wrong password";
                    return;
                }
            } else if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
                $response["error"] = "FORBIDDEN";
                return;
            }

            $salt = token(9);
            $sha_password = sha1($salt . sha1($salt . sha1($_POST["data"]['password'])));
            $sql = "UPDATE oc_customer SET password='$sha_password', salt='$salt' WHERE customer_id=" . $id;
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // update session
            $_SESSION["pw_hash"] = $sha_password;
            $response["success"] = true;
            break;
        case "change_email":
            if (!isset($_SESSION["id"])) {
                $response["error"] = "you must be logged in to do this action";
                return;
            }
            $id = $_SESSION["id"];

            if (!isset($_POST["data"]) || !isset($_POST["data"]["email"])) {
                $response["error"] = "No new email address given";
                return;
            }
            $emailAddr = $conn->escape_string($_POST["data"]["email"]);

            // check email
            $sql = "SELECT email FROM oc_customer WHERE email='" . $emailAddr . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows > 0) {
            	$response["error"] = "email already in use";
            	return;
            }

            $sql = "SELECT password, gender, firstname, lastname, email
                    FROM oc_customer
                        LEFT JOIN oc_customer_infos ON oc_customer.customer_id = oc_customer_infos.customer_id
                    WHERE oc_customer.customer_id=" . $id;
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $data = $result->fetch_assoc();

            // if ($_SESSION["pw_hash"] != $data["password"]) {
            //     $response["error"] = "wrong password hash";
            //     return;
            // }

            $sql = "UPDATE oc_customer SET new_email='$emailAddr' WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            $confirm_token = token(50);
            $sql = "UPDATE oc_customer_infos SET confirm_token='$confirm_token' WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            if (!sendEmail($response, 'Bitte bestätigen Sie noch die Änderung Ihrer E-Mail-Adresse', $emailAddr,
                    sprintf(CHANGE_MAIL_TEMPLATE,
                            ($data["gender"] == "m" ? "r Herr " : " Frau "),
                            $data["firstname"],
                            $data["lastname"],
                            WEBSITE_ROOT, $confirm_token,
                            WEBSITE_ROOT, $confirm_token))) return;

            $response["success"] = true;
            break;
        case "send_new_confirm_email":
            // get email
            if (!isset($_POST["email"])) {
                $response["error"] = "no email given";
                return;
            }
            $email = $conn->escape_string($_POST["email"]);

            $sql = "SELECT oc_customer.customer_id, status, confirm_token, email, firstname, lastname, gender
                    FROM oc_customer, oc_customer_infos
                    WHERE oc_customer.customer_id=oc_customer_infos.customer_id AND email='$email'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows === 0) {
                $response["error"] = "no email found for that user";
                $response["query"] = $sql;
                return;
            }
            $data = $result->fetch_assoc();

            if ($data["status"] != 0) {
                $response["error"] = "invalid status";
                return;
            }

            if (!($ndata = getNewsletters($conn, $response, $data["customer_id"]))) return;

            $confirm_token = $data["confirm_token"];
            if (!$confirm_token) {
                $confirm_token = token(50);
                $sql = "UPDATE oc_customer_infos SET confirm_token='$confirm_token' WHERE customer_id=" . $data["customer_id"];
                $result = $conn->query($sql);
                if (!$result) {
                    $response["error"] = $conn->error;
                    $response["query"] = $sql;
                    return;
                }
            }

            // send email
            if (!sendEmail($response, 'Bitte bestätigen Sie die Aktualisierung Ihres MT-Nutzerkontos', $data["email"],
                    sprintf(STATUS2_TEMPLATE,
                            ($data["gender"] == "m" ? "r Herr " : " Frau "),
                            $data["firstname"],
                            $data["lastname"],
                            WEBSITE_ROOT, $confirm_token,
                            WEBSITE_ROOT, $confirm_token,
                            getNLString(array_keys(array_filter($ndata)))))) return;

            $response["success"] = true;
            break;
        case "register":
            $req_fields = array(
                "email",
                "password",
                "passwordconfirm",
                "firstname",
                "lastname",
                "job",
            );

            foreach($req_fields as $field){
                if(empty($_POST["data"][$field])){
                    $response = "error";
                    return;
                }
            }

            $kv = getKeysValuesFromPOST($conn);
            if (is_string($kv)) {
                $response["error"] = $kv;
                return;
            }
            $data = $_POST["data"];

            // check email
            $sql = "SELECT email FROM oc_customer WHERE email='" . $conn->escape_string($data["email"]) . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows > 0) {
            	$response["error"] = "email already in use";
            	return;
            }

            $salt = token(9);
            $sha_password = sha1($salt . sha1($salt . sha1($data['password'])));

            // insert int oc_customer
            $kv[0][0] .= ", email, password, salt, language_id, date_added, ip, doc_status";
            $kv[0][1] .= ", '" . $conn->escape_string($data["email"]) . "'";
            $kv[0][1] .= ", '" . $sha_password . "'";
            $kv[0][1] .= ", '" . $salt . "'";
            $kv[0][1] .= ", '2'";
            $kv[0][1] .= ", now()";
            $kv[0][1] .= ", '" . $_SERVER['REMOTE_ADDR'] . "'";
            if (isset($data["sub_job"]["uploaded_document"])) {
                $kv[0][1] .= ", 0";
            } else {
                $kv[0][1] .= ", 1";
            }

            $sql = "INSERT INTO oc_customer (" . $kv[0][0] . ") VALUES (" . $kv[0][1] . ")";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $id = $conn->insert_id;

            // insert into oc_address
            $kv[1][0] .= ", customer_id";
            $kv[1][1] .= ", $id";
            $sql = "INSERT INTO oc_address (" . $kv[1][0] . ") VALUES (" . $kv[1][1] . ")";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $address_id = $conn->insert_id;

            // update oc_customer with address id
            $sql = "UPDATE oc_customer SET address_id=$address_id WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // insert into oc_customer_infos
            $confirm_token = token(50);
            $kv[2][0] .= ", customer_id, confirm_token";
            $kv[2][1] .= ", $id, '$confirm_token'";
            $sql = "INSERT INTO oc_customer_infos (" . $kv[2][0] . ") VALUES (" . $kv[2][1] . ")";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // send email
            if (!sendEmail($response, 'Bitte aktivieren Sie noch Ihr Nutzerkonto bei Medical Tribune', $data["email"],
                    sprintf(MAIL_TEMPLATE,
                            ($data["gender"] == "m" ? "r Herr " : " Frau "),
                            $data["firstname"],
                            $data["lastname"],
                            WEBSITE_ROOT, $confirm_token,
                            WEBSITE_ROOT, $confirm_token,
                            getNLString(array_keys($data["newsletter"]))))) return;

            if (isset($data["sub_job"]["uploaded_document"])) {
                foreach ([DOC_EMAIL, DOC_EMAIL2] as $email) {
                    if (!sendEmail($response, "Neue Registrierung bei Medical Tribune", $email,
                            sprintf(NEW_DOC_TEMPLATE,
                                    $id,
                                    $data["firstname"],
                                    $data["lastname"],
                                    $data["email"],
                                    $data["address"] . ", " . $data["postcode"] . " " . $data["city"],
                                    "Dokument: " . WEBSITE_ROOT . "/" . $data["sub_job"]["uploaded_document"]))) return;
                }
            }

            $response["success"] = true;
            break;
        case "send_mail":
            if (!isset($_POST["email"]) && !isset($_GET["email"])) {
                $response["error"] = "No email address given";
                return;
            }
            $email = isset($_POST["email"]) ? $_POST["email"] : $_GET["email"];

            $token = token(50);
            $sql = "UPDATE oc_customer SET update_token='$token' WHERE email='$email'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // send email
            if (!sendEmail($response, 'Bitte überprüfen und erneuern Sie Ihr MT-Nutzerkonto', $email,
                    sprintf(REGISTER_LINK_MAIL_TEMPLATE,
                            WEBSITE_ROOT, $token,
                            WEBSITE_ROOT, $token))) return;

            $response["success"] = true;
            break;
        case "reset_password":
            if (!isset($_GET["token"])) {
                $response["error"] = "no token given";
                return;
            }
            if (!isset($_POST["email"])) {
                $response["error"] = "no email given";
                return;
            }
            if (!isset($_POST["password"])) {
                $response["error"] = "no password given";
                return;
            }
            if (!isset($_POST["password_confirm"])) {
                $response["error"] = "no 2nd password given";
                return;
            }
            $token = $_GET["token"];

            $sql = "SELECT email FROM oc_customer WHERE password_token='$token'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows == 0) {
                $response["error"] = "Invalid token";
                return;
            }
            if ($_POST["email"] != $result->fetch_assoc()["email"]) {
                $response["error"] = "Invalid Email";
                return;
            }
            if ($_POST["password"] != $_POST["password_confirm"]) {
                $response["error"] = "The passwords dont match";
                return;
            }

            $salt = token(9);
            $sha_password = sha1($salt . sha1($salt . sha1($_POST['password'])));
            $sql = "UPDATE oc_customer SET password_token=NULL, password='$sha_password', salt='$salt' WHERE email='" . $_POST["email"] . "'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $response["success"] = true;
            break;
        case "send_reset_password":
            if (!isset($_POST["username"])) {
                $response["error"] = "no email given";
                return;
            }
            $mail = $_POST["username"];

            $sql = "SELECT gender, firstname, lastname FROM oc_customer, oc_customer_infos
                    WHERE oc_customer.customer_id = oc_customer_infos.customer_id AND email='$mail'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows == 0) {
                $response["success"] = true;
                return;
            }
            $data = $result->fetch_assoc();

            $token = token(50);
            $sql = "UPDATE oc_customer SET password_token='$token' WHERE email='$mail'";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // send email
            if (!sendEmail($response, 'Zurücksetzen Ihres Passworts bei Medical Tribune', $mail,
                    sprintf(RESET_PASSWORD_TEMPLATE,
                            ($data["gender"] == "m" ? "r Herr " : " Frau "),
                            $data["firstname"],
                            $data["lastname"],
                            WEBSITE_ROOT, $token,
                            WEBSITE_ROOT, $token))) return;

            $response["success"] = true;
            break;
        case "delete_account":
            if (!isset($_SESSION["id"])) {
                $response["error"] = "you must be logged in to do this action";
                return;
            }
            $id = $_SESSION["id"];
            if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
                if (!isset($_POST["customer_id"])) {
                    $response["error"] = "no id supplied";
                    return;
                }
                $id = $conn->escape_string($_POST["customer_id"]);
            }

            // delete newsletters
            if (!($data = getNewsletters($conn, $response, $id))) return;
            $result = modifyNewsletters(NEWSLETTER_MODE_DELETE, $conn, $id, array_filter($data));
            if (count($result) > 0) {
                $response["errors"] = $result;
                return;
            }

            // delete addresses
            $sql = "DELETE FROM oc_address WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // delete infos
            $sql = "DELETE FROM oc_customer_infos WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }

            // delete data
            $sql = "DELETE FROM oc_customer WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            $response["success"] = true;
            break;
        case "search":
            if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
                return "no permission to do that";
            }

            $no_params = empty($_GET["filter-field"]) && empty($_GET["compareBy"]) && empty($_GET["compareValue"]) && !isset($_GET["indentations"]) && empty($_GET["operator"]);

            if (!$no_params && (empty($_GET["filter-field"]) || empty($_GET["compareBy"]) || !isset($_GET["indentations"])
                || (empty($_GET["operator"]) && count($_GET["filter-field"]) > 1))) {
                return "Missing data";
            }
            $filters = isset($_GET["filter-field"]) ? $_GET["filter-field"] : [];
            $operators = isset($_GET["operator"]) ? $_GET["operator"] : [];
            $compareBy = isset($_GET["compareBy"]) ? $_GET["compareBy"] : [];
            $compareValues = empty($_GET["compareValue"]) ? array_fill(0, count($compareBy), "") : $_GET["compareValue"];
            $indents = isset($_GET["indentations"]) ? $_GET["indentations"] : "";

            if (!$no_params &&
                  (!is_array($filters)   || !is_array($operators)
                || !is_array($compareBy) || !is_array($compareValues) || !is_string($indents)
                || count($filters) != count($operators) + 1
                || count($filters) != count($compareBy)
                || count($filters) != count($compareValues)
                || count($filters) +  count($operators) != strlen($indents)))
            {
                return "Invalid data";
            }

            function append(&$sql, $conn, $id) {
                global $valid_filter_fields, $valid_compares;
                if (!in_array($_GET["filter-field"][$id], $valid_filter_fields)) {
                    return "Invalid field: " . $_GET["filter-field"][$id];
                }
                if (!in_array($_GET["compareBy"][$id], $valid_compares)) {
                    return "Invalid compare: " . $_GET["compareBy"][$id];
                }

                $field_start = $field_end = "";
                if (substr($_GET["compareBy"][$id], -4) == "LIKE") {
                    $start = "'%";
                    $end = "%'";
                } else if ($_GET["filter-field"][$id] == "date_added") {
                    $start = "DATE(STR_TO_DATE('";
                    $end = "', '%d.%m.%Y'))";
                    $field_start = "DATE(";
                    $field_end = ")";
                } else {
                    $start = $end = "'";
                }
                $sql .=   $field_start . $conn->escape_string($_GET["filter-field"][$id]) . $field_end . " "
                        . $conn->escape_string($_GET["compareBy"][$id])
                        . " $start" . $conn->escape_string($_GET["compareValue"][$id]) . "$end";
            }

            global $valid_operators;
            $last_indent = 0;
            $sql = "FROM        oc_customer
                    LEFT JOIN   oc_address          ON oc_customer.address_id           = oc_address.address_id
                    LEFT JOIN   oc_customer_infos   ON oc_customer.customer_id          = oc_customer_infos.customer_id
                    LEFT JOIN   oc_customer_group   ON oc_customer.customer_group_id    = oc_customer_group.customer_group_id";
            if (!$no_params) {
                $sql .= " WHERE ";
                for ($i = 0; $i < count($operators); ++$i) {
                    if ($last_indent > $indents[2 * $i]) {
                        return "Invalid Syntax";
                    }
                    while ($last_indent < $indents[2 * $i]) {
                        $sql .= "(";
                        $last_indent++;
                    }
                    if ($r = append($sql, $conn, $i)) return $r;

                    if ($last_indent < $indents[2 * $i + 1]) {
                        return "Invalid Syntax";
                    }
                    while ($last_indent > $indents[2 * $i + 1]) {
                        $sql .= ")";
                        $last_indent--;
                    }
                    if (!in_array($operators[$i], $valid_operators)) {
                        return "Invalid operator: " . $operators[$i];
                    }
                    $sql .= " " . $conn->escape_string($operators[$i]) . " ";
                }
                if ($r = append($sql, $conn, $i)) return $r;
                while ($last_indent-- > 0) {
                    $sql .= ")";
                }
            }

            $sql .= " ORDER BY oc_customer.date_added DESC";
            $end = "";
            if (empty($cmd[1]) || $cmd[1] != "details") {
                $end .= " LIMIT " . (PAGE_SIZE + 1);
                if (isset($_GET["page"]) && is_numeric($_GET["page"])) {
                    $end .= " OFFSET " . (intval($_GET["page"]) - 1) * PAGE_SIZE;
                }
            }

            $query = "SELECT oc_customer.customer_id, oc_customer_group.title AS job, oc_customer.firstname,
                             oc_customer.lastname, email, submit_method, uploaded_document, status, email_status, doc_status, last_login,
                             DATE_FORMAT(date_added, '%d.%m.%Y %H:%i:%s') as date_added " . $sql . $end;
            $response["query"] = $query;
            $result = $conn->query($query);
            if (!$result) return $conn->error;

            while ($elem = $result->fetch_array(MYSQLI_ASSOC)) {
                $response["data"][] = $elem;
            }
            if ($response["more_pages"] = $result->num_rows > PAGE_SIZE) {
                array_pop($response["data"]);
            }

            if (isset($cmd[1]) && $cmd[1] == "details") {
                while ($field = $result->fetch_field()) {
                    $response["headers"][] = $field->name;
                }
            } else {
                $query = "SELECT COUNT(*) " . $sql;
                $response["query2"] = $query;
                $result = $conn->query($query);
                if (!$result) return $conn->error;
                if (!$result->num_rows) return "Error counting rows";
                $cnt = $result->fetch_array();
                if (!$cnt) return "Error counting rows";
                $response["count"] = $cnt[0];
            }
            $response["success"] = true;
            break;
            
        case "saveFilter":
            $filterData = array();
            $loggedInUserId = $_SESSION["id"];
            $filterData['indentations'] = $_GET['indentations'];
            $filterData['filter-field'] = $_GET['filter-field'];
            $filterData['compareBy'] = $_GET['compareBy'];
            $filterData['compareValue'] = $_GET['compareValue'];
            $filterData['operator'] = $_GET['operator'];
            // echo "<pre>";
            // print_r($_REQUEST);
            // print_r($filterData);
            // echo "</pre>";
            // exit;
            $filterEncodedData = json_encode($filterData);
            $sql = "UPDATE oc_customer_infos SET filter = '" . $filterEncodedData . "' WHERE customer_id=" . $loggedInUserId;
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            break;
        case "getFilter":
            $loggedInUserId = $_SESSION["id"];
            $sql = "SELECT customer_id,filter FROM oc_customer_infos WHERE customer_id=" . $loggedInUserId;
            $result = $conn->query($sql);
            if (!$result) {
                $response["error"] = $conn->error;
                $response["query"] = $sql;
                return;
            }
            if ($result->num_rows === 0) {
                $response["error"] = "No User found with this id";
                $response["query"] = $sql;
                return;
            }
            $response["data"] = $result->fetch_assoc();
            $response["success"] = true;
            break;
        default:
            $response["error"] = "unknown cmd[0]: " . $cmd[0];
            break;
    }
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

function getKeysValuesFromPOST($conn, $id = null) {
    $data = &$_POST["data"];
    global $cr_group_ids, $info_fields;

    $kv = [[], [], []];

    // oc_customer
    // keys
    $kv[0][0] = "firstname, lastname, customer_group_id, telephone";
    // values
    $kv[0][1] = "'" . $conn->escape_string($data["firstname"]) . "'";
    $kv[0][1] .= ", '" . $conn->escape_string($data["lastname"]) . "'";
    $kv[0][1] .= ", '" . $conn->escape_string($data["job"]) . "'";
    $kv[0][1] .= ", " . (isset($data["telephone"]) && $data["telephone"] != "" ? "'" . $conn->escape_string($data["telephone"]) . "'" : "NULL");

    if (isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true) {
        if (isset($data["doc_status"])) {
            $kv[0][0] .= ", doc_status";
            $kv[0][1] .= ", '" . $conn->escape_string($data["doc_status"]) . "'";
        }
        if (isset($data["status"])) {
            $kv[0][0] .= ", status";
            $kv[0][1] .= ", '" . $conn->escape_string($data["status"]) . "'";
        }
        if (isset($data["comment"]) && $id) {
            $sql = "SELECT custom_field FROM oc_customer WHERE customer_id=$id";
            $result = $conn->query($sql);
            if (!$result) return $conn->error . "($sql)";
            if ($result->num_rows == 0) return "No results: $sql";

            $cf = json_decode($result->fetch_row()[0], true);
            $cf["3"] = $data["comment"];
            $kv[0][0] .= ", custom_field";
            $kv[0][1] .= ", '" . $conn->escape_string(json_encode($cf)) . "'";
        }
    }

    // oc_address
    // keys
    $kv[1][0] = "firstname, lastname, address_1, city, postcode, country_id";
    // values
    $kv[1][1] = "'" . $conn->escape_string($data["firstname"]) . "'";
    $kv[1][1] .= ", '" . $conn->escape_string($data["lastname"]) . "'";
    $kv[1][1] .= ", " . (isset($data["address"]) ? "'" . $conn->escape_string($data["address"]) . "'" : "NULL");
    $kv[1][1] .= ", " . (isset($data["city"]) ? "'" . $conn->escape_string($data["city"]) . "'" : "NULL");
    $kv[1][1] .= ", " . (isset($data["postcode"]) ? "'" . $conn->escape_string($data["postcode"]) . "'" : "NULL");
    $kv[1][1] .= ", '" . (isset($data["country"]) ? $conn->escape_string($data["country"]) : "") . "'";

    // oc_customer_infos
    // keys
    $kv[2][0] = "gender, title_prefix, title_suffix, birthday";
    // values
    $kv[2][1] =         (isset($data["gender"]) && $data["gender"] ? "'" . $conn->escape_string($data["gender"]) . "'" : "NULL");
    $kv[2][1] .= ", " . (isset($data["title_prefix"]) && $data["title_prefix"] ? "'" . $conn->escape_string($data["title_prefix"]) . "'" : "NULL");
    $kv[2][1] .= ", " . (isset($data["title_suffix"]) && $data["title_suffix"] ? "'" . $conn->escape_string($data["title_suffix"]) . "'" : "NULL");
    $kv[2][1] .= ", " . (isset($data["birthday"]) && $data["birthday"] != "" ? "'" . $conn->escape_string($data["birthday"]) . "'" : "NULL");

    // handle doc upload
    if (isset($_FILES["data"]["name"]["sub_job"]["uploaded_document"])) {
        $uname = $_FILES["data"]["name"]["sub_job"]["uploaded_document"];
        $utmpname = $_FILES["data"]["tmp_name"]["sub_job"]["uploaded_document"];
        $usize = $_FILES["data"]["size"]["sub_job"]["uploaded_document"];

        $filename = "doc_confirmations/" . preg_replace('((^\.)|\/|(\.$))', '_', $uname);
        $dir_prefix = __DIR__ . "/../";
        $ext = "";
        if (strrpos($filename, ".")) {
            $ext = substr($filename, strrpos($filename, "."));
            $filename = substr($filename, 0, strrpos($filename, "."));
        }
        $target_file = $filename . $ext;
        for ($i = 1; file_exists($dir_prefix . $target_file); $i++) $target_file = $filename . "_" . $i . $ext;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        global $accepted_mimetypes;

        if ($usize > 5000000) {
            return "File is too large.";
        } else if (!in_array(finfo_file($finfo, $utmpname), $accepted_mimetypes)) {
            return "Invalid file type uploaded: " . $uname;
        } else if (!move_uploaded_file($utmpname, $dir_prefix . $target_file)) {
            return "The file could not be uploaded: " . $uname;
        } else {
            $data["sub_job"]["uploaded_document"] = $target_file;
        }
    }
    foreach ($info_fields as $key) {
        if (!empty($data["sub_job"][$key])) {
            $kv[2][0] .= ", " . $key;
            $kv[2][1] .= ", '" . $conn->escape_string($data["sub_job"][$key]) . "'";
        } else if (empty($data["sub_job"]["keep-" . $key])) {
            $kv[2][0] .= ", " . $key;
            $kv[2][1] .= ", NULL";
        }
    }

    // newsletters
    foreach ($cr_group_ids as $name => $value) {
        $kv[2][0] .= ", " . $name;
        if (!isset($data["newsletter"]) || !isset($data["newsletter"][$name])) {
            $kv[2][1] .= ", 0";
        } else {
            $kv[2][1] .= ", 1";
        }
    }
    return $kv;
}

function getNLString($arr) {
    $realNames = [
        "praxisletter" => "PraxisLetter",
        "cmeletter" => "CMELetter",
        "coronaletter" => "Coronaletter",
        "onkoletter" => "OnkoLetter",
        "pneumoletter" => "PneumoLetter",
        "kardioletter" => "KardioLetter",
        "neuroletter" => "NeuroLetter",
        "gastroletter" => "GastroLetter",
        "infoletter" => "InfoLetter mit Cartoon",
        "honorarletter" => "HonorarLetter",
        "diabetesletter" => "DiabetesLetter",
        "paediatrieletter" => "PädiatrieLetter",
        "gynletter" => "GynLetter",
        "dermaletter" => "DermaLetter",
		"rheumaletter" => "RheumaLetter"
    ];
    $str = implode("<br>", array_map(function($name) use ($realNames) { return $realNames[$name]; }, $arr));
    if (!$str) {
        $str = "keine";
    }
    return $str;
}

function InsertToUpdate($keys, $values) {
    $str = "";
    $exploded = [explode(", ", $keys), explode(", ", $values)];
    for ($i = 0; $i < count($exploded[0]); $i++) {
        $str .= trim($exploded[0][$i]) . "=" . trim($exploded[1][$i]) . ",";
    }
    return trim($str, ",");
}

function getNewsletters($conn, &$response, $customer_id) {
    global $cr_group_ids;
    $sql = "SELECT " . implode(", ", array_keys($cr_group_ids)) . " FROM oc_customer_infos WHERE customer_id=$customer_id";
    $result = $conn->query($sql);
    if (!$result) {
        $response["error"] = $conn->error;
        $response["query"] = $sql;
        return;
    }
    if ($result->num_rows === 0) {
        $response["error"] = "No User found with this id";
        $response["query"] = $sql;
        return;
    }
    return $result->fetch_assoc();
}

function sendEmail(&$response, $subject, $mail, $body) {
    require_once('phpmailer/Exception.php');
    require_once('phpmailer/OAuth.php');
    require_once('phpmailer/SMTP.php');
    require_once('phpmailer/POP3.php');
    require_once('phpmailer/PHPMailer.php');

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

    if ($email->AddEmbeddedImage(dirname(__FILE__) . '/../img/logo.png', 'logo') === false) {
        $response["error"] = "Adding image failed: " . $email->ErrorInfo;
        return;
    }
    if ($email->AddAddress($mail) === false) {
        $response["error"] = "Adding mail receiver failed: " . $email->ErrorInfo;
        return;
    }
    if ($email->Send() === false) {
        $response["error"] = "Sending email failed: " . $email->ErrorInfo;
        return;
    }

    return $email;
}

function constructMailReceiver($conn, $id) {
    // get customer data
    $sql = "SELECT oc_customer.firstname, oc_customer.lastname, email, title_prefix, title_suffix, gender, address_1, city, postcode
            FROM oc_customer
                LEFT JOIN oc_address ON oc_customer.address_id = oc_address.address_id
                LEFT JOIN oc_customer_infos ON oc_customer.customer_id = oc_customer_infos.customer_id
            WHERE oc_customer.customer_id=$id";
    $result = $conn->query($sql);
    if (!$result) {
        $response["error"] = $conn->error;
        $response["query"] = $sql;
        return;
    }
    $data = $result->fetch_assoc();

    // construct receiver
    $title = isset($data["title_prefix"]) ? $data["title_prefix"] : NULL;
    if ($title && isset($data["title_prefix"])) {
        $title .= " " . $data["title_suffix"];
    }
    return array(
        "email"			=> $data["email"],
        "registered"	=> time(),
        "activated"		=> time(),
        "attributes"	=> array(
                            "salutation" => ($data["gender"] == "m" ? "Herr" : "Frau"),
                            "title" => $title,
                            "firstname" => $data["firstname"],
                            "lastname" => $data["lastname"],
                            "zip" => $data["postcode"],
                            "city" => $data["city"],
                            "street" => $data["address_1"]
                        )
    );
}

function isRegistered(&$response, $id, $email) {
    global $rest;
    try {
        return $rest->get("/groups/{$id}/receivers/" . $email);
    } catch (\Exception $e){
        if (json_decode($rest->error)->error->code !== 404 && $response) {
            $response["errors"][] = [
                "exception" => $e,
                "rest_error" => $rest->error
            ];
        }
    }
    return false;
}

function modifyNewsletters($mode, $conn, $user_id, $nl) {
    if (count($nl) == 0) {
        return;
    }

    $response = [];
    $receiver = constructMailReceiver($conn, $user_id);
    global $cr_group_ids, $rest, $exkl_letter_ids;


    foreach ($nl as $key => $val) {
        if (isset($cr_group_ids[$val])) {
            $group_id = $cr_group_ids[$val];
        } else if (isset($cr_group_ids[$key])) {
            $group_id = $cr_group_ids[$key];
        } else {
            $response["errors"][] = "invalid group id: $key/$val";
            continue;
        }

        $registered = isRegistered($response, $group_id, $receiver["email"]);

        try {
            if ($mode == NEWSLETTER_MODE_ADD) {
                if (!$registered) {
                    $rest->post("/groups/" . $group_id . "/receivers", $receiver);
                } else {
                    $rest->put("/groups/" . $group_id . "/receivers/" . $receiver['email'] . "/setactive");
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

        //register exklusive letter
        if (isset($exkl_letter_ids[$val])) {
            $exkl_letter_group = $exkl_letter_ids[$val];
        } else if (isset($exkl_letter_ids[$key])) {
            $exkl_letter_group = $exkl_letter_ids[$key];
        } else {
            $response["errors"][] = "invalid group id: $key/$val";
            continue;
        }

        $registered = isRegistered($response, $exkl_letter_group, $receiver["email"]);

        try {
            if ($mode == NEWSLETTER_MODE_ADD) {
                if (!$registered) {
                    $rest->post("/groups/" . $exkl_letter_group . "/receivers", $receiver);
                } else {
                    $rest->put("/groups/" . $exkl_letter_group . "/receivers/" . $receiver['email'] . "/setactive");
                }
            } else if ($mode == NEWSLETTER_MODE_UPDATE && $registered) {
                $rest->put("/groups/" . $exkl_letter_group . "/receivers/" . $receiver["email"], $receiver);
            } else if ($mode == NEWSLETTER_MODE_DELETE && $registered) {
                $rest->put("/groups/" . $exkl_letter_group . "/receivers/" . $receiver["email"] . "/setinactive");
            } else if ($mode == NEWSLETTER_MODE_DELETE_REAL && $registered) {
                $rest->delete("/groups/" . $exkl_letter_group . "/receivers/" . $receiver["email"]);
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
