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
    if (authorised("delete publisher", array("publisher" => $publisher), false)) {
        echo "   <hr />\n";
        echo "   <h3>Delete publisher</h3>\n<br />";
        echo "   <button type=\"button\" class=\"delete-btn\" onclick=\"askUser('Are you sure you want to delete this publisher?', 'deletePublisher', {'publisher': '$publisher'})\">Delete publisher</button>\n";
    }
}
?>
  </main>
<?php
make_footer();
?>
