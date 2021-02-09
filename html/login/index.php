<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Login", "", "");
?>

  <main>
   <h2>Login</h2>

<?php
if (!isset($_SESSION["username"])) {
?>
   <form action="action.php" method="POST" id="login-form">
    <input class="login-field" type="text" name="username" id="username-entry" placeholder="Username" required="required" />
    <input class="login-field" type="password" name="password" id="password-entry" placeholder="Password" required="required" />
    <input type="submit" value="Login" />
   </form>
<?php
} else {
    echo "   <p>You are already logged in as " . $_SESSION["username"] . ". You must <a href=\"/logout/\">log out before you can log in again.</p>\n";
}
?>
  </main>

<?php
make_footer();
?>
