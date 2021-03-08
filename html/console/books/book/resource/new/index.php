<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$isbn = empty($_GET["isbn"]) ? NULL : sanitise($_GET["isbn"]);
make_header("Resource linking", "", "");
$authorised = authorised("edit book", array("isbn" => $isbn));
if ($authorised) {
    $book = fetch_book($isbn);
    $title = $book["title"];
    echo "  <h2>Link resource to $title</h2>\n";
    echo "  <a class=\"back\" href=\"/console/books/book?isbn=$isbn\">&laquo; $title</a>\n  <main>";
} else {
    echo "  <h2>Unknown book</h2>\n";
    echo "  <a class=\"back\" href=\"/console/books/\">&laquo; Books</a>\n  <main>";
}
display_status();
if ($authorised) {
?>

    <h3>Select resources to link</h3>

    <div class="limited-height-scrollable">
     <a class="card-list-item card-list-add-item" href="/console/resources/new/">
      <img src="/assets/images/icons/plus-5-128.png" alt="" />
      <span>Add new resource</span>
     </a>

<?php
    $resources = fetch_resources();
    if (authorised("list resources")) {
        foreach ($resources as $resource) {
            $rid = $resource["rid"];
            $name = $resource["name"];
            $url = $resource["url"];
            $display = $resource["display"];
            echo "   <div class=\"selectable-card-list-item card-list-item\" id=\"selectable-resource-$rid\" onclick=\"toggleResourceSelect($rid)\">\n";
            echo "    <a href=\"$url\">\n";
            echo "     <img src=\"/console/resources/resource/preview?rid=$rid\" class=\"preview\" alt=\"Preview of $name\" height=\"128\" />\n";
            echo "    </a>\n";
            echo "    <a class=\"button card-list-btn\" href=\"/console/resources/resource?rid=$rid\">Edit</a>\n";
            echo "    <h4>$name</h4>\n";
            echo "    <p>Hosted at <a href=\"$url\">$url</a></p>\n";
            echo "    <p>Displayed as $display</p>\n";
            echo "   </div>\n";
        }
    }
?>
    </div>

    <h3>Add triggers</h3>

    <form>
     <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
     <input type="hidden" name="isbn" id="isbn-input" value="<?php echo $isbn; ?>" />
     <div class="input-container">
      <label for="trigger-images" class="help" title="Select zero or more images to use as triggers">Trigger image(s)</label>
      <input type="file" name="trigger-images" id="trigger-images-input" multiple="multiple" />
     </div>
     <div class="input-container">
      <label for="pages" class="help" title="Enter zero or more page numbers to use as triggers. Page numbers are relative to the PDF upload and must be separated by spaces">Page number(s)</label>
      <input type="text" name="pages" id="pages-input" placeholder="Pages" <?php echo (get_book_type($isbn) === NULL) ? "disabled=\"disabled\" title=\"You must upload a PDF to use this feature\"" : ""; ?>/>
     </div>
     <button id="resource-link-btn" type="button" onclick="submitResourceLink()">Link resource</button>
    </form>

<?php
}
echo "  </main>";
make_footer();
?>
