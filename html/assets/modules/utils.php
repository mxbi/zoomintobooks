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
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function is_valid_resource_display_mode($type) {
    return in_array($type, DISPLAY_MODES);
}

function is_valid_isbn($isbn) {
    $isbn = preg_replace('/[^\d]/', '', $isbn);
    $sum = 0;
    $len = strlen($isbn);
    $check = "";
    if ($len === 10) {
        $digits = str_split(substr($isbn, 0, 9));

        foreach ($digits as $index => $digit) {
            $sum += (10 - $index) * $digit;
        }

        $check = 11 - ($sum % 11);

        // 10 -> X, 11 -> 0
        if ($check == 10) $check = "X";
        else if ($check == 11) $check = "0";
    } else if ($len === 13) {
        $digits = str_split(substr($isbn, 0, 12));
        foreach($digits as $index => $digit) {
            $sum += ($index % 2) ? $digit * 3 : $digit;
        }
        $check = (10 - $sum % 10);
        if ($check == 10) $check = "0";
    } else {
        return false;
    }

    if ($check == substr($isbn, -1)) {
        return $isbn;
    } else {
        return false;
    }
}

function is_pos_int($s) {
    return is_numeric($s) && $s > 0 && $s == round($s);
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

function json_status() {
    $status = array("errors" => $_SESSION["errors"], "notices" => $_SESSION["notices"], "success" => $_SESSION["success"]);
    if (empty($_SESSION["redirect"])) {
        clear_errors();
        clear_notices();
        $_SESSION["success"] = "";
    } else {
        $status["redirect"] = $_SESSION["redirect"];
        unset($_SESSION["redirect"]);
    }
    header("Content-Type: application/json");
    echo json_encode($status);
}

function get_type($file, $max_size, $legal_subtypes) {
    $path = escapeshellarg($file["tmp_name"]);
    $file_info = exec("/usr/bin/file -i $path", $output, $status);
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
        $_SESSION["redirect"] = $_SERVER["REQUEST_URI"];
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

function generate_random_string($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function file_rollback($tmps) {
    if (!$tmps) return true;
    foreach ($tmps as $tmp => $path) {
        if ((file_exists($path) && !rrm($path)) || (file_exists($tmp) && !rcp($tmp, $path))) {
            add_error("Failed to rollback file operation");
            return false;
        }
    }
    return true;
}

function file_commit($tmp, $path) {
    if (!$tmps) return true;
    foreach ($tmps as $tmp => $path) {
        if (file_exists($tmp) && !rrm($tmp)) {
            add_error("Failed to commit file operation");
            file_rollback($tmp, $path);
            return false;
        }
    }
    return true;
}

function file_ops($ops) {
    $tmps = array();
    foreach ($ops as $op) {
        $path = $op["path"];
        $tmp = "";
        do {
            $tmp = "/var/www/zib/tmp/" . generate_random_string();
        } while (file_exists($tmp));

        if (file_exists($path) && !rcp($path, $tmp)) {
            add_error("Failed to make backup before executing file operation");
            file_rollback($tmps);
            return false;
        }
        $tmps[$tmp] = $path;

        $type = $op["type"];
        if ($type === "mv upload") {
            if (!move_uploaded_file($op["file"]["tmp_name"], $path)) {
                add_error("Failed to move uploaded file");
            }
        } else if ($type === "cp upload") {
            if (!is_uploaded_file($op["file"]["tmp_name"]) || !rcp($op["file"]["tmp_name"], $path)) {
                add_error("Failed to copy uploaded file $path");
            }
        } else if ($type === "cmd") {
            $out = NULL;
            $ret = NULL;
            $success = exec(escapeshellcmd($op["cmd"]), $out, $ret);
            if ($success === false) {
                add_error($op["error"] . " " . $op["cmd"] . " (code: $ret): " . implode("\n", $out));
            }
        } else if ($type === "mkdir") {
            if (!mkdir($path, $op["permission"])) {
                add_error("Failed to make directory");
            }
        } else if ($type === "cp") {
            if (file_exists($op["src"]) && !rcp($op["src"], $path)) {
                add_error("Failed to copy file");
            }
        } else if ($type === "rm") {
            if (file_exists($path) && !rrm($path)) {
                add_error("Failed to remove file");
            }
        } else {
            add_error("Unknown file operation: $type");
        }
        if (errors_occurred()) {
            file_rollback($tmps);
            return false;
        }
    }
    return $tmps;
}

function rollback($dbc, $tmps) {
    if (!mysqli_rollback($dbc) && file_rollback($tmps)) {
        add_error("Rollback failed");
        return false;
    }
    return true;
}

function commit($dbc, $tmps) {
    if (!mysqli_commit($dbc) && file_commit($tmps)) {
        add_error("Commit failed");
        rollback($dbc, $tmps);
        return false;
    }
    return true;
}


//https://www.php.net/manual/en/function.copy.php#104020
function rrm($dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        $success = true;
        foreach ($files as $file) {
            if ($file != "." && $file != "..") $success = $success && rrm("$dir/$file");
        }
        return $success && rmdir($dir);
    } else if (file_exists($dir)) {
        return unlink($dir);
    } else {
        return false;
    }
}

function rcp($src, $dst) {
    if (file_exists($dst)) {
        if (!rrm($dst)) return false;
    }
    if (is_dir($src)) {
        if (!mkdir($dst, 0744)) return false;
        $success = true;
        $files = scandir($src);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") $success = $success && rcp("$src/$file", "$dst/$file");
        }
        return $success;
    } else if (file_exists($src)) {
        return copy($src, $dst);
    } else {
        return false;
    }
}
