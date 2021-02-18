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

} ?>

  </main>

<?php make_footer(); ?>
