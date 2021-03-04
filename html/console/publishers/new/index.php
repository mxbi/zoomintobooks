<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Add publisher", "", "");
?>

  <h2>Add publisher</h2>
  <main>
<?php
$authorised = authorised("add publisher");
display_status();
if ($authorised) {
    show_publisher_form(false);
}
?>
  </main>
<?php
make_footer();
?>
