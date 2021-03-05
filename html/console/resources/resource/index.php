<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$rid = empty($_GET["rid"]) ? NULL : sanitise($_GET["rid"]);
$resource = fetch_resource($rid);
$title = ($resource === NULL) ? "Unknown resource" : $resource["name"];

if (strpos($_SERVER["HTTP_REFERER"], "/console/books/book/resource/new") !== false) {
    $_SESSION["redirect"] = $_SERVER["HTTP_REFERER"]; // If page accessed from resource link page
}

make_header($title, "", "");

echo "   <h2>$title</h2>\n";
echo "   <main>\n";

$authorised = authorised("edit resource", array("rid" => $rid));
display_status();
if ($authorised) {
    echo "    <h3>Edit resource properties</h3>\n";
    show_resource_form(true, $rid);
    // TODO: show books using this resource
}
echo "   </main>\n";
make_footer();
?>
