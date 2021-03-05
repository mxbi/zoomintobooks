<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Your resources", "The Zoom Into Books management console", "");
?>

  <h2>Your resources</h2>
  <main>
<?php
$authorised = authorised("list resources");
display_status();
if ($authorised) {
    if (authorised("add resource", array(), $errors=false)) { ?>
   <a class="card-list-item card-list-add-item" href="new/">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add new resource</span>
   </a>
<?php
    }
    $resources = fetch_resources(); // Load all books editable by the user
    foreach ($resources as $resource) {
        $rid = $resource["rid"];
        $name = $resource["name"];
        $url = $resource["url"];
        $display = $resource["display"];
        echo "   <div class=\"card-list-item\">\n";
        echo "    <a href=\"$url\">\n";
        echo "     <img src=\"/console/resources/resource/preview?rid=$rid\" class=\"preview\" alt=\"Preview of $name\" height=\"128\" />\n";
        echo "    </a>\n";
        echo "    <a class=\"button card-list-btn\" href=\"resource?rid=$rid\">Edit</a>\n";
        echo "    <h4>$name</h4>\n";
        echo "    <p>Hosted at <a href=\"$url\">$url</a></p>\n";
        echo "    <p>Displayed as $display</p>\n";
        echo "   </div>\n";
    }
} ?>

  </main>

<?php make_footer(); ?>
