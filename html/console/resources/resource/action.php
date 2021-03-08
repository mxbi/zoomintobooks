<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$rid = sanitise($_POST["rid"]);

$authorised = authorised("edit resource", array("rid" => $rid));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    $file = empty($_FILES["resource"]) ? NULL : $_FILES["resource"];
    manage_resource($file, $_POST, true);
}

json_status();
