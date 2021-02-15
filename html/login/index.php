<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Login", "", "");
?>

  <h2>Login</h2>
  <main id="login-form">
<?php
display_status();
if (!$is_logged_in) {
?>
   <form action="action.php" method="POST">
    <div class="input-container">
     <label for="username">Username</label>
     <input class="login-field" type="text" name="username" id="username-entry" placeholder="Username" required="required" />
    </div>
    <div class="input-container">
     <label for="password">Password</label>
     <input class="login-field" type="password" name="password" id="password-entry" placeholder="Password" required="required" />
    </div>
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
