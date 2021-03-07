<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$authorised = authorised("add book");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    $file = empty($_FILES["book"]) ? NULL : $_FILES["book"];
    manage_book($_POST, $file, false);
}

json_status();
