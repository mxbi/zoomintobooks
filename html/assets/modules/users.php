<?php
function authenticate($username, $password) {
    $username = sanitise($username);
    $result = db_select("SELECT password, user_type FROM user WHERE username = '$username'", true);
    if (password_verify($password, $result["password"])) {
        $_SESSION["username"] = $username;
        $_SESSION["account_type"] = $result["user_type"];
        return true;
    } else {
        return false;
    }
}

function user_exists($username) {
    if ($username === NULL) return false;
    $username = sanitise($username);
    $c = db_select("SELECT 1 FROM user WHERE username = '$username'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function can_edit_user($username) {
    global $is_admin;
    return $is_admin || ($_SESSION["username"] == $username);
}

function fetch_users() {
    if (!authorised("list users")) return array();
    $username = sanitise($_SESSION["username"]);
    return db_select("SELECT username, publisher, user_type FROM user");
}

function fetch_user($username) {
    if (!authorised("view user", array("username" => $username))) return array();
    $username = sanitise($username);
    return db_select("SELECT username, publisher, user_type FROM user WHERE username = '$username'", true);
}

function manage_user($values, $edit) {
    global $dbc;

    $username = sanitise($values["username"]);
    $new_username = empty($values["new_username"]) ? NULL : sanitise($values["new_username"]);
    $password = $values["password"];
    $password2 = $values["password2"];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $publisher = empty($values["publisher"]) ? NULL : sanitise($values["publisher"]);

    if ($edit && !authorised("edit user", array("username" => $username))) return;
    if (!$edit && !authorised("add user")) return;

    if (is_blank($username)) add_error("Username cannot be blank");
    if ($edit && is_blank($new_username)) add_error("New username cannot be blank");
    if ($password !== $password2) add_error("Passwords do not match");
    if ((!$edit || ($edit && fetch_user($username)["user_type"] === "standard")) && !publisher_exists($publisher)) add_error("Publisher does not exist");

    if (errors_occurred()) return;

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    if (!$edit) {
        $q = "INSERT INTO user(username, password, publisher, user_type) VALUES ('$username', '$hash', '$publisher', 'standard')";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    } else {
        $q = "UPDATE user SET username='$new_username'";
        if (!empty($password)) {
            $q .= ", password='$hash'";
        }
        if ($user["user_type"] === "standard") {
            $q .= ", publisher='$publisher'";
        }
        $q .= " WHERE username='$username'";
        $r = mysqli_query($dbc, $q);
        if (!$r) {
            add_error(mysqli_error($dbc));
        }
    }

    if (errors_occurred()) {
        rollback($dbc, array());
    } else if (commit($dbc, array())) {
        if ($edit) {
            set_success("Edited $new_username");
            if ($new_username !== $username) {
                $_SESSION["redirect"] = "/console/users/user?username=$new_username";
            }
        } else {
            set_success("Created $username");
            $_SESSION["redirect"] = "/console/users/user?username=$username";
        }
    }
}

function show_user_form($edit, $username = NULL) {
    if ($edit && !authorised("edit user", array("username" => $username))) return;
    if (!$edit  && !authorised("add user")) return;

    // TODO: selectable menu of available publishers
    $values = array("username" => "", "publisher" => "", "user_type" => "standard");
    if ($edit) {
        $values = fetch_user($username);
        if (empty($values)) {
            add_error("Failed to load values for $username");
        }
    }
?>
   <form>
<?php
if (!$edit) { ?>
    <div class="input-container">
     <label for="username">Username</label>
     <input type="text" name="username" id="username-input" placeholder="Username" required="required" value="<?php echo $values["username"]; ?>" />
    </div>
<?php
} else {
 ?>
    <div class="input-container">
     <input type="hidden" name="username" id="username-input" value="<?php echo $values["username"]; ?>" />
     <label for="new_username">Username</label>
     <input type="text" name="new_username" id="new-username-input" placeholder="Username" required="required" value="<?php echo $values["username"]; ?>" />
    </div> <?php
} ?>
    <div class="input-container">
     <label for="password">Password</label>
     <input type="password" name="password" id="password-input" placeholder="Password" required="required" />
     <?php echo "<p><small>If left blank, the password will not be changed.</small></p>"; ?>
    </div>
    <div class="input-container">
     <label for="password2">Repeat password</label>
     <input type="password" name="password2" id="password2-input" placeholder="Password again" required="required" />
    </div>
<?php if ($values["user_type"] == "standard") { ?>
    <div class="input-container">
     <label for="publisher">Publisher</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" required="required" value="<?php echo $values["publisher"]; ?>" />
    </div>
<?php } ?>
    <input type="button" id="manage-user-btn" onclick="manageUser()" value="<?php echo $edit ? "Edit user" : "Create user"; ?>" />
   </form>
<?php
}

function delete_user($username) {
    global $dbc;
    if (!authorised("delete user", array("username" => $username))) return;
    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "DELETE FROM user WHERE username='$username'";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error(mysqli_error($dbc));
    }
    if (errors_occurred()) {
        rollback($dbc, array());
    } else if (commit($dbc, array())) {
        set_success("Deleted " . $username);
        $_SESSION["redirect"] = "/console/users/";
    }
}

