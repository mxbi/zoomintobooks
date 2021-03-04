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

function publisher_exists($publisher) {
    $publisher = sanitise($publisher);
    $c = db_select("SELECT 1 FROM publisher WHERE publisher = '$publisher'", true);
    $c = $c ? $c : array();
    return count($c) === 1;
}

function fetch_publisher($username) {
    if (!authorised("view user", array("username" => $username))) return array();
    $username = sanitise($username);
    return db_select("SELECT p.* FROM publisher AS p JOIN user AS u ON p.publisher = u.publisher AND u.username = '$username'", true);
}

function fetch_users() {
    if (!authorised("list users")) return array();
    $username = sanitise($_SESSION["username"]);
    return db_select("SELECT * FROM user");
}

function fetch_user($username) {
    if (!authorised("view user", array("username" => $username))) return array();
    $username = sanitise($username);
    return db_select("SELECT * FROM user WHERE username = '$username'", true);
}

function fetch_publishers() {
    if (!authorised("list publishers")) return array();
    $username = sanitise($_SESSION["username"]);
    return db_select("SELECT * FROM publisher");
}


function manage_user($values, $edit) {
    global $dbc;

    $admin = sanitise($_SESSION["username"]);
    $username = sanitise($values["username"]);
    $password = $values["password"];
    $password2 = $values["password2"];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $publisher = sanitise($values["publisher"]);

    if ($edit && !authorised("edit user", array("username" => $username))) return;
    if (!$edit && !authorised("add user")) return;

    if ($password !== $password2) {
        add_error("Passwords do not match");
        return;
    }

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "INSERT INTO user(username, password, publisher, user_type) VALUES ('$username', '$hash', '$publisher', 'standard')";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error(mysqli_error($dbc));
    }

    if (errors_occurred()) {
        rollback($dbc, array());
    } else if (commit($dbc, array())) {
        set_success("Created user $username");
    }
}

function manage_publisher($values, $edit) {
    global $dbc;
    $publisher = sanitise($values["publisher"]);
    $email = sanitise($values["email"]);
    // TODO: make this consistent with how it's done in manage_book
    if ($edit && !authorised("edit publisher", array("publisher" => $publisher))) return;
    if (!$edit  && !authorised("add publisher")) return;

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "INSERT INTO publisher(publisher, email) VALUES ('$publisher', '$email')";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error(mysqli_error($dbc));
    }

    if (errors_occurred()) {
        rollback($dbc, array());
    } else if (commit($dbc, array())) {
        set_success("Created publisher $publisher");
    }
}

function show_user_form($edit, $username = NULL) {
    if ($edit && !authorised("edit user", array("username" => $username))) return;
    if (!$edit  && !authorised("add user")) return;

    // TODO: selectable menu of available publishers
    $values = array("username" => "", "publisher" => "");
    if ($edit) {
        $values = fetch_user($username);
        if (empty($values)) {
            add_error("Failed to load values for $username");
        }
    }
?>
   <form action="action.php" method="POST">
    <div class="input-container">
     <label for="username">Username</label>
     <input type="text" name="username" id="username-input" placeholder="Username" required="required" value="<?php echo $values["username"]; ?>" />
    </div>
    <div class="input-container">
     <label for="password">Password</label>
     <input type="password" name="password" id="password-input" placeholder="Password" required="required" />
    </div>
    <div class="input-container">
     <label for="password2">Repeat password</label>
     <input type="password" name="password2" id="password2-input" placeholder="Password again" required="required" />
    </div>
    <div class="input-container">
     <label for="publisher">Publisher</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" required="required" value="<?php echo $values["publisher"]; ?>" />
    </div>
    <input type="button" id="manage-user-btn" onclick="manageUser()" value="<?php echo $edit ? "Edit user" : "Create user"; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

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
   <form action="action.php" method="POST">
    <div class="input-container">
     <label for="publisher">Publisher name</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" value="<?php echo $values["publisher"]; ?>" required="required" />
    </div>
    <div class="input-container">
     <label for="email">Publisher e-mail</label>
     <input type="email" name="email" id="email-input" placeholder="E-mail" value="<?php echo $values["email"]; ?>" required="required" />
    </div>
    <input type="button" id="manage-publisher-btn" onclick="managePublisher()" value="<?php echo $edit ? "Edit publisher" : "Create publisher"; ?>" />
   </form>
<?php
}

?>
