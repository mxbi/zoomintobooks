<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$publisher = empty($_POST["publisher"]) ? NULL : sanitise($_POST["publisher"]);

$authorised = authorised("edit publisher", array("publisher" => $publisher));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_publisher($_POST, true);
}
json_status();
