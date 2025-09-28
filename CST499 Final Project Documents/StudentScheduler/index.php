<?php
// index.php Landing page with navigation links based on login status
session_start();
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Landing Page</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <h1>Welcome to the Project University Class Scheduler</h1>
    <nav>
<?php if (!isset($_SESSION['login'])): ?>
    <a href="login.php">Login</a>
    <a href="registration.php">Registration</a>
<?php else: ?>
    <a href="welcome.php">Dashboard</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
<?php endif; ?>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>