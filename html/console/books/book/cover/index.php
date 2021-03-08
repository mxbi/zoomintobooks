<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$isbn = sanitise($_GET["isbn"]);
header("Content-Type: image/png");
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    generate_text_image(array("Request", "method", "must be GET"));
} else if (authorised("view book", array("isbn" => $isbn), false)) {
    $path = book_cover_path($isbn);
    if (file_exists($path)) {
        $fp = fopen($path, 'rb');
        if ($fp !== false) {
            header("Content-Length: " . filesize($path));
            fpassthru($fp);
        } else {
            generate_text_image(array("Cover IO", "failed"));
        }
    } else {
        generate_text_image(array("No cover"));
    }
} else {
    generate_text_image(array("Insufficient", "permissions", "to view cover"));
}
ob_flush();
