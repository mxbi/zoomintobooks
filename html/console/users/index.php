<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Manage users", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/users/";
?>

  <h2>Manage users</h2>
  <main>
<?php
display_status();
if ($is_logged_in) { 
    if ($is_admin) {
    ?>
        
    <?php
    } else {
        echo "   <p>You do not have the required permissions to view this page.</p>\n";
    }
} else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} ?>
  </main>

<?php make_footer(); ?>