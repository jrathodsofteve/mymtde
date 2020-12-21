<?php

// uploads
$accepted_mimetypes = ["application/pdf", "image/jpg", "image/jpeg", "image/png"];

//backend stuff
define("PAGE_SIZE", 50);
$valid_filter_fields = array_merge(array_keys($cr_group_ids), [
    "oc_customer.firstname",
    "oc_customer.lastname",
    "oc_customer.customer_group_id",
    "email",
    "telephone",
    "status",
    "date_added",
    "address_1",
    "city",
    "postcode",
    "country_id",
    "title_prefix",
    "title_suffix",
    "gender",
    "birthday",
    "work_area",
    "work_area_extra",
    "association",
    "pharmacist_chamber",
    "special_field",
    "specialisation",
    "work_area",
    "main_interests_1",
    "main_interests_2",
    "efn",
    "submit_method",
    "form_of_employment",
    "expected_graduation_year",
    "focus",
    "subject_area",
    "dentist_chamber",
    "study_subject",
    "medical_association",
    "description"
]);
$valid_operators = ["AND", "OR", "XOR"];
$valid_compares = ["=", "!=", "NOT LIKE", "LIKE", "<", "<=", ">", ">="];
$user_states = ["Inaktiv", "Aktiv", "Update"];
$count_cols = [
    "Newsletter" => "newsletter",
    "Beruf" => "customer_group_id",
    "Fachgebiet" => "special_field",
    "Status" => "status",
    "Land" => "country_id",
    "1. Hauptinteressengebiet" => "main_interests_1",
    "2. Hauptinteressengebiet" => "main_interests_2",
    "Ärztekammer" => "medical_association"
];
define("NEWSLETTER_MODE_ADD", 0);
define("NEWSLETTER_MODE_UPDATE", 1);
define("NEWSLETTER_MODE_DELETE", 2);
define("NEWSLETTER_MODE_DELETE_REAL", 3);

$columns_list = [
    ["size" => 2, "fields" => ["firstname", "lastname"], "header" => "Name"],
    ["size" => 2, "fields" => ["email"], "header" => "E-Mail"],
    ["size" => "2_5", "fields" => ["job"], "header" => "Beruf"],
    ["size" => 2, "fields" => ["status", "email_status", "doc_status", "submit_method", "uploaded_document"], "func" => function($s, $es, $ds, $sm, $doc) {
        global $user_states;
        $select = "<select class='state-select'>";
        foreach ($user_states as $i => $state) {
            $select .= "<option value='$i'" . ($i == $s ? " selected" : "") . ">" . $state . "</option>";
        }
        $select .= "</select>";
        $out = ["E-Mail " . ($es ? "" : "nicht ") . "bestätigt"];
        if ($sm == "E-Mail") {
            $out[] = "Bestätigung per E-Mail";
        } else if ($doc) {
            $out[] = "Dokument " . ($ds ? "" : "nicht ") . "bestätigt";
        }
        return $select . implode("<br>", $out);
    }, "header" => "Status"],
    ["size" => "1_5", "fields" => ["date_added"], "header" => "Hinzugefügt"],
    ["size" => "1_5", "fields" => ["last_login"], "header" => "Letzter Login"],
    ["size" => "0_5", "fields" => ["uploaded_document"], "func" => function($d) {
        return '<a class="fas fa-file icon-button" target="_blank" ' . ($d ? "href='../$d'" : "style='visibility:hidden'") . "></a>";
    }]
];

$info_fields = [
    "work_area",
    "work_area_extra",
    "association",
    "pharmacist_chamber",
    "special_field",
    "specialisation",
    "main_interests_1",
    "main_interests_2",
    "efn",
    "uploaded_document",
    "form_of_employment",
    "expected_graduation_year",
    "focus",
    "subject_area",
    "dentist_chamber",
    "study_subject",
    "medical_association",
    "description"
];

?>
