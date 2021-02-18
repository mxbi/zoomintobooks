<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/publishers/new/";

$authorised = authorised("add publisher");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    add_publisher($_POST);
}

header("Location: " . $_SESSION["redirect"]);
?>
