<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Manage publishers", "The Zoom Into Books management console", "");
?>

  <h2>Manage publishers</h2>
  <a class="back" href="/console">&laquo; Console</a>
  <main>
<?php
$authorised = authorised("list publishers");
display_status();
if ($authorised) {

    if (authorised("add publisher", array(), $errors=false)) {
    ?>
   <a class="card-list-item card-list-add-item" href="new/">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add new publisher</span>
   </a>
<?php
    }
    $publishers = fetch_publishers(); // Load all users by this user
    foreach ($publishers as $publisher) {
        // TODO: show preview of users and books associated with publisher
        $name = $publisher["publisher"];
        $email = $publisher["email"];
        echo "   <div class=\"card-list-item\">\n";
        echo "    <a class=\"button card-list-btn\" href=\"publisher?publisher=$name\">Edit</a>\n";
        echo "    <h4>$name</h4>\n";
        echo "    <a href=\"mailto:$email\">$email</a>\n";
        echo "   </div>\n";
    }
} ?>
  </main>

<?php make_footer(); ?>
