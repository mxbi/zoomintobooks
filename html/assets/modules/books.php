<?php
function fetch_book($isbn) {
    if (!authorised("view book", array("isbn" => $isbn))) return array();
    $isbn = sanitise($isbn);
    return db_select("SELECT b.* FROM book AS b WHERE isbn='$isbn'", true);
}

function fetch_books() {
    if (!authorised("list books")) return array();
    $username = sanitise($_SESSION["username"]);
    $q  = "SELECT b.* FROM book AS b ";
    $q .= "JOIN editable_by AS eb ON b.isbn = eb.isbn ";
    $q .= "AND eb.username = '$username' ";
    return db_select($q);
}

function can_edit_book($isbn) {
    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($isbn);
    return count(db_select("SELECT 1 FROM editable_by WHERE isbn = '$isbn' AND username = '$username'", true)) === 1;
}

function count_resources($isbn) {
    if (!authorised("view book", array("isbn" => $isbn))) return 0;
    $isbn = sanitise($isbn);
    return db_select("SELECT COUNT(rid) AS count FROM resource_instance WHERE isbn = '$isbn'", true)["count"];
}

function show_book_form($mode, $isbn=NULL) {
    if (($mode === "edit") && !authorised("edit book", array("isbn" => $isbn))) return;
    if (($mode === "new")  && !authorised("add book")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal book form mode");
        return;
    }
    $values = $_SESSION["sticky"];
    if ($mode == "edit") {
        $values = fetch_book($isbn);
        if (empty($values)) {
            add_error("Failed to load values for $isbn");
        }
    }
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
    <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
    <div class="input-container">
     <label for="isbn">ISBN</label>
     <input type="text" name="isbn" id="isbn-input" placeholder="ISBN" required="required" value="<?php echo $values["isbn"]; ?>" />
    </div>
    <div class="input-container">
     <label for="title">Title</label>
     <input type="text" name="title" id="title-input" placeholder="Title" required="required" value="<?php echo $values["title"]; ?>" />
    </div>
    <div class="input-container">
     <label for="author">Author</label>
     <input type="text" name="author" id="author-input" placeholder="Author" required="required" value="<?php echo $values["author"]; ?>" />
    </div>
    <div class="input-container">
     <label for="edition">Edition</label>
     <input type="number" name="edition" id="edition-input" value="<?php echo isset($values["edition"]) ? $values["edition"] : 1; ?>" min="1" step="1" />
    </div>
    <div class="input-container">
     <label for="book">Book upload</label>
     <input type="file" name="book" id="book-input" required="required" />
    </div>
    <input type="submit" value="<?php echo ($mode == "new") ? "Add book" : "Edit book" ; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

function add_book($values, $file) {
    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($values["isbn"]);
    $title = sanitise($values["title"]);
    $author = sanitise($values["author"]);
    $edition = sanitise($values["edition"]);
    $mode = sanitise($values["mode"]);
    if (($mode === "edit") && !authorised("edit book", array("isbn" => $isbn))) return false;
    if (($mode === "new")  && !authorised("add book")) return false;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal book form mode");
        return false;
    }

    global $dbc;

    $_SESSION["sticky"]["isbn"] = $isbn;
    $_SESSION["sticky"]["title"] = $title;
    $_SESSION["sticky"]["author"] = $author;
    $_SESSION["sticky"]["edition"] = $edition;

    if (empty($file)) add_error("No file uploaded");
    if (!is_valid_isbn($isbn)) add_error("ISBN is invalid");
    if (is_blank($title)) add_error("Title is blank");
    if (is_blank($author)) add_error("Author is blank");
    if (!is_pos_int($edition)) add_error("Edition is invalid");
    $publisher = fetch_publisher($_SESSION["username"])["publisher"];

    if (errors_occurred()) return false;

    // Perform updates to database and file system

    $type = get_type($file, MAX_BOOK_FILE_SIZE, BOOK_TYPES);
    $cover = generate_cover($file, $type, $isbn);
    if (!$cover) return false;
    $ar_blob = generate_ar_blob($file, $type);
    if (!$ar_blob) {
        add_error("Failed to generate AR blob");
        return false;
    }
    $ocr_blob = generate_ocr_blob($file, $type);
    if (!$ocr_blob) {
        add_error("Failed to generate OCR blob");
        return false;
    }
    $pattern_blob = generate_pattern_blob($file, $type);
    if (!$pattern_blob) {
        add_error("Failed to generate pattern blob");
        return false;
    }

    if ($mode === "new") {
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "INSERT INTO book(isbn, title, author, edition, publisher) VALUES ('$isbn', '$title', '$author', $edition, '$publisher')";
        $r = mysqli_query($dbc, $q);

        if (!$r || mysqli_affected_rows($dbc) != 1) {
            add_error("Failed to insert into book table (" . mysqli_error($dbc) . ")");
        } else {
            $q2 = "INSERT INTO editable_by(isbn, username) VALUES ('$isbn', '$username')";
            $r2 = mysqli_query($dbc, $q2);
            if (!$r2 || mysqli_affected_rows($dbc) != 1) {
                add_error("Failed to insert into editable_by table (" . mysqli_error($dbc) . ")");
            }
            mysqli_free_result($r2);
        }
        mysqli_free_result($r);

        if (errors_occurred()) {
            rollback_book();
        } else {
            if (mysqli_commit($dbc)) {
                set_success("Added $title");
                $_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
            } else {
                add_error("Commit failed");
                rollback_book();
            }
        }
    } else {
        // TODO: authorisation check
        // Update book
    }
}

function generate_cover($file, $type, $isbn) {
    $input = escapeshellarg($file['tmp_name']);
    $output = escapeshellarg("/var/www/zib/html/console/books/covers/$isbn");
    $size = 128;
    $out = null;
    $retval = null;
    $cmd = "pdftoppm $input $output -png -f 1 -singlefile -scale-to $size";
    $success = exec($cmd, $out, $retval);
    if ($success === false) {
        add_error("Failed to generate cover ($cmd failed with $retval: " . implode("\n", $out) . ")");
    } else {
        $success = true;
    }
    return $success;
}

function generate_ar_blob($file, $type) {
    return true;
}

function generate_ocr_blob($file, $type) {
    return true;
}

function generate_pattern_blob($file, $type) {
    return true;
}

function rollback_book() {
    // TODO: clean up files
    global $dbc;
    if (!mysqli_rollback($dbc)) {
        add_error("Rollback failed");
    }
}
?>
