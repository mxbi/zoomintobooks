<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = empty($_GET["isbn"]) ? NULL : sanitise($_GET["isbn"]);
$book = fetch_book($isbn);
$title = ($book === NULL) ? "Unknown book" : $book["title"];

make_header($title, "", "");
echo "   <h2>$title</h2>\n";
echo "   <a class=\"back\" href=\"/console/books/\">&laquo; Books</a>\n";
echo "   <main>\n";

$authorised = authorised("edit book", array("isbn" => $isbn));
display_status();
if ($authorised) {
    echo "    <h3>Edit book properties</h3>\n";
    if (get_book_type($isbn) !== NULL) {
        echo "<div class=\"preview-container\"><a href=\"upload?isbn=$isbn\"><span>View</span><img class=\"preview\" id=\"book-preview-img\" src=\"cover?isbn=$isbn\" alt=\"\" /></a></div>\n";
    }
    show_book_form(true, $isbn);
    echo "    <hr />\n";
    echo "    <h3>Resources linked to this book</h3>\n";
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
            $trigger = "";
            if ($ar_count > 0) {
                $trigger .= $ar_count . ($ar_count == 1 ? " image" : " images");
                if ($ocr_count > 0) $trigger .= " and";
            }
            if ($ocr_count > 0) {
                $trigger .= " " . $ocr_count . ($ocr_count == 1 ? " page" : " pages");
            }

            echo "   <div class=\"card-list-item\" id=\"resource-container-$rid\">\n";
            echo "    <img src=\"/console/resources/resource/preview?rid=$rid\" class=\"preview\" alt=\"Preview of $name\" height=\"128\" />\n";
            echo "    <button class=\"card-list-btn delete-btn\" id=\"unlink-resource-btn-$rid\" onclick=\"askUser('Are you sure you want to unlink this resource?', 'unlinkResource', {'isbn': '$isbn', 'rid': $rid})\">Unlink</button>";
            echo "    <a class=\"button card-list-btn\" href=\"/console/resources/resource?rid=$rid\">Edit</a>";
            echo "    <h4>$name</h4>\n";
            echo "    <a href=\"$url\">$url</a>\n";
            echo "    <p>Displayed as $display</p>\n";
            echo "    <p>Triggered by $trigger</p>\n";
            echo "   </div>\n";
        }
    }
    if (authorised("delete book", array("isbn" => $isbn), false)) {
        echo "   <hr />\n";
        echo "   <h3>Delete book</h3>\n<br />";
        echo "   <button type=\"button\" id=\"book-delete-btn\" class=\"delete-btn\" onclick=\"askUser('Are you sure you want to delete this book?', 'deleteBook', {'isbn': '$isbn'})\">Delete book</button>\n";
    }
}
echo "   </main>\n";
make_footer();
?>
