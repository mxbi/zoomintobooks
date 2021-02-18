<?php
function fetch_book($isbn) {
    global $dbc;
    $isbn = sanitise($isbn);
    $q  = "SELECT b.* FROM book AS b WHERE isbn='$isbn'";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error("Failed to fetch book $isbn (" . mysqli_error($dbc) . ")");
        return 0;
    }
    $book = mysqli_fetch_array($r, MYSQLI_ASSOC);
    mysqli_free_result($r);
    return $book;
}

function fetch_books() {
    global $dbc;
    $username = sanitise($_SESSION["username"]);
    $q  = "SELECT b.* FROM book AS b ";
    $q .= "JOIN editable_by AS eb ON b.isbn = eb.isbn ";
    $q .= "AND eb.username = '$username' ";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error("Failed to fetch books (" . mysqli_error($dbc) . ")");
        return 0;
    }
    $books = mysqli_fetch_all($r, MYSQLI_ASSOC);
    mysqli_free_result($r);
    return $books;
}

function can_edit_book($isbn) {
    global $dbc;
    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($isbn);
    $q = "SELECT 1 FROM editable_by WHERE isbn = '$isbn' AND username = '$username'";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error("Failed to determine if book $isbn is editable by $username (" . mysqli_error($dbc) . ")");
        return false;
    }
    $editable = (mysqli_num_rows($r) === 1) ? true : false;
    mysqli_free_result($r);
    return $editable;
}

function count_resources($isbn) {
    global $dbc;
    $isbn = sanitise($isbn);
    $q = "SELECT COUNT(rid) AS c FROM resource_instance WHERE isbn = '$isbn'";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error("Failed to count resources for book $isbn (" . mysqli_error($dbc) . ")");
        return 0;
    }
    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
    mysqli_free_result($r);
    if ($row === NULL) {
        return 0;
    } else {
        return $row["c"];
    }
}

function show_book_form($mode) {
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
    <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
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
     <input type="file" name="book" id="book-input" required="required" />
    </div>
    <input type="submit" value="Add book" />
   </form>
<?php
}

function add_book($values, $file) {
    global $dbc;

    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($values["isbn"]);
    $title = sanitise($values["title"]);
    $author = sanitise($values["author"]);
    $edition = sanitise($values["edition"]);
    $mode = sanitise($values["mode"]);

    if (empty($file)) add_error("No file uploaded");
    if (!is_valid_isbn($isbn)) add_error("ISBN is invalid");
    if (is_blank($title)) add_error("Title is blank");
    if (is_blank($author)) add_error("Author is blank");
    if (!is_pos_int($edition)) add_error("Edition is invalid");
    $pub_id = get_publisher($_SESSION["username"])["pub_id"];

    if (errors_occurred()) return;

    // Perform updates to database and file system

    if ($mode === "new") {
        // TODO: uniqueness check

        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "INSERT INTO book VALUES ('$isbn', '$title', '$author', $edition, $pub_id)";
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

        if (!errors_occurred()) {
            $type = get_type($file, MAX_BOOK_FILE_SIZE, BOOK_TYPES);
            if ($type) {
                if (generate_cover($file, $type)) {
                    if (generate_ocr_blob($file, $type)) {

                    } else {
                        add_error("Failed to generate OCR blob");
                    }
                } else {
                    add_error("Failed to generate cover thumbnail");
                }
            }
        }

        if (errors_occurred()) {
            // TODO: remove files
            if (!mysqli_rollback($dbc)) {
                add_error("Rollback failed");
            }
        } else {
            if (mysqli_commit($dbc)) {
                set_success("Added $title");
                $_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
            } else {
                add_error("Commit failed");
                // TODO: rollback all
            }
        }
    } else {
        // TODO: authorisation check
        // Update book
    }
}

function generate_cover($file, $type) {
    return true;
}

function generate_ocr_blob($file, $type) {
    return true;
}
?>
