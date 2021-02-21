<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$rid = sanitise($_POST["rid"]);
$_SESSION["redirect"] = "/console/resources/resource/?rid=$rid";

$authorised = authorised("edit resource", array("rid" => $rid));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    manage_resource($_POST, true);
}

header("Location: " . $_SESSION["redirect"]);
?>
