<?php
// welcome.php Dashboard page displaying student overview and class management
require_once 'requirelogon.php';
require_once 'config.php';
require_once 'sharedfunctions.php';

// Handle notification dismissal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_notification'])) {
    $notificationId = (int)$_POST['dismiss_notification'];
    if (markNotificationAsRead($con, $notificationId, $studentID)) {
        redirectTo("welcome.php");
    }
}

// Semester sorting by academic year order
function customSemesterSort($a, $b) {
    $seasonA = strtolower(trim($a));
    $seasonB = strtolower(trim($b));
    
    // Define academic year order
    $seasonOrder = ['fall' => 1, 'winter' => 2, 'spring' => 3];
    
    $orderA = $seasonOrder[$seasonA] ?? 999;
    $orderB = $seasonOrder[$seasonB] ?? 999;
    
    // Sort by season order, then alphabetical fallback
    if ($orderA !== $orderB) {
        return $orderA - $orderB;
    } else {
        return strcmp($a, $b);
    }
}

// Holds variables
$selectedSemester = $_GET['semester'] ?? 'all';
$classesBySemester = [];
$semesters = [];
$errorMessage = '';
$allActiveEnrollments = [];
$enrolledCount = 0;
$waitlistedCount = 0;
$totalClasses = 0;
$notifications = [];

// Fetch data with error handling
if (isset($studentID) && $con) {
    try {
        // Get unread notifications
        $notifications = getUnreadNotifications($con, $studentID);
        
        // Fetch all enrollments to get unique semesters
        $allEnrollments = getStudentEnrollments($con, $studentID);
        
        // Filter to active statuses only
        $allActiveEnrollments = array_filter($allEnrollments, function($e) {
            return in_array($e['status'], ['enrolled', 'waitlist']);
        });
        
        // Calculate Quick Stats counts
        $enrolledCount = count(array_filter($allActiveEnrollments, function($e) {
            return $e['status'] === 'enrolled';
        }));
        $waitlistedCount = count(array_filter($allActiveEnrollments, function($e) {
            return $e['status'] === 'waitlist';
        }));
        $totalClasses = $enrolledCount + $waitlistedCount;
        
        // Extract unique semesters from active enrollments
        $semesters = array_unique(array_column($allActiveEnrollments, 'semester'));
        
        // Sort semesters by academic year order
        usort($semesters, 'customSemesterSort');
        
        // Handle selected semester
        if ($selectedSemester !== 'all') {
            $enrollments = getStudentEnrollments($con, $studentID, $selectedSemester);
            $activeEnrollments = array_filter($enrollments, function($e) {
                return in_array($e['status'], ['enrolled', 'waitlist']);
            });
        } else {
            $activeEnrollments = $allActiveEnrollments;
        }
        
        // Group by semester and add professor names
        foreach ($activeEnrollments as $enrollment) {
            $sem = $enrollment['semester'];
            if (!isset($classesBySemester[$sem])) {
                $classesBySemester[$sem] = [];
            }
            
            // Fetch professor name
            $profQuery = "SELECT profFName, profLName FROM tblprofessor p 
                          JOIN tblclasses c ON p.professorID = c.professorID 
                          WHERE c.cid = ?";
            $prof = queryOne($con, $profQuery, [$enrollment['classID']], "i");
            $enrollment['professorName'] = $prof ? trim($prof['profFName'] . ' ' . $prof['profLName']) : 'Unknown';
            
            $classesBySemester[$sem][] = $enrollment;
        }
        
        // If specific semester selected but no data, clear groups
        if ($selectedSemester !== 'all' && !isset($classesBySemester[$selectedSemester])) {
            $classesBySemester = [];
        } else {
            // For 'all', re-sort the grouped array keys by season order for display
            if ($selectedSemester === 'all' && !empty($classesBySemester)) {
                $sortedSemesters = array_keys($classesBySemester);
                usort($sortedSemesters, 'customSemesterSort');
                $sortedGroups = [];
                foreach ($sortedSemesters as $sem) {
                    if (isset($classesBySemester[$sem])) {
                        $sortedGroups[$sem] = $classesBySemester[$sem];
                    }
                }
                $classesBySemester = $sortedGroups;
            }
        }
        
    } catch (Exception $e) {
        $errorMessage = "Error fetching enrollments: " . $e->getMessage();
    }
} else {
    $errorMessage = "Database connection or student ID not available. Please log in again.";
}

// Determine display groups based on semester selection
if ($selectedSemester !== 'all') {
    $displayGroups = isset($classesBySemester[$selectedSemester]) ? [$selectedSemester => $classesBySemester[$selectedSemester]] : [];
} else {
    $displayGroups = $classesBySemester;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Welcome - Student Scheduler</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .notification-panel {
        position: fixed;
        top: 20px;
        right: 20px;
        max-width: 320px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 15px;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        z-index: 1000;
    }
    .notification-header {
        margin: 0 0 12px 0;
        font-size: 16px;
        font-weight: 600;
        color: #343a40;
    }
    .notification-item {
        margin-bottom: 12px;
        padding: 12px;
        background: white;
        border-radius: 4px;
        border-left: 3px solid #007bff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .notification-message {
        margin: 0 0 6px 0;
        font-size: 14px;
        line-height: 1.4;
    }
    .notification-time {
        color: #6c757d;
        font-size: 12px;
        margin-bottom: 8px;
    }
    .dismiss-btn {
        background: #6c757d;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 12px;
    }
    .dismiss-btn:hover {
        background: #5a6268;
    }
    
    /* Responsive design for mobile */
    @media (max-width: 768px) {
        .notification-panel {
            position: relative;
            top: auto;
            right: auto;
            max-width: none;
            margin-bottom: 20px;
        }
    }
  </style>
</head>
<body>

  <div class="container">
    <h1>Welcome to Student Scheduler</h1>

    <nav>
      <a href="index.php" class="btn">Home</a>
      <a href="classes.php" class="btn">My Classes</a>
      <a href="scheduleclasses.php" class="btn">Schedule Classes</a>
      <a href="profile.php" class="btn">Profile</a>
      <a href="logout.php" class="btn">Logout</a>
    </nav>

    <?php if ($errorMessage): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Notification Panel -->
    <?php if (!empty($notifications)): ?>
        <div class="notification-panel">
            <h4 class="notification-header">Updates</h4>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <p class="notification-message"><?= sanitizeInput($notification['message']) ?></p>
                    <div class="notification-time">
                        <?= date('M j, g:i A', strtotime($notification['created_at'])) ?>
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="dismiss_notification" value="<?= $notification['id'] ?>">
                        <button type="submit" class="dismiss-btn">Dismiss</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Quick Stats Section -->
    <div style="display: flex; gap: 20px; margin-top: 20px;">
      <div style="flex: 1; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2>Quick Stats</h2>
        <p>Classes Enrolled: <?= $enrolledCount ?></p>
        <p>Classes Waitlisted: <?= $waitlistedCount ?></p>
        <p>Total Classes: <?= $totalClasses ?></p>
      </div>
      <div style="flex: 1; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h2>Quick Actions</h2>
        <a href="scheduleclasses.php" class="btn" style="display: block; margin-bottom: 10px;">Enroll in New Class</a>
        <a href="classes.php" class="btn" style="display: block;">View Full Schedule</a>
      </div>
    </div>

    <!-- Current Classes Section -->
    <h2>Your Current Classes</h2>
    <form method="GET" style="margin-bottom: 20px;">
      <label for="semester" style="margin-right: 10px; font-weight: bold;">Filter by Semester:</label>
      <select name="semester" id="semester" onchange="this.form.submit();" style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
        <option value="all" <?= ($selectedSemester === 'all') ? 'selected' : '' ?>>All Semesters</option>
        <?php foreach ($semesters as $sem): ?>
          <option value="<?= htmlspecialchars($sem) ?>" <?= ($selectedSemester === $sem) ? 'selected' : '' ?>><?= htmlspecialchars($sem) ?></option>
        <?php endforeach; ?>
      </select>
    </form>

    <?php if (empty($displayGroups)): ?>
        <div style="text-align: center; margin: 40px 0; padding: 20px; background: #f8f9fa; border-radius: 5px;">
            <h3>No Classes Yet</h3>
            <p>You haven't enrolled in any classes for the selected semester(s). <a href="scheduleclasses.php" class="btn">Schedule some now!</a></p>
        </div>
    <?php else: ?>
        <?php foreach ($displayGroups as $semester => $classes): ?>
            <h3><?= htmlspecialchars($semester) ?></h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px; table-layout: fixed;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="width: 35%; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Class Name</th>
                        <th style="width: 25%; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Professor</th>
                        <th style="width: 15%; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Day</th>
                        <th style="width: 25%; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; font-weight: bold; vertical-align: top;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $index => $class): ?>
                        <tr style="background-color: <?= ($index % 2 == 0) ? '#f8f9fa' : 'white' ?>;">
                            <td style="width: 35%; padding: 12px; border-bottom: 1px solid #ddd; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word;"><?= htmlspecialchars($class['className']) ?></td>
                            <td style="width: 25%; padding: 12px; border-bottom: 1px solid #ddd; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word;"><?= htmlspecialchars($class['professorName']) ?></td>
                            <td style="width: 15%; padding: 12px; border-bottom: 1px solid #ddd; vertical-align: top;"><?= htmlspecialchars($class['dayOfWeek']) ?></td>
                            <td style="width: 25%; padding: 12px; border-bottom: 1px solid #ddd; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word;">
                                <span class="<?= htmlspecialchars($class['status']) ?>"><?= htmlspecialchars(ucfirst($class['status'])) ?></span>
                                <?php if ($class['status'] === 'waitlist' && isset($class['waitlist_position']) && $class['waitlist_position'] > 0): ?>
                                    (Position: <?= htmlspecialchars($class['waitlist_position']) ?>)
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php endif; ?>
  </div>

</body>
</html>