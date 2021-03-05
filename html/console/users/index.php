<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Manage users", "The Zoom Into Books management console", "");
?>

  <h2>Manage users</h2>
  <main>
<?php
$authorised = authorised("list users");
display_status();
if ($authorised) {
    if (authorised("add user", array(), $errors=false)) {
    ?>
   <a class="card-list-item card-list-add-item" href="new/">
    <img src="/assets/images/icons/plus-5-128.png" alt="" />
    <span>Add new user</span>
   </a>
<?php
    }
    $users = fetch_users();
    foreach ($users as $user) {
        $username = $user["username"];
        $type = $user["user_type"];
        $publisher = $user["publisher"];
        echo "   <a class=\"card-list-item\" href=\"user?username=$username\">\n";
        echo "    <h4>$username</h4>\n";
        echo "    <p>" . ucfirst($type) . " account</p>\n";
        if ($publisher) {
            echo "    <p>Publisher: " . $publisher["publisher"] . "</p>\n";
        }
        echo "   </a>\n";
    }
} ?>
  </main>

<?php make_footer(); ?>
