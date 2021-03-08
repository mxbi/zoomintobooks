<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Add book", "", "");
?>

  <h2>Add book</h2>
  <a class="back" href="/console/books/">&laquo; Books</a>
  <main>
<?php
$authorised = authorised("add book");
display_status();
if ($authorised) {
    show_book_form(false);
}
?>
  </main>
<?php
make_footer();
?>
