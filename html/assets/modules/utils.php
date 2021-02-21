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
}

function is_blank($s) {
    return preg_match("/^\s*$/", $s) === 1;
}

function is_valid_url($url) {
    // TODO
    return true;
}

function is_valid_resource_type($type) {
    // TODO
    return true;
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

function get_form_value($key, $values, $default=NULL) {
    if (isset($values[$key])) {
        return $values[$key];
    } else if (isset($_SESSION["sticky"][$key])) {
        return $_SESSION["sticky"][$key];
    } else {
        return $default;
    }
}

function errors_occurred() {
    return !empty($_SESSION["errors"]);
}

function add_error($msg) {
    $_SESSION["errors"][] = $msg;
}

function clear_errors() {
    unset($_SESSION["errors"]);
}

function set_success($msg) {
    $_SESSION["success"] = $msg;
}

function display_status() {
    if (!empty($_SESSION["errors"])) {
        echo "   <div class=\"errors\">\n";
        echo "    <ul>\n";
        foreach ($_SESSION["errors"] as $error) {
            echo "     <li>$error</li>\n";
        }
        echo "    </ul>\n";
        echo "   </div>\n";
        $_SESSION["errors"] = array();
    }

    if (!empty($_SESSION["success"])) {
        echo "   <div class=\"success\">" . $_SESSION["success"] . "</div>\n";
        $_SESSION["success"] = "";
    }
}

function get_type($file, $max_size, $legal_types) {
    $path = escapeshellarg($file["tmp_name"]);
    $file_info = exec("file -i $path", $output, $status);
    if ($status) {
        add_error("Could not safely determine file type (" . UPLOAD_ERROR_MSGS[$file["error"]] . ")");
    } else {
        $type = explode("/", explode(";", explode(":", $file_info)[1])[0])[1]; //Extract file subtype from MIME type output from "file" command
        if (!in_array($type, $legal_types)) {
            add_error("File type $type is not allowed");
        }
        if ($_FILES["book"]["size"] > $max_size) {
            add_error("File is too large");
        }
        if (!isset($_FILES["book"]["error"]) || is_array($_FILES["book"]["error"])) {
            add_error("Invalid parameters");
        }
        if (!errors_occurred()) {
            return $type;
        }
    }
    return false;
}

function authorised($action, $params=array(), $errors=true) {
    if (!isset($_SESSION["username"])) {
        if ($errors) add_error("You must <a href=\"/login/\">log in</a> first");
        return false;
    }
    $is_admin = $_SESSION["account_type"] == "admin";

    $authorised = false;
    switch ($action) {
        case "view console":
        case "add book":
        case "add resource":
        case "list books":
            $authorised = true;
            break;

        case "list resources":
            if (isset($params["isbn"])) { // List resources for a book
                $authorised = book_exists($params["isbn"]) && ($is_admin || can_edit_book($params["isbn"]));
            } else { // List owned resources
                $authorised = true;
            }
            break;

        case "edit book":
        case "view book":
            $authorised = book_exists($params["isbn"]) && ($is_admin || can_edit_book($params["isbn"]));
            break;

        case "edit resource":
        case "view resource":
            $authorised = resource_exists($params["rid"]) && ($is_admin || can_edit_resource($params["rid"]));
            break;

        case "view user":
        case "edit user":
            $authorised = $is_admin || $params["username"] === $_SESSION["username"];
            break;

        case "view publisher":
        case "add user":
        case "add publisher":
        case "edit publisher":
        case "list users":
        case "list publishers":
            $authorised = $is_admin;
            break;

        default:
            if ($errors) add_error("Attempted to perform unknown action (action: $action)");
            return false;
    }

    if (!$authorised && $errors) {
        add_error("You do not have the required permissions (action: $action)");
    }
    return $authorised;
}

function db_select($q, $one=false) {
    global $dbc;
    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_ONLY);
    $result = array();
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error(mysqli_error($dbc));
    } else {
        if (!$one) { // Fetch all
            $result = mysqli_fetch_all($r, MYSQLI_ASSOC);
        } else {
            $rows = mysqli_num_rows($r);
            if ($rows <= 1) {
                $result = mysqli_fetch_array($r, MYSQLI_ASSOC);
            } else {
                add_error("Expected at most 1 result, got $rows");
            }
        }
        mysqli_free_result($r);
    }
    return $result;
}

function book_cover_path_no_ext($isbn) {
    return "/var/www/zib/books/covers/$isbn";
}

function book_cover_path($isbn) {
    return book_cover_path_no_ext($isbn) . ".png";
}

function book_upload_path($isbn, $type) {
    return "/var/www/zib/books/uploads/$isbn.$type";
}
?>
