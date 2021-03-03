<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/users/new/";
make_header("Add user", "", "");
?>

  <h2>Add user</h2>
  <main>
<?php
$authorised = authorised("add user");
display_status();
if ($authorised) {
    show_user_form(false);
}
?>
  </main>
<?php
make_footer();
?>
