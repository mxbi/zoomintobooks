<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$rid = sanitise($_GET["rid"]);
header("Content-Type: image/png");
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    generate_text_image(array("Request", "method", "must be GET"));
} else if (authorised("view resource", array("rid" => $rid), false)) {
    $path = resource_preview_path($rid);
    if (file_exists($path)) {
        $fp = fopen($path, 'rb');
        fpassthru($fp);
        header("Content-Length: " . filesize($path));
    } else {
        generate_text_image(array("No preview"));
    }
} else {
    generate_text_image(array("Insufficient", "permissions", "to view preview"));
}
ob_flush();
