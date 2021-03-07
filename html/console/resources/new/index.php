<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Add resource", "", "");

if (strpos($_SERVER["HTTP_REFERER"], "/console/books/book/resource/new") !== false) {
    $_SESSION["redirect"] = $_SERVER["HTTP_REFERER"]; // If page accessed from resource link page
}

?>

  <h2>Add resource</h2>
  <a class="back" href="/console/resources/">&laquo; Resources</a>
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
