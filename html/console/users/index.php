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
   <a class="card-list-item card-list-add-item" href="new/">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add new user</span>
   </a>
<?php
        $users = fetch_users(); // Load all users by this user
        foreach ($users as $user) {
            $username = $user["username"];
            $publisher = get_publisher($username)["name"];
            $author = $book["author"];
            echo "   <a class=\"card-list-item\" href=\"user?username=$username\">\n";
            echo "    <p>$username</p>\n";
            echo "    <p>$publisher</p>\n";
            echo "   </a>\n";
        }
    } else {
        echo "   <p>You do not have the required permissions to view this page.</p>\n";
    }
} else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} ?>
  </main>

<?php make_footer(); ?>
