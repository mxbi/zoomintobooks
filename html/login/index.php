<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Login", "", "");
?>

  <h2>Login</h2>
  <main id="login-form">
<?php
if (!logged_in()) {
    display_status();
?>
   <form action="action.php" method="POST">
    <input class="login-field" type="text" name="username" id="username-entry" placeholder="Username" required="required" />
    <input class="login-field" type="password" name="password" id="password-entry" placeholder="Password" required="required" />
    <input type="submit" value="Login" />
   </form>
<?php
} else {
    echo "   <p>You are currently logged in as " . $_SESSION["username"] . ".</p>\n    <p>You must <a href=\"/logout/\">log out</a> before you can log in again.</p>\n";
}
?>
  </main>

<?php
make_footer();
?>
