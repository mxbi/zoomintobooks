<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Your books", "The Zoom Into Books management console", "");
?>

  <h2>Your books</h2>
  <main>
<?php if (logged_in()) { ?>

<?php } else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} ?>
  </main>

<?php make_footer(); ?>
