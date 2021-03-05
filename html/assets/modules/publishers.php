<?php
function show_publisher_form($edit, $publisher = NULL) {
    if ($edit && !authorised("edit publisher", array("publisher" => $publisher))) return;
    if (!$edit  && !authorised("add publisher")) return;

    $values = array("publisher" => "", "email" => "");
    if ($edit) {
        $values = fetch_publisher($publisher);
        if (empty($values)) {
            add_error("Failed to load values for $publisher");
        }
    }
?>
   <form>
<?php
if (!$edit) { ?>
    <div class="input-container">
     <label for="publisher">Publisher name</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" value="<?php echo $values["publisher"]; ?>" required="required" />
    </div>
<?php
} else {
?>
    <div class="input-container">
     <input type="hidden" name="publisher" id="publisher-input" value="<?php echo $values["publisher"]; ?>" />
     <label for="new-publisher">Publisher name</label>
     <input type="text" name="new_publisher" id="new-publisher-input" placeholder="Publisher" value="<?php echo $values["publisher"]; ?>" required="required" />
    </div>
<?php } ?>
    <div class="input-container">
     <label for="email">Publisher e-mail</label>
     <input type="email" name="email" id="email-input" placeholder="E-mail" value="<?php echo $values["email"]; ?>" required="required" />
    </div>
    <input type="button" id="manage-publisher-btn" onclick="managePublisher()" value="<?php echo $edit ? "Edit publisher" : "Create publisher"; ?>" />
   </form>
<?php
}

function manage_publisher($values, $edit) {
    global $dbc;
    $publisher = sanitise($values["publisher"]);
    $new_publisher = empty($values["new_publisher"]) ? NULL : sanitise($values["new_publisher"]);
    $email = sanitise($values["email"]);
    if ($edit && !authorised("edit publisher", array("publisher" => $publisher))) return;
    if (!$edit  && !authorised("add publisher")) return;

    if (is_blank($publisher)) add_error("Publisher name cannot be blank");
    if ($edit && is_blank($new_publisher)) add_error("New publisher name cannot be blank");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) add_error("Invalid e-mail address");

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    if (!$edit) {
        $q = "INSERT INTO publisher(publisher, email) VALUES ('$publisher', '$email')";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    } else {
        $q = "UPDATE publisher SET publisher='$new_publisher', email='$email' WHERE publisher='$publisher'";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    }

    if (errors_occurred()) {
        rollback($dbc, array());
    } else if (commit($dbc, array())) {
        if ($edit) {
            set_success("Edited $new_publisher");
            if ($publisher !== $new_publisher) {
                $_SESSION["redirect"] = "/console/publishers/publisher?publisher=$new_publisher";
            }
        } else {
            set_success("Created publisher $publisher");
            $_SESSION["redirect"] = "/console/publishers/publisher?publisher=$publisher";
        }
    }
}

function fetch_publishers() {
    if (!authorised("list publishers")) return array();
    $username = sanitise($_SESSION["username"]);
    return db_select("SELECT * FROM publisher");
}


function can_edit_publisher($publisher) {
    global $is_admin;
    return $is_admin;
}

function publisher_exists($publisher) {
    if ($publisher === NULL) return false;
    $publisher = sanitise($publisher);
    $c = db_select("SELECT 1 FROM publisher WHERE publisher = '$publisher'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function fetch_user_publisher($username) {
    if (!authorised("view user", array("username" => $username))) return array();
    $username = sanitise($username);
    return db_select("SELECT publisher FROM user WHERE username = '$username'", true);
}

function fetch_publisher($publisher) {
    if (!authorised("view publisher", array("publisher" => $publisher))) return array();
    $publisher = sanitise($publisher);
    return db_select("SELECT * FROM publisher WHERE publisher='$publisher'", true);
}

