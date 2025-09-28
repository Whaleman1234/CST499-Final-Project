<?php
// login.php Handles student authentication and session management
session_start();
require_once 'config.php';
require_once 'sharedfunctions.php';

if (isset($_SESSION['login'])) redirectTo("welcome.php");

// Holds submitted errors
$errors = [];

// Verifies email/password for login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password = $_POST["password"] ?? '';
    
    if (!$email) $errors[] = "Email is required";
    elseif (!validateEmail($email)) $errors[] = "Invalid email format";
    
    if (!$password) $errors[] = "Password is required";
    
    if (!$errors) {
        $user = queryOne($con, "SELECT * FROM tblStudent WHERE email = ?", [$email], "s");
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['login'] = $user;
            redirectTo("welcome.php");
        } else {
            $errors[] = "Email or password is incorrect";
        }
    }
}
?>

<!-- Login Fields and Display Errors -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css" />
    <style>.error {color: #FF0000;}</style>
</head>
<body>
    <h1>Login Page</h1>
    <nav>
        <a href="index.php" class="btn">Home</a>
        <a href="registration.php" class="btn">Registration</a>
        <br></br>
    </nav>

    <?php if ($errors): ?>
        <?php foreach($errors as $error): ?>
            <p class="error"><?= sanitizeInput($error) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= sanitizeInput($email ?? '') ?>" required>
        <br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <br><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>