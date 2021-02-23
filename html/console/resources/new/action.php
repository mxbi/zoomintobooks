<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/resources/new/";

$authorised = authorised("add resource");
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_resource($_FILES["resource"], $_POST, false);
}

header("Location: " . $_SESSION["redirect"]);
?>
