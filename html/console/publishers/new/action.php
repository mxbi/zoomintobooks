<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$authorised = authorised("add publisher");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_publisher($_POST, false);
}
json_status();
