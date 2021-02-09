<?php
function make_header($title, $description, $keywords) {
    ob_start();
    session_start();

    $nav_pages = array();
    if (isset($_SESSION["username"])) {
        $nav_pages["Console"] = "/console/";
        $nav_pages["Help"] = "/help/";
        $nav_pages["Logout"] = "/logout/";
    } else {
        $nav_pages["Help"] = "/help/";
        $nav_pages["Login"] = "/login/";
    }


?>
<!DOCTYPE html>

<html lang="en">
 <head>
  <title><?php echo $title; ?></title>
  <meta charset="UTF-8" />
  <meta name="description" content="<?php echo $description; ?>" />
  <meta name="keywords" content="<?php echo $keywords; ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="/assets/styles/main.css" />
  <link rel="stylesheet" href="/assets/styles/fonts.css" />
  <link rel="favicon" href="/assets/images/icons/favicon.png" />
 </head>
 <body>
  <header>
   <h1>
    <a href="/"><img src="/assets/images/icons/header.png" alt="" />Zoom Into Books</a>
   </h1>
   <nav id="header-nav">
<?php
    foreach ($nav_pages as $title => $url) {
        echo "    <a href=\"$url\">$title</a>\n";
    }
?>
   </nav>
  </header>
<?php
}
?>