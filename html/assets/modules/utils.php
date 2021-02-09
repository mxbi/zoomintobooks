<?php
$is_logged_in = false;
$is_admin = false;

function init() {
    global $is_logged_in;
    global $is_admin;

    session_start();
    if (!isset($_SESSION["errors"])) {
        $_SESSION["errors"] = array();
    }
    $is_logged_in = isset($_SESSION["username"]);
    $is_admin = ($_SESSION["account_type"] === "admin");
}

function add_error($msg) {
    $_SESSION["errors"][] = $msg;
}

function set_success($msg) {
    $_SESSION["success"] = $msg;
}

function display_status() {
    if (!empty($_SESSION["errors"])) {
        echo "   <div class=\"errors\">\n";
        echo "    <p>The following errors occurred:</p>\n";
        echo "    <ul>\n";
        foreach ($_SESSION["errors"] as $error) {
            echo "     <li>$error</li>\n";
        }
        echo "    </ul>\n";
        echo "   </div>\n";
        $_SESSION["errors"] = array();
    }

    if (!empty($_SESSION["success"])) {
        echo "   <div class=\"success\">\n";
        echo "    <p>" . $_SESSION["success"] . "</p>\n";
        echo "   </div>\n";
        $_SESSION["success"] = "";
    }
}
?>
