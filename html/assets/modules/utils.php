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

function is_blank($s) {
    return preg_match("/^\s*$/", $s) === 1;
}

function is_valid_isbn($isbn) {
    // TODO
    return true;
}

function is_pos_int($s) {
    // TODO
    return true;
}

function sanitise($s) {
    global $dbc;
    return mysqli_real_escape_string($dbc, htmlentities($s));
}

function authorise($username, $password) {
    if ($username === "test") {
        return "standard";
    } else if ($username === "admin") {
        return "admin";
    } else {
        return "";
    }
}

function get_publisher() {
    global $dbc;
    $username = sanitise($_SESSION["username"]);
    $q = "SELECT pub_id FROM user WHERE username = '$username'";
    $r = mysqli_query($q);
    if (!$r || mysqli_num_rows($dbc) != 1) {
        add_error("Failed to get publisher for $username (" . mysqli_error($dbc) . ")");
        return -1;
    }
    $pub_id = mysqli_fetch_array($r, MYSQLI_ASSOC)["pub_id"];
    mysqli_free_result($r);
    return $pub_id;
}

function errors_occurred() {
    return !empty($_SESSION["errors"]);
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
