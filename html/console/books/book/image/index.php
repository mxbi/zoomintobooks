<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = sanitise($_GET["isbn"]);
$img = sanitise($_GET["img"]);
header("Content-Type: image/png");
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    generate_text_image(array("Request", "method", "must be GET"));
} else if (authorised("view book", array("isbn" => $isbn), false)) {
    $path = book_images_path($isbn) . "/$img.png";
    if (file_exists($path)) {
        $fp = fopen($path, 'rb');
        fpassthru($fp);
        header("Content-Length: " . filesize($path));
    } else {
        generate_text_image(array("No such", "image"));
    }
} else {
    generate_text_image(array("Insufficient", "permissions", "to view image"));
}
ob_flush();
