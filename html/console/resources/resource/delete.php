<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$rid = sanitise($_POST["rid"]);

$authorised = authorised("delete resource", array("rid" => $rid));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    delete_resource($rid);
}

json_status();
