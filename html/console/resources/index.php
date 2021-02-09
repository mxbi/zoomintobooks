<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Your resources", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/resources/";
?>

  <h2>Your resources</h2>
  <main>
<?php
display_status();
if (logged_in()) { ?>

<?php } else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} ?>

  </main>

<?php make_footer(); ?>
