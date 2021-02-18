<?php
function authenticate($username, $password) {
    global $dbc;
    $username = sanitise($username);
    $q = "SELECT password FROM user WHERE username = '$username'";
    $r = mysqli_query($dbc, $q);
    $success = false;
    $rows = mysqli_num_rows($r);
    if (!$r) {
        add_error("Failed to access table during authentication (" . mysqli_error($dbc) . ")");
    } else if ($rows === 0) {
        $q2 = "SELECT password FROM administrator WHERE username = '$username'";
        $r2 = mysqli_query($dbc, $q2);
        $rows2 = mysqli_num_rows($r2);
        if (!$r2) {
            add_error("Failed to access table during authentication (" . mysqli_error($dbc) . ")");
        } else if ($rows2 === 1) {
            $hash = mysqli_fetch_array($r2, MYSQLI_ASSOC)["password"];
            if (password_verify($password, $hash)) {
                $success = true;
                $_SESSION["username"] = $username;
                $_SESSION["account_type"] = "admin";
            }
        } else if ($rows2 === 0) {
            // Do nowt
        } else if ($rows2 > 1) {
            add_error("Duplicate rows found in table during authentication");
        }
        mysqli_free_result($r2);
    } else if ($rows === 1){
        $hash = mysqli_fetch_array($r, MYSQLI_ASSOC)["password"];
        if (password_verify($password, $hash)) {
            $success = true;
            $_SESSION["username"] = $username;
            $_SESSION["account_type"] = "standard";
        }
    } else {
        add_error("Duplicate rows found in table during authentication");
    }
    mysqli_free_result($r);
    return $success;
}

function get_publisher($username) {
    global $dbc;
    $username = sanitise($username);
    $q = "SELECT p.*, p.name FROM publisher AS p JOIN user AS u ON p.pub_id = u.pub_id AND u.username = '$username'";
    $r = mysqli_query($dbc, $q);
    $rows = mysqli_num_rows($r);
    $publisher = array();
    if (!$r) {
        add_error("Failed to get publisher for $username (" . mysqli_error($dbc) . ")");
    } else if ($rows == 0) {
        add_error("No publisher associated with $username");
    } else if ($rows > 1) {
        add_error("Multiple publishers associated with $username");
    } else {
        $publisher = mysqli_fetch_array($r, MYSQLI_ASSOC);
    }
    mysqli_free_result($r);
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
    global $dbc;
    $username = sanitise($_SESSION["username"]);
    $q = "SELECT u.* FROM user AS u JOIN managed_by AS m ON m.username = u.username AND m.admin_username = '$username'";
    $r = mysqli_query($dbc, $q);
    $users = array();
    if (!$r) {
        add_error("Failed to get users managed by $username (" . mysqli_error($dbc) . ")");
    } else {
        $users = mysqli_fetch_all($r, MYSQLI_ASSOC);
    }
    mysqli_free_result($r);
    return $users;
}

function fetch_user($username) {
    global $dbc;
    $username = sanitise($username);
    $q = "SELECT * FROM user WHERE username = '$username'";
    $r = mysqli_query($dbc, $q);
    if (!$r) {
        add_error("Failed to get users $username (" . mysqli_error($dbc) . ")");
    }
    $user = mysqli_fetch_array($r, MYSQLI_ASSOC);
    mysqli_free_result($r);
    return $user;
}

function fetch_publishers() {
    // TODO: publisher_managed_by
    global $dbc;
    $username = sanitise($_SESSION["username"]);
    $q = "SELECT * FROM publisher";
    $r = mysqli_query($dbc, $q);
    $publishers = array();
    if (!$r) {
        add_error("Failed to get publishers (" . mysqli_error($dbc) . ")");
    } else {
        $publishers = mysqli_fetch_all($r, MYSQLI_ASSOC);
    }
    mysqli_free_result($r);
    return $publishers;
}


function add_user($values) {
    global $dbc;
    $admin = sanitise($_SESSION["username"]);
    $username = sanitise($values["username"]);
    $password = $values["password"];
    $password2 = $values["password2"];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $publisher = sanitise($values["publisher"]);
    $pub_id = get_pub_id($publisher);
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
    // TODO: publisher_managed_by table
    global $dbc;
    $name = sanitise($values["name"]);

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

function show_publisher_form($mode) {
    // TODO: selectable menu of available publishers
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
