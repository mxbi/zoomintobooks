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
    if (!isset($_SESSION["notices"])) {
        $_SESSION["notices"] = array();
    }

    $is_admin = isset($_SESSION["account_type"]) && $_SESSION["account_type"] === "admin";
    $is_logged_in = isset($_SESSION["username"]);
}

function is_blank($s) {
    return preg_match("/^\s*$/", $s) === 1;
}

function is_valid_url($url) {
    // TODO
    return true;
}

function is_valid_resource_display_mode($type) {
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
    $_SESSION["errors"] = array();
}

function add_notice($msg) {
    $_SESSION["notices"][] = $msg;
}

function clear_notices() {
    unset($_SESSION["notices"]);
    $_SESSION["notices"] = array();
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
        clear_errors();
    }

    if (!empty($_SESSION["notices"])) {
        echo "   <div class=\"notices\">\n";
        echo "    <ul>\n";
        foreach ($_SESSION["notices"] as $notice) {
            echo "     <li>$notice</li>\n";
        }
        echo "    </ul>\n";
        echo "   </div>\n";
        clear_notices();
    }

    if (!empty($_SESSION["success"])) {
        echo "   <div class=\"success\">" . $_SESSION["success"] . "</div>\n";
        $_SESSION["success"] = "";
    }
}

function get_type($file, $max_size, $legal_subtypes) {
    $path = escapeshellarg($file["tmp_name"]);
    $file_info = exec("file -i $path", $output, $status);
    if ($status) {
        add_error("Could not safely determine file type (" . UPLOAD_ERROR_MSGS[$file["error"]] . ")");
    } else {
        $type = explode(";", explode(":", $file_info)[1])[0]; //Extract file subtype from MIME type output from "file" command
        if (!in_array(get_subtype($type), $legal_subtypes)) {
            add_error("File type $type is not allowed");
        }
        if ($file["size"] > $max_size) {
            add_error("File is too large");
        }
        if (!isset($file["error"]) || is_array($file["error"])) {
            add_error("Invalid parameters");
        }
        if (!errors_occurred()) {
            return $type;
        }
    }
    return false;
}

function get_subtype($type) {
    return explode("/", $type)[1];
}

function authorised($action, $params=array(), $errors=true) {
    global $is_logged_in;
    global $is_admin;
    if (!$is_logged_in) {
        if ($errors) add_error("You must <a href=\"/login/\">log in</a> first");
        return false;
    }

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
                $authorised = book_exists($params["isbn"]) && can_edit_book($params["isbn"]);
            } else { // List owned resources
                $authorised = true;
            }
            break;

        case "edit book":
        case "view book":
            $authorised = book_exists($params["isbn"]) && can_edit_book($params["isbn"]);
            break;

        case "edit resource":
        case "view resource":
            $authorised = resource_exists($params["rid"]) && can_edit_resource($params["rid"]);
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
    mysqli_commit($dbc);
    return $result;
}

function book_cover_path_no_ext($isbn) {
    return "/var/www/zib/books/covers/$isbn";
}

function book_cover_path($isbn) {
    return book_cover_path_no_ext($isbn) . ".png";
}

function book_upload_path($isbn, $type) {
    return "/var/www/zib/books/uploads/$isbn." . get_subtype($type);
}

function book_images_path($isbn) {
    return "/var/www/zib/books/images/$isbn";
}

function resource_upload_path($rid, $type) {
    return "/var/www/zib/resources/uploads/$rid." . get_subtype($type);
}

function resource_preview_path($rid) {
    return "/var/www/zib/resources/previews/$rid.png";
}

function ar_blob_output_path($isbn) {
    return "/var/www/zib/books/ar/$isbn.imgdb";
}

function generate_text_image($lines, $w=100, $h=128) {
    $img = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 0, 0, $w-1, $h-1, $black);
    $font = $_SERVER["DOCUMENT_ROOT"] . "/assets/fonts/open_sans/OpenSans-Regular.ttf";
    $top = 20;
    foreach ($lines as $line) {
        imagettftext($img, 10, 0, 10, $top, $white, $font, $line);
        $top += 20;
    }
    imagepng($img);
    imagedestroy($img);
}

?>
