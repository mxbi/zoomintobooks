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
    $q = "SELECT COUNT(rid) AS c FROM has_resource WHERE isbn = '$isbn'";
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

function add_book($values, $file) {
    global $dbc;

    $isbn = sanitise($values["isbn"]);
    $title = sanitise($values["title"]);
    $author = sanitise($values["author"]);
    $edition = sanitise($values["edition"]);

    if (empty($file)) add_error("No file uploaded");
    if (!is_valid_isbn($isbn)) add_error("ISBN is invalid");
    if (is_blank($title)) add_error("Title is blank");
    if (is_blank($author)) add_error("Author is blank");
    if (!is_pos_int($edition)) add_error("Edition is invalid");
    $pub_id = get_publisher();

    if (errors_occurred()) return;

    // Perform updates to database and file system

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_WRITE);
    $q = "INSERT INTO book VALUES ('$isbn', '$title', '$author', $edition, $pub_id)";
    $r = mysqli_query($dbc, $q);

    if (!$r || mysqli_affected_rows($dbc) != 1) {
        add_error("Failed to insert into book table (" . mysqli_error($dbc) . ")");
    }

    if (!errors_occurred()) {
        $type = get_type($file, MAX_BOOK_FILE_SIZE, BOOK_TYPES);
        if ($type) {
            if (generate_cover($file, $type)) {
                generate_ocr_blob($file, $type);
            }
        }
    }

    if (errors_occurred()) {
        add_book_rollback();
    }
}

function generate_cover($file, $type) {
    return true;
}

function generate_ocr_blob($file, $type) {
    return true;
}

function add_book_rollback() {
    global $dbc;
    // TODO: remove files
    mysqli_rollback($dbc);
}
?>
