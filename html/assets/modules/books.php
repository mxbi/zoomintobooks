<?php
function fetch_book($isbn) {
    if (!authorised("view book", array("isbn" => $isbn))) return;
    $isbn = sanitise($isbn);
    return db_select("SELECT b.* FROM book AS b WHERE isbn='$isbn'", true);
}

function fetch_books() {
    global $is_admin;
    if (!authorised("list books")) return;
    if ($is_admin) {
        return db_select("SELECT * FROM book");
    } else {
        $username = sanitise($_SESSION["username"]);
        $q  = "SELECT b.* FROM book AS b ";
        $q .= "JOIN book_editable_by AS eb ON b.isbn = eb.isbn ";
        $q .= "AND eb.username = '$username' ";
        return db_select($q);
    }
}

function book_exists($isbn) {
    $isbn = sanitise($isbn);
    $c = db_select("SELECT 1 FROM book WHERE isbn = '$isbn'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function can_edit_book($isbn) {
    global $is_admin;
    if ($is_admin) return true;
    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($isbn);
    $c = db_select("SELECT 1 FROM book_editable_by WHERE isbn = '$isbn' AND username = '$username'", true);
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
    $row = db_select("SELECT book_type FROM book WHERE isbn = '$isbn'", true);
    return $row ? $row["book_type"] : NULL;
}

function show_book_form($edit, $isbn=NULL) {
    global $is_admin;
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
   <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
<?php
if (!$edit) { ?>
    <div class="input-container">
     <label for="isbn">ISBN</label>
     <input type="text" name="isbn" id="isbn-input" placeholder="ISBN" required="required" value="<?php echo get_form_value("isbn", $values); ?>" />
    </div> <?php
} else { ?>
    <div class="input-container">
     <input type="hidden" name="isbn" id="isbn-input" value="<?php echo get_form_value("isbn", $values); ?>" />
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
<?php if ($is_admin) { ?>
    <div class="input-container">
     <label for="publisher">Publisher</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" required="required" value="<?php echo get_form_value("publisher", $values); ?>" />
    </div>
<?php } ?>
    <div class="input-container">
     <label for="edition">Edition</label>
     <input type="number" name="edition" id="edition-input" value="<?php echo get_form_value("edition", $values, $default=1); ?>" min="1" step="1" />
    </div>
    <div class="input-container">
     <label for="book">Book upload</label>
     <input type="file" name="book" id="book-input" <?php echo $edit ? "" : "required=\"required\""; ?> />
     <?php if ($edit) echo "<p><small>File already uploaded. You may upload a new one if you wish. If you leave this blank, the existing file will not be changed.</small></p>\n"; ?>
    </div>
    <input id="manage-book-btn" type="button" onclick="manageBook()" value="<?php echo $edit ? "Edit book" : "Add book" ; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

function manage_book($values, $file, $edit) {
    global $is_admin;
    global $dbc;

    $username = sanitise($_SESSION["username"]);
    $isbn = sanitise($values["isbn"]);
    $new_isbn = empty($values["new_isbn"]) ? NULL : sanitise($values["new_isbn"]);
    $title = sanitise($values["title"]);
    $author = sanitise($values["author"]);
    $edition = sanitise($values["edition"]);

    $publisher = NULL;
    if ($is_admin) {
        $publisher = sanitise($values["publisher"]);
    } else {
        $publisher = fetch_publisher($_SESSION["username"])["publisher"];
    }

    if (!$edit && !authorised("add book")) return false;
    if ($edit && !authorised("edit book", array("isbn" => $isbn))) return false;

    $_SESSION["sticky"]["isbn"] = $isbn;
    $_SESSION["sticky"]["new_isbn"] = $new_isbn;
    $_SESSION["sticky"]["title"] = $title;
    $_SESSION["sticky"]["author"] = $author;
    $_SESSION["sticky"]["edition"] = $edition;
    $_SESSION["sticky"]["publisher"] = $publisher;

    $file_present = !empty($file) && file_exists($file['tmp_name']) && is_uploaded_file($file['tmp_name']);

    if (!$edit && !$file_present) add_error("No file uploaded");
    if (!is_valid_isbn($isbn)) add_error("ISBN is invalid");
    if (is_blank($title)) add_error("Title is blank");
    if (is_blank($author)) add_error("Author is blank");
    if (!is_pos_int($edition)) add_error("Edition is invalid");
    if (!publisher_exists($publisher)) add_error("No such publisher");

    if (errors_occurred()) return false;

    // Perform updates to database and file system

    $type = NULL;

    if (!$edit) {
        $type = get_type($file, MAX_BOOK_FILE_SIZE, BOOK_TYPES);
        if ($type) {
            mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
            $q = "INSERT INTO book(isbn, title, author, edition, publisher, book_type) VALUES ('$isbn', '$title', '$author', $edition, '$publisher', '$type')";
            $r = mysqli_query($dbc, $q);

            if (!$r) {
                add_error(mysqli_error($dbc));
            } else {
                $q2 = "INSERT INTO book_editable_by(isbn, username) VALUES ('$isbn', '$username')";
                $r2 = mysqli_query($dbc, $q2);
                if (!$r2) add_error(mysqli_error($dbc));
            }
        } else {
            add_error("Failed to determine type for uploaded file");
        }
    } else {
        $type = db_select("SELECT book_type FROM book WHERE isbn = '$isbn'", true)["book_type"];
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "UPDATE book SET isbn='$new_isbn', title='$title', author='$author', edition=$edition, publisher='$publisher'" . ($file_present ? ", book_type='$type'" : "") . " WHERE isbn='$isbn'";
        $r = mysqli_query($dbc, $q);
        if (!$r) add_error(mysqli_error($dbc));
    }

    if (!errors_occurred()) {
        if ($file_present) {
            $cover = generate_cover($file, $type, $isbn);
            $upload = upload_book($file, $type, $isbn);
            $images = extract_images($file, $type, $isbn);
            if (!$cover || !$upload || !$images) add_error("Failed to upload book");
        } else if ($edit && $new_isbn !== $isbn) { // Editing ISBN without changing file
            $success = rename(book_cover_path($isbn), book_cover_path($new_isbn));
            if (!$success) add_error("Failed to move cover");
            $old_upload_path = book_upload_path($isbn, $type);
            $new_upload_path = book_upload_path($new_isbn, $type);
            $success = $success && rename($old_upload_path, $new_upload_path);
            if (!$success) add_error("Failed to move uploaded file");
        }
    }

    if (errors_occurred()) {
        rollback_book($isbn, $type);
    } else {
        if (mysqli_commit($dbc)) {
            if ($edit) {
                set_success("Updated $title");
                $_SESSION["redirect"] = "/console/books/book?isbn=$new_isbn";
            } else {
                set_success("Added $title");
                $_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
            }
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
    $cmd = "/usr/bin/pdftoppm $input $output -png -f 1 -singlefile -scale-to $size";
    $success = exec($cmd);
    if ($success === false) {
        add_error("Failed to generate cover");
    } else {
        $success = true;
    }
    return $success;
}

function upload_book($file, $type, $isbn) {
    return move_uploaded_file($file["tmp_name"], book_upload_path($isbn, $type));
}

function extract_images($file, $type, $isbn) {
    $input = escapeshellarg(book_upload_path($isbn, $type));
    $dir = book_images_path($isbn);
    if (!mkdir($dir, 0774)) {
        add_error("Failed to make image directory $dir");
        return false;
    } else {
        return true;
        /*$escdir = escapeshellarg($dir);
        $cmd = "cd $escdir && /usr/bin/pdfimages -png $input image";
        $success = exec($cmd);
        if ($success === false) add_error("Failed to extract images");
        else $success = true;
        return $success;*/
    }
}

function rollback_book($isbn, $type) {
    // TODO: rollback to previous uploads if editing fails rather than deleting
    global $dbc;
    if (!mysqli_rollback($dbc)) add_error("Database rollback failed");
    //if (!unlink(book_cover_path($isbn))) add_error("Book cover rollback failed");
    //if (!unlink(book_upload_path($isbn, $type))) add_error("Book upload rollback failed");
}

function update_blobs($isbn) {
    global $dbc;
    if (!authorised("edit book", array("isbn" => $isbn))) return;

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);

    generate_ar_blob($isbn);
    if (!errors_occurred()) generate_ocr_blob($isbn);

    if (errors_occurred()) {
        if (!mysqli_rollback($dbc)) {
            add_error("Database rollback failed");
        }
    } else {
        if (mysqli_commit($dbc)) {
            set_success("Generated blobs");
        } else {
            add_error("Database commit failed");
        }
        if (!mysqli_rollback($dbc)) {
            add_error("Database rollback failed");
        }
    }

}

function generate_image_list($isbn) {
    $dir = book_images_path($isbn);
    $imglist = "$dir/imglist";
    $f = fopen($imglist, "w");
    $i = 0;
    foreach (scandir("$dir") as $img) {
        if ($img != "." && $img != ".." && $img != "imglist") {
            fwrite($f, "$i|$dir/$img|0.1\n");
        }
    }
    fclose($f);
    return $imglist;
}

function generate_ar_blob($isbn) {
    global $dbc;
    $imglist = generate_image_list($isbn);
    $out_path = ar_blob_output_path($isbn);
    $cmd = "/usr/bin/arcoreimg build-db --input_image_list_path=$imglist --output_db_path=$out_path";
    $output = null;
    $ret = null;
    $success = exec($cmd, $output, $ret);
    $out_str = mysqli_real_escape_string($dbc, implode("\n", $output));
    if ($success) {
        $f = fopen($out_path, "r");
        $ar_blob = mysqli_real_escape_string($dbc, fread($f, filesize($out_path)));
        fclose($f);
        $q = "UPDATE book SET ar_blob='$ar_blob' WHERE isbn='$isbn'";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    } else {
        add_error("Failed to generate AR blob (error: $ret)\n$out_str $cmd");
    }
}

function generate_ocr_blob($isbn) {
    global $dbc;
    $type = get_book_type($isbn);
    $pdf = book_upload_path($isbn, $type);
    $pages_result = db_select("SELECT page FROM ocr_resource_link WHERE isbn = '$isbn'");
    if (empty($pages_result)) return;
    $pages = array();
    foreach ($pages_result as $result) {
            $pages[] = $result["page"];
    }
    $pages_str = implode(",", $pages);
    $cmd = "/usr/bin/python3 /var/www/zib/ocr/extract_pdf.py $pdf $pages_str -";
    $output = null;
    $ret = null;
    $success = exec($cmd, $output, $ret);
    $out_str = mysqli_real_escape_string($dbc, implode("\n", $output));
    if ($success) {
        $q = "UPDATE book SET ocr_blob='$out_str' WHERE isbn='$isbn'";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    } else {
        add_error("Failed to generate OCR blob (error: $ret)\n$out_str");
    }
}
?>
