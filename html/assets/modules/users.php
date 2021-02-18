<?php
function authenticate($username, $password) {
    $username = sanitise($username);
    $hash = db_select("SELECT password FROM user WHERE username = '$username'", true)["password"];
    $hash2 = db_select("SELECT password FROM administrator WHERE username = '$username'", true)["password"];
    if (password_verify($password, $hash)) {
        $_SESSION["username"] = $username;
        $_SESSION["account_type"] = "standard";
        return true;
    } else if (password_verify($password, $hash2)) {
        $_SESSION["username"] = $username;
        $_SESSION["account_type"] = "admin";
        return true;
    } else {
        return false;
    }
}

function fetch_publisher($username) {
    if (!authorised("view user", array("username" => $username))) return array();
    $username = sanitise($username);
    $publisher = db_select("SELECT p.*, p.name FROM publisher AS p JOIN user AS u ON p.pub_id = u.pub_id AND u.username = '$username'", true);
    return $publisher;
}

function get_pub_id($name) {
    global $dbc;
    $name = sanitise($name);
    $q = "SELECT pub_id FROM publisher WHERE name = '$name'";
    $r = mysqli_query($dbc, $q);
    $rows = mysqli_num_rows($r);
    $pub_id = -1;
    if (!$r) {
        add_error("Failed to get pub_id for $name (" . mysqli_error($dbc) . ")");
    } else if ($rows == 0) {
        add_error("Publisher does not exist");
    } else if ($rows > 1) {
        add_error("Multiple publishers with name");
    } else {
        $pub_id = mysqli_fetch_array($r, MYSQLI_ASSOC)["pub_id"];
    }
    mysqli_free_result($r);
    return $pub_id;
}

function fetch_users() {
    if (!authorised("list users")) return array();
    $username = sanitise($_SESSION["username"]);
    return db_select("SELECT u.* FROM user AS u JOIN managed_by AS m ON m.username = u.username AND m.admin_username = '$username'");
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
    $admin = sanitise($_SESSION["username"]);
    $username = sanitise($values["username"]);
    $password = $values["password"];
    $password2 = $values["password2"];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $publisher = sanitise($values["publisher"]);
    $pub_id = get_pub_id($publisher);
    $mode = sanitise($values["mode"]);
    if ($mode === "edit" && !authorised("edit user", array("username" => $username))) return;
    if ($mode === "new"  && !authorised("add user")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }

    global $dbc;
    $_SESSION["sticky"]["username"] = $username;
    $_SESSION["sticky"]["publisher"] = $publisher;

    if ($password !== $password2) {
        add_error("Passwords do not match");
        return false;
    }
    if ($pub_id < 0) {
        return false;
    }

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "(SELECT 1 FROM user WHERE username = '$username') UNION (SELECT 1 FROM administrator WHERE username = '$username')";
    $r = mysqli_query($dbc, $q);
    $success = false;
    if (!$r) {
        add_error("Failed to check for user existence when adding user (" . mysqli_error($dbc). ")");
    } else if (mysqli_num_rows($r) > 0) {
        add_error("$username already exists");
    } else {
        $q2 = "INSERT INTO user(username, password, pub_id) VALUES ('$username', '$hash', $pub_id)";
        $r2 = mysqli_query($dbc, $q2);
        if (!$r2) {
            add_error("Failed to insert into user table (" . mysqli_error($dbc) . ")");
        } else {
            $q3 = "INSERT INTO managed_by(username, admin_username) VALUES ('$username', '$admin')";
            $r3 = mysqli_query($dbc, $q3);
            if (!$r3) {
                add_error("Failed to insert into managed_by table (" . mysqli_error($dbc) . ")");
            } else {
                $success = true;
                set_success("Created user $username");
                $_SESSION["redirect"] = "/console/users/";
            }
            mysqli_free_result($r3);
        }
        mysqli_free_result($r2);
    }
    mysqli_free_result($r);

    if (!$success) {
        mysqli_rollback($dbc);
    } else {
        mysqli_commit($dbc);
    }
    return $success;
}

function add_publisher($values) {
    global $dbc;
    $name = sanitise($values["name"]);
    $mode = sanitise($values["mode"]);
    if ($mode === "edit" && !authorised("edit publisher", array("pub_id" => get_pub_id($name)))) return;
    if ($mode === "new"  && !authorised("add publisher")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }
    // TODO: publisher_managed_by table

    mysqli_begin_transaction($dbc, MYSQLI_TRANS_START_READ_WRITE);
    $q = "(SELECT 1 FROM publisher WHERE name = '$name')";
    $r = mysqli_query($dbc, $q);
    $success = false;
    if (!$r) {
        add_error("Failed to check for publisher existence when adding publisher (" . mysqli_error($dbc). ")");
    } else if (mysqli_num_rows($r) > 0) {
        add_error("$name already exists");
    } else {
        $q2 = "INSERT INTO publisher(name) VALUES ('$name')";
        $r2 = mysqli_query($dbc, $q2);
        if (!$r2) {
            add_error("Failed to insert into publisher table (" . mysqli_error($dbc) . ")");
        } else {
            $success = true;
            set_success("Created publisher $name");
        }
        mysqli_free_result($r2);
    }
    mysqli_free_result($r);

    if (!$success) {
        mysqli_rollback($dbc);
    } else {
        mysqli_commit($dbc);
    }
    return $success;
}

function show_user_form($mode, $username) {
    if ($mode === "edit" && !authorised("edit user", array("username" => $username))) return;
    if ($mode === "new"  && !authorised("add user")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }
    // TODO: selectable menu of available publishers
    $values = $_SESSION["sticky"];
    if ($mode == "edit") {
        $values = fetch_user($username);
        if (empty($values)) {
            add_error("Failed to load values for $username");
        }
    }
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="$mode" />
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
    <input type="submit" value="<?php echo $mode == "new" ? "Create user" : "Edit user"; ?>" />
   </form>
<?php
    unset($_SESSION["sticky"]);
}

function show_publisher_form($mode, $pub_id = NULL) {
    if ($mode === "edit" && !authorised("edit publisher", array("pub_id" => $pub_id))) return;
    if ($mode === "new"  && !authorised("add publisher")) return;
    if ($mode !== "edit" && $mode !== "new") {
        add_error("Illegal mode: $mode");
        return;
    }
?>
   <form action="action.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="$mode" />
    <div class="input-container">
     <label for="name">Publisher name</label>
     <input type="text" name="name" id="name-input" placeholder="Name" required="required" />
    </div>
    <input type="submit" value="Create publisher" />
   </form>
<?php
}

?>
