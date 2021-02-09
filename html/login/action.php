<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

if (logged_in()) {
    add_error("You are already logged in");
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    add_error("Authentication not implemented");
} else {
    add_error("Login request method must be POST");
}
header("Location: /login/");
?>
