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


function add_user($values) {
    global $dbc;

    $admin = sanitise($_SESSION["username"]);
    $username = sanitise($values["username"]);
    $password = $values["password"];
    $password2 = $values["password2"];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $publisher = sanitise($values["publisher"]);
    $mode = sanitise($values["mode"]);

    if ($mode === "edit" && !authorised("edit user", array("username" => $username))) return;
    if ($mode === "new"  && !authorised("add user")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }

    $_SESSION["sticky"]["username"] = $username;
    $_SESSION["sticky"]["publisher"] = $publisher;

    if ($password !== $password2) {
        add_error("Passwords do not match");
        return false;
    }

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "INSERT INTO user(username, password, publisher, user_type) VALUES ('$username', '$hash', '$publisher', 'standard')";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error(mysqli_error($dbc));
    } else {
        $success = true;
        set_success("Created user $username");
        $_SESSION["redirect"] = "/console/users/";
    }
    mysqli_free_result($r);

    if (!$success) {
        mysqli_rollback($dbc);
    } else {
        mysqli_commit($dbc); // TODO: rollback on commit failure
    }
    return $success;
}

function add_publisher($values) {
    global $dbc;
    $publisher = sanitise($values["publisher"]);
    $email = sanitise($values["email"]);
    $mode = sanitise($values["mode"]);
    // TODO: make this consistent with how it's done in manage_book
    if ($mode === "edit" && !authorised("edit publisher", array("publisher" => $publisher))) return;
    if ($mode === "new"  && !authorised("add publisher")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "INSERT INTO publisher(publisher, email) VALUES ('$publisher', '$email')";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error(mysqli_error($dbc));
    } else {
        $success = true;
        set_success("Created publisher $name");
        $_SESSION["redirect"] = "/console/publishers/";
    }
    mysqli_free_result($r);

    if (!$success) {
        mysqli_rollback($dbc);
    } else {
        mysqli_commit($dbc); // TODO: rollback on commit failure
    }
    return $success;
}

function show_user_form($mode, $username = NULL) {
    if ($mode === "edit" && !authorised("edit user", array("username" => $username))) return;
    if ($mode === "new"  && !authorised("add user")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }
    // TODO: selectable menu of available publishers
    $values = array();
    if ($mode == "edit") {
        $values = fetch_user($username);
        if (empty($values)) {
            add_error("Failed to load values for $username");
        }
    }
?>
   <form action="action.php" method="POST">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
    <div class="input-container">
     <label for="username">Username</label>
     <input type="text" name="username" id="username-input" placeholder="Username" required="required" value="<?php echo get_form_value("username", $values); ?>" />
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
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" required="required" value="<?php echo get_form_value("publisher", $values); ?>" />
    </div>
    <input type="submit" value="<?php echo $mode == "new" ? "Create user" : "Edit user"; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

function show_publisher_form($mode, $publisher = NULL) {
    if ($mode === "edit" && !authorised("edit publisher", array("publisher" => $publisher))) return;
    if ($mode === "new"  && !authorised("add publisher")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }
    // TODO: make sticky
?>
   <form action="action.php" method="POST">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>" />
    <div class="input-container">
     <label for="publisher">Publisher name</label>
     <input type="text" name="publisher" id="publisher-input" placeholder="Publisher" required="required" />
    </div>
    <div class="input-container">
     <label for="email">Publisher e-mail</label>
     <input type="email" name="email" id="email-input" placeholder="E-mail" required="required" />
    </div>
    <input type="submit" value="<?php echo $mode == "new" ? "Create publisher" : "Edit publisher"; ?>" />
   </form>
<?php
}

?>
