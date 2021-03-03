<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/users/new/";

$authorised = authorised("add user");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_user($_POST, false);
}
json_status();
