<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Management console", "The Zoom Into Books management console", "");
?>

  <h2>Management console</h2>
  <main>
<?php if (is_logged_in()) { ?>

   <a href="books/">View your books</a>
   <a href="resources/">View your resources</a>

<?php } else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} ?>
  </main>

<?php make_footer(); ?>
