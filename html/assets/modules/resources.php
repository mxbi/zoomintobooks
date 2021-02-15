<?php
function fetch_resources($isbn) {
    global $dbc;
    $isbn = sanitise($isbn);
    $q  = "SELECT r.* FROM resource AS r ";
    $q .= "JOIN has_resource AS hr ON hr.rid = r.rid AND hr.isbn = '$isbn'";
    $r = mysqli_query($dbc, $q);
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}
?>
