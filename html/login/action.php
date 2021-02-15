<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

if ($is_logged_in) {
    add_error("You are already logged in");
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $result = authorise($username, $password);
    if ($result === "") {
        add_error("Authentication failed");
    } else {
        $_SESSION["username"] = $username;
        $_SESSION["account_type"] = $result;
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
