<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/books/new/";
make_header("Add book", "", "");
?>

  <h2>Add book</h2>
  <main>
<?php
display_status();
if ($is_logged_in) {
    show_book_edit_form("new");
} else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
}
?>
  </main>
<?php
make_footer();
?>
