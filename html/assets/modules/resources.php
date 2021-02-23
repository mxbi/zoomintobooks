<?php
function fetch_resource($rid) {
    $rid = sanitise($rid);
    if (!authorised("view resource", array("rid" => $rid))) return;
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
    $rid = sanitise($rid);
    $c = db_select("SELECT 1 FROM resource WHERE rid = $rid", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function get_resource_mime_type($rid) {
    $rid = sanitise($rid);
    $type = db_select("SELECT type FROM resource WHERE rid = $rid", true);
    return $type ? $type["type"] : NULL;
}

function was_resource_uploaded($rid) {
    return get_resource_mime_type($rid) ? true : false;
}

function show_resources($resources)  {
    if (!authorised("list resources")) return;
    foreach ($resources as $resource) {
        $rid = $resource["rid"];
        $name = $resource["name"];
        $url = $resource["url"];
        $display = $resource["display"];
        echo "   <a class=\"card-list-item\" href=\"resource?rid=$rid\">\n";
        echo "    <img src=\"resource/preview?rid=$rid\" alt=\"Preview of $name\" height=\"128\" />\n";
        echo "    <h4>$name</h4>\n";
        echo "    <p>$url</p>\n";
        echo "    <p>Display as $display</p>\n";
        echo "   </a>\n";
    }
}

function can_edit_resource($rid) {
    global $is_admin;
    if ($is_admin) return true;
    $username = sanitise($_SESSION["username"]);
    $rid = sanitise($rid);
    $c = db_select("SELECT 1 FROM resource_editable_by WHERE rid = $rid AND username = '$username'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function show_resource_form($edit, $rid=NULL) {
    if (!$edit && !authorised("add resource")) return;
    if ($edit && !authorised("edit resource", array("rid" => $rid))) return;
    $values = array();
    if ($edit) {
        $values = fetch_resource($rid);
        if (empty($values)) {
            add_error("Failed to load values for $rid");
            return;
        }
    }
    $display = get_form_value("display", $values, $default="overlay");
    $downloadable = get_form_value("downloadable", $values, $default="1") === "1";
    $uploaded = was_resource_uploaded($values["rid"]);
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="MAX_FILE_SIZE" value="50000000" />
    <input type="hidden" name="rid" value="<?php echo $edit ? $values["rid"] : -1;?>" />
    <div class="input-container">
     <label for="name">Name</label>
     <input type="text" name="name" id="name-input" placeholder="Name" required="required" value="<?php echo get_form_value("name", $values); ?>" />
    </div>
    <div class="input-container">
     <label for="url">URL</label>
     <input type="text" name="url" id="url-input" placeholder="URL" value="<?php echo get_form_value("url", $values); ?>" />
<?php if ($uploaded) { echo "     <span>This resource was uploaded to this server</span>\n"; } ?>
    </div>
    <div class="input-container">
     <label for="resource">Resource upload</label>
     <input type="file" name="resource" id="resource-input" />
     <span>Uploading a file will make it publicly accessible on our server.</span>
<?php if ($uploaded) { echo "     <span>Uploading another resource will overwrite the old one.</span>\n"; } ?>
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
    <input type="submit" value="<?php echo $edit ? "Edit resource" : "Add resource" ; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

function manage_resource($file, $values, $edit) {
    global $dbc;

    $username = sanitise($_SESSION["username"]);
    $rid = sanitise($values["rid"]);
    $name = sanitise($values["name"]);
    $url = sanitise($values["url"]);
    $display = sanitise($values["display"]);
    $downloadable = !empty($values["downloadable"]) && $values["downloadable"] === "downloadable";
    $downloadable = $downloadable ? 1 : 0;

    if (!$edit && !authorised("add resource")) return false;
    if ($edit && !authorised("edit resource", array("rid" => $rid))) return false;

    $_SESSION["sticky"]["name"] = $name;
    $_SESSION["sticky"]["url"] = $url;
    $_SESSION["sticky"]["display"] = $display;
    $_SESSION["sticky"]["downloadable"] = $downloadable ? "downloadable" : "";

    $file_present = file_exists($file['tmp_name']) && is_uploaded_file($file['tmp_name']);

    if (!$file_present && !is_valid_url($url)) add_error("URL is invalid");
    if ($edit && !is_pos_int($rid)) add_error("rid is invalid");
    if (is_blank($name)) add_error("Name is blank");
    if (!is_valid_resource_display_mode($display)) add_error("Resource display mode is invalid");

    if (errors_occurred()) return false;

    // Perform updates to database and file system

    // TODO: produce preview of resource

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

    $type = $file_present ? get_type($file, MAX_RESOURCE_FILE_SIZE, RESOURCE_TYPES) : NULL;
    if (!errors_occurred() && $type) {
        if (!move_uploaded_file($file["tmp_name"], resource_upload_path($rid, $type))) {
            add_error("Failed to upload resource");
        } else {
            $url = "http://86.23.244.209:8080/console/resources/resource?rid=$rid";
        }
    }

    $q = "UPDATE resource SET url='$url'" . ($type ? ", type='$type'" : "") . " WHERE rid='$rid'";
    $r = mysqli_query($dbc, $q);
    if (!$r) add_error(mysqli_error($dbc));

    $_SESSION["sticky"]["url"] = $url;

    if (errors_occurred()) {
        mysqli_rollback($dbc);
    } else {
        if (mysqli_commit($dbc)) {
            if ($edit) set_success("Updated $name");
            else set_success("Added $name");
            $_SESSION["redirect"] = "/console/resources/resource?rid=$rid";
        } else {
            add_error("Commit failed");
            mysqli_rollback($dbc);
        }
    }
}
?>
