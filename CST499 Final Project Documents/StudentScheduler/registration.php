<?php
// registration.php Handles new student account creation with validation
session_start();
require_once 'config.php';
require_once 'sharedfunctions.php';

if (isset($_SESSION['login'])) 
    redirectTo("welcome.php");

// Holds submitted errors and form data
$errors = [];
$formData = [];

// Sanitize and generate form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formData = [
        'email' => $_POST["email"] ?? '',
        'password' => $_POST["password"] ?? '',
        'vpassword' => $_POST["vpassword"] ?? '',
        'fName' => sanitizeInput($_POST["fName"] ?? ''),
        'lName' => sanitizeInput($_POST["lName"] ?? ''),
        'address' => sanitizeInput($_POST["address"] ?? ''),
        'pnumber' => preg_replace('/\D/', '', $_POST["pnumber"] ?? '')
    ];
    
    // Form validation
    if (!$formData['email']) $errors[] = "Email is required";
    elseif (!validateEmail($formData['email'])) $errors[] = "Invalid email format";
    elseif (emailExists($con, $formData['email'])) $errors[] = "Email already registered";
    
    if (!$formData['password']) $errors[] = "Password is required";
    elseif (!validatePassword($formData['password'])) $errors[] = "Password must be at least 8 characters with uppercase, lowercase, number, and special character";
    
    if (!$formData['vpassword']) $errors[] = "Please confirm your password";
    elseif ($formData['password'] !== $formData['vpassword']) $errors[] = "Passwords do not match";
    
    if (!$formData['fName']) $errors[] = "First name is required";
    if (!$formData['lName']) $errors[] = "Last name is required";
    if (!$formData['address']) $errors[] = "Address is required";
    if (!$formData['pnumber']) $errors[] = "Phone number is required";
    elseif (strlen($formData['pnumber']) !== 10) $errors[] = "Phone number must be exactly 10 digits";
    
    // Password hashing and form submission
    if (!$errors) {
        $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
        if (executeQuery($con, "INSERT INTO tblStudent (email, password, firstName, lastName, address, phone) VALUES (?, ?, ?, ?, ?, ?)", 
            [$formData['email'], $hashedPassword, $formData['fName'], $formData['lName'], $formData['address'], $formData['pnumber']], "ssssss")) {
            redirectTo("login.php?msg=registered");
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Registration Page</title>
    <link rel="stylesheet" href="style.css" />
    <style>.error {color: #FF0000;}</style>
</head>
<body>
    <h1>Registration</h1>
    <nav>
        <a href="index.php" class="btn">Home</a>
        <a href="login.php" class="btn">Login</a>
        <br></br>
    </nav>
    
    <!-- Display Errors -->
    <?php if ($errors): ?>
        <?php foreach($errors as $error): ?>
            <p class="error"><?= $error ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- form fields -->
    <form method="post">
        First Name: <input type="text" name="fName" value="<?= $formData['fName'] ?? '' ?>">
        <br><br>
        Last Name: <input type="text" name="lName" value="<?= $formData['lName'] ?? '' ?>">
        <br><br>
        Email: <input type="email" name="email" value="<?= $formData['email'] ?? '' ?>">
        <br><br>
        Address: <input type="text" name="address" value="<?= $formData['address'] ?? '' ?>">
        <br><br>
        Phone Number: <input type="tel" name="pnumber" value="<?= $formData['pnumber'] ?? '' ?>">
        <br><br>
        Password: <input type="password" name="password">
        <br><br>
        Verify Password: <input type="password" name="vpassword">
        <br><br>
        <input type="submit" value="Register">
    </form>
</body>
</html>