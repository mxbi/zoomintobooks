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
?>
    <p>To do</p>
<?php
}
echo "   </main>\n";
make_footer();
?>
