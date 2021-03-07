<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$username = empty($_GET["username"]) ? NULL : sanitise($_GET["username"]);
$title = $username ? "Edit $username" : "Unknown user";
make_header($title, "", "");
?>

  <h2><?php echo $title; ?></h2>
  <a class="back" href="/console/users/">&laquo; Users</a>
  <main>
<?php
$authorised = authorised("edit user", array("username" => $username));
display_status();
if ($authorised) {
    show_user_form(true, $username);
    if (authorised("delete user", array("username" => $username), false)) {
        echo "   <hr />\n";
        echo "   <h3>Delete user</h3>\n<br />";
        echo "   <button type=\"button\" class=\"delete-btn\" onclick=\"askUser('Are you sure you want to delete this user?', 'deleteUser', {'username': '$username'})\">Delete user</button>\n";
    }
}
?>
  </main>
<?php
make_footer();
?>
