<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require $_SERVER["DOCUMENT_ROOT"] . "/../db_conn.php";
$root = $_SERVER["DOCUMENT_ROOT"] . "/assets/modules";
foreach (glob("$root/*.php") as $path) {
    if ($path != $root . "/includes.php") {
        include $path;
    }
}

init();
?>
