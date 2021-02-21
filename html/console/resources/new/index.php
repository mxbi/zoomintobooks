<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/resources/new/";
make_header("Add resource", "", "");
?>

  <h2>Add resource</h2>
  <main>
<?php
$authorised = authorised("add resource");
display_status();
if ($authorised) {
    show_resource_form(false);
}
?>
  </main>
<?php
make_footer();
?>
