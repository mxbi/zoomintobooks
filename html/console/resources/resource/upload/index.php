<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";


$rid = isset($_GET["rid"]) ? sanitise($_GET["rid"]) : -1;
//$authorised = authorised("view resource", array("rid" => $rid));
$type = get_resource_mime_type($rid);

if ($type) {
    $path = resource_upload_path($rid, $type);
    if (file_exists($path)) {
        header("Content-Type: $type");
        $fp = fopen($path, 'rb');
        fpassthru($fp);
        header("Content-Length: " . filesize($path));
        header("Content-Transfer-Encoding: binary");
        header("Accept-Ranges: bytes");
    } else {
        header("Content-Type: image/png");
        generate_text_image(array("No upload"));
    }
} else {
    header("Content-Type: image/png");
    generate_text_image(array("Resource not", "hosted here"));
}
ob_flush();
