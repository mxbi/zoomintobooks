<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
make_header("Management console", "The Zoom Into Books management console", "");
?>

  <h2>Management console</h2>
  <main>
<?php if (logged_in()) { ?>

   <div class="card-container">
    <a class="card" href="books/"><img src="/assets/images/icons/book-stack-128.png" alt=""/><span class="card-label">Manage your books</span></a>
    <a class="card" href="books/new/"><img src="/assets/images/icons/plus-5-128.png" alt="" /><span class="card-label">Add a new book</span></a>
    <a class="card" href="resources/"><img src="/assets/images/icons/pages-1-128.png" alt="" /><span class="card-label">View your resources</span></a>
   </div>

<?php } else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
} ?>
  </main>

<?php make_footer(); ?>
