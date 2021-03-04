<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = empty($_GET["isbn"]) ? NULL : sanitise($_GET["isbn"]);
$book = fetch_book($isbn);
$title = ($book === NULL) ? "Unknown book" : $book["title"];

make_header($title, "", "");
echo "   <script src=\"/assets/scripts/utils.js\"></script>\n";
echo "   <h2>$title</h2>\n";
echo "   <main>\n";

$authorised = authorised("edit book", array("isbn" => $isbn));
display_status();
if ($authorised) {
    echo "    <h3>Edit book properties</h3>\n";
    show_book_form(true, $isbn);
    echo "    <h3>Resources linked to this book</h3>\n";
    echo "    <button id=\"update-triggers-btn\" onclick=\"updateBlobs('$isbn')\">Update triggers</button>"; // TODO: explain better, only show when resource links changed
?>
   <a class="card-list-item card-list-add-item" href="resource/new?isbn=<?php echo $isbn;?>">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Link resource to book</span>
   </a>
<?php
    if (authorised("list resources", array("isbn" => $isbn), false)) {
        $resources = fetch_book_resources($isbn, "ar");
        foreach ($resources as $resource) {
            $rid = $resource["rid"];
            $name = $resource["name"];
            $url = $resource["url"];
            $display = $resource["display"];
            $ar_count = db_select("SELECT COUNT(1) AS ar_count FROM ar_resource_link WHERE isbn='$isbn' AND rid='$rid'", true)["ar_count"];
            $ocr_pages = db_select("SELECT page FROM ocr_resource_link WHERE isbn='$isbn' AND rid='$rid'");
            $pages = array();
            foreach ($ocr_pages as $ocr_page) {
                $pages[] = $ocr_page["page"];
            }
            $ocr_count = count($ocr_pages);
            echo "   <a class=\"card-list-item\" href=\"/console/resources/resource?rid=$rid\">\n";
            echo "    <img src=\"/console/resources/resource/preview?rid=$rid\" class=\"preview\" alt=\"Preview of $name\" height=\"128\" />\n";
            echo "    <h4>$name</h4>\n";
            echo "    <p>$url</p>\n";
            echo "    <p>Displayed as $display</p>\n";
            echo "    <p>Triggered by $ar_count " . ($ar_count == 1 ? "image" : "images") . "</p>\n";
            echo "    <p>Triggered by " . count($pages) . ($ocr_count == 1 ? " page: " : ($ocr_count == 0 ? " pages" : " pages: ")) . implode(", ", $pages) . "</p>\n";
            echo "   </a>\n";
        }
    }
}
echo "   </main>\n";
make_footer();
?>
