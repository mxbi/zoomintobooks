<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = $_GET["isbn"];
$_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
$book = fetch_book($isbn);
$title = ($book === NULL) ? "Unknown book" : $book["title"];

make_header($title, "", "");

echo "   <h2>$title</h2>\n";
echo "   <main>\n";

$authorised = authorised("edit book", array("isbn" => $isbn));
display_status();
if ($authorised) {
    echo "    <h3>Edit book properties</h3>\n";
    show_book_form("edit", $isbn);
    echo "    <h3>Resources for this book</h3>\n";
    if (authorised("add resource", array("isbn" => $isbn), $errors=false)) {
?>
   <a class="card-list-item card-list-add-item" href="resource/new?isbn=$isbn">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add resource to book</span>
   </a>
<?php
    }
    $resources = fetch_resources($isbn);
    show_resources($resources);
}
echo "   </main>\n";
make_footer();
?>
