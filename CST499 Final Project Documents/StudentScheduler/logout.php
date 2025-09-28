<?php
// logout.php Handles user session termination
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

// Remove all session variables and destroy the session
session_unset();
session_destroy();
?>

<!-- Logout Message Display and Redirect Link -->
<!doctype html>
<html lang="en">
  <head>   
    <meta charset="utf-8">
    <title>Logout</title>
    <meta http-equiv="refresh" content="3;url=index.php" />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <p>Student has been successfully logged out! Redirecting to home...</p>
    <p>If you are not redirected in 3 seconds, <a href="index.php">click here</a>.</p>
  </body>
</html>