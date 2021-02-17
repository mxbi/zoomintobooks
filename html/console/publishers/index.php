<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Manage publishers", "The Zoom Into Books management console", "");
$_SESSION["redirect"] = "/console/publishers/";
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
    <span>Add new publisher</span>
   </a>
<?php
        $publishers = fetch_publishers(); // Load all users by this user
        foreach ($publishers as $publisher) {
            // TODO: show preview of users and books associated with publisher
            $pub_id = $publisher["pub_id"];
            $name = $publisher["name"];
            echo "   <a class=\"card-list-item\" href=\"publisher?pub_id=$pub_id\">\n";
            echo "    <p>$name</p>\n";
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
