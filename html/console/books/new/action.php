<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/books/new/";

$authorised = authorised("add book");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    add_book($_POST, $_FILES["book"]);
}

header("Location: " . $_SESSION["redirect"]);
?>
