<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";
$_SESSION["redirect"] = "/console/books/new/";
make_header("Add book", "", "");
?>

  <h2>Add book</h2>
  <main>
<?php
display_status();
if ($is_logged_in) {
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <div class="input-container">
     <label for="isbn">ISBN</label>
     <input type="text" name="isbn" id="isbn-input" placeholder="ISBN" required="required" />
    </div>
    <div class="input-container">
     <label for="title">Title</label>
     <input type="text" name="title" id="title-input" placeholder="Title" required="required" />
    </div>
    <div class="input-container">
     <label for="author">Author</label>
     <input type="text" name="author" id="author-input" placeholder="Author" required="required" />
    </div>
    <div class="input-container">
     <label for="edition">Edition</label>
     <input type="number" name="edition" id="edition-input" value="1" min="1" step="1" />
    </div>
    <div class="input-container">
     <label for="book">Book upload</label>
     <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
     <input type="file" name="book" id="book-input" required="required" />
    </div>
    <input type="submit" value="Submit" />
   </form>
<?php
} else {
    echo "   <p>You must <a href=\"/login/\">log in</a> to view this page.</p>\n";
}
?>
  </main>
<?php
make_footer();
?>
