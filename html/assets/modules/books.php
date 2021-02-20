<?php
function fetch_book($isbn) {
    if (!authorised("view book", array("isbn" => $isbn))) return;
    $isbn = sanitise($isbn);
    return db_select("SELECT b.* FROM book AS b WHERE isbn='$isbn'", true);
}

function fetch_books() {
    if (!authorised("list books")) return;
    $username = sanitise($_SESSION["username"]);
    $q  = "SELECT b.* FROM book AS b ";
    $q .= "JOIN editable_by AS eb ON b.isbn = eb.isbn ";
    $q .= "AND eb.username = '$username' ";
    return db_select($q);
}

function book_exists($isbn) {
    $isbn = sanitise($isbn);
    $c = db_select("SELECT 1 FROM book WHERE isbn = '$isbn'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function can_edit_book($isbn) {
    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($isbn);
    $c = db_select("SELECT 1 FROM editable_by WHERE isbn = '$isbn' AND username = '$username'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function count_resources($isbn) {
    if (!authorised("view book", array("isbn" => $isbn))) return 0;
    $isbn = sanitise($isbn);
    return db_select("SELECT COUNT(rid) AS count FROM resource_instance WHERE isbn = '$isbn'", true)["count"];
}

function get_book_type($isbn) {
    if (!authorised("view book", array("isbn" => $isbn))) return;
    $isbn = sanitise($isbn);
    $row = db_select("SELECT type FROM book WHERE isbn = '$isbn'", true);
    return $row ? $row["type"] : NULL;
}

function show_book_form($edit, $isbn=NULL) {
    if (!$edit && !authorised("add book")) return;
    if ($edit && !authorised("edit book", array("isbn" => $isbn))) return;
    $values = array();
    if ($edit) {
        $values = fetch_book($isbn);
        if (empty($values)) {
            add_error("Failed to load values for $isbn");
            return;
        }
    }
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
<?php
if (!$edit) { ?>
    <div class="input-container">
     <label for="isbn">ISBN</label>
     <input type="text" name="isbn" id="isbn-input" placeholder="ISBN" required="required" value="<?php echo get_form_value("isbn", $values); ?>" />
    </div> <?php
} else { ?>
    <div class="input-container">
     <input type="hidden" name="isbn" value="<?php echo get_form_value("isbn", $values); ?>" />
     <label for="new_isbn">ISBN</label>
     <input type="text" name="new_isbn" id="new-isbn-input" placeholder="ISBN" required="required" value="<?php echo get_form_value("isbn", $values); ?>" />
    </div> <?php
} ?>
    <div class="input-container">
     <label for="title">Title</label>
     <input type="text" name="title" id="title-input" placeholder="Title" required="required" value="<?php echo get_form_value("title", $values); ?>" />
    </div>
    <div class="input-container">
     <label for="author">Author</label>
     <input type="text" name="author" id="author-input" placeholder="Author" required="required" value="<?php echo get_form_value("author", $values); ?>" />
    </div>
    <div class="input-container">
     <label for="edition">Edition</label>
     <input type="number" name="edition" id="edition-input" value="<?php echo get_form_value("edition", $values, $default=1); ?>" min="1" step="1" />
    </div>
    <div class="input-container">
     <label for="book">Book upload</label>
     <input type="file" name="book" id="book-input" <?php echo $edit ? "" : "required=\"required\""; ?> />
     <?php if ($edit) echo "<p><small>File already uploaded. You may upload a new one if you wish. If you leave this blank, the existing file will not be changed.</small></p>\n"; ?>
    </div>
    <input type="submit" value="<?php echo $edit ? "Edit book" : "Add book" ; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

function manage_book($values, $file, $edit) {
    global $dbc;

    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($values["isbn"]);
    $new_isbn = sanitise($values["new_isbn"]);
    $title = sanitise($values["title"]);
    $author = sanitise($values["author"]);
    $edition = sanitise($values["edition"]);

    if (!$edit && !authorised("add book")) return false;
    if ($edit && !authorised("edit book", array("isbn" => $isbn))) return false;

    $_SESSION["sticky"]["isbn"] = $isbn;
    $_SESSION["sticky"]["title"] = $title;
    $_SESSION["sticky"]["author"] = $author;
    $_SESSION["sticky"]["edition"] = $edition;

    $file_present = file_exists($file['tmp_name']) && is_uploaded_file($file['tmp_name']);

    if (!$edit && !$file_present) add_error("No file uploaded");
    if (!is_valid_isbn($isbn)) add_error("ISBN is invalid");
    if (is_blank($title)) add_error("Title is blank");
    if (is_blank($author)) add_error("Author is blank");
    if (!is_pos_int($edition)) add_error("Edition is invalid");

    if (errors_occurred()) return false;

    // Perform updates to database and file system

    $publisher = fetch_publisher($_SESSION["username"])["publisher"];

    $type = NULL;

    if ($file_present) {
        $type = get_type($file, MAX_BOOK_FILE_SIZE, BOOK_TYPES);
        $cover = generate_cover($file, $type, $isbn);
        $upload = upload_book($file, $type, $isbn);
        if (!$cover || !$upload) {
            rollback_book($isbn, $type);
            return false;
        }
    } // TODO: move book to new isbn on edit

    if (!$edit) {
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "INSERT INTO book(isbn, title, author, edition, publisher, type) VALUES ('$isbn', '$title', '$author', $edition, '$publisher', '$type')";
        $r = mysqli_query($dbc, $q);

        if (!$r) {
            add_error(mysqli_error($dbc));
        } else {
            $q2 = "INSERT INTO editable_by(isbn, username) VALUES ('$isbn', '$username')";
            $r2 = mysqli_query($dbc, $q2);
            if (!$r2) add_error(mysqli_error($dbc));
        }
    } else {
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "UPDATE book SET isbn='$new_isbn', title='$title', author='$author', edition=$edition, publisher='$publisher'" . ($file_present ? ", type='$type'" : "") . " WHERE isbn='$isbn'";
        $r = mysqli_query($dbc, $q);
        if (!$r) add_error(mysqli_error($dbc));
    }

    if (errors_occurred()) {
        rollback_book($isbn, $type);
    } else {
        if (mysqli_commit($dbc)) {
            set_success("Added $title");
            $_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
        } else {
            add_error("Commit failed");
            rollback_book($isbn, $type);
        }
    }
}

function generate_cover($file, $type, $isbn) {
    $input = escapeshellarg($file['tmp_name']);
    $output = escapeshellarg(book_cover_path_no_ext($isbn));
    $size = 128;
    $out = null;
    $retval = null;
    $cmd = "/usr/bin/pdftoppm $input $output -png -f 1 -singlefile -scale-to $size";
    $success = exec($cmd, $out, $retval);
    if ($success === false) {
        add_error("Failed to generate cover ($cmd failed with $retval: " . implode("\n", $out) . ")");
    } else {
        $success = true;
    }
    return $success;
}

function upload_book($file, $type, $isbn) {
    return move_uploaded_file($file["tmp_name"], book_upload_path($isbn, $type));
}

function rollback_book($isbn, $type) {
    // TODO: rollback to previous uploads if editing fails rather than deleting
    global $dbc;
    if (!mysqli_rollback($dbc)) add_error("Database rollback failed");
    if (!unlink(book_cover_path($isbn))) add_error("Book cover rollback failed");
    if (!unlink(book_upload_path($isbn, $type))) add_error("Book upload rollback failed");
}
?>
