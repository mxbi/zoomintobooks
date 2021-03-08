<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$username = empty($_POST["username"]) ? NULL : sanitise($_POST["username"]);

$authorised = authorised("edit user", array("username" => $username));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_user($_POST, true);
}
json_status();
