<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Login", "", "");
?>

  <main>
   <h2>Login</h2>

<?php
if (!isset($_SESSION["username"])) {
?>
   <form action="action.php" method="POST">
    <label for="username-entry">Username</label>
    <input type="text" name="username" id="username-entry" placeholder="Username" required="required" />
    <label for="password-entry">Password</label>
    <input type="password" name="password" id="password-entry" placeholder="Password" required="required" />
    <input type="submit" value="Login" />
   </form>
<?php
} else {
    echo "   <p>You are already logged in as " . $_SESSION["username"] . ". You must <a href=\"/logout/\">log out before you can log in again.</p>\r\n";
}
?>
  </main>

<?php
make_footer();
?>
