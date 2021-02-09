<?php
function init() {
    session_start();
    if (!isset($_SESSION["errors"])) {
        $_SESSION["errors"] = array();
    }
}

function logged_in() {
    return isset($_SESSION["username"]);
}

function add_error($msg) {
    $_SESSION["errors"][] = $msg;
}

function display_errors() {
    if (!empty($_SESSION["errors"])) {
        echo "   <div class=\"errors\">\n";
        echo "    <p>The following errors occurred:</p>\n";
        echo "    <ul>\n";
        foreach ($_SESSION["errors"] as $error) {
            echo "     <li>$error</li>\n";
        }
        echo "    </ul>\n";
        echo "   </div>\n";
    }
    $_SESSION["errors"] = array();
}
?>
