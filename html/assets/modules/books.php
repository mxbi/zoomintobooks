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
    $values = array("isbn" => "", "title" => "", "author" => "", "publisher" => "", "edition" => "1");
    if ($edit) {
        $values = fetch_book($isbn);
        if (empty($values)) {
            add_error("Failed to load values for $isbn");
            return;
        }
    }
?>
   <form enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="100000000" />
<?php
if (!$edit) { ?>
    <div class="input-container">
     <label for="isbn">ISBN</label>
     <input type="text" name="isbn" id="isbn-input" placeholder="ISBN" required="required" value="<?php echo $values["isbn"]; ?>" />
    </div> <?php
} else { ?>
    <div class="input-container">
     <input type="hidden" name="isbn" id="isbn-input" value="<?php echo $values["isbn"]; ?>" />
     <label for="new_isbn">ISBN</label>
     <input type="text" name="new_isbn" id="new-isbn-input" placeholder="ISBN" required="required" value="<?php echo $values["isbn"]; ?>" />
    </div> <?php
} ?>
    <div class="input-container">
     <label for="title">Title</label>
     <input type="text" name="title" id="title-input" placeholder="Title" required="required" value="<?php echo $values["title"]; ?>" />
    </div>
    <div class="input-container">
     <label for="author">Author</label>
     <input type="text" name="author" id="author-input" placeholder="Author" required="required" value="<?php echo $values["author"]; ?>" />
    </div>
<?php if ($is_admin) { ?>
    <div class="input-container">
     <label for="publisher">Publisher</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" required="required" value="<?php echo $values["publisher"]; ?>" />
    </div>
<?php } ?>
    <div class="input-container">
     <label for="edition">Edition</label>
     <input type="number" name="edition" id="edition-input" value="<?php echo $values["edition"]; ?>" min="1" step="1" />
    </div>
    <div class="input-container">
     <label for="book" class="help" title="You may upload a PDF of your book to make use of text-based triggers">Book upload</label>
     <input type="file" name="book" id="book-input" />
     <?php if ($isbn !== NULL && get_book_type($isbn) !== NULL) echo "<p><small>File already uploaded. You may upload a new one if you wish. If you leave this blank, the existing file will not be changed.</small></p>\n"; ?>
    </div>
    <input id="manage-book-btn" type="button" onclick="manageBook()" value="<?php echo $edit ? "Edit book" : "Add book" ; ?>" />
   </form>
<?php
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
        $publisher = fetch_user_publisher($_SESSION["username"])["publisher"];
    }

    if (!$edit && !authorised("add book")) return false;
    if ($edit && !authorised("edit book", array("isbn" => $isbn))) return false;

    $file_present = !empty($file) && file_exists($file['tmp_name']) && is_uploaded_file($file['tmp_name']);

    //if (!$edit && !$file_present) add_error("No file uploaded");
    if (!$edit && !($isbn = is_valid_isbn($isbn))) add_error("ISBN is invalid");
    if ($new_isbn !== NULL && !($new_isbn = is_valid_isbn($new_isbn))) add_error("New ISBN is invalid");
    if (is_blank($title)) add_error("Title is blank");
    if (is_blank($author)) add_error("Author is blank");
    if (!is_pos_int($edition)) add_error("Edition is invalid");
    if (!publisher_exists($publisher)) add_error("No such publisher");

    if (errors_occurred()) return false;

    // Perform updates to database and file system

    $type = $file_present ? get_type($file, MAX_BOOK_FILE_SIZE, BOOK_TYPES) : NULL;

    if (!$edit) {
            mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
            $q = "";
            if ($file_present) {
                $q = "INSERT INTO book(isbn, title, author, edition, publisher, book_type) VALUES ('$isbn', '$title', '$author', $edition, '$publisher', '$type')";
            } else {
                $q = "INSERT INTO book(isbn, title, author, edition, publisher) VALUES ('$isbn', '$title', '$author', $edition, '$publisher')";
            }
            $r = mysqli_query($dbc, $q);

            if (!$r) {
                add_error(mysqli_error($dbc));
            } else {
                $q2 = "INSERT INTO book_editable_by(isbn, username) VALUES ('$isbn', '$username')";
                $r2 = mysqli_query($dbc, $q2);
                if (!$r2) add_error(mysqli_error($dbc));
            }
    } else {
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "UPDATE book SET isbn='$new_isbn', title='$title', author='$author', edition=$edition, publisher='$publisher'" . ($file_present ? ", book_type='$type'" : "") . " WHERE isbn='$isbn'";
        $r = mysqli_query($dbc, $q);
        if (!$r) add_error(mysqli_error($dbc));
    }

    $tmps = array();
    if (!errors_occurred()) {
        if (!$edit) {
            $img_dir = book_images_path($isbn);
            $make_img_dir_op = array("type" => "mkdir", "path" => $img_dir, "permission" => 0744);

            $ops = array($make_img_dir_op);

            $tmps = file_ops($ops);
        } else if ($edit && $new_isbn !== $isbn) { // Editing ISBN without changing file
            $ops = array();
            if ($type !== NULL) {
                $cp_cover_op = array("type" => "cp", "src" => book_cover_path($isbn), "path" => book_cover_path($new_isbn));
                $rm_cover_op = array("type" => "rm", "path" => book_cover_path($isbn));
                $cp_upload_op = array("type" => "cp", "src" => book_upload_path($isbn, $type), "path" => book_upload_path($new_isbn, $type));
                $rm_upload_op = array("type" => "rm", "path" => book_upload_path($isbn, $type));
                $ops[] = $cp_cover_op;
                $ops[] = $rm_cover_op;
                $ops[] = $cp_upload_op;
                $ops[] = $rm_upload_op;
            }
            $cp_ar_op = array("type" => "cp", "src" => ar_blob_output_path($isbn), "path" => ar_blob_output_path($new_isbn));
            $rm_ar_op = array("type" => "rm", "path" => ar_blob_output_path($isbn));
            $cp_imgs_op = array("type" => "cp", "src" => book_images_path($isbn), "path" => book_images_path($new_isbn));
            $rm_imgs_op = array("type" => "rm", "path" => book_images_path($isbn));

            $ops[] = $cp_ar_op;
            $ops[] = $rm_ar_op;
            $ops[] = $cp_imgs_op;
            $ops[] = $rm_imgs_op;
            $tmps = file_ops($ops);
        }
        if ($file_present) {
            $upload_isbn = $edit ? $new_isbn : $isbn;
            $input = $file['tmp_name'];
            $output = book_cover_path_no_ext($upload_isbn);
            $cover_cmd = "/usr/bin/pdftoppm $input $output -png -f 1 -singlefile -scale-to 128";
            $cover_op = array("type" => "cmd", "cmd" => $cover_cmd, "error" => "Failed to generate cover", "path" => book_cover_path($upload_isbn), "file" => $file);

            $upload_op = array("type" => "mv upload", "file" => $file, "path" => book_upload_path($upload_isbn, $type));
            $ops = array($cover_op, $upload_op);
            $tmps = array_merge($tmps, file_ops($ops));
        }

    }

    if (errors_occurred()) {
        rollback($dbc, $tmps);
    } else if (commit($dbc, $tmps)) {
        if ($edit) {
            set_success("Updated $title");
            $_SESSION["redirect"] = "/console/books/book?isbn=$new_isbn";
        } else {
            set_success("Added $title");
            $_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
        }
    }
}

function update_blobs($isbn) {
    global $dbc;
    if (!authorised("edit book", array("isbn" => $isbn))) return;

    $isbn = is_valid_isbn(sanitise($isbn));
    if (!$isbn) {
        add_error("Invalid ISBN");
        return;
    }

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);

    $tmps = generate_ar_blob($isbn);
    if (!errors_occurred()) generate_ocr_blob($isbn);

    if (errors_occurred()) {
        rollback($dbc, $tmps);
    } else if (commit($dbc, $tmps)) {
        set_success("Generated blobs");
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
    $arcoreimg_op = array("type" => "cmd", "cmd" => $cmd, "path" => $out_path, "error" => "Failed to generate AR blob");
    $ops = array($arcoreimg_op);
    $tmps = file_ops($ops);

    unlink($imglist);

    if (!file_exists($out_path)) {
        add_error("Failed to generate image triggers: make sure your images are detailed enough");
    }

    if (!errors_occurred()) {
        $f = fopen($out_path, "r");
        if (!$f) {
            add_error("Failed to generate image triggers");
            return;
        }
        $ar_blob = mysqli_real_escape_string($dbc, fread($f, filesize($out_path)));
        fclose($f);
        $q = "UPDATE book SET ar_blob='$ar_blob' WHERE isbn='$isbn'";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    }
    return $tmps;
}

function generate_ocr_blob($isbn) {
    global $dbc;
    $type = get_book_type($isbn);
    $pdf = escapeshellarg(book_upload_path($isbn, $type));
    $pages_result = db_select("SELECT page FROM ocr_resource_link WHERE isbn = '$isbn'");
    if (empty($pages_result)) return;
    $pages = array();
    foreach ($pages_result as $result) {
            $pages[] = $result["page"];
    }
    $pages_str = escapeshellarg(implode(",", $pages));
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
