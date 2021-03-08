<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$authorised = authorised("add resource");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    $file = empty($_FILES["resource"]) ? NULL : $_FILES["resource"];
    manage_resource($file, $_POST, false);
}

json_status();
