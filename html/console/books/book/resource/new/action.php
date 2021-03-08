<?php
require $_SERVER["DOCUMENT_ROOT"] . "/assets/modules/includes.php";

$isbn = sanitise($_POST["isbn"]);

$authorised = authorised("edit book", array("isbn" => $isbn));
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    add_error("Request method must be POST");
} else if ($authorised) {
    $resources = $_POST["resources"];
    $trigger_images = $_FILES;
    $pages = explode(" ", str_replace(",", " ", trim(sanitise($_POST["pages"]))));
    if (empty($resources)) {
        add_error("No resources selected");
    }
    if (empty($trigger_images) && empty($pages)) {
        add_error("No triggers selected");
    }
    if (!errors_occurred()) {
        $trigger_images = array();
        $img_count = empty($_FILES["trigger_images"]) ? 0 : count($_FILES["trigger_images"]["name"]);
        for ($i = 0; $i < $img_count; $i++) {
            $img = array("name" => $_FILES["trigger_images"]["name"][$i], "size" =>  $_FILES["trigger_images"]["size"][$i], "type" =>  $_FILES["trigger_images"]["type"][$i], "tmp_name" =>  $_FILES["trigger_images"]["tmp_name"][$i], "error" =>  $_FILES["trigger_images"]["error"][$i]);
            $trigger_images[] = $img;
        }
        manage_resource_links($_POST["isbn"], $resources, $trigger_images, $pages, false);
    }
}
json_status();
?>
