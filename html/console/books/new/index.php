<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/books/new/";
make_header("Add book", "", "");
?>

  <h2>Add book</h2>
  <main>
<?php
$authorised = authorised("add book");
display_status();
if ($authorised) {
    show_book_form(false, 0);
}
?>
  </main>
<?php
make_footer();
?>
