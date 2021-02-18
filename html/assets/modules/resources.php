<?php
function fetch_resources($isbn) {
    if (!authorised("list resources", array("isbn" => $isbn))) return;
    $isbn = sanitise($isbn);
    $q  = "SELECT r.* FROM resource AS r ";
    $q .= "JOIN resource_instance AS ri ON ri.rid = r.rid AND ri.isbn = '$isbn'";
    return db_select($q);
}

function show_resources($resources)  {
    if (!authorised("list resources")) return;
    foreach ($resources as $resource) {
        $rid = $resource["rid"];
        $thumb = $resource["thumb"]; // TODO
        $url = $resource["url"];
        $type = $resource["type"];
        $downloadable = $resource["downloadable"];
        echo "   <a class=\"resource-list-item\" href=\"/console/resources/resource?rid=$rid\">\n";
        echo "    <img src=\"/console/resources/thumb?rid=$rid.png\" alt=\"Resource thumbnail\" />\n";
        echo "    <p>URL: <a href=\"$url\">$url</a></p>\n";
        echo "    <p>Type: $type</p>\n";
        echo "    <p>Downloadable: $downloadable</p>\n";
        echo "   </a>\n";
    }
}

function can_edit_resource($rid) {
    // TODO;
    return true;
}
?>
