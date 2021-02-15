<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

define("MAX_BOOK_FILE_SIZE", 50000000);
define("MAX_RESOURCE_FILE_SIZE", 50000000);
define("BOOK_TYPES", array("pdf"));
define("RESOURCE_TYPES", array("jpeg", "png", "gif", "webm", "tiff"));

define("UPLOAD_ERROR_MSGS", array("unknown error", "file size exceeds php.ini directive", "file size exceeds MAX_FILE_SIZE", "file only partially uploaded", "no file uploaded", "missing temporary directory", "disk write failed", "blocked by PHP extension"));

require $_SERVER["DOCUMENT_ROOT"] . "/../db_conn.php";
$root = $_SERVER["DOCUMENT_ROOT"] . "/assets/modules";
foreach (glob("$root/*.php") as $path) {
    if ($path != $root . "/includes.php") {
        include $path;
    }
}

init();
?>
