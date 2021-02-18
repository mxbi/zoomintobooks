<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

if (isset($_SESSION["username"])) {
    add_error("You are already logged in");
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $success = authenticate($username, $password);
    if (!$success) {
        add_error("Authentication failed");
    } else {
        set_success("Logged in as " . $username);
    }
} else {
    add_error("Login request method must be POST");
}

$redirect = "/login/";
if (empty($_SESSION["errors"]) && isset($_SESSION["redirect"])) {
    $redirect = $_SESSION["redirect"];
    unset($_SESSION["redirect"]);
}
header("Location: $redirect");
?>
