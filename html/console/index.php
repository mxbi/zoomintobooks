<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Management console", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/";
?>

  <h2>Management console</h2>
  <main>
<?php
$authorised = authorised("view console");
display_status();
if ($authorised) {
    echo "   <div class=\"card-container\">";
    if (authorised("list books", array(), $errors=false))        echo "    <a class=\"card\" href=\"books/\"><img src=\"/assets/images/icons/book-stack-128.png\" alt=\"\"/><span class=\"card-label\">Manage your books</span></a>\n";
    if (authorised("add book", array(), $errors=false))          echo "    <a class=\"card\" href=\"books/new/\"><img src=\"/assets/images/icons/plus-5-128.png\" alt=\"\" /><span class=\"card-label\">Add a new book</span></a>\n";
    if (authorised("list resources", array(), $errors=false))    echo "    <a class=\"card\" href=\"resources/\"><img src=\"/assets/images/icons/pages-1-128.png\" alt=\"\" /><span class=\"card-label\">View your resources</span></a>\n";
    if (authorised("list users", array(), $errors=false))        echo "    <a class=\"card\" href=\"users/\"><img src=\"/assets/images/icons/group-128.png\" alt=\"\" /><span class=\"card-label\">Manage users</span></a>";
    if (authorised("list publishers", array(), $errors=false))   echo "    <a class=\"card\" href=\"publishers/\"><img src=\"/assets/images/icons/briefcase-6-128.png\" alt=\"\" /><span class=\"card-label\">Manage publishers</span></a>";
    echo "   </div>";
} ?>
  </main>

<?php make_footer(); ?>
