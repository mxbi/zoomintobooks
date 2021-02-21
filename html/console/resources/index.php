<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Your resources", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/resources/";
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
    show_resources($resources);
} ?>

  </main>

<?php make_footer(); ?>
