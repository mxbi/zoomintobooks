<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$authorised = authorised("add book");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_book($_POST, $_FILES["book"], false);
}

if (!errors_occurred()) {
    header("Location: " . $_SESSION["redirect"]);
} else {
    json_status();
}
