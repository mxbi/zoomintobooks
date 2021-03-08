<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";


$isbn = isset($_GET["isbn"]) ? sanitise($_GET["isbn"]) : -1;
$authorised = authorised("view book", array("isbn" => $isbn));

if ($authorised) {
    $type = get_book_type($isbn);
    $path = book_upload_path($isbn, $type);
    if (file_exists($path)) {
        header("Content-Type: $type");
        $fp = fopen($path, 'rb');
        fpassthru($fp);
        header("Content-Length: " . filesize($path));
        header("Content-Disposition: inline; filename=\"$isbn." . get_subtype($type) . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Accept-Ranges: bytes");
    } else {
        $title = fetch_book($isbn)["title"];
        make_header("View $title", "", "");
        echo "  <h2>$title</h2>";
        echo "  <main>\n";
        add_error("No upload for $title");
        display_status();
        echo "  </main>\n";
        make_footer();
    }
} else {
    make_header("View book", "", "");
?>
  <h2>View book</h2>
  <main>
<?php display_status(); ?>
  </main>
<?php
    make_footer();
}
