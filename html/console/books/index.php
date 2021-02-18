<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Your books", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/books/";
?>

  <h2>Your books</h2>
  <main>
<?php
$authorised = authorised("list books");
display_status();
if ($authorised) {
    if (authorised("add book", array(), $errors=false)) { ?>
   <a class="card-list-item card-list-add-item" href="new/">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add new book</span>
   </a>
<?php
    }
    $books = fetch_books(); // Load all books editable by the user
    foreach ($books as $book) {
        $isbn = $book["isbn"];
        $title = $book["title"];
        $author = $book["author"];
        $resource_count = count_resources($isbn);
        echo "   <a class=\"card-list-item\" href=\"book?isbn=$isbn\">\n";
        echo "    <img src=\"covers/$isbn.png\" alt=\"Front cover of $title\" height=\"128\" />\n";
        echo "    <p>$title</p>\n";
        echo "    <p>$author</p>\n";
        echo "    <p>$resource_count resources</p>\n";
        echo "   </a>\n";
    }
} ?>
  </main>

<?php make_footer(); ?>
