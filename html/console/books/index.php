<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Your books", "The Zoom Into Books management console", "");
?>

  <h2>Your books</h2>
  <a class="back" href="/console">&laquo; Console</a>
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
        $edition = $book["edition"];
        $publisher = $book["publisher"];
        $resource_count = count_resources($isbn);
        echo "   <div class=\"card-list-item\">\n";
        echo "    <a href=\"book/upload?isbn=$isbn\">\n";
        echo "     <img src=\"book/cover?isbn=$isbn\" class=\"preview\" alt=\"Front cover of $title\" height=\"128\" />\n";
        echo "    </a>\n";
        echo "    <a class=\"button card-list-btn\" href=\"book?isbn=$isbn\">Edit</a>\n";
        echo "    <h4>$title<small> - edition $edition</small></h4>\n";
        echo "    <p>By <strong>$author</strong></p>\n";
        if ($is_admin) echo "<p>Published by <strong>$publisher</strong></p>\n";
        echo "    <p>$resource_count " . (($resource_count == 1) ? "resource" : "resources") . "</p>\n";
        echo "   </div>\n";
    }
} ?>
  </main>

<?php make_footer(); ?>
