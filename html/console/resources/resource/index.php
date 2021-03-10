<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$rid = empty($_GET["rid"]) ? NULL : sanitise($_GET["rid"]);
$resource = fetch_resource($rid);
$title = ($resource === NULL) ? "Unknown resource" : $resource["name"];
$url = ($resource === NULL) ? NULL : $resource["url"];

if (!empty($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], "/console/books/book/resource/new") !== false) {
    $_SESSION["redirect"] = $_SERVER["HTTP_REFERER"]; // If page accessed from resource link page
}

make_header($title, "", "");

echo "   <h2>$title</h2>\n";
echo "   <a class=\"back\" href=\"/console/resources/\">&laquo; Resources</a>\n";
echo "   <main>\n";

$authorised = authorised("edit resource", array("rid" => $rid));
display_status();
if ($authorised) {
    echo "    <h3>Edit resource properties</h3>\n";
    echo "<div class=\"preview-container\"><a href=\"$url\"><span>View</span><img class=\"preview\" id=\"resource-preview-img\" src=\"preview?rid=$rid\" alt=\"\" /></a></div>\n";
    show_resource_form(true, $rid);
    if (authorised("delete resource", array("rid" => $rid), false)) {
        echo "   <hr />\n";
        echo "   <h3>Delete resource</h3>\n<br />";
        echo "   <button type=\"button\" id=\"resource-delete-btn\" class=\"delete-btn\" onclick=\"askUser('Are you sure you want to delete this resource?', 'deleteResource', {'rid': '$rid'})\">Delete resource</button>\n";
    }
}
echo "   </main>\n";
make_footer();
?>
