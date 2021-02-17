<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/users/new/";
make_header("Add user", "", "");
?>

  <h2>Add user</h2>
  <main>
<?php
display_status();
if ($is_logged_in) {
    if ($is_admin) {
        show_user_form("new");
    } else {
        echo "   <p>You do not have the required permissions to view this page.</p>\n";
    }
} else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
}
?>
  </main>
<?php
make_footer();
?>
