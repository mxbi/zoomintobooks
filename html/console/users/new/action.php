<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/users/new/";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if (!$is_logged_in) {
    add_error("You must <a href=\"/login/\">log in</a> to view this page");
} else if (!$is_admin) {
    add_error("You do not have the required permissions to view this page");
} else {
    add_user($_POST);
}

header("Location: /console/users/new/");
?>
