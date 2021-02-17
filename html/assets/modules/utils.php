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

function get_type($file, $max_size, $legal_types) {
    $path = $file["tmp_name"];
    $file_info = exec("file -i $path", $output, $status);
    if ($status) {
        add_error("Could not safely determine file type (" . UPLOAD_ERROR_MSGS[$file["error"]] . ")");
    } else {
        $type = explode("/", explode(";", explode(":", $file_info)[1])[0])[1]; //Extract file subtype from MIME type output from "file" command
        if (!in_array($type, $legal_types)) {
            add_error("File type $type is not allowed");
        }
        if ($_FILES["resource"]["size"] > $max_size) {
            add_error("File is too large");
        }
        if (!isset($_FILES["resource"]["error"]) || is_array($_FILES["resource"]["error"])) {
            add_error("Invalid parameters");
        }
        if (!errors_occurred()) {
            return $type;
        }
    }
    return false;
}

?>
