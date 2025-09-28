<?php
// profile.php Displays student profile information in read-only format
require_once 'requirelogon.php';
require_once 'config.php';

// Initialize variables
$profile = ['firstName' => '', 'lastName' => '', 'email' => '', 'phone' => '', 'address' => ''];
$errorMessage = '';

// Fetch current student profile data
if (isset($studentID) && $con) {
    $query = "SELECT firstName, lastName, email, phone, address FROM tblstudent WHERE id = ?";
    $stmt = $con->prepare($query);
    
    if ($stmt === false) {
        $errorMessage = "Database query preparation failed: " . $con->error;
    } else {
        $stmt->bind_param("i", $studentID);
        if (!$stmt->execute()) {
            $errorMessage = "Database execution failed: " . $stmt->error;
        } else {
            $result = $stmt->get_result();
            if ($result && $row = $result->fetch_assoc()) {
                $profile = $row;
            } else {
                $errorMessage = "No profile data found for your account. Please contact support.";
            }
        }
        $stmt->close();
    }
} else {
    $errorMessage = "Database connection or student ID not available. Please log in again.";
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Profile - Student Scheduler</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <div class="container">
    <h1>Student Profile</h1>

    <nav>
      <a href="index.php" class="btn btn-secondary">Home</a>
      <a href="classes.php" class="btn">My Classes</a>
      <a href="scheduleclasses.php" class="btn">Schedule Classes</a>
      <a href="welcome.php" class="btn">Dashboard</a>
      <a href="logout.php" class="btn btn-secondary">Logout</a>
    </nav>

    <?php if ($errorMessage): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

<!-- User Info Table -->
    <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: 20px 0 20px 0;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr style="background-color: #f8f9fa;">
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold;">Field</th>
                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold;">Value</th>
            </tr>
            <tr style="background-color: white;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">First Name</td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($profile['firstName']) ?></td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">Last Name</td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($profile['lastName']) ?></td>
            </tr>
            <tr style="background-color: white;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">Email</td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($profile['email']) ?></td>
            </tr>
            <tr style="background-color: #f8f9fa;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">Phone</td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($profile['phone']) ?: 'Not provided' ?></td>
            </tr>
            <tr style="background-color: white;">
                <td style="padding: 12px; border-bottom: 1px solid #ddd;">Address</td>
                <td style="padding: 12px; border-bottom: 1px solid #ddd;"><?= htmlspecialchars($profile['address']) ?: 'Not provided' ?></td>
            </tr>
        </table>
    </div>
  </div>

</body>
</html>