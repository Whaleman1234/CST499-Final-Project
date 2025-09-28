<?php
// classes.php Displays student's current class enrollments with filtering options
require_once 'requirelogon.php';
require_once 'config.php';
require_once 'sharedfunctions.php';

$selectedSemester = $_GET['semester'] ?? '';
$enrollments = getStudentEnrollments($con, $studentID, $selectedSemester ?: null);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Classes</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>

<h1>Your Current Classes</h1>

<nav>
    <a href="welcome.php" class="btn">Dashboard</a>
    <a href="profile.php" class="btn">Profile</a>
    <a href="logout.php" class="btn">Logout</a>
    <a href="scheduleclasses.php" class="btn">Schedule Classes</a>
    <br></br>
</nav>

<!-- Semester Filter -->
<form method="GET" style="margin-bottom: 20px;">
    <label for="semester">Filter by Semester:</label>
    <select name="semester" id="semester" onchange="this.form.submit()">
        <option value="">All Semesters</option>
        <option value="Fall" <?= $selectedSemester === 'Fall' ? 'selected' : '' ?>>Fall</option>
        <option value="Winter" <?= $selectedSemester === 'Winter' ? 'selected' : '' ?>>Winter</option>
        <option value="Spring" <?= $selectedSemester === 'Spring' ? 'selected' : '' ?>>Spring</option>
    </select>
</form>

<!-- Enrollment Table -->
<?php if (empty($enrollments)): ?>
    <?php if ($selectedSemester): ?>
        <p>You are not enrolled or waitlisted for any classes in the <?= strtolower($selectedSemester) ?>.</p>
    <?php else: ?>
        <p>You are not enrolled or waitlisted for any classes.</p>
    <?php endif; ?>
    <p>Click <a href='scheduleclasses.php'>here</a> to sign up for the <?= strtolower($selectedSemester) ?> semester.</p>
<?php else: ?>
    <table class="classes-table">
        <tr>
            <th>Class Name</th>
            <th>Day of Week</th>
            <th>Semester</th>
            <th>Status</th>
            <th>Waitlist Position</th>
            <th>Action</th>
        </tr>
        <?php foreach ($enrollments as $enrollment): ?>
            <tr>
                <td><?= sanitizeInput($enrollment['className']) ?></td>
                <td><?= sanitizeInput($enrollment['dayOfWeek']) ?></td>
                <td><?= sanitizeInput($enrollment['semester']) ?></td>
                <td class="<?= $enrollment['status'] ?>">
                    <?= ucfirst($enrollment['status']) ?>
                </td>
                <td><?= $enrollment['status'] === 'waitlist' ? $enrollment['waitlist_position'] : '-' ?></td>
                <td>
                    <form method='POST' action='cancelclass.php' 
                          onsubmit='return confirm("Are you sure you want to cancel this class?")'>
                        <input type='hidden' name='cancel_class_id' value='<?= $enrollment['classID'] ?>'>
                        <input type='submit' value='Cancel'>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>