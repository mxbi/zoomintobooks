<?php
function fetch_resource($rid) {
    if (!authorised("view resource", array("rid" => $rid))) return;
    $rid = sanitise($rid);
    return db_select("SELECT * FROM resource WHERE rid = $rid", true);
}

function fetch_resources() {
    global $is_admin;
    if (!authorised("list resources")) return;
    if ($is_admin) {
        return db_select("SELECT * FROM resource");
    } else {
        $username = sanitise($_SESSION["username"]);
        $q  = "SELECT r.* FROM resource AS r ";
        $q .= "JOIN resource_editable_by AS eb ON eb.rid = r.rid AND eb.username = '$username'";
        return db_select($q);
    }
}

function fetch_book_resources($isbn) {
    $isbn = sanitise($isbn);
    if (!authorised("list resources", array("isbn" => $isbn))) return;
    $q  = "SELECT r.* FROM resource AS r ";
    $q .= "JOIN resource_instance AS ri ON ri.rid = r.rid AND ri.isbn = '$isbn'";
    return db_select($q);
}

function resource_exists($rid) {
    if ($rid === NULL) return false;
    $rid = sanitise($rid);
    $c = db_select("SELECT 1 FROM resource WHERE rid = $rid", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function get_resource_mime_type($rid) {
    if ($rid === NULL) return NULL;
    $rid = sanitise($rid);
    $type = db_select("SELECT resource_type FROM resource WHERE rid = $rid", true);
    return $type ? $type["resource_type"] : NULL;
}

function was_resource_uploaded($rid) {
    return get_resource_mime_type($rid) ? true : false;
}

function can_edit_resource($rid) {
    global $is_admin;
    if ($is_admin) return true;
    $username = sanitise($_SESSION["username"]);
    if ($rid === NULL) return false;
    $rid = sanitise($rid);
    $c = db_select("SELECT 1 FROM resource_editable_by WHERE rid = $rid AND username = '$username'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function show_resource_form($edit, $rid=NULL) {
    if (!$edit && !authorised("add resource")) return;
    if ($edit && !authorised("edit resource", array("rid" => $rid))) return;
    $values = array("name" => "", "url" => "", "display" => "overlay", "downloadable" => "1");
    if ($edit) {
        $values = fetch_resource($rid);
        if (empty($values)) {
            add_error("Failed to load values for $rid");
            return;
        }
    }
    $rid = $edit ? $values["rid"] : -1;
    $display = $values["display"];
    $downloadable = ($values["downloadable"] === "1");
    $uploaded = was_resource_uploaded($rid);
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
    <input type="hidden" id="rid-input" name="rid" value="<?php echo $rid;?>" />
    <div class="input-container">
     <label for="name">Name</label>
     <input type="text" name="name" id="name-input" placeholder="Name" required="required" value="<?php echo $values["name"]; ?>" />
    </div>
    <div>
    <div class="tabs-container">
     <button type="button" onclick="showUrlInputContainer()" class="tab active-tab" id="url-input-tab">URL</button>
     <button type="button" onclick="showResourceInputContainer()" class="tab" id="resource-input-tab">Upload</button>
    </div>
    <div class="input-container tabbed-container" id="url-input-container">
     <label for="url">URL</label>
     <input type="text" name="url" id="url-input" placeholder="URL" value="<?php echo $values["url"]; ?>" />
<?php if ($uploaded) { echo "     <p><small>This resource was uploaded to this server</small></p>\n"; } ?>
    </div>
    <div class="input-container tabbed-container" id="resource-input-container" style="display: none">
     <label for="resource">Resource upload</label>
     <input type="file" name="resource" id="resource-input" />
     <p><small>Uploading a file will make it publicly accessible on our server.</small></p>
<?php if ($uploaded) { echo "     <p><small>Uploading another resource will overwrite the old one.</small></p>\n"; } ?>
    </div>
    </div>
    <div class="input-container">
     <label for="display">Display mode</label>
     <select name="display" id="display-input">
      <option value="overlay" <?php echo $display === "overlay" ? "selected=\"selected\"" : ""; ?>>AR overlay image</option>
      <option value="image" <?php echo $display === "image" ? "selected=\"selected\"" : ""; ?>>Normal image</option>
      <option value="video" <?php echo $display === "video" ? "selected=\"selected\"" : ""; ?>>Video</option>
      <option value="audio" <?php echo $display === "audio" ? "selected=\"selected\"" : ""; ?>>Audio</option>
      <option value="webpage" <?php echo $display === "webpage" ? "selected=\"selected\"" : ""; ?>>Webpage</option>
     </select>
    </div>
    <div class="input-container">
     <label for="downloadable">Downloadable</label>
     <input type="checkbox" value="downloadable" name="downloadable" id="downloadable-input" <?php echo $downloadable ? "checked=\"checked\"" : "" ;?> />
    </div>
    <input type="button" onclick="manageResource()" id="manage-resource-btn" value="<?php echo $edit ? "Edit resource" : "Add resource" ; ?>" />
   </form>
<?php
}

function manage_resource($file, $values, $edit) {
    global $dbc;

    $username = sanitise($_SESSION["username"]);
    $rid = empty($values["rid"]) ? -1 : sanitise($values["rid"]);
    $name = sanitise($values["name"]);
    $url = empty($values["url"]) ? NULL : sanitise($values["url"]);
    $display = sanitise($values["display"]);
    $downloadable = !empty($values["downloadable"]) && $values["downloadable"] === "downloadable";
    $downloadable = $downloadable ? 1 : 0;

    if (!$edit && !authorised("add resource")) return false;
    if ($edit && !authorised("edit resource", array("rid" => $rid))) return false;

    $file_present = !empty($file) && file_exists($file['tmp_name']) && is_uploaded_file($file['tmp_name']);

    if (!$file_present && !is_valid_url($url)) add_error("URL is invalid");
    if ($edit && !is_pos_int($rid)) add_error("rid is invalid");
    if (is_blank($name)) add_error("Name is blank");
    if (!is_valid_resource_display_mode($display)) add_error("Resource display mode is invalid");

    if (errors_occurred()) return false;

    if (!$edit) {
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "INSERT INTO resource(name, url, display, downloadable) VALUES ('$name', 'ERROR: URL NOT SET', '$display', $downloadable)";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        } else {
            $rid = mysqli_insert_id($dbc);
            $q2 = "INSERT INTO resource_editable_by(rid, username) VALUES ($rid, '$username')";
            $r2 = mysqli_query($dbc, $q2);
            if (!$r2) add_error(mysqli_error($dbc));
        }
    } else {
        mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
        $q = "UPDATE resource SET name='$name', url='ERROR: URL NOT SET', display='$display', downloadable=$downloadable WHERE rid='$rid'";
        $r = mysqli_query($dbc, $q);
        if (!$r) add_error(mysqli_error($dbc));
    }

    $tmps = array();
    $type = $file_present ? get_type($file, MAX_RESOURCE_FILE_SIZE, RESOURCE_TYPES) : NULL;
    if (!errors_occurred() && $type) {
        $path = resource_upload_path($rid, $type);
        $tmps = file_ops(array(array("type" => "mv upload", "file" => $file, "path" => $path)));
        if ($tmps) {
            $url = "https://uniform.ml/console/resources/resource/upload?rid=$rid";
        }
    }

    $q = "UPDATE resource SET url='$url', resource_type=" . ($type ? "'$type'" : "NULL") . " WHERE rid='$rid'";
    $r = mysqli_query($dbc, $q);
    if (!$r) add_error(mysqli_error($dbc));

    /*if (!errors_occurred()) {
        generate_thumb($rid, $url);
    }*/

    if (errors_occurred()) {
        rollback($dbc, $tmps);
    } else if (commit($dbc, $tmps)) {
        if ($edit) {
            set_success("Updated $name");
        } else {
            set_success("Added $name");
            if (empty($_SESSION["redirect"])) {
                $_SESSION["redirect"] = "/console/resources/resource?rid=$rid";
            }
        }
    }
}

function manage_resource_links($isbn, $resources, $trigger_images, $pages, $edit) {
    global $dbc;
    if (!authorised("edit book", array("isbn" => $isbn))) return;

    $isbn = is_valid_isbn(sanitise($isbn));
    if (!$isbn) add_error("Invalid ISBN");
    if (empty($resources)) add_error("No resources selected");
    if (empty($trigger_images) && empty($pages)) add_error("No triggers specified");

    if (errors_occurred()) return;

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $max = db_select("SELECT MAX(ar_id) AS max_ar_id FROM ar_resource_link WHERE isbn='$isbn'", true);
    $types = array();

    $linked = false;

    foreach ($trigger_images as $img) {
        $type = get_type($img, MAX_TRIGGER_IMAGE_FILE_SIZE, TRIGGER_IMAGE_TYPES);
        if ($type) {
            $types[$img["tmp_name"]] = $type;
        }
    }
    $tmps = array();
    if (!errors_occurred()) {
        $ar_id = empty($max["max_ar_id"]) ? 0 : $max["max_ar_id"];
        foreach ($trigger_images as $img) {
            $ext = get_subtype($types[$img["tmp_name"]]);

            foreach ($resources as $rid) {
                $ar_id++;
                $cp_upload_op = array("type" => "cp upload", "file" => $img, "path" => "/var/www/zib/books/images/$isbn/$ar_id.$ext");
                $this_tmp = file_ops(array($cp_upload_op));
                if (!$this_tmp) break;
                foreach ($this_tmp as $tmp => $path) {
                    $tmps[$tmp] = $path;
                }
                $rid = sanitise($rid);
                $q = "INSERT IGNORE INTO ar_resource_link (isbn, rid, ar_id, trigger_type) VALUES ('$isbn', $rid, $ar_id, '$type')";
                $r = mysqli_query($dbc, $q);
                if (!$r) {
                    add_error(mysqli_error($dbc));
                    break;
                } else if (mysqli_affected_rows($dbc) === 0) {
                    add_notice("Ignoring duplicate trigger image $ar_id");
                } else {
                    $linked = true;
                }
            }
            if (errors_occurred()) break;
        }
    }

    if (!errors_occurred()) {
        if (get_book_type($isbn) === NULL && !empty($pages)) {
            add_error("Cannot use page as trigger without PDF upload");
        } else {
            foreach ($resources as $rid) {
                $rid = sanitise($rid);
                foreach ($pages as $page) {
                    if (empty($page)) continue;
                    $q = "INSERT IGNORE INTO ocr_resource_link (isbn, rid, page) VALUES ('$isbn', $rid, $page)";
                    $r = mysqli_query($dbc, $q);
                    if (!$r) {
                        add_error(mysqli_error($dbc));
                        break;
                    } else if (mysqli_affected_rows($dbc) === 0) {
                        add_notice("Ignoring duplicate trigger page $page");
                    } else {
                        $linked = true;
                    }
                }
                $q = "INSERT IGNORE INTO resource_instance (isbn, rid) VALUES ('$isbn', $rid)";
                $r = mysqli_query($dbc, $q);
                if (!$r) {
                    add_error(mysqli_error($dbc));
                }
                if (errors_occurred()) break;
            }
        }
    }

    $tmps = array_merge($tmps, update_blobs($isbn));

    unset($_SESSION["redirect"]);
    if (errors_occurred()) {
        rollback($dbc, $tmps);
    } else if (commit($dbc, $tmps)) {
        if ($linked) set_success("Successfully linked resources to book");
        $_SESSION["redirect"] = "/console/books/book?isbn=$isbn";
    }
}

function unlink_resource($isbn, $rid) {
    global $dbc;
    if (!authorised("edit book", array("isbn" => $isbn))) return;

    $isbn = is_valid_isbn(sanitise($isbn));
    if (!$isbn) add_error("Invalid ISBN");
    if (!resource_exists($rid)) add_error("Invalid rid");

    if (errors_occurred()) return;

    // TODO: delete ar trigger images if not used by other resource links

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q1 = "DELETE FROM resource_instance WHERE isbn='$isbn' AND rid=$rid";
    $q2 = "DELETE FROM ar_resource_link WHERE isbn='$isbn' AND rid=$rid";
    $q3 = "DELETE FROM ocr_resource_link WHERE isbn='$isbn' AND rid=$rid";

    $r1 = mysqli_query($dbc, $q1);
    if (!$r1) {
        add_error(mysqli_error($dbc));
    } else {
        $r2 = mysqli_query($dbc, $q2);
        if (!$r2) {
            add_error(mysqli_error($dbc));
        } else {
            $r3 = mysqli_query($dbc, $q3);
            if (!$r3) add_error(mysqli_error($dbc));
        }
    }

    $tmps = update_blobs($isbn);

    foreach ($tmps as $tmp => $path) {
        add_notice("$tmp => $path");
    }

    if (errors_occurred()) {
        rollback($dbc, $tmps);
    } else if (commit($dbc, $tmps)) {
        $name = fetch_resource($rid)["name"];
        $title = fetch_book($isbn)["title"];
        set_success("Successfully unlinked $name from $title");
    }
}

/*function generate_thumb($rid, $url, $display, $type) {
    $type = pathinfo($file, PATHINFO_EXTENSION);
    if ($display == "image" || $display == "overlay") {
        $size = getimagesize($file);
        $width = $size[0];
        $height = $size[1];
        $w = ($height > 200) ? ($width * 200 / $height) : $width;
        $h = ($height > 200) ? 200 : $height;
        $src = false;

        if ($type == "png") {
            $src = imagecreatefrompng($file);
        } else if ($type == "jpeg") {
            $src = imagecreatefromjpeg($file);
        } else if ($type == "gif") {
            $src = imagecreatefromgif($file);
        }

        if (!$src) {
            add_error("Thumbnail generation failed");
            return array();
        } else {
            $dst = imagecreatetruecolor($w, $h);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $width, $height);
            imagepng($dst, $thumb);
            return array();
        }
    } else if ($display == "video") {
        $file = escapeshellarg($file);
        $thumb = escapeshellarg($thumb);
        $cmd = "ffmpeg -i $file -vframes 1 $thumb";
        exec($cmd, $output, $status); // Create screenshot
        if ($status) {
            return;
        } else {
            return create_thumb($thumb, $thumb); // Shrink screenshot to thumbnail size
        }
    } else if ($display == "audio") {
        return array();
    } else if ($display == "webpage") {
        return array();
    } else {
        add_error("Unknown display mode");
        return array();
    }
}

*/
