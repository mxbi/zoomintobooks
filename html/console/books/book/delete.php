<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = empty($_POST["isbn"]) ? NULL : sanitise($_POST["isbn"]);

$authorised = authorised("delete book", array("isbn" => $isbn));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    delete_book($isbn);
}
json_status();
