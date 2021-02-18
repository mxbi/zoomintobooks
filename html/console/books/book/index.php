<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = $_GET["isbn"];
$_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
$book = fetch_book($isbn);
$title = ($book === NULL) ? "Unknown book" : $book["title"];

make_header($title, "", "");

echo "   <h2>$title</h2>\n";
echo "   <main>\n";

if ($book === NULL) {
    echo "    <p>The requested book does not exist.</p>\n";
} else if (!$is_logged_in) {
    echo "    <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} else if (!can_edit_book($isbn)) {
    echo "    <p>You do not have the required permissions to view this page.</p>\n";
} else {
    echo "    <h3>Edit book properties</h3>\n";
    show_book_form("edit", $isbn);
    echo "    <h3>Resources for this book</h3>\n";
?>
   <a class="card-list-item card-list-add-item" href="/console/resources/new?isbn=$isbn">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add new resource</span>
   </a>
<?php
    $resources = fetch_resources($isbn);
    show_resources($resources);
}
echo "   </main>\n";
make_footer();
?>
