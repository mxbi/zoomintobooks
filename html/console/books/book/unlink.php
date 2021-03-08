<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = empty($_POST["isbn"]) ? NULL : sanitise($_POST["isbn"]);
$rid = empty($_POST["rid"]) ? NULL : sanitise($_POST["rid"]);

$authorised = authorised("edit book", array("isbn" => $isbn));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    unlink_resource($isbn, $rid);
}
json_status();
