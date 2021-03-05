<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$username = empty($_GET["username"]) ? NULL : sanitise($_GET["username"]);
$title = $username ? "Edit $username" : "Unknown user";
make_header($title, "", "");
?>

  <h2><?php echo $title; ?></h2>
  <main>
<?php
$authorised = authorised("edit user", array("username" => $username));
display_status();
if ($authorised) {
    show_user_form(true, $username);
}
?>
  </main>
<?php
make_footer();
?>
