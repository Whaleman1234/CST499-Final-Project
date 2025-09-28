<?php
// pleaselogin.php Handles redirect without session
session_start();
require_once 'sharedfunctions.php';

// Redirect logged in users to dashboard
if (isset($_SESSION['login'])) {
    redirectTo("welcome.php");
    exit;
}
?>

<!-- Redirect to login page -->
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Please Login</title>
    <meta http-equiv="refresh" content="3;url=login.php" />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <h1>Please log in with a valid profile</h1>
  </body>
</html>