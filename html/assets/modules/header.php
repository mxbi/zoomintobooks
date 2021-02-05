<?php
function make_header($title, $description, $keywords, $nav_pages) {
    ob_start();
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
  <link rel="favicon" href="/assets/images/icons/favicon.png" />
 </head>
 <body>
  <header>
   <h1>
    <a href="/"><img src="/assets/images/icons/header.png" alt="" />Zoom Into Books</a>
   </h1>
   <nav id="header-nav">
    <ul>
<?php
    foreach ($nav_pages as $title => $url) {
        echo "     <li><a href=\"$url\">$title</a></li>\n";
    }
?>
    </ul>
   </nav>
  </header>
<?php
}
?>
