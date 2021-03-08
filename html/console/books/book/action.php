<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = empty($_POST["isbn"]) ? NULL : sanitise($_POST["isbn"]);

$authorised = authorised("edit book", array("isbn" => $isbn));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    $file = empty($_FILES["book"]) ? NULL : $_FILES["book"];
    manage_book($_POST, $file, true);
}
json_status();
