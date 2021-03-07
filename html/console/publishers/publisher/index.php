<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$publisher = empty($_GET["publisher"]) ? NULL : sanitise($_GET["publisher"]);
$title = $publisher ? "Edit $publisher" : "Unknown publisher";
make_header($title, "", "");
?>

  <h2><?php echo $title; ?></h2>
  <a class="back" href="/console/publishers/">&laquo; Publishers</a>
  <main>
<?php
$authorised = authorised("edit publisher", array("publisher" => $publisher));
display_status();
if ($authorised) {
    show_publisher_form(true, $publisher);
}
?>
  </main>
<?php
make_footer();
?>
