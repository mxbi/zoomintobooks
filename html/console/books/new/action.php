<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/books/new/";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if (!$is_logged_in) {
    add_error("You must <a href=\"/login/\">log in</a> to view this page");
} else {
    add_book($_POST, $_FILES["book"]);
}

header("Location: " . $_SESSION["redirect"]);
?>
