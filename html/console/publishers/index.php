<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Manage publishers", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/publishers/";
?>

  <h2>Manage publishers</h2>
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
        echo "   <a class=\"card-list-item\" href=\"publisher?publisher=$name\">\n";
        echo "    <p>$name</p>\n";
        echo "   </a>\n";
    }
} ?>
  </main>

<?php make_footer(); ?>
